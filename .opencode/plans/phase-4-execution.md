# Phase 4 Execution Plan — smart-african-marketplace

**Prepared:** 2026-11-06  
**Status:** HOLD — awaiting credit recovery  
**For:** Next AI agent executing Phase 4 items independently.  
**Baseline before work begins:** Full test suite green (540 tests / 1203 assertions / exit 0), `npm run build` exit 0, PHP 8.3/Laravel 12.

---

## How to use this plan

This is a step-by-step execution contract. Each item below is self-contained
with exact paths, commands, acceptance criteria and dependencies listed.
A subagent should pick an item where it has availability; otherwise work in
sequence. Do NOT split one item across two sessions — a blocked item blocks
the whole pipeline.

**Recurring constraint:** subagent launches hit `Rate limit exceeded: free-models-per-day`.
Work around it by finishing each item in one pass (one launch per item). If a
subagent fails twice on the same item, retry after waiting ≥ 1 hour for credits.
Do NOT fan out 4 independent subagents at once — they will all fail.

---

## Item 4.4 — OpenAPI specification (highest leverage, lowest blast radius)

OpenAPI is missing entirely. The API surface is small and stable enough to
generate a spec from code rather than maintaining by hand, which is the item
that explicitly drives Phase 4.4. There is no infrastructure cost.

### Pre-work
1. Confirm the version of the response envelope the trait produces:
   - `app/Http/Controllers/Api/Concerns/ApiResponse.php` defines:
     - `successResponse($data, $message = 'Request successful', $status = 200)`
       → envelope `{success: true, data: <payload>, message: <string>}`,
       optionally `meta` (pagination links) when paginating, and when paginating
       a bare paginator or nested resource collection the payload itself is unwrapped.
     - `errorResponse($message, $status = 400, $errors = null)`
       → envelope `{success: false, message: <string>, errors: [<field>, ...]}`
   - Public GET endpoints return paginated collections of `Product`, `Category`,
     and `Brand` models — infer types from `app/Models/*.php` and the migrations.
2. Enumerate routes (one command):
   ```
   php artisan route:list --path=api | Select-String -Pattern 'GET|POST|PATCH|DELETE|PUT'
   ```
   Save output to a scratch file. Each row: method + URI + controller class +
   middleware list. Cross-reference with the prior enumeration done during this
   session: public GET `/products`, `/categories`; brand GET `/brands`,
   `/{slug}`; brand product list GET `/{slug}/products`; salon GET `/salon`;
   cart CRUD (`/cart`); orders full CRUD; addresses full CRUD;
   `checkout/shipping-preview`, `checkout`; mobile auth login/register/me/profile/logout;
   Sanctum `auth/me`, `auth/profile`, `auth/logout`.
3. Read `docs/reference/api.md` top section — it already documents every
   endpoint; rewrite as reference for the spec location only.

### Work
1. Create `docs/reference/openapi.yaml` (OpenAPI 3.0.3, `openapi: 3.0.3`
   header). Do NOT copy from `api.md` wholesale — derive schemas from model
   contracts (migrations + model mutators/accessors). Required components:
   - `schemas`: `Product`, `Category`, `Brand`, `PaginatedProducts`,
     `PaginatedCategories`, `PaginatedBrands`, `Order`, `Address`, `Cart`,
     `CartItem`, `Payment`, `MobileAuthToken`, `Error` (fields mirror
     `errorResponse`), `MobileAuthLoginRequest`, `MobileAuthRegisterRequest`,
     `MobileProfileUpdateRequest`, `CheckoutShippingPreviewRequest`,
     `CheckoutCreateRequest`, `BrandProductListQuery`, `CartItemActionRequest`
     (DELETE body), plus open-ended `x-type` extensions where the actual type
     comes from code rather than the envelope.
   - `paths`: every route enumerated in pre-work step 2, each with one path
     item; request bodies inferred from controller signatures; responses typed
     by the envelope shape above. For GET-with-query-params, use `parameters`
     not separate path segments (e.g. `/brands` with `slug` query param for the
     single-brand lookup — match reality from `BrandController::show`).
   - `components/securitySchemes`: `bearerAuth` (OAuth 2.0 Bearer via Sanctum)
     for `*auth/*`, `mobile/*`, `module/*` groups; empty security on public
     routes.
   - Every parameter name and status code must match what the implementation
     actually returns, verified against one real call per endpoint during the
     change window.
