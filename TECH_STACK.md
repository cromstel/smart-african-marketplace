# African Market - Tech Stack Summary

**Framework**: Laravel 12 (PHP 8.2+)  
**Database**: MySQL 8.0+ (Your existing installation)  
**Server**: Apache (Your existing installation)  
**Last Major Upgrade**: July 11, 2026

---

## Core Stack

### Backend & Frontend (Unified)
- **Laravel 12**: Full-stack PHP framework
  - Eloquent ORM for database
  - Blade templates for views
  - Built-in authentication & authorization
  - Queue system for background jobs
  - File storage handling
  
### UI/Frontend
- **Livewire 3.8**: Real-time reactive components (no JavaScript frameworks needed)
- **Alpine.js**: Minimal JavaScript for interactions
- **Tailwind CSS 3**: Utility-first CSS framework
- **DaisyUI / Flowbite**: Free Tailwind component libraries

### Database
- **MySQL 8.0+**: Your existing MySQL server
- **Laravel Migrations**: Database version control
- **Laravel Seeders**: Sample data generation

### Payment Gateways
- **Stripe** (v17.6): Cards, Apple Pay, Google Pay
  - Integration: Laravel Cashier 16
- **Paystack**: African payment gateway
  - Mobile money: M-Pesa, MTN, Airtel
  - Bank transfers
  - Cards

### Delivery System
- **Built-in Custom System**: No external APIs/services
  - Delivery zones management
  - Distance-based pricing
  - Driver/courier management
  - Order assignment
  - GPS tracking
  - Proof of delivery (photos)

### Email & Notifications
- **Laravel Mail**: SMTP (Gmail/SendGrid free tier)
- **Laravel Notifications**: Email + database notifications
- **SMS**: Free gateway APIs or manual queueing

### Admin Panel
- **Laravel Blade + Livewire**: Custom admin interface
- **Chart.js / ApexCharts**: Free charting libraries
- **No paid templates**: Everything custom-built

### Development Tools
- **Composer**: PHP dependency manager
- **NPM**: For Tailwind CSS compilation
- **Laravel Telescope 5**: Debugging (free)
- **Laravel Log Viewer**: Error tracking (free)

---

## Why This Stack?

### ✅ Meets Your Requirements

1. **Works with existing Apache/PHP/MySQL** - No need for Node.js, Docker, or cloud services
2. **CMS-style installation** - Can be uploaded to any server and installed via web wizard
3. **No external paid services** - Everything self-hosted except payment gateways
4. **Cross-platform** - Runs on Windows, Linux, Mac
5. **Easy deployment** - FTP upload + web installer

### ✅ No External Dependencies

- **No Redis required** - Laravel Queue uses database driver
- **No cloud storage** - Files stored locally
- **No paid admin templates** - Custom Livewire components
- **No SendGrid/Twilio required** - Use free SMTP/SMS alternatives
- **No Docker** - Runs directly on Apache

### ✅ Built-in Delivery System

- Zone-based delivery management
- Driver assignment and tracking
- Distance calculation for pricing
- Proof of delivery with photos
- Performance analytics
- No third-party delivery APIs

---

## Installation Requirements

**Server:**
- Apache 2.4+ with mod_rewrite
- PHP 8.2+ (Laravel 12 requirement)
- MySQL 8.0+
- 1GB disk space
- 512MB RAM (2GB recommended)

**PHP Extensions:**
- pdo_mysql, mysqli
- mbstring, xml, curl
- zip, gd, fileinfo
- openssl, tokenizer, json

**Deployment:**
1. Upload files via FTP/SFTP
2. Navigate to /install in browser
3. Follow installation wizard
4. Database setup
5. Admin account creation
6. Done!

---

## Development Setup (Windows)

Your existing environment works perfectly:

```bash
# 1. Install Composer (if not already installed)
# Download from: https://getcomposer.org/

# 2. Install Laravel
composer create-project laravel/laravel african-market
cd african-market

# 3. Install dependencies
composer install
npm install

# 4. Configure database
cp .env.example .env
# Edit .env with your MySQL credentials

# 5. Generate app key
php artisan key:generate

# 6. Run migrations
php artisan migrate

# 7. Install Livewire
composer require livewire/livewire

# 8. Install Tailwind
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init

# 9. Compile assets
npm run dev

# 10. Serve application
php artisan serve
# Visit: http://localhost:8000
```

---

## File Structure

```
african-market/
├── app/
│   ├── Http/Controllers/      # Request handlers
│   ├── Models/                # Database models
│   ├── Services/              # Business logic
│   └── Livewire/              # Livewire components
├── database/
│   ├── migrations/            # Database schema
│   └── seeders/               # Sample data
├── resources/
│   ├── views/                 # Blade templates
│   ├── css/                   # Tailwind CSS
│   └── js/                    # Alpine.js
├── public/
│   ├── index.php              # Entry point
│   ├── install/               # Installer
│   └── uploads/               # User files
├── routes/
│   ├── web.php                # Web routes
│   └── api.php                # API routes
├── config/                     # Configuration
├── storage/                    # Logs, cache
├── .env                        # Environment config
├── composer.json               # PHP dependencies
└── artisan                     # Laravel CLI
```

---

## Payment Gateway Setup

### Stripe
```bash
composer require laravel/cashier
```

**.env:**
```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

### Paystack
```bash
# Custom integration (no external package required)
# Paystack API calls handled via app/Services/PaymentService.php
```

**.env:**
```
PAYSTACK_PUBLIC_KEY=pk_test_...
PAYSTACK_SECRET_KEY=sk_test_...
PAYSTACK_PAYMENT_URL=https://api.paystack.co
```

---

## Key Advantages

| Feature | Benefit |
|---------|---------|
| Laravel | Mature, well-documented, large community |
| Livewire | Build reactive UIs without JavaScript frameworks |
| Blade | Simple, powerful templating |
| Eloquent | Intuitive database ORM |
| Free | No paid services except payment gateways |
| Portable | Upload anywhere, install via web |
| Your Stack | Uses your existing Apache/PHP/MySQL |

---

## Next Steps

1. **Set up Laravel project** (5 min)
2. **Configure database** (2 min)
3. **Install Livewire + Tailwind** (10 min)
4. **Create first model & migration** (10 min)
5. **Build authentication** (Laravel Breeze - 5 min)
6. **Start building features!**

---

**Ready to start development!** 🚀

