# Complete Features Documentation

## Overview

Laravel 13 application with SAP HANA database integration and dynamic CRUD management system.

## Core Features

### 1. Custom SAP HANA Database Driver

Custom Laravel database driver for SAP HANA located in `packages/custom/laravel-hana/`.

**Features:**
- Full Laravel Query Builder support
- Schema builder for migrations
- Connection management
- Custom grammar for HANA SQL syntax

**Configuration:**
```env
DB_CONNECTION=hana
DB_HOST=your-hana-host
DB_PORT=30015
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password
HANA_SCHEMA=your-schema
```

### 2. Dynamic Menu Management

Create CRUD interfaces directly from the UI without writing code.

**Features:**
- Select existing database table
- Auto-detect table columns and types
- Configure menu name, icon, and order
- Toggle active/inactive status
- Auto-generate 4 permissions per menu (view, create, edit, delete)

**Usage:**
1. Go to Menu Management
2. Click "Create Menu"
3. Select table from dropdown
4. Configure menu settings
5. Save - CRUD interface is ready!

**Controllers:**
- `MenuController.php` - Menu management
- `DynamicCrudController.php` - Dynamic CRUD operations

### 3. Table Builder

Create database tables from UI without writing migrations.

**Supported Field Types:**
- String (VARCHAR)
- Text (NCLOB)
- Integer
- Decimal
- Boolean (TINYINT)
- Date
- DateTime
- Timestamp
- File (for file uploads)
- Image (for image uploads)

**Field Properties:**
- Nullable
- Unique
- Default value
- Length (for string/decimal)

**Usage:**
1. Go to "Create Table" in sidebar
2. Enter table name
3. Add fields with properties
4. Click "Create Table"
5. Table is created in HANA database

**Controller:** `TableBuilderController.php`

### 4. User Management

Complete user management with role-based access control.

