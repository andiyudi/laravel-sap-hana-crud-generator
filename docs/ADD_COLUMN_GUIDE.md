# Add Column to Existing Table - Guide

## Overview
Fitur ini memungkinkan Anda menambahkan kolom baru ke tabel yang sudah ada melalui UI, tanpa perlu menulis migration manual.

## Cara Menggunakan

### Langkah 1: Akses Add Column
1. Login ke aplikasi
2. Klik **"Add Column"** di sidebar (Menu Builder section)

### Langkah 2: Pilih Tabel
1. Pilih tabel yang ingin ditambahkan kolom dari dropdown
2. Sistem akan otomatis load daftar kolom yang sudah ada

### Langkah 3: Isi Detail Kolom Baru
- **Column Name**: Nama kolom (lowercase, underscore)
- **Type**: Tipe data (String, Text, Integer, Decimal, Boolean, Date, Datetime, Timestamp, File, Image)
- **Length**: Panjang (untuk String, default 255)
- **Insert After Column**: Posisi kolom (opsional, default di akhir)
- **Default Value**: Nilai default (opsional)
- **Nullable**: Centang jika boleh kosong
- **Unique**: Centang jika harus unik

### Langkah 4: Submit
1. Klik **"Add Column"**
2. Kolom akan ditambahkan ke database

### Langkah 5: Refresh Menu (PENTING!)
Setelah kolom ditambahkan, Anda HARUS refresh menu agar field baru muncul di form:

1. Klik **"Manage Menus"** di sidebar
2. Cari menu untuk tabel yang baru ditambahkan kolom
3. Klik tombol **"Edit"** (icon pensil)
4. **JANGAN UBAH APAPUN**, langsung klik **"Update"**
5. Sistem akan otomatis refresh field definitions

### Langkah 6: Test
1. Klik menu tabel di sidebar
2. Klik **"Add New"** atau **"Edit"**
3. Field baru seharusnya sudah muncul!

---

## Contoh Use Case

### Contoh 1: Tambah Icon ke Categories
**Situasi:** Tabel categories sudah ada dengan kolom: id, name, description, is_active

**Langkah:**
1. Klik "Add Column"
2. Select Table: `categories`
3. Column Name: `icon`
4. Type: `Image`
5. Nullable: ✓ (centang)
6. Insert After Column: `name`
7. Klik "Add Column"
8. Refresh menu Categories (Edit → Update)
9. Test: Buat/edit category, field icon sudah muncul!

### Contoh 2: Tambah Image ke Products
**Situasi:** Tabel products sudah ada dengan kolom: id, name, category_id, price, description

**Langkah:**
1. Klik "Add Column"
2. Select Table: `products`
3. Column Name: `image`
4. Type: `Image`
5. Nullable: ✓ (centang)
6. Insert After Column: `name`
7. Klik "Add Column"
8. Refresh menu Products (Edit → Update)
9. Test: Buat/edit product, field image sudah muncul!

### Contoh 3: Tambah Status ke Orders
**Situasi:** Tabel orders perlu kolom status

**Langkah:**
1. Klik "Add Column"
2. Select Table: `orders`
3. Column Name: `status`
4. Type: `String`
5. Length: `50`
6. Default Value: `pending`
7. Nullable: (tidak dicentang)
8. Klik "Add Column"
9. Refresh menu Orders

### Contoh 4: Tambah Notes ke Products
**Situasi:** Tabel products perlu kolom notes untuk catatan panjang

**Langkah:**
1. Klik "Add Column"
2. Select Table: `products`
3. Column Name: `notes`
4. Type: `Text`
5. Nullable: ✓ (centang)
6. Insert After Column: `description`
7. Klik "Add Column"
8. Refresh menu Products

---

## Field Types

| Type | Database Type | Form Input | Use Case |
|------|---------------|------------|----------|
| String | VARCHAR(255) | Text input | Short text (name, title, code) |
| Text | NCLOB | Textarea | Long text (description, notes) |
| Integer | INTEGER | Number input | Whole numbers (quantity, count) |
| Decimal | DECIMAL(10,2) | Number input | Money, prices |
| Boolean | TINYINT | Checkbox | Yes/No, Active/Inactive |
| Date | DATE | Date picker | Birth date, deadline |
| Datetime | DATETIME | Datetime picker | Created at, updated at |
| Timestamp | TIMESTAMP | Datetime picker | Auto timestamp |
| File | VARCHAR(500) | File upload | Documents, PDFs |
| Image | VARCHAR(500) | Image upload | Photos, icons |

---

## Tips

1. **Nama Kolom**: Gunakan lowercase dan underscore (contoh: `user_id`, `is_active`, `created_at`)
2. **Type Image/File**: Jangan lupa jalankan `php artisan storage:link` sebelum upload file
3. **Nullable**: Centang jika kolom boleh kosong (recommended untuk kolom baru di tabel yang sudah ada data)
4. **Insert After**: Gunakan untuk mengatur posisi kolom agar lebih rapi
5. **Refresh Menu**: WAJIB dilakukan setelah add column agar field muncul di form

---

## Troubleshooting

### Issue 1: Field baru tidak muncul di form
**Solusi:** Refresh menu dengan cara:
1. Menu Management → Edit menu → Update (tanpa ubah apapun)

### Issue 2: Error "Column already exists"
**Solusi:** Kolom dengan nama tersebut sudah ada di tabel. Gunakan nama lain.

### Issue 3: Error saat add column
**Cek:**
- Nama kolom valid (lowercase, underscore only)
- Tabel exists
- Type dipilih dengan benar

### Issue 4: File upload tidak muncul
**Solusi:**
1. Pastikan type = "Image" atau "File"
2. Refresh menu
3. Jalankan `php artisan storage:link`

---

## Limitations

1. **Tidak bisa hapus kolom**: Fitur ini hanya untuk menambah kolom, tidak bisa hapus
2. **Tidak bisa ubah kolom**: Tidak bisa mengubah tipe atau properties kolom yang sudah ada
3. **Tidak bisa rename kolom**: Tidak bisa mengubah nama kolom yang sudah ada
4. **Foreign key**: Tidak otomatis membuat foreign key constraint (hanya kolom biasa)

Untuk operasi advanced (hapus, ubah, rename kolom), gunakan migration manual.

---

## Alternative: Via Migration

Jika Anda lebih suka menggunakan migration:

```php
// database/migrations/xxxx_add_icon_to_categories.php
Schema::table('categories', function (Blueprint $table) {
    $table->string('icon', 255)->nullable()->after('name');
});
```

Kemudian:
```bash
php artisan migrate
```

Dan jangan lupa refresh menu!

---

## Related Documentation

- [File Upload Feature](FILE_UPLOAD_FEATURE.md)
- [Table Builder Guide](TABLE_BUILDER_GUIDE.md)
- [Menu Builder Guide](MENU_BUILDER_GUIDE.md)

---

**Created:** March 27, 2026
**Version:** 1.0
