<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Menu;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GenerateMenuPermissions extends Command
{
    protected $signature = 'permissions:generate-menu';
    protected $description = 'Generate permissions for all dynamic menus';

    public function handle()
    {
        $menus = Menu::all();
        $actions = ['view', 'create', 'edit', 'delete'];
        $createdCount = 0;

        foreach ($menus as $menu) {
            $menuSlug = strtolower(str_replace(' ', '_', $menu->name));

            foreach ($actions as $action) {
                $permissionName = "{$menuSlug}.{$action}";

                $permission = Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web'
                ]);

                if ($permission->wasRecentlyCreated) {
                    $this->info("Created: {$permissionName}");
                    $createdCount++;
                }
            }
        }

        // Give all menu permissions to admin role
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $allMenuPermissions = Permission::where('name', 'like', '%.view')
                ->orWhere('name', 'like', '%.create')
                ->orWhere('name', 'like', '%.edit')
                ->orWhere('name', 'like', '%.delete')
                ->get();

            $admin->syncPermissions($allMenuPermissions);
            $this->info("Admin role updated with all menu permissions");
        }

        $this->info("\nTotal permissions created: {$createdCount}");
        $this->info("Total menus processed: " . $menus->count());

        return 0;
    }
}
