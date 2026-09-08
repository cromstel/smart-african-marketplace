# Getting Started

**Status: Current** — created 2026-09-08.

[Documentation Hub](../README.md) › Guides › Getting Started

From a fresh clone to a running application. For deployment see the
[Deployment Runbook](../DEPLOYMENT_RUNBOOK.md).

---

## Prerequisites

| Requirement | Version | Notes |
|---|---|---|
| PHP | 8.2+ | `composer.json` requires `^8.2`; CI runs 8.3 |
| Composer | 2.x | |
| MySQL | 8.0+ | MariaDB 10.6+ works |
| Node.js | 18+ | For Vite asset builds |
| Apache | 2.4+ | With `mod_rewrite`. Optional for local — `artisan serve` is enough |

PHP extensions: `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, `gd`, `fileinfo`,
`openssl`, `tokenizer`, `json`.

Windows users: see [Localhost on Apache (Windows)](../../documentation/localhost-apache-windows.md).

---

## Setup

### 1. Clone and install

```bash
git clone https://github.com/cromstel/smart-african-marketplace.git
cd smart-african-marketplace
composer install
npm install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`. The minimum to boot:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=african_market
DB_USERNAME=root
DB_PASSWORD=your_password
```

Everything else can wait. Payment keys are only needed when you work on
checkout — see [Configuration Reference](../reference/configuration.md).

### 3. Databases

Create both — the second is used by the test suite:

```sql
CREATE DATABASE african_market      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE african_market_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Migrate and seed

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

`db:seed` creates demo accounts — see [Demo User Credentials](../demo-user-credentials.md).

> Seeder passwords come from `DEMO_USER_PASSWORD` and `VENDOR_DEMO_PASSWORD`.
> Never set those in production.

### 5. Build assets and run

```bash
npm run dev        # Vite dev server, hot reload
php artisan serve  # http://localhost:8000
```

Two terminals. For a production-style build use `npm run build`.

---

## Verify the install

```bash
php artisan app:verify-readiness
php artisan test
php artisan route:list
```

Then open `http://localhost:8000` and sign in with a seeded account.

---

## Optional services

None are required for day-to-day development.

| Service | Needed for | Setup |
|---|---|---|
| Soketi | Realtime notifications, live auction bids | [Soketi Setup](../../documentation/soketi-websockets-setup.md) |
| Stripe / Paystack / Swish | Checkout work | Test keys — [Configuration](../reference/configuration.md#payments) |
| Twilio | SMS | Set `SMS_ENABLED=false` to skip |
| OpenAI | Chatbot and knowledge base | `OPENAI_API_KEY` |

---

## Orientation

Read in this order:

1. [Architecture Overview](../architecture/overview.md) — how it fits together
2. [Data Model](../architecture/data-model.md) — the 98 models by domain
3. [Testing Guide](testing.md) — how to prove your change
4. [Contributing](../../CONTRIBUTING.md) — how to land it

> **Before you build a screen:** the UI is server-rendered **Blade + Tailwind +
> Alpine.js**. Older documents described Livewire as the frontend — only two
> components use it. See
> [Frontend architecture](../architecture/overview.md#frontend-architecture).

---

## Common problems

| Symptom | Cause | Fix |
|---|---|---|
| `No application encryption key` | `APP_KEY` unset | `php artisan key:generate` |
| `SQLSTATE[HY000] [1049]` | Database missing | Create it (step 3) |
| Blank page, 500 | Storage not writable | `chmod -R 775 storage bootstrap/cache` |
| Assets 404 | Vite not running | `npm run dev` or `npm run build` |
| Images 404 | No storage symlink | `php artisan storage:link` |
| Config changes ignored | Cached config | `php artisan config:clear` |
| Tests fail on connection | Test DB missing | Create `african_market_test` |

Full list: [Troubleshooting](../../documentation/troubleshooting.md).

### Clearing caches

```bash
php artisan optimize:clear   # config, route, view, cache in one
```

> Avoid `config:cache` locally. It bakes `.env` values in, and nine `env()` calls
> in `app/` return `null` afterwards — see
> [Known configuration issues](../reference/configuration.md#known-configuration-issues).

---

## See also

- [Quickstart](../../QUICKSTART.md)
- [Setup Guide](../../SETUP_GUIDE.md)
- [Configuration Reference](../reference/configuration.md)
- [Artisan Commands](../reference/commands.md)
