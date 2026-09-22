# PROGRESS

Working memory for this repo. Keep this file current and lean — link out to
`docs/` for anything that isn't state or the active task. Rewrite sections in
place; don't append a history log here.

Last updated: 2026-09-22 (accessibility pass session).

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
maturity, observability) remains open there — two sessions running now have
made real progress on 4.1 (performance) and 4.2 (accessibility), both still
open for more.

`docs/product/todo.md` is a **historical** backlog (superseded by the
roadmap) — don't pull tasks from it.

---

## Current session

**Idle — nothing in progress, tree clean, all work committed and tested.**
Worked through every candidate left open at the end of the prior session
(booking-types admin UI → N+1 audit extension → changePlan() proration
investigation → legacy VendorSubscription audit → browse-testing sweep →
accessibility pass), testing and committing after each item. Test suite
grew from 369 → 415 over the session.

Candidates for next time, not yet started:
- `BookingType::services()` is still broken (`salon_services` has no
  `booking_type_id`, same root cause the `bookings()` relation had before
  this session's fix) — left broken and undocumented in code on purpose,
  since fixing it means designing how services get assigned to booking
  types in their own forms, beyond this session's UI-completion scope. See
  the `9a65b82` commit body.
- N+1 smoke testing (`AdminIndexPagesSmokeTest` +
  `VendorIndexPagesSmokeTest`) only covers *index* routes — 44 admin + 31
  vendor. Show/create/edit pages across those same 75 controllers are
  still untested for N+1, and `Model::preventLazyLoading()` only catches
  violations on paths something actually exercises.
- Accessibility code-level audit is now complete for this pass (alt text,
  ARIA labels on icon-only controls, `text-gray-*` contrast on the
  dark admin/vendor theme, `prefers-reduced-motion`) — see item 1 below.
  Not done: actual assistive-tech testing (a real screen reader, not
  static analysis), keyboard-trap testing in modals/dropdowns, and
  contrast on non-gray combinations (colored badges, borders, focus
  rings) — the regex-based audit this pass used only targeted the
  `text-gray-500` pattern because it was the one confirmed at scale.
- **Housekeeping**: don't run ad-hoc `php artisan tinker --execute=...`
  writes directly against `smart_africanmarket_test` outside a test
  transaction — did this once in a prior session (checking
  `MaintenanceService` behavior) and it left a stray `Setting` row that
  made an unrelated test fail nondeterministically until traced back and
  cleaned up. Use a real test, or a throwaway DB session, instead.
- **Environment note**: this session hit repeated `php artisan test`
  failures from Windows `VirtualAlloc`/paging-file exhaustion (PHPUnit
  crashing before or during a run, unrelated to the code) — not a code
  issue, but worth knowing the local dev box can run low on committed
  memory under sustained work. Check `Get-CimInstance
  Win32_OperatingSystem | select FreePhysicalMemory` if `php artisan
  test` dies with a `VirtualAlloc` / out-of-memory error with no test
  output.

---

## Recently completed (this session, most recent first)

1. **Accessibility pass: ARIA labels, reduced motion, contrast** (`293f989`)
   — Added contextual `aria-label`s to icon-only buttons/links across
   admin and vendor views (edit/delete/close/remove/dismiss/copy actions);
   a regex-based audit script (matches `<button>`/`<a>` pairs, strips
   `<svg>` blocks and Blade comments, checks for `aria-label`/`sr-only`/
   `title`) went from 36 genuine findings to 1 confirmed false positive
   (an Alpine `x-text` search suggestion that renders visible text only at
   runtime). Added a global `prefers-reduced-motion` media query to
   `app.css` (WCAG 2.3.3). Fixed a widespread WCAG AA contrast failure:
   `text-gray-500` on the dark admin/vendor theme's `bg-gray-800`/`900`
   computes to ~3.5:1, below the 4.5:1 minimum — bulk-swapped to
   `text-gray-400` (~4.6:1) across ~90 views, after confirming via a
   `bg-white` sweep that none of them mix in a light-background section.
   The one file that genuinely does mix backgrounds
   (`admin/chatbot/sessions/show.blade.php`'s three-role chat bubbles) got
   per-background colors instead of the blind swap; the two light-themed
   `vendor/announcements/*` views were left untouched (their existing
   `text-gray-500` already passes on white). Full suite: 415 passed
   (unchanged — style/attribute-only change, no new tests needed).
2. **Auction settlement grace period checking the wrong timestamp**
   (`0e2efc9`) — `ProcessAuctions::settleEndedAuctions()` compared
   `created_at` (when the auction was *listed*) against a 5-minute window,
   not when it *ended*. `auctions` has no `ended_at` column, but
   `AuctionService::end()`'s `update(['status' => 'ended'])` bumps
   `updated_at` to exactly that moment — that's the real signal. Fixed to
   check `updated_at`. The bug cut both ways: normal auctions settled
   immediately on the first run after ending (the grace period was a
   no-op), while short-lived auctions created and ended within the same
   5-minute window had settlement delayed for an unrelated reason.
3. **Duplicate expiring-subscription notifications for upgraded vendors**
   (`947ec91`) — any vendor who ever upgraded has both a native
   `UserSubscription` and a synced legacy `VendorSubscription` row (kept in
   sync so `EffectiveSubscriptionResolver`'s bridge stays populated).
   `subscriptions:check-expiring` queried both tables with no
   de-duplication, so those vendors got the same
   `SubscriptionExpiringNotification` twice. Fixed by skipping the legacy
   loop for any vendor whose user already has a native subscription.
4. **Removed dead `SubscriptionService::upgradePlan`/`downgradePlan`/
   `changePlan`** (`c3cf562`) — zero callers anywhere in the app, and no
   proration logic. The proration gap this was meant to close doesn't
   exist on any reachable path: the real, live upgrade flow already calls
   `StripePaymentService::changePlan()`, which does real Stripe proration.
   Removed the trap rather than "completing" logic nothing uses.
5. **N+1 smoke testing extended to vendor controllers; missing followers
   view fixed** (`a9a867c`) — `VendorIndexPagesSmokeTest` added, covering
   all 31 vendor index routes (mirroring the admin coverage from the prior
   session). Found `vendor.followers.index` had a correct nav link and
   controller but no view at all anywhere in
   `resources/views/vendor/followers/` — added it, matching the
   `vendor/reviews/index.blade.php` pattern.
6. **Built the `admin/booking-types/*` admin UI; fixed its broken
   `bookings()` relation** (`9a65b82`) — `BookingTypeController` was a
   complete real backend with zero admin views and no nav link. Built
   index/create/edit views plus a `reorder()` action; discovered along the
   way that the entire Salon Services + Bookings + Auctions admin area had
   no sidebar navigation at all, not just booking types — added a "Salon &
   Bookings" submenu and a standalone Auctions submenu. Fixed
   `BookingType::bookings()`, which matched on a `booking_type_id` column
   that was never migrated onto `bookings`; the live booking flow actually
   populates a `booking_type` enum column matching `BookingType::name` by
   string, which the relation now uses instead.

Full diffs: `git log --oneline` / `git show <hash>`.

---

## Recently completed (prior session — Phase 4 audit)

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
   dedicated test — found items 1 and 2 above, plus item 8.
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
8. **Known gap at the time, fixed next session**: `admin.booking-types.index`
   rendered a view that didn't exist anywhere in `resources/views/admin/` —
   excluded from the smoke test with an explanation rather than built,
   since it was feature work, not a bug fix. Built in the following
   session, see item 6 in "Recently completed (this session)" above
   (`9a65b82`).

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

Test suite baseline: **415 passed** (1044 assertions), verified 2026-09-22
(up from 369 earlier the same day). Re-verify with `php artisan test`
before trusting this number — don't cite it without running it.

---

## References

- [Roadmap](docs/product/roadmap.md) — current prioritised plan, Phase 4 open items
- [Documentation Audit](docs/product/documentation-audit.md) — evidence behind the roadmap
- [Architecture Overview](docs/architecture/overview.md)
- [Testing Guide](docs/guides/testing.md)
- [CONTRIBUTING.md](CONTRIBUTING.md) — workflow, code standards, review checklist
- [Documentation Hub](docs/README.md) — index of everything else
