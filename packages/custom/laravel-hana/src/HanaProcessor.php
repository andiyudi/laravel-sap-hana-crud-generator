<?php

namespace Custom\LaravelHana;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor;

class HanaProcessor extends Processor
{
    /**
     * Process an "insert get ID" query.
     * HANA ODBC does not support lastInsertId(), so we use CURRENT_IDENTITY_VALUE().
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $sql
     * @param  array   $values
     * @param  string|null  $sequence
     * @return int
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);

        /** @var \Custom\LaravelHana\HanaConnection $connection */
        $connection = $query->getConnection();
        $pdo  = $connection->getPdo();
        $stmt = $pdo->query('SELECT CURRENT_IDENTITY_VALUE() AS "id" FROM DUMMY');
        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);

        $id = $row['id'] ?? 0;

        return is_numeric($id) ? (int) $id : $id;
    }
}
