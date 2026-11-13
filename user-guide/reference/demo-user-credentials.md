### Demo Credentials (Local/Test Only)
## ===================================
These accounts are intended for local and test environments only.

Set `DEMO_USER_PASSWORD` before seeding if you need a known password locally:

```
DEMO_USER_PASSWORD=your-local-password
```

In automated tests, seeded demo accounts use `password`.

## Available Demo Accounts
=========================

Set `DEMO_USER_PASSWORD` in your `.env` before seeding to use a known password.
All accounts use the same password as defined in the `DEMO_USER_PASSWORD` env var.

- admin@africanmarket.com
- vendor@africanmarket.com
- salon@africanmarket.com
- customer@africanmarket.com
- customer2@africanmarket.com

## Login Portals
=================

2. Admin Access to Edit Products - Complete
Site admins can manage products through the admin dashboard:

Product Management: /admin/products - View, create, edit, delete products
Full CRUD: Create new products, upload images, set prices, manage stock
Category Management: Assign products to categories
Featured Toggle: Mark products as featured to show on home page
Vendor Assignment: Products linked to approved vendors

3. Separate Login URLs for Different User Types - Complete
Three Login Flows:

# Admin Login - /admin/login

- Dedicated admin portal login page
- Validates admin role access
- Redirects non-admins with error message
- Direct access to admin dashboard upon successful login
- Regular User Login - /login

# Customer and vendor login
==========================
Role-based redirect after authentication:
- Admin users → Admin Dashboard (/admin/dashboard)
- Vendor users → Vendor Dashboard (/vendor/dashboard)
- Customer users → Customer Dashboard (/dashboard)
