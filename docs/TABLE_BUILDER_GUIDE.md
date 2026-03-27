# Table Builder - Create Tables from UI

## Overview

**Table Builder** adalah fitur baru yang memungkinkan Anda membuat tabel database langsung dari UI tanpa perlu menulis migration atau SQL!

## Status: ✅ IMPLEMENTED

## Cara Menggunakan

### Step 1: Akses Table Builder

1. Login ke aplikasi
2. Di sidebar, klik **"Create Table"** (bagian Menu Builder)

### Step 2: Buat Tabel

1. **Masukkan Nama Tabel**
   - Contoh: `products`, `categories`, `orders`
   - Gunakan lowercase dan underscore
   - Hindari spasi dan karakter khusus

2. **Tambah Fields**
   - Klik tombol "Add Field"
   - Isi informasi field:
     - **Field Name**: nama kolom (e.g., `name`, `price`, `description`)
     - **Type**: tipe data (string, text, integer, dll)
     - **Length**: panjang karakter (untuk string)
     - **Default Value**: nilai default (opsional)
     - **Nullable**: boleh kosong atau tidak
     - **Unique**: harus unik atau tidak

3. **Tambah Field Lainnya**
   - Klik "Add Field" lagi untuk menambah field baru
   - Ulangi sampai semua field selesai

4. **Klik "Create Table"**

### Step 3: Buat Menu

Setelah tabel dibuat, Anda akan diarahkan ke halaman Create Menu untuk membuat menu untuk tabel tersebut.

## Contoh: Membuat Tabel Products

### Input:

**Table Name:** `products`

**Fields:**

1. **Field #1:**
   - Name: `name`
   - Type: String
   - Length: 255
   - Nullable: ✗
   - Unique: ✗

2. **Field #2:**
   - Name: `description`
   - Type: Text
   - Nullable: ✓

3. **Field #3:**
   - Name: `price`
   - Type: Decimal
   - Nullable: ✗
   - Default: 0

4. **Field #4:**
   - Name: `stock`
   - Type: Integer
   - Nullable: ✗
   - Default: 0

5. **Field #5:**
   - Name: `sku`
   - Type: String
   - Length: 100
   - Nullable: ✗
   - Unique: ✓

6. **Field #6:**
   - Name: `is_active`
   - Type: Boolean
   - Default: true

### Output:

Tabel `products` dibuat dengan struktur:
```sql
CREATE TABLE products (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    sku VARCHAR(100) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Field Types

### String
- Short text (max 255 characters by default)
- Untuk: nama, email, SKU, kode
- Bisa set custom length

### Text
- Long text (unlimited)
- Untuk: deskripsi, catatan, konten
- Tidak perlu set length

### Integer
- Whole numbers
- Untuk: quantity, stock, age, count
- Range: -2,147,483,648 to 2,147,483,647

### Decimal
- Numbers with decimals (10 digits, 2 decimal places)
- Untuk: price, weight, percentage
- Format: 12345678.90

### Boolean
- True/False values
- Untuk: is_active, is_published, is_featured
- Stored as 0 or 1

### Date
- Date only (YYYY-MM-DD)
- Untuk: birth_date, publish_date
- No time component

### Datetime
- Date and time
- Untuk: event_date, scheduled_at
- Format: YYYY-MM-DD HH:MM:SS

### Timestamp
- Auto-updating timestamp
- Untuk: created_at, updated_at
- Automatically managed

## Field Properties

### Nullable
- ✓ Checked: Field can be empty (NULL)
- ✗ Unchecked: Field is required (NOT NULL)

### Unique
- ✓ Checked: Value must be unique across all records
- ✗ Unchecked: Duplicate values allowed

### Default Value
- Value to use if not provided
- Examples:
  - String: `"pending"`
  - Integer: `0`
  - Boolean: `true` or `false`
  - Date: `CURRENT_DATE`

### Length
- Only for String type
- Default: 255
- Max recommended: 65,535

## Automatic Fields

These fields are added automatically:
- `id` - Primary key (auto-increment)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## Complete Workflow

### 1. Create Table
```
Sidebar → Create Table
↓
Fill table name: "products"
↓
Add fields (name, price, stock, etc.)
↓
Click "Create Table"
```

### 2. Create Menu
```
Redirected to Create Menu
↓
Fill menu name: "Products"
↓
Select table: "PRODUCTS"
↓
Set icon: "bi-box-seam"
↓
Click "Create Menu"
```

### 3. Use Menu
```
Menu appears in sidebar
↓
Click menu to manage data
↓
Add/Edit/Delete products
```

## Examples

### Example 1: Categories Table

**Table Name:** `categories`

**Fields:**
- `name` (string, 255, required, unique)
- `slug` (string, 255, required, unique)
- `description` (text, nullable)
- `parent_id` (integer, nullable)
- `order` (integer, default: 0)
- `is_active` (boolean, default: true)

### Example 2: Orders Table

**Table Name:** `orders`

**Fields:**
- `order_number` (string, 50, required, unique)
- `customer_name` (string, 255, required)
- `customer_email` (string, 255, required)
- `total_amount` (decimal, required)
- `status` (string, 50, default: "pending")
- `order_date` (datetime, required)
- `notes` (text, nullable)

### Example 3: Blog Posts Table

**Table Name:** `posts`

**Fields:**
- `title` (string, 255, required)
- `slug` (string, 255, required, unique)
- `content` (text, required)
- `excerpt` (text, nullable)
- `author` (string, 255, required)
- `published_at` (datetime, nullable)
- `is_published` (boolean, default: false)
- `view_count` (integer, default: 0)

## Tips & Best Practices

### Naming Conventions
✅ Use lowercase: `products`, `categories`
✅ Use underscores: `order_items`, `user_profiles`
✅ Use plural for tables: `products` not `product`
✅ Use singular for fields: `name` not `names`

❌ Avoid spaces: `my products` ✗
❌ Avoid uppercase: `Products` ✗
❌ Avoid special chars: `products@2024` ✗

### Field Design
- Keep field names short and descriptive
- Use appropriate types for data
- Set nullable only when truly optional
- Use unique for identifiers (SKU, email, slug)
- Provide sensible defaults

### Performance
- Don't create too many fields (max 20-30)
- Use indexes for frequently searched fields
- Use appropriate field types (don't use text for short strings)

## Troubleshooting

### Table name already exists
- Choose a different name
- Or drop the existing table first (be careful!)

### Invalid field name
- Use only lowercase letters and underscores
- Start with a letter, not a number
- Avoid SQL reserved words (select, from, where, etc.)

### Table creation failed
- Check database connection
- Verify user has CREATE TABLE permission
- Check error message for details

## Limitations

Current limitations:
- Cannot add foreign keys (relationships)
- Cannot add indexes (except unique)
- Cannot modify table after creation
- Cannot add custom constraints

For advanced features, use migrations.

## Next Steps

After creating a table:
1. ✅ Create a menu for it
2. ✅ Start adding data via UI
3. ✅ Permissions are created automatically
4. ✅ No coding needed!

## Conclusion

Table Builder membuat proses pembuatan tabel menjadi sangat mudah:
- ✅ No SQL knowledge needed
- ✅ Visual interface
- ✅ Instant results
- ✅ Integrated with Menu Management

**Sekarang Anda bisa membuat tabel dan menu langsung dari UI!** 🎉

---

**Status**: ✅ FULLY IMPLEMENTED

**Date**: March 26, 2026
