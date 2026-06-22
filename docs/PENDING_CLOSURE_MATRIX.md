# Laravel Pending Closure Matrix

This matrix tracks closure of Laravel-active pending tasks and separates code-complete items from deployment-time environment provisioning.

| Item | Status | Evidence | Notes |
|---|---|---|---|
| Stripe webhook signature validation | Complete | `tests/Feature/WebhookE2ETest.php` | Invalid signature path verified. |
| Paystack webhook signature validation | Complete | `tests/Feature/WebhookE2ETest.php` | Invalid signature path verified with `hash_equals`. |
| Webhook idempotent replay behavior | Complete | `tests/Feature/WebhookE2ETest.php` | Duplicate replay does not duplicate payments. |
| Deterministic local webhook replay tooling | Complete | `app/Console/Commands/ReplayWebhookFixtureCommand.php`, `scripts/webhooks/replay-fixtures.ps1` | No live provider callback required. |
| Readiness verification command | Complete | `app/Console/Commands/VerifyReadinessCommand.php` | Supports `--strict` for production gate. |
| Deployment runbook executable verification steps | Complete | `docs/DEPLOYMENT_RUNBOOK.md` | Includes readiness and webhook E2E commands. |
| CI non-strict readiness gate | Complete | `.github/workflows/ci.yml` | Always runs in CI. |
| CI strict readiness gate with secrets | Complete | `.github/workflows/ci.yml` | Runs only when all required secrets exist. |
| Migration state | Complete | `php artisan migrate:status` | No pending migrations. |
| Test suite baseline | Complete | `php artisan test` | Current suite passes. |
| Production payment keys provisioning | Deployment-time | Env/Secrets | Requires real values in environment. |
| Production SMTP provisioning | Deployment-time | Env/Secrets | Requires real `MAIL_*` values. |
| SSL/TLS, backups, infra monitoring wiring | Deployment-time | Infrastructure | Not a code gap. |

