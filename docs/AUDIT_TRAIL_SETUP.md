# Audit Trail Setup Guide

## Using Spatie Laravel Activitylog

We use [Spatie Laravel Activitylog](https://github.com/spatie/laravel-activitylog) for comprehensive audit trail functionality.

## Installation Steps

### 1. Install Package

```bash
composer require spatie/laravel-activitylog
```

### 2. Publish Configuration & Migration

```bash
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"
```

### 3. Run Migration

```bash
php artisan migrate
```

This will create the `activity_log` table with columns:
- id
- log_name
- description
- subject_type, subject_id (the model being logged)
- causer_type, causer_id (the user who made the change)
- properties (JSON - stores old/new values)
- created_at, updated_at

### 4. Configure Models

For automatic logging, add trait to models. Since we use dynamic tables, we'll implement logging in the controller instead.

## Implementation in Dynamic CRUD

### Logging Strategy

We'll log activities in DynamicCrudController for:
- **CREATE**: Log when new record is created
- **UPDATE**: Log with before/after values
- **DELETE**: Log with deleted record data
- **BULK_DELETE**: Log bulk operations
- **BULK_UPDATE**: Log bulk updates

### Log Format

```php
activity()
    ->performedOn($model)
    ->causedBy(auth()->user())
    ->withProperties([
        'table' => 'products',
        'menu_id' => 1,
        'old' => [...],  // For updates
        'new' => [...]   // For updates
    ])
    ->log('created');
```

## Features

### 1. Activity Log Page
- View all activities across all tables
- Filter by: user, action, date range, table
- Search functionality
- Pagination

### 2. History Tab in Detail Page
- View all changes for specific record
- Show before/after values
- Timeline view
- User who made changes

### 3. Activity Types
- `created` - New record created
- `updated` - Record modified (with changes)
- `deleted` - Record deleted
- `bulk_deleted` - Multiple records deleted
- `bulk_updated` - Multiple records updated

## Usage Examples

### View All Activities
```
Navigate to: /activity-log
```

### View Record History
```
Navigate to: /crud/{menu}/{id}
Click on "History" tab
```

### Filter Activities
```
- By User: Select user from dropdown
- By Action: created, updated, deleted
- By Date: Date range picker
- By Table: Select table/menu
```

## Configuration Options

Edit `config/activitylog.php`:

```php
return [
    // Enable/disable logging
    'enabled' => env('ACTIVITY_LOG_ENABLED', true),
    
    // Table name
    'table_name' => 'activity_log',
    
    // Delete old logs after X days
    'delete_records_older_than_days' => 365,
    
    // Log name
    'default_log_name' => 'default',
];
```

## Performance Considerations

1. **Index the table** - Add indexes on subject_type, subject_id, causer_id
2. **Clean old logs** - Schedule command to delete old logs
3. **Async logging** - Use queues for logging (optional)

## Cleanup Command

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('activitylog:clean')->daily();
}
```

## Security

- Only authenticated users can view activity logs
- Sensitive data should be excluded from logging
- Use permissions to restrict access

## Next Steps

After installation:
1. Test logging by creating/updating/deleting records
2. Check activity_log table for entries
3. View logs in Activity Log page
4. Check History tab in detail pages
