<?php

namespace Custom\LaravelHana;

use Illuminate\Database\Connection;

class HanaConnection extends Connection
{
    /** Delimiter used to separate multiple INSERT statements compiled by HanaQueryGrammar */
    const STMT_DELIMITER = '/*HANA_STMT*/';

    protected function getDefaultQueryGrammar()
    {
        return new HanaQueryGrammar($this);
    }

    protected function getDefaultSchemaGrammar()
    {
        return new HanaSchemaGrammar($this);
    }

    protected function getDefaultPostProcessor()
    {
        return new HanaProcessor();
    }

    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }
        return new HanaSchemaBuilder($this);
    }

    /**
     * Substitute bindings directly into SQL — HANA ODBC does not support PDO params.
     */
    protected function substituteBindings(string $query, array $bindings): string
    {
        foreach ($bindings as $binding) {
            $value = match (true) {
                is_null($binding)                    => 'NULL',
                is_bool($binding)                    => $binding ? '1' : '0',
                is_int($binding), is_float($binding) => (string) $binding,
                default                              => "'" . str_replace("'", "''", (string) $binding) . "'",
            };
            // Use str_replace with first occurrence only to avoid regex issues with $ in bcrypt hashes
            $pos = strpos($query, '?');
            if ($pos !== false) {
                $query = substr_replace($query, $value, $pos, 1);
            }
        }
        return $query;
    }

    /**
     * Split a query on HANA_STMT delimiter and execute each part.
     * Returns total rows affected.
     */
    protected function executeStatements(string $query, array $preparedBindings): int
    {
        $statements    = array_filter(array_map('trim', explode(self::STMT_DELIMITER, $query)));
        $totalRows     = 0;
        $bindingOffset = 0;

        foreach ($statements as $sql) {
            $count        = substr_count($sql, '?');
            $stmtBindings = array_slice($preparedBindings, $bindingOffset, $count);
            $bindingOffset += $count;

            $interpolated = $this->substituteBindings($sql, $stmtBindings);
            $rows         = $this->getPdo()->exec($interpolated);

            if ($rows !== false) {
                $totalRows += $rows;
            }
        }

        return $totalRows;
    }

    public function select($query, $bindings = [], $useReadPdo = true, array $fetchUsing = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return [];
            }

            $sql = $this->substituteBindings($query, $this->prepareBindings($bindings));

            // Use native odbc_* functions to bypass Microsoft ODBC Cursor Library
            // which causes "No columns were bound" errors on HANA system views via PDO
            $config = $this->getConfig();
            $dsn    = "DRIVER={HDBODBC};ServerNode={$config['host']}:{$config['port']};DatabaseName={$config['database']}";
            $conn   = odbc_connect($dsn, $config['username'], $config['password']);

            if ($config['schema'] ?? null) {
                odbc_exec($conn, "SET SCHEMA \"{$config['schema']}\"");
            }

            $result  = odbc_exec($conn, $sql);
            $results = [];

            if ($result) {
                while ($row = odbc_fetch_array($result)) {
                    $results[] = $row;
                }
            }

            odbc_close($conn);

            return $results;
        });
    }

    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        $records = $this->select($query, $bindings, $useReadPdo);
        return array_shift($records);
    }

    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }
            $this->executeStatements($query, $this->prepareBindings($bindings));
            $this->recordsHaveBeenModified();
            return true;
        });
    }

    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }
            $rows = $this->executeStatements($query, $this->prepareBindings($bindings));
            $this->recordsHaveBeenModified($rows > 0);
            return $rows;
        });
    }
}
