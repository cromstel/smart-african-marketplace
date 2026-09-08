## Command Line:

php artisan maintenance:enable "Scheduled maintenance"
php artisan maintenance:disable
php artisan maintenance:status

## Quick Visual Regression Checklist

- Home, Shop, Blog: logo alignment and favicon loads; confirm only one favicon set in the head.
- Auth screens: Login, Register, Reset Password, Verify Email, Admin Login; logo position and size consistent.
- Dashboards: User, Admin, Vendor; logo position and size consistent.
- Vendor storefront: global branding logo + favicon (no theme override).
- Error pages: 404 and maintenance pages show favicon and no duplicate favicon tags.
