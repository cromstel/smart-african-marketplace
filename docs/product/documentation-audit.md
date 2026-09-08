# Documentation Audit — 2026-09-08

**Status: Current**

[Documentation Hub](../README.md) › Documentation Audit

Findings from a systematic comparison of every markdown file in this repository
against the code it describes. Method: automated link checking, automated
verification of every file path cited in backticks, and manual reading of the
core documents against the source tree.

**Scope:** 63 markdown files, 14,075 lines, across `/`, `docs/`, `documentation/`,
`plans/` and `.github/`.

---

## Headline

> The codebase is in materially better shape than its documentation claimed, but
> several documents assert verification that was never performed.

Code hygiene measured clean: no `dd()`/`dump()`/`var_dump()` left in
`app/` or `resources/views/`, no `TODO`/`FIXME`/`HACK` markers, no models with
`$guarded = []`, and no string-interpolated `DB::raw()`/`whereRaw()`. That is
better than most codebases of this size.

The problem is documentation drift, and in three cases documentation that cites
evidence which does not exist.

---

## Finding 1 — Documents cite files that were never written

**Severity: High.** These are not stale references to deleted files. `git log --all`
shows *no history* for any of them: they were never committed.

### 1a. Webhook verification evidence is fictional

`docs/security/pending-closure-matrix.md` marks five payment-security items **"Complete"**
with `tests/Feature/WebhookE2ETest.php` as evidence. That file does not exist.

`docs/operations/webhook-e2e.md` documents a verification procedure depending on five
missing files:

| Cited file | Exists |
|---|---|
| `tests/Feature/WebhookE2ETest.php` | No |
| `tests/Feature/MobileMoneyWebhookTest.php` | No |
| `tests/Feature/MobileMoneyCheckoutFlowTest.php` | No |
| `tests/Fixtures/webhooks/stripe-charge-succeeded.json` | No |
| `tests/Fixtures/webhooks/paystack-charge-success.json` | No |

The tooling around them *is* real — `ReplayWebhookFixtureCommand.php` and
`scripts/webhooks/replay-fixtures.ps1` both exist. But the command resolves
fixtures at `base_path("tests/Fixtures/webhooks/{$name}.json")`, and that
directory is absent. **The documented verification procedure cannot be executed
as written.**

**Important distinction:** the underlying security controls are genuinely
implemented. `WebhookController.php` verifies Stripe signatures via
`\Stripe\Webhook::constructEvent()` and Paystack via `hash_equals()`;
`SwishPaymentController.php` does the same. The controls are real; the *proof*
is missing. The risk is that a reader trusts the matrix and skips a real check.

### 1b. A security patch is logged that was never applied — **RETRACTED**

> **This finding was wrong.** See the correction at the top of
> [the patch log](../security/patch-log.md).
>
> The two filenames sit under a heading reading *Manual Patches (Removed After
> Upgrade)*, struck through, in a section stating they were written for Laravel
> 10.x and removed once CVE-2026-48019 was fixed upstream in Laravel 12.60.0+.
> The log was recording a removal. It was never claiming the files were present.
>
> The automated check flagged the filenames; the write-up below treated that
> flag as the finding without reading the surrounding section. That is a
> reviewer error, and it is the reason the original text is struck through
> rather than deleted — an audit that quietly edits away its own mistakes is
> not worth reading.
>
> What survives: `git log --all` genuinely shows no history for either file in
> this repository, and there is genuinely no CRLF handling in `app/` or
> `config/`. Both are consistent with a deliberate removal plus a squashed
> import, and neither supports the original claim.

*Original finding, retained for the record:*


`docs/security/patch-log.md` records CRLF-injection protection via:

- `app/Http/Middleware/PreventCrlfInjection.php` — does not exist
- `app/Providers/CrlfEmailValidationServiceProvider.php` — does not exist

A repository-wide search for `Crlf` or `CRLF` in `app/` and `config/` returns
nothing. Unlike 1a, here **there is no control at all** — only a log entry saying
there is. This is the most serious finding in the audit.

### 1c. Plans cite unbuilt files (expected, but unmarked)

`docs/plans/configuration-management.md` references nine files that do not
exist. For a *plan* that is correct and expected — but nothing on the document
says it is unimplemented, so a reader can mistake intent for description. Fixed
by adding status banners.

`docs/guides/setup-guide.md` cites four tutorial files (`app/Livewire/ProductList.php` and
friends) that are illustrative examples, not repository files. Needs clarifying.

---

## Finding 2 — The documented architecture is not the real architecture

`README.md` and `docs/architecture/tech-stack.md` present **Livewire 3** as the UI layer:
*"real-time reactive components (no JavaScript frameworks needed)"*.

| Claim | Measured |
|---|---|
| Livewire is the frontend | 2 components in `app/Livewire/` |
| Views are Livewire-driven | 2 of ~330 Blade views reference Livewire |

