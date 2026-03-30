# Install Audit Trail Feature

## Quick Installation Guide

Follow these steps to activate the Audit Trail feature:

### Step 1: Install Spatie Package
```bash
composer require spatie/laravel-activitylog
```

### Step 2: Publish Configuration and Migrations
```bash
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"
```

### Step 3: Run Migration
```bash
php artisan migrate
```

This will create the `activity_log` table.

### Step 4: Add batch_uuid Column (HANA Compatibility)

The Spatie migration may not properly add the `batch_uuid` column in HANA. Add it manually:

Create a temporary file `add_batch_uuid.php` in the laravel13hana directory:

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    DB::statement('ALTER TABLE "activity_log" ADD ("batch_uuid" NVARCHAR(36) NULL)');
    echo "Column batch_uuid added successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

Run it:
```bash
php add_batch_uuid.php
```

Then delete the file after successful execution.

### Step 5: Test the Feature

1. **Create a record** in any dynamic table
   - Go to any table (e.g., Categories, Products)
   - Create a new record
   - Check Activity Log in sidebar

2. **Update a record**
   - Edit any record
   - Change some fields
   - View the record's History tab to see changes

3. **Delete a record**
   - Delete a record
   - Check Activity Log to see deletion logged

4. **Bulk operations**
   - Select multiple records
   - Perform bulk delete or update
   - Check Activity Log for bulk operation entry

### Step 6: View Activity Logs

#### Global Activity Log
- Click "Activity Log" in the sidebar
- Filter by user, action, table, or date range
- See all activities across the system

#### Record History
- Go to any record detail page (click "View" button)
- Click "History" tab
- See timeline of all changes for that specific record
- View before/after values for updates

### Step 7: (Optional) Configure Cleanup

Add to `app/Console/Kernel.php` to automatically clean old logs:

```php
protected function schedule(Schedule $schedule)
{
    // Clean activity logs older than 365 days
    $schedule->command('activitylog:clean')->daily();
}
```

## What Gets Logged?

- ✅ Record creation (with all field values)
- ✅ Record updates (with before/after values)
- ✅ Record deletion (with deleted data)
- ✅ Bulk delete operations (with record IDs and count)
- ✅ Bulk update operations (with field, value, and count)

## Features Available

1. **Activity Log Page** (`/activity-log`)
   - View all activities
   - Filter by user, action, table, date
   - Search functionality
   - Pagination

2. **History Tab** (in record detail page)
   - Timeline view of changes
   - Before/after comparison
   - User attribution
   - Timestamps

3. **Sidebar Menu**
   - "Activity Log" link in System section
   - Easy access from anywhere

## Troubleshooting

### Package not found
```bash
# Make sure you're in the laravel13hana directory
cd laravel13hana
composer require spatie/laravel-activitylog
```

### Migration fails
```bash
# Check database connection
php artisan migrate:status

# Try running migration again
php artisan migrate
```

### Activity log not showing
- Clear cache: `php artisan cache:clear`
- Check if `activity_log` table exists in database
- Verify Spatie package is installed: `composer show spatie/laravel-activitylog`

## Done!

Once installed, the Audit Trail feature is fully integrated and will automatically log all CRUD operations across all dynamic tables.
