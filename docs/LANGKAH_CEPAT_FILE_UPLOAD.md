# Langkah Cepat: Tambah File Upload ke Categories & Products

## Langkah 1: Setup Storage (WAJIB - Sekali Saja)

```bash
php artisan storage:link
```

---

## Langkah 2: Tambah Kolom ke Database

```bash
php artisan migrate
```

Ini akan menambahkan:
- Kolom `icon` ke tabel `categories`
- Kolom `image` ke tabel `products`

---

## Langkah 3: Cari Menu ID

```bash
php artisan tinker
```

Kemudian jalankan:
```php
\App\Models\Menu::select('id', 'name', 'table_name')->get();
```

Catat ID untuk:
- Categories (misal: ID = 1)
- Products (misal: ID = 2)

Ketik `exit` untuk keluar dari tinker.

---

## Langkah 4: Refresh Menu Fields

### Untuk Categories:
```bash
# Ganti 1 dengan ID menu Categories Anda
php artisan tinker
```

```php
$menu = \App\Models\Menu::find(1);
$menu->refreshFieldsFromDatabase();
$menu->save();
exit
```

### Untuk Products:
```bash
php artisan tinker
```

```php
$menu = \App\Models\Menu::find(2);
$menu->refreshFieldsFromDatabase();
$menu->save();
exit
```

---

## Langkah 5: Update Field Type ke "image"

### Untuk Categories (icon):
```bash
# Ganti 1 dengan ID menu Categories
php artisan menu:update-field-types 1 icon image
```

### Untuk Products (image):
```bash
# Ganti 2 dengan ID menu Products
php artisan menu:update-field-types 2 image image
```

---

## Langkah 6: Test!

1. Buka browser
2. Login ke aplikasi
3. Klik "Categories" → "Add New" atau "Edit"
4. Upload icon untuk category
5. Klik "Products" → "Add New" atau "Edit"
6. Upload image untuk product
7. Lihat hasilnya di list view!

---

## Selesai!

Sekarang Anda bisa:
- ✓ Upload icon untuk categories
- ✓ Upload image untuk products
- ✓ Lihat thumbnail di list view
- ✓ Edit dan ganti image
- ✓ Image otomatis terhapus saat delete

---

## Jika Ada Masalah

### Storage link tidak ada:
```bash
php artisan storage:link
```

### Field tidak muncul:
```bash
php artisan tinker
```
```php
$menu = \App\Models\Menu::find(MENU_ID);
$menu->refreshFieldsFromDatabase();
$menu->save();
exit
```

### Lihat field definitions:
```bash
php artisan tinker
```
```php
$menu = \App\Models\Menu::find(MENU_ID);
print_r($menu->getFieldDefinitions());
exit
```

---

**Dokumentasi Lengkap:** `docs/CARA_TAMBAH_FILE_UPLOAD.md`
