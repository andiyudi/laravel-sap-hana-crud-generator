<?php

namespace Custom\LaravelHana;

use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;

class HanaSchemaGrammar extends Grammar
{
    /**
     * The possible column modifiers.
     *
     * @var string[]
     */
    protected $modifiers = ['Nullable', 'Default', 'Increment'];

    /**
     * The possible column serials.
     *
     * @var string[]
     */
    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];

    /**
     * Compile the query to determine the tables.
     *
     * @param  string|string[]|null  $schema
     * @return string
     */
    public function compileTables($schema): string
    {
        $schema = is_array($schema) ? implode("','", $schema) : $schema;

        return "SELECT TABLE_NAME AS \"name\", SCHEMA_NAME AS \"schema\", 'BASE TABLE' AS \"type\", "
            . "CAST(0 AS BIGINT) AS \"size\", COMMENTS AS \"comment\" "
            . "FROM TABLES "
            . "WHERE SCHEMA_NAME = '{$schema}' "
            . "ORDER BY TABLE_NAME";
    }

    /**
     * Compile the query to determine the columns.
     *
     * @param  string|null  $schema
     * @param  string  $table
     * @return string
     */
    public function compileColumns($schema, $table): string
    {
        return "SELECT COLUMN_NAME AS \"name\", DATA_TYPE_NAME AS \"type_name\", "
            . "LENGTH AS \"length\", SCALE AS \"scale\", "
            . "IS_NULLABLE AS \"nullable\", DEFAULT_VALUE AS \"default\", "
            . "COMMENTS AS \"comment\", GENERATION_TYPE AS \"extra\", "
            . "POSITION AS \"position\" "
            . "FROM TABLE_COLUMNS "
            . "WHERE SCHEMA_NAME = '{$schema}' AND TABLE_NAME = '{$table}' "
            . "ORDER BY POSITION";
    }

    /**
     * Compile a primary key command.
     */
    public function compilePrimary(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s PRIMARY KEY (%s)',
            $this->wrapTable($blueprint),
            $this->wrap($command->index),
            $this->columnize($command->columns)
        );
    }

    /**
     * Compile a plain index key command.
     */
    public function compileIndex(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'CREATE INDEX %s ON %s (%s)',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $this->columnize($command->columns)
        );
    }

    /**
     * Compile a unique key command.
     */
    public function compileUnique(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s UNIQUE (%s)',
            $this->wrapTable($blueprint),
            $this->wrap($command->index),
            $this->columnize($command->columns)
        );
    }

    /**
     * Compile a create table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileCreate(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'CREATE COLUMN TABLE %s (%s)',
            $this->wrapTable($blueprint),
            implode(', ', $this->getColumns($blueprint))
        );
    }

    /**
     * Compile a drop table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDrop(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return 'DROP TABLE ' . $this->wrapTable($blueprint);
    }

    /**
     * Compile a drop table (if exists) command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDropIfExists(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $command): string
    {
        return 'DROP TABLE IF EXISTS ' . $this->wrapTable($blueprint);
    }

    /**
     * Create the column definition for a medium text type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMediumText(Fluent $column): string
    {
        return 'NCLOB';
    }

    /**
     * Create the column definition for a long text type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeLongText(Fluent $column): string
    {
        return 'NCLOB';
    }

    /**
     * Create the column definition for a tiny integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTinyInteger(Fluent $column): string
    {
        return 'TINYINT';
    }

    /**
     * Create the column definition for a small integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeSmallInteger(Fluent $column): string
    {
        return 'SMALLINT';
    }

    /**
     * Create the column definition for a medium integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMediumInteger(Fluent $column): string
    {
        return 'INTEGER';
    }

    /**
     * Create the column definition for a float type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeFloat(Fluent $column): string
    {
        return 'FLOAT';
    }

    /**
     * Create the column definition for a double type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDouble(Fluent $column): string
    {
        return 'DOUBLE';
    }

    /**
     * Create the column definition for a date type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDate(Fluent $column): string
    {
        return 'DATE';
    }

    /**
     * Create the column definition for a time type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTime(Fluent $column): string
    {
        return 'TIME';
    }

    /**
     * Create the column definition for a dateTime type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDateTime(Fluent $column): string
    {
        return 'TIMESTAMP';
    }

    /**
     * Create the column definition for a dateTimeTz type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDateTimeTz(Fluent $column): string
    {
        return 'TIMESTAMP';
    }

    /**
     * Create the column definition for a timestampTz type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTimestampTz(Fluent $column): string
    {
        return 'TIMESTAMP';
    }

    /**
     * Create the column definition for a uuid type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeUuid(Fluent $column): string
    {
        return 'NVARCHAR(36)';
    }

    /**
     * Create the column definition for a json type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeJson(Fluent $column): string
    {
        return 'NCLOB';
    }

    /**
     * Create the column definition for a jsonb type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeJsonb(Fluent $column): string
    {
        return 'NCLOB';
    }

    /**
     * Create the column definition for a binary type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeBinary(Fluent $column): string
    {
        return 'BLOB';
    }

    /**
     * Create the column definition for a char type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeChar(Fluent $column): string
    {
        return "NCHAR({$column->length})";
    }

    /**
     * Create the column definition for a big integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeBigInteger(Fluent $column): string
    {
        return 'BIGINT';
    }

    /**
     * Create the column definition for an integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeInteger(Fluent $column): string
    {
        return 'INTEGER';
    }

    /**
     * Create the column definition for a string type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeString(Fluent $column): string
    {
        return "NVARCHAR({$column->length})";
    }

    /**
     * Create the column definition for a text type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeText(Fluent $column): string
    {
        return 'NCLOB';
    }

    /**
     * Create the column definition for a decimal type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDecimal(Fluent $column): string
    {
        return "DECIMAL({$column->total}, {$column->places})";
    }

    /**
     * Create the column definition for a boolean type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeBoolean(Fluent $column): string
    {
        return 'TINYINT';
    }

    /**
     * Create the column definition for a timestamp type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTimestamp(Fluent $column): string
    {
        return 'TIMESTAMP';
    }

    /**
     * Get the SQL for a nullable column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $column
     * @return string|null
     */
    protected function modifyNullable(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $column): ?string
    {
        return $column->nullable ? ' NULL' : ' NOT NULL';
    }

    /**
     * Get the SQL for a default column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $column
     * @return string|null
     */
    protected function modifyDefault(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $column): ?string
    {
        if (! is_null($column->default)) {
            return ' DEFAULT ' . $this->getDefaultValue($column->default);
        }

        return null;
    }

    /**
     * Get the SQL for an auto-increment column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $column
     * @return string|null
     */
    protected function modifyIncrement(\Illuminate\Database\Schema\Blueprint $blueprint, Fluent $column): ?string
    {
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return ' GENERATED ALWAYS AS IDENTITY PRIMARY KEY';
        }

        return null;
    }
}
