# PROGRESS

Working memory for this repo. Keep this file current and lean — link out to
`docs/` for anything that isn't state or the active task. Rewrite sections in
place; don't append a history log here.

Last updated: 2026-09-25 (booking-type wiring session).

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
maturity, observability) remains open there — several sessions running now
have made real progress on 4.1 (performance) and 4.2 (accessibility), both
still open for more.

`docs/product/todo.md` is a **historical** backlog (superseded by the
roadmap) — don't pull tasks from it.

---

## Current session

**Idle — nothing in progress, tree clean, all work committed and tested.**
Resolved the one remaining candidate that needed the user's call rather
than a guess: the orphaned multi-type booking subsystem. Asked directly;
they chose to wire it into the live flow rather than delete it or leave it
flagged. See item 1 below — this was a real feature build, not a bug fix,
and surfaced a genuine DB-level blocker (two NOT NULL columns that made
resource bookings impossible to create at all) plus three more
phantom-column bugs on top of the one already fixed in an earlier session.
Test suite grew from 529 → 540.

Candidates for next time, not yet started:
- Accessibility: actual assistive-tech testing with a real screen reader.
  Everything this multi-session pass has covered (alt text, ARIA labels,
  contrast, keyboard-trap/focus-trap) has been static analysis and
  computed contrast ratios, not a live AT session — this is the one
  remaining gap, and it needs a human with a screen reader, not more
  automated passes.
- **Housekeeping**: don't run ad-hoc `php artisan tinker --execute=...`
  writes directly against `smart_africanmarket_test` outside a test
  transaction — did this once in an earlier session (checking
  `MaintenanceService` behavior) and it left a stray `Setting` row that
  made an unrelated test fail nondeterministically until traced back and
  cleaned up. Use a real test, or a throwaway DB session, instead.
- **Environment note**: Windows `VirtualAlloc`/paging-file exhaustion
  during `php artisan test` (PHPUnit crashing before or during a run, no
  test output, unrelated to the code) has hit multiple sessions running
  now — a recurring condition on this dev box under sustained work, not a
  one-off. Didn't recur this session (test runs were just slow under
  system load, not fatal), but check `Get-CimInstance
  Win32_OperatingSystem | select FreePhysicalMemory` if `php artisan
  test` dies this way; retry once memory is actually free rather than
  looping blindly.

---

## Recently completed (this session, most recent first)

