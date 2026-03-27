<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TableColumnController extends Controller
{
    /**
     * Show form to add column to existing table
     */
    public function create()
    {
        // Get all tables
        $tables = $this->getDatabaseTables();
        return view('tables.add-column', compact('tables'));
    }

    /**
     * Add column to existing table
     */
    public function store(Request $request)
    {
        Log::info('=== ADD COLUMN START ===', ['request' => $request->all()]);

        $validated = $request->validate([
            'table_name' => 'required|string',
            'column_name' => 'required|string|max:255|regex:/^[a-z_]+$/',
            'column_type' => 'required|string|in:string,text,integer,decimal,boolean,date,datetime,timestamp,file,image',
            'length' => 'nullable|integer|min:1',
            'nullable' => 'nullable|boolean',
            'unique' => 'nullable|boolean',
            'default' => 'nullable|string',
        ]);

        Log::info('Validation passed', $validated);

        $tableName = strtolower($validated['table_name']);
        $columnName = $validated['column_name'];
        $columnType = $validated['column_type'];

        // Handle checkbox values (checkbox sends "1" or nothing)
        $nullable = $request->has('nullable');
        $unique = $request->has('unique');

        Log::info('Checkboxes', ['nullable' => $nullable, 'unique' => $unique]);

        // Check if table exists using direct SQL (Schema::hasTable doesn't work reliably with HANA)
        $schema = strtoupper(config('database.connections.hana.schema'));
        $tableExists = DB::select("SELECT COUNT(*) as count FROM TABLES WHERE SCHEMA_NAME = '{$schema}' AND TABLE_NAME = '{$tableName}'");

        // HANA returns uppercase column names, handle both cases
        $firstResult = $tableExists[0];
        if (is_array($firstResult)) {
            $tableCount = $firstResult['COUNT'] ?? $firstResult['count'] ?? 0;
        } else {
            $tableCount = $firstResult->COUNT ?? $firstResult->count ?? 0;
        }

        if ($tableCount == 0) {
            Log::error('Table does not exist', ['table' => $tableName]);
            return back()->withErrors(['table_name' => 'Table does not exist.'])->withInput();
        }

        // Check if column already exists using direct SQL (Schema::hasColumn doesn't work reliably with HANA)
        $columnExists = DB::select("SELECT COUNT(*) as count FROM TABLE_COLUMNS WHERE SCHEMA_NAME = '{$schema}' AND TABLE_NAME = '{$tableName}' AND COLUMN_NAME = '{$columnName}'");

        // HANA returns uppercase column names, handle both cases
        $firstResult = $columnExists[0];
        if (is_array($firstResult)) {
            $columnCount = $firstResult['COUNT'] ?? $firstResult['count'] ?? 0;
        } else {
            $columnCount = $firstResult->COUNT ?? $firstResult->count ?? 0;
        }

        if ($columnCount > 0) {
            Log::error('Column already exists', ['table' => $tableName, 'column' => $columnName]);
            return back()->withErrors(['column_name' => 'Column already exists in this table.'])->withInput();
        }

        try {
            // Build SQL for HANA ALTER TABLE
            $length = $validated['length'] ?? null;
            $default = $validated['default'] ?? null;

            // IMPORTANT: HANA doesn't allow adding NOT NULL columns to tables with data
            // If table has data and column is NOT NULL without default, force nullable
            // Use direct SQL query instead of exists() which doesn't work with HANA
            $countResult = DB::select("SELECT COUNT(*) as count FROM \"{$schema}\".\"{$tableName}\"");
            $firstResult = $countResult[0];
            if (is_array($firstResult)) {
                $rowCount = $firstResult['COUNT'] ?? $firstResult['count'] ?? 0;
            } else {
                $rowCount = $firstResult->COUNT ?? $firstResult->count ?? 0;
            }
            $tableHasData = $rowCount > 0;
            Log::info('Table has data check', ['has_data' => $tableHasData, 'row_count' => $rowCount]);

            if ($tableHasData && !$nullable && ($default === null || $default === '')) {
                // Force nullable for tables with existing data
                $nullable = true;
                Log::info('Forced nullable=true');
            }

            // Map column type to HANA SQL type
            $sqlType = match ($columnType) {
                'string' => 'NVARCHAR(' . ($length ?? 255) . ')',
                'text' => 'NCLOB',
                'integer' => 'INTEGER',
                'decimal' => 'DECIMAL(10,2)',
                'boolean' => 'TINYINT',
                'date' => 'DATE',
                'datetime' => 'TIMESTAMP',
                'timestamp' => 'TIMESTAMP',
                'file', 'image' => 'NVARCHAR(500)',
                default => 'NVARCHAR(255)',
            };

            // Build ALTER TABLE statement
            $sql = "ALTER TABLE \"{$schema}\".\"{$tableName}\" ADD (\"{$columnName}\" {$sqlType}";

            // Add NULL/NOT NULL
            if ($nullable) {
                $sql .= " NULL";
            } else {
                // Only add NOT NULL if we have a default value
                if ($default !== null && $default !== '') {
                    $sql .= " NOT NULL";
                } else {
                    // Force NULL if no default
                    $sql .= " NULL";
                }
            }

            // Add DEFAULT
            if ($default !== null && $default !== '') {
                if ($columnType === 'boolean') {
                    $sql .= " DEFAULT " . (($default === 'true' || $default === '1') ? '1' : '0');
                } elseif (in_array($columnType, ['string', 'text'])) {
                    $sql .= " DEFAULT '" . str_replace("'", "''", $default) . "'";
                } elseif (in_array($columnType, ['date', 'datetime', 'timestamp'])) {
                    // Check if it's a database function (CURRENT_DATE, CURRENT_TIMESTAMP, NOW())
                    $upperDefault = strtoupper(trim($default));
                    if (in_array($upperDefault, ['CURRENT_DATE', 'CURRENT_TIMESTAMP', 'NOW()'])) {
                        $sql .= " DEFAULT {$upperDefault}";
                    } else {
                        // It's a literal date/datetime value
                        $sql .= " DEFAULT '{$default}'";
                    }
                } else {
                    $sql .= " DEFAULT {$default}";
                }
            }

            $sql .= ")";

            Log::info('Executing SQL', ['sql' => $sql]);

            // Execute ALTER TABLE
            DB::statement($sql);

            Log::info('SQL executed successfully');

            // Add UNIQUE constraint if needed
            if ($unique) {
                $constraintName = "uq_{$tableName}_{$columnName}";
                DB::statement("ALTER TABLE \"{$schema}\".\"{$tableName}\" ADD CONSTRAINT \"{$constraintName}\" UNIQUE (\"{$columnName}\")");
                Log::info('Unique constraint added');
            }

            // Auto-refresh menu if exists for this table
            Log::info('Auto-refreshing menu for table', ['table' => $tableName]);
            $this->autoRefreshMenu($tableName);

            Log::info('=== ADD COLUMN SUCCESS ===');

            return redirect()->route('tables.add-column')
                ->with('success', "Column '{$columnName}' added to table '{$tableName}' successfully! Menu has been automatically refreshed.");
        } catch (\Exception $e) {
            Log::error('=== ADD COLUMN FAILED ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to add column: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Get all database tables
     */
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

    /**
     * Get columns for a table (AJAX)
     */
    public function getTableColumns(Request $request)
    {
        $tableName = $request->input('table');

        if (!$tableName) {
            return response()->json(['columns' => []]);
        }

        try {
            // Get columns directly from HANA
            $schema = strtoupper(config('database.connections.hana.schema'));
            $columns = DB::select("
                SELECT COLUMN_NAME
                FROM TABLE_COLUMNS
                WHERE SCHEMA_NAME = '{$schema}'
                AND TABLE_NAME = '" . strtolower($tableName) . "'
                ORDER BY POSITION
            ");

            $columnNames = [];
            foreach ($columns as $column) {
                $colName = is_array($column) ? $column['COLUMN_NAME'] : $column->COLUMN_NAME;
                // Skip system columns
                if (!in_array($colName, ['id', 'created_at', 'updated_at'])) {
                    $columnNames[] = $colName;
                }
            }

            return response()->json(['columns' => $columnNames]);
        } catch (\Exception $e) {
            return response()->json(['columns' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * Auto-refresh menu field definitions after adding column
     */
    private function autoRefreshMenu($tableName)
    {
        try {
            Log::info('autoRefreshMenu: Starting', ['table' => $tableName]);

            // Find menu for this table
            $menu = \App\Models\Menu::where('table_name', $tableName)->first();

            if (!$menu) {
                Log::info('autoRefreshMenu: No menu found for table', ['table' => $tableName]);
                return; // No menu exists for this table
            }

            Log::info('autoRefreshMenu: Menu found', ['menu_id' => $menu->id, 'menu_name' => $menu->name]);

            // Get fresh fields from database
            $fields = $this->getTableFields($tableName);

            if (empty($fields)) {
                Log::warning('autoRefreshMenu: No fields retrieved from database', ['table' => $tableName]);
                return;
            }

            Log::info('autoRefreshMenu: Fields retrieved', ['count' => count($fields), 'fields' => $fields]);

            // Update menu fields
            $menu->fields = $fields;

            // Re-detect relationships
            $relationships = $this->detectRelationships($tableName, $fields);
            $menu->relationships = $relationships;

            Log::info('autoRefreshMenu: Saving menu', ['fields_count' => count($fields), 'relationships_count' => count($relationships)]);

            $menu->save();

            Log::info('autoRefreshMenu: Menu saved successfully', ['menu_id' => $menu->id]);
        } catch (\Exception $e) {
            // Silent fail - don't break the add column process
            Log::warning("Failed to auto-refresh menu for table {$tableName}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get table fields from database
     */
    private function getTableFields($tableName)
    {
        try {
            $schema = strtoupper(config('database.connections.hana.schema'));
            Log::info('getTableFields: Querying database', ['schema' => $schema, 'table' => $tableName]);

            $columns = DB::select("
                SELECT COLUMN_NAME, DATA_TYPE_NAME, IS_NULLABLE, DEFAULT_VALUE
                FROM TABLE_COLUMNS
                WHERE SCHEMA_NAME = '{$schema}'
                AND TABLE_NAME = '" . strtolower($tableName) . "'
                ORDER BY POSITION
            ");

            Log::info('getTableFields: Query result', ['columns_count' => count($columns)]);

            if (empty($columns)) {
                Log::warning('getTableFields: No columns found', ['table' => $tableName]);
                return [];
            }

            $fields = [];
            foreach ($columns as $column) {
                $colName = is_array($column) ? $column['COLUMN_NAME'] : $column->COLUMN_NAME;
                $colType = is_array($column) ? $column['DATA_TYPE_NAME'] : $column->DATA_TYPE_NAME;
                $nullable = is_array($column) ? $column['IS_NULLABLE'] : $column->IS_NULLABLE;
                $default = is_array($column) ? ($column['DEFAULT_VALUE'] ?? null) : ($column->DEFAULT_VALUE ?? null);

                $mappedType = $this->mapColumnTypeForMenu($colType, $colName);

                // Auto-set display_in_list: true for first 6 non-system columns
                $isSystemColumn = in_array($colName, ['id', 'created_at', 'updated_at']);
                $displayInList = !$isSystemColumn && count(array_filter($fields, fn($f) => ($f['display_in_list'] ?? false))) < 6;

                $fields[] = [
                    'name' => $colName,
                    'type' => $mappedType,
                    'nullable' => $nullable === 'TRUE',
                    'default' => $default,
                    'display_in_list' => $displayInList,
                ];

                Log::info('getTableFields: Mapped column', [
                    'name' => $colName,
                    'db_type' => $colType,
                    'mapped_type' => $mappedType,
                    'display_in_list' => $displayInList
                ]);
            }

            Log::info('getTableFields: Completed', ['fields_count' => count($fields)]);
            return $fields;
        } catch (\Exception $e) {
            Log::error('getTableFields: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Map database column type to form input type
     */
    private function mapColumnTypeForMenu($dbType, $columnName = '')
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

    /**
     * Detect relationships from fields
     */
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

    /**
     * Guess related table from foreign key
     */
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

    /**
     * Guess display column for related table
     */
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
