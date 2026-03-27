# Relationship Feature Documentation

## Overview

The application now automatically detects and handles relationships between tables based on foreign key naming conventions.

## How It Works

### 1. Auto-Detection

When you create a menu, the system automatically:
- Detects columns ending with `_id` (e.g., `user_id`, `category_id`)
- Guesses the related table name (e.g., `user_id` → `users`)
- Determines the best display column (name, title, email, etc.)
- Stores relationship configuration in the menu

### 2. Foreign Key Naming Convention

The system follows Laravel's naming convention:
- `user_id` → looks for `users` table
- `category_id` → looks for `categories` table
- `product_id` → looks for `products` table

### 3. Display Column Priority

When showing related data, the system looks for columns in this order:
1. `name`
2. `title`
3. `label`
4. `email`
5. `username`
6. `code`
7. Second column (if none of the above exist)

## Features

### Dropdown Selection (Create/Edit)

Foreign key fields automatically render as dropdowns instead of text inputs:
- Shows meaningful data (names, titles) instead of IDs
- Validates that selected ID exists in related table
- Supports nullable foreign keys (optional selection)

### Display Related Data (List View)

In the index/list view:
- Shows related data instead of raw IDs
- Example: Shows "John Doe" instead of user_id "1"
- Automatically joins related tables

### Validation

Foreign key fields are validated to ensure:
- Required fields must have a value
- Selected ID must exist in related table
- Nullable fields can be left empty

## Example

### Table Structure

```sql
CREATE TABLE categories (
    id BIGINT PRIMARY KEY,
    name NVARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE products (
    id BIGINT PRIMARY KEY,
    name NVARCHAR(255),
    category_id BIGINT,  -- Foreign key
    price DECIMAL(10,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Auto-Detected Relationship

When creating a menu for `products` table:

```json
{
    "foreign_key": "category_id",
    "related_table": "categories",
    "display_column": "name",
    "type": "belongsTo"
}
```

### Result

**Create/Edit Form:**
- `category_id` renders as dropdown
- Shows category names, not IDs
- User selects "Electronics" instead of typing "1"

**List View:**
- Shows "Electronics" in category column
- Not "1" or "category_id: 1"

## Supported Relationship Types

Currently supports:
- **belongsTo** - Many-to-one relationships (e.g., Product belongs to Category)

## Limitations

Current implementation does NOT support:
- hasMany relationships (one-to-many)
- belongsToMany relationships (many-to-many)
- Polymorphic relationships
- Nested relationships (relationship of relationships)
- Custom foreign key names (must end with `_id`)

## Manual Relationship Configuration

Relationships are stored in the `menus` table as JSON:

```json
[
    {
        "foreign_key": "user_id",
        "related_table": "users",
        "display_column": "name",
        "type": "belongsTo"
    },
    {
        "foreign_key": "category_id",
        "related_table": "categories",
        "display_column": "name",
        "type": "belongsTo"
    }
]
```

You can manually edit this in the database if auto-detection fails.

## Troubleshooting

### Foreign Key Not Detected

**Problem:** Column ending with `_id` not showing as dropdown

**Solutions:**
1. Check if related table exists
2. Verify table name follows convention (plural form)
3. Edit menu and save again to re-detect relationships
4. Manually add relationship in database

### Wrong Display Column

**Problem:** Dropdown shows IDs or wrong data

**Solutions:**
1. Check if related table has `name`, `title`, or `email` column
2. Manually update `display_column` in menu's relationships JSON
3. Edit menu and save to re-detect

### Related Data Not Showing in List

**Problem:** List view still shows IDs instead of names

**Solutions:**
1. Check if relationship is properly configured
2. Verify related table and display column exist
3. Check for typos in table/column names

## Future Enhancements

Planned features:
- hasMany relationship support
- belongsToMany (many-to-many) support
- Relationship configuration UI
- Custom foreign key names
- Nested relationship display
- Eager loading optimization
- Cascade delete options

## Technical Details

### Database Schema

```sql
ALTER TABLE menus ADD COLUMN relationships JSON;
```

### Menu Model Methods

- `getRelationships()` - Get all relationships
- `isForeignKey($fieldName)` - Check if field is FK
- `getRelationshipForField($fieldName)` - Get FK config

### Controller Methods

- `detectRelationships()` - Auto-detect FKs
- `guessRelatedTable()` - Find related table
- `guessDisplayColumn()` - Find best display column
- `getRelatedData()` - Fetch related records for dropdowns

## Testing

To test the relationship feature:

1. Create a table with foreign key:
```sql
CREATE TABLE orders (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    total DECIMAL(10,2),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

2. Create menu for `orders` table
3. Check that `user_id` shows as dropdown
4. Create an order and verify user selection works
5. View orders list and verify user names display

## Notes

- Relationships are detected when menu is created or updated
- Existing menus need to be edited and saved to detect relationships
- Foreign keys must follow `{table}_id` naming convention
- Related tables must exist before creating the menu
