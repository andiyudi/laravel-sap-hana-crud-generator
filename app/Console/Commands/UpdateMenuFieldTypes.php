<?php

namespace App\Console\Commands;

use App\Models\Menu;
use Illuminate\Console\Command;

class UpdateMenuFieldTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:update-field-types {menu_id} {field_name} {new_type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update field type in menu field definitions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $menuId = $this->argument('menu_id');
        $fieldName = $this->argument('field_name');
        $newType = $this->argument('new_type');

        $menu = Menu::find($menuId);

        if (!$menu) {
            $this->error("Menu with ID {$menuId} not found!");
            return 1;
        }

        $fields = $menu->getFieldDefinitions();
        $updated = false;

        foreach ($fields as &$field) {
            if ($field['name'] === $fieldName) {
                $oldType = $field['type'];
                $field['type'] = $newType;
                $updated = true;
                $this->info("Updated field '{$fieldName}' from type '{$oldType}' to '{$newType}'");
                break;
            }
        }

        if (!$updated) {
            $this->error("Field '{$fieldName}' not found in menu '{$menu->name}'!");
            return 1;
        }

        // Save updated fields
        $menu->fields = json_encode($fields);
        $menu->save();

        $this->info("Menu '{$menu->name}' updated successfully!");
        $this->line("Field definitions:");
        $this->table(
            ['Field Name', 'Type', 'Nullable'],
            collect($fields)->map(fn($f) => [$f['name'], $f['type'], $f['nullable'] ? 'Yes' : 'No'])
        );

        return 0;
    }
}
