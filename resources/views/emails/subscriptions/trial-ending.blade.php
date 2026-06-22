<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Trial is Ending Soon</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f7fafc; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f7fafc; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%); padding: 40px; text-align: center;">
                            <h2 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">Trial Ending Soon</h2>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 0 0 20px 0;">
                                Hi {{ $subscription->user->name }},
                            </p>
                            
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 0 0 20px 0;">
                                Your <strong>{{ $subscription->plan->name }}</strong> free trial will end in {{ $daysRemaining }} days on <strong>{{ $subscription->trial_ends_at->format('F j, Y') }}</strong>.
                            </p>
                            
                            <p style="color: #1a202c; font-size: 16px; line-height: 24px; margin: 0 0 30px 0;">
                                To continue enjoying all the benefits of your plan, no action is needed. Your subscription will automatically convert to a paid plan at <strong>${{ number_format($subscription->plan->price, 2) }}/{{ $subscription->plan->billing_cycle }}</strong>.
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <a href="{{ route('subscriptions.my') }}" style="background-color: #ea580c; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 6px; display: inline-block; font-weight: 600; font-size: 16px;">Manage Subscription</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="background-color: #fef3c7; border-left: 4px solid: #f59e0b; padding: 16px; margin: 30px 0;">
                                <p style="color: #92400e; font-size: 14px; line-height: 20px; margin: 0;">
                                    <strong>Want to cancel?</strong> No problem! You can cancel anytime before {{ $subscription->trial_ends_at->format('F j, Y') }} and you won't be charged.
                                </p>
                            </div>
                            
                            <p style="color: #718096; font-size: 14px; line-height: 20px; margin: 30px 0 0 0;">
                                If you have any questions, please contact our support team.
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

