# Smart African Marketplace - Quick Start Guide

**Project Status**: ✅ **READY TO INSTALL LARAVEL**

---

## 🎯 What's Ready

✅ **Laravel project structure** - All directories created  
✅ **Configuration files** - composer.json, package.json, configs  
✅ **Routes** - web.php, api.php, console.php  
✅ **Views** - Beautiful welcome page with Tailwind CSS  
✅ **Assets** - Tailwind CSS + Alpine.js configured  
✅ **Apache** - .htaccess files ready  
✅ **Documentation** - Complete guides available

---

## ⚡ Install in 3 Minutes

### Step 1: Install Dependencies (2 min)

```bash
# Install Composer packages (Laravel core)
composer install

# Install NPM packages (Tailwind, Alpine)
npm install
```

### Step 2: Configure Environment (30 sec)

```bash
# Copy environment file
copy .env.example .env

# Edit .env (set DB_PASSWORD)
notepad .env
```

### Step 3: Set Up Database (30 sec)

```bash
# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate
```

### ✅ Done! Start Development

```bash
# Terminal 1: Start Laravel
php artisan serve

# Terminal 2: Compile assets
npm run dev
```

**Visit**: http://localhost:8000

---

## 📁 Project Structure

```
african-market/
├── app/              ✅ Controllers, Models, Services
├── resources/        ✅ Views (welcome.blade.php), CSS, JS
├── routes/           ✅ web.php, api.php, console.php
├── config/           ✅ app, database, payment, delivery
├── public/           ✅ index.php, .htaccess
├── database/         ✅ migrations, seeders
├── storage/          ✅ logs, cache
├── artisan           ✅ Laravel CLI
├── composer.json     ✅ Laravel 10+ dependencies
├── package.json      ✅ Vite, Tailwind, Alpine
└── .env.example      ✅ Environment template
```

---

## 🎨 Features Ready

### Welcome Page (http://localhost:8000)
- ✅ Hero section
- ✅ Features showcase
- ✅ Category grid
- ✅ Call-to-action
- ✅ Full footer
- ✅ Tailwind CSS styled
- ✅ Responsive design

### Routes Configured
- `/` - Welcome page
- `/shop/products` - Product listing
- `/shop/cart` - Shopping cart
- `/salon` - Salon services
- `/admin/dashboard` - Admin panel
- `/vendor/dashboard` - Vendor portal
- `/api/v1/*` - API endpoints
- `/healthz` - Runtime health/readiness check

### Mobile API Readiness (Sanctum)
- `POST /api/v1/mobile/auth/login` - Issue Bearer token
- `GET /api/v1/mobile/auth/me` - Current authenticated user
- `POST /api/v1/mobile/auth/logout` - Revoke current token

Example login request:
```bash
curl -X POST http://localhost:8000/api/v1/mobile/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"vendor@africanmarket.com\",\"password\":\"password\",\"device_name\":\"android\"}"
```

Use returned token:
```bash
curl http://localhost:8000/api/v1/mobile/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Custom Tailwind Classes
- `btn-primary` - Primary buttons
- `btn-secondary` - Secondary buttons
- `card` - Card containers
- `input-field` - Form inputs

---

## 🛠️ Available Commands

### Development
```bash
php artisan serve              # Start server
npm run dev                    # Watch assets
php artisan route:list         # View routes
php artisan tinker             # Laravel REPL
```

### Database
```bash
php artisan migrate            # Run migrations
php artisan db:seed            # Seed data
php artisan migrate:fresh      # Fresh start
```

### Custom Commands
```bash
php artisan market:setup       # Setup database
php artisan inspire            # Get inspired!
```

---

## 📚 Documentation

| File | Description |
|------|-------------|
| **[README.md](../../README.md)** | Complete project overview |
| **[SETUP_GUIDE.md](setup-guide.md)** | Detailed installation steps |
| **[TECH_STACK.md](../architecture/tech-stack.md)** | Technology stack details |
| **[PRD.md](../product/prd.md)** | Product requirements |
| **[TODO.md](../product/todo.md)** | Development tasks |
| **[QUICKSTART.md](quickstart.md)** | This file |

---

## 🔧 Configuration Files

### Environment (.env)
```env
APP_NAME="Smart African Marketplace"
DB_DATABASE=african_market
DB_USERNAME=root
DB_PASSWORD=your_password

STRIPE_KEY=pk_test_...
PAYSTACK_PUBLIC_KEY=pk_test_...
```

### Payment (config/payment.php)
- Stripe integration
- Paystack integration
- Cash on delivery

### Delivery (config/delivery.php)
- Distance-based pricing
- GPS tracking
- Proof of delivery

---

## 🌍 Tech Stack

- **Framework**: Laravel 10+
- **Frontend**: Livewire 3 + Alpine.js
- **CSS**: Tailwind CSS 3
- **Database**: MySQL 8.0+
- **Payments**: Stripe + Paystack
- **Server**: Apache

---

## 🎯 Next Steps

### Week 1: Core Features
1. Install Laravel Breeze for authentication
   ```bash
   composer require laravel/breeze --dev
   php artisan breeze:install
   ```

2. Create Product model
   ```bash
   php artisan make:model Product -m
   ```

3. Build product catalog

### Week 2: Shopping Cart
1. Create cart Livewire component
2. Implement add to cart
3. Build checkout flow

### Week 3: Payments
1. Integrate Stripe
2. Integrate Paystack
3. Order management

---

## ✅ Checklist Before Starting

- [ ] PHP 8.1+ installed
- [ ] Composer installed
- [ ] MySQL running
- [ ] NPM installed
- [ ] Ran `composer install`
- [ ] Ran `npm install`
- [ ] Created `.env` file
- [ ] Set database password in `.env`
- [ ] Ran `php artisan key:generate`
- [ ] Ran `php artisan migrate`
- [ ] Started `php artisan serve`
- [ ] Started `npm run dev`

---

## 🐛 Quick Troubleshooting

**Issue**: Class not found  
**Fix**: `composer dump-autoload`

**Issue**: Database connection failed  
**Fix**: Check `.env` DB credentials

**Issue**: Vite not found  
**Fix**: `npm install`

**Issue**: Permission denied  
**Fix**: Ensure `storage/` and `bootstrap/cache/` are writable

---

## 💡 Pro Tips

1. **Use Telescope** for debugging:
   ```bash
   composer require laravel/telescope --dev
   php artisan telescope:install
   ```

2. **Format code** with Pint:
   ```bash
   vendor/bin/pint
   ```

3. **Use Tinker** for quick tests:
   ```bash
   php artisan tinker
   >>> User::count()
   ```

4. **Hot reload** assets:
   ```bash
   npm run dev
   # Edit CSS/JS and see changes instantly!
   ```

---

## 🚀 You're All Set!

Your Smart African Marketplace platform is ready for development!

```bash
# Start coding:
php artisan serve
npm run dev

# Visit:
http://localhost:8000
```

**Welcome to Smart African Marketplace! 🌍🎉**

---

**Need help?** Check:
- [SETUP_GUIDE.md](setup-guide.md) - Detailed guide
- [README.md](../../README.md) - Full documentation
- [Laravel Docs](https://laravel.com/docs) - Official docs
