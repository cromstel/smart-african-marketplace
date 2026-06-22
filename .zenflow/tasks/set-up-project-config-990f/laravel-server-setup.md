# Laravel Application Access Guide

## ❌ Common Mistake

**You're accessing the wrong port!**

- `http://localhost:5173/` = Vite dev server (asset bundler only)
- `http://localhost:8000/` = Laravel application ✅

## How Laravel + Vite Works

This is a **Laravel backend application** with Vite for frontend assets:

```
┌─────────────────────────────────────────┐
│ Laravel Server (Port 8000)              │
│ ├─ Renders Blade templates              │
│ ├─ Handles routes & controllers         │
│ └─ Loads assets from Vite               │
└─────────────────────────────────────────┘
                  ↓ (includes)
┌─────────────────────────────────────────┐
│ Vite Dev Server (Port 5173)             │
│ ├─ Hot reloads CSS/JS                   │
│ ├─ Compiles Tailwind CSS                │
│ └─ Bundles JavaScript                   │
└─────────────────────────────────────────┘
```

**Access the Laravel app, NOT the Vite server directly!**

---

## ✅ Correct Setup Process

### Step 1: Start Vite Dev Server (Terminal 1)

```bash
cd "C:\Users\Cardif Nordic\.zenflow\worktrees\set-up-project-config-990f"
npm run dev
```

**Expected output:**
```
VITE v5.0.8  ready in XXX ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
➜  press h + enter to show help
```

**Leave this running!** This provides hot reloading for CSS/JS changes.

### Step 2: Start Laravel Server (Terminal 2 - New Window)

Open a **second terminal** and run:

```bash
cd "C:\Users\Cardif Nordic\.zenflow\worktrees\set-up-project-config-990f"
php artisan serve
```

**Expected output:**
```
INFO  Server running on [http://127.0.0.1:8000].

Press Ctrl+C to stop the server
```

### Step 3: Access the Application

Open your browser and go to:

**🔗 http://localhost:8000** or **http://127.0.0.1:8000**

**NOT** ~~http://localhost:5173~~ ❌

---

## Alternative: Using Apache/Nginx

If you're using Apache or Nginx instead of `php artisan serve`:

### For Apache (XAMPP, WAMP, LAMP)

1. **Point your virtual host to the `public/` directory:**

```apache
<VirtualHost *:80>
    ServerName african-market.local
    DocumentRoot "C:/path/to/african-market/public"
    
    <Directory "C:/path/to/african-market/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

2. **Update your hosts file** (C:\Windows\System32\drivers\etc\hosts):
```
127.0.0.1    african-market.local
```

3. **Access via**: http://african-market.local

### Still need Vite running

Even with Apache/Nginx, you still need to run `npm run dev` in the background for asset hot reloading during development.

---

## Development Workflow

### Daily Development

**Terminal 1:**
```bash
npm run dev
# Leave running
```

**Terminal 2:**
```bash
php artisan serve
# Leave running
```

**Browser:**  
→ http://localhost:8000

### Production Build

When deploying to production:

```bash
# Build optimized assets (creates public/build/)
npm run build

# No need to run "npm run dev" in production
# Assets are compiled and ready in public/build/
```

---

## Troubleshooting

### Issue: "ERR_CONNECTION_REFUSED" on localhost:8000

**Cause:** Laravel server not running

**Solution:**
```bash
php artisan serve
```

### Issue: "Target class [DashboardController] does not exist"

**Cause:** Autoload cache issue

**Solution:**
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Issue: CSS/JS not loading or updating

**Cause:** Vite not running or old build artifacts

**Solution:**
```bash
# Make sure Vite is running
npm run dev

# Clear build cache
rm -rf public/build
npm run build
```

### Issue: Blank page or "500 Internal Server Error"

**Cause:** Environment or database configuration

**Solution:**
```bash
# Check .env file exists
copy .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Issue: Styles look broken

**Cause:** Tailwind not compiled or old cache

**Solution:**
```bash
# Stop Vite (Ctrl+C)
rm -rf node_modules
npm install
npm run dev
```

---

## Summary

| What | Port | Purpose | Access |
|------|------|---------|--------|
| **Laravel App** | 8000 | Main application | ✅ http://localhost:8000 |
| **Vite Dev Server** | 5173 | Asset hot reload | ❌ Don't access directly |

**Always access the Laravel application (port 8000), not Vite (port 5173)!**

The Vite server is just a helper that runs in the background to provide hot reloading during development. The actual application runs through Laravel.

---

## Quick Start Commands

```bash
# Terminal 1: Start Vite
npm run dev

# Terminal 2: Start Laravel  
php artisan serve

# Browser: Open
http://localhost:8000
```

That's it! The application should now be fully accessible.
