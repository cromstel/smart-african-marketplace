# Troubleshooting

## Login Issues

- Verify email/password.
- Reset password if needed.
- Ensure account is active.

## Vendor Access Issues

- Confirm user has vendor role.
- Confirm subscription is active.
- Check module access based on plan tier.

## Storefront Issues

- Check vendor status is approved and active.
- Confirm vendor slug URL works.
- For subdomains/custom domains, verify host and DNS configuration.

## Payment Issues

- Verify gateway keys.
- Confirm webhook secrets and endpoint reachability.
- Check server logs for webhook signature failures.

## Media and Upload Issues

- Confirm storage link exists (`php artisan storage:link`).
- Verify file permissions.
- Check upload size limits in PHP and app config.

## Performance Notes

- Clear caches after config changes:
  - `php artisan config:clear`
  - `php artisan route:clear`
  - `php artisan view:clear`

## When to Contact Support

- Repeated payment failures despite valid cards.
- Persistent 403 module-access errors after confirmed upgrade.
- Domain mapping still failing after DNS propagation.
