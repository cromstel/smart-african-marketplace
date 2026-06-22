<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Subscription Canceled</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(to right, #6b7280, #4b5563); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .info-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #e5e7eb; }
        .highlight { background: #dbeafe; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #3b82f6; }
        .button { display: inline-block; background: linear-gradient(to right, #ea580c, #dc2626); color: white !important; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .button-secondary { background: #6b7280; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0;">Subscription Canceled</h2>
        <p style="margin: 10px 0 0 0;">We're sorry to see you go</p>
    </div>

    <div class="content">
        <p>Hi {{ $subscription->user->name }},</p>
        
        <p>Your subscription to the <strong>{{ $subscription->plan->name }}</strong> plan has been canceled.</p>

        @if($subscription->cancel_at_period_end && $subscription->current_period_end)
            <div class="highlight">
                <h3 style="margin-top: 0;">📅 Access Until {{ $subscription->current_period_end->format('F j, Y') }}</h3>
                <p style="margin-bottom: 0;">
                    You still have access to all plan features until the end of your current billing period. 
                    After that, your account will be downgraded.
                </p>
            </div>
        @else
            <div class="highlight">
                <h3 style="margin-top: 0;">🔒 Access Ended</h3>
                <p style="margin-bottom: 0;">Your subscription access has ended immediately.</p>
            </div>
        @endif

        <div class="info-box">
            <h3 style="margin-top: 0;">Subscription Details</h3>
            <p style="margin: 5px 0;"><strong>Plan:</strong> {{ $subscription->plan->name }}</p>
            <p style="margin: 5px 0;"><strong>Price:</strong> {{ $subscription->plan->currency }} {{ number_format($subscription->plan->price, 2) }}/{{ $subscription->plan->billing_cycle }}</p>
            @if($subscription->canceled_at)
                <p style="margin: 5px 0;"><strong>Canceled on:</strong> {{ $subscription->canceled_at->format('F j, Y') }}</p>
            @endif
            @if($subscription->cancel_at_period_end && $subscription->current_period_end)
                <p style="margin: 5px 0;"><strong>Access until:</strong> {{ $subscription->current_period_end->format('F j, Y') }}</p>
            @endif
        </div>

        <h3>What happens now?</h3>
        <ul style="color: #4b5563;">
            <li>You won't be charged anymore</li>
            <li>Your data will be preserved for 90 days</li>
            <li>You can reactivate anytime</li>
            <li>Free plan features remain available</li>
        </ul>

        <div style="background: #fffbeb; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0;">📢 We'd Love Your Feedback</h3>
            <p style="margin-bottom: 10px;">Would you mind telling us why you canceled? Your feedback helps us improve.</p>
            <a href="{{ config('app.url') }}/feedback?reason=cancellation" style="color: #ea580c; text-decoration: none; font-weight: bold;">Share Feedback →</a>
        </div>

        @if($subscription->cancel_at_period_end)
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/my-subscription" class="button">Resume Subscription</a>
            </div>
        @else
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/pricing" class="button">View Plans</a>
            </div>
        @endif

        <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
            Thank you for being a valued member. We hope to see you back soon!
        </p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>This is an automated email, please do not reply.</p>
    </div>
</body>
</html>

