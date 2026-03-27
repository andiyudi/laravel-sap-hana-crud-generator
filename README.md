# Laravel 13 with SAP HANA Database

A Laravel 13 application with custom SAP HANA database driver and dynamic CRUD management system.

## Features

- Custom SAP HANA database driver
- Dynamic menu management system
- Dynamic CRUD generator from UI
- Table builder (create tables from UI)
- User, Role, and Permission management (Spatie Laravel Permission)
- Bootstrap 5 with dark/light mode
- Dynamic permission generation

## Quick Start

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure HANA database in .env
DB_CONNECTION=hana
DB_HOST=your-hana-host
DB_PORT=30015
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password
HANA_SCHEMA=your-schema

# Run migrations and seeders
php artisan migrate:fresh --seed

# Build assets
npm run build

# Start server
php artisan serve
```

## Default Login

- Email: admin@example.com
- Password: password

## Documentation

All documentation is available in the `/docs` folder:

- [Quick Start Guide](docs/QUICK_START.md)
- [Complete Features](docs/COMPLETE_FEATURES.md)
- [Menu Management](docs/MENU_MANAGEMENT_SUMMARY.md)
- [Table Builder Guide](docs/TABLE_BUILDER_GUIDE.md)
- [User Management](docs/USER_MANAGEMENT.md)
- [Dynamic Permissions](docs/DYNAMIC_PERMISSIONS.md)

## Tech Stack

- Laravel 13
- PHP 8.3
- SAP HANA Database
- Bootstrap 5.3.2
- Spatie Laravel Permission
- Custom HANA Driver Package

## License

This project is open-sourced software.
