# User Management Documentation

## Overview
Aplikasi ini dilengkapi dengan sistem User Management yang lengkap menggunakan Spatie Laravel Permission package. Sistem ini memungkinkan pengelolaan Users, Roles, dan Permissions dengan interface yang user-friendly.

## Features

### 1. User Management
Mengelola user accounts dalam sistem.

#### Fitur:
- **List Users**: Melihat semua users dengan pagination
- **Create User**: Menambah user baru dengan email, password, dan roles
- **Edit User**: Mengubah informasi user dan assign roles
- **Delete User**: Menghapus user (tidak bisa menghapus diri sendiri)
- **View User**: Melihat detail user, roles, dan permissions

#### Routes:
- `GET /users` - List all users
- `GET /users/create` - Form create user
- `POST /users` - Store new user
- `GET /users/{id}` - View user details
- `GET /users/{id}/edit` - Form edit user
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user

### 2. Role Management
Mengelola roles dan permissions yang terkait.

#### Fitur:
- **List Roles**: Melihat semua roles dengan jumlah permissions dan users
- **Create Role**: Membuat role baru dan assign permissions
- **Edit Role**: Mengubah nama role dan permissions
- **Delete Role**: Menghapus role (tidak bisa jika masih ada users)
- **View Role**: Melihat detail role, permissions, dan users yang assigned

#### Routes:
- `GET /roles` - List all roles
- `GET /roles/create` - Form create role
- `POST /roles` - Store new role
- `GET /roles/{id}` - View role details
- `GET /roles/{id}/edit` - Form edit role
- `PUT /roles/{id}` - Update role
- `DELETE /roles/{id}` - Delete role

### 3. Permission Management
Mengelola permissions dalam sistem.

#### Fitur:
- **List Permissions**: Melihat semua permissions dengan jumlah roles
- **Create Permission**: Membuat permission baru
- **Edit Permission**: Mengubah nama permission
- **Delete Permission**: Menghapus permission (tidak bisa jika masih assigned ke roles)
- **View Permission**: Melihat detail permission dan roles yang menggunakan

#### Routes:
- `GET /permissions` - List all permissions
- `GET /permissions/create` - Form create permission
- `POST /permissions` - Store new permission
- `GET /permissions/{id}` - View permission details
- `GET /permissions/{id}/edit` - Form edit permission
- `PUT /permissions/{id}` - Update permission
- `DELETE /permissions/{id}` - Delete permission

## Permission Naming Convention

Gunakan dot notation untuk nama permission:
```
resource.action
```

### Examples:
- `product.create` - Can create products
- `product.edit` - Can edit products
- `product.delete` - Can delete products
- `product.view` - Can view products
- `user.create` - Can create users
- `user.edit` - Can edit users
- `user.delete` - Can delete users
- `role.manage` - Can manage roles
- `permission.manage` - Can manage permissions

## Default Roles & Permissions

Setelah running seeder (`RolePermissionSeeder`), sistem akan memiliki:

### Roles:
1. **Admin** - Full access to all features
2. **Manager** - Can manage products and view users
3. **User** - Basic access (view only)

### Permissions:
- `product.create`
- `product.edit`
- `product.delete`

## Usage Examples

### 1. Create New User
```php
// Via UI: /users/create
// Or programmatically:
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password'),
]);

// Assign role
$user->assignRole('Manager');
```

### 2. Create New Role
```php
// Via UI: /roles/create
// Or programmatically:
$role = Role::create(['name' => 'Editor']);

// Assign permissions
$role->givePermissionTo(['product.create', 'product.edit']);
```

### 3. Check Permissions in Code
```php
// Check if user has permission
if (auth()->user()->can('product.create')) {
    // User can create products
}

// Check if user has role
if (auth()->user()->hasRole('Admin')) {
    // User is admin
}
```

### 4. Check Permissions in Blade
```blade
@can('product.create')
    <a href="{{ route('products.create') }}">Add Product</a>
@endcan

@role('Admin')
    <p>You are an administrator</p>
@endrole
```

### 5. Protect Routes with Middleware
```php
// In routes/web.php
Route::middleware(['auth', 'permission:product.create'])->group(function () {
    Route::get('/products/create', [ProductController::class, 'create']);
});

// Or with role
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::resource('users', UserController::class);
});
```

## UI Components

### Navigation
Menu User Management tersedia di sidebar dengan 3 sub-menu:
- **Users** (icon: people)
- **Roles** (icon: shield-check)
- **Permissions** (icon: key)

### Tables
Semua list pages menggunakan Bootstrap table dengan:
- Responsive design
- Hover effects
- Action buttons (View, Edit, Delete)
- Pagination
- Badge indicators untuk counts

### Forms
Semua forms dilengkapi dengan:
- Bootstrap validation styling
- Icons untuk setiap field
- Required field indicators (*)
- Error messages
- Cancel & Submit buttons

### Badges
- **Primary** (blue): Roles
- **Secondary** (gray): Permissions
- **Success** (green): User counts
- **Info** (cyan): Permission counts

## Security Features

### 1. Self-Protection
- User tidak bisa menghapus diri sendiri
- Ditandai dengan badge "You" di user list

### 2. Cascade Protection
- Role tidak bisa dihapus jika masih ada users
- Permission tidak bisa dihapus jika masih assigned ke roles
- Error message akan ditampilkan

### 3. Password Handling
- Password di-hash menggunakan bcrypt
- Minimum 8 characters
- Confirmation required
- Optional saat edit (leave blank to keep current)

### 4. Validation
- Email must be unique
- Role name must be unique
- Permission name must be unique
- All required fields validated

## Database Tables

### Users Table
```sql
- id
- name
- email (unique)
- password
- remember_token
- created_at
- updated_at
```

### Roles Table (Spatie)
```sql
- id
- name (unique)
- guard_name
- created_at
- updated_at
```

### Permissions Table (Spatie)
```sql
- id
- name (unique)
- guard_name
- created_at
- updated_at
```

### Pivot Tables
- `model_has_roles` - User-Role relationship
- `model_has_permissions` - User-Permission relationship
- `role_has_permissions` - Role-Permission relationship

## Best Practices

### 1. Role Hierarchy
Buat roles berdasarkan level akses:
```
Admin > Manager > Editor > User
```

### 2. Permission Grouping
Group permissions by resource:
```
product.* (create, edit, delete, view)
user.* (create, edit, delete, view)
report.* (view, export)
```

### 3. Naming Convention
- Use lowercase
- Use dot notation
- Be specific and descriptive
- Keep it short

### 4. Assignment Strategy
- Assign permissions to roles, not directly to users
- Users get permissions through roles
- Use direct permission assignment only for exceptions

## Troubleshooting

### Issue: Permission not working
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Re-sync permissions
php artisan permission:cache-reset
```

### Issue: User can't access after role assignment
- Check if role has the required permissions
- Verify middleware is applied to routes
- Clear application cache

### Issue: Can't delete role/permission
- Check if it's still assigned to users/roles
- Remove assignments first
- Then delete

## Future Enhancements

- [ ] Add permission groups/categories
- [ ] Add bulk user import
- [ ] Add user activity log
- [ ] Add role templates
- [ ] Add permission inheritance
- [ ] Add API endpoints for user management
- [ ] Add 2FA (Two-Factor Authentication)
- [ ] Add password reset functionality
- [ ] Add email verification

## References

- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission)
- [Laravel Authorization](https://laravel.com/docs/authorization)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs)
