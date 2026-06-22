# UI Flows (v2)

## Public Discovery Flow

Goal: Help new users discover products, services, and pricing before sign-up.

1. Open Home page: `/`
2. Open product catalog: `/shop`
3. Open salon services: `/salons`
4. Open editorial content: `/blog`
5. Review plans: `/pricing`
6. Continue to login or registration: `/login` or `/register`

Screenshots:

![Home](../assets/screenshots/v2/home.png)
![Shop](../assets/screenshots/v2/shop.png)
![Salons](../assets/screenshots/v2/salons.png)
![Blog](../assets/screenshots/v2/blog.png)
![Pricing](../assets/screenshots/v2/pricing.png)
![Login](../assets/screenshots/v2/login.png)
![Register](../assets/screenshots/v2/register.png)

## Customer Login to Dashboard Flow

Goal: Get a customer authenticated and into account workspace.

1. Open `/login`
2. Enter customer credentials
3. Click `Sign In`
4. Confirm redirect to `/dashboard`
5. Use dashboard links to continue with orders, profile, and shopping actions

Screenshots:

![Customer Post Login](../assets/screenshots/v2/customer-post-login.png)
![Customer Dashboard](../assets/screenshots/v2/customer-dashboard.png)

## Vendor Login to Operations Flow

Goal: Move vendor from login to active store operations.

1. Open `/login`
2. Enter vendor credentials
3. Click `Sign In`
4. Confirm redirect to `/vendor/dashboard`
5. Continue to vendor modules (products, orders, withdrawals)

Screenshots:

![Vendor Post Login](../assets/screenshots/v2/vendor-post-login.png)
![Vendor Dashboard](../assets/screenshots/v2/vendor-dashboard.png)

## Enterprise Domain Mapping Flow (Vendor)

Goal: Assign vendor-facing URL and optionally map custom DNS.

1. Vendor opens `/vendor/store-domain`
2. Platform checks subscription entitlement
3. If Enterprise + active subscription:
   - Show domain mapping form
   - Vendor enters custom domain
   - Vendor configures DNS records
   - Vendor runs verification
4. If not Enterprise:
   - Redirect vendor to `/vendor/subscription/upgrade`
   - Vendor upgrades plan first

Screenshots:

![Store Domain Access](../assets/screenshots/v2/vendor-store-domain.png)
![Upgrade Subscription](../assets/screenshots/v2/vendor-subscription-upgrade.png)

## Admin Control Flow

Goal: Move admin from login to central control panel.

1. Open `/login`
2. Enter admin credentials
3. Click `Sign In`
4. Confirm redirect to `/admin/dashboard`
5. Use `Marketplace Manager` for vendors, services, subscriptions, finance, and shipping settings

Screenshots:

![Admin Post Login](../assets/screenshots/v2/admin-post-login.png)
![Admin Dashboard](../assets/screenshots/v2/admin-dashboard.png)
