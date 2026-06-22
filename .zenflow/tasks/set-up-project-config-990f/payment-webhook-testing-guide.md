# Payment Webhook Testing Guide
**Created**: 2026-02-18  
**Purpose**: Comprehensive guide for testing Stripe and Paystack webhook integrations

---

## Overview

The African Market platform has complete webhook implementations for both Stripe and Paystack. This guide provides step-by-step instructions for testing these webhooks with sandbox accounts.

---

## Webhook Implementations

### Files Involved
- **Controller**: `app/Http/Controllers/WebhookController.php`
- **Middleware**: `app/Http/Middleware/VerifyCsrfToken.php` (CSRF exemption for webhooks)
- **Routes**: `routes/web.php` (webhook endpoints)

### Webhook Endpoints
- **Stripe**: `POST /webhooks/stripe`
- **Paystack**: `POST /webhooks/paystack`

---

## Stripe Webhook Testing

### Prerequisites
1. Stripe account (sandbox/test mode)
2. Stripe CLI installed (for local testing)
3. Test API keys configured in Settings model

### Setup Steps

#### 1. Install Stripe CLI
```bash
# Windows (using Scoop)
scoop bucket add stripe https://github.com/stripe/scoop-stripe-cli.git
scoop install stripe

# Mac (using Homebrew)
brew install stripe/stripe-cli/stripe

# Or download from: https://stripe.com/docs/stripe-cli
```

#### 2. Login to Stripe
```bash
stripe login
```

#### 3. Forward Webhooks to Local Server
```bash
# Start Laravel development server
php artisan serve

# In another terminal, forward webhooks
stripe listen --forward-to localhost:8000/webhooks/stripe
```

This will output a webhook signing secret like:
```
whsec_xxxxxxxxxxxxxxxxxxxxx
```

#### 4. Configure Webhook Secret
Add the webhook secret to your database via Settings model or admin panel:
- Key: `stripe_webhook_secret`
- Value: `whsec_xxxxxxxxxxxxxxxxxxxxx`

#### 5. Test Events

**Trigger Payment Success:**
```bash
stripe trigger payment_intent.succeeded
```

**Trigger Payment Failure:**
```bash
stripe trigger payment_intent.payment_failed
```

**Trigger Charge Success:**
```bash
stripe trigger charge.succeeded
```

**Trigger Charge Failure:**
```bash
stripe trigger charge.failed
```

### Verification Checklist

- [ ] **Webhook Received**: Check Laravel logs for "Stripe payment confirmed"
- [ ] **Order Updated**: Verify order payment_status changed to 'paid'
- [ ] **Payment Record Created**: Check payments table for new record
- [ ] **Loyalty Points Awarded**: Verify loyalty_points table has new entry
- [ ] **Email Sent**: Check queued jobs for OrderPlaced email
- [ ] **Failed Payment Handling**: Verify order marked as 'failed' on payment failure
- [ ] **Signature Verification**: Test with invalid signature (should return 400)
- [ ] **Idempotency**: Send same webhook twice (should not duplicate)

### Production Deployment

1. **Configure Production Webhook:**
   - Go to Stripe Dashboard → Developers → Webhooks
   - Add endpoint: `https://yourdomain.com/webhooks/stripe`
   - Select events:
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`
     - `charge.succeeded`
     - `charge.failed`

2. **Copy Signing Secret:**
   - After creating webhook, copy the signing secret
   - Add to Settings model: `stripe_webhook_secret`

3. **Test in Production:**
   - Use Stripe Dashboard → Developers → Webhooks → Send test webhook
   - Verify event appears in webhook logs

---

## Paystack Webhook Testing

### Prerequisites
1. Paystack account (sandbox/test mode)
2. Test API keys configured in Settings model

### Setup Steps

#### 1. Configure Webhook Secret
Add your Paystack secret key to Settings model:
- Key: `paystack_secret_key`
- Value: `sk_test_xxxxxxxxxxxxx`

#### 2. Expose Local Server (Development)
For local testing, you need to expose your local server to the internet:

**Option A: Using ngrok**
```bash
# Install ngrok: https://ngrok.com/download
ngrok http 8000
```

This gives you a URL like: `https://abc123.ngrok.io`

**Option B: Using Laravel Valet (Mac only)**
```bash
valet share
```

#### 3. Configure Paystack Webhook
1. Go to Paystack Dashboard → Settings → Webhooks
2. Add webhook URL: `https://your-ngrok-url.ngrok.io/webhooks/paystack`
3. Copy the webhook URL

#### 4. Test with Paystack Test Cards

**Test Successful Payment:**
- Card Number: `5078 5078 5078 5078 12`
- Expiry: Any future date
- CVV: `123`

**Test Failed Payment:**
- Card Number: `5078 5078 5078 5078 03`
- Expiry: Any future date
- CVV: `123`

### Manual Webhook Testing

You can manually trigger webhooks using curl:

```bash
# Test charge.success event
curl -X POST http://localhost:8000/webhooks/paystack \
  -H "Content-Type: application/json" \
  -H "X-Paystack-Signature: YOUR_COMPUTED_SIGNATURE" \
  -d '{
    "event": "charge.success",
    "data": {
      "reference": "TEST_REF_123",
      "amount": 5000,
      "currency": "USD",
      "metadata": {
        "order_id": 1
      }
    }
  }'
```

**Note**: You must compute the HMAC SHA-512 signature of the payload using your secret key.

### Verification Checklist