2. Validate the YAML is valid YAML (no PHP dependency needed — pure config):
   ```
   php -r "echo json_encode(yaml_parse_file('docs/reference/openapi.yaml')) !== false ? 'valid' : 'invalid';"
   ```
   (If `pcntl` unavailable, use Python: `python -c "import yaml; yaml.safe_load(...)"`.)
3. Add a CI check in `.github/workflows/docs.yml` or the docs pipeline: run
   `pyyaml`/`yaml` linter over `docs/reference/openapi.yaml` and fail if
   schema violations are detectable. Non-blocking until vetted.
4. Record the item completion in `docs/product/roadmap.md` Phase 4 section:
   add a line under 4.4 noting "🟢 OpenAPI spec generated from codebase
   endpoints" and attach a link to `docs/reference/openapi.yaml`. Remove
   the trailing "Generated OpenAPI documentation has not been started" line
   — it was superseded by this deliverable.

### Acceptance criteria
- `docs/reference/openapi.yaml` exists and is valid OpenAPI 3.0.3.
- One `openapi: 3.0.3` schema entry per domain entity used by any api route.
- Every path in `routes/api.php` appears as a `paths:` key.
- Tooling parse succeeds; CI-path check passes once wiring exists.

### Dependencies
None — first deliverable in the sequence.

---

## Item 4.5 — Structured logging (JSON + trace IDs)

Infrastructure behind everything else in Phase 4 (observability, error tracking,
dashboards). No business code changes required — config only, low blast radius,
keeps us honest that warnings/errors surface now instead of later.

### Pre-work
1. Read `config/logging.php` (prior session confirms 9 channels exist);
   note current drivers: stack, single, daily, slack, papertrail
   (`monolog` + `SyslogUdpHandler`), stderr (`monolog`), syslog, errorlog,
   null, emergency.
2. Identify existing structured fields: are any fields already extracted into
   log entries today? Search for any existing logging helpers or tags attached
   to log calls. If none, build an extension point (`Logger::log()` + tag
   convention) rather than retrofitting every call site — 10 calls sites → 1
   helper call pattern is safer than touching 40 call sites.
3. Identify current trace ID mechanism (Redis queue? plain correlation id?) —
   confirm trace ids flow from HTTP request start to end so a parent process
   can correlate child logs. Search `bootstrap/app.php`,
   `Illuminate/Foundation/Http/Kernel.php`, middleware stack.

### Work
1. Add a new channel named `json` (or `structured`) to `config/logging.php`:
   ```php
   'json' => [
       'driver' => 'single',
       'path' => storage_path('logs/structured.log'),
       'level' => env('LOG_LEVEL', 'debug'),
       'format' => Monolog\Formatter\JsonFormatter::class,
       'processors' => [Monolog\Processor\ProcessorInterface::class],  // if custom
       'handler' => [new Monolog\Handler\StreamHandler(storage_path('logs/structured.log'), env('LOG_LEVEL', 'debug')), 'append'],
   ],
   ```
   Use `StreamHandler` with `JsonFormatter` plus a processor that stamps
   `requestId` (trace), `userId` (when available from request context), and
   `context` keys derived from the standard Laravel logger arguments. Do not
   attempt zero-padding `datetime` here — Laravel's default
   `JsonFormatter` already serializes the context object intact.
2. Add a global middleware that assigns `traceId` per request: read current
   trace mechanism (likely absent — verify). If absent, add a thin
   `TraceMiddleware` injected onto the `web` and `api` groups in
   `bootstrap/app.php` (or wherever middleware is declared for those groups),
   using a `trace:start` context key — keep generator trivial so this does not
   become a unit of work:
   ```php
   // app/Http/Middleware/TraceMiddleware.php
   public function handle($request, Closure $next) {
       $traceId = bin2hex(random_bytes(8));
       \Log::share(['requestId' => $traceId]);
       $request->attributes->set('traceId', $traceId);
       return $next($request);
   }
   ```
