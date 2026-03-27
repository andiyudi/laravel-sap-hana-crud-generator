<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::ordered()->paginate(10);
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        // Get all tables from database
        $tables = $this->getDatabaseTables();
        return view('menus.create', compact('tables'));
    }

    public function store(Request $request)
    {
        Log::info('Menu store called', ['request' => $request->all()]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'table_name' => 'required|string|max:255|unique:menus,table_name',
            'icon' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        Log::info('Validation passed', ['validated' => $validated]);

        // Convert is_active to boolean (checkbox sends "1" or nothing)
        $validated['is_active'] = $request->input('is_active', false) == '1';

        // Get table columns and create field definitions
        try {
            Log::info('Getting fields for table', ['table' => $validated['table_name']]);

            $fields = $this->getTableFields($validated['table_name']);

            Log::info('Fields retrieved', ['count' => count($fields)]);

            if (empty($fields)) {
                Log::error('No fields found', ['table' => $validated['table_name']]);
                return back()->withErrors([
                    'table_name' => "Could not read columns from table '{$validated['table_name']}'. Please make sure the table exists."
                ])->withInput();
            }

            $validated['fields'] = $fields;

            // Auto-detect relationships
            $relationships = $this->detectRelationships($validated['table_name'], $fields);
            $validated['relationships'] = $relationships;

            Log::info('Creating menu', ['data' => $validated]);

            $menu = Menu::create($validated);

            Log::info('Menu created', ['id' => $menu->id]);

            // Auto-create permissions for this menu
            $this->createMenuPermissions($menu);

            Log::info('Permissions created');

            return redirect()->route('menus.index')
                ->with('success', 'Menu created successfully with permissions.');
        } catch (\Exception $e) {
            Log::error('Menu creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'error' => 'Failed to create menu: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function show(Menu $menu)
    {
        return view('menus.show', compact('menu'));
    }

    public function edit(Menu $menu)
    {
        $tables = $this->getDatabaseTables();
        return view('menus.edit', compact('menu', 'tables'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'table_name' => 'required|string|max:255|unique:menus,table_name,' . $menu->id,
            'icon' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->input('is_active', false) == '1';

        // Update field definitions if table changed
        if ($validated['table_name'] !== $menu->table_name) {
            $fields = $this->getTableFields($validated['table_name']);
            $validated['fields'] = $fields;

            // Re-detect relationships
            $relationships = $this->detectRelationships($validated['table_name'], $fields);
            $validated['relationships'] = $relationships;
        }

        $menu->update($validated);

        return redirect()->route('menus.index')
            ->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu)
    {
        // Delete associated permissions
        $this->deleteMenuPermissions($menu);

        $menu->delete();

        return redirect()->route('menus.index')
            ->with('success', 'Menu deleted successfully.');
    }

    /**
     * Update display columns configuration
     */
    public function updateDisplayColumns(Request $request, Menu $menu)
    {
        $displayColumns = $request->input('display_columns', []);

        // Get current fields
        $fields = $menu->fields;

        // Create a map of field names to their data
        $fieldMap = [];
        foreach ($fields as $field) {
            $fieldMap[$field['name']] = $field;
        }

        // Reorder fields based on the submitted order
        $reorderedFields = [];

        // First, add selected columns in the order they were submitted
        foreach ($displayColumns as $fieldName) {
            if (isset($fieldMap[$fieldName])) {
                $fieldMap[$fieldName]['display_in_list'] = true;
                $reorderedFields[] = $fieldMap[$fieldName];
                unset($fieldMap[$fieldName]); // Remove from map
            }
        }

        // Then, add remaining fields (not selected for display)
        foreach ($fieldMap as $field) {
            $field['display_in_list'] = false;
            $reorderedFields[] = $field;
        }

        // Save updated fields
        $menu->fields = $reorderedFields;
        $menu->save();

        return redirect()->route('menus.edit', $menu)
            ->with('success', 'Display columns updated successfully.');
    }

    /**
     * Create permissions for a menu
     */
    private function createMenuPermissions(Menu $menu)
    {
        $slug = strtolower(str_replace(' ', '_', $menu->name));

        $permissions = [
            "{$slug}.view",
            "{$slug}.create",
            "{$slug}.edit",
            "{$slug}.delete",
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Assign all permissions to admin role
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }
    }

    /**
     * Delete permissions for a menu
     */
    private function deleteMenuPermissions(Menu $menu)
    {
        $slug = strtolower(str_replace(' ', '_', $menu->name));

        $permissions = [
            "{$slug}.view",
            "{$slug}.create",
            "{$slug}.edit",
            "{$slug}.delete",
        ];

        Permission::whereIn('name', $permissions)->delete();
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

        // Return table names in lowercase to match actual table names in HANA
        return collect($tables)->map(function ($table) {
            $tableName = is_array($table) ? $table['TABLE_NAME'] : $table->TABLE_NAME;
            return strtolower($tableName);
        })->toArray();
    }

    /**
     * Get table fields/columns
     */
    private function getTableFields($tableName)
    {
        try {
            // Try to get columns using Schema facade
            $columns = Schema::getColumns($tableName);

            if (empty($columns)) {
                // If Schema::getColumns returns empty, try direct query
                $schema = strtoupper(config('database.connections.hana.schema'));
                $columns = DB::select("
                    SELECT COLUMN_NAME, DATA_TYPE_NAME, IS_NULLABLE, DEFAULT_VALUE
                    FROM TABLE_COLUMNS
                    WHERE SCHEMA_NAME = '{$schema}'
                    AND TABLE_NAME = '" . strtolower($tableName) . "'
                    ORDER BY POSITION
                ");

                if (empty($columns)) {
                    Log::error("No columns found for table: {$tableName}");
                    return [];
                }

                // Convert to expected format
                $fields = [];
                foreach ($columns as $column) {
                    $colName = is_array($column) ? $column['COLUMN_NAME'] : $column->COLUMN_NAME;
                    $colType = is_array($column) ? $column['DATA_TYPE_NAME'] : $column->DATA_TYPE_NAME;
                    $nullable = is_array($column) ? $column['IS_NULLABLE'] : $column->IS_NULLABLE;
                    $default = is_array($column) ? ($column['DEFAULT_VALUE'] ?? null) : ($column->DEFAULT_VALUE ?? null);

                    // Auto-set display_in_list: true for first 6 non-system columns
                    $isSystemColumn = in_array($colName, ['id', 'created_at', 'updated_at']);
                    $displayInList = !$isSystemColumn && count(array_filter($fields, fn($f) => ($f['display_in_list'] ?? false))) < 6;

                    $fields[] = [
                        'name' => $colName,
                        'type' => $this->mapColumnType($colType, $colName),
                        'nullable' => $nullable === 'TRUE',
                        'default' => $default,
                        'display_in_list' => $displayInList,
                    ];
                }

                return $fields;
            }

            $fields = [];
            foreach ($columns as $column) {
                $fields[] = [
                    'name' => $column['name'],
                    'type' => $this->mapColumnType($column['type_name'] ?? $column['type']),
                    'nullable' => $column['nullable'] ?? false,
                    'default' => $column['default'] ?? null,
                ];
            }

            return $fields;
        } catch (\Exception $e) {
            Log::error("Error getting table fields for {$tableName}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Map database column type to form input type
     */
    private function mapColumnType($dbType, $columnName = '')
    {
        $dbType = strtolower($dbType);
        $columnName = strtolower($columnName);

        // Check for file/image fields by name pattern FIRST (before other checks)
        // This is important because file/image columns are stored as VARCHAR in database
        if (str_contains($columnName, 'image') || str_contains($columnName, 'photo') || str_contains($columnName, 'picture') || str_contains($columnName, 'avatar') || str_contains($columnName, 'icon') || str_contains($columnName, 'logo') || str_contains($columnName, 'thumbnail')) {
            return 'image';
        }
        if (str_contains($columnName, 'file') || str_contains($columnName, 'document') || str_contains($columnName, 'attachment') || str_contains($columnName, 'pdf') || str_contains($columnName, 'upload')) {
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
     * Auto-detect relationships based on foreign key naming convention
     */
    private function detectRelationships($tableName, $fields)
    {
        $relationships = [];
        $allTables = $this->getDatabaseTables();

        foreach ($fields as $field) {
            $fieldName = $field['name'];

            // Check if field ends with _id (foreign key convention)
            if (str_ends_with($fieldName, '_id') && $fieldName !== 'id') {
                // Extract related table name (e.g., user_id -> users)
                $relatedTable = $this->guessRelatedTable($fieldName, $allTables);

                if ($relatedTable) {
                    // Get display column from related table
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
     * Guess related table name from foreign key
     */
    private function guessRelatedTable($foreignKey, $allTables)
    {
        // Remove _id suffix
        $baseName = str_replace('_id', '', $foreignKey);

        // Try plural form first (user_id -> users)
        $pluralTable = $baseName . 's';
        if (in_array($pluralTable, $allTables)) {
            return $pluralTable;
        }

        // Try singular form (category_id -> category)
        if (in_array($baseName, $allTables)) {
            return $baseName;
        }

        // Try with 'ies' ending (category_id -> categories)
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

        // Priority order for display columns
        $preferredColumns = ['name', 'title', 'label', 'email', 'username', 'code'];

        foreach ($preferredColumns as $preferred) {
            foreach ($fields as $field) {
                if (strtolower($field['name']) === $preferred) {
                    return $field['name'];
                }
            }
        }

        // If no preferred column found, use second column (skip id)
        if (count($fields) > 1) {
            return $fields[1]['name'];
        }

        // Fallback to id
        return 'id';
    }
}
