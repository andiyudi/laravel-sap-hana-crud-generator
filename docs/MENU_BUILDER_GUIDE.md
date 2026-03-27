# Menu Builder - Quick Start Guide

## What is Menu Builder?

Menu Builder is a powerful feature that allows you to create CRUD (Create, Read, Update, Delete) interfaces for any database table without writing code. Simply select a table, configure the menu, and you're ready to manage data!

## Getting Started

### Step 1: Access Menu Management

1. Login to your application
2. Look for "Menu Builder" section in the sidebar
3. Click on "Manage Menus"

### Step 2: Create Your First Menu

1. Click the "Create Menu" button
2. Fill in the form:
   - **Menu Name**: Display name (e.g., "Products", "Categories")
   - **Database Table**: Select from dropdown (e.g., "PRODUCTS")
   - **Icon**: Bootstrap icon class (e.g., "bi-box-seam")
   - **Order**: Display order in sidebar (lower = higher position)
   - **Active**: Check to show in sidebar

3. Click "Create Menu"

### Step 3: Use Your New Menu

1. The menu will appear in the "Dynamic Menus" section of the sidebar
2. Click on it to see the list of records
3. Use the "Add New" button to create records
4. Edit or delete records using the action buttons

## Example: Creating a Products Menu

Let's create a menu for managing products:

### Configuration:
- **Menu Name**: Products
- **Database Table**: PRODUCTS
- **Icon**: bi-box-seam
- **Order**: 1
- **Active**: ✓ Checked

### Result:
A "Products" menu appears in the sidebar with:
- List view showing all products
- Create form with all product fields
- Edit form for updating products
- Delete functionality

## Supported Field Types

The system automatically detects field types and creates appropriate form inputs:

| Field Type | Form Input | Example |
|-----------|-----------|---------|
| Integer | Number input | Product ID, Quantity |
| Varchar | Text input | Product Name, SKU |
| Text | Textarea | Description |
| Date | Date picker | Created Date |
| Datetime | Datetime picker | Last Updated |
| Boolean | Checkbox | Is Active |

## Tips & Best Practices

### Choosing Icons
- Browse Bootstrap Icons: https://icons.getbootstrap.com/
- Common icons:
  - `bi-box-seam` - Products
  - `bi-people` - Customers
  - `bi-cart` - Orders
  - `bi-tags` - Categories
  - `bi-file-text` - Documents

### Menu Ordering
- Use increments of 10 (10, 20, 30) to allow easy reordering
- Lower numbers appear first in the sidebar
- Group related menus with similar numbers

### Table Requirements
- Table must have an `id` column (primary key)
- Recommended: `created_at` and `updated_at` columns
- Avoid tables with complex relationships for simple CRUD

## Managing Menus

### View Menu Details
1. Go to Menu Management
2. Click the eye icon (👁️) to view details
3. See all field definitions and settings

### Edit Menu
1. Click the pencil icon (✏️)
2. Update settings as needed
3. Save changes

### Delete Menu
1. Click the trash icon (🗑️)
2. Confirm deletion
3. Menu is removed from sidebar

**Note**: Deleting a menu does NOT delete the database table or data!

## Troubleshooting

### Menu doesn't appear in sidebar
- Check that "Active" is enabled
- Verify the table exists in database
- Clear cache: `php artisan view:clear`

### Form fields not showing correctly
- Check field types in database
- Verify table has columns
- View menu details to see field definitions

### Can't create/edit records
- Check field validation (required fields)
- Ensure database connection is working
- Check for unique constraints

## Advanced Usage

### Working with Existing Tables
The Menu Builder works with any existing table:
- User-created tables
- Migration-generated tables
- Legacy database tables

### Multiple Menus
You can create multiple menus:
- One menu per table
- Different menus for different user roles (future feature)
- Organize by business function

## What's Next?

After creating menus, you can:
1. Manage data through the UI
2. Create more menus for other tables
3. Customize menu order and icons
4. Enable/disable menus as needed

## Need Help?

- Check the field definitions in menu details
- Review the MENU_MANAGEMENT_SUMMARY.md for technical details
- Ensure your database table structure is correct

---

**Happy Building! 🚀**

The Menu Builder makes it easy to create admin interfaces without coding. Start by creating a menu for your most-used table and expand from there!
