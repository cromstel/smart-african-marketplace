# Support Quick Start (v2)

Audience: Support agents handling first-line onboarding and access issues.

## 1) First 2 Minutes Triage

1. Confirm user role:
   - Customer
   - Vendor
   - Admin
2. Confirm exact failing URL and timestamp.
3. Confirm whether issue is:
   - Login/auth
   - Permission/subscription
   - Data/content
   - DNS/domain
4. Reproduce with test account in local/staging first.

## 2) Fast Route Checks

- Public:
  - `/`
  - `/shop`
  - `/salons`
  - `/blog`
  - `/pricing`
- Auth:
  - `/login`
  - `/register`
- Customer:
  - `/dashboard`
- Vendor:
  - `/vendor/dashboard`
  - `/vendor/store-domain`
  - `/vendor/subscription/upgrade`
- Admin:
  - `/admin/dashboard`

If route mismatch is reported, verify whether user is being redirected because of auth or plan gating.

## 3) Role-Based Expected Behavior

Customer:

- Login should redirect to `/dashboard`.
- Customer should not see vendor/admin dashboards.

Vendor:

- Login should redirect to `/vendor/dashboard`.
- Vendor storefront should resolve on platform URL.
- Custom DNS mapping is Enterprise-only.

Admin:

- Login should redirect to `/admin/dashboard`.
- Admin should have access to vendor/subscription management.

## 4) Enterprise Domain Mapping Rules (Critical)

- Every vendor has platform access URL:
  - `/vendors/{vendor-slug}`
  - `{vendor-slug}.{base-domain}` when configured
- Custom DNS mapping is allowed only when:
  - Vendor has active Enterprise subscription
  - Domain has been verified from Store Domain page
- Non-Enterprise vendors should be directed to:
  - `/vendor/subscription/upgrade`

## 5) Quick Evidence Pack for Escalation

Collect before escalating:

- User email + role
- Affected URL
- Exact error text/screenshot
- Reproduction steps
- Whether issue reproduces with test account
- Current subscription plan (for vendor cases)

## 6) One-Page Troubleshooting Decisions

Login loops:

1. Confirm credentials.
2. Clear cookies/session.
3. Retry `/login`.
4. Verify user role and status in DB/admin.

Vendor domain access denied:

1. Check vendor plan tier.
2. If not Enterprise: expected redirect to upgrade.
3. If Enterprise: verify DNS target and verification status.

Admin page errors:

1. Confirm admin role assignment.
2. Check failing API endpoints from browser network logs.
3. Escalate with evidence pack if reproducible.

## 7) Linked v2 Docs

- `user-guide/v2/README.md`
- `user-guide/v2/ui-flows.md`
- `user-guide/v2/onboarding-customer.md`
- `user-guide/v2/onboarding-vendor.md`
- `user-guide/v2/onboarding-admin.md`
