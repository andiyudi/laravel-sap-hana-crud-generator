<?php

namespace App\Console\Commands;

use App\Models\Menu;
use Illuminate\Console\Command;

class ShowMenuFields extends Command
{
    protected $signature = 'menu:show-fields {menu_id}';
    protected $description = 'Show field definitions for a menu';

    public function handle()
    {
        $menuId = $this->argument('menu_id');
        $menu = Menu::find($menuId);

        if (!$menu) {
            $this->error("Menu with ID {$menuId} not found!");
            return 1;
        }

        $this->info("Menu: {$menu->name}");
        $this->info("Table: {$menu->table_name}");
        $this->line("");

        $fields = $menu->getFieldDefinitions();

        $this->table(
            ['Field Name', 'Type', 'Nullable', 'Default'],
            collect($fields)->map(fn($f) => [
                $f['name'],
                $f['type'],
                $f['nullable'] ? 'Yes' : 'No',
                $f['default'] ?? '-'
            ])
        );

        return 0;
    }
}
