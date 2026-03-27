# Cara Menambahkan File Upload ke Tabel yang Sudah Ada

## Situasi Anda

Anda memiliki:
- Tabel `categories` dengan kolom: `id`, `name`, `description`, `is_active`, `created_at`, `updated_at`
- Tabel `products` dengan kolom: `id`, `name`, `category_id`, `price`, `description`, `is_active`, `created_at`, `updated_at`

Anda ingin menambahkan:
- Icon/image untuk categories
- Image untuk products

---

## Langkah-langkah

### Langkah 1: Setup Storage Link (WAJIB)

Jalankan command ini SEKALI SAJA:

```bash
php artisan storage:link
```

Output yang benar:
```
The [public/storage] link has been connected to [storage/app/public].
```

---

### Langkah 2: Jalankan Migration untuk Menambah Kolom

Saya sudah membuatkan 2 migration files:
- `2026_03_26_100000_add_image_to_products_table.php`
- `2026_03_26_100001_add_icon_to_categories_table.php`

Jalankan migration:

```bash
php artisan migrate
```

Ini akan menambahkan:
- Kolom `image` ke tabel `products`
- Kolom `icon` ke tabel `categories`

---

### Langkah 3: Refresh Menu Fields

Setelah kolom ditambahkan, Anda perlu refresh field definitions di menu:

#### Untuk Categories:
1. Buka browser, masuk ke aplikasi
2. Klik "Menu Management" di sidebar
3. Cari menu "Categories"
4. Klik tombol "Edit" (icon pensil)
5. **JANGAN UBAH APAPUN**, langsung klik tombol "Update"
6. Sistem akan otomatis mendeteksi kolom baru `icon`

#### Untuk Products:
1. Di "Menu Management"
2. Cari menu "Products"
3. Klik tombol "Edit"
4. **JANGAN UBAH APAPUN**, langsung klik "Update"
5. Sistem akan otomatis mendeteksi kolom baru `image`

---

### Langkah 4: Ubah Tipe Field menjadi Image

Setelah refresh, field `icon` dan `image` akan terdeteksi sebagai tipe "text". Kita perlu ubah ke tipe "image":

#### Cara Manual (via Database):

**Untuk Categories:**
```sql
-- Ambil data fields saat ini
SELECT fields FROM menus WHERE table_name = 'categories';

-- Update field 'icon' menjadi tipe 'image'
-- Anda perlu edit JSON, ubah field 'icon' dari:
-- {"name":"icon","type":"text",...}
-- menjadi:
-- {"name":"icon","type":"image",...}
```

**Untuk Products:**
```sql
-- Ambil data fields saat ini
SELECT fields FROM menus WHERE table_name = 'products';

-- Update field 'image' menjadi tipe 'image'
-- Ubah dari type "text" ke "image"
```

#### Cara Otomatis (via Code):

Saya akan buatkan script helper untuk Anda.

---

### Langkah 5: Test Upload

#### Test Categories:
1. Klik "Categories" di sidebar
2. Klik "Add New" atau "Edit" pada category yang ada
3. Anda akan melihat field "Icon" dengan input file
4. Upload gambar (JPEG, PNG, JPG, GIF, WEBP, max 2MB)
5. Klik "Create" atau "Update"
6. Di list view, icon akan muncul sebagai thumbnail

#### Test Products:
1. Klik "Products" di sidebar
2. Klik "Add New" atau "Edit" pada product yang ada
3. Anda akan melihat field "Image" dengan input file
4. Upload gambar produk
5. Klik "Create" atau "Update"
6. Di list view, image akan muncul sebagai thumbnail

---

## Opsi Alternatif: Buat Tabel Baru dengan File Upload

Jika Anda ingin membuat tabel baru dari awal dengan file upload:

### Via Table Builder:

1. Klik "Create Table" di sidebar
2. Table name: `test_products`
3. Tambah fields:
   - Field 1: `name` (String, required)
   - Field 2: `category_id` (Integer, nullable)
   - Field 3: `image` (Image, nullable) ← PILIH TYPE "IMAGE"
   - Field 4: `price` (Decimal, nullable)
4. Klik "Create Table"
5. Buat menu untuk tabel tersebut
6. Langsung bisa upload image!

---

## Troubleshooting

### Issue 1: Field tidak muncul setelah migration
**Solusi:**
1. Edit menu di Menu Management
2. Klik "Update" tanpa ubah apapun
3. Field akan ter-refresh

### Issue 2: Field masih tipe "text" bukan "image"
**Solusi:**
Edit manual di database atau gunakan script helper yang saya buatkan.

### Issue 3: Upload gagal
**Cek:**
```bash
# Pastikan storage link ada
ls -la public/storage

# Jika tidak ada, jalankan:
php artisan storage:link
```

### Issue 4: Gambar tidak muncul
**Cek:**
1. Storage link sudah dibuat
2. File ada di `storage/app/public/images/`
3. Path di database benar (contoh: `images/xyz123.jpg`)

---

## Struktur File Setelah Upload

```
storage/
  app/
    public/
      images/
        abc123def456.jpg  ← Category icon
        xyz789ghi012.png  ← Product image
```

Database:
```
categories table:
id | name        | icon                    | ...
1  | Electronics | images/abc123def456.jpg | ...

products table:
id | name    | image                   | category_id | ...
1  | Laptop  | images/xyz789ghi012.png | 1           | ...
```

---

## Script Helper untuk Update Field Type

Saya akan buatkan command artisan untuk update field type secara otomatis.

---

## Contoh Penggunaan

### Scenario 1: Upload Icon Category
1. Edit category "Electronics"
2. Upload icon: `electronics-icon.png`
3. Save
4. Icon muncul di list sebagai thumbnail kecil

### Scenario 2: Upload Image Product
1. Edit product "Laptop"
2. Upload image: `laptop-photo.jpg`
3. Save
4. Image muncul di list sebagai thumbnail

### Scenario 3: Ganti Image
1. Edit product yang sudah ada image
2. Upload image baru
3. Save
4. Image lama otomatis terhapus
5. Image baru tersimpan

---

## Tips

1. **Ukuran Image**: Resize dulu sebelum upload untuk performa lebih baik
2. **Nama File**: Sistem otomatis generate nama unik
3. **Format**: Gunakan JPEG/PNG untuk kompatibilitas terbaik
4. **Backup**: Backup folder `storage/app/public/images/` secara berkala

---

## Next Steps

Setelah berhasil:
1. Test upload di categories
2. Test upload di products
3. Test edit dan ganti image
4. Test delete (image ikut terhapus)
5. Cek storage disk usage

---

**Butuh bantuan?** Lihat dokumentasi lengkap di:
- `docs/FILE_UPLOAD_FEATURE.md`
- `docs/FILE_UPLOAD_TEST_GUIDE.md`
