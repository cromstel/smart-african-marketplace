<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Subscription Created</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(to right, #ea580c, #dc2626); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .plan-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 2px solid #ea580c; }
        .highlight { background: #fef3c7; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #f59e0b; }
        .feature-list { list-style: none; padding: 0; }
        .feature-list li { padding: 8px 0; padding-left: 25px; position: relative; }
        .feature-list li:before { content: "✓"; position: absolute; left: 0; color: #10b981; font-weight: bold; }
        .button { display: inline-block; background: linear-gradient(to right, #ea580c, #dc2626); color: white !important; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0;">🎉 Welcome to {{ $subscription->plan->name }}!</h2>
        <p style="margin: 10px 0 0 0;">Your free trial has started</p>
    </div>

    <div class="content">
        <p>Hi {{ $subscription->user->name }},</p>
        
        <p>Congratulations! Your subscription to the <strong>{{ $subscription->plan->name }}</strong> plan has been created successfully.</p>

        <div class="highlight">
            <h3 style="margin-top: 0;">🎁 Your {{ $subscription->plan->trial_days }}-Day Free Trial Started!</h3>
            <p style="margin-bottom: 0;">Trial ends on <strong>{{ $subscription->trial_ends_at->format('F j, Y') }}</strong>. You won't be charged until then.</p>
        </div>

        <div class="plan-box">
            <h2 style="margin-top: 0; color: #ea580c;">{{ $subscription->plan->name }} Plan</h2>
            <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">
                @if($subscription->plan->price == 0)
                    Free
                @else
                    {{ $subscription->plan->currency }} {{ number_format($subscription->plan->price, 2) }}/{{ $subscription->plan->billing_cycle }}
                @endif
            </p>

            @if($subscription->plan->features && count($subscription->plan->features) > 0)
                <h3>What's Included:</h3>
                <ul class="feature-list">
                    @foreach($subscription->plan->features as $feature)
                        <li>{{ $feature }}</li>
                    @endforeach
                    <li>{{ $subscription->plan->max_products == -1 ? 'Unlimited' : $subscription->plan->max_products }} Products</li>
                    <li>{{ $subscription->plan->max_orders == -1 ? 'Unlimited' : $subscription->plan->max_orders }} Orders/month</li>
                    <li>{{ $subscription->plan->commission_rate }}% Platform Commission</li>
                </ul>
            @endif
        </div>

        <div style="background: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #1e40af;">Quick Start Guide:</h3>
            <ol style="margin: 0; padding-left: 20px;">
                <li>Complete your profile setup</li>
                <li>Add your first product or salon service</li>
                <li>Customize your vendor store</li>
                <li>Start receiving orders and bookings</li>
            </ol>
        </div>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/my-subscription" class="button">View My Subscription</a>
        </div>

        <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
            <strong>Remember:</strong> You can cancel anytime during your trial period and you won't be charged.
        </p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>This is an automated email, please do not reply.</p>
    </div>
</body>
</html>

