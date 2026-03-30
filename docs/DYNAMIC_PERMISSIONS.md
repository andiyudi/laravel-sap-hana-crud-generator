# Dynamic Menu Permissions

## Overview
Sistem permission otomatis untuk dynamic menus menggunakan Spatie Laravel Permission.

## Permission Structure

Setiap menu memiliki 4 permissions:
- `{menu_slug}.view` - Melihat list dan detail
- `{menu_slug}.create` - Membuat record baru
- `{menu_slug}.edit` - Mengubah record
- `{menu_slug}.delete` - Menghapus record

Contoh untuk menu "Categories":
- `categories.view`
- `categories.create`
- `categories.edit`
- `categories.delete`

## Roles

### Admin
- Memiliki SEMUA permissions
- Akses penuh ke semua menu dan fitur

### Editor
- Dapat view, create, dan edit semua dynamic menus
- TIDAK dapat delete
- TIDAK dapat manage users, roles, permissions

### Viewer
- Hanya dapat view (read-only)
- Tidak dapat create, edit, atau delete
- Akses ke semua menu tapi read-only

## Generate Permissions

Setiap kali membuat menu baru, jalankan command:

```bash
php artisan permissions:generate-menu
```

Command ini akan:
1. Generate 4 permissions untuk setiap menu
2. Assign semua permissions ke role admin
3. Update permissions untuk role editor dan viewer

## Update Role Permissions

Untuk update permissions role secara manual:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

## Cara Kerja

### 1. Controller Check
Setiap method di `DynamicCrudController` mengecek permission:
```php
$this->checkMenuPermission($menu, 'view');  // untuk index, show
$this->checkMenuPermission($menu, 'create'); // untuk create, store
$this->checkMenuPermission($menu, 'edit');   // untuk edit, update
$this->checkMenuPermission($menu, 'delete'); // untuk destroy
```

### 2. Sidebar Filter
Sidebar hanya menampilkan menu yang user punya akses:
```php
$canView = $user->hasRole('admin') || $user->can("{$menuSlug}.view");
```

### 3. View Buttons
Tombol create, edit, delete di view harus dicek:
```blade
@can("{$menuSlug}.create")
    <a href="{{ route('dynamic.create', $menu->id) }}" class="btn btn-primary">
        Create New
    </a>
@endcan
```

## Testing

### Test sebagai Editor:
1. Login dengan user role editor
2. Bisa lihat semua menu
3. Bisa create dan edit
4. TIDAK bisa delete (tombol delete tidak muncul)

### Test sebagai Viewer:
1. Login dengan user role viewer
2. Bisa lihat semua menu
3. TIDAK bisa create (tombol create tidak muncul)
4. TIDAK bisa edit (tombol edit tidak muncul)
5. TIDAK bisa delete (tombol delete tidak muncul)

## Assign Permission ke User

```php
// Assign role
$user->assignRole('editor');

// Atau assign permission spesifik
$user->givePermissionTo('categories.view');
$user->givePermissionTo('products.edit');
```

## Custom Permissions

Untuk permission khusus, tambahkan manual:

```php
use Spatie\Permission\Models\Permission;

Permission::create(['name' => 'categories.export']);
Permission::create(['name' => 'products.import']);
```

Lalu assign ke role:

```php
$editor = Role::findByName('editor');
$editor->givePermissionTo('categories.export');
```

## Troubleshooting

### User masih bisa akses semua menu
- Pastikan sudah run `php artisan permissions:generate-menu`
- Pastikan sudah run `php artisan db:seed --class=RolePermissionSeeder`
- Clear cache: `php artisan cache:clear`
- Check role user: `$user->getRoleNames()`

### Permission denied error
- Check permission name sesuai format: `{menu_slug}.{action}`
- Check user punya role atau permission: `$user->can('categories.view')`
- Check di database table `permissions` dan `role_has_permissions`

### Menu tidak muncul di sidebar
- Check permission view: `$user->can("{$menuSlug}.view")`
- Check menu is_active = 1
- Check menu order

## Best Practices

1. Selalu generate permissions setelah create menu baru
2. Gunakan role untuk group permissions
3. Assign role ke user, bukan individual permissions
4. Test dengan user non-admin
5. Dokumentasikan custom permissions
