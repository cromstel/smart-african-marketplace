# Subscriptions and Plans

## Plan Types

The platform includes multiple tiers such as:

- Free Trial
- Starter
- Professional
- Business
- Enterprise

## What Plans Control

- Access to vendor modules
- Operational limits (products/orders)
- Feature availability (for example, advanced modules)

## Upgrade Path

Vendors can upgrade from:

- `/vendor/subscription/upgrade`

## Trial and Renewal

- Trial status and expiration are tracked per subscription.
- Renewal processes run via scheduled command jobs.
- Webhook updates from payment providers keep status current.

## Enterprise Benefits

- Includes custom domain mapping for storefront access.

## Troubleshooting

- If a module is blocked, verify active subscription and plan tier.
- If renewal fails, review payment and webhook logs.
