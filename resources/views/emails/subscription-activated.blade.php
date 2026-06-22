<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Subscription Activated</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(to right, #10b981, #059669); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .success-box { background: #d1fae5; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #10b981; }
        .plan-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #e5e7eb; }
        .feature-list { list-style: none; padding: 0; }
        .feature-list li { padding: 8px 0; padding-left: 25px; position: relative; }
        .feature-list li:before { content: "✓"; position: absolute; left: 0; color: #10b981; font-weight: bold; }
        .button { display: inline-block; background: linear-gradient(to right, #ea580c, #dc2626); color: white !important; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0;">✅ Subscription Activated!</h2>
        <p style="margin: 10px 0 0 0;">Your trial has ended and payment was successful</p>
    </div>

    <div class="content">
        <p>Hi {{ $subscription->user->name }},</p>
        
        <p>Great news! Your free trial has ended and your paid subscription to the <strong>{{ $subscription->plan->name }}</strong> plan is now active.</p>

        <div class="success-box">
            <h3 style="margin-top: 0;">🎉 Payment Successful</h3>
            <p style="font-size: 18px; margin: 5px 0;"><strong>{{ $subscription->plan->currency }} {{ number_format($subscription->plan->price, 2) }}</strong> charged</p>
            <p style="margin-bottom: 0;">Your next billing date is <strong>{{ $subscription->current_period_end->format('F j, Y') }}</strong></p>
        </div>

        <div class="plan-box">
            <h2 style="margin-top: 0; color: #10b981;">{{ $subscription->plan->name }} Plan - Active</h2>
            
            @if($subscription->plan->features && count($subscription->plan->features) > 0)
                <h3>Your Benefits:</h3>
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
            <h3 style="margin-top: 0; color: #1e40af;">Subscription Details</h3>
            <p style="margin: 5px 0;"><strong>Status:</strong> <span style="color: #10b981;">Active</span></p>
            <p style="margin: 5px 0;"><strong>Billing Cycle:</strong> {{ ucfirst($subscription->plan->billing_cycle) }}</p>
            <p style="margin: 5px 0;"><strong>Amount:</strong> {{ $subscription->plan->currency }} {{ number_format($subscription->plan->price, 2) }}</p>
            <p style="margin: 5px 0;"><strong>Next Billing Date:</strong> {{ $subscription->current_period_end->format('F j, Y') }}</p>
            <p style="margin: 5px 0;"><strong>Payment Method:</strong> {{ ucfirst($subscription->payment_provider ?? 'Not set') }}</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/my-subscription" class="button">Manage Subscription</a>
        </div>

        <p style="margin-top: 30px;">
            Thank you for choosing {{ config('app.name') }}! If you have any questions or need assistance, our support team is here to help.
        </p>

        <p style="color: #6b7280; font-size: 14px;">
            You can manage your subscription, view invoices, or update payment methods from your subscription dashboard.
        </p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>This is an automated email, please do not reply.</p>
    </div>
</body>
</html>

