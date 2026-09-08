# Tech Stack

**Status: Current** — corrected against the codebase on 2026-09-08.

[Documentation Hub](docs/README.md) › Tech Stack

> **What changed.** Earlier revisions of this page described Livewire as the UI
> layer and claimed the platform used no external services. Neither was accurate.
> Both are corrected below; see the
> [Documentation Audit](docs/DOCUMENTATION_AUDIT.md) for the evidence.

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
  
### UI / Frontend

The UI is **server-rendered Blade**, styled with Tailwind and made interactive
with Alpine.js. Livewire is present but used by exception.

- **Blade**: the templating layer — ~330 views, the actual frontend
- **Tailwind CSS 3**: utility-first styling
- **Alpine.js**: lightweight interactivity
- **Livewire 3**: 2 components (live search). Reach for it only when a component
  genuinely needs stateful server round-trips — see
  [ADR 0002](docs/architecture/decisions/0002-blade-over-livewire.md)
- **Vite**: asset pipeline
- **Chart.js**: admin analytics · **TinyMCE**: rich text editing

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
- **Laravel Pint**: PHP formatting
- **ESLint / Prettier**: JavaScript and CSS
- **Playwright**: browser tests
- **PHPUnit**: feature tests — see the [Testing Guide](docs/guides/testing.md)

> Telescope must stay disabled in production (`TELESCOPE_ENABLED=false`); it
> exposes request payloads.

---

## Why This Stack?

### ✅ Meets Your Requirements

1. **Works with existing Apache/PHP/MySQL** - No need for Node.js, Docker, or cloud services
2. **CMS-style installation** - Can be uploaded to any server and installed via web wizard
3. **No external paid services** - Everything self-hosted except payment gateways
4. **Cross-platform** - Runs on Windows, Linux, Mac
5. **Easy deployment** - FTP upload + web installer

### ✅ Minimal Infrastructure Dependencies

The goal is that the platform runs on commodity Apache/PHP/MySQL hosting. It
does — but it is not dependency-free.

**Not required:**

- **No Redis** — queue, cache and session default to the database driver
  ([ADR 0004](docs/architecture/decisions/0004-database-queue-driver.md))
- **No cloud storage** — files are stored locally by default
- **No paid admin templates** — the admin UI is custom Blade
- **No Docker** — runs directly on Apache

**Integrated external services** (most are optional and can be switched off):

| Service | Used for | Optional |
|---|---|---|
| Stripe / Paystack / Swish | Payments | At least one is needed to take money |
| Twilio | SMS | Yes — `SMS_ENABLED=false` |
| Soketi | Realtime (self-hosted) | Yes — `BROADCAST_DRIVER=log` |
| Firebase Cloud Messaging | Mobile push | Yes |
| OpenAI | Chatbot and knowledge base | Yes |
| Google / Facebook OAuth | Social login | Yes |
| SMTP provider | Transactional email | Needed in production |

See the [Configuration Reference](docs/reference/configuration.md) for the keys
each one needs.

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

# 2. Clone this repository
git clone https://github.com/cromstel/smart-african-marketplace.git
cd smart-african-marketplace

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

# 7. Compile assets
npm run dev

# 8. Serve application
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

---

## See also

- [Architecture Overview](docs/architecture/overview.md) — how the pieces interact
- [Getting Started](docs/guides/getting-started.md) — the current setup guide
- [Configuration Reference](docs/reference/configuration.md) — every env var
- [Decision Records](docs/architecture/decisions/README.md) — why this stack
- [Documentation Hub](docs/README.md)

