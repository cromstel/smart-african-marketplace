# Enterprise Custom Domains

Custom domain mapping is available only for vendors with an active Enterprise subscription.

## Default Store URLs

All vendors can use:

- `/vendors/{vendor-slug}`
- `http(s)://{vendor-slug}.{base-domain}`

## Enterprise Mapping Flow

1. Open Vendor Dashboard.
2. Go to `Store Domain`.
3. Enter custom domain (example: `store.example.com`).
4. Configure DNS to point to platform target.
5. Run `Verify DNS` in dashboard.
6. Share verified domain with customers.

## Required DNS

Use your platform DNS target from environment config:

- `VENDOR_STORE_DOMAIN_TARGET`

Typical setups:

- CNAME record from custom subdomain to platform target.
- A/AAAA records if infrastructure requires direct IP mapping.

## Access Rules

- Non-enterprise vendors cannot save or verify custom domains.
- If an enterprise vendor is downgraded, custom domain storefront access is disabled.

## Environment Settings

- `VENDOR_STORE_SCHEME`
- `VENDOR_STORE_BASE_DOMAIN`
- `VENDOR_STORE_DOMAIN_TARGET`
