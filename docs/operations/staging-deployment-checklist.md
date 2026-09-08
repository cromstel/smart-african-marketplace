# Staging External Deployment Execution Checklist

Use this checklist to execute the strictly external deployment tasks on the staging environment.

## Scope
- Target environment: `staging`
- Local repo policy: do **not** pull any remote changes into local worktree
- Execution mode: operator-run on staging host / CI runner

## 1) Secrets and environment
Set and verify these on staging:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` set to staging HTTPS URL
- Database connection vars
- SMTP vars
- `STRIPE_KEY`
- `STRIPE_SECRET`
- `PAYSTACK_PUBLIC_KEY`
- `PAYSTACK_SECRET_KEY`

Verification command:

```powershell
php artisan app:verify-readiness --strict
```

Expected: pass (no pending migrations, no missing secret warnings).

Optional one-shot runner (captures transcript evidence under `storage/logs/deploy-evidence`):

```powershell
powershell -ExecutionPolicy Bypass -File scripts/deploy/run-staging-external-checklist.ps1
```

## 2) Database and data readiness
Run:

```powershell
php artisan migrate --force
php artisan db:seed --class=SubscriptionPlanSeeder --force
php artisan migrate:status
```

Expected:
- `migrate --force` succeeds
- subscription plans seeded
- `migrate:status` shows no pending migrations

## 3) Runtime services
Ensure:
- queue worker running under process manager
- scheduler cron running every minute

Quick checks:

```powershell
php artisan queue:work --once
php artisan schedule:list
```

## 4) SSL/TLS tasks
Apply the procedure from `docs/operations/ssl-certificate-renewal.md`.

Post-renewal validation:
- HTTPS handshake works
- full chain valid
- no mixed-content warnings on key pages
- webhook endpoints reachable over TLS

## 5) Webhook real-credential verification
Run:

```powershell
.\scripts\webhooks\verify-real-credentials.ps1
php artisan test --filter=WebhookE2ETest
```

Expected: both commands pass.

## 6) Smoke verification
Run minimum smoke checks from `docs/operations/deployment-runbook.md`:
- admin login and `/admin/dashboard`
- vendor login and vendor submenu access
- checkout path basic flow
- mobile auth endpoints:
  - `POST /api/v1/mobile/auth/login`
  - `GET /api/v1/mobile/auth/me`

## 7) Performance and accessibility evidence
Attach:
- local Playwright perf/a11y automation outputs
- staging page timings and accessibility spot-check notes
- screenshots for desktop/mobile dashboard behavior

## 8) Evidence package (required)
Archive these artifacts for handover:
- strict readiness output
- migration output
- webhook verification output
- SSL validation output
- smoke test output
- dashboard screenshots (desktop/mobile)

## 9) Go/No-Go gate
Proceed only if all are true:
- strict readiness passes
- no pending migrations
- webhook real-credential verification passes
- SSL/TLS validation passes
- smoke checks pass

## Latest execution evidence (March 3, 2026)
- Checklist command: `powershell -ExecutionPolicy Bypass -File scripts/deploy/run-staging-external-checklist.ps1`
- Evidence log: `storage/logs/deploy-evidence/staging-external-checklist-20260303-144055.log`
- Result: readiness, migration status, queue/scheduler checks, webhook credential verification, and webhook E2E regression all passed.
