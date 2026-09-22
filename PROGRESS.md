# PROGRESS

Working memory for this repo. Keep this file current and lean — link out to
`docs/` for anything that isn't state or the active task. Rewrite sections in
place; don't append a history log here.

Last updated: 2026-09-22.

---

## Project snapshot

Laravel 12 / PHP 8.2+ multi-vendor marketplace ("Smart African Market" —
renamed 2026-09-22 from "Smart African Marketplace"; brand text only, not
code identifiers, see below). Blade + Tailwind + Alpine frontend (Livewire
only by exception — see
[ADR 0002](docs/architecture/decisions/0002-blade-over-livewire.md)). MySQL 8,
database queue/cache drivers (no Redis requirement). 98 models, ~146
controllers, ~150 migrations, 3 payment rails (Stripe, Paystack, Swish) plus
mobile money and COD.

Full picture: [README](README.md) · [Architecture Overview](docs/architecture/overview.md) ·
[Documentation Hub](docs/README.md).

Roadmap Phases 0–3 (integrity gaps, provable payment path, real test
foundation, structural debt) are **all shipped and verified** — see
[docs/product/roadmap.md](docs/product/roadmap.md). Only Phase 4 (ongoing
platform enhancement: performance, accessibility, frontend consistency, API
maturity, observability) remains open there.

`docs/product/todo.md` is a **historical** backlog (superseded by the
roadmap) — don't pull tasks from it.

---

## Current session

**Subscription lifecycle fix in progress, not yet committed.** The rename
and SEO work below are committed (`13a0f3b`, `dd9bbd0`). Working tree right
now has: `routes/console.php`, `app/Console/Commands/ProcessSubscriptionRenewals.php`,
`tests/Feature/SubscriptionLifecycleTest.php` (new). 314 tests pass, Pint
clean.

4. **Subscription lifecycle was never running** — continuing the
   browse-testing sweep into subscriptions surfaced the highest-value bug
   found this session. Three commands existed but were **never scheduled**,
   not in `routes/console.php` and not in the pre-3.3 `Kernel.php` it
   replaced (confirmed via `git show` on the pre-migration commit — this
   predates that migration, not a regression from it):
   `subscriptions:check-trial-expiry`, `subscriptions:process-renewals`,
   `subscriptions:send-trial-reminders`. Since
   `UserSubscription::isActive()` only checks `status`, never
   `current_period_end`, the gap was silent: trials never converted to
   billed subscriptions or expired, active subscriptions never renewed
   (`current_period_end` just sat in the past forever with no new
   `SubscriptionPayment` rows), and grace-period cancellations never
   actually canceled anything. Added all three to the schedule.
   **Scheduling `process-renewals` for the first time immediately surfaced
   a real, previously-untested bug in it**: its renewal query didn't
   exclude `cancel_at_period_end = true`, so a customer who explicitly
   canceled "at period end" would get silently auto-renewed (and re-billed)
   instead of canceled, because the renewal loop ran before the
   grace-period-cancellation loop and unconditionally pushed
   `current_period_end` back into the future. Fixed by excluding
   `cancel_at_period_end` from the renewal query. New coverage:
   `tests/Feature/SubscriptionLifecycleTest.php` (7 tests) — schedule
   registration itself, trial→paid conversion, trial→expired on a free
   plan, renewal advancing the period + recording a payment, the
   grace-period-cancellation-vs-renewal race, and the reminder email.

Earlier this session (committed):

