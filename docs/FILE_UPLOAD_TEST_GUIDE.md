# File Upload Feature - Testing Guide

## Prerequisites

Before testing, you MUST create the storage symbolic link:

```bash
php artisan storage:link
```

This creates a link from `public/storage` to `storage/app/public/`.

## Test Scenario 1: Product with Image

### Step 1: Create Table
1. Go to "Create Table" in sidebar
2. Table name: `test_products`
3. Add fields:
   - Field 1: `name` (String, required)
   - Field 2: `price` (Decimal, nullable)
   - Field 3: `photo` (Image, nullable)
   - Field 4: `description` (Text, nullable)
4. Click "Create Table"

### Step 2: Create Menu
1. Go to "Menu Management"
2. Click "Create Menu"
3. Select table: `test_products`
4. Menu name: `Test Products`
5. Icon: `bi-image`
6. Click "Create Menu"

### Step 3: Test Create
1. Click "Test Products" in sidebar
2. Click "Add New"
3. Fill in:
   - Name: "Sample Product"
   - Price: 99.99
   - Photo: Upload an image (JPG/PNG)
   - Description: "Test description"
4. Verify image preview appears
5. Click "Create"

### Step 4: Verify List View
1. Check the list view
2. Verify image appears as thumbnail (50x50px)
3. Verify other fields display correctly

### Step 5: Test Edit
1. Click "Edit" button
2. Verify current image displays
3. Upload a different image
4. Verify new image preview appears
5. Click "Update"
6. Verify new image replaced old one
7. Check `storage/app/public/images/` - old file should be deleted

### Step 6: Test Delete
1. Click "Delete" button
2. Confirm deletion
3. Check `storage/app/public/images/` - file should be deleted

---

## Test Scenario 2: Document with File

### Step 1: Create Table
1. Go to "Create Table"
2. Table name: `test_documents`
3. Add fields:
   - Field 1: `title` (String, required)
   - Field 2: `file` (File, required)
   - Field 3: `uploaded_at` (Date, nullable)
4. Click "Create Table"

### Step 2: Create Menu
1. Go to "Menu Management"
2. Click "Create Menu"
3. Select table: `test_documents`
4. Menu name: `Test Documents`
5. Icon: `bi-file-earmark`
6. Click "Create Menu"

### Step 3: Test Create
1. Click "Test Documents" in sidebar
2. Click "Add New"
3. Fill in:
   - Title: "Sample Document"
   - File: Upload a PDF or any file
   - Uploaded at: Today's date
4. Click "Create"

### Step 4: Verify List View
1. Check the list view
2. Verify download button appears
3. Click download button - file should download

### Step 5: Test Edit
1. Click "Edit" button
2. Verify current file displays with download link
3. Upload a different file
4. Click "Update"
5. Verify new file replaced old one

---

## Test Scenario 3: Employee with Avatar

### Step 1: Create Table
1. Table name: `test_employees`
2. Add fields:
   - `name` (String, required)
   - `email` (String, required)
   - `avatar` (Image, nullable)
   - `resume` (File, nullable)
   - `is_active` (Boolean, default: 1)

### Step 2: Create Menu
1. Menu name: `Test Employees`
2. Icon: `bi-people`

### Step 3: Test Multiple Files
1. Create employee with both avatar and resume
2. Verify both files upload correctly
3. Edit and replace only avatar (leave resume)
4. Verify resume kept, avatar replaced
5. Delete employee
6. Verify both files deleted from storage

---

## Validation Tests

### Test 1: Image Type Validation
1. Try uploading a PDF as image
2. Should show error: "The field must be an image file"

### Test 2: Image Size Validation
1. Try uploading image > 2MB
2. Should show error: "The field file size must not exceed 2048 KB"

### Test 3: File Size Validation
1. Try uploading file > 5MB
2. Should show error: "The field file size must not exceed 5120 KB"

### Test 4: Required Field
1. Create table with required image field
2. Try submitting without image
3. Should show error: "The field is required"

### Test 5: Optional Field
1. Create table with nullable image field
2. Submit without image
3. Should save successfully

---

## Edge Cases

### Test 1: Edit Without Changing File
1. Edit record
2. Don't upload new file
3. Update other fields
4. Verify old file kept

### Test 2: Multiple Edits
1. Create record with file
2. Edit and replace file (File A → File B)
3. Edit again and replace file (File B → File C)
4. Verify only File C exists in storage

### Test 3: Special Characters in Filename
1. Upload file with special characters: `test file (1) [copy].jpg`
2. Verify Laravel sanitizes filename
3. Verify file accessible

---

## Troubleshooting

### Issue: "File not found" error
**Solution:**
```bash
php artisan storage:link
```

### Issue: Upload fails silently
**Check:**
1. PHP upload_max_filesize in php.ini
2. PHP post_max_size in php.ini
3. Disk space available
4. Directory permissions (storage/app/public should be writable)

### Issue: Image doesn't display
**Check:**
1. Storage link exists: `public/storage` → `storage/app/public`
2. File path in database is correct
3. File exists in storage/app/public/images/

### Issue: Old files not deleted
**Check:**
1. File path in database matches actual location
2. Storage disk configuration in config/filesystems.php

---

## Expected Results

### Storage Structure After Tests
```
storage/
  app/
    public/
      images/
        xyz123abc456.jpg
        def789ghi012.png
      files/
        abc123def456.pdf
        ghi789jkl012.docx
```

### Database Records
File paths stored as:
- `images/xyz123abc456.jpg`
- `files/abc123def456.pdf`

### List View Display
- Images: Thumbnail with img tag
- Files: Download button with icon

---

## Performance Check

### Test with Multiple Files
1. Create 10 records with images
2. Check list view load time
3. Check storage disk usage
4. Verify pagination works

### Test with Large Files
1. Upload image close to 2MB limit
2. Upload file close to 5MB limit
3. Verify upload completes
4. Check response time

---

## Cleanup After Testing

### Remove Test Tables
```sql
DROP TABLE test_products;
DROP TABLE test_documents;
DROP TABLE test_employees;
```

### Remove Test Menus
1. Go to Menu Management
2. Delete test menus
3. Permissions auto-deleted

### Clean Storage
```bash
# Remove test files
rm -rf storage/app/public/images/*
rm -rf storage/app/public/files/*
```

---

## Success Criteria

✓ Storage link created
✓ Image upload works
✓ File upload works
✓ Image preview in forms
✓ Thumbnails in list view
✓ Download links work
✓ Old files deleted on update
✓ Files deleted with record
✓ Validation works correctly
✓ Edit without file change works
✓ Multiple file types supported

---

## Next Steps After Testing

If all tests pass:
1. Document any issues found
2. Adjust file size limits if needed
3. Consider adding more file types
4. Plan Phase 2 features (image resize, preview modal, etc.)

If tests fail:
1. Check error logs: `storage/logs/laravel.log`
2. Verify storage link exists
3. Check file permissions
4. Review validation rules
5. Test with different file types
