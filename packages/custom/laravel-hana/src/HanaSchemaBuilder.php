<?php

namespace Custom\LaravelHana;

use Illuminate\Database\Schema\Builder;
use Custom\LaravelHana\HanaConnection;

/**
 * SAP HANA Schema Builder
 *
 * Extends Laravel's Schema Builder to provide SAP HANA specific functionality.
 *
 * @property \Custom\LaravelHana\HanaConnection $connection The database connection instance
 */
class HanaSchemaBuilder extends Builder
{
    /**
     * Drop all tables from the schema.
     *
     * @return void
     */
    public function dropAllTables(): void
    {
        /** @var HanaConnection $connection */
        $connection = $this->connection;
        $schema = strtoupper($connection->getConfig('schema'));

        // Get all tables from the schema
        // HANA uses TABLE_TYPE = 'COLUMN' for column store tables (default in Laravel migrations)
        $rows = $connection->select(
            "SELECT TABLE_NAME FROM TABLES WHERE SCHEMA_NAME = '{$schema}' ORDER BY TABLE_NAME"
        );

        foreach ($rows as $row) {
            $name = $row['TABLE_NAME']; // Keep original case from database
            try {
                // Use statement() method which properly handles HANA queries
                // Don't convert to uppercase - use the exact name from database
                $connection->statement("DROP TABLE \"{$schema}\".\"{$name}\" CASCADE");
            } catch (\Exception) {
                // Ignore if table doesn't exist or can't be dropped
            }
        }
    }

    /**
     * Get the tables for the database.
     * Uses HanaConnection::select() which handles ODBC fetch issues.
     *
     * @param  bool  $withSize
     * @return array<int, array<string, mixed>>
     */
    public function getTables($withSize = false): array
    {
        /** @var HanaConnection $connection */
        $connection = $this->connection;
        $schema = strtoupper($connection->getConfig('schema'));

        $rows = $connection->select(
            "SELECT TABLE_NAME FROM TABLES WHERE SCHEMA_NAME = '{$schema}' ORDER BY TABLE_NAME"
        );

        return array_map(fn($row) => [
            'name'    => $row['TABLE_NAME'],
            'schema'  => $schema,
            'type'    => 'BASE TABLE',
            'size'    => 0,
            'comment' => '',
        ], $rows);
    }

    /**
     * Determine if the given table exists.
     *
     * @param  string  $table
     * @return bool
     */
    public function hasTable($table): bool
    {
        /** @var HanaConnection $connection */
        $connection = $this->connection;
        $schema = strtoupper($connection->getConfig('schema'));
        $table  = strtoupper($table);

        $rows = $connection->select(
            "SELECT COUNT(*) AS \"CNT\" FROM TABLES WHERE SCHEMA_NAME = '{$schema}' AND UPPER(TABLE_NAME) = '{$table}'"
        );

        return isset($rows[0]['CNT']) && (int) $rows[0]['CNT'] > 0;
    }
}
