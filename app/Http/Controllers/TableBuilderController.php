<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TableBuilderController extends Controller
{
    public function index()
    {
        return view('tables.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_name' => 'required|string|max:255|regex:/^[a-z_]+$/',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:255|regex:/^[a-z_]+$/',
            'fields.*.type' => 'required|string|in:string,text,integer,decimal,boolean,date,datetime,timestamp,file,image',
            'fields.*.length' => 'nullable|integer|min:1',
            'fields.*.nullable' => 'boolean',
            'fields.*.unique' => 'boolean',
            'fields.*.default' => 'nullable|string',
        ]);

        $tableName = strtolower($validated['table_name']);

        // Check if table already exists
        if (Schema::hasTable($tableName)) {
            return back()->withErrors(['table_name' => 'Table already exists.'])->withInput();
        }

        try {
            Schema::create($tableName, function (Blueprint $table) use ($validated) {
                $table->id();

                foreach ($validated['fields'] as $field) {
                    $column = null;
                    $fieldName = $field['name'];
                    $fieldType = $field['type'];
                    $length = $field['length'] ?? null;
                    $nullable = $field['nullable'] ?? false;
                    $unique = $field['unique'] ?? false;
                    $default = $field['default'] ?? null;

                    // Create column based on type
                    switch ($fieldType) {
                        case 'string':
                            $column = $table->string($fieldName, $length ?? 255);
                            break;
                        case 'text':
                            $column = $table->text($fieldName);
                            break;
                        case 'integer':
                            $column = $table->integer($fieldName);
                            break;
                        case 'decimal':
                            $column = $table->decimal($fieldName, 10, 2);
                            break;
                        case 'boolean':
                            $column = $table->boolean($fieldName);
                            break;
                        case 'date':
                            $column = $table->date($fieldName);
                            break;
                        case 'datetime':
                            $column = $table->dateTime($fieldName);
                            break;
                        case 'timestamp':
                            $column = $table->timestamp($fieldName);
                            break;
                        case 'file':
                        case 'image':
                            // Store file path as string
                            $column = $table->string($fieldName, 500);
                            break;
                    }

                    // Apply modifiers
                    if ($column) {
                        if ($nullable) {
                            $column->nullable();
                        }
                        if ($unique) {
                            $column->unique();
                        }
                        if ($default !== null && $default !== '') {
                            if ($fieldType === 'boolean') {
                                $column->default($default === 'true' || $default === '1');
                            } elseif (in_array($fieldType, ['date', 'datetime', 'timestamp'])) {
                                // Check if it's a database function
                                $upperDefault = strtoupper(trim($default));
                                if (in_array($upperDefault, ['CURRENT_DATE', 'CURRENT_TIMESTAMP', 'NOW()'])) {
                                    // Use raw expression for database functions
                                    $column->default(DB::raw($upperDefault));
                                } else {
                                    // Use literal value
                                    $column->default($default);
                                }
                            } else {
                                $column->default($default);
                            }
                        }
                    }
                }

                $table->timestamps();
            });

            return redirect()->route('menus.create')
                ->with('success', "Table '{$tableName}' created successfully! You can now create a menu for it.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create table: ' . $e->getMessage()])->withInput();
        }
    }
}
