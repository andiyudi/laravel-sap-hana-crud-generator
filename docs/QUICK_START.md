# Quick Start Guide - Laravel 13 HANA App

## Prerequisites
- PHP 8.3+
- Composer
- SAP HANA Database
- Node.js & NPM (optional, for asset compilation)

## Installation

### 1. Clone & Install Dependencies
```bash
cd laravel13hana
composer install
```

### 2. Configure Environment
```bash
# Copy .env.example if needed
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Database
Edit `.env` file:
```env
DB_CONNECTION=hana
DB_HOST=your-hana-host
DB_PORT=30013
DB_DATABASE=SYSTEMDB
DB_USERNAME=your-username
DB_PASSWORD=your-password
DB_SCHEMA=your-schema
```

### 4. Run Migrations & Seeders
```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed
```

This will create:
- Users table with test user
- Products table
- Permission tables (Spatie)
- Default roles and permissions

### 5. Start Development Server
```bash
php artisan serve
```

Visit: http://localhost:8000

## Default Login Credentials

After running seeders, you can login with:
```
Email: test@example.com
Password: password
```

## Available Routes

### Public Routes
- `GET /` - Welcome page
- `GET /login` - Login page
- `POST /login` - Login action

### Protected Routes (requires authentication)
- `GET /dashboard` - Dashboard
- `GET /products` - Products list
- `GET /products/create` - Create product form
- `POST /products` - Store product
- `GET /products/{id}/edit` - Edit product form
- `PUT /products/{id}` - Update product
- `DELETE /products/{id}` - Delete product
- `POST /logout` - Logout

## Features

### 1. Authentication
- Login with email & password
- Remember me functionality
- Session-based authentication
- Logout

### 2. Products Management
- List all products with pagination
- Create new product
- Edit existing product
- Delete product
- Permission-based access control

### 3. UI Features
- Bootstrap 5 responsive design
- Dark/Light mode toggle
- Sidebar navigation
- Alert notifications
- Form validation
- Responsive tables

### 4. Permissions
Using Spatie Laravel Permission:
- `product.create` - Can create products
- `product.edit` - Can edit products
- `product.delete` - Can delete products

## Development Commands

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Database
```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migration with seed
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status
```

### Tinker (REPL)
```bash
php artisan tinker
```

### Routes
```bash
# List all routes
php artisan route:list

# List specific routes
php artisan route:list --name=products
```

## Troubleshooting

### Issue: "Class not found" errors
```bash
composer dump-autoload
```

### Issue: View not updating
```bash
php artisan view:clear
```

### Issue: Config not updating
```bash
php artisan config:clear
```

### Issue: Database connection failed
1. Check HANA ODBC driver is installed
2. Verify `.env` database credentials
3. Test connection with:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### Issue: Permission denied errors
Check file permissions:
```bash
chmod -R 775 storage bootstrap/cache
```

## Project Structure

```
laravel13hana/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php
│   │   │   └── ProductController.php
│   │   └── Requests/
│   │       ├── StoreProductRequest.php
│   │       └── UpdateProductRequest.php
│   └── Models/
│       ├── Product.php
│       └── User.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── RolePermissionSeeder.php
├── packages/
│   └── custom/
│       └── laravel-hana/  # Custom HANA driver
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── auth/
│       │   └── login.blade.php
│       ├── products/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       └── dashboard.blade.php
└── routes/
    └── web.php
```

## Next Steps

1. Customize the UI to match your brand
2. Add more features (search, filters, export)
3. Add API endpoints if needed
4. Configure production environment
5. Set up deployment pipeline

## Support

For issues related to:
- Laravel: https://laravel.com/docs
- Bootstrap: https://getbootstrap.com/docs
- SAP HANA: https://help.sap.com/hana

## License

This project is open-sourced software licensed under the MIT license.
