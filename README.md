# Smart African Market

<p align="center">
  <img src="./public/assets/hero-banner.svg" alt="Smart African Market" width="100%">
</p>

> A self-hostable, multi-vendor marketplace for African food, fashion and salon
> services — with built-in delivery, bookings, auctions and subscriptions.

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql)](https://mysql.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**📖 [Documentation](user-guide/README.md)** — every document in this repository,
in one place.

---

## What this is

A multi-vendor e-commerce platform for the African diaspora market, covering
food, fashion and salon services. It is a Laravel monolith deployed the standard
Laravel way — PHP-FPM behind Nginx or Apache, MySQL, and a queue worker.

It is considerably more than a storefront. Thirteen domains are implemented
across ~100 models, ~162 controllers and ~159 migrations.

| Domain | What it does |
|---|---|
| **Catalogue & commerce** | Products, variants, categories, brands, cart, checkout, coupons, orders |
| **Multi-vendor** | Vendor stores, custom domains, balances, ledger, payouts, withdrawals, KYC |
| **Delivery** | Zones, distance pricing, drivers, assignment, GPS tracking, proof of delivery |
| **Salon & bookings** | Services, stylists, resources, slots, tiered pricing, reminders |
| **Auctions** | Listings, live bidding, proxy auto-bids, automated settlement |
| **Subscriptions** | Customer and vendor plans, trials, renewals, module entitlements |
| **Content & CMS** | Blog with revisions, static pages, hero sections, widgets, media library, SEO |
| **AI chatbot** | Retrieval-augmented assistant over a managed knowledge base |
| **Marketing** | Campaigns, attribution, newsletters, referrals, loyalty points |
| **Support & returns** | Tickets, enquiries, refund requests, RMA |
| **Trust & safety** | Reviews with images and helpful votes, activity audit log |
| **Compliance** | GDPR data-export requests, configuration audit trail |
| **Mobile** | Native Android app consuming the REST API |

> **On the installer:** a CMS-style web installer (`/install` wizard) is designed
> in the [PRD](user-guide/product/prd.md#3-installer-system-cms-style) but **is not
> implemented**. There is no `/install` route in this codebase — deploy with the
> [Deployment Runbook](user-guide/operations/deployment-runbook.md).

### Payments

Three rails, reflecting a Sweden + West Africa footprint:

| Provider | Covers |
|---|---|
| **Stripe** | Cards, Apple Pay, Google Pay (via Laravel Cashier) |
| **Paystack** | African cards, bank transfer, mobile money — M-Pesa, MTN, Airtel |
| **Swish** | Swedish instant payments |

Plus cash on delivery. All webhook handlers verify signatures with timing-safe
comparison and are idempotent.

### Realtime

Broadcasting runs on an **internal, self-hosted [Soketi](https://soketi.app)**
server over the Pusher protocol — no third-party push service is contacted.
`pusher-js` and Laravel Echo are used only as protocol clients. See the
[Soketi setup guide](user-guide/guides/soketi-websockets-setup.md).

---

## Tech stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Database | MySQL 8.0+ |
| Views | **Blade** + Tailwind CSS 3 + Alpine.js |
| Reactivity | Livewire 4 — used by exception, not by default (2 components) |
| Assets | Vite 8 |
| Auth | Laravel Sanctum, Socialite for OAuth |
| Realtime | Soketi (self-hosted) + Laravel Echo |
| Permissions | spatie/laravel-permission |
| Queue & cache | Database drivers by default — no Redis required |

Details: [Tech Stack](user-guide/architecture/tech-stack.md) ·
[Architecture Overview](user-guide/architecture/overview.md)

---

## Quick start

```bash
git clone https://github.com/cromstel/smart-african-marketplace.git
cd smart-african-marketplace

composer install && npm install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate

# create the database, then set DB_* in .env
php artisan migrate --seed
php artisan storage:link

npm run build                 # or: npm run dev
php artisan serve             # → http://localhost:8000
```

Realtime is optional for local work. To run the bundled Soketi server:

```bash
cd soketi && npm install && npm start
```

Full walkthrough, prerequisites and troubleshooting:
**[Getting Started](user-guide/guides/getting-started.md)**.

### Production install

Deploy as a standard Laravel application, then work through the
[Deployment Runbook](user-guide/operations/deployment-runbook.md) and the
[Staging Checklist](user-guide/operations/staging-deployment-checklist.md).

---

## Requirements

**Server:** PHP 8.2+ (CI covers 8.2 and 8.3), MySQL 8.0+ (or MariaDB 10.6+),
Apache 2.4+ with `mod_rewrite` or Nginx, 512 MB RAM minimum (2 GB recommended),
1 GB disk.

**PHP extensions:** `bcmath`, `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`,
`gd`, `fileinfo`, `openssl`, `tokenizer`, `json`.

> `bcmath` is not optional: Laravel Cashier depends on `moneyphp/money`, which
> requires it. Without it `composer install` fails outright.

**Build tools:** Composer 2.x, Node.js 20.19+ or 22 LTS (Vite 8 requirement).

---

## Documentation

Everything is indexed at **[user-guide/README.md](user-guide/README.md)**. The
most-used pages:

| | |
|---|---|
| [Getting Started](user-guide/guides/getting-started.md) | Clone to running app |
| [Architecture Overview](user-guide/architecture/overview.md) | How the system fits together |
| [Data Model](user-guide/architecture/data-model.md) | The models by domain |
| [Configuration Reference](user-guide/reference/configuration.md) | Every environment variable |
| [API Reference](user-guide/reference/api.md) | REST endpoints, auth, rate limits |
| [Artisan Commands](user-guide/reference/commands.md) | The 22 custom commands |
| [Testing Guide](user-guide/guides/testing.md) | Running and writing tests |
| [Contributing](CONTRIBUTING.md) | How to land a change |
| [Roadmap](user-guide/product/roadmap.md) | What we are building next, and why |
| [Documentation Audit](user-guide/product/documentation-audit.md) | Docs-vs-code findings |
| [Decision Records](user-guide/architecture/decisions/README.md) | Why things are the way they are |
| [User Guides](user-guide/customer-guide.md) | For customers, vendors and admins |

---

## Project status

Phases 1 and 2 of the original [Implementation Plan](user-guide/product/implementation-plan.md)
are substantially delivered: authentication, catalogue, cart and checkout,
payments, orders, the admin panel, multi-vendor, bookings, reviews, delivery and
a native Android app are all in the codebase.

**Current focus is verification, not new features.** A
[documentation audit](user-guide/product/documentation-audit.md) found a
feature-rich but under-tested platform and several documents that cited
verification which had never been written. The
[Roadmap](user-guide/product/roadmap.md) sequences the work: close the integrity
gaps, make the payment path provable, build a real test foundation, then extend.

Recently closed:

- [x] Registration endpoint throttled; SEO config no longer breaks under `config:cache`
- [x] Webhook fixtures, signature and replay-idempotency tests — **passing**
- [x] Six defects that stopped a clean clone from installing, migrating or
      running at all (see [Finding 10](user-guide/product/documentation-audit.md#finding-10--the-application-could-not-be-installed-or-migrated-from-a-clean-clone))

`php artisan migrate` runs all 159 migrations clean. The in-repo PHPUnit suite
covers **569 tests / 1,264 assertions**, and two Playwright specs cover the
service pages end to end.

Next up:

- [ ] Vendor-isolation tests across every vendor controller
- [ ] Mobile-money webhook coverage
- [ ] Decide on the four available major dependency upgrades

---

## Common commands

```bash
php artisan serve                       # dev server
php artisan migrate                     # run migrations
php artisan test                        # run the PHPUnit suite
php artisan route:list                  # inspect routes
php artisan optimize:clear              # clear all caches
php artisan app:verify-readiness        # pre-flight check
vendor/bin/pint                         # format PHP

npm run dev / npm run build             # assets
npm run lint / npm run format           # JS and CSS
npm run test                            # Playwright
npm run soketi                          # self-hosted websocket server
```

Full list: [Artisan Commands](user-guide/reference/commands.md).

---

## Before you deploy

> **Set `MOMO_WEBHOOK_SECRET` if you accept mobile money (MTN, M-Pesa).**
> The callback endpoint authenticates with an HMAC and **fails closed** — with
> no secret configured it refuses every callback with a 503, so mobile money
> payments will not be confirmed. `php artisan app:verify-readiness` flags this,
> and `--strict` blocks the deploy. See
> [Configuration](user-guide/reference/configuration.md#payments).

Run the readiness gate before every release:

```bash
php artisan app:verify-readiness --strict
```

---

## Security

Report vulnerabilities privately per [SECURITY.md](SECURITY.md). **Please do not
open a public issue for a security problem.**

The platform hashes passwords with bcrypt, protects forms with CSRF tokens, uses
Eloquent's parameter binding throughout, escapes output in Blade, authenticates
the API with Sanctum, rate-limits public endpoints (including `POST /auth/login`
at 5 attempts/minute), and verifies every payment webhook signature with
timing-safe comparison. Responses carry a restrictive CSP and standard security
headers via `app/Http/Middleware/SecurityHeaders.php`, and CORS is restricted in
`config/cors.php`.

> **Read the [Documentation Audit](user-guide/product/documentation-audit.md)
> before relying on any security claim in this repository.** It identifies one
> logged control that was never implemented, and several verification tests that
> do not exist. The controls listed above were confirmed present in code; the
> audit says exactly what was and was not verified.

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for workflow, commit conventions, code
standards and the review checklist.

Two documentation rules matter especially here: **never cite a file that does not
exist**, and **link every new document from the hub** at
[user-guide/README.md](user-guide/README.md).

---

## License

MIT — see [LICENSE](LICENSE).

---

**Made with care for the African diaspora community.**
*Connecting cultures through authentic products and services.*
