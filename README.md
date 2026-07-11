# African Market - E-commerce Platform

<p align="center">
  <img src="./public/assets/hero-banner.svg" alt="African Market Hero Banner" width="100%">
</p>

> CMS-style installable e-commerce platform for African food products, clothing, and salon services.

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql)](https://mysql.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Security](https://img.shields.io/badge/security-0_vulnerabilities-brightgreen)](docs/SECURITY_PATCH_LOG.md)

---

## 🌍 About

**African Market** is a comprehensive e-commerce platform specializing in:

- **African Food Products**: Authentic ingredients, spices, ready-to-eat meals
- **African Clothing & Fashion**: Traditional and modern African apparel
- **Salon Services**: Hair braiding, styling, beauty services booking
- **Multi-Vendor Marketplace**: Allow vendors to sell products
- **Built-in Delivery System**: Manage deliveries without external services

**Key Feature**: Installable like CMS - upload, run installer, done!

---

## ✨ Features

### Core Features
- 🛒 Complete E-commerce (cart, checkout, orders)
- 💳 **Stripe & Paystack** payment gateways
- 📱 Mobile money support (M-Pesa, MTN, Airtel via Paystack)
- 💰 Cash on delivery
- 🚚 **Built-in delivery management system**
- 👤 User management (customers, vendors, salon owners, admins)
- 📦 Order tracking & management
- 🔐 Secure authentication (Laravel Sanctum)
- 📧 Email notifications
- 🎛️ Comprehensive admin panel
- 💿 **CMS-style web installer**

### Advanced Features
- 💇 Salon booking system
- 🏪 Multi-vendor marketplace
- ⭐ Reviews & ratings
- ❤️ Wishlist
- 🔍 Advanced search & filters
- 🎁 Loyalty program
- 🔗 Referral system
- 📊 Analytics & reporting

### Delivery Management
- Delivery zones & pricing
- Distance-based shipping rates
- Driver/courier management
- Order assignment to drivers
- Real-time GPS tracking
- Proof of delivery (photos, signatures)
- Performance metrics

---

## 🏗️ Tech Stack

| Component | Technology |
|-----------|------------|
| **Framework** | Laravel 12 (PHP 8.2+) |
| **Database** | MySQL 8.0+ |
| **Server** | Apache |
| **Frontend** | Livewire 3 + Alpine.js |
| **CSS** | Tailwind CSS 3 |
| **Payments** | Stripe + Paystack |
| **No External Services** | Self-hosted (except payments) |

**See [TECH_STACK.md](./TECH_STACK.md) for detailed information.**

---

## 📋 Requirements

### Server Requirements
- **PHP**: 8.1 or 8.2 (recommended)
- **Apache**: 2.4+ with mod_rewrite enabled
- **MySQL**: 8.0+ or MariaDB 10.6+
- **Memory**: 512MB minimum, 2GB recommended
- **Disk Space**: 1GB minimum

### PHP Extensions
```
pdo_mysql, mysqli, mbstring, xml, curl
zip, gd, fileinfo, openssl, tokenizer, json
```

### Development Tools
- **Composer**: PHP dependency manager
- **NPM**: For asset compilation (Tailwind CSS)

---

## 🚀 Installation

### Option 1: Web Installer (Production - Like CMS)

1. **Download** the African Market package
2. **Upload** files to your web server via FTP/SFTP
3. **Navigate** to `http://yourdomain.com/install`
4. **Follow** the installation wizard:
   - System requirements check
   - Database configuration
   - Admin account creation
   - Site settings
5. **Done!** Start selling

### Option 2: Manual Installation (Development)

#### Step 1: Install Dependencies

```bash
# Install Composer if not installed
# Download from: https://getcomposer.org/

# Install Laravel
composer create-project laravel/laravel african-market
cd african-market

# Or clone this repository
git clone https://github.com/yourusername/african-market.git
cd african-market
composer install
```

#### Step 2: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit .env with your settings
# Set database credentials, payment keys, etc.
```

**Key Configuration (.env):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=african_market
DB_USERNAME=root
DB_PASSWORD=your_password

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...

PAYSTACK_PUBLIC_KEY=pk_test_...
PAYSTACK_SECRET_KEY=sk_test_...
```

#### Step 3: Set Up Database

```bash
# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional - sample data)
php artisan db:seed

# Create storage symlink
php artisan storage:link
```

#### Step 4: Install Frontend Dependencies

```bash
# Install NPM packages
npm install

# Compile assets (development)
npm run dev

# Or for production
npm run build
```

#### Step 5: Start Development Server

```bash
# Start Laravel development server
php artisan serve

# Visit: http://localhost:8000
```

**For Apache:**
- Point your virtual host to `/public` directory
- Ensure `.htaccess` is working (mod_rewrite enabled)

---

## 📁 Project Structure

```
african-market/
├── app/
│   ├── Http/Controllers/      # Controllers
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic
│   └── Livewire/              # Livewire components
├── database/
│   ├── migrations/            # Database migrations
│   └── seeders/               # Seeders
├── resources/
│   ├── views/                 # Blade templates
│   ├── css/                   # Styles
│   └── js/                    # JavaScript
├── public/                     # Web root
│   ├── index.php              # Entry point
│   ├── install/               # Installer
│   └── uploads/               # User uploads
├── routes/                     # Routes
├── config/                     # Configuration
├── storage/                    # Storage
├── tests/                      # Tests
├── .env.example               # Environment template
├── composer.json              # PHP dependencies
└── artisan                     # Laravel CLI
```

---

## 💳 Payment Gateway Setup

### Stripe

```bash
composer require laravel/cashier
```

Get API keys from: https://dashboard.stripe.com/apikeys

### Paystack

```bash
composer require unicodeveloper/laravel-paystack
```

Get API keys from: https://dashboard.paystack.com/#/settings/developer

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

---

## 🔧 Development Commands

```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Create new migration
php artisan make:migration create_table_name

# Create new model
php artisan make:model ModelName -m

# Create new controller
php artisan make:controller ControllerName

# Create new Livewire component
php artisan make:livewire ComponentName

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Format code
vendor/bin/pint

# Open Tinker (REPL)
php artisan tinker

# View routes
php artisan route:list

# Open Telescope (debugging)
# Visit: /telescope
```

---

## 📖 Documentation

- **[PRD.md](./PRD.md)** - Product Requirements Document
- **[TECH_STACK.md](./TECH_STACK.md)** - Technology Stack Details
- **[TODO.md](./TODO.md)** - Development Tasks and Laravel status
- **[IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md)** - Implementation roadmap
- **[docs/DEPLOYMENT_RUNBOOK.md](./docs/DEPLOYMENT_RUNBOOK.md)** - Production readiness checklist
- **[docs/WEBHOOK_E2E.md](./docs/WEBHOOK_E2E.md)** - Deterministic webhook E2E verification
- **[docs/PENDING_CLOSURE_MATRIX.md](./docs/PENDING_CLOSURE_MATRIX.md)** - Auditable pending-task closure status

---

## 🌐 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Generate new `APP_KEY`
- [ ] Configure production database
- [ ] Set up production Stripe/Paystack keys
- [ ] Configure mail server (SMTP)
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `npm run build` for assets
- [ ] Set proper file permissions
- [ ] Configure Apache virtual host
- [ ] Enable SSL/TLS (Let's Encrypt)
- [ ] Set up automated backups
- [ ] Run `php artisan app:verify-readiness --strict`
- [ ] Run deterministic webhook checks from `docs/WEBHOOK_E2E.md`

### CI Strict Readiness Secrets

To enable strict readiness in GitHub Actions, configure these repository secrets:

- `STRIPE_KEY`
- `STRIPE_SECRET`
- `PAYSTACK_PUBLIC_KEY`
- `PAYSTACK_SECRET_KEY`

### File Permissions

```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName africanmarket.com
    DocumentRoot /var/www/african-market/public

    <Directory /var/www/african-market/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

---

## 🔐 Security

- All passwords hashed with bcrypt
- CSRF protection on all forms
- SQL injection protection (Eloquent ORM)
- XSS protection (Blade escaping)
- Authentication via Laravel Sanctum
- Rate limiting on API endpoints
- Input validation on all requests
- Secure file uploads

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'feat: add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

### Commit Convention
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation
- `style:` Formatting
- `refactor:` Code refactoring
- `test:` Tests
- `chore:` Maintenance

---

## 📝 License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file for details.

---

## 💬 Support

- **Documentation**: [docs/](./docs/)
- **Issues**: [GitHub Issues](https://github.com/yourusername/african-market/issues)
- **Email**: support@africanmarket.com

---

## 🎯 Roadmap

### Phase 1: MVP (Current)
- [x] Project planning & documentation
- [ ] Authentication system
- [ ] Product catalog
- [ ] Shopping cart & checkout
- [ ] Stripe/Paystack integration
- [ ] Order management
- [ ] Admin panel
- [ ] Web installer

### Phase 2: Enhanced Features
- [ ] Salon booking system
- [ ] Multi-vendor marketplace
- [ ] Reviews & ratings
- [ ] Delivery management system
- [ ] Email notifications

### Phase 3: Growth
- [ ] Loyalty program
- [ ] Referral system
- [ ] Advanced analytics
- [ ] Mobile apps
- [ ] AI recommendations

---

## 🙏 Acknowledgments

- Laravel for the amazing framework
- Livewire for reactive components
- Tailwind CSS for styling
- Stripe & Paystack for payments
- All contributors and supporters

---

**Made with ❤️ for the African diaspora community**

*Connecting cultures through authentic products and services*

---

## 🚀 Quick Start Summary

```bash
# 1. Install dependencies
composer install && npm install

# 2. Configure environment
cp .env.example .env
# Edit .env with your MySQL credentials

# 3. Set up database
php artisan key:generate
php artisan migrate
php artisan db:seed

# 4. Compile assets
npm run dev

# 5. Start server
php artisan serve

# Visit: http://localhost:8000
```

**That's it! Start building your African Market!** 🎉

---

## Documentation

- User-facing documentation: `documentation/README.md`
- Existing technical/deployment notes: `docs/`