**Features:**
- Create, edit, delete users
- Assign multiple roles to users
- Self-protection (can't delete yourself)
- Password management
- Email validation

**Default Users:**
- Admin: admin@example.com / password
- User: user@example.com / password

**Controller:** `UserController.php`

### 5. Role Management

Manage roles and their permissions.

**Features:**
- Create, edit, delete roles
- Assign permissions to roles
- View role statistics (users count, permissions count)
- Cascade protection (can't delete role with users)
- Permission grouping by module

**Default Roles:**
- admin - Full access
- user - Limited access

**Controller:** `RoleController.php`

### 6. Permission Management

Manage individual permissions.

**Features:**
- Create, edit, delete permissions
- View which roles have the permission
- Cascade protection (can't delete if assigned to roles)
- Auto-generated from menus

**Permission Naming Convention:**
- `{module}.view` - View/list records
- `{module}.create` - Create new records
- `{module}.edit` - Edit existing records
- `{module}.delete` - Delete records

**Controller:** `PermissionController.php`

### 7. Dynamic Permissions

Permissions are automatically created/deleted with menus.

**Auto-generation:**
- When menu is created → 4 permissions created
- Permissions auto-assigned to admin role
- When menu is deleted → permissions deleted

**Example:**
Menu "Products" creates:
- products.view
- products.create
- products.edit
- products.delete

### 8. Bootstrap 5 UI with Dark/Light Mode

Modern, responsive UI with theme switching.

**Features:**
- Bootstrap 5.3.2
- Dark/light mode toggle
- Theme persistence (localStorage)
- Responsive sidebar navigation
- Bootstrap Icons
- Clean card-based layouts
- Improved pagination with result count

**Layout:** `resources/views/layouts/app.blade.php`

### 9. Automatic Relationship Detection

Automatically detects and handles foreign key relationships.

**Features:**
- Auto-detect columns ending with `_id` as foreign keys
- Guess related table name (user_id → users)
- Dropdown selection for foreign keys (not text input)
- Display related data in list view (show names, not IDs)
- Smart display column detection (name, title, email, etc.)

**Example:**
Table `orders` with `user_id` column:
- Create/Edit: Dropdown to select user by name
- List: Shows "John Doe" instead of user_id "1"

**Supported:**
- belongsTo relationships (many-to-one)
- Foreign key naming convention: `{table}_id`

**See:** [Relationship Feature Documentation](RELATIONSHIP_FEATURE.md)

### 11. Advanced Search & Filtering

Case-insensitive search across all text and number fields.

**Features:**
- Search in main table fields
- Search in related table display columns
- Case-insensitive using LOWER() function
- Preserve search with pagination
- Clear filters button

**Example:**
Search "ele" finds "Electronics" category

### 12. Column Sorting

Click column headers to sort data.

**Features:**
- Sort by any column
- Toggle ascending/descending
- Visual indicators (↑↓ arrows)
- Preserve sort with search/pagination

### 13. Export to Excel

Export current view with active filters to Excel.

**Features:**
- Uses maatwebsite/excel package
- Exports filtered/sorted data
- Auto-sized columns
- Bold headers
- Related data shows names (not IDs)
- Filename: `{menu_name}_{datetime}.xlsx`

**Button:** Appears in list view when data exists

### 14. Advanced Validation

Type-specific validation rules for fields.

**Validation Types:**
- **Numeric**: min/max value
- **String**: min/max length, email, url, regex, unique
- **Date**: date format
- **File**: file type, max size
- **Image**: image type, dimensions, max size

**Configuration:**
Validation rules stored in field definitions JSON in menus table.

### 15. File Upload

Upload and manage files and images.

**Features:**
- **Image fields**: JPEG, PNG, JPG, GIF, WEBP (max 2MB)
- **File fields**: Any file type (max 5MB)
- Automatic storage in `storage/app/public/`
- Image preview in forms
- Thumbnail display in list view
- Download links for files
- Old file deletion on update
- File cleanup on record deletion

**Setup:**
```bash
php artisan storage:link
```

**See:** [File Upload Feature Documentation](FILE_UPLOAD_FEATURE.md)

### 10. Smart Field Type Detection

Automatically maps database column types to appropriate form inputs.

**Type Mapping:**
- INT/BIGINT → Number input
- VARCHAR/NVARCHAR → Text input
- TEXT/NCLOB → Textarea
- DATE → Date picker
- DATETIME/TIMESTAMP → DateTime picker
- TINYINT → Checkbox (if name matches boolean patterns)
- File/Image columns → File upload input

**Boolean Detection Patterns:**
- is_* (is_active, is_published)
- has_* (has_permission)
- can_* (can_edit)
- should_* (should_notify)
- *active, *enabled, *disabled, *published

### 16. Pagination

Bootstrap 5 pagination with result information.

**Features:**
- 10 items per page
- "Showing X to Y of Z results" info
- Bootstrap 5 styled navigation
- Consistent across all list pages

**Component:** `resources/views/components/pagination.blade.php`

## File Structure

```
laravel13hana/
├── app/
│   ├── Exports/
│   │   └── DynamicExport.php
│   ├── Http/Controllers/
│   │   ├── DynamicCrudController.php
│   │   ├── MenuController.php
│   │   ├── TableBuilderController.php
│   │   ├── UserController.php
│   │   ├── RoleController.php
│   │   └── PermissionController.php
│   └── Models/
│       ├── Menu.php
│       └── User.php
├── packages/custom/laravel-hana/
│   └── src/
│       ├── HanaConnection.php
│       ├── HanaConnector.php
│       ├── HanaProcessor.php
│       ├── HanaQueryGrammar.php
│       ├── HanaSchemaBuilder.php
│       ├── HanaSchemaGrammar.php
│       └── HanaServiceProvider.php
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── components/pagination.blade.php
│   ├── dynamic/ (3 files)
│   ├── menus/ (4 files)
│   ├── tables/ (1 file)
│   ├── users/ (4 files)
│   ├── roles/ (4 files)
│   └── permissions/ (4 files)
└── docs/
    ├── COMPLETE_FEATURES.md (this file)
    ├── QUICK_START.md
    ├── MENU_BUILDER_GUIDE.md
    ├── TABLE_BUILDER_GUIDE.md
    ├── USER_MANAGEMENT.md
    ├── DYNAMIC_PERMISSIONS.md
    ├── RELATIONSHIP_FEATURE.md
    ├── FILE_UPLOAD_FEATURE.md
    └── BOOLEAN_FIELD_FIX.md
```

## Database Tables

- `users` - User accounts
- `roles` - User roles
- `permissions` - Individual permissions
- `role_has_permissions` - Role-permission pivot
- `model_has_roles` - User-role pivot
- `model_has_permissions` - User-permission pivot
- `menus` - Dynamic menu definitions

## Security Features

- Password hashing (bcrypt)
- CSRF protection
- Authentication middleware
- Self-deletion protection
- Cascade deletion protection
- Role-based access control

## Known Issues & Solutions

### Boolean Fields
TINYINT fields are detected as boolean by name patterns. For existing menus, edit and save the menu again to refresh field definitions.

### Case Sensitivity
HANA stores table names in lowercase. Always use lowercase in queries.

### Array vs Object
HANA returns query results as arrays. Views handle both formats.

## Future Enhancements

- Bulk operations
- Activity logging
- API endpoints
- Rich text editor
- Advanced relationship management (hasMany, belongsToMany)
- Custom validation rules from UI
- Data import/export (CSV, JSON)
- File preview modal
- Image cropping/resizing

## Support

For issues or questions, refer to individual guide documents in `/docs` folder.
