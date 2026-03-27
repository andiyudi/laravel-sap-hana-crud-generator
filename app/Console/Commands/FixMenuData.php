<?php

namespace App\Console\Commands;

use App\Models\Menu;
use Illuminate\Console\Command;

class FixMenuData extends Command
{
    protected $signature = 'menu:fix-data {menu_id}';
    protected $description = 'Fix menu data - ensure fields and relationships are properly stored as JSON';

    public function handle()
    {
        $menuId = $this->argument('menu_id');
        $menu = Menu::find($menuId);

        if (!$menu) {
            $this->error("Menu with ID {$menuId} not found!");
            return 1;
        }

        $this->info("Fixing menu: {$menu->name}");
        $this->line("");

        // Check fields
        $this->info("Current fields type: " . gettype($menu->fields));
        if (is_string($menu->fields)) {
            $this->warn("Fields is a string, attempting to decode...");
            $decoded = json_decode($menu->fields, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $menu->fields = $decoded;
                $this->info("✓ Fields decoded successfully");
            } else {
                $this->error("✗ Failed to decode fields: " . json_last_error_msg());
            }
        } else {
            $this->info("✓ Fields is already an array");
        }

        // Check relationships
        $this->info("Current relationships type: " . gettype($menu->relationships));
        if (is_string($menu->relationships)) {
            $this->warn("Relationships is a string, attempting to decode...");
            $decoded = json_decode($menu->relationships, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $menu->relationships = $decoded;
                $this->info("✓ Relationships decoded successfully");
            } else {
                $this->error("✗ Failed to decode relationships: " . json_last_error_msg());
            }
        } else {
            $this->info("✓ Relationships is already an array");
        }

        // Save
        $menu->save();
        $this->line("");
        $this->info("✓ Menu data fixed and saved!");
        $this->line("");

        // Show fields
        $fields = $menu->getFieldDefinitions();
        if (is_array($fields) && !empty($fields)) {
            $this->info("Field definitions:");
            $this->table(
                ['Field Name', 'Type', 'Nullable'],
                collect($fields)->map(fn($f) => [
                    $f['name'] ?? 'unknown',
                    $f['type'] ?? 'unknown',
                    isset($f['nullable']) ? ($f['nullable'] ? 'Yes' : 'No') : 'unknown'
                ])
            );
        } else {
            $this->warn("No fields found or fields is not an array");
        }

        return 0;
    }
}