The real frontend is server-rendered Blade + Tailwind + Alpine.js, with Livewire
used for live search only. A developer onboarding from these documents would
start in the wrong paradigm.

Corrected in [Architecture Overview](../architecture/overview.md).

---

## Finding 3 — "No external services" is no longer true

`docs/architecture/tech-stack.md` claimed *"No SendGrid/Twilio required"* and *"No Redis required —
everything self-hosted except payment gateways."*

Actually integrated: **Twilio** (`twilio/sdk` in `composer.json`), **Soketi**
websockets (`soketi/` directory, `laravel-echo`, `pusher-js`), **Firebase Cloud
Messaging** (`FcmPushService.php`), and **Laravel Socialite** for social login.

The self-hosting *goal* is real and worth keeping. The absolute claim is not.

---

## Finding 4 — The README roadmap contradicts the git history

The README's "Phase 1: MVP (Current)" listed authentication, product catalogue,
cart/checkout, payments, order management, admin panel and the installer as
**unchecked**. The repository contains 98 models, 146 controllers, 148 migrations
and 330 views, and recent commits ship a native Android app and a checkout API.

Phases 1 and 2 are substantially delivered. The README understated the project by
roughly two years of work.

---

## Finding 5 — Whole domains are undocumented

Present in code, absent from the README: auctions, bookings and resource
scheduling, subscriptions and plan gating, blog/CMS with revisions, an
AI chatbot with a retrieval knowledge base (`KnowledgeDocument`,
`KnowledgeChunk`), marketing campaigns and attribution, vendor payouts/ledger/
withdrawals, RMA and refund requests, Swish payments, the media library, SEO
metadata management, GDPR data-export requests, and multi-language support.

---

## Finding 6 — Test coverage is the largest engineering gap

| Metric | Count |
|---|---|
| Test files | 6 |
| Controllers | 146 |
| Models | 98 |
| Migrations | 148 |

`phpunit.xml` defines only a `Feature` suite — there is no `Unit` suite, though
the README documents `php artisan test --testsuite=Feature` as if there were a
choice. Payments, subscriptions, auctions, bookings, payouts and vendor isolation
have no automated tests at all.

Given that vendor isolation is enforced in application code rather than by the
database, an untested ownership constraint is a potential cross-tenant data leak.

---

## Finding 7 — `env()` outside `config/` breaks under `config:cache`

Nine call sites in `app/`. The consequential ones are in `SeoService.php`:

```php
return $this->meta['description'] ?? env('SEO_DESCRIPTION', '…');
env('FACEBOOK_URL'), env('TWITTER_URL'), env('INSTAGRAM_URL'), env('YOUTUBE_URL')
```

`docs/operations/deployment-runbook.md` and the README both instruct running
`php artisan config:cache` in production. After that command, `env()` returns
null outside cached config. **Following the documented deployment procedure
silently degrades SEO metadata and social links in production.** Also affects
`ConfigurationService.php` and `VerifyReadinessCommand.php`.

---

## Finding 8 — An unthrottled public registration endpoint

In `routes/api.php`:

```php
Route::post('/auth/login',    …)->middleware('throttle:10,1');   // throttled
Route::post('/auth/register', …);                                 // not throttled
```

Login is rate-limited; registration is not. The web surface throttles its public
forms via `throttle:public-forms`. The mobile registration endpoint is an open
account-creation vector.

---

## Finding 9 — Structural documentation problems

> **Resolved.** The layout described below was reorganised into a single `docs/`
> tree grouped by audience. Paths in this section describe the state *at the time
> of the audit* and are kept as the record of what was found.

**The docs were islands.** Only 69 relative links existed across 63 documents —
most were reachable from nothing. Fixed by [the Documentation Hub](../README.md).

**Four competing locations** with no stated rule: root (8 files), `docs/` (60),
`documentation/` (23, plus a `v2/` subtree), `plans/` (3).

**One broken link:** `docs/plans/booking-system-summary.md` →
`docs/plans/booking-system.md` (wrong relative depth).

**Housekeeping:** a committed backup file
(`docs/archive/todo-2026-02-22.md`, 1,083 lines) duplicating `docs/product/todo.md`;
a misspelled `vunerabilities-to-fix/` directory containing only a screenshot;
inconsistent filename conventions (`SCREAMING_SNAKE.md`, `kebab-case.md`,
`Title Case With Spaces.md`).

**Version drift:** README said PHP 8.1/8.2, `composer.json` requires `^8.2`,
CI pins 8.3.

---

## Finding 10 — The application could not be installed or migrated from a clean clone

**Severity: Critical.** Added 2026-09-08, after actually running the project
rather than only reading it.

Every issue below was invisible to static review and to the documentation
checks. They surfaced within minutes of a real `composer install` → `migrate` →
`test` on a clean checkout, and each one alone stops a new developer, a CI run,
and the web installer.

