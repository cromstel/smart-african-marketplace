# Payments and Checkout

## Supported Payment Flows

- Stripe
- Paystack
- Optional payment methods enabled by platform configuration

## Customer Checkout Flow

1. Add items to cart.
2. Start checkout.
3. Choose payment method.
4. Complete payment confirmation.
5. Receive order confirmation.

## Webhooks

The app uses payment-provider webhooks for status updates.

- Stripe webhook endpoint: `/webhooks/stripe`
- Paystack webhook endpoint: `/webhooks/paystack`

## Common Issues

- Payment succeeded but order pending: check webhook delivery and signatures.
- Duplicate callback concerns: ensure idempotent payment processing remains enabled.

## Security Notes

- Never expose secret keys in client code or repos.
- Verify webhook signatures.
- Rotate credentials after any suspected leak.
