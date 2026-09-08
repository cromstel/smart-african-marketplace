# Vendor Guide

## Vendor Dashboard

Use `/vendor/dashboard` to manage your business.

## Manage Products

- Create, edit, publish, and archive products.
- Manage inventory and variants.
- Set categories, tags, and pricing.

## Manage Orders

- View order queue.
- Update order statuses.
- Add notes and shipment tracking where available.

## Storefront Access

Each vendor has a unique storefront URL:

- Path-based: `/vendors/{vendor-slug}`
- Subdomain-based: `http(s)://{vendor-slug}.{base-domain}`

Base domain is controlled by environment settings.

## Store Customization

- Configure theme, colors, fonts, and branding.
- Add store banner and logo.

## Subscription and Modules

- Your plan controls module access.
- Upgrade from `/vendor/subscription/upgrade`.
- Enterprise plan includes custom domain mapping.

## Custom Domain (Enterprise Only)

- Open `/vendor/store-domain`.
- Add your domain.
- Configure DNS target.
- Verify the domain in dashboard.

See [Enterprise Custom Domains](enterprise-custom-domains.md).
