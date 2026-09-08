# Laravel Pending Closure Matrix

**Status: Needs review** — corrected 2026-09-08.

[Documentation Hub](README.md) › Pending Closure Matrix

> ## ⚠️ Correction notice
>
> An audit on 2026-09-08 found that five rows below were marked **Complete** with
> `tests/Feature/WebhookE2ETest.php` cited as evidence. **That file does not
> exist**, and `git log --all` shows it never did.
>
> **The underlying controls are real.** `WebhookController.php` verifies Stripe
> signatures via `\Stripe\Webhook::constructEvent()` and Paystack via
> `hash_equals()`; `SwishPaymentController.php` does the same. What is missing is
> the automated proof that they keep working.
>
> Those rows are re-marked **Implemented, unverified** below. They return to
> Complete when the tests exist and pass in CI — see
> [Roadmap Phase 1](ROADMAP.md#phase-1--make-the-payment-path-provable-2-weeks).
>
> Going forward: **a row may not cite a file that does not exist.** The audit
> script in [Documentation Audit](DOCUMENTATION_AUDIT.md#reproducing-this-audit)
> checks this and belongs in CI.

This matrix tracks closure of Laravel-active pending tasks and separates code-complete items from deployment-time environment provisioning.

| Item | Status | Evidence | Notes |
|---|---|---|---|
| Stripe webhook signature validation | **Implemented, unverified** | Control: `app/Http/Controllers/WebhookController.php` (`constructEvent`). Test: _missing_ | Signature check present in code; no automated test. |
| Paystack webhook signature validation | **Implemented, unverified** | Control: `app/Http/Controllers/WebhookController.php` (`hash_equals`). Test: _missing_ | Timing-safe HMAC check present; no automated test. |
| Webhook idempotent replay behavior | **Unverified** | Test: _missing_ | Highest-risk gap: a regression here duplicates payments silently. |
| Deterministic local webhook replay tooling | **Partially complete** | `app/Console/Commands/ReplayWebhookFixtureCommand.php`, `scripts/webhooks/replay-fixtures.ps1` | Tooling exists, but it resolves `tests/Fixtures/webhooks/` which is absent — nothing to replay. |
| Readiness verification command | Complete | `app/Console/Commands/VerifyReadinessCommand.php` | Supports `--strict` for production gate. |
| Deployment runbook executable verification steps | Complete | `docs/DEPLOYMENT_RUNBOOK.md` | Includes readiness and webhook E2E commands. |
| CI non-strict readiness gate | Complete | `.github/workflows/ci.yml` | Always runs in CI. |
| CI strict readiness gate with secrets | Complete | `.github/workflows/ci.yml` | Runs only when all required secrets exist. |
| Migration state | Complete | `php artisan migrate:status` | No pending migrations. |
| Test suite baseline | **Complete, but thin** | `php artisan test` | Suite passes: 6 test files against 146 controllers and 98 models. See [Roadmap Phase 2](ROADMAP.md#phase-2--establish-a-real-test-foundation-46-weeks). |
| Production payment keys provisioning | Deployment-time | Env/Secrets | Requires real values in environment. |
| Production SMTP provisioning | Deployment-time | Env/Secrets | Requires real `MAIL_*` values. |
| SSL/TLS, backups, infra monitoring wiring | Deployment-time | Infrastructure | Not a code gap. |



---

## See also

- [Documentation Audit](DOCUMENTATION_AUDIT.md) — how these corrections were found
- [Roadmap](ROADMAP.md) — when each gap closes
- [Webhook E2E](WEBHOOK_E2E.md) — the verification procedure
- [Testing Guide](guides/testing.md) — how to write the missing tests
