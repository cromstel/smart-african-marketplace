# SWISH Payment Integration Documentation

## Overview

SWISH is a Swedish mobile payment system that enables instant payments using phone numbers. This integration supports customers from Nordic countries (Sweden, Norway, Denmark, Finland, and Iceland).

## Supported Countries

- **Sweden (SE)** - Primary market (SEK currency)
- **Norway (NO)**
- **Denmark (DK)**
- **Finland (FI)**
- **Iceland (IS)**

## Features

- QR code-based payments for mobile
- Phone number-based payments
- Real-time payment status polling
- Webhook support for asynchronous payment confirmation
- Automatic order completion on successful payment
- Loyalty points award on successful payment

## Configuration

### Admin Settings

The following settings must be configured in the admin panel (Settings > Payment):

| Setting | Required | Description |
|---------|----------|-------------|
| `swish_enabled` | Yes | Enable/disable SWISH payment |
| `swish_merchant_id` | Yes | Your SWISH merchant ID |
| `swish_environment` | Yes | `sandbox` or `production` |
| `swish_certificate_path` | No | Path to SSL certificate for production |
| `swish_certificate_password` | No | Certificate password |
| `swish_merchant_token` | No | Token for QR code generation |

### Setting Values

```php
// In database settings table
Setting::set('swish_enabled', true);
Setting::set('swish_merchant_id', 'YOUR_MERCHANT_ID');
Setting::set('swish_environment', 'sandbox'); // Change to 'production' for live
Setting::set('swish_merchant_token', 'YOUR_MERCHANT_TOKEN');
```

## API Integration

### Payment Flow

1. **Checkout**: Customer selects SWISH as payment method
2. **Phone Entry**: Customer enters Swedish mobile number
3. **QR Code**: QR code generated for easy mobile payment
4. **Polling**: Client polls for payment status every 3 seconds
5. **Confirmation**: Order updated on successful payment

### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/payment/swish/{order}` | Show SWISH payment page |
| POST | `/payment/swish/{order}` | Process SWISH payment |
| GET | `/payment/swish/{order}/status` | Check payment status |
| POST | `/webhooks/swish` | SWISH webhook callback |

### Response Codes

#### Payment Status Responses

```json
// Pending
{"status": "pending", "message": "Waiting for payment approval..."}

// Completed
{"status": "completed", "message": "Payment completed successfully!"}

// Failed
{"status": "failed", "message": "Payment failed. Please try again."}

// Cancelled
{"status": "cancelled", "message": "Payment was cancelled."}
```

## Webhook Integration

### Callback URL

```
https://yourdomain.com/webhooks/swish
```

### Webhook Payload

```json
{
  "id": "payment_request_id",
  "instructionUUID": "uuid",
  "amount": "100.00",
  "currency": "SEK",
  "status": "PAID"
}
```

### Status Mapping

| SWISH Status | Internal Status |
|--------------|-----------------|
| PAID | completed |
| DECLINED | failed |
| CANCELLED | cancelled |
| ERROR | failed |
| (other) | pending |

## Testing

### Sandbox Environment

1. Set `swish_environment` to `sandbox`
2. Use test phone numbers provided by SWISH
3. Use sandbox API endpoints

### Test Phone Numbers

Use the SWISH test numbers from their documentation:
- Sweden: +46 70 123 45 67 (typically)

### Testing Checklist

- [ ] SWISH option appears for Nordic country addresses
- [ ] QR code generates correctly
- [ ] Phone number validation works
- [ ] Payment polling returns correct status
- [ ] Order completes on successful payment
- [ ] Order fails on declined payment
- [ ] Webhook callback processes correctly

## Troubleshooting

### Common Issues

#### SWISH option not showing
- Check `swish_enabled` setting is true
- Verify customer address is in Nordic country
- Check billing address country if shipping not set

#### Payment not completing
- Verify merchant ID is correct
- Check API credentials
- Ensure webhook URL is accessible
- Check SWISH dashboard for errors

#### QR code not working
- Verify merchant token is set
- Check QR code API endpoint
- Ensure SSL certificate is valid (production)

### Logs

Check Laravel logs for:
- `/storage/logs/laravel.log`
- Look for "SWISH" tagged entries

### Debug Mode

Enable detailed logging by setting log level in `.env`:

```env
LOG_LEVEL=debug
```

## Security Considerations

1. **SSL/TLS**: Always use HTTPS in production
2. **Webhook Verification**: Verify webhook signatures
3. **Certificate Management**: Keep certificates updated
4. **Merchant ID Security**: Don't expose in client-side code

## Migration from Other Payment Providers

### Adding SWISH to Existing Installations

1. Run database migrations (if any new fields needed)
2. Configure SWISH settings in admin panel
3. Test with sandbox environment
4. Switch to production when ready

### Code Changes Required

The following files were modified:
- `app/Services/SwishService.php` - NEW
- `app/Http/Controllers/SwishPaymentController.php` - NEW
- `app/Http/Controllers/CheckoutController.php` - Modified
- `app/Http/Controllers/WebhookController.php` - Modified
- `routes/web.php` - Modified
- `resources/views/checkout/index.blade.php` - Modified
- `resources/views/payment/swish.blade.php` - NEW

## Support

For SWISH-specific issues:
- SWISH Developer Documentation: https://developer.swish.nu/
- SWISH Support: support@swish.nu

For platform-specific issues:
- Check Laravel logs
- Review payment gateway configuration
- Verify network connectivity

## Changelog

### Version 1.0.0
- Initial SWISH integration
- Support for Sweden, Norway, Denmark, Finland, Iceland
- QR code and phone number payment methods
- Real-time status polling
- Webhook support
