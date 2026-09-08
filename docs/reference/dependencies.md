# Dependency Reference

**Status: Current** — resolved and audited 2026-09-08.

[Documentation Hub](../README.md) › Reference › Dependencies

What the project depends on, how versions are pinned, and which upgrades are
outstanding and why.

---

## Contents

- [Policy](#policy)
- [PHP runtime and extensions](#php-runtime-and-extensions)
- [Composer dependencies](#composer-dependencies)
- [npm dependencies](#npm-dependencies)
- [Security posture](#security-posture)
- [Outstanding major upgrades](#outstanding-major-upgrades)
- [Keeping this current](#keeping-this-current)

---

## Policy

Take the latest stable release **compatible with the declared platform and the
rest of the tree**. Not the newest release that exists.

That distinction matters here: four direct dependencies have newer major
versions available, and all four are deliberately not taken — see
[Outstanding major upgrades](#outstanding-major-upgrades). A major bump on a
payment library, in a codebase whose payment tests have never been executed, is
not an upgrade; it is an untested rewrite of the money path.

Rules:

- No prerelease, beta, alpha or abandoned packages
- No dependency added where the framework or an existing package already does it
- Lock files are committed and are the source of truth for what is installed
- `composer.lock` and `package-lock.json` change together with the manifest that
  caused the change, in the same commit

---

## PHP runtime and extensions

`composer.json` requires `php: ^8.2` and pins resolution to that floor:

```json
"config": { "platform": { "php": "8.2.0" } }
```

Without that pin, a developer on 8.4 resolves packages that a server on 8.2
cannot install. CI runs a matrix of **8.2 and 8.3** so both the declared floor
and the deployed version are exercised.

### Extensions

| Extension | Needed by |
|---|---|
| **bcmath** | `moneyphp/money`, via Laravel Cashier |
| pdo_mysql, mysqli | Database |
| mbstring | Framework, dompdf, and much of the tree |
| xml, dom, libxml, xmlwriter | dompdf, css-to-inline-styles, PHPUnit |
| curl | Stripe SDK, HTTP client |
| zip, gd | Archives, image processing |
| fileinfo | Flysystem, MIME detection |
| openssl | Framework, OAuth |
| tokenizer, ctype, filter, hash, session, pcre, iconv, json | Framework internals |

> **`bcmath` is not optional and was undocumented until 2026-09-08.** Laravel
> Cashier depends on `moneyphp/money`, which requires it, so `composer install`
> fails outright without it — it is not a runtime surprise, it is an install
> failure. It was absent from the README, TECH_STACK and the CI extension list.
> It is now declared in `composer.json` as `ext-bcmath` so the requirement is
> visible in the manifest rather than only three levels down the tree.
>
> Debian/Ubuntu: `sudo apt install php8.2-bcmath`.

---

## Composer dependencies

Direct requirements:

| Package | Constraint | Purpose |
|---|---|---|
| `laravel/framework` | ^12.0 | Framework |
| `laravel/cashier` | ^16.0 | Stripe subscriptions and billing |
| `laravel/sanctum` | ^4.0 | API token authentication |
| `laravel/socialite` | ^5.0 | Google and Facebook OAuth |
| `laravel/tinker` | ^3.0 | REPL |
| `livewire/livewire` | ^3.0 | Reactive components (2 in use) |
| `stripe/stripe-php` | ^17.0 | Stripe API |
| `twilio/sdk` | ^8.0 | SMS |
| `barryvdh/laravel-dompdf` | ^3.0 | PDF generation |
| `intervention/image` | ^3.0 | Image processing |
| `endroid/qr-code` | ^6.0 | QR codes |
| `spatie/laravel-permission` | ^6.0 | Roles and permissions |
| `spatie/laravel-activitylog` | ^4.0 | Audit trail |
| `spatie/laravel-sluggable` | ^3.0 | Slugs |

Dev: `phpunit/phpunit` ^11, `laravel/pint` ^1, `laravel/telescope` ^5,
`mockery/mockery` ^1.6, `nunomaduro/collision` ^8, `fakerphp/faker` ^1.23,
`laravel/breeze` ^2, `laravel/sail` ^1, `spatie/laravel-ignition` ^2.

**Last resolution:** 57 packages updated within existing constraints on
2026-09-08, including `laravel/framework` v12.63.0 → v12.69.2 and the Symfony
7.4 components. No constraint in `composer.json` was widened.

Two transitive majors came with that update, both mandated by their dependents
rather than chosen:

| Package | Change | Required by |
|---|---|---|
| `phpseclib/phpseclib` | 3.0.55 → 4.0.1 | `laravel/socialite` v5.31.0 requires `^4.0` |
| `hamcrest/hamcrest-php` | 2.1.1 → 3.0.0 | `mockery/mockery` 1.6.15 allows `^2.0 \|\| ^3.0` |

---

## npm dependencies

Runtime: `chart.js` ^4.5.1, `laravel-echo` ^1.16.1, `pusher-js` ^8.4.0,
`tinymce` ^7.9.3.

Build and tooling: `vite` ^8.1.4, `laravel-vite-plugin` ^3.0, `tailwindcss`
^3.1, `@tailwindcss/forms`, `postcss`, `autoprefixer`, `alpinejs` ^3.4,
`axios`, `esbuild`, `eslint` ^8.57.1, `prettier` ^3.3.3, `playwright` ^1.58.2,
`rimraf`.

`package.json` carries `overrides` pinning `picomatch` to patched builds. Leave
those in place unless the advisory they answer is confirmed resolved upstream.

---

## Security posture

Both trees audited 2026-09-08 after updating:

| Tree | Before | After |
|---|---|---|
| Composer | No advisories | **No advisories** |
| npm | **5 high severity** | **0** |

The npm findings were `nanoid` (two advisories: non-secure generators looping on
negative and zero size), `postcss` (two path-traversal advisories via
`sourceMappingURL`, one an incomplete fix of an earlier issue), and `js-yaml`.
All three resolved **within existing semver ranges** — `nanoid` 3.3.15 → 3.3.18,
`postcss` 8.5.16 → 8.5.28, `js-yaml` 4.3.0 → 4.3.2 — so no manifest change was
needed, only a stale lock file.

> That is the lesson worth keeping: the vulnerabilities were not caused by
> permissive constraints, they were caused by a lock file nobody had refreshed.
> `.github/workflows/security-audit.yml` exists; make sure it fails the build on
> high-severity findings rather than only reporting them.

Run both locally:

```bash
composer audit
npm audit
npm run security-check     # npm audit --audit-level=high
```

---

## Outstanding major upgrades

Available, resolvable, and **deliberately not applied**. Each needs a human
decision and a green test run first.

| Package | Current | Latest | Assessment |
|---|---|---|---|
| `stripe/stripe-php` | 17.6.0 | 21.3.1 | Cashier v16.8 permits `^17.4\|^18\|^19\|^20\|^21`, so it resolves cleanly. But it spans four majors of API change on the payment path, and `StripePaymentService` calls the SDK directly. Do this after the webhook tests have run green, not before. |
| `livewire/livewire` | 3.8.8 | 4.4.4 | Resolves cleanly. Blast radius is unusually small — only 2 components and 2 views use Livewire — but v4 changes component and template syntax. Cheap to do, still needs verifying by eye. |
| `laravel-echo` | 1.19.0 | 2.5.0 | Pairs with the Soketi/Pusher setup; check protocol compatibility before bumping. |
| `tinymce` | 7.9.3 | 8.9.0 | Editor used in admin content screens. Licence terms have changed across TinyMCE majors — check before upgrading. |

None of these are security-driven; both trees are clean at their current
versions.

---

## Keeping this current

```bash
# Composer — within existing constraints
composer update
composer audit
composer outdated --direct        # what has a newer major

# npm — within existing constraints
npm update
npm audit
npm outdated
```

Widening a constraint in `composer.json` or `package.json` is a deliberate act:
say why in the commit message, and confirm the test suite passes on the new
version before merging.

---

## See also

- [Tech Stack](../architecture/tech-stack.md)
- [Configuration Reference](configuration.md)
- [Getting Started](../guides/getting-started.md)
- [Testing Guide](../guides/testing.md)
- [Roadmap](../product/roadmap.md)
