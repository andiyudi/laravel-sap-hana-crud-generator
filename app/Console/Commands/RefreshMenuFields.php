<?php

namespace App\Console\Commands;

use App\Models\Menu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshMenuFields extends Command
{
    protected $signature = 'menu:refresh-fields {menu_id}';
    protected $description = 'Refresh field definitions for a menu from database table';

    public function handle()
    {
        $menuId = $this->argument('menu_id');
        $menu = Menu::find($menuId);

        if (!$menu) {
            $this->error("Menu with ID {$menuId} not found!");
            return 1;
        }

        $this->info("Refreshing fields for menu: {$menu->name}");
        $this->info("Table: {$menu->table_name}");
        $this->line("");

        try {
            // Get fresh fields from database
            $fields = $this->getTableFields($menu->table_name);

            if (empty($fields)) {
                $this->error("No fields found for table {$menu->table_name}");
                return 1;
            }

            // Update menu
            $menu->fields = $fields;

            // Re-detect relationships
            $relationships = $this->detectRelationships($menu->table_name, $fields);
            $menu->relationships = $relationships;

            $menu->save();

            $this->info("✓ Fields refreshed successfully!");
            $this->line("");

            // Show updated fields
            $this->info("Updated field definitions:");
            $this->table(
                ['Field Name', 'Type', 'Nullable', 'Default'],
                collect($fields)->map(fn($f) => [
                    $f['name'],
                    $f['type'],
                    $f['nullable'] ? 'Yes' : 'No',
                    $f['default'] ?? '-'
                ])
            );

            if (!empty($relationships)) {
                $this->line("");
                $this->info("Detected relationships:");
                $this->table(
                    ['Foreign Key', 'Related Table', 'Display Column'],
                    collect($relationships)->map(fn($r) => [
                        $r['foreign_key'],
                        $r['related_table'],
                        $r['display_column']
                    ])
                );
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    private function getTableFields($tableName)
    {
        try {
            $schema = strtoupper(config('database.connections.hana.schema'));
            $columns = DB::select("
                SELECT COLUMN_NAME, DATA_TYPE_NAME, IS_NULLABLE, DEFAULT_VALUE
                FROM TABLE_COLUMNS
                WHERE SCHEMA_NAME = '{$schema}'
                AND TABLE_NAME = '" . strtolower($tableName) . "'
                ORDER BY POSITION
            ");

            if (empty($columns)) {
                return [];
            }

            $fields = [];
            foreach ($columns as $column) {
                $colName = is_array($column) ? $column['COLUMN_NAME'] : $column->COLUMN_NAME;
                $colType = is_array($column) ? $column['DATA_TYPE_NAME'] : $column->DATA_TYPE_NAME;
                $nullable = is_array($column) ? $column['IS_NULLABLE'] : $column->IS_NULLABLE;
                $default = is_array($column) ? ($column['DEFAULT_VALUE'] ?? null) : ($column->DEFAULT_VALUE ?? null);

                $fields[] = [
                    'name' => $colName,
                    'type' => $this->mapColumnType($colType, $colName),
                    'nullable' => $nullable === 'TRUE',
                    'default' => $default,
                ];
            }

            return $fields;
        } catch (\Exception $e) {
            $this->error("Error getting table fields: " . $e->getMessage());
            return [];
        }
    }

    private function mapColumnType($dbType, $columnName = '')
    {
        $dbType = strtolower($dbType);
        $columnName = strtolower($columnName);

        // Check for file/image fields by name pattern FIRST
        if (
            str_contains($columnName, 'image') || str_contains($columnName, 'photo') ||
            str_contains($columnName, 'picture') || str_contains($columnName, 'avatar') ||
            str_contains($columnName, 'icon') || str_contains($columnName, 'logo') ||
            str_contains($columnName, 'thumbnail')
        ) {
            return 'image';
        }
        if (
            str_contains($columnName, 'file') || str_contains($columnName, 'document') ||
            str_contains($columnName, 'attachment') || str_contains($columnName, 'pdf') ||
            str_contains($columnName, 'upload')
        ) {
            return 'file';
        }

        // Check if it's a boolean field by name pattern
        $booleanPatterns = ['is_', 'has_', 'can_', 'should_', 'active', 'enabled', 'disabled', 'published'];
        foreach ($booleanPatterns as $pattern) {
            if (str_contains($columnName, $pattern)) {
                return 'checkbox';
            }
        }

        $typeMap = [
            'int' => 'number',
            'integer' => 'number',
            'bigint' => 'number',
            'smallint' => 'number',
            'tinyint' => 'number',
            'decimal' => 'number',
            'float' => 'number',
            'double' => 'number',
            'varchar' => 'text',
            'nvarchar' => 'text',
            'char' => 'text',
            'nchar' => 'text',
            'text' => 'textarea',
            'nclob' => 'textarea',
            'date' => 'date',
            'datetime' => 'datetime-local',
            'timestamp' => 'datetime-local',
            'time' => 'time',
            'boolean' => 'checkbox',
        ];

        foreach ($typeMap as $key => $value) {
            if (str_contains($dbType, $key)) {
                return $value;
            }
        }

        return 'text';
    }

    private function detectRelationships($tableName, $fields)
    {
        $relationships = [];
        $allTables = $this->getDatabaseTables();

        foreach ($fields as $field) {
            $fieldName = $field['name'];

            if (str_ends_with($fieldName, '_id') && $fieldName !== 'id') {
                $relatedTable = $this->guessRelatedTable($fieldName, $allTables);

                if ($relatedTable) {
                    $displayColumn = $this->guessDisplayColumn($relatedTable);

                    $relationships[] = [
                        'foreign_key' => $fieldName,
                        'related_table' => $relatedTable,
                        'display_column' => $displayColumn,
                        'type' => 'belongsTo',
                    ];
                }
            }
        }

        return $relationships;
    }

    private function getDatabaseTables()
    {
        $schema = strtoupper(config('database.connections.hana.schema'));
        $tables = DB::select(
            "SELECT TABLE_NAME FROM TABLES WHERE SCHEMA_NAME = '{$schema}' AND TABLE_TYPE = 'COLUMN' ORDER BY TABLE_NAME"
        );

        return collect($tables)->map(function ($table) {
            return strtolower(is_array($table) ? $table['TABLE_NAME'] : $table->TABLE_NAME);
        })->toArray();
    }

    private function guessRelatedTable($foreignKey, $allTables)
    {
        $baseName = str_replace('_id', '', $foreignKey);
        $pluralTable = $baseName . 's';

        if (in_array($pluralTable, $allTables)) {
            return $pluralTable;
        }
        if (in_array($baseName, $allTables)) {
            return $baseName;
        }
        if (str_ends_with($baseName, 'y')) {
            $iesTable = substr($baseName, 0, -1) . 'ies';
            if (in_array($iesTable, $allTables)) {
                return $iesTable;
            }
        }

        return null;
    }

    private function guessDisplayColumn($tableName)
    {
        $fields = $this->getTableFields($tableName);
        $preferredColumns = ['name', 'title', 'label', 'email', 'username', 'code'];

        foreach ($preferredColumns as $preferred) {
            foreach ($fields as $field) {
                if (strtolower($field['name']) === $preferred) {
                    return $field['name'];
                }
            }
        }

        if (count($fields) > 1) {
            return $fields[1]['name'];
        }

        return 'id';
    }
}
