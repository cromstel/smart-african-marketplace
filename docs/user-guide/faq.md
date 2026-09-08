# FAQ

## Where is my store URL?

Vendors can use:

- `/vendors/{vendor-slug}`
- `http(s)://{vendor-slug}.{base-domain}`

Enterprise vendors may map their own custom domain.

## Why can I not access a vendor module?

Your current plan may not include that module. Upgrade subscription and retry.

## Why did payment complete but order status did not update?

Usually webhook delivery/signature issues. Ask support/admin to verify webhook logs.

## Can customers use mobile money?

Yes, where enabled through provider configuration.

## How do I reset my password?

Use `Forgot Password` on login page and follow the reset email flow.

## What if custom domain is not verifying?

- Confirm DNS points to correct target.
- Wait for DNS propagation.
- Retry verification in vendor dashboard.
