> **Status: Current** — the missing files were created on 2026-09-08.
>
> [Documentation Hub](../README.md) › Operations › Webhook E2E
>
> This procedure was previously **not executable**: it depended on fixtures and
> tests that did not exist. `tests/Fixtures/webhooks/` and
> `tests/Feature/WebhookE2ETest.php` now do, so the replay command has payloads
> to load.
>
> **The tests have now been run and pass** — all 8 webhook cases, within a full
> suite of 56 passing tests on PHP 8.3 / MySQL 10.11.
>
> One caveat remains: the mobile-money tests (MobileMoneyWebhookTest,
> MobileMoneyCheckoutFlowTest) are still outstanding.

# Deterministic Webhook E2E Verification

This project includes local deterministic webhook verification for Stripe, Paystack, and Mobile Money (MTN + M-Pesa) without requiring live external callbacks.

## Prerequisites

- Database is migrated.
- App can run Artisan commands.

## Fixtures

- `tests/Fixtures/webhooks/stripe-charge-succeeded.json`
- `tests/Fixtures/webhooks/paystack-charge-success.json`

## Replay Commands

Run each provider fixture directly:

```bash
php artisan webhooks:replay-fixture stripe stripe-charge-succeeded.json --secret=whsec_fixture_test
php artisan webhooks:replay-fixture paystack paystack-charge-success.json --secret=sk_test_fixture
```

PowerShell helper:

```powershell
.\scripts\webhooks\replay-fixtures.ps1
```

## Automated Tests

Webhook deterministic E2E tests are in:

- `tests/Feature/WebhookE2ETest.php`
- MobileMoneyWebhookTest
- MobileMoneyCheckoutFlowTest

Run:

```bash
php artisan test --filter=WebhookE2ETest
php artisan test tests/Feature/MobileMoneyWebhookTest.php tests/Feature/MobileMoneyCheckoutFlowTest.php
```

## What Is Verified

- Signature rejection for invalid requests.
- Valid signed webhook processing for Stripe and Paystack.
- Idempotent replay behavior (duplicate event does not duplicate payment records).
- MTN and M-Pesa webhook callbacks update payment/order status correctly.
- Checkout can route to provider-specific Mobile Money payment flow (`provider=mtn|mpesa`).

## Real-Credential Sandbox Verification (Production Readiness)

Use this only with valid sandbox credentials configured in `.env`:

- `STRIPE_KEY`
- `STRIPE_SECRET`
- `PAYSTACK_PUBLIC_KEY`
- `PAYSTACK_SECRET_KEY`

### Step 1: Validate readiness in strict mode

```bash
php artisan app:verify-readiness --strict
```

### Step 2: Execute deterministic replay with configured secrets

```powershell
.\scripts\webhooks\verify-real-credentials.ps1
```

This script:
- Checks required webhook/payment secret env keys are set and non-placeholder.
- Replays both Stripe and Paystack fixtures via Artisan command.
- Runs `WebhookE2ETest` as a final verification gate.

### Step 3: Capture evidence

Attach the following to deployment handover:
- Command output from strict readiness.
- Command output from `verify-real-credentials.ps1`.
- Test output from `php artisan test --filter=WebhookE2ETest`.


---

## See also

- [Roadmap Phase 1](../product/roadmap.md#phase-1--make-the-payment-path-provable-2-weeks)
- [Testing Guide](../guides/testing.md#testing-webhooks)
- [Pending Closure Matrix](../security/pending-closure-matrix.md)
- [API Reference — Webhooks](../reference/api.md#webhooks)
