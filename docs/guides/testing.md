# Testing Guide

**Status: Current** — created 2026-09-08.

[Documentation Hub](../README.md) › Guides › Testing

How to run, write and structure tests. Read
[Roadmap Phase 2](../product/roadmap.md#phase-2--establish-a-real-test-foundation-46-weeks)
for what needs covering and in what order.

---

## Where the suite stands today

| Metric | Count |
|---|---|
| Test files | 6 |
| Controllers | 146 |
| Models | 98 |

Existing tests: `AuthenticationTest`, `AuthorizationTest`, `AntiSpamTest`,
`Api/RegistrationTest`, `Api/AddressApiTest`, `Api/CheckoutApiTest`.

Payments, subscriptions, auctions, bookings, payouts and vendor isolation have
no automated coverage. Treat that as the context for every change you make: if
you touch one of those areas, you are the first line of defence.

---

## Running tests

```bash
php artisan test                          # everything
php artisan test --testsuite=Feature      # the Feature suite
php artisan test tests/Feature/AuthenticationTest.php
php artisan test --filter=it_rejects_an_invalid_signature
php artisan test --coverage                # requires Xdebug or PCOV
```

### Test database

`phpunit.xml` runs against **MySQL**, database `african_market_test`, not
SQLite — the schema uses MySQL-specific behaviour. Create it once:

```sql
CREATE DATABASE african_market_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

The suite overrides: `APP_ENV=testing`, `BCRYPT_ROUNDS=4`, `CACHE_DRIVER=array`,
`SESSION_DRIVER=array`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=array`,
`TELESCOPE_ENABLED=false`.

> `QUEUE_CONNECTION=sync` means queued work runs inline. Convenient, but it hides
> ordering and failure behaviour — assert with `Queue::fake()` when the dispatch
> itself is the thing under test.

---

## Suite structure

`phpunit.xml` currently defines **only a Feature suite**. There is no Unit suite,
so service classes cannot be tested without booting the framework. Adding one is
[Roadmap 2.1](../product/roadmap.md#21-split-the-test-suites):

```xml
<testsuites>
  <testsuite name="Unit">
    <directory suffix="Test.php">./tests/Unit</directory>
  </testsuite>
  <testsuite name="Feature">
    <directory suffix="Test.php">./tests/Feature</directory>
  </testsuite>
</testsuites>
```

| Suite | Tests | Boots Laravel |
|---|---|---|
| `Unit` | Pure logic — pricing, commission, distance, slot maths | No |
| `Feature` | Routes, middleware, policies, persistence | Yes |

---

## Writing a feature test

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_add_a_product_to_their_cart(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/cart', [
                'product_id' => $product->id,
                'quantity'   => 2,
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);
    }
}
```

Conventions:

- One behaviour per test; name it as a sentence
- `RefreshDatabase` for anything touching persistence
- Assert the **database**, not only the response — a 200 with no row is a bug
- Build state with factories, never by hand-inserting rows

---

## The vendor isolation pattern

**The most important test pattern in this codebase.** Tenancy is enforced in
application code, not by the database, so an unconstrained query is a
cross-tenant data leak rather than a cosmetic bug.

Every vendor-scoped endpoint needs this shape:

```php
public function test_a_vendor_cannot_view_another_vendors_order(): void
{
    $mine      = Vendor::factory()->create();
    $theirs    = Vendor::factory()->create();
    $theirOrder = Order::factory()->for($theirs)->create();

    $this->actingAs($mine->user)
        ->getJson("/api/v1/vendor/orders/{$theirOrder->id}")
        ->assertNotFound();   // 404, not 403 — 403 confirms it exists
}
```

Apply it across all 32 vendor controllers. Assert `404`: a `403` tells the caller
the resource exists, which is itself a small leak.

---

## Testing webhooks

The highest-priority gap ([Roadmap 1.1–1.3](../product/roadmap.md#phase-1--make-the-payment-path-provable-2-weeks)).

Fixtures belong at `tests/Fixtures/webhooks/`, which is where
`ReplayWebhookFixtureCommand` resolves them:

```php
base_path("tests/Fixtures/webhooks/{$name}.json")
```

A webhook test must cover four things:

1. a valid signature is accepted
2. an invalid or tampered signature is rejected
3. the payment record is created correctly
4. **replaying the identical event does not create a second payment**

Point 4 is the one that costs money when it regresses.

```php
public function test_replaying_a_webhook_does_not_duplicate_the_payment(): void
{
    $payload   = file_get_contents(base_path('tests/Fixtures/webhooks/stripe-charge-succeeded.json'));
    $signature = $this->signStripe($payload);

    $this->postJson('/webhooks/stripe', json_decode($payload, true),
        ['Stripe-Signature' => $signature])->assertOk();
    $this->postJson('/webhooks/stripe', json_decode($payload, true),
        ['Stripe-Signature' => $signature])->assertOk();

    $this->assertDatabaseCount('payments', 1);
}
```

Manual replay without a provider:

```bash
php artisan webhooks:replay-fixture stripe-charge-succeeded
php artisan webhooks:replay-fixture paystack-charge-success
```

---

## What to test first

From [Roadmap 2.2](../product/roadmap.md#22-cover-the-money-and-trust-paths-in-this-order),
in order:

1. **Vendor isolation** — leak risk
2. **Checkout and order creation** — revenue path
3. **Payouts, ledger, withdrawals** — money leaving; hard to reverse
4. **Subscriptions and plan gating** — entitlement bypass is revenue loss
5. **Auctions and bidding** — concurrency-sensitive
6. **Bookings** — double-booking and slot integrity
7. **Refunds and RMA** — money and trust

Skip trivial getters. Coverage percentage is a diagnostic, not a goal.

---

## Browser tests

Playwright is configured in `package.json`:

```bash
npm run test          # headless
npm run test:ui       # interactive
npm run test:headed   # visible browser
```

Use it for flows that genuinely need a browser — checkout, vendor onboarding —
not for logic a feature test can cover faster.

---

## Static analysis and style

```bash
vendor/bin/pint            # PHP formatting (Laravel Pint)
npm run lint               # ESLint
npm run lint:fix
npm run format             # Prettier check
npm run format:fix
npm run security-check     # npm audit, high severity
```

---

## Continuous integration

`.github/workflows/ci.yml` on push and PR to `main`/`master`: PHP 8.3, MySQL 8.0
service, `composer install`, `key:generate`, `migrate --force`, `php artisan test`.

Also `.github/workflows/security-audit.yml` and `android-release.yml`.

> CI pins **PHP 8.3** while `composer.json` requires `^8.2`. Reconcile
> deliberately ([Roadmap 0.4](../product/roadmap.md#04-reconcile-the-php-version-low)) so
> you are testing what you deploy.

### Worth adding to CI

The two audit scripts from
[Documentation Audit](../product/documentation-audit.md#reproducing-this-audit) — broken
links, and docs citing files that do not exist. Both are dependency-free and
would have caught the fictional-evidence problem before it shipped.

---

## Definition of done

A change is ready for review when:

- [ ] `php artisan test` passes
- [ ] New behaviour has a test that fails without the change
- [ ] Vendor-scoped changes have an isolation test
- [ ] `vendor/bin/pint` is clean
- [ ] Docs updated in the same commit
- [ ] No document cites a file that does not exist

---

## See also

- [Contributing](../../CONTRIBUTING.md)
- [Roadmap](../product/roadmap.md)
- [Architecture Overview](../architecture/overview.md)
- [Webhook E2E](../operations/webhook-e2e.md)
