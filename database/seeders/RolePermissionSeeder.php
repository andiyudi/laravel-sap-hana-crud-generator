<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed roles dan permissions.
     * Menggunakan firstOrCreate agar idempotent (aman dijalankan berulang kali).
     */
    public function run(): void
    {
        // Base permissions untuk user management
        $basePermissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            'menus.view',
            'menus.create',
            'menus.edit',
            'menus.delete',
        ];

        // Create all permissions
        foreach ($basePermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Admin role - has all permissions
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($basePermissions);

        // Editor role - can manage content but not users/roles
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions([
            'menus.view',
            'menus.create',
            'menus.edit',
        ]);

        // Viewer role - read-only access
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'users.view',
            'roles.view',
            'permissions.view',
            'menus.view',
        ]);
    }
}
