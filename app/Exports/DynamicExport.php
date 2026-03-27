<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DynamicExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $menu;
    protected $query;
    protected $fields;

    public function __construct($menu, $query)
    {
        $this->menu = $menu;
        $this->query = $query;
        $this->fields = array_filter($menu->getFieldDefinitions(), function ($field) {
            return !in_array($field['name'], ['created_at', 'updated_at']);
        });
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        $headings = [];
        foreach ($this->fields as $field) {
            $headings[] = ucwords(str_replace('_', ' ', $field['name']));
        }
        return $headings;
    }

    public function map($row): array
    {
        $mapped = [];

        foreach ($this->fields as $field) {
            $value = is_array($row) ? ($row[$field['name']] ?? '') : ($row->{$field['name']} ?? '');

            // Check if this is a foreign key with display value
            $displayKey = $field['name'] . '_display';
            $hasDisplay = is_array($row) ? isset($row[$displayKey]) : isset($row->$displayKey);

            if ($hasDisplay) {
                // Show related data instead of ID
                $value = is_array($row) ? $row[$displayKey] : $row->$displayKey;
            } elseif ($field['type'] === 'checkbox') {
                $value = $value ? 'Yes' : 'No';
            } elseif (in_array($field['type'], ['date', 'datetime-local']) && $value) {
                $value = date('Y-m-d H:i:s', strtotime($value));
            }

            $mapped[] = $value;
        }

        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
