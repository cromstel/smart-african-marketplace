<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f7fafc; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f7fafc; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); padding: 40px; text-align: center;">
                            <div style="width: 60px; height: 60px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                                <span style="color: #ffffff; font-size: 32px;">!</span>
                            </div>
                            <h2 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">Payment Failed</h2>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 0 0 20px 0;">
                                Hi {{ $subscription->user->name }},
                            </p>
                            
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 0 0 20px 0;">
                                We had trouble processing your payment for your <strong>{{ $subscription->plan->name }}</strong> subscription.
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 16px; margin: 30px 0;">
                                <tr>
                                    <td>
                                        <p style="color: #991b1b; font-size: 14px; line-height: 20px; margin: 0 0 10px 0;">
                                            <strong>Amount Due:</strong> ${{ number_format($subscription->plan->price, 2) }}
                                        </p>
                                        <p style="color: #991b1b; font-size: 14px; line-height: 20px; margin: 0;">
                                            <strong>Reason:</strong> {{ $failureReason ?? 'Payment method declined' }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 30px 0 20px 0;">
                                To avoid service interruption, please update your payment method as soon as possible.
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{{ route('subscriptions.my') }}" style="background-color: #dc2626; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; display: inline-block; font-weight: 600; font-size: 16px;">Update Payment Method</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; margin: 30px 0;">
                                <p style="color: #92400e; font-size: 14px; line-height: 20px; margin: 0;">
                                    <strong>What happens next?</strong> We'll automatically retry the payment in a few days. If payment continues to fail, your subscription may be suspended.
                                </p>
                            </div>
                            
                            <p style="color: #718096; font-size: 14px; line-height: 20px; margin: 30px 0 0 0;">
                                Need help? <a href="{{ route('contact.index') }}" style="color: #ea580c; text-decoration: none;">Contact our support team</a>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="background-color: #f7fafc; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="color: #718096; font-size: 12px; line-height: 18px; margin: 0;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

