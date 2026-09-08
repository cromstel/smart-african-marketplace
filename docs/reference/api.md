# API Reference

**Status: Current** — verified against `routes/api.php` and
`app/Providers/RouteServiceProvider.php` on 2026-09-08.

[Documentation Hub](../README.md) › Reference › API

The REST API serving the native mobile clients and any external integration.
Server-rendered web routes live in `routes/web.php` and are not part of this
contract.

---

## Contents

- [Base URL and versioning](#base-url-and-versioning)
- [Authentication](#authentication)
- [Rate limiting](#rate-limiting)
- [Public endpoints](#public-endpoints)
- [Authenticated endpoints](#authenticated-endpoints)
- [Vendor endpoints](#vendor-endpoints)
- [Mobile endpoints](#mobile-endpoints)
- [Errors](#errors)
- [Webhooks](#webhooks)
- [Known gaps](#known-gaps)

---

## Base URL and versioning

```
https://<your-domain>/api/v1
```

All routes are prefixed `v1` and named `api.*`. There is no `v2`. Breaking
changes require a new prefix — the Android app in `mobile/android/` pins `v1`.

---

## Authentication

**Laravel Sanctum** bearer tokens.

```http
Authorization: Bearer <token>
Accept: application/json
```

Obtain a token from [`POST /api/v1/mobile/auth/login`](#mobile-endpoints).
Token lifetime is governed by `SANCTUM_EXPIRATION`
([Configuration](configuration.md#security)); unset means tokens do not expire,
which is worth changing deliberately rather than by default.

Identity check:

```http
GET /api/user
```

Returns the authenticated user. Note this route sits **outside** the `v1` prefix.

---

## Rate limiting

Named limiters from `RouteServiceProvider`:

| Limiter | Limit | Keyed by |
|---|---|---|
| `api` | 60/min | user id, else IP |
| `public-forms` | 20/min | IP |
| `search` | 90/min | user id, else IP |
| `chatbot` | configurable, clamped 10–600/min | user id, else IP |

Route-level throttles:

| Route | Throttle |
|---|---|
| `POST /api/v1/mobile/auth/login` | `10,1` |
| `POST /api/v1/mobile/auth/register` | **none** — see [Known gaps](#known-gaps) |

Exceeding a limit returns `429 Too Many Requests` with a `Retry-After` header.

---

## Public endpoints

No authentication required.

### Catalogue

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/products` | List products |
| `GET` | `/api/v1/products/{product}` | Product detail |
| `GET` | `/api/v1/categories` | List categories |
| `GET` | `/api/v1/categories/{category}` | Category detail |
| `GET` | `/api/v1/brands` | List brands |
| `GET` | `/api/v1/brands/{brand:slug}` | Brand detail by slug |
| `GET` | `/api/v1/brands/{brand:slug}/products` | Products for a brand |

### Salon

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/salon` | List salons and services |

Brands resolve by **slug**; products and categories by **id**. Worth aligning in
a future version.

---

## Authenticated endpoints

Require `Authorization: Bearer <token>`.

### Cart

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/cart` | Current cart |
| `POST` | `/api/v1/cart` | Add an item |
| `PATCH` | `/api/v1/cart/{cartItem}` | Update quantity |
| `DELETE` | `/api/v1/cart/{cartItem}` | Remove an item |

### Orders

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/orders` | List the caller's orders |
| `GET` | `/api/v1/orders/{order}` | Order detail |

### Addresses

Full resource — `index`, `store`, `show`, `update`, `destroy`:

| Method | Path |
|---|---|
| `GET` | `/api/v1/addresses` |
| `POST` | `/api/v1/addresses` |
| `GET` | `/api/v1/addresses/{address}` |
| `PUT`/`PATCH` | `/api/v1/addresses/{address}` |
| `DELETE` | `/api/v1/addresses/{address}` |

### Checkout

| Method | Path | Description |
|---|---|---|
| `POST` | `/api/v1/checkout/shipping-preview` | Quote shipping before committing |
| `POST` | `/api/v1/checkout` | Place the order and initiate payment |

`shipping-preview` exists so the client can show a total before the customer
commits. Call it whenever the address or cart changes.

> Every endpoint returning a `{cartItem}`, `{order}` or `{address}` must scope by
> the authenticated user. Ownership is enforced in application code, not by the
> database — see [Architecture](../architecture/overview.md#multi-tenancy-and-roles).

---

## Vendor endpoints

Require a token **and** the `vendor` middleware.

| Method | Path | Description |
|---|---|---|
| `GET` | `/api/v1/vendor/modules` | Modules the vendor's plan entitles them to |

Backs client-side feature gating. The server enforces entitlement independently
via `EnsureModuleAccess` — the client must not be the only gate.

---

## Mobile endpoints

Consumed by the native Android app.

| Method | Path | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/mobile/auth/login` | none | Issue a token. Throttled `10,1` |
| `POST` | `/api/v1/mobile/auth/register` | none | Create an account |
| `GET` | `/api/v1/mobile/auth/me` | token | Current user |
| `PUT` | `/api/v1/mobile/auth/profile` | token | Update profile |
| `POST` | `/api/v1/mobile/auth/logout` | token | Revoke the current token |

---

## Errors

Standard Laravel JSON shapes. Always send `Accept: application/json` — without it
you may receive an HTML error page.

| Status | Meaning |
|---|---|
| `401` | Missing, invalid or expired token |
| `403` | Authenticated but not permitted (wrong role, or not the owner) |
| `404` | Not found — **or** found but not yours |
| `422` | Validation failed |
| `429` | Rate limited; honour `Retry-After` |
| `500` | Server error |

Validation errors:

```json
{
  "message": "The given data was invalid.",
  "errors": { "email": ["The email field is required."] }
}
```

> Returning `404` rather than `403` for another tenant's resource is the correct
> default — `403` confirms the resource exists.

---

## Webhooks

Inbound from payment providers, handled by `WebhookController` and
`SwishPaymentController`. Not part of the client API.

| Provider | Verification |
|---|---|
| Stripe | `\Stripe\Webhook::constructEvent()` against `STRIPE_WEBHOOK_SECRET` |
| Paystack | `hash_equals()` on an HMAC of the raw body, keyed by `PAYSTACK_SECRET_KEY` |
| Swish | `hash_equals()` on the computed signature |

Handlers are idempotent: replaying an event must not create a second payment.

Test locally without a provider:

```bash
php artisan webhooks:replay-fixture stripe-charge-succeeded
```

See [Webhook E2E](../WEBHOOK_E2E.md).

---

## Known gaps

Scheduled in the [Roadmap](../ROADMAP.md).

1. **`POST /mobile/auth/register` is unthrottled.** Login is limited to 10/min;
   registration has no limit and creates rows. *(Roadmap 0.2)*
2. **No automated webhook tests.** The signature checks are implemented, but the
   tests cited as evidence do not exist. *(Roadmap 1.1–1.3)*
3. **No pagination contract.** List endpoints do not document page size, cursors
   or envelope shape.
4. **No generated OpenAPI spec.** This page is maintained by hand and can drift.
5. **Inconsistent route binding.** Brands bind by slug, products and categories
   by id.
6. **Thin surface.** No API access to bookings, auctions, subscriptions,
   wishlists or reviews, all of which exist on the web.

---

## See also

- [Architecture Overview](../architecture/overview.md)
- [Configuration Reference](configuration.md)
- [Testing Guide](../guides/testing.md)
- [Mobile App Implementation](../../documentation/mobile-app-implementation.md)