- [ ] **Webhook Received**: Check Laravel logs for "Paystack payment confirmed"
- [ ] **Order Updated**: Verify order payment_status changed to 'paid'
- [ ] **Payment Record Created**: Check payments table for new record
- [ ] **Loyalty Points Awarded**: Verify loyalty_points table has new entry
- [ ] **Email Sent**: Check queued jobs for OrderPlaced email
- [ ] **Failed Payment Handling**: Verify order marked as 'failed' on payment failure
- [ ] **Signature Verification**: Test with invalid signature (should return 400)
- [ ] **Idempotency**: Send same webhook twice (should not duplicate)

### Production Deployment

1. **Update Webhook URL:**
   - Go to Paystack Dashboard → Settings → Webhooks
   - Update URL to: `https://yourdomain.com/webhooks/paystack`

2. **Verify Secret Key:**
   - Ensure production secret key is in Settings model
   - Key: `paystack_secret_key`

3. **Test in Production:**
   - Make test payment with Paystack test card
   - Verify webhook appears in Paystack Dashboard → Developers → Logs

---

## Common Issues & Troubleshooting

### Issue: Webhook Not Received

**Symptoms**: No logs in Laravel, webhook not triggered

**Solutions**:
- Verify Laravel server is running
- Check firewall/network settings
- For local testing, ensure ngrok/tunnel is active
- Verify webhook URL is correct in payment provider dashboard

### Issue: Signature Verification Failed

**Symptoms**: Webhook returns 400, "Invalid signature" in logs

**Solutions**:
- Verify webhook secret matches in Settings model
- For Stripe: Use correct signing secret from Stripe CLI or Dashboard
- For Paystack: Use correct secret key (starts with `sk_`)
- Check payload is sent exactly as received (no modifications)

### Issue: Order Not Updated

**Symptoms**: Webhook received but order status unchanged

**Solutions**:
- Check order_id exists in metadata
- Verify order exists in database
- Check for database transaction rollbacks in logs
- Verify LoyaltyPointsService is working correctly

### Issue: Duplicate Processing

**Symptoms**: Same webhook processed twice, duplicate payments

**Solutions**:
- Webhook controller has idempotency checks
- Verify order.payment_status check is working
- Check webhook provider for duplicate sends

### Issue: Emails Not Sent

**Symptoms**: Webhook processed but no email received

**Solutions**:
- Verify queue is running: `php artisan queue:work`
- Check failed_jobs table for errors
- Verify SMTP settings in .env
- Check Laravel logs for email errors

---

## Testing Script

Run this artisan command to test webhook functionality:

```bash
# Create test order
php artisan tinker
> $order = Order::find(1);
> $order->update(['payment_status' => 'pending']);

# Manually trigger webhook handler
> app(WebhookController::class)->handleStripeChargeSucceeded((object)[
    'id' => 'ch_test_123',
    'amount' => 5000,
    'currency' => 'usd',
    'metadata' => (object)['order_id' => 1]
  ]);

# Verify order updated
> Order::find(1)->payment_status; // Should be 'paid'
```

---

## Monitoring & Logging

### Key Log Messages

**Stripe Success**:
```
Stripe payment confirmed for order: {order_id}
```

**Paystack Success**:
```
Paystack payment confirmed for order: {order_id}
```

**Failures**:
```
Stripe webhook invalid signature
Paystack webhook invalid signature
Order not found: {order_id}
Error processing Stripe charge succeeded: {error}
```

### Database Checks

**Verify Payment Record:**
```sql
SELECT * FROM payments WHERE order_id = 1 ORDER BY created_at DESC LIMIT 1;
```

**Verify Order Status:**
```sql
SELECT id, payment_status, paid_at FROM orders WHERE id = 1;
```

**Verify Loyalty Points:**
```sql
SELECT * FROM loyalty_points WHERE order_id = 1;
```

---

## Production Readiness Checklist

### Before Going Live

- [ ] Test all webhook events in sandbox
- [ ] Verify signature verification works
- [ ] Test idempotency (duplicate webhook handling)
- [ ] Confirm email notifications send correctly
- [ ] Verify loyalty points are awarded
- [ ] Test failed payment handling
- [ ] Configure production webhook URLs
- [ ] Add production webhook secrets to Settings
- [ ] Set up webhook monitoring/alerts
- [ ] Document webhook retry logic (Stripe retries automatically)
- [ ] Set up error tracking (Sentry, Bugsnag, etc.)

### Post-Launch Monitoring

- Monitor webhook delivery rate (should be close to 100%)
- Check for signature verification failures
- Monitor failed_jobs queue
- Track payment success/failure rates
- Review webhook logs weekly

---

## Additional Resources

### Documentation Links
- [Stripe Webhooks Guide](https://stripe.com/docs/webhooks)
- [Stripe CLI Documentation](https://stripe.com/docs/stripe-cli)
- [Paystack Webhooks Guide](https://paystack.com/docs/payments/webhooks/)
- [Laravel Queue Documentation](https://laravel.com/docs/10.x/queues)

### Support
- Stripe Support: https://support.stripe.com
- Paystack Support: https://paystack.com/contact
- Laravel Community: https://laravel.io

---

## Summary

Both Stripe and Paystack webhook implementations are **production-ready** with:

✅ Signature verification  
✅ Idempotency checks  
✅ Database transactions  
✅ Comprehensive error logging  
✅ Automatic order updates  
✅ Loyalty points integration  
✅ Email notifications  
✅ Failed payment handling  

Only remaining task: **Manual end-to-end testing with sandbox accounts**
