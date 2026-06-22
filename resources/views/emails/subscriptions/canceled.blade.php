<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Canceled</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f7fafc; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f7fafc; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); padding: 40px; text-align: center;">
                            <h2 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">Subscription Canceled</h2>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 0 0 20px 0;">
                                Hi {{ $subscription->user->name }},
                            </p>
                            
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 0 0 20px 0;">
                                We're sorry to see you go! Your <strong>{{ $subscription->plan->name }}</strong> subscription has been canceled.
                            </p>
                            
                            @if($subscription->current_period_end && $subscription->current_period_end->isFuture())
                                <div style="background-color: #dbeafe; border-left: 4px solid #3b82f6; padding: 16px; margin: 30px 0;">
                                    <p style="color: #1e40af; font-size: 14px; line-height: 20px; margin: 0;">
                                        <strong>You still have access!</strong> Your subscription will remain active until <strong>{{ $subscription->current_period_end->format('F j, Y') }}</strong>.
                                    </p>
                                </div>
                            @endif
                            
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 30px 0 20px 0;">
                                We'd love to have you back! Here's what you'll be missing:
                            </p>
                            
                            <ul style="color: #1a202c; font-size: 16px; line-height: 28px; margin: 0 0 30px 0; padding-left: 20px;">
                                <li>{{ $subscription->plan->max_products === -1 ? 'Unlimited' : $subscription->plan->max_products }} Products</li>
                                <li>{{ $subscription->plan->max_orders === -1 ? 'Unlimited' : $subscription->plan->max_orders }} Orders per month</li>
                                <li>Only {{ $subscription->plan->commission_rate }}% Commission Rate</li>
                                @if($subscription->plan->features)
                                    @foreach($subscription->plan->features as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                @endif
                            </ul>
                            
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{{ route('subscriptions.index') }}" style="background-color: #ea580c; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; display: inline-block; font-weight: 600; font-size: 16px;">Resubscribe Now</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #718096; font-size: 14px; line-height: 20px; margin: 30px 0 0 0;">
                                Have feedback for us? We'd love to hear how we can improve. <a href="{{ route('contact.index') }}" style="color: #ea580c; text-decoration: none;">Contact us</a>
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

