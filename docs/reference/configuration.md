# Configuration Reference

**Status: Current** — verified against `.env.example` and `config/` on 2026-09-08.

[Documentation Hub](../README.md) › Reference › Configuration

Every environment variable the platform reads, grouped by concern.
`.env.example` defines 127 keys; `config/` reads 108. This page reconciles both
and flags the mismatches.

> **Never commit `.env`.** It is gitignored. `.env.example` is the template and
> must never contain a real credential.

---

## Contents

- [How configuration is loaded](#how-configuration-is-loaded)
- [Application](#application)
- [Database](#database)
- [Session, cache and queue](#session-cache-and-queue)
- [Mail](#mail)
- [Payments](#payments)
- [SMS and push](#sms-and-push)
- [Social login](#social-login)
- [Broadcasting and realtime](#broadcasting-and-realtime)
- [Commerce and delivery](#commerce-and-delivery)
- [Vendor and commission](#vendor-and-commission)
- [Security](#security)
- [SEO, social and analytics](#seo-social-and-analytics)
- [AI and chatbot](#ai-and-chatbot)
- [Installer and maintenance](#installer-and-maintenance)
- [Known configuration issues](#known-configuration-issues)

---

## How configuration is loaded

```
.env  ──►  config/*.php  ──►  config('key.path')  ──►  application code
```

**Application code must read `config()`, never `env()`.** In production the
deployment runbook runs `php artisan config:cache`, after which `env()` returns
`null` outside config files. Nine call sites currently violate this — see
[Known configuration issues](#known-configuration-issues).

Config files: `app`, `auction`, `auth`, `branding`, `broadcasting`, `cache`,
`cors`, `database`, `delivery`, `filesystems`, `logging`, `mail`, `maintenance`,
`modules`, `payment`, `permission`, `queue`, `sanctum`, `services`, `session`,
`settings`, `vendor-store`, `view`.

---

## Application

| Variable | Default | Notes |
|---|---|---|
| `APP_NAME` | Smart African Marketplace | Display name |
| `APP_ENV` | `local` | `production` in production |
| `APP_KEY` | — | **Required.** `php artisan key:generate` |
| `APP_DEBUG` | `true` | **Must be `false` in production** — leaks stack traces |
| `APP_URL` | `http://localhost` | Absolute URL generation, webhooks, emails |
| `APP_VERSION` | — | Surfaced in readiness output |
| `APP_TIMEZONE` | `UTC` | ⚠️ Declared twice in `.env.example` |
| `APP_LOCALE` | `en` | ⚠️ Declared twice |
| `APP_FALLBACK_LOCALE` | `en` | ⚠️ Declared twice |
| `APP_SUPPORTED_LOCALES` | — | Comma-separated; drives `SetLocale` middleware |
| `ASSET_URL` | — | CDN asset host. *Read by config, absent from `.env.example`* |
| `LOG_CHANNEL` / `LOG_STACK` | `stack` | See `config/logging.php` |
| `LOG_LEVEL` | `debug` | Use `warning` or above in production |
| `LOG_DAILY_DAYS` | 14 | Daily log retention |

### Vendor storefront domains

| Variable | Notes |
|---|---|
| `VENDOR_STORE_SCHEME` | `http` or `https` for vendor subdomains |
| `VENDOR_STORE_BASE_DOMAIN` | Base domain vendor stores hang off |
| `VENDOR_STORE_DOMAIN_TARGET` | CNAME target for custom domains |

See [Enterprise Custom Domains](../user-guide/enterprise-custom-domains.md).

---

## Database

| Variable | Default | Notes |
|---|---|---|
| `DB_CONNECTION` | `mysql` | MySQL 8.0+ / MariaDB 10.6+ |
| `DB_HOST` / `DB_PORT` | `127.0.0.1` / `3306` | |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | — | **Required** |
| `DB_PERSISTENT` | `false` | Persistent PDO connections |
| `DB_CONNECTION_TIMEOUT` | — | Connection timeout (seconds) |
| `DATABASE_URL` | — | Connection-string alternative. *Not in `.env.example`* |
| `DB_FOREIGN_KEYS` | `true` | SQLite only. *Not in `.env.example`* |

---

## Session, cache and queue

Defaults avoid Redis so the platform runs on commodity hosting.

| Variable | Default | Notes |
|---|---|---|
| `SESSION_DRIVER` | `database` | |
| `SESSION_LIFETIME` | `120` | Minutes |
| `SESSION_DOMAIN` | — | **Security-relevant** cookie scope. *Not in `.env.example`* |
| `CACHE_DRIVER` | `database` | |
| `CACHE_PREFIX` | — | Needed when several apps share a cache store |
| `SETTINGS_CACHE_TTL` | — | `config/settings.php` cache lifetime |
| `QUEUE_CONNECTION` | `database` | See [Queue Workers](../operations/queue-workers.md) |
| `QUEUE_FAILED_DRIVER` | `database-uuids` | Failed-job storage |

Memcached, Redis, DynamoDB and SQS keys exist in `config/` for operators who want
them, but are intentionally absent from `.env.example`.

---

## Mail

| Variable | Notes |
|---|---|
| `MAIL_MAILER` | `smtp`, `log`, `array`, `sendmail` |
| `MAIL_HOST` / `MAIL_PORT` | SMTP endpoint |
| `MAIL_USERNAME` / `MAIL_PASSWORD` | SMTP credentials |
| `MAIL_ENCRYPTION` | `tls` recommended |
| `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME` | Envelope sender |
| `MAIL_TO` | **Dev catch-all — redirects _all_ outgoing mail.** Must be empty in production |
| `MAIL_EHLO_DOMAIN` | EHLO identity |
| `MAIL_LOG_CHANNEL` | Channel when `MAIL_MAILER=log` |

> `MAIL_TO` left set in production sends every customer email to one inbox and
> nothing to customers. Assert it is empty in your release checklist.

---

## Payments

### Stripe

| Variable | Notes |
|---|---|
| `STRIPE_KEY` | Publishable key |
| `STRIPE_SECRET` | **Secret.** Server-side only |
| `STRIPE_WEBHOOK_SECRET` | Required for signature verification |

### Paystack

| Variable | Notes |
|---|---|
| `PAYSTACK_PUBLIC_KEY` | Publishable key |
| `PAYSTACK_SECRET_KEY` | **Secret.** Also the webhook HMAC key |
| `PAYSTACK_PAYMENT_URL` | `https://api.paystack.co` |
| `MERCHANT_EMAIL` | Merchant identity on transactions |

Covers cards, bank transfer and mobile money (M-Pesa, MTN, Airtel).

### Swish

| Variable | Notes |
|---|---|
| `SWISH_PRODUCTION_URL` | Production endpoint |
| `SWISH_SANDBOX_URL` | Sandbox endpoint |

See [Swish Payment Integration](../modules/swish-payment-integration.md).

### Cash on delivery

| Variable | Notes |
|---|---|
| `COD_ENABLED` | Toggle. *Read by `config/payment.php`, absent from `.env.example`* |
| `COD_FEE` | Surcharge. *Same* |

---

## SMS and push

| Variable | Notes |
|---|---|
| `SMS_ENABLED` | Master toggle |
| `TWILIO_ACCOUNT_SID` / `TWILIO_AUTH_TOKEN` | **Secret** |
| `TWILIO_PHONE_NUMBER` | Sending number |

Africa's Talking keys are present but commented out. Push delivery is handled by
`FcmPushService`.

---

## Social login

Laravel Socialite.

| Variable | Notes |
|---|---|
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` | Google OAuth |
| `FACEBOOK_CLIENT_ID` / `FACEBOOK_CLIENT_SECRET` / `FACEBOOK_REDIRECT_URI` | Facebook OAuth |

Redirect URIs must match the provider console exactly, including scheme and port.

---

## Broadcasting and realtime

Self-hosted [Soketi](../guides/soketi-websockets-setup.md) speaking the
Pusher protocol.

| Variable | Notes |
|---|---|
| `BROADCAST_DRIVER` | `pusher` for Soketi, `log` to disable |
| `PUSHER_APP_ID` / `PUSHER_APP_KEY` / `PUSHER_APP_SECRET` | Soketi credentials |
| `PUSHER_HOST` / `PUSHER_PORT` / `PUSHER_SCHEME` | Soketi endpoint |
| `VITE_PUSHER_*` | Mirrored to the browser bundle |

> `VITE_`-prefixed values are **compiled into public JavaScript**. Never put a
> secret behind a `VITE_` prefix. `PUSHER_APP_SECRET` has no `VITE_` counterpart
> for exactly this reason.

---

## Commerce and delivery

| Variable | Notes |
|---|---|
| `DEFAULT_CURRENCY` / `CURRENCY_SYMBOL` | Display and charge currency |
| `DISTANCE_UNIT` | `km` or `mi` |
| `DEFAULT_DELIVERY_RADIUS` | Default zone radius |
| `BASE_DELIVERY_FEE` | Flat component |
| `PER_KM_RATE` | Distance component |
| `POD_PHOTO_REQUIRED` | Proof of delivery photo. *Not in `.env.example`* |
| `POD_SIGNATURE_REQUIRED` | Proof of delivery signature. *Not in `.env.example`* |
| `MAX_UPLOAD_SIZE` | Bytes; must not exceed PHP `upload_max_filesize` |
| `ALLOWED_IMAGE_TYPES` / `ALLOWED_DOCUMENT_TYPES` | Upload allow-lists |

---

## Vendor and commission

| Variable | Notes |
|---|---|
| `DEFAULT_COMMISSION_RATE` | Platform take rate |
| `VENDOR_PAYOUT_MINIMUM` | Withdrawal threshold |

---

## Security

| Variable | Default | Notes |
|---|---|---|
| `REGISTRATION_ENABLED` | `true` | Close public signup |
| `TWO_FACTOR_ENABLED` | — | 2FA (see `User` model) |
| `MIN_PASSWORD_LENGTH` | `8` | |
| `REQUIRE_UPPERCASE` / `REQUIRE_NUMBER` / `REQUIRE_SPECIAL_CHAR` | — | Complexity rules |
| `SANCTUM_EXPIRATION` | — | API token lifetime (minutes). Null = never expires |
| `SANCTUM_STATEFUL_DOMAINS` | — | **Security-relevant.** *Not in `.env.example`* |
| `API_RATE_LIMIT` | — | Default API throttle |
| `ADMIN_PREFIX` / `VENDOR_PREFIX` / `SALON_PREFIX` | — | Dashboard route prefixes |
| `TELESCOPE_ENABLED` | `false` | **Never enable in production** — exposes requests and payloads |
| `DEMO_USER_PASSWORD` / `VENDOR_DEMO_PASSWORD` | — | Seeder passwords. **Never set in production** |

---

## SEO, social and analytics

| Variable | Notes |
|---|---|
| `SEO_TITLE` / `SEO_DESCRIPTION` / `SEO_KEYWORDS` | Defaults. ⚠️ read via `env()` — see below |
| `FACEBOOK_URL` / `TWITTER_URL` / `INSTAGRAM_URL` / `YOUTUBE_URL` | ⚠️ read via `env()` |
| `GOOGLE_ANALYTICS_ID` | GA measurement ID |
| `FACEBOOK_PIXEL_ID` | Meta pixel |
| `SENTRY_LARAVEL_DSN` | Error tracking |

---

## AI and chatbot

| Variable | Notes |
|---|---|
| `OPENAI_API_KEY` | **Secret.** Powers `ChatbotService` and `KnowledgeBaseService` |

Reindex with `php artisan chatbot:reindex-knowledge`; purge with
`php artisan chatbot:purge-data`.

---

## Installer and maintenance

| Variable | Notes |
|---|---|
| `INSTALLED` | Set `true` after the web installer completes |
| `INSTALL_LOCK` | Blocks re-running the installer |
| `BACKUP_ENABLED` / `BACKUP_FREQUENCY` / `BACKUP_RETENTION_DAYS` | See [Backup Runbook](../operations/backup-verification.md) |
| `MAINTENANCE_ALLOWED_IPS` | Bypass list. *Not in `.env.example`* |
| `MAINTENANCE_RETRY_AFTER` | `Retry-After` header |

> Leaving `INSTALL_LOCK` unset in production allows the installer to be re-run by
> anyone who finds `/install`. Verify it before going live.

---

## Known configuration issues

Tracked in the [Roadmap](../product/roadmap.md), Phase 0.

**1. `env()` called outside `config/`** — **largely fixed on 2026-09-08.**
`SeoService` now reads `config('seo.*')` via the new `config/seo.php`, so SEO
metadata and social links survive `config:cache`. Three call sites remain by
design: `ConfigurationService` (runtime-computed keys) and
`VerifyReadinessCommand` (deliberately inspecting raw environment). Both are
still incorrect under `config:cache` and are tracked in
[Roadmap 0.3](../product/roadmap.md#03-move-env-calls-into-config--medium--implemented-partial).

The original problem, for the record:

```php
return $this->meta['description'] ?? env('SEO_DESCRIPTION', '…');
env('FACEBOOK_URL'), env('TWITTER_URL'), env('INSTAGRAM_URL'), env('YOUTUBE_URL')
```

After `php artisan config:cache` these return `null`. The deployment runbook
instructs that command, so **following the documented deploy silently empties SEO
descriptions and social links in production.**

**2. Duplicate keys in `.env.example`** — `APP_TIMEZONE`, `APP_LOCALE` and
`APP_FALLBACK_LOCALE` are each declared twice. The later assignment wins, which
is confusing and easy to mis-edit.

**3. Keys read by `config/` but missing from `.env.example`** — most consequential:
`SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`, `COD_ENABLED`, `COD_FEE`,
`POD_PHOTO_REQUIRED`, `POD_SIGNATURE_REQUIRED`, `MAINTENANCE_ALLOWED_IPS`,
`ASSET_URL`, `CACHE_PREFIX`. Operators cannot discover these without reading
`config/`.

### Verifying configuration

```bash
php artisan app:verify-readiness            # non-strict
php artisan app:verify-readiness --strict   # production gate
```

---

## See also

- [Architecture Overview](../architecture/overview.md)
- [Deployment Runbook](../operations/deployment-runbook.md)
- [API Reference](api.md)
- [Generating App Keys & Tokens](../guides/generating-keys-and-tokens.md)