1. **Rename**: "Smart African Marketplace" → "Smart African Market" across
   67 tracked files (docs, config, Blade, lang/*, mobile app strings) —
   display text only. Deliberately left untouched: Android package id
   `com.africanmarket.app`, composer package name `african-market/ecommerce`,
   npm package name, `admin@africanmarket.com`-style domains/emails — those
   are code/infra identifiers, not branding, and renaming them is a separate,
   much riskier decision (Play Store listing continuity for the Android
   `applicationId` in particular) nobody asked for.
2. **SEO audit** (this was the "untouched area" of the browse-testing
   sweep — SEO/sitemap infra had never been touched by the prior sessions'
   bug-fix run). Found and fixed, in order of severity:
   - `public/robots.txt` was a **static file** containing the literal,
     unprocessed text `{{ url('/sitemap.xml') }}` — Blade syntax served
     verbatim to every crawler, forever, since static files in `public/`
     never reach Laravel's router. The sitemap has never been discoverable
     via robots.txt. Fixed by making `/robots.txt` a real route
     (`SitemapController::robots()`) and deleting the static file.
   - A legacy `sitemap:generate` Artisan command was scheduled **daily** and
     wrote a static `public/sitemap.xml` covering only 3 URL types. Because
     Apache serves an existing static file before it reaches Laravel's
     router, that file — once written once, in any real deployment —
     **permanently shadowed** the actually-comprehensive dynamic
     `SitemapController@index` (a proper sitemap index: static pages,
     products, categories, salon, blog, auctions, vendors). Deleted the dead
     command and its schedule entry; the dynamic route is now the only path.
   - `resources/views/shop/show.blade.php` hand-rolled Product JSON-LD by
     interpolating `{{ }}` directly into a JSON string literal — Blade only
     HTML-escapes, it doesn't escape JSON control characters, so a
     description with a newline (routine for a textarea field) produced
     invalid JSON-LD that Google silently rejected.
   - **The whole `SeoService::addStructuredData()` /
     `getStructuredData()` mechanism was dead code** — six controllers
     (Shop, Blog, Salon, Auction, Page, VendorStore) built Product/Article/
     Breadcrumb/Organization schema and threw it away because (a)
     `SeoService` wasn't bound as a singleton, so a fresh empty instance
     resolved wherever it was next read, and (b) nothing ever rendered
     `getStructuredData()` in the first place.
   - **Deeper version of the same bug on titles/descriptions**:
     `layouts/shop.blade.php`'s `<x-seo>` reads `$seoTitle`/`$seoDescription`/
     etc. as plain Blade variables, but no controller (Shop, Blog, Salon,
     Auction, Page) ever passed them — every page on that layout silently
     fell back to generic site-wide meta regardless of what
     `SeoService::setTitle()` etc. had built. Fixed with a `View::composer`
     on `layouts.shop` in `AppServiceProvider::boot()` that feeds the
     singleton's state in — one fix, covers shop/blog/salon/auctions and any
     future page on that layout.
   - `vendor-store/show.blade.php` (per-vendor storefronts) had the same
     bug plus a duplicate empty `<title>` from an unconfigured `<x-seo />`
     sitting next to a hardcoded one. Fixed directly (this view has its own
     bespoke `<head>`, not `layouts.shop`).
   - `generateProductSchema()` checked `$product->stock` (column doesn't
     exist — real column is `stock_quantity`), so it would always have
     reported every product `OutOfStock`; also added `brand` and switched
     `image` to a fully-qualified URL. Never shipped before this session, so
     not a live regression, just fixed before wiring it up.
   - Google Analytics / Facebook Pixel IDs were read via raw `env()` in a
     Blade view — same "breaks under `config:cache`" bug class Roadmap 0.3
     fixed, but that fix only audited `app/`, not `resources/views/`. Added
     `config('services.google_analytics.id')` / `.facebook_pixel.id`.
   - New coverage: `tests/Feature/SeoTest.php` (4 tests) — robots.txt,
     sitemap index, product-page JSON-LD with an adversarial description
     (newline/quote/backslash/`</script>`), vendor storefront title +
     Organization schema.
3. **Phase 4 (performance) spot-check**: audited `ShopController::index`
   and `SalonController::index` for N+1 — both already select specific
   columns and eager-load what their views touch; no issue found. Didn't
   extend further (49 admin + 32 vendor controllers is out of scope for one
   pass) — a full N+1 audit is still open on the roadmap.

Next candidates, not yet chosen:
- Continue the browse-testing sweep into the remaining auction/checkout
  edge cases, or the legacy `VendorSubscription` system (the older, parallel
  subscription table `EffectiveSubscriptionResolver` bridges from — not
  audited this session beyond confirming the bridge exists).
- Broader Phase 4 N+1 audit across admin/vendor controllers.
- Accessibility (WCAG) pass — see
  [roadmap.md §4.2](docs/product/roadmap.md#42-accessibility-and-responsive-quality).
- `SubscriptionService::changePlan()` (upgrade/downgrade) doesn't recalculate
  `current_period_end` or charge/credit proration — worth a deliberate look
  at whether that's intended (bill-at-next-renewal) or a gap.

---

## Recently completed (last 30 commits, most recent first)

A sustained browse-and-fix session across the live app, not tied to a
specific roadmap item — finding real bugs by using the product rather than
reading code. Grouped by area:

**Vendor dashboard**
- Coupon `usage_limit` / `per_user_limit` not enforced at checkout (064d691)
- Product variant management: undefined relation, silently dropped attributes (b4de457)
- Manual vendor orders crashing, leaking raw SQL errors (9b109f7)
- Vendor coupon form missing usage-limit/expiry fields (a8551eb)
- Sidebar brand text overflow on mobile (bf1ddcf)
- Layout breakage on mobile/tablet — unclosed sidebar home link (1d48718)
- Vendors getting 404 on immediately-published announcements (a3cddac)
- Booking actions shown that the server always rejects (d7a4760)
- Blank verification status on first view (619e7a7)
- Auto-assign free subscription plan on admin approval (6ff4ff6)

**Checkout / customer**
- Checkout blocked for customers without a dedicated billing address (77351fb)
- Customer dashboard showing cancelled bookings as "Upcoming" (26c180a)
- Broken Alpine.js slot picker and search suggestions — `@json` in HTML attrs (96ec92d)
- Booking access blocked by vendor subscription gate (aea7763)
- Salon service pages showing wrong category (8e02d95)
- Search-suggestions dropdown JS syntax error — unescaped quote (7686a27)

**Admin**
- Layout blank-page bug on tablet/mobile viewports (843637a)
- Wrong column name in admin analytics CSV exports (9c6fdc9)
- Vendor verification approve/reject not guarded against re-review (090c47b)
- Duplicate flash messages, stale password hint (59b4c57)

**Platform / cross-cutting**
- DB outage crashing constructors of Stripe/Paystack/Swish/SMS services (0eda18e)
- Post-commit mail/event failure blocking login on registration (ffd5a64)
- Hidden vendor fields blocking customer registration (4383a5e)
- Two pre-existing test bugs found while browse-testing (3bd58ac)
- `bootstrap/app.php` migrated to Laravel 11/12 slim skeleton (f9a968d, roadmap 3.3)

Full history: `git log --oneline`.

---

## Architectural constraints (don't relitigate these)

- **Controllers validate and delegate; business logic lives in `app/Services/`.**
- **Every model declares `$fillable`** — no `$guarded = []`, codebase is 100% compliant.
- **No `env()` outside `config/`** — breaks under `config:cache`. Add a config file.
- **Never interpolate into `DB::raw()`/`whereRaw()`** — bind parameters.
- **Every vendor query must be scoped by ownership** — tenancy is enforced in
  app code, not the DB. A missing constraint is a cross-tenant data leak (see
  the `Vendor/*IsolationTest` pattern — 19 controllers covered, [roadmap
  §2.3](docs/product/roadmap.md#23-vendor-isolation-as-a-first-class-test-pattern--target-set-complete)).
- Full standards: [CONTRIBUTING.md](CONTRIBUTING.md#code-standards).

---

## Commands

```bash
vendor/bin/pint                          # format PHP — run before every commit
php artisan test                         # full suite (Unit + Feature)
php artisan test --testsuite=Feature     # feature only
php artisan route:list                   # inspect routes
php artisan app:verify-readiness --strict  # pre-deploy gate
```

Commit convention: `fix:`, `feat:`, `refactor:`, `test:`, `docs:`, `chore:` —
imperative subject ("add", not "added"). See [CONTRIBUTING.md](CONTRIBUTING.md#commit-convention).

Test suite baseline: 307 passed, verified 2026-09-22 (up from 258 at the last
roadmap update — +49 from this session's SEO fixes and their tests, plus
whatever landed between). Re-verify with `php artisan test` before trusting
this number — don't cite it without running it.

---

## References

- [Roadmap](docs/product/roadmap.md) — current prioritised plan, Phase 4 open items
- [Documentation Audit](docs/product/documentation-audit.md) — evidence behind the roadmap
- [Architecture Overview](docs/architecture/overview.md)
- [Testing Guide](docs/guides/testing.md)
- [CONTRIBUTING.md](CONTRIBUTING.md) — workflow, code standards, review checklist
- [Documentation Hub](docs/README.md) — index of everything else
