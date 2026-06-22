<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Failed</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(to right, #dc2626, #991b1b); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .error-box { background: #fee2e2; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #dc2626; }
        .info-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #e5e7eb; }
        .button { display: inline-block; background: linear-gradient(to right, #ea580c, #dc2626); color: white !important; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0;">❌ Payment Failed</h2>
        <p style="margin: 10px 0 0 0;">Action required to maintain your subscription</p>
    </div>

    <div class="content">
        <p>Hi {{ $subscription->user->name }},</p>
        
        <p>We were unable to process your subscription payment for the <strong>{{ $subscription->plan->name }}</strong> plan.</p>

        <div class="error-box">
            <h3 style="margin-top: 0;">⚠️ Payment Issue</h3>
            <p style="margin: 5px 0;">
                <strong>Amount:</strong> {{ $subscription->plan->currency }} {{ number_format($subscription->plan->price, 2) }}
            </p>
            <p style="margin: 5px 0;">
                <strong>Payment Method:</strong> {{ ucfirst($subscription->payment_provider ?? 'Not set') }}
            </p>
            <p style="margin-bottom: 0;">
                <strong>Attempted on:</strong> {{ now()->format('F j, Y') }}
            </p>
        </div>

        <div class="info-box">
            <h3 style="margin-top: 0;">Why did this happen?</h3>
            <ul style="margin: 10px 0; padding-left: 20px; color: #4b5563;">
                <li>Insufficient funds in your account</li>
                <li>Expired or invalid payment method</li>
                <li>Bank declined the transaction</li>
                <li>Card limit exceeded</li>
            </ul>
        </div>

        <h3>What you need to do:</h3>
        <ol style="color: #4b5563;">
            <li>Update your payment method or add a new one</li>
            <li>Ensure sufficient funds are available</li>
            <li>We'll automatically retry the payment</li>
        </ol>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/my-subscription" class="button">Update Payment Method</a>
        </div>

        <div style="background: #fffbeb; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #f59e0b;">
            <p style="margin: 0;"><strong>⏰ Grace Period:</strong> You have 7 days to update your payment before your subscription is suspended.</p>
        </div>

        <p style="color: #6b7280; font-size: 14px;">
            If you no longer wish to continue your subscription, you can cancel it from your subscription dashboard.
        </p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>This is an automated email, please do not reply.</p>
    </div>
</body>
</html>

