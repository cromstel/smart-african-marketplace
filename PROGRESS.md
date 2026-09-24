# PROGRESS

Working memory for this repo. Keep this file current and lean — link out to
`docs/` for anything that isn't state or the active task. Rewrite sections in
place; don't append a history log here.

Last updated: 2026-09-24.

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
maturity, observability) remains open there — this session made real
progress on 4.1 (performance) and 4.2 (accessibility), both still open for
more.

`docs/product/todo.md` is a **historical** backlog (superseded by the
roadmap) — don't pull tasks from it.

---

## Current session

**Idle — nothing in progress, tree clean.** This session built the missing
booking-types admin UI (item 0 below). Test suite: 381 passing.

Candidates for next time, not yet started:
- **Service pricing tiers have no UI anywhere.** `ServicePricingTier`
  (keyed on `salon_service_id`) and `BookingPricingService` exist, but no
  admin or vendor screen creates tiers. The five `admin.booking-types.*`
  pricing-tier/reorder routes that pointed at never-written controller
  methods were removed (item 0); a real UI belongs on salon services, not
  booking types.
- `BookingService::createBooking()` and `BookingPricingService::calculateRefund()`
  have no callers — the live booking flow in `BookingController` bypasses
  both. Worth deciding whether to wire them in or delete them.
- Broader Phase 4 N+1 audit beyond what the test suite exercises —
  `Model::preventLazyLoading()` is on now (item 3), but only catches N+1 on
  code paths something actually calls; large parts of the 49 admin + 32
  vendor controllers still have no test at all.
- `SubscriptionService::changePlan()` (upgrade/downgrade) doesn't
  recalculate `current_period_end` or charge/credit proration — worth a
  deliberate look at whether that's intended (bill-at-next-renewal) or a
  gap. Not touched this session.
- The legacy `VendorSubscription` system `EffectiveSubscriptionResolver`
  bridges from — confirmed it exists and how the bridge works (item 5), but
  not independently audited for bugs of its own.
- Continue the browse-testing sweep into auction/checkout edge cases not
  yet covered.
- Accessibility: only the focus-visible class of bug was audited this pass
  (item 1). Alt text, ARIA labels on icon-only controls, color contrast,
  and `prefers-reduced-motion` are still unaudited — a live browser session
  would let this go further than the code-level grep this session used.
- **Housekeeping**: don't run ad-hoc `php artisan tinker --execute=...`
  writes directly against `smart_africanmarket_test` outside a test
  transaction — did this once this session (checking `MaintenanceService`
  behavior) and it left a stray `Setting` row that made an unrelated test
  fail nondeterministically until traced back and cleaned up. Use a real
  test, or a throwaway DB session, instead.

---

## Recently completed (this session, most recent first)

0. **Booking-types admin UI + phantom `booking_type_id`** (2026-09-24) —
   built `admin/booking-types` index/create/edit (+ shared `_form`),
   linked Bookings and Booking Types from the Marketplace nav (neither
   was reachable before), and re-added the route to the admin smoke test.
   Building it found that `bookings.booking_type_id` was never migrated:
   `BookingType::bookings()` threw on every delete, and
   `BookingPricingService::calculateRefund()` looked up the type by that
   null column, so the admin-set cancellation notice was never applied.
   Relations now key on `bookings.booking_type` = `booking_types.slug`
   (the link that actually exists), and the slug is fixed after creation,
   since renaming would orphan bookings. Also fixed: edit form couldn't
   deactivate a type (no hidden fallback for the checkbox), blank sort
   order failed on a NOT NULL column, and slug collisions reached the DB.
   Removed 5 routes pointing at controller methods that never existed.
   Tests: `tests/Feature/Admin/BookingTypeManagementTest.php`.

1. **WCAG 2.4.7 (Focus Visible)** (`8336f35`) — `focus:outline-none` with
   no replacement focus style, found across every shared layout: the
   account/admin/vendor dropdown trigger in all three layouts (every page
   in the app), the password show/hide toggle (identical across all 6 auth
   views), the "Forgot password?" trigger, and the notifications search
   input. Fixed with this app's own existing `focus:ring-2` conventions.
