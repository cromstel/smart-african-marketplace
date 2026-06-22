<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Ending Soon</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(to right, #f59e0b, #d97706); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .warning-box { background: #fef3c7; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #f59e0b; }
        .plan-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #e5e7eb; }
        .button { display: inline-block; background: linear-gradient(to right, #ea580c, #dc2626); color: white !important; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 5px; }
        .button-secondary { background: #6b7280; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0;">⏰ Your Trial is Ending Soon</h2>
        <p style="margin: 10px 0 0 0;">{{ $daysLeft }} days remaining</p>
    </div>

    <div class="content">
        <p>Hi {{ $subscription->user->name }},</p>
        
        <p>Your {{ $subscription->plan->trial_days }}-day free trial of the <strong>{{ $subscription->plan->name }}</strong> plan is ending soon.</p>

        <div class="warning-box">
            <h3 style="margin-top: 0;">📅 Trial End Date</h3>
            <p style="font-size: 18px; margin: 5px 0;"><strong>{{ $subscription->trial_ends_at->format('F j, Y') }}</strong></p>
            <p style="margin-bottom: 0;">You have <strong>{{ $daysLeft }} day{{ $daysLeft != 1 ? 's' : '' }}</strong> left in your trial period.</p>
        </div>

        <div class="plan-box">
            <h3 style="margin-top: 0;">Your Current Plan</h3>
            <p style="font-size: 20px; font-weight: bold; color: #ea580c; margin: 10px 0;">
                {{ $subscription->plan->name }} - {{ $subscription->plan->currency }} {{ number_format($subscription->plan->price, 2) }}/{{ $subscription->plan->billing_cycle }}
            </p>
            <p style="color: #6b7280; margin: 0;">
                @if($subscription->payment_provider)
                    Your subscription will automatically renew using your saved payment method.
                @else
                    Please add a payment method to continue after trial.
                @endif
            </p>
        </div>

        <h3>What happens next?</h3>
        <ul style="color: #4b5563;">
            @if($subscription->payment_provider)
                <li>On {{ $subscription->trial_ends_at->format('F j') }}, your paid subscription will begin</li>
                <li>You'll be charged {{ $subscription->plan->currency }} {{ number_format($subscription->plan->price, 2) }}</li>
                <li>All features will continue uninterrupted</li>
            @else
                <li>Add a payment method to continue after trial</li>
                <li>Without payment, your account will be downgraded</li>
                <li>Your data will be preserved for 30 days</li>
            @endif
        </ul>

        <div style="text-align: center; margin: 30px 0;">
            @if(!$subscription->payment_provider)
                <a href="{{ config('app.url') }}/my-subscription" class="button">Add Payment Method</a>
            @endif
            <a href="{{ config('app.url') }}/pricing" class="button">View All Plans</a>
            <br>
            <a href="{{ config('app.url') }}/my-subscription" class="button button-secondary">Cancel Subscription</a>
        </div>

        <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
            You can cancel anytime before {{ $subscription->trial_ends_at->format('F j') }} and you won't be charged.
        </p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>This is an automated email, please do not reply.</p>
    </div>
</body>
</html>

