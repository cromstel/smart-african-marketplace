> **Status: Needs review** — superseded in part by
> [Getting Started](getting-started.md).
>
> [Documentation Hub](../README.md) › Setup Guide
>
> Two cautions:
>
> 1. The walkthrough sections build a **Livewire** `ProductList` component. Files
>    such as `app/Livewire/ProductList.php` and `resources/views/welcome.blade.php`
>    are files the tutorial asks you to *create*; they are not part of the
>    repository. Livewire is also no longer the default UI pattern — see
>    [ADR 0002](../architecture/decisions/0002-blade-over-livewire.md).
> 2. For setting up an existing clone, follow
>    [Getting Started](getting-started.md) instead. This guide remains
>    useful for the deeper environment and Apache notes.

# Smart African Market - Setup Guide

**Status**: Project structure initialized ✅  
**Next**: Install Laravel core via Composer

---

## 🎯 Current Status

✅ **Completed:**
- Laravel directory structure created
- Tailwind CSS + Alpine.js + Vite configured
- Package.json with dependencies
- Apache .htaccess files
- Resource files (CSS/JS)
- Documentation (PRD, README, TECH_STACK)
- Composer.json with Laravel 10+ dependencies

⏳ **Next Steps:**
- Install Composer packages (Laravel core)
- Configure database
- Run migrations
- Start development

---

## 📋 Prerequisites

Before you begin, ensure you have:

✅ **PHP 8.1 or 8.2** installed  
✅ **Composer** installed ([getcomposer.org](https://getcomposer.org/))  
✅ **MySQL 8.0+** running (you have this)  
✅ **Apache** with mod_rewrite (you have this)  
✅ **NPM** installed (comes with Node.js)

### Check Your Setup

```bash
# Check PHP version
php -v
# Should show: PHP 8.1.x or 8.2.x

# Check Composer
composer --version
# Should show: Composer version 2.x

# Check NPM
npm -v
# Should show: 9.x or 10.x

# Check MySQL
mysql --version
# Should show: mysql Ver 8.0.x
```

---

## 🚀 Installation Steps

### Step 1: Install PHP Dependencies (Laravel Core)

```bash
# Navigate to project directory
cd "C:\Users\Cardif Nordic\.zenflow\worktrees\set-up-project-config-990f"

# Install Composer packages (this will install Laravel)
composer install

# This will install:
# - Laravel 10+ framework
# - Livewire 3
# - Laravel Cashier (Stripe)
# - Paystack package
# - All other dependencies from composer.json
```

**Expected output:**
```
Loading composer repositories with package information
Installing dependencies from lock file
Package operations: 100+ installs, 0 updates, 0 removals
...
Generating optimized autoload files
...
```

### Step 2: Install Frontend Dependencies

```bash
# Install NPM packages (Tailwind CSS, Alpine.js, Vite)
npm install

# Expected output:
# added 50+ packages in 20s
```

### Step 3: Configure Environment

```bash
# Copy environment file
copy .env.example .env

# Edit .env file with your settings:
# - notepad .env (or use your preferred editor)
```

**Key settings to update in .env:**

```env
# App
APP_NAME="Smart African Market"
APP_URL=http://localhost:8000

# Database (use your MySQL credentials)
DB_DATABASE=african_market
DB_USERNAME=root
DB_PASSWORD=your_mysql_password

# Payment gateways (get from dashboards)
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
PAYSTACK_PUBLIC_KEY=pk_test_...
PAYSTACK_SECRET_KEY=sk_test_...
```

### Step 4: Generate Application Key

```bash
# Generate unique app key (required for encryption)
php artisan key:generate

# Output: Application key set successfully.
```

### Step 5: Create Database

Option A: **MySQL Command Line**
```sql
mysql -u root -p
CREATE DATABASE african_market CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

Option B: **phpMyAdmin**
- Open phpMyAdmin in browser
- Click "New" database
- Name: `african_market`
- Collation: `utf8mb4_unicode_ci`
- Click "Create"

### Step 6: Run Database Migrations

```bash
# Create database tables
php artisan migrate

# Expected output:
# Migration table created successfully.
# Migrating: 2014_10_12_000000_create_users_table
# Migrated:  2014_10_12_000000_create_users_table (50ms)
# ...
```

### Step 7: Seed Database (Required)

```bash
# Add sample data for testing
php artisan db:seed

# This will create:
# - Sample admin user
# - Sample products
# - Sample categories
# - Sample orders (for testing)
```

### Step 8: Create Storage Link

```bash
# Link public storage (for user uploads)
php artisan storage:link

# Output: The [public/storage] link has been connected to [storage/app/public].
```

### Step 9: Compile Frontend Assets

```bash
# Compile Tailwind CSS and Alpine.js
npm run build

# For development (with hot reload):
npm run dev
```

### Step 10: Start Development Server

```bash
# Start Laravel development server
php artisan serve

# Output:
# INFO  Server running on [http://127.0.0.1:8000].
# Press Ctrl+C to stop the server
```

**Open browser**: http://localhost:8000

---

## ✅ Verification Checklist

After installation, verify everything works:

- [ ] Visit http://localhost:8000 - you should see a page (not error)
- [ ] Check database - tables should be created in `african_market` database
- [ ] Check `storage/logs/laravel.log` - no fatal errors
- [ ] CSS loads properly (Tailwind classes applied)
- [ ] No JavaScript errors in browser console

---

## 🔧 Useful Commands

### Development

```bash
# Start dev server
php artisan serve

# Watch CSS/JS changes (in separate terminal)
npm run dev

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# View routes
php artisan route:list

# Open Tinker (Laravel REPL)
php artisan tinker
```

### Database

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (drop all + recreate)
php artisan migrate:fresh

# Seed database
php artisan db:seed
```

### Code Generation

```bash
# Create model + migration
php artisan make:model Product -m

# Create controller
php artisan make:controller ProductController

# Create Livewire component
php artisan make:livewire ProductList

# Create migration
php artisan make:migration create_products_table

# Create seeder
php artisan make:seeder ProductSeeder
```

### Testing

```bash
# Run tests
php artisan test

# Format code
vendor/bin/pint

# Specific test
php artisan test --filter ProductTest
```

---

## 🎨 Tailwind CSS Classes

The project includes custom utility classes:

```html
<!-- Buttons -->
<button class="btn-primary">Primary Action</button>
<button class="btn-secondary">Secondary Action</button>

<!-- Card -->
<div class="card">
    Card content here
</div>

<!-- Input field -->
<input type="text" class="input-field" placeholder="Enter text">
```

---

## 📁 Project Structure

```
african-market/
├── app/                    # Application code
│   ├── Http/
│   │   ├── Controllers/   # Request handlers
│   │   ├── Middleware/    # HTTP middleware
│   │   └── Requests/      # Form requests
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic
│   └── Livewire/          # Livewire components
│
├── database/
│   ├── migrations/        # Database migrations
│   ├── seeders/           # Database seeders
│   └── factories/         # Model factories
│
├── resources/
│   ├── views/             # Blade templates
│   ├── css/               # Tailwind CSS
│   └── js/                # Alpine.js
│
├── public/                # Web root
│   ├── index.php          # Entry point
│   ├── .htaccess          # Apache config
│   └── uploads/           # User uploads
│
├── routes/
│   ├── web.php            # Web routes
│   └── api.php            # API routes
│
├── storage/               # App storage
│   ├── app/               # User files
│   ├── framework/         # Cache, sessions
│   └── logs/              # Log files
│
├── tests/                 # PHPUnit tests
│
├── .env                   # Environment config
├── composer.json          # PHP dependencies
├── package.json           # NPM dependencies
└── artisan                # Laravel CLI
```

---

## 🐛 Troubleshooting

### Issue: "Class not found" error

```bash
# Solution: Regenerate autoload files
composer dump-autoload
```

### Issue: Permission errors (Windows)

```bash
# Ensure these directories are writable:
# - storage/
# - bootstrap/cache/
```

### Issue: Vite not found

```bash
# Solution: Reinstall NPM packages
rm -rf node_modules
npm install
```

### Issue: Database connection failed

Check `.env` file:
- `DB_HOST` should be `127.0.0.1` (not `localhost`)
- `DB_DATABASE` must exist in MySQL
- `DB_USERNAME` and `DB_PASSWORD` are correct

Test connection:
```bash
php artisan tinker
DB::connection()->getPdo();
# Should show: PDO object if connected
```

### Issue: Composer install fails

Update Composer:
```bash
composer self-update
composer install
```

---

## 📚 Next Development Steps

### 1. Create First Model & Migration

```bash
php artisan make:model Product -m
```

Edit `database/migrations/xxxx_create_products_table.php`:
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description');
    $table->decimal('price', 10, 2);
    $table->integer('stock')->default(0);
    $table->string('image')->nullable();
    $table->timestamps();
});
```

Run migration:
```bash
php artisan migrate
```

### 2. Create First Controller

```bash
php artisan make:controller ProductController
```

### 3. Create First View

Create `resources/views/welcome.blade.php`:
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart African Market</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center mb-8">
            Welcome to Smart African Market
        </h1>
        <div class="card">
            <p class="text-gray-600">
                Your platform for authentic African products and services.
            </p>
        </div>
    </div>
</body>
</html>
```