3. Wire structured logging into error handling: `AppServiceProvider::boot()`
   or `ExceptionHandler::respondUsing()` should call `Log::json()` /
   `Log::channel('json')->error(...)` on every HTTP exception before responding,
   including exceptions where the Handler currently returns silently. Verify
   existing Handler behavior (it rewrites messages — check it also logs).
4. Add a test verifying structured format on an error response: hit an endpoint
   that throws, assert `storage_path('logs/structured.log')` contains JSON
   lines and each line has `requestId`, `level`, `channel`, and either
   `message` or `exception.*`.

### Acceptance criteria
- A `channels.json`/`channels.json.php` channel whose handler is a `StreamHandler`
  using `JsonFormatter`.
- Every log line for requests hitting `api` group written to the json channel as
  one JSON object, containing at minimum `requestId`, `level`, `channel`, `message`.
- Trace middleware assigned a `requestId` and shared through `Log::share()` so
  any child log inherits the same trace id within the same request lifecycle.
- At least one new test asserting the structured channel produces valid JSON.

### Dependencies
None — but should precede 4.5.2 (error tracking config) since the error path
extension is part of this item.

---

## Item 4.5.1 — Observability foundation (health + readiness + structured logging combo)

`HealthController` and `SystemHealthController` already exist. They are a start,
not an end. This item completes the foundation: make readiness verification
authoritative, ensure health endpoints are covered by tests, and route them
properly (health checks for Stripe/Paystack should probe the gateway's webhook
health if reachable — but guard against timeout: do not block on external
services in readiness; report what's checked only).

### Work
1. Read `VerifyReadinessCommand` and its registration. Confirm it currently
   reports DB connectivity only. Extend to include: Redis (if a queue
   connection is wired — confirm `REDIS_HOST` usage), disk space thresholds,
   outbound webhook endpoint reachability (with a short connect timeout so a
   flaky gateway cannot hold readiness green). Refuse readiness if critical
   services have timeout-based failures; report per-check status instead of
   binary up/down.
2. Add feature tests: a passing readiness check with all healthy, a failing
   case where DB connection drops mid-flight (faked session), a case where an
   external webhook URL refuses connection.
3. Ensure `/healthz` and `/system-health` are documented in
   `docs/operations/monitoring-and-alerting.md` and stay consistent with the
   real command output shape.

### Acceptance criteria
- Readiness command performs N checks with per-check status (healthy/degraded/fail)
  rather than a single binary response.
- Test suite asserts both healthy and degraded scenarios.

### Dependencies
4.5 first (health is read by observability docs).

---

## Item 4.5.2 — Error tracking integration

The framework docs (`docs/operations/error-tracking.md`) describe ideal behaviour
but there is no integration code. This item closes that gap.

### Pre-work
1. Read `docs/operations/error-tracking.md` sections describing: missing field
   `environment`, excluded traces, ruleset, owner assignment, escalation levels.
2. Confirm whether there is any existing error tracker package in
   `composer.json` autoload — search `composer.json` for packages like
   `laravel-error` or `sentry/sdk-php`. Earlier audit found none active.

### Work
1. Decision the next actor makes: (a) add Sentry SDK if credentials exist, (b)
   implement a lightweight logging-backed stack trace collector that writes
   exception snapshots to a dedicated `errors` log channel instead of
   propagating raw `exception` objects to the client, (c) another approach —
   note that `Handler::respondUsing()` strips `exception`/`file`/`line`/
   `trace` from the client response. Option (c) partially covers client
   exposure; server-side retention for client-facing incidents is still gap.
2. If going with option (b): create `errors` log channel in `config/logging.php`
   writing one JSON line per event with fields: `timestamp`, `requestId`,
   `env` (from `APP_ENV`), `logger`, `method`, `url`, `status`, `exception
   message`, `exception class`, `trace` (truncated to last 3 frames).
   Never propagate secrets from exception data to the logs.

### Acceptance criteria
- Either Sentry SDK wired (with env vars read from config, never hardcoded) OR a
  dedicated `errors` JSON log channel documenting exception events with
  `requestId`, `env`, `exceptionClass`, truncated `trace`.
- At least one feature test asserting the error envelope path logs through the
  appropriate channel.

### Dependencies
4.5 first (error envelope is already handled via `ApiResponse` trait; this item
extends the logging side).

---

## Item 4.2 — Accessibility WCAG audit

Blade views built over a long period (330 per roadmap). This requires
sedentary inspection across view files, which is not something a pure
auto-discovery agent does well — pair with at least 2 days of focused review.
Either run the accessibility tool across views and fix reported violations, or
manually audit the report. Given rate limits from subagents, do it in one
pass.

### Approach (recommended)
1. Scan all Blade views under `resources/views/` (recursively) for:
   - `@if` conditions rendering buttons by href (actionable but non-default)
   - `<img>` without `alt=`
   - interactive elements via JavaScript only (`@click`, inline JS)
   - forms without associated `<label>`
   - missing `aria-*` on custom interactive elements
2. Start from the highest-density areas: `auth/`, `layouts/`, `admin/`,
   `vendor/`, then catalogue storefront (`home`, `products`, `product-*,
   `category-*`, `brand-*` templates). Manually review each matched violation.
3. Prioritize irreversible fixes first (interactive elements mis-labeled as
   links/buttons; images meaningless on screen readers) over cosmetic ones
   (reduced motion, contrast quirks) — the latter can be deferred as a second
   pass once the unblockable items land.

### Acceptance criteria
- Every view flagged in audit output has an actionable fix in source.
- No unfixed critical/high severity issues remain from the initial pass; medium
  severity issues deferred with a documented reason in the fix notes.
- Update `docs/operations/performance-accessibility-audit-2026-03-03.md`
  to extend its point-in-time snapshot into a repeatable lint command defined in
  CI (tailwind/accessibility lint hook is available if Vue/Alpine only affects
  some surfaces).

### Dependencies
None — can begin immediately.

---

## Item 4.3 — Frontend consistency (ADR + component library)

Decide Livewire explicitly (no architectural debt), then extract a component
library and install it where missing. Later views will be consistent with
already-built ones.

### Step 1 — Decide Livewire (ADR)
1. Read `docs/architecture/overview.md` frontend architecture section — it states
   the Livewire question explicitly. Ask the deciding agent: is adding Livewire
   viable in this project (stateful surface with async mutations) or is it
   deadweight given the current Alpine + HTMX-style ad hoc state management?
2. The agency vote (this task) records the decision as ADR text:
   - Use name, date, stakeholder, decision, rationale, tradeoffs,
     residual questions, future recommendations.
   - Store under `docs/architecture/decisions/` as
   `livewire-consistency-2026-11-06.md`.
3. Deploy: do NOT install Livewire as the frontend keeps going. Only record
   the decision as an ADR; do not install packages until a full team review.

### Step 2 — Component library extraction
1. Review the Blade components in the project — enumerate them once:
   `grep -rn "@extends('.*components.*')" resources/views/` plus
   `find resources/views -name "*.blade.php"` scanning for component tags.
   Prior exploration noted `resources/` layout usage — check
   `app/View/Components/` or `resources/views/components/` for existing named
   components.
2. Choose blade component library style (recommended, conservative): a set of
   small single-purpose components covering the repeated patterns:
   - `ui/button.blade.php` (variants: primary, secondary, ghost, destructive)
   - `ui/card.blade.php`, `ui/form-field.blade.php` (with label + hint),
     `ui/pagination.blade.php` (wraps the pagination envelope output),
     `ui/badge.blade.php`
   Do not try to replace every Blade view — target the patterns the auditors
   (Step 1 for 4.2) flag as repeated. Keep components additive; existing views
   that reimplement the same pattern get updated in place.
3. Implement per chosen patterns with tests under
   `tests/Unit/View/Components/` — render each component once per variant to verify
   output contracts.
4. Add a `docs/architecture/frontend-components.md` listing every component,
   its inputs/outputs contract, and migration guidance ("views using old
   `<div>` wrapper removed in favour of Card") so future PRs reuse it.

### Acceptance criteria
- Livewire ADR recorded; no new package installed.
- A `ui/` blade component library added with ≥ 4 named components + tests.
- `docs/architecture/frontend-components.md` updated with at least
  the component catalog and a migration snippet.

### Dependencies
4.2 first (view audit informs component candidates).

---

## Item 4.1 — Performance (N+1 audit, index review, caching)

No auto-discovery success in prior attempts; this needs focused effort paired
against the routes enumerated (44 GET routes under `api/v1` generate the
most direct threat surface). Sequencing: audit → review → repair.

### Work sequence
1. **N+1 audit.** Write a script that runs all 44 GET GET routes through an
   HTTP-aware worker hitting each in turn, capturing SQL queries via the
   profiler (`DB::enableQueryLog()` inside a test, or dump the query log in the
   test suite). Cross-reference queries issued per request against the known
   controller actions in `app/Http/Controllers/Api/*Controller.php`. Record
   violations in a scratch markdown table with columns: route, controller,
   recorded N+1, recommended Eloquent relation / eager-loaded attribute,
   remediation risk.
2. **Index review.** Pull representative queries from #1, map each filter /
   sort / join against migrations (read `database/migrations/*.sql` indexes).
   Identify missing composite indexes on `products`, `orders`, `payments`,
   `vendor_payments`, `refund_ledger`. Use
   `docs/operations/database-optimization.md` as template guidance — add index
   recommendations with migration justification under a scratch file, defer
   write migrations to the deploy window only once perf gains justify them.
3. **Cache the storefront** for the four highest-traffic GET collections:
   `GET /api/v1/products`, `GET /api/v1/categories`, `GET /api/v1/brands`,
   `GET /api/v1/salon`. Add `Cache::remember(...)` with a sensible TTL
   (storefront is least personal traffic — 5–15 minutes is reasonable),
   wrap in try/catch so invalidation failure falls back to refresh-on-miss.
   Exclude authenticated cart/order routes — those change every request.
4. Add a feature test: write a storefront GET, assert second call within
   one TTL returns cached data identical to first call, and that a
   `Cache::forget(...)` forces refresh.

### Acceptance criteria
- N+1 audit reports ≤ 3 remaining (most were already fixed in prior work); each
  violation comes with a fix justification.
- Index review documents ≥ 5 added index recommendations (count ≥ 3 to prove the
  audit worked despite prior partial completion).
- Storefront 4-collection endpoint served from `Cache::remember` in tests.

### Dependencies
4.3 first (route list is enriched for the audit).

---

## Item ordering in this plan

```
4.4 (OpenAPI spec)        → starts immediately, no dependencies
4.5 (structured logging)  → needed by 4.5.2 and by observability docs
4.5.1 (readiness)         → depends on 4.5 for tracing fields
4.5.2 (error tracking)   → depends on 4.5.1 for logged errors
4.2 (accessibility audit)→ parallel with 4.4; lower blast radius if started first
4.3 (frontend consistency)→ depends on 4.2 component evidence
4.1 (performance)         → depends on 4.3 route list
```

### Recommended launch order (concrete steps)
1. Run 4.4 alone first — one subagent, one API call, closes the most visible gap
   and writes the manifest item into Phase 4.4 status.
2. Pair 4.4 and 4.2 together but split the work: one subagent owns OpenAPI
   generation, the other owns view audit. Accept either subagent hit the
   rate limit — retry after waiting 60 min for the free tier to recover.
3. Submit 4.5 as one self-contained task (config changes only, code changes
   single file each) — single launch avoids multiple-quota hits.
4. Submit 4.5.1 and 4.5.2 together as one larger unit covering readiness +
   error logging — one launch covers both observability legs because they
   share the trace ID story.
5. Submit 4.3 (Livewire decision + component library) — one launch
   handles both halves; collect design evidence before installing anything.
6. Submit 4.1 last — it depends on route list; pair with 4.3's outputs.

Each item, once complete, updates `docs/product/roadmap.md` and
`.kilo/plans/review-todo.md` — see item-specific update instructions above.
Tie every acceptance criterion to a test or tool assertion; nothing is a
declaration.
