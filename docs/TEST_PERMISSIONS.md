# Testing Permission System

## Quick Test

### 1. Check Current Setup
```bash
php test_permission.php
```

Ini akan menampilkan permissions untuk user editor.

### 2. Assign Role ke User

Jika Anda sudah punya user tapi belum punya role:

```bash
php assign_roles.php
```

Atau via tinker:
```bash
php artisan tinker
```

```php
// Assign role editor
$user = User::where('email', 'your@email.com')->first();
$user->assignRole('editor');

// Atau assign role viewer
$user->assignRole('viewer');

// Check role
$user->getRoleNames();

// Check permissions
$user->getAllPermissions()->pluck('name');
```

### 3. Test Login

1. **Login sebagai Admin** (administrator@example.com)
   - Bisa lihat semua menu
   - Bisa create, edit, delete semua
   - Bisa akses User Management

2. **Login sebagai Editor** (editor@example.com)
   - Bisa lihat Categories dan Products
   - Bisa create dan edit
   - TIDAK bisa delete (tombol delete tidak muncul)
   - TIDAK bisa akses User Management

3. **Login sebagai Viewer**
   - Bisa lihat Categories dan Products
   - TIDAK bisa create (tombol "Create New" tidak muncul)
   - TIDAK bisa edit (tombol edit tidak muncul)
   - TIDAK bisa delete

## Troubleshooting

### User masih bisa akses semua
1. Check role user:
```bash
php artisan tinker
```
```php
$user = User::find(1);
$user->getRoleNames(); // Harus ada role
```

2. Clear cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

3. Logout dan login lagi

### Permission denied tapi seharusnya bisa akses

1. Check permission exists:
```php
Permission::where('name', 'categories.view')->first();
```

2. Check role has permission:
```php
$role = Role::findByName('editor');
$role->permissions->pluck('name');
```

3. Regenerate permissions:
```bash
php artisan permissions:generate-menu
php artisan db:seed --class=RolePermissionSeeder
```

### Menu tidak muncul di sidebar

1. Check menu is_active:
```php
Menu::where('is_active', 1)->get();
```

2. Check user can view:
```php
$user = auth()->user();
$menu = Menu::find(1);
$menuSlug = strtolower(str_replace(' ', '_', $menu->name));
$user->can("{$menuSlug}.view"); // Harus true
```

## Create New User with Role

```bash
php artisan tinker
```

```php
// Create user
$user = User::create([
    'name' => 'Test Editor',
    'email' => 'test.editor@example.com',
    'password' => bcrypt('password')
]);

// Assign role
$user->assignRole('editor');

// Verify
$user->getRoleNames();
$user->getAllPermissions()->pluck('name');
```

## Expected Behavior

### Admin
- Sees: Dashboard, Menu Builder, Dynamic Menus, User Management, System
- Can: Everything

### Editor
- Sees: Dashboard, Dynamic Menus (Categories, Products)
- Can: View, Create, Edit
- Cannot: Delete, Access User Management

### Viewer
- Sees: Dashboard, Dynamic Menus (Categories, Products)
- Can: View only
- Cannot: Create, Edit, Delete, Access User Management

## Verify Permissions in Database

```sql
-- Check permissions
SELECT * FROM permissions WHERE name LIKE '%categories%';

-- Check role permissions
SELECT r.name as role, p.name as permission 
FROM roles r
JOIN role_has_permissions rhp ON r.id = rhp.role_id
JOIN permissions p ON rhp.permission_id = p.id
WHERE r.name IN ('admin', 'editor', 'viewer')
ORDER BY r.name, p.name;

-- Check user roles
SELECT u.name, u.email, r.name as role
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id;
```