1. **Wired the orphaned multi-type booking subsystem into the live flow**
   (`3c23de9`) — `BookingType` had a full admin CRUD UI and zero
   customer-facing entry point; investigating turned up a fully-built,
   fully-orphaned parallel booking system (`BookingService`,
   `BookingPricingService`, `BookingResourceService`, `/api/bookings/*`
   endpoints, an Alpine `<x-booking.booking-form>` component) with no
   live route reaching it. Asked the user directly rather than guess —
   they chose to wire it in. Fixed three more phantom-column bugs as
   prerequisites (`Booking::$fillable` claimed 7 columns —
   `booking_type_id`, `guest_count`, `recurring_interval`,
   `recurring_count`, `subtotal`, `tax_amount`, `discount_amount` — that
   don't exist anywhere in the real schema, confirmed via
   `Schema::getColumnListing()`; `Booking::bookingType()`'s relation and
   `BookingPricingService::calculateRefund()` had the same
   `booking_type_id` bug). Found a genuine DB-level blocker along the
   way: `salon_service_id` and `booking_end_date` were both `NOT NULL`,
   making a resource-type booking impossible to insert regardless of
   application code — added a migration relaxing both (applied to dev
   and test DBs, no backfill needed, every existing row already has real
   data). Built `BookingTypeController` (the new "start a booking"
   entry point — pick a type, see what's bookable) and
   `ResourceBookingController` (the actual rental-booking creation flow,
   the first thing to ever call `BookingResourceService`'s
   already-solid availability/pricing logic to write a booking rather
   than just query one). Only appointment/consultation (→ `SalonService`)
   and rental (→ `BookingResource`) have a real backing model;
   reservation/class/event describe entities that don't exist anywhere
   in this schema, so they get an honest "not available yet" page
   instead of a fake listing. Made the existing "My Bookings" pages and
   `BookingController::edit`/`update` resource-aware (a booking has
   either a `salonService` or a `resource`, never both; reschedule is
   guarded to salon-service bookings only, since it's a time-slot
   concept a date-range rental doesn't have). Removed the old scaffold
   outright once confirmed unsalvageable, not just unused — its `<form>`
   posted to a hardcoded, always-404 service id, so it could never have
   worked even with a link pointing at it. New coverage:
   `BookingTypesAndResourceBookingTest` (12 tests). Full suite: 540
   passed, up from 529.

Full diffs: `git log --oneline` / `git show <hash>`.

---

## Recently completed (prior session — create-route coverage)

1. **N+1 smoke testing extended to create routes; two broken routes fixed**
   (`7b809ba`) — completed the route-shape coverage begun in earlier
   sessions (index, then show/edit) with the last shape: create pages.
   Added `AdminCreatePagesSmokeTest` (28 routes) and
   `VendorCreatePagesSmokeTest` (15 routes) — the same pattern that's now
   found a real bug in every shape it's been pointed at. `Admin\
   AuctionController` never implemented `create()`/`store()`, despite
   `Route::resource('auctions', ...)` auto-registering both — hitting
   `admin.auctions.create` threw a fatal `BadMethodCallException`. Fixed
   with a redirect-and-explain, matching `admin.orders.create`'s existing
   pattern for the same shape of situation (auctions are vendor-created,
   not an admin action — not a missing feature, a missing "that's not how
   you do that" response). `vendor.media.create` was different: a real
   route with no controller method, no view, and nothing in the vendor UI
   even linking to it (vendor media management is entirely modal-based).
   Unlike the auctions case there's no reason vendors shouldn't have this,
   so built the missing `create()` and view, mirroring the working admin
   equivalent. Full suite: 529 passed, up from 486.

Full diffs: `git log --oneline` / `git show <hash>`.

---

## Recently completed (prior session — contrast + keyboard-trap)

1. **Keyboard-close and real focus-trapping for modals/dropdowns**
   (`169fef0`) — audited every Alpine `x-show="open"` and plain-JS modal/
   dropdown in the app for keyboard operability. Found three with no
   Escape-to-close path at all: `components/dropdown.blade.php` (the
   shared account-menu dropdown — had `@click.outside` but nothing for
   keyboard users), `livewire/live-search.blade.php` (search suggestions),
   and `admin/media/index.blade.php`'s upload/folder modals (plain
   `classList` toggling, not Alpine). Fixed all three, matching this app's
   own already-correct pattern in the auth modals.
   `vendor/media/index.blade.php`'s equivalent modals needed nothing — real
   Bootstrap 5 modals, which handle Escape and focus natively. Separately:
   no modal in the app trapped Tab focus — a keyboard user could Tab out of
   an open modal into the page behind it, since `x-show` only toggles
   `display:none` with no focus management of its own. Installed
   `@alpinejs/focus` (wired through the `livewire:init` event, since
   Livewire v3 owns Alpine's lifecycle here and starting Alpine twice
   breaks) and added `x-trap="open"` to the three genuine full-viewport
   modal dialogs only — not the dropdown/disclosure components above,
   which don't need Tab-trapping per the ARIA APG. Also added a missing
   `role="dialog" aria-modal="true"` to `register-modal.blade.php`, which
   lacked what its two siblings already had. `npm run build` verified
   clean; full suite unchanged (486 — no PHP touched).
2. **Focus-ring contrast failing WCAG 1.4.11 on light-theme pages**
   (`a22fe61`) — `focus:ring-orange-500`, the customer/auth theme's
   standard focus indicator (103 uses), only reaches 2.80:1 against white
   — computed via the actual relative-luminance formula, not eyeballed —
   below the 3:1 minimum for UI-state indicators. This directly undermined
   an earlier session's WCAG 2.4.7 fix: the ring is there, just too faint
   to reliably see on a light background. Bulk-swapped to
   `focus:ring-orange-600` (3.56:1) on the 61 occurrences confirmed to sit
   on a light/white background across 16 files; left the other 42 (7
   files, all dark-themed `bg-gray-700`/`800`) alone since orange-500
   already passes there (3.68-6.33:1) and a darker shade would only narrow
   that margin. The same script checked every colored badge combination
   already in use (`bg-X-900`/`text-X-2xx`, `bg-X-100`/`text-X-7xx`
   patterns) — all comfortably pass WCAG AA, no fix needed. Full suite
   unchanged (486 — pure CSS-class change).

Full diffs: `git log --oneline` / `git show <hash>`.

---

## Recently completed (prior session — salon-services fixes)

1. **N+1 smoke testing extended from index to show/edit routes**
   (`3e5a08c`, bug fixes in `1adb19e`) — added 65 tests across 7 new files
   (5 admin, 2 vendor), covering 42/43 admin and 23/24 vendor show/edit
   routes (skips explained above). Delegated to a subagent working in the
   background while this session continued other work; found and fixed 5
   real bugs along the way: a dead `admin.categories.show` route (no
   `show()`, no view — excluded from the resource route instead), a
   missing `AnnouncementController::show()` despite the view already
   existing and being linked from the index (added it; also fixed the
   view referencing stale `Announcement` model fields), a missing
   `admin/auctions/edit.blade.php` view for an otherwise-working route
   (built one from the vendor equivalent), a
   `RelationNotFoundException` from eager-loading `'winner.user'` in both
   `Admin\AuctionController::show()` and
   `Vendor\AuctionController::show()` (`Auction::winner()` is already
   `belongsTo(User)` — loading `.user` off of that tried to load
   `User::user()`, which doesn't exist; only surfaces when an auction has
   a real winner, so a nested eager-load of an always-null relation had
   silently no-op'd until then), and N+1 on `admin.products.edit` /
   `vendor.products.edit` (`$product->images`, and vendor's also
   `$product->tags`, rendered without being eager-loaded).
2. **Salon-service vendor ownership, delete guard, and stale bookability**
   (`2be5c50`) — `Admin\SalonServiceController::store()` forced
   `user_id = auth()->id()`: every salon service created through the
   admin panel was silently owned by the admin's own account, not any
   vendor, despite this controller's own index view rendering
   `$service->user->name` as the "Vendor" column. Fixed by requiring an
   explicit vendor selection (validated against approved vendors), matching
   how `Admin\SalonController` already does the same thing correctly for
   salons. Two smaller bugs in the same controller: `destroy()` had no
   guard against deleting a service with existing bookings (the vendor
   controller's does — an admin delete would throw a raw DB
   restrict-constraint exception instead of a clean error), and `index()`
   didn't eager-load `salonCategory` despite the view rendering
   `$service->salonCategory->name` on every row. Also fixed
   `BookingController::store()` (the live customer booking-creation
   endpoint), which had no `is_active` check at all, unlike
   `CartController`'s equivalent guard for products — a customer could
   book a service the vendor had deactivated. Removed
   `BookingType::services()` (zero callers anywhere, confirmed by tracing
   the entire orphaned multi-type booking subsystem end to end — see the
   candidate note above) and corrected the admin/booking-types UI copy
   that implied the feature already worked.

Full diffs: `git log --oneline` / `git show <hash>`.

---

## Recently completed (prior session — accessibility pass)

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

Test suite baseline: **540 passed** (1203 assertions), verified
2026-09-25 (up from 529 the day before — 12 new tests for the
booking-type/resource-booking feature). Re-verify with `php artisan test` before
trusting this number — don't cite it without running it.

---

## References

- [Roadmap](docs/product/roadmap.md) — current prioritised plan, Phase 4 open items
- [Documentation Audit](docs/product/documentation-audit.md) — evidence behind the roadmap
- [Architecture Overview](docs/architecture/overview.md)
- [Testing Guide](docs/guides/testing.md)
- [CONTRIBUTING.md](CONTRIBUTING.md) — workflow, code standards, review checklist
- [Documentation Hub](docs/README.md) — index of everything else
