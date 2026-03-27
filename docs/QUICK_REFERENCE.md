# Quick Reference - Phase 1 Features

## Setup Commands

```bash
# Install Excel package
composer require maatwebsite/excel

# Create storage link (REQUIRED for file uploads)
php artisan storage:link

# Run migrations
php artisan migrate:fresh --seed
```

---

## Feature 1: Search & Filtering

**Usage:**
1. Go to any dynamic CRUD list view
2. Enter search term in search box
3. Click "Search" button
4. Click "Clear" to reset

**Features:**
- Case-insensitive
- Searches all text/number fields
- Searches related table names
- Preserves with pagination

---

## Feature 2: Column Sorting

**Usage:**
1. Click any column header
2. Click again to toggle asc/desc
3. Visual arrows show sort direction

**Features:**
- Works on all columns
- Preserves with search/pagination
- Visual indicators (↑↓)

---

## Feature 3: Export to Excel

**Usage:**
1. Go to any dynamic CRUD list view
2. Click "Export Excel" button (top right)
3. File downloads automatically

**Features:**
- Exports current view (with filters)
- Related data shows names
- Auto-sized columns
- Filename: `{menu}_{datetime}.xlsx`

---

## Feature 4: Advanced Validation

**Setup:**
1. Create table via Table Builder
2. Validation rules auto-applied by type

**Validation Types:**
- **Numeric**: min/max value
- **String**: min/max length, email, url, unique
- **Date**: date format
- **File**: type, size
- **Image**: type, size

**Custom Messages:**
- Automatically generated
- Field-specific

---

## Feature 5: File Upload

**Setup:**
```bash
php artisan storage:link
```

**Create Table with File Field:**
1. Go to "Create Table"
2. Add field with type "File" or "Image"
3. Create table
4. Create menu

**Usage:**
- **Create**: Upload file via file input
- **Edit**: See current file, upload new to replace
- **List**: View thumbnails (images) or download (files)
- **Delete**: Files auto-deleted

**Limits:**
- Images: 2MB (JPEG, PNG, JPG, GIF, WEBP)
- Files: 5MB (any type)

**Storage:**
- Images: `storage/app/public/images/`
- Files: `storage/app/public/files/`

---

## File Types Reference

### Table Builder Field Types
- String (VARCHAR)
- Text (NCLOB)
- Integer
- Decimal
- Boolean (TINYINT)
- Date
- DateTime
- Timestamp
- **File** ← NEW
- **Image** ← NEW

---

## Common Tasks

### Create Table with Image
```
1. Create Table
2. Add field: "photo" (Image, nullable)
3. Create table
4. Create menu
5. Upload images in CRUD
```

### Create Table with File
```
1. Create Table
2. Add field: "document" (File, nullable)
3. Create table
4. Create menu
5. Upload files in CRUD
```

### Export Filtered Data
```
1. Search for records
2. Sort by column
3. Click "Export Excel"
4. Filtered data exported
```

---

## Troubleshooting

### File Upload Not Working
```bash
# Run this command
php artisan storage:link

# Check if link exists
ls -la public/storage
```

### Upload Size Limit
Edit `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Search Not Working
- Check HANA connection
- Verify table has data
- Try exact match first

### Export Button Missing
- Ensure table has data
- Check if maatwebsite/excel installed

---

## File Locations

### Controllers
- `app/Http/Controllers/DynamicCrudController.php`
- `app/Http/Controllers/MenuController.php`
- `app/Http/Controllers/TableBuilderController.php`

### Views
- `resources/views/dynamic/index.blade.php`
- `resources/views/dynamic/create.blade.php`
- `resources/views/dynamic/edit.blade.php`

### Exports
- `app/Exports/DynamicExport.php`

### Documentation
- `docs/COMPLETE_FEATURES.md`
- `docs/FILE_UPLOAD_FEATURE.md`
- `docs/PHASE1_ADVANCED_FEATURES.md`
- `FILE_UPLOAD_TEST_GUIDE.md`

---

## Testing Checklist

### Search
- [ ] Search finds records
- [ ] Case-insensitive works
- [ ] Search in related tables
- [ ] Clear button works

### Sorting
- [ ] Click header sorts
- [ ] Toggle asc/desc
- [ ] Arrows show direction
- [ ] Works with search

### Export
- [ ] Button appears
- [ ] File downloads
- [ ] Correct data exported
- [ ] Related names shown

### Validation
- [ ] Required fields validated
- [ ] Type validation works
- [ ] Custom messages show
- [ ] Unique validation works

### File Upload
- [ ] Storage link created
- [ ] Image upload works
- [ ] File upload works
- [ ] Preview shows
- [ ] Thumbnails display
- [ ] Download works
- [ ] Old files deleted
- [ ] Files deleted with record

---

## Quick Tips

1. **Always run** `php artisan storage:link` before using file uploads
2. **Search is case-insensitive** - "ele" finds "Electronics"
3. **Export respects filters** - search/sort before export
4. **Validation is automatic** - based on field type
5. **Files auto-deleted** - on update and record deletion
6. **Related data shows names** - not IDs in list view
7. **Pagination preserves** - search and sort parameters

---

## Support

**Documentation:**
- `docs/` folder - Complete guides
- `FILE_UPLOAD_TEST_GUIDE.md` - Testing steps
- `IMPLEMENTATION_SUMMARY.md` - Technical details

**Logs:**
- `storage/logs/laravel.log` - Application logs

**Commands:**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check storage link
ls -la public/storage

# Check permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

---

## Phase 2 Preview

Coming soon:
- Bulk operations
- Advanced filters
- Activity logging
- Rich text editor
- Image resize
- File preview modal
- Drag & drop upload
- Progress bars

---

**Version:** 1.0
**Date:** March 26, 2026
**Status:** Production Ready
