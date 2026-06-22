# Localhost Testing on Windows with Standalone Apache

This guide is for local testing only.

## Recommended Local Env Values

In `.env.local`:

- `APP_URL=http://localhost`
- `VENDOR_STORE_SCHEME=http`
- `VENDOR_STORE_BASE_DOMAIN=localhost`
- `VENDOR_STORE_DOMAIN_TARGET=localhost`

## Apache Notes

1. Point Apache `DocumentRoot` to the project `public` folder.
2. Enable rewrite rules.
3. Restart Apache after virtual host changes.

## Test Vendor Subdomains

Examples:

- `http://african-delights-store.localhost`
- `http://african-market-official-store.localhost`

## Test Custom Domains (Enterprise)

1. Add hosts entry in Windows (example):
   - `127.0.0.1 store.vendor.test`
2. Add Apache virtual host with `ServerName store.vendor.test`.
3. Open Vendor Dashboard `Store Domain`.
4. Save domain and click verify.

## Useful Commands

- `php artisan migrate`
- `php artisan storage:link`
- `php artisan config:clear`
- `php artisan test`
