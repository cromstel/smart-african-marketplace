# Admin Onboarding Checklist

**Status: Current** — created 2026-11-22.

[Documentation Hub](../README.md) › User Documentation › Walkthroughs › Admin Onboarding

## Objective

Enable a new admin to manage vendors, subscriptions, and platform operations safely.

## UI Walkthrough

1. Login at `/auth/admin/login` with admin account
2. Confirm landing on `/admin/dashboard`
3. Verify primary admin widgets and navigation render
4. Open `Marketplace Manager` and validate vendors, services, and subscription package sections
5. Validate access to system-level settings sections

![Admin dashboard](../assets/screenshots/walkthroughs/admin-dashboard.avif)

## Day-0 Checklist

- [ ] Admin account has correct role permissions
- [ ] Dashboard loads without auth redirects
- [ ] `Marketplace Manager` opens and shows vendor/service/subscription links
- [ ] Vendor management and subscription plan pages are accessible
- [ ] Core reports/widgets render successfully

## Governance Checklist

- [ ] Enterprise-only domain policy is enforced for vendors
- [ ] Subscription downgrade behavior is validated
- [ ] Audit trail/log review path is defined
- [ ] Backup and incident response contacts are documented
