<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Exports\DynamicExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DynamicCrudController extends Controller
{
    public function index(Request $request, $menuId)
    {
        $menu = Menu::findOrFail($menuId);

        // Get data with joins for related tables
        $query = DB::table($menu->table_name);

        // Add joins for relationships
        $relationships = $menu->getRelationships();
        foreach ($relationships as $rel) {
            $query->leftJoin(
                $rel['related_table'],
                $menu->table_name . '.' . $rel['foreign_key'],
                '=',
                $rel['related_table'] . '.id'
            );
            // Select related display column with alias
            $query->addSelect($rel['related_table'] . '.' . $rel['display_column'] . ' as ' . $rel['foreign_key'] . '_display');
        }

        // Select all columns from main table
        $query->addSelect($menu->table_name . '.*');

        // Search functionality (case-insensitive)
        $search = $request->input('search');
        if ($search) {
            $fields = $menu->getFieldDefinitions();
            $query->where(function ($q) use ($search, $fields, $menu, $relationships) {
                // Search in main table fields
                foreach ($fields as $field) {
                    if (
                        !in_array($field['name'], ['id', 'created_at', 'updated_at']) &&
                        in_array($field['type'], ['text', 'textarea', 'number'])
                    ) {
                        $q->orWhereRaw("LOWER(\"{$menu->table_name}\".\"{$field['name']}\") LIKE ?", ['%' . strtolower($search) . '%']);
                    }
                }

                // Also search in related table display columns
                foreach ($relationships as $rel) {
                    $q->orWhereRaw("LOWER(\"{$rel['related_table']}\".\"{$rel['display_column']}\") LIKE ?", ['%' . strtolower($search) . '%']);
                }
            });
        }

        // Column filters
        $filters = $request->input('filter', []);
        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                $query->where($menu->table_name . '.' . $column, $value);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($menu->table_name . '.' . $sortBy, $sortOrder);

        $data = $query->paginate(10)->appends($request->query());

        return view('dynamic.index', compact('menu', 'data'));
    }

    public function create($menuId)
    {
        $menu = Menu::findOrFail($menuId);

        // Get related data for foreign keys
        $relatedData = $this->getRelatedData($menu);

        return view('dynamic.create', compact('menu', 'relatedData'));
    }

    public function store(Request $request, $menuId)
    {
        $menu = Menu::findOrFail($menuId);

        // Build validation rules
        [$rules, $messages] = $this->buildValidationRules($menu);
        $validated = $request->validate($rules, $messages);

        // Handle file uploads
        $validated = $this->handleFileUploads($request, $menu, $validated);

        // Convert checkbox values to 0 or 1
        foreach ($menu->getFieldDefinitions() as $field) {
            if ($field['type'] === 'checkbox' && isset($validated[$field['name']])) {
                $validated[$field['name']] = $validated[$field['name']] == '1' ? 1 : 0;
            }
        }

        // Add timestamps
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table($menu->table_name)->insert($validated);

        return redirect()->route('dynamic.index', $menuId)
            ->with('success', 'Record created successfully.');
    }

    public function edit($menuId, $id)
    {
        $menu = Menu::findOrFail($menuId);

        $record = DB::table($menu->table_name)->where('id', $id)->first();

        if (!$record) {
            abort(404);
        }

        // Get related data for foreign keys
        $relatedData = $this->getRelatedData($menu);

        return view('dynamic.edit', compact('menu', 'record', 'relatedData'));
    }

    public function update(Request $request, $menuId, $id)
    {
        $menu = Menu::findOrFail($menuId);

        // Get old record for file handling
        $oldRecord = DB::table($menu->table_name)->where('id', $id)->first();

        // Build validation rules
        [$rules, $messages] = $this->buildValidationRules($menu, $id);
        $validated = $request->validate($rules, $messages);

        // Handle file uploads
        $validated = $this->handleFileUploads($request, $menu, $validated, $oldRecord);

        // Convert checkbox values to 0 or 1
        foreach ($menu->getFieldDefinitions() as $field) {
            if ($field['type'] === 'checkbox' && isset($validated[$field['name']])) {
                $validated[$field['name']] = $validated[$field['name']] == '1' ? 1 : 0;
            }
        }

        // Update timestamp
        $validated['updated_at'] = now();

        DB::table($menu->table_name)->where('id', $id)->update($validated);

        return redirect()->route('dynamic.index', $menuId)
            ->with('success', 'Record updated successfully.');
    }

    public function destroy($menuId, $id)
    {
        $menu = Menu::findOrFail($menuId);

        // Get record to delete associated files
        $record = DB::table($menu->table_name)->where('id', $id)->first();

        if ($record) {
            // Delete associated files
            foreach ($menu->getFieldDefinitions() as $field) {
                if (in_array($field['type'], ['image', 'file'])) {
                    $filePath = is_array($record) ? ($record[$field['name']] ?? null) : ($record->{$field['name']} ?? null);
                    if ($filePath && Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                }
            }

            // Delete record
            DB::table($menu->table_name)->where('id', $id)->delete();
        }

        return redirect()->route('dynamic.index', $menuId)
            ->with('success', 'Record deleted successfully.');
    }

    /**
     * Get related data for foreign keys
     */
    private function getRelatedData($menu)
    {
        $relatedData = [];
        $relationships = $menu->getRelationships();

        foreach ($relationships as $rel) {
            $data = DB::table($rel['related_table'])
                ->select('id', $rel['display_column'])
                ->get();

            $relatedData[$rel['foreign_key']] = [
                'data' => $data,
                'display_column' => $rel['display_column'],
                'table' => $rel['related_table'],
            ];
        }

        return $relatedData;
    }

    /**
     * Export data to Excel
     */
    public function export(Request $request, $menuId)
    {
        $menu = Menu::findOrFail($menuId);

        // Build same query as index
        $query = DB::table($menu->table_name);

        // Add joins for relationships
        $relationships = $menu->getRelationships();
        foreach ($relationships as $rel) {
            $query->leftJoin(
                $rel['related_table'],
                $menu->table_name . '.' . $rel['foreign_key'],
                '=',
                $rel['related_table'] . '.id'
            );
            $query->addSelect($rel['related_table'] . '.' . $rel['display_column'] . ' as ' . $rel['foreign_key'] . '_display');
        }

        $query->addSelect($menu->table_name . '.*');

        // Apply search if exists
        $search = $request->input('search');
        if ($search) {
            $fields = $menu->getFieldDefinitions();
            $query->where(function ($q) use ($search, $fields, $menu, $relationships) {
                foreach ($fields as $field) {
                    if (
                        !in_array($field['name'], ['id', 'created_at', 'updated_at']) &&
                        in_array($field['type'], ['text', 'textarea', 'number'])
                    ) {
                        $q->orWhereRaw("LOWER(\"{$menu->table_name}\".\"{$field['name']}\") LIKE ?", ['%' . strtolower($search) . '%']);
                    }
                }

                foreach ($relationships as $rel) {
                    $q->orWhereRaw("LOWER(\"{$rel['related_table']}\".\"{$rel['display_column']}\") LIKE ?", ['%' . strtolower($search) . '%']);
                }
            });
        }

        // Apply filters
        $filters = $request->input('filter', []);
        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                $query->where($menu->table_name . '.' . $column, $value);
            }
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($menu->table_name . '.' . $sortBy, $sortOrder);

        // Generate filename
        $filename = strtolower(str_replace(' ', '_', $menu->name)) . '_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new DynamicExport($menu, $query), $filename);
    }

    /**
     * Build validation rules for fields
     */
    private function buildValidationRules($menu, $recordId = null)
    {
        $rules = [];
        $messages = [];

        foreach ($menu->getFieldDefinitions() as $field) {
            if (!in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                $fieldRules = [];

                // Required/Nullable
                if (!$field['nullable']) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                // Type-specific validation
                switch ($field['type']) {
                    case 'number':
                        $fieldRules[] = 'numeric';
                        if (isset($field['validation'])) {
                            if (isset($field['validation']['min'])) {
                                $fieldRules[] = 'min:' . $field['validation']['min'];
                            }
                            if (isset($field['validation']['max'])) {
                                $fieldRules[] = 'max:' . $field['validation']['max'];
                            }
                        }
                        break;

                    case 'text':
                        $fieldRules[] = 'string';
                        if (isset($field['validation'])) {
                            if (isset($field['validation']['min_length'])) {
                                $fieldRules[] = 'min:' . $field['validation']['min_length'];
                            }
                            if (isset($field['validation']['max_length'])) {
                                $fieldRules[] = 'max:' . $field['validation']['max_length'];
                            }
                            if (isset($field['validation']['email']) && $field['validation']['email']) {
                                $fieldRules[] = 'email';
                            }
                            if (isset($field['validation']['url']) && $field['validation']['url']) {
                                $fieldRules[] = 'url';
                            }
                            if (isset($field['validation']['regex'])) {
                                $fieldRules[] = 'regex:' . $field['validation']['regex'];
                            }
                            if (isset($field['validation']['unique']) && $field['validation']['unique']) {
                                $uniqueRule = 'unique:' . $menu->table_name . ',' . $field['name'];
                                if ($recordId) {
                                    $uniqueRule .= ',' . $recordId;
                                }
                                $fieldRules[] = $uniqueRule;
                            }
                        }
                        break;

                    case 'textarea':
                        $fieldRules[] = 'string';
                        if (isset($field['validation']['max_length'])) {
                            $fieldRules[] = 'max:' . $field['validation']['max_length'];
                        }
                        break;

                    case 'date':
                    case 'datetime-local':
                        $fieldRules[] = 'date';
                        break;

                    case 'image':
                        // For update, image is optional if already exists
                        if ($recordId && $field['nullable']) {
                            $fieldRules = ['nullable'];
                        }
                        $fieldRules[] = 'image';
                        $fieldRules[] = 'mimes:jpeg,png,jpg,gif,webp';
                        $fieldRules[] = 'max:2048'; // 2MB
                        break;

                    case 'file':
                        // For update, file is optional if already exists
                        if ($recordId && $field['nullable']) {
                            $fieldRules = ['nullable'];
                        }
                        $fieldRules[] = 'file';
                        $fieldRules[] = 'max:5120'; // 5MB
                        break;
                }

                $rules[$field['name']] = $fieldRules;

                // Custom error messages
                $fieldLabel = ucwords(str_replace('_', ' ', $field['name']));
                $messages[$field['name'] . '.required'] = "The {$fieldLabel} field is required.";
                $messages[$field['name'] . '.email'] = "The {$fieldLabel} must be a valid email address.";
                $messages[$field['name'] . '.url'] = "The {$fieldLabel} must be a valid URL.";
                $messages[$field['name'] . '.unique'] = "The {$fieldLabel} has already been taken.";
                $messages[$field['name'] . '.image'] = "The {$fieldLabel} must be an image file.";
                $messages[$field['name'] . '.mimes'] = "The {$fieldLabel} must be a file of type: :values.";
                $messages[$field['name'] . '.file'] = "The {$fieldLabel} must be a file.";
                $messages[$field['name'] . '.max'] = "The {$fieldLabel} file size must not exceed :max KB.";
            }
        }

        return [$rules, $messages];
    }

    /**
     * Handle file uploads and return validated data
     */
    private function handleFileUploads(Request $request, $menu, $validated, $oldRecord = null)
    {
        foreach ($menu->getFieldDefinitions() as $field) {
            if (in_array($field['type'], ['image', 'file'])) {
                if ($request->hasFile($field['name'])) {
                    // Delete old file if exists
                    if ($oldRecord) {
                        $oldPath = is_array($oldRecord) ? ($oldRecord[$field['name']] ?? null) : ($oldRecord->{$field['name']} ?? null);
                        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }

                    // Upload new file
                    $file = $request->file($field['name']);
                    $folder = $field['type'] === 'image' ? 'images' : 'files';
                    $path = $file->store($folder, 'public');
                    $validated[$field['name']] = $path;
                } elseif ($oldRecord) {
                    // Keep old file path if no new file uploaded
                    $oldPath = is_array($oldRecord) ? ($oldRecord[$field['name']] ?? null) : ($oldRecord->{$field['name']} ?? null);
                    if ($oldPath) {
                        $validated[$field['name']] = $oldPath;
                    }
                }
            }
        }

        return $validated;
    }
}
