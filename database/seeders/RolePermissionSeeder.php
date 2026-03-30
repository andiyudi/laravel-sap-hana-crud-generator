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
        $admin->syncPermissions(Permission::all());

        // Editor role - can view and edit dynamic menus, but cannot delete or manage users
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editorPermissions = Permission::where('name', 'like', '%.view')
            ->orWhere('name', 'like', '%.create')
            ->orWhere('name', 'like', '%.edit')
            ->get()
            ->pluck('name')
            ->filter(function ($perm) {
                // Exclude user management permissions
                return !str_starts_with($perm, 'users.') &&
                    !str_starts_with($perm, 'roles.') &&
                    !str_starts_with($perm, 'permissions.');
            })
            ->toArray();
        $editor->syncPermissions($editorPermissions);

        // Viewer role - read-only access to all menus (NO access to user management)
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewerPermissions = Permission::where('name', 'like', '%.view')
            ->get()
            ->pluck('name')
            ->filter(function ($perm) {
                // Exclude user management permissions
                return !str_starts_with($perm, 'users.') &&
                    !str_starts_with($perm, 'roles.') &&
                    !str_starts_with($perm, 'permissions.');
            })
            ->toArray();
        $viewer->syncPermissions($viewerPermissions);
    }
}
