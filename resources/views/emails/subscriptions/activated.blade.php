<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Activated</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f7fafc; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f7fafc; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 40px; text-align: center;">
                            <div style="width: 60px; height: 60px; background-color: rgba(255,255,255,0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                                <span style="font-size: 32px;">✓</span>
                            </div>
                            <h2 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">Subscription Activated!</h2>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 0 0 20px 0;">
                                Hi {{ $subscription->user->name }},
                            </p>
                            
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 0 0 20px 0;">
                                Great news! Your <strong>{{ $subscription->plan->name }}</strong> subscription is now active.
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 30px 0;">
                                <tr>
                                    <td>
                                        <p style="color: #4a5568; font-size: 14px; margin: 0 0 10px 0;">Plan Details:</p>
                                        <p style="color: #1a202c; font-size: 18px; font-weight: bold; margin: 0 0 5px 0;">{{ $subscription->plan->name }}</p>
                                        <p style="color: #4a5568; font-size: 16px; margin: 0 0 15px 0;">${{ number_format($subscription->plan->price, 2) }}/{{ $subscription->plan->billing_cycle }}</p>
                                        <p style="color: #4a5568; font-size: 14px; margin: 0;">Next billing date: <strong>{{ $subscription->current_period_end->format('F j, Y') }}</strong></p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 0 0 30px 0;">
                                You now have access to:
                            </p>
                            
                            <ul style="color: #1a202c; font-size: 16px; line-height: 28px; margin: 0 0 30px 0; padding-left: 20px;">
                                <li>{{ $subscription->plan->max_products === -1 ? 'Unlimited' : $subscription->plan->max_products }} Products</li>
                                <li>{{ $subscription->plan->max_orders === -1 ? 'Unlimited' : $subscription->plan->max_orders }} Orders per month</li>
                                <li>{{ $subscription->plan->commission_rate }}% Commission Rate</li>
                                @if($subscription->plan->features)
                                    @foreach($subscription->plan->features as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                @endif
                            </ul>
                            
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{{ route('subscriptions.my') }}" style="background-color: #ea580c; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; display: inline-block; font-weight: 600; font-size: 16px;">View Subscription</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #718096; font-size: 14px; line-height: 20px; margin: 30px 0 0 0;">
                                Thank you for choosing us! If you have any questions, our support team is here to help.
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

