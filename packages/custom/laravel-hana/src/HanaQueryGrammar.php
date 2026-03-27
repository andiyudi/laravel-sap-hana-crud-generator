<?php

namespace Custom\LaravelHana;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;

class HanaQueryGrammar extends Grammar
{
    /**
     * Compile an insert statement into SQL.
     * HANA does not support multi-row VALUES syntax, so we split into individual INSERTs
     * joined by a special delimiter — HanaConnection handles execution.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @return string
     */
    public function compileInsert(Builder $query, array $values): string
    {
        $table = $this->wrapTable($query->from);

        if (empty($values)) {
            return "INSERT INTO {$table} DEFAULT VALUES";
        }

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));
        $sqls    = [];

        foreach ($values as $record) {
            $params = array_fill(0, count($record), '?');
            $sqls[] = "INSERT INTO {$table} ({$columns}) VALUES (" . implode(', ', $params) . ")";
        }

        // Use a unique delimiter that won't appear in data
        return implode('/*HANA_STMT*/', $sqls);
    }

    /**
     * Compile a select query into SQL.
     */
    public function compileSelect(Builder $query): string
    {
        return parent::compileSelect($query);
    }

    /**
     * Compile the "limit" portions of the query.
     */
    protected function compileLimit(Builder $query, $limit): string
    {
        return "LIMIT {$limit}";
    }

    /**
     * Compile the "offset" portions of the query.
     */
    protected function compileOffset(Builder $query, $offset): string
    {
        return "OFFSET {$offset}";
    }
}
