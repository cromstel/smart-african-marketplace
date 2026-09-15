# Rate Limiting Audit — API and Web Endpoints

**Status: Current** — 2026-09-15.

[Documentation Hub](../README.md) › Security › Rate Limiting Audit

This is the discrete audit deliverable for the externally-tracked todo item
"Rate limiting audit across API and web endpoints." Earlier verification work
this session found real, working rate-limit coverage but no single document
tying it together (unlike file-upload security, which has both a documented
finding and `tests/Feature/Vendor/MediaUploadSecurityTest.php`) — this fills
that gap.

## Named limiters (`app/Providers/AppServiceProvider.php::configureRateLimiting()`)

| Limiter | Rule | Used by |
|---|---|---|
| `api` | 60/min, keyed by user ID or IP | Every route in the `api` middleware group (blanket, via `bootstrap/app.php`) |
| `public-forms` | 20/min, keyed by IP | Contact/newsletter forms; campaign tracking (open/click/unsubscribe) |
| `search` | 90/min | Search endpoints |
| `chatbot` | 10–600/min, configurable via `Setting` | Chatbot endpoints |
| `webhook` | 120/min, keyed by IP | Inbound payment-provider webhooks |

## Coverage by surface

**`routes/api.php`** — the entire `api` middleware group carries a blanket
`ThrottleRequests::class.':api'` (confirmed in `bootstrap/app.php`), so every
`/api/*` route is rate-limited by default even where no route adds its own.
Mobile auth endpoints tighten this further: login is `throttle:10,1`,
register is `throttle:5,1`. Public `v1` GET routes (products, categories,
brands, salon) rely on the blanket group throttle — appropriate for
read-only, non-abuse-prone endpoints.

**`routes/auth.php`** — registration, admin login, forgot-password,
reset-password, email-verification and confirm-password all carry explicit
`throttle:N,1` middleware. The plain `auth/login` POST route has no route-level
throttle middleware, but is not a gap: Laravel Breeze's
`Auth\LoginRequest::ensureIsNotRateLimited()` enforces its own limiter (5
attempts per email+IP combination, via `RateLimiter::tooManyAttempts()`) inside
the FormRequest itself, before the controller ever runs.

**`routes/web.php`** — named throttles are applied individually to search,
the chatbot, blog comments, and webhook-adjacent endpoints.
**Finding and fix (this audit):** the three campaign-tracking routes
(`campaigns.track.open`, `campaigns.track.click`, `campaigns.unsubscribe`) had
no throttle at all. They are public and token-guarded by design (hit from
email clients, not logged-in sessions), but each does a DB lookup and two of
the three also write — an unthrottled flood against even one known/guessed
token is a cheap DB-amplification vector. Fixed by adding
`throttle:public-forms` to all three (reusing the existing limiter built for
exactly this class of anonymous-public-endpoint abuse, rather than inventing a
new one). Covered by `tests/Feature/CampaignTrackingThrottleTest.php`, written
against the same N+1-requests-then-assert-429 pattern already established in
`tests/Feature/Api/RegistrationThrottleTest.php`.

## Verification status

`tests/Feature/CampaignTrackingThrottleTest.php` has been written but **not
yet executed**: this environment's local MariaDB instance is currently
rejecting all connections (`SQLSTATE[HY000] [1045] Access denied for user
'user_africanmarket'@'localhost'`), the same recurring grant-table outage
documented in `claude/engineering-conventions.md` (project memory) that
requires admin access to this machine to resolve. The fix itself
(`routes/web.php`) is a one-line-per-route middleware addition with no logic
change, so the risk of it being wrong is low, but it should be treated as
**pending first run**, not complete, until the suite executes green.

## Conclusion

Rate limiting across both API and web endpoints is broad, layered (group
blanket + per-route overrides + FormRequest-level throttling for Breeze
auth), and was missing in exactly one place, now fixed. No further gaps were
found in this pass.