### 4. Create First Livewire Component

```bash
php artisan make:livewire ProductList
```

This creates:
- `app/Livewire/ProductList.php`
- `resources/views/livewire/product-list.blade.php`

---

## 🎯 Development Roadmap

### Week 1: Core Setup ✅
- [x] Project structure
- [x] Laravel installation
- [ ] Authentication (Laravel Breeze)
- [ ] Admin panel layout

### Week 2-4: Product Catalog
- [ ] Product model & migrations
- [ ] Category system
- [ ] Product CRUD (admin)
- [ ] Product listing (public)
- [ ] Product detail page
- [ ] Image upload

### Week 5-6: Shopping Cart & Checkout
- [ ] Cart functionality (Livewire)
- [ ] Checkout flow
- [ ] Stripe integration
- [ ] Paystack integration
- [ ] Order management

### Week 7-8: User Management
- [ ] User registration
- [ ] User dashboard
- [ ] Order history
- [ ] Profile management
- [ ] Address management

### Month 3: Advanced Features
- [ ] Vendor system
- [ ] Salon booking
- [ ] Reviews & ratings
- [ ] Delivery system

### Month 4: Polish & Launch
- [ ] CMS-style installer
- [ ] Testing
- [ ] Documentation
- [ ] Deployment

---

## 📖 Resources

- **Laravel Docs**: https://laravel.com/docs
- **Livewire Docs**: https://livewire.laravel.com
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Alpine.js**: https://alpinejs.dev
- **Stripe Docs**: https://stripe.com/docs
- **Paystack Docs**: https://paystack.com/docs

---

## 💡 Tips

1. **Use Laravel Telescope** for debugging (install: `composer require laravel/telescope --dev`)
2. **Format code** regularly with Pint: `vendor/bin/pint`
3. **Use Tinker** for quick tests: `php artisan tinker`
4. **Keep .env secure** - never commit to Git
5. **Use migrations** for all database changes
6. **Write tests** as you build features

---

## ✅ You're Ready!

Your Smart African Market platform is initialized and ready for development. Start building by running:

```bash
php artisan serve
npm run dev
```

Then open http://localhost:8000 in your browser.

**Happy Coding! 🚀🌍**

