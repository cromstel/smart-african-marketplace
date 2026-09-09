# Roadmap & Enhancement Plan

**Status: Current** — created 2026-09-08.

[Documentation Hub](../README.md) › Roadmap

A prioritised plan for hardening and extending the platform. Every item traces to
evidence in the [Documentation Audit](documentation-audit.md) or to a measured
property of the codebase — nothing here is aspirational filler.

---

## Guiding principle

The platform is **feature-rich and under-verified**. 98 models, 146 controllers
and 148 migrations are supported by 6 test files. The correct next move is not
more features — it is making the existing surface trustworthy, then building on
a foundation that can be changed safely.

Sequencing reflects that: correctness and proof first, then structural debt, then
new capability.

---

## Phase 0 — Close the integrity gaps (1 week)

> **Delivered and verified 2026-09-08.** All four items implemented, and the
> suite has now been executed: **56 passed, 190 assertions** on PHP 8.3 /
> MariaDB 10.11.
>
> Running it required first fixing six defects that stopped a clean clone from
> installing at all — a missing Sanctum migration (all API auth broken), a
> missing spatie/laravel-permission migration, a migration with a year typo, an
> unquoted `.env.example` value, a removed `APP_URL` default, and untracked
> `storage/framework` directories. See
> [Finding 10](documentation-audit.md#finding-10--the-application-could-not-be-installed-or-migrated-from-a-clean-clone).

Small, high-severity items. Nothing else should start until these land.

### 0.1 Resolve the CRLF protection claim — ~~**High**~~ **premise retracted**

> The finding behind this item was a misreading: the patch log was recording a
> *removed* Laravel 10.x patch, not claiming an active control. See
> [the retraction](documentation-audit.md#1b-a-security-patch-is-logged-that-was-never-applied--retracted).
>
> `PreventCrlfInjection` was reintroduced anyway, as defence in depth beyond the
> upstream CVE fix — but that reverses a deliberate decision and should be
> reviewed by someone who knows why it was removed.

`docs/security/patch-log.md` records a control that does not exist anywhere in
the codebase. Either:

- **(a)** implement header/email CRLF sanitisation and land the middleware the log
  names, or
- **(b)** retract the entry and record honestly that it was never applied.

Do not leave it as-is. A security log that overstates coverage is worse than no
log — it stops someone from doing the work.

*Definition of done:* the log entry and the codebase agree, with a test proving
whichever is true.

### 0.2 Throttle the mobile registration endpoint — **Medium** ✅ implemented

```php
// routes/api.php — currently unthrottled
Route::post('/auth/register', [MobileAuthController::class, 'register'])
    ->middleware('throttle:5,1')          // add
    ->name('auth.register');
```

Login already carries `throttle:10,1`. Registration is the more attractive target
— it creates rows.

*Definition of done:* throttle applied; a feature test asserts the 429.

### 0.3 Move `env()` calls into config — **Medium** ✅ implemented (partial)

Nine call sites break under `php artisan config:cache`, which the deployment
runbook mandates. Highest impact in `SeoService.php` (SEO description, keywords,
four social URLs).

Add a `config/seo.php`, read through `config()`, and keep `env()` to config files
only.

*Definition of done:* `grep -rn "env(" app/` returns nothing; a test asserts SEO
metadata survives `config:cache`.

**What shipped.** `config/seo.php` was added and `SeoService` now reads
`config('seo.*')` — the description, keywords and all four social URLs. That was
the consequential case, since those values are rendered on every page.

**Three call sites remain, deliberately.** `ConfigurationService` (lines 46, 121)
looks up `env()` by a key it computes at runtime, so there is no static config
file to point it at; converting it is a redesign of that service rather than a
find-and-replace. `VerifyReadinessCommand` (line 106) reads raw `env()` on
purpose — it is checking whether an operator has actually set a variable. Both
still behave incorrectly under `config:cache`; both need a considered fix rather
than a mechanical one, and neither silently corrupts page output the way
`SeoService` did.

### 0.4 Reconcile the PHP version — **Low** ✅ implemented

CI pins 8.3, `composer.json` requires `^8.2`. Pick the supported floor
deliberately and make the three sources agree.

**What shipped.** CI now runs a matrix of 8.2 and 8.3 — the floor
`composer.json` declares, and the version previously pinned. Testing only 8.3
left the declared floor unexercised; testing only 8.2 would not cover what is
deployed.

---

## Phase 1 — Make the payment path provable (2 weeks)

> **Complete.** All of Phase 1 is delivered and passing: fixtures, Stripe and
> Paystack signature/idempotency tests, mobile money coverage, and the closure
> matrix corrected. Mobile money turned out to be the most serious finding of
> the whole review — see 1.3.

The platform moves money across Stripe, Paystack and Swish. The signature checks
are implemented; the tests asserting they work are not.

### 1.1 Create the missing webhook fixtures ✅ done

`ReplayWebhookFixtureCommand` resolves
`base_path("tests/Fixtures/webhooks/{$name}.json")`. Create that directory and
the fixtures the documentation already promises:

```
tests/Fixtures/webhooks/stripe-charge-succeeded.json
tests/Fixtures/webhooks/paystack-charge-success.json
```

This alone makes `docs/operations/webhook-e2e.md` executable for the first time.

### 1.2 Write `tests/Feature/WebhookE2ETest.php` ✅ done

The test the closure matrix has been citing. It must assert:

- a valid Stripe signature is accepted, an invalid one rejected
- a valid Paystack HMAC is accepted, a tampered body rejected
- Swish signature verification behaves the same way
- **replaying an identical event does not create a second `Payment` row**

Idempotency is the assertion that matters most — it is the one that silently
costs money when it regresses.

### 1.3 Mobile money coverage — ✅ done (and found a hole)

> `tests/Feature/MobileMoneyWebhookTest.php` — 10 tests, passing. Writing them
> surfaced that the endpoint had **no authentication at all**, and that real
> M-Pesa STK callbacks were never matched. Both fixed.

`MobileMoneyWebhookTest` and `MobileMoneyCheckoutFlowTest`, also already cited.
M-Pesa, MTN and Airtel via Paystack are core to the African market.

### 1.4 Correct the closure matrix

Re-mark items honestly, then re-mark them Complete as each test lands. Add a rule
to `CONTRIBUTING.md`: **a document may not cite a file that does not exist**, and
enforce it with the audit script in CI.

*Definition of done:* every "Complete" row in the matrix points at a file that
exists and a test that passes in CI.

---

## Phase 2 — Establish a real test foundation (4–6 weeks)

Not "100% coverage" — coverage of what breaks expensively.

### 2.1 Split the test suites

`phpunit.xml` defines only `Feature`. Add a `Unit` suite so service-layer logic
can be tested without booting the framework.

### 2.2 Cover the money and trust paths, in this order

| Priority | Area | Why |
|---|---|---|
| 1 | **Vendor isolation** | Tenancy is enforced in app code, not the database. An unconstrained query is a cross-tenant data leak. |
| 2 | Checkout & order creation | Revenue path; touches inventory, pricing, shipping |
| ~~3~~ | ~~Payouts, ledger, withdrawals~~ — **started**: `Vendor/WithdrawalTest` (5 tests). Found and fixed a vendor-id/user-id mix-up in the balance calculation. Payouts and ledger still uncovered | Moves money *out*; errors are hard to reverse |
| 4 | Subscriptions & plan gating | `CheckSubscriptionLimits`, `EnsureModuleAccess` — entitlement bypass is revenue loss |
| 5 | Auctions & bidding | Concurrency-sensitive; race conditions decide outcomes |
| 6 | Bookings | Double-booking and slot integrity |
| 7 | Refunds & RMA | Money and customer trust |

### 2.3 Vendor isolation as a first-class test pattern — 🟡 pattern established

> `tests/Feature/Vendor/VendorIsolationTest.php` implements the pattern for the
> product routes (5 tests, passing). The remaining vendor controllers still need
> the same treatment — this establishes the shape, not the coverage.

Write a reusable assertion — *vendor A cannot read, update or delete vendor B's
resource* — and apply it across all 32 vendor controllers. This is the single
highest-value test investment in the codebase.

### 2.4 Add coverage reporting to CI

Not as a gate to start with. Make the number visible, then ratchet.

*Definition of done:* CI runs Unit + Feature; the seven areas above have tests;
coverage is reported on every PR.

---

## Phase 3 — Pay down structural debt (3–4 weeks)

### 3.1 De-duplicate authorization middleware — **security-relevant**

`AdminMiddleware` / `EnsureUserIsAdmin` and `VendorMiddleware` /
`EnsureUserIsVendor` are two implementations of one rule each. Two
implementations drift, and a drifted authorization check is a vulnerability.
Keep one of each, alias the other, migrate routes, then delete.

### 3.2 Introduce real queued jobs

`app/Jobs/` is empty; long work runs inside scheduled commands, so it cannot be
retried, backed off or scaled. Move the heavy operations — campaign sends, media
optimisation, chatbot reindexing, payout batches — into queued jobs with retry
policies.

### 3.3 Decide on the bootstrap structure

`bootstrap/app.php` uses the Laravel 10 skeleton on Laravel 12. It works, but
every current tutorial and upgrade guide assumes otherwise, and the next major
upgrade will be harder for it. Either migrate to the slim skeleton or record an
[ADR](../architecture/decisions/README.md) explaining the deliberate choice.

### 3.4 Documentation housekeeping

- [x] Consolidate `docs/`, `documentation/`, `plans/`, `commands/` and the root
      into one `docs/` tree grouped by audience
- [x] Rename `vunerabilities-to-fix/` → `docs/security/findings/`
- [x] Normalise filenames to `kebab-case.md` (spaces and parentheses in
      filenames break tooling)
- [x] Add the audit script to CI (`.github/workflows/docs.yml`)
- [ ] Delete `docs/archive/todo-2026-02-22.md` — that is what git is for

---

## Phase 4 — Platform enhancement (ongoing)

Only after Phases 0–2. Ordered by leverage.

### 4.1 Performance

- Audit N+1 queries across the 49 admin and 32 vendor controllers — the most
  likely source of slow pages at this model count
- Review index coverage against real query patterns (see
  [Database Optimization Runbook](../operations/database-optimization.md))
- Revisit the [Elasticsearch evaluation](../modules/elasticsearch-evaluation.md); MySQL
  `LIKE` search degrades badly as the catalogue grows
- Cache the storefront aggressively; it is the highest-traffic, least-personal
  surface

### 4.2 Accessibility and responsive quality

330 Blade views built over a long period. Run an audit pass against WCAG:
semantic markup, focus states, contrast, keyboard navigation, `prefers-reduced-motion`.
Extend [Local Performance & Accessibility Checks](../operations/performance-accessibility-audit-2026-03-03.md)
from a point-in-time snapshot into a repeatable CI check.

### 4.3 Frontend consistency

Decide the Livewire question explicitly (see
[Architecture Overview](../architecture/overview.md#frontend-architecture)) and
record it as an ADR. Extract a documented Blade component library so the next 100
views are consistent with the last 330.

### 4.4 API maturity

The API is versioned at `/api/v1` but thin — catalogue, cart, orders, addresses,
checkout, mobile auth. As the Android app grows, add pagination contracts,
consistent error envelopes, and generated OpenAPI documentation from
[API Reference](../reference/api.md).

### 4.5 Observability

`VerifyReadinessCommand` and `HealthController` exist. Extend into structured
logging, error tracking, and the dashboards described in
[Monitoring & Alerting](../operations/monitoring-and-alerting.md).

---

## Sequencing at a glance

```
Week  1        Phase 0   integrity gaps         ── blocks everything
Weeks 2–3      Phase 1   payment path proven    ── blocks Phase 4
Weeks 4–9      Phase 2   test foundation        ── blocks Phase 3
Weeks 10–13    Phase 3   structural debt
Ongoing        Phase 4   enhancement
```

Phases 0 and 1 are short and non-negotiable. Phase 2 is the expensive one and the
one that makes every later phase safe. Phase 3 changes security-relevant code, so
it deliberately follows the tests that would catch a mistake.

---

## What is deliberately *not* on this plan

- **A framework rewrite.** The Laravel monolith is a good fit for the
  self-hosting constraint. Nothing in the audit argues for changing it.
- **Broad refactoring for style.** The code hygiene measured clean. Refactoring
  ahead of tests would spend the risk budget in the wrong place.
- **New feature domains.** There are already thirteen. Depth over breadth until
  the existing surface is verified.

---

## See also

- [Documentation Audit](documentation-audit.md) — the evidence
- [Architecture Overview](../architecture/overview.md) — the debts in context
- [Testing Guide](../guides/testing.md) — how to write the Phase 2 tests
- [Implementation Plan](implementation-plan.md) — original product phasing
