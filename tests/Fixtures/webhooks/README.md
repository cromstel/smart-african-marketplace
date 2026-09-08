# Webhook fixtures

Signed-replay payloads for the payment webhooks, used by
`tests/Feature/WebhookE2ETest.php` and by the replay command:

```bash
php artisan webhooks:replay-fixture stripe stripe-charge-succeeded
php artisan webhooks:replay-fixture paystack paystack-charge-success
```

`ReplayWebhookFixtureCommand` resolves names against this directory
(`base_path("tests/Fixtures/webhooks/{name}.json")`), signs the body with a
test secret, and posts it to the webhook route — no live provider callback
required.

## metadata.order_id

Each fixture carries `metadata.order_id: "1"` as a placeholder. The test suite
decodes the fixture, substitutes the id of an order it created, and re-signs
the body, so the fixed value is never relied on. When replaying by hand, either
create an order with that id or pass a fixture whose id matches your data —
otherwise the handler logs "Order not found" and returns success without
recording a payment, which looks like a pass but proves nothing.

## Signing

Reproduced from the replay command, so a hand-built request matches:

| Provider | Header | Value | Test secret |
|---|---|---|---|
| Stripe | `Stripe-Signature` | `t={ts},v1={hmac_sha256("{ts}.{body}", secret)}` | `whsec_fixture_test` |
| Paystack | `X-Paystack-Signature` | `hmac_sha512(body, secret)` | `sk_test_fixture` |

The secret is read from the `Setting` model (`stripe_webhook_secret`,
`paystack_secret_key`), not from the environment.

Signing covers the **raw body**. Re-encoding JSON before signing changes the
bytes and invalidates the signature — sign exactly what you send.