2. **Maintenance mode** (`dabb4aa`) — `admin.maintenance.index` crashed
   outright (`config/maintenance.php` had no `title` key, though the
   controller/view both already read/wrote one — found by item 3's smoke
   test). Deeper bug: every maintenance setting was written through the
   `Config` facade (process-local) or a 5-minute `Cache::put()` with no
   durable fallback — a site placed into maintenance mode came back online
   on its own 5 minutes later, and a custom title/message set via the
   admin form or CLI was never actually shown to a visitor, because the
   enforcing middleware read `config('maintenance.*')` directly too. Fixed
   by making `Setting` (the app's existing DB-backed store) the single
   source of truth end to end. Also fixed `preview()`, which called the
   (now-durable) `enable()` with no restore path — a "preview" that
   permanently took the live site down for every visitor — and gave it a
   route (it had none).
3. **N+1 prevention + admin smoke test** (`12379ae`) — enabled
   `Model::preventLazyLoading(!isProduction())` (Roadmap 4.1). Running the
   existing suite under it found one real violation (`CheckoutTest`, fixed).
   Added `tests/Feature/Admin/AdminIndexPagesSmokeTest.php` hitting all 44
   admin index routes to extend that coverage to controllers with no
   dedicated test — found items 1 and 2 above, plus the missing booking-types views (item 0).
4. **Vendor plan-upgrade payment bypass** (`a86c9cb`) — the
   "Choose {plan}" button on `/vendor/subscription/upgrade` called no
   payment gateway at all; any vendor could self-upgrade to the $199
   Enterprise plan for free with one POST, despite
   `StripePaymentService::changePlan()` already existing and simply never
   being called. Fixed: a paid-plan switch now calls `changePlan()` when
   Stripe billing is on file, or redirects to real checkout otherwise.
   Also: `UserSubscription::isActive()` didn't check `current_period_end`
   (only `status`) — fixed to match the legacy model's own behavior.
5. **Subscription lifecycle was never running** (`f0256a9`) — three
   commands existed but were never scheduled, not in `routes/console.php`
   and not in the pre-3.3 `Kernel.php` it replaced (confirmed via `git
   show`, so a long-standing gap, not a migration regression):
   `subscriptions:check-trial-expiry`, `subscriptions:process-renewals`,
   `subscriptions:send-trial-reminders`. Trials never converted to billed
   subscriptions or expired; active subscriptions never renewed; grace-
   period cancellations never actually canceled anything. Scheduling
   `process-renewals` immediately surfaced a real bug in it too: its query
   didn't exclude `cancel_at_period_end`, so an explicit cancellation got
   silently auto-renewed instead. Fixed both.
6. **SEO infrastructure** (`dd9bbd0`) — `robots.txt` was a static file
   serving the literal unprocessed text `{{ url('/sitemap.xml') }}` to
   every crawler; a legacy `sitemap:generate` command permanently shadowed
   the real dynamic sitemap once it ran once; `SeoService`'s entire
   structured-data and per-page-title mechanism was dead code across six
   controllers (not bound as a singleton, nothing ever rendered it);
   product JSON-LD was hand-built via unescaped `{{ }}` interpolation
   (broke on any multi-line description). Full detail in the commit body.
7. **Rename** (`13a0f3b`) — "Smart African Marketplace" → "Smart African
   Market" across 67 tracked files, display text only. Deliberately left
   untouched: Android package id `com.africanmarket.app`, composer/npm
   package names, `africanmarket.com`-style domains/emails.
Full diffs: `git log --oneline` / `git show <hash>`.

---

## Recently completed (prior session — browse-testing sweep)

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
- **Persist admin-configurable settings through the `Setting` model**, not
  `Cache::put()` with a TTL or the `Config` facade — both are process-local
  or expire silently (see the maintenance-mode fix, item 2 above, for what
  that cost in practice).
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

Test suite baseline: **381 passed**, verified 2026-09-24 (up from 258 at the
last roadmap update). Re-verify with `php artisan test` before trusting this
number — don't cite it without running it.

---

## References

- [Roadmap](docs/product/roadmap.md) — current prioritised plan, Phase 4 open items
- [Documentation Audit](docs/product/documentation-audit.md) — evidence behind the roadmap
- [Architecture Overview](docs/architecture/overview.md)
- [Testing Guide](docs/guides/testing.md)
- [CONTRIBUTING.md](CONTRIBUTING.md) — workflow, code standards, review checklist
- [Documentation Hub](docs/README.md) — index of everything else
