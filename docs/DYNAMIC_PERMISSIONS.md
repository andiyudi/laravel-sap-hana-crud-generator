# Dynamic Permissions - Automatic Permission Creation

## Overview

Sistem Menu Management sekarang dilengkapi dengan fitur **Dynamic Permissions** yang secara otomatis membuat permission saat menu dibuat.

## Status: ✅ IMPLEMENTED

## How It Works

### 1. Automatic Permission Creation

Saat Anda membuat menu baru, sistem akan otomatis membuat 4 permission:
- `{menu_slug}.view` - Melihat data
- `{menu_slug}.create` - Membuat data baru
- `{menu_slug}.edit` - Mengedit data
- `{menu_slug}.delete` - Menghapus data

### 2. Menu Slug Generation

Menu slug dibuat dari nama menu dengan aturan:
- Lowercase
- Spasi diganti underscore
- Contoh: "User Data" → "user_data"

### 3. Auto-Assign to Admin

Semua permission yang dibuat otomatis di-assign ke role `admin`.

## Example

### Membuat Menu "Products"

**Input:**
- Name: Products
- Table: products
- Icon: bi-box-seam

**Output (Automatic):**
```
✓ Menu created: Products
✓ Permissions created:
  - products.view
  - products.create
  - products.edit
  - products.delete
✓ Permissions assigned to admin role
```

## Base Permissions

Sistem sudah memiliki base permissions untuk management:

### User Management:
- users.view
- users.create
- users.edit
- users.delete

### Role Management:
- roles.view
- roles.create
- roles.edit
- roles.delete

### Permission Management:
- permissions.view
- permissions.create
- permissions.edit
- permissions.delete

### Menu Management:
- menus.view
- menus.create
- menus.edit
- menus.delete

## Dynamic Permissions

Setiap menu yang dibuat akan menambah 4 permission baru:

### Example: Menu "Categories"
- categories.view
- categories.create
- categories.edit
- categories.delete

### Example: Menu "Orders"
- orders.view
- orders.create
- orders.edit
- orders.delete

## Permission Deletion

Saat menu dihapus, permission terkait juga akan dihapus otomatis untuk menjaga kebersihan database.

## Implementation Details

### MenuController.php

```php
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

    // Assign to admin role
    $adminRole = Role::where('name', 'admin')->first();
    if ($adminRole) {
        $adminRole->givePermissionTo($permissions);
    }
}
```

## Benefits

### 1. Zero Configuration
- Tidak perlu manual membuat permission
- Tidak perlu update seeder
- Tidak perlu assign permission ke role

### 2. Consistent Naming
- Semua permission mengikuti pattern yang sama
- Mudah diprediksi dan di-manage

### 3. Automatic Cleanup
- Permission dihapus saat menu dihapus
- Tidak ada orphaned permissions

### 4. Role Integration
- Admin role otomatis mendapat akses
- Role lain bisa di-assign manual sesuai kebutuhan

## Usage in Controllers

Untuk menggunakan permission di controller:

```php
// Check permission
if (auth()->user()->can('products.view')) {
    // User can view products
}

// Middleware
Route::middleware(['permission:products.create'])->group(function () {
    // Routes that require products.create permission
});

// In controller constructor
public function __construct()
{
    $this->middleware('permission:products.view')->only(['index', 'show']);
    $this->middleware('permission:products.create')->only(['create', 'store']);
    $this->middleware('permission:products.edit')->only(['edit', 'update']);
    $this->middleware('permission:products.delete')->only(['destroy']);
}
```

## Usage in Blade Views

```blade
@can('products.create')
    <a href="{{ route('products.create') }}" class="btn btn-primary">
        Add Product
    </a>
@endcan

@can('products.edit')
    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning">
        Edit
    </a>
@endcan

@can('products.delete')
    <form method="POST" action="{{ route('products.destroy', $product) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Delete</button>
    </form>
@endcan
```

## Future Enhancements

Possible improvements:
- [ ] Custom permission names per menu
- [ ] Granular permission control (field-level)
- [ ] Permission templates
- [ ] Bulk permission assignment
- [ ] Permission inheritance
- [ ] API permission support

## Testing

### Test Permission Creation:

1. Create a new menu via UI
2. Check permissions table:
```bash
php artisan tinker
>>> \Spatie\Permission\Models\Permission::where('name', 'like', 'your_menu%')->get()
```

3. Verify admin has permissions:
```bash
>>> $admin = \Spatie\Permission\Models\Role::where('name', 'admin')->first()
>>> $admin->permissions->pluck('name')
```

## Troubleshooting

### Permission not created
- Check if menu was created successfully
- Verify Spatie Permission is installed
- Check database connection

### Permission not assigned to admin
- Verify admin role exists
- Check role name is exactly 'admin'
- Run seeder to create base roles

### Permission not working
- Clear permission cache: `php artisan permission:cache-reset`
- Check middleware is applied
- Verify user has the role

## Conclusion

Dynamic Permissions membuat sistem lebih fleksibel dan mudah digunakan. Setiap menu yang dibuat otomatis mendapat permission yang sesuai, tanpa perlu konfigurasi manual.

---

**Status**: ✅ FULLY IMPLEMENTED AND TESTED

**Date**: March 26, 2026