| # | Defect | Effect |
|---|---|---|
| 10a | `config/app.php` had `'url' => env('APP_URL')` with no default (Laravel ships `'http://localhost'`) | `composer install` fails at `package:discover` on a fresh clone — null passed to `Request::create()` |
| 10b | `.env.example` line 333: `MAIL_SENDMAIL_PATH=/usr/sbin/sendmail -bs -i`, unquoted | Copying the example file makes the app unbootable: "Failed to parse dotenv file" |
| 10c | `storage/framework/{cache/data,sessions,views,testing}` not tracked in git | `php artisan` fails with "Please provide a valid cache path" |
| 10d | A migration dated `2025_03_20_210000` among an otherwise 2026 set — a year typo | Sorted before `create_users_table`; altered `vendors` before it existed, so `migrate` failed immediately |
| 10e | `laravel/sanctum` migration never published — no `personal_access_tokens` table | **All API token authentication broken.** Mobile login and registration returned 500 |
| 10f | `spatie/laravel-permission` migration never published — no `permissions`, `model_has_permissions`, `model_has_roles` | `migrate` failed on a foreign key to a table that is never created |

### What this implies about CI

`.github/workflows/ci.yml` runs `php artisan migrate --force` followed by
`php artisan test`. Given 10d and 10f, that migrate step **cannot have
succeeded** on a clean database. Either the workflow has been failing, or it has
not run. The green-looking "0 vulnerabilities" security badge in the old README
pointed the same way, which is why it was removed.

Two further CI gaps, both found the same way:

- No `npm ci` / `npm run build` step. Blade views use `@vite`, so every
  view-rendering test failed with "Vite manifest not found".
- `ext-bcmath` was absent from the extension list, though Cashier requires it
  transitively.

### After the fixes

`php artisan migrate` runs all 150 migrations clean (117 tables), and
`php artisan test` reports **56 passed, 190 assertions** on PHP 8.3 /
MariaDB 10.11.

### The lesson

The first nine findings came from reading the repository against itself and were
worth having. None of them found a defect that stopped the application from
starting. **Running the thing found six in under an hour.** Static checks and
documentation audits are complements to execution, not substitutes for it.

---

## Disposition

| # | Finding | Severity | Action |
|---|---|---|---|
| 1b | ~~CRLF protection logged but absent~~ | **Retracted** | Finding was a misreading — see above |
| 1a | Webhook test evidence fictional | **High** | Write the tests and fixtures |
| 6 | Test coverage | **High** | Phased test programme |
| 8 | Unthrottled registration | **Medium** | Add throttle middleware |
| 7 | `env()` under `config:cache` | **Medium** | Move to `config/` |
| 2 | Livewire misdescribed | Medium | Corrected in docs |
| 3 | External-services claim | Medium | Corrected in docs |
| 4 | Roadmap stale | Medium | Corrected in docs |
| 5 | Undocumented domains | Medium | Corrected in docs |
| 10 | Fresh-install and migration defects | **Critical** | All six fixed; suite now runs |
| 9 | Structure and links | Low | Hub added; cleanup scheduled |
| 1c | Unmarked plans | Low | Status banners |

Scheduling is in [ROADMAP.md](roadmap.md).

---

## Reproducing this audit

Both checks are dependency-free and worth running in CI:

```bash
# Broken relative links across all markdown
python3 - <<'PY'
import os,re,urllib.parse
skip={'.git','node_modules','vendor','storage'}
bad=[]
for dp,dn,fn in os.walk('.'):
    dn[:]=[d for d in dn if d not in skip]
    for f in (x for x in fn if x.endswith('.md')):
        p=os.path.join(dp,f)
        for m in re.finditer(r'\[([^\]]*)\]\(([^)\s]+)\)',open(p,encoding='utf-8',errors='ignore').read()):
            t=m.group(2)
            if t.startswith(('http','#','mailto:')): continue
            t=urllib.parse.unquote(t.split('#')[0])
            if t and not os.path.exists(os.path.normpath(os.path.join(dp,t))):
                bad.append((p,m.group(2)))
print(f"{len(bad)} broken links")
[print(f"  {p} -> {t}") for p,t in sorted(bad)]
PY
```

```bash
# Docs citing repository files that do not exist
python3 - <<'PY'
import os,re
skip={'.git','node_modules','vendor','storage'}
pat=re.compile(r'`([A-Za-z0-9_./\-]+\.(?:php|md|yml|yaml|json|ps1|sh|js|css))`')
for dp,dn,fn in os.walk('.'):
    dn[:]=[d for d in dn if d not in skip]
    for f in (x for x in fn if x.endswith('.md')):
        p=os.path.join(dp,f)
        miss={m for m in pat.findall(open(p,encoding='utf-8',errors='ignore').read())
              if '/' in m and not os.path.exists(m)}
        if miss: print(p); [print(f"    MISSING: {m}") for m in sorted(miss)]
PY
```
