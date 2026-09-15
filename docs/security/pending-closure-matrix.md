# Laravel Pending Closure Matrix

**Status: Current** — corrected 2026-09-08, test-count refreshed 2026-09-15.

[Documentation Hub](../README.md) › Pending Closure Matrix

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
> **Update 2, 2026-09-08: the suite has now been executed. 56 tests pass, 190
> assertions, against MySQL 10.11 on PHP 8.3.** The rows below are Complete on
> evidence, not on intent. Getting there required fixing six defects that made
> the suite unrunnable at all — see
> [Fresh-install defects](../product/documentation-audit.md#finding-10--the-application-could-not-be-installed-or-migrated-from-a-clean-clone).
>
> Original update: the fixtures and tests now exist —
> `tests/Fixtures/webhooks/` and `tests/Feature/WebhookE2ETest.php`, covering
> signature acceptance, rejection of an invalid signature, rejection of a body
> tampered after signing, and replay idempotency for both providers. They have
> **not yet been executed**: they were written in an environment without PHP,
> Composer or MySQL. The rows below therefore read **Implemented, tests written,
> pending first CI run** and become Complete on a green run — not before.
>
> Going forward: **a row may not cite a file that does not exist.** The audit
> script in [Documentation Audit](../product/documentation-audit.md#reproducing-this-audit)
> checks this and belongs in CI.

This matrix tracks closure of Laravel-active pending tasks and separates code-complete items from deployment-time environment provisioning.

| Item | Status | Evidence | Notes |
|---|---|---|---|
| Stripe webhook signature validation | **Complete** | `app/Http/Controllers/WebhookController.php`; `tests/Feature/WebhookE2ETest.php` | Valid, invalid and tampered-body cases covered. |
| Paystack webhook signature validation | **Complete** | `app/Http/Controllers/WebhookController.php`; `tests/Feature/WebhookE2ETest.php` | Valid, invalid and missing-signature cases covered. |
| Webhook idempotent replay behavior | **Complete** | `tests/Feature/WebhookE2ETest.php` | Asserts a replayed event yields exactly one payment row, per provider. |
| Deterministic local webhook replay tooling | **Complete** | `app/Console/Commands/ReplayWebhookFixtureCommand.php`, `tests/Fixtures/webhooks/` | Fixtures now exist at the path the command resolves, so replay is executable. |
| Readiness verification command | Complete | `app/Console/Commands/VerifyReadinessCommand.php` | Supports `--strict` for production gate. |
| Deployment runbook executable verification steps | Complete | `docs/operations/deployment-runbook.md` | Includes readiness and webhook E2E commands. |
| CI non-strict readiness gate | Complete | `.github/workflows/ci.yml` | Always runs in CI. |
| CI strict readiness gate with secrets | Complete | `.github/workflows/ci.yml` | Runs only when all required secrets exist. |
| Migration state | Complete | `php artisan migrate:status` | No pending migrations. |
| Test suite baseline | **Complete** | `php artisan test` — 248 passed, 46 test files (refreshed 2026-09-15) | Grew from the original 56 passed / 10 files via Roadmap Phase 2: the seven money/trust areas in [2.2](../product/roadmap.md#22-cover-the-money-and-trust-paths-in-this-order) and 19-controller vendor isolation in [2.3](../product/roadmap.md#23-vendor-isolation-as-a-first-class-test-pattern--target-set-complete) are now covered. Not "100% coverage" — see Phase 2's guiding principle. |
| Production payment keys provisioning | Deployment-time | Env/Secrets | Requires real values in environment. |
| Production SMTP provisioning | Deployment-time | Env/Secrets | Requires real `MAIL_*` values. |
| SSL/TLS, backups, infra monitoring wiring | Deployment-time | Infrastructure | Not a code gap. |



---

## See also

- [Documentation Audit](../product/documentation-audit.md) — how these corrections were found
- [Roadmap](../product/roadmap.md) — when each gap closes
- [Webhook E2E](../operations/webhook-e2e.md) — the verification procedure
- [Testing Guide](../guides/testing.md) — how to write the missing tests
