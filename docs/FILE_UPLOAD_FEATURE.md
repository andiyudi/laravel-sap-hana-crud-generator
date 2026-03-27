# File Upload Feature

## Overview
The Dynamic CRUD system now supports file and image uploads with automatic storage management, validation, and display.

## Features

### 1. Field Types
- **Image**: For image files (JPEG, PNG, JPG, GIF, WEBP)
  - Max size: 2MB
  - Stored in `storage/app/public/images/`
  - Automatic thumbnail display in list view
  - Image preview in create/edit forms

- **File**: For any file type
  - Max size: 5MB
  - Stored in `storage/app/public/files/`
  - Download link in list view

### 2. Automatic Features
- **Validation**: Type-specific validation rules
- **Storage**: Files stored in Laravel's public disk
- **Old File Deletion**: Previous files automatically deleted on update
- **File Cleanup**: Files deleted when record is deleted

### 3. How to Use

#### Step 1: Create Storage Link
Before using file uploads, create a symbolic link:
```bash
php artisan storage:link
```

This creates a link from `public/storage` to `storage/app/public`.

#### Step 2: Add File/Image Field to Table
When creating a table via Table Builder:
1. Add a new field
2. Set field name (e.g., `photo`, `document`, `avatar`)
3. Select type: **File** or **Image**
4. Set nullable if optional
5. Create table

#### Step 3: Create Menu
Create a menu for the table as usual. The system will automatically detect file/image fields.

#### Step 4: Use the CRUD
- **Create**: Upload files via file input
- **Edit**: See current file/image, upload new one to replace
- **List**: View thumbnails (images) or download links (files)
- **Delete**: Files automatically deleted with record

## Technical Details

### Storage Structure
```
storage/
  app/
    public/
      images/          # Image uploads
        xyz123.jpg
      files/           # File uploads
        abc456.pdf
```

### Database Storage
File paths are stored as strings in the database:
- Example: `images/xyz123.jpg`
- Example: `files/abc456.pdf`

### Validation Rules
**Image Fields:**
- `image` - Must be an image file
- `mimes:jpeg,png,jpg,gif,webp` - Allowed formats
- `max:2048` - Max 2MB (in KB)

**File Fields:**
- `file` - Must be a file
- `max:5120` - Max 5MB (in KB)

### Controller Methods

#### buildValidationRules()
Builds validation rules including file/image validation:
```php
case 'image':
    $fieldRules[] = 'image';
    $fieldRules[] = 'mimes:jpeg,png,jpg,gif,webp';
    $fieldRules[] = 'max:2048';
    break;
```

#### handleFileUploads()
Handles file upload, storage, and old file deletion:
```php
if ($request->hasFile($field['name'])) {
    // Delete old file
    if ($oldRecord && $oldPath) {
        Storage::disk('public')->delete($oldPath);
    }
    
    // Upload new file
    $path = $file->store($folder, 'public');
    $validated[$field['name']] = $path;
}
```

### View Display

#### Create Form
```blade
@elseif($field['type'] === 'image')
    <input type="file" accept="image/*" onchange="previewImage(this)">
    <div id="preview"></div>
@endif
```

#### Edit Form
```blade
@if($fieldValue)
    <img src="{{ asset('storage/' . $fieldValue) }}">
@endif
<input type="file" accept="image/*">
```

#### List View
```blade
@elseif ($field['type'] === 'image')
    <img src="{{ asset('storage/' . $value) }}" class="img-thumbnail">
@elseif ($field['type'] === 'file')
    <a href="{{ asset('storage/' . $value) }}">Download</a>
@endif
```

## Example Use Cases

### 1. Product Catalog
- Table: `products`
- Fields:
  - `name` (text)
  - `price` (decimal)
  - `photo` (image) - Product image
  - `manual` (file) - PDF manual

### 2. Employee Directory
- Table: `employees`
- Fields:
  - `name` (text)
  - `position` (text)
  - `avatar` (image) - Profile photo
  - `resume` (file) - CV document

### 3. Document Management
- Table: `documents`
- Fields:
  - `title` (text)
  - `category_id` (integer)
  - `file` (file) - Document file
  - `thumbnail` (image) - Preview image

## Security Considerations

1. **File Validation**: Always validate file types and sizes
2. **Storage Location**: Files stored outside web root by default
3. **Access Control**: Use Laravel's authorization for file access
4. **File Names**: Laravel generates unique names to prevent conflicts

## Troubleshooting

### Issue: Files not displaying
**Solution**: Run `php artisan storage:link`

### Issue: Upload fails
**Check:**
- File size limits in `php.ini`
- Disk space available
- Directory permissions

### Issue: Old files not deleted
**Check:**
- Storage disk configuration
- File path in database matches actual location

## Configuration

### Change Upload Limits

**In Controller:**
```php
// For images
$fieldRules[] = 'max:5120'; // 5MB instead of 2MB

// For files
$fieldRules[] = 'max:10240'; // 10MB instead of 5MB
```

### Change Storage Location

**In Controller:**
```php
// Change folder
$folder = $field['type'] === 'image' ? 'uploads/images' : 'uploads/files';
```

### Add More Image Formats

**In Controller:**
```php
$fieldRules[] = 'mimes:jpeg,png,jpg,gif,webp,svg,bmp';
```

## Best Practices

1. **Always create storage link** before using file uploads
2. **Set appropriate file size limits** based on your needs
3. **Use image type** for photos to get automatic thumbnails
4. **Use file type** for documents, PDFs, etc.
5. **Make fields nullable** if uploads are optional
6. **Test file deletion** to ensure no orphaned files

## Related Files

- `app/Http/Controllers/DynamicCrudController.php` - Main logic
- `resources/views/dynamic/create.blade.php` - Create form
- `resources/views/dynamic/edit.blade.php` - Edit form
- `resources/views/dynamic/index.blade.php` - List view
- `resources/views/tables/create.blade.php` - Table builder
