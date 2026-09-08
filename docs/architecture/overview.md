# Architecture Overview

**Status: Current** — verified against the codebase on 2026-09-08.

[Documentation Hub](../README.md) › Architecture › Overview

This document describes how Smart African Marketplace is actually built, as
opposed to how earlier documents described it. Read it before making structural
changes.

---

## Contents

- [The shape of the system](#the-shape-of-the-system)
- [Request lifecycle](#request-lifecycle)
- [Layers](#layers)
- [Domain modules](#domain-modules)
- [Frontend architecture](#frontend-architecture)
- [Background work](#background-work)
- [Payments](#payments)
- [Multi-tenancy and roles](#multi-tenancy-and-roles)
- [Known architectural debts](#known-architectural-debts)

---

## The shape of the system

A **Laravel 12 server-rendered monolith** with a REST API attached for the native
mobile clients. One deployable unit, one MySQL database, no service mesh.

| Concern | Choice |
|---|---|
| Framework | Laravel 12 on PHP 8.2+ |
| HTTP layer | Classic controllers (~146) returning Blade views |
| Views | ~330 Blade templates, Tailwind CSS 3, Alpine.js |
| Reactivity | Livewire 3 — used sparingly, see [Frontend architecture](#frontend-architecture) |
| Persistence | MySQL 8.0+ via Eloquent, ~148 migrations, ~98 models |
| Business logic | ~37 service classes under `app/Services/` |
| API | Laravel Sanctum token auth, versioned under `/api/v1` |
| Realtime | Soketi (self-hosted Pusher protocol) + Laravel Echo |
| Assets | Vite |

The deliberate constraint throughout is **self-hosting**: the platform is meant to
be uploadable to commodity Apache/PHP/MySQL hosting and installed through a web
wizard. That constraint explains several choices that would otherwise look dated
(database queue driver rather than Redis, local filesystem rather than object
storage).

> **Accuracy note.** Earlier documents claimed the platform uses *no* external
> services beyond payments. That is no longer true: Twilio (SMS), Soketi
> (websockets) and optional Firebase Cloud Messaging (push) are all integrated.
> See [Configuration Reference](../reference/configuration.md) for what each
> needs and whether it is optional.

---

## Request lifecycle

```
Browser / Mobile app
        │
        ▼
public/index.php ──► bootstrap/app.php ──► App\Http\Kernel
        │
        ▼
Global middleware      TrustProxies, TrimStrings, CompressResponse,
                       MaintenanceMode, SetLocale, CaptureCampaignContext
        │
        ▼
Route (routes/web.php · routes/api.php · routes/auth.php)
        │
        ▼
Route middleware       auth · auth:sanctum · admin · vendor · salon-owner
                       subscription gates · module access · throttle
        │
        ▼
Controller             validates request, delegates work
        │
        ▼
Service                business logic, transactions, external calls
        │
        ▼
Model (Eloquent)  ──►  MySQL
        │
        ├──► Observer      (Order, User, Vendor, KnowledgeBase)
        ├──► Event/Listener (booking lifecycle, chatbot)
        │
        ▼
Blade view  ·or·  JSON resource
```

### Route files

| File | Lines | Purpose |
|---|---|---|
| `routes/web.php` | ~708 | Storefront, customer, vendor and admin UI |
| `routes/api.php` | ~78 | Public catalogue, authenticated cart/orders, mobile auth |
| `routes/auth.php` | ~77 | Login, registration, password reset, verification |
| `routes/console.php` | ~26 | Closure-based console commands |

---

## Layers

### Controllers — `app/Http/Controllers/`

Grouped by audience rather than by resource:

| Group | Count | Responsibility |
|---|---|---|
| `Admin/` | 49 | Platform administration |
| `Vendor/` | 32 | Vendor storefront and back-office |
| `Api/` | 11 | JSON API for mobile clients |
| `Auth/` | 11 | Authentication flows |
| `Customer/`, `Shop/` | 4 | Customer account and shop surfaces |
| Root level | ~39 | Cross-cutting: cart, checkout, search, blog, salon, auctions |

Controllers should validate input and delegate. Business rules belong in services.

### Services — `app/Services/`

Where the domain logic lives. Grouped subdirectories carry the more involved
domains:

| Namespace | Contents |
|---|---|
| `Payment/` | `StripePaymentService`, `PaystackPaymentService` |
| `Auction/` | `AuctionService`, `BiddingService` |
| `Chatbot/` | `ChatbotService`, `KnowledgeBaseService` (retrieval-augmented) |
| `Shipping/` | `ShippingCalculatorService`, `ShippingLabelService` |
| `Subscription/` | `EffectiveSubscriptionResolver` |
| `Campaign/` | `CampaignTrackingService` |
| `Support/` | `SupportTicketService` |
| Root | Booking (5 services), configuration, SEO, media, loyalty, payouts, SMS, QR, geolocation, data export |

### Models — `app/Models/`

~98 Eloquent models. Every model declares `$fillable` rather than
`$guarded = []`, so mass-assignment is closed by default. See
[Data Model](data-model.md) for the domain grouping.

### Middleware — `app/Http/Middleware/`

Beyond Laravel's defaults, the notable ones:

| Middleware | Purpose |
|---|---|
| `AdminMiddleware`, `EnsureUserIsAdmin` | Admin authorization |
| `VendorMiddleware`, `EnsureUserIsVendor` | Vendor authorization |
| `EnsureUserIsSalonOwner` | Salon owner authorization |
| `CheckSubscription`, `CheckSubscriptionLimits` | Plan gating and quota enforcement |
| `EnsureModuleAccess` | Per-module entitlement checks |
| `PreventSpamSubmissions` | Public form abuse protection |
| `CaptureCampaignContext` | Attribution capture |
| `TrackUserBehavior` | Analytics events |
| `SetLocale` | Localisation (see `lang/`) |
| `MaintenanceMode` | Custom maintenance gate |

Note there are two middleware for both admin and vendor authorization. See
[Known architectural debts](#known-architectural-debts).

---

## Domain modules

The platform is considerably broader than a storefront. Each of these is a
first-class domain with its own models, services and controllers:

| Module | Core models |
|---|---|
| **Catalogue** | `Product`, `ProductVariant`, `ProductImage`, `Category`, `Brand`, `ProductAttribute` |
| **Commerce** | `Cart`, `Order`, `OrderGroup`, `OrderItem`, `Payment`, `Coupon` |
| **Multi-vendor** | `Vendor`, `VendorBalance`, `VendorLedger`, `Payout`, `Withdrawal`, `VendorVerification`, `VendorTheme` |
| **Delivery** | `Delivery`, `DeliveryDriver`, `DeliveryZone`, `Shipment` |
| **Salon & booking** | `Salon`, `SalonService`, `SalonStylist`, `Booking`, `BookingResource`, `BookingType` |
| **Auctions** | `Auction`, `AuctionBid`, `AuctionAutoBid`, `AuctionWinner` |
| **Subscriptions** | `SubscriptionPlan`, `UserSubscription`, `VendorSubscription`, `SubscriptionPayment` |
| **Content / CMS** | `BlogPost`, `BlogCategory`, `SitePage`, `HeroSection`, `Widget`, `MediaLibrary`, `SeoMeta` |
| **AI chatbot** | `ChatbotSession`, `ChatbotMessage`, `KnowledgeDocument`, `KnowledgeChunk` |
| **Marketing** | `Campaign`, `CampaignRecipient`, `Newsletter`, `Referral`, `LoyaltyPoint`, `LeadAttribution` |
| **Support & returns** | `SupportTicket`, `RefundRequest`, `RmaRequest`, `Inquiry` |
| **Trust & safety** | `Review`, `ReviewImage`, `ReviewHelpfulVote`, `ActivityLog` |
| **Compliance** | `DataExportRequest`, `ConfigurationAudit` |

None of this appeared in the README before this revision.

---

## Frontend architecture

**This is the most commonly misunderstood part of the system.**

Earlier documentation described the UI as "Livewire 3 — real-time reactive
components, no JavaScript frameworks needed". In practice:

| Claim | Reality |
|---|---|
| Livewire is the UI layer | 2 Livewire components exist (`LiveSearch`, one vendor component) |
| Livewire drives the views | 2 of ~330 Blade views reference a Livewire component |

The real frontend is **server-rendered Blade with Tailwind CSS and Alpine.js for
interactivity**. Livewire is present as a dependency and used for live search.

Treat Blade + Alpine as the default when building a new screen. Reach for
Livewire only when a component genuinely needs stateful server round-trips, and
say so in the pull request — the pattern is currently rare enough that
consistency matters more than convenience.

Other frontend facts:

- **Vite** builds assets; entry points in `vite.config.js`
- **Chart.js** for admin analytics
- **TinyMCE** for rich text editing
- **Laravel Echo + pusher-js** talking to self-hosted **Soketi**
- Translations live in `lang/`, switched by `SetLocale` middleware

---

## Background work

Queue driver defaults to `database` to avoid requiring Redis.

Custom Artisan commands under `app/Console/Commands/` handle recurring work:

| Area | Commands |
|---|---|
| Subscriptions | `ProcessSubscriptionRenewals`, `CheckExpiringSubscriptions`, `CheckTrialExpiry`, `SendTrialEndingReminders` |
| Auctions | `ProcessAuctions` |
| Bookings | `SendBookingReminders` |
| Commerce | `CheckLowStock`, `SendAbandonedCartRecoveryCommand` |
| Loyalty | `ExpireLoyaltyPointsCommand` |
| Marketing | `SendWelcomeEmailSeriesCommand`, `SendWinBackCampaignCommand` |
| Chatbot | `ReindexChatbotKnowledge`, `PurgeChatbotData` |
| Media | `BackfillMediaLibrary`, `OptimizePendingMediaCommand` |
| Ops | `VerifyReadinessCommand`, `ReplayWebhookFixtureCommand`, `GenerateSitemap`, maintenance toggles |

See [Queue Workers & Background Jobs](../operations/queue-workers.md)
for supervision, and [Artisan Commands](../reference/commands.md) for signatures.

> Note: `app/Jobs/` is empty. Asynchronous work is currently driven by scheduled
> commands rather than queued jobs. See [Known architectural debts](#known-architectural-debts).

---

## Payments

Three rails, reflecting the platform's Sweden + West Africa footprint:

| Provider | Handled by | Notes |
|---|---|---|
| **Stripe** | `StripePaymentService`, Laravel Cashier 16 | Cards, wallets |
| **Paystack** | `PaystackPaymentService` | African cards, bank transfer, mobile money (M-Pesa, MTN, Airtel) |
| **Swish** | `SwishPaymentController`, `SwishService` | Swedish instant payments |

All three verify webhook signatures before acting:

- Stripe via `\Stripe\Webhook::constructEvent()`
- Paystack via `hash_equals()` against an HMAC of the raw body
- Swish via `hash_equals()` on the computed signature

Webhook handling is idempotent — replaying an event must not duplicate a payment.

> **Verification.** The signature checks are implemented, and as of 2026-09-08
> `tests/Feature/WebhookE2ETest.php` covers acceptance, rejection and replay
> idempotency for both providers. Those tests have not yet been executed — see
> the [Pending Closure Matrix](../security/pending-closure-matrix.md).

---

## Multi-tenancy and roles

Not tenant-isolated at the database level. A single database holds all vendors;
separation is enforced in application code through ownership scoping and the
vendor middleware.

Roles come from `spatie/laravel-permission` alongside a local `Role` model.
Principal roles: **customer**, **vendor**, **salon owner**, **admin**.

Authorization lives in `app/Policies/` (4 policies) plus the middleware above.

> Because isolation is application-enforced rather than database-enforced, every
> vendor-scoped query must constrain by vendor ownership. A missing constraint is
> a cross-tenant data leak, not merely a bug. Policies and tests should treat it
> that way.

---

## Known architectural debts

Recorded here so they are visible rather than rediscovered. Scheduled in the
[Roadmap](../product/roadmap.md).

1. **Legacy bootstrap on a modern framework.** `bootstrap/app.php` uses the
   Laravel 10-era structure (explicit `Http\Kernel`, `Console\Kernel`,
   `ExceptionHandler` singletons) while `composer.json` requires
   `laravel/framework: ^12.0`. It works, but every Laravel 11/12 tutorial and
   upgrade guide will describe a skeleton this project does not have.

2. **Duplicate authorization middleware.** `AdminMiddleware` and
   `EnsureUserIsAdmin` coexist, as do `VendorMiddleware` and `EnsureUserIsVendor`.
   Two implementations of one security rule is a divergence risk.

3. **`env()` called outside `config/`.** ~~Nine call sites.~~ Reduced to three
   on 2026-09-08: `SeoService` now reads `config('seo.*')`. The remainder are
   `ConfigurationService` (keys computed at runtime) and `VerifyReadinessCommand`
   (deliberate environment inspection); both still misbehave under
   `config:cache`.

4. **Empty `app/Jobs/`.** Long-running work runs inside scheduled commands rather
   than queued jobs, so it cannot be retried or scaled independently.

5. **Test coverage.** 6 test files against 146 controllers and 98 models.

6. ~~**PHP version drift.**~~ **Resolved** — CI runs a matrix of 8.2 (the
   `composer.json` floor) and 8.3.

---

## See also

- [Data Model](data-model.md)
- [Decision Records](decisions/README.md)
- [Configuration Reference](../reference/configuration.md)
- [API Reference](../reference/api.md)
- [Testing Guide](../guides/testing.md)
