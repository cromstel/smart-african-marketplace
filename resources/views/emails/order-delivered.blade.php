<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Delivered</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #059669; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .success-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 2px solid #059669; text-align: center; }
        .order-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .button { display: inline-block; background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
        .rating-prompt { background: #fef3c7; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #f59e0b; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0;">✅ Order Delivered Successfully!</h2>
        <p style="margin: 10px 0 0 0;">Order #{{ $order->order_number }}</p>
    </div>

    <div class="content">
        <p>Hi {{ $order->user->name }},</p>
        
        <p>Great news! Your order has been delivered successfully.</p>

        <div class="success-box">
            <h2 style="margin-top: 0; color: #059669;">✓ Delivery Confirmed</h2>
            <p style="margin: 10px 0;"><strong>Order Number:</strong> #{{ $order->order_number }}</p>
            <p style="margin: 10px 0;"><strong>Delivered Date:</strong> {{ $order->delivered_at->format('F d, Y') }}</p>
            <p style="margin: 10px 0; font-size: 18px;">We hope you enjoy your purchase!</p>
        </div>

        <h3>Order Summary</h3>
        <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            @foreach($order->items as $item)
                <div class="order-item">
                    <div>
                        <strong>{{ $item->product_name }}</strong><br>
                        <span style="color: #6b7280;">Quantity: {{ $item->quantity }}</span>
                    </div>
                    <div style="text-align: right;">
                        <strong>${{ number_format($item->total, 2) }}</strong>
                    </div>
                </div>
            @endforeach
            <div style="padding-top: 15px; margin-top: 15px; border-top: 2px solid #059669; display: flex; justify-content: space-between; font-size: 18px; font-weight: bold;">
                <span>Total Paid:</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <div class="rating-prompt">
            <h3 style="margin-top: 0; color: #92400e;">⭐ How Was Your Experience?</h3>
            <p style="margin-bottom: 15px;">We'd love to hear your feedback! Please take a moment to review the products you purchased.</p>
            <div style="text-align: center;">
                @foreach($order->items as $item)
                    <a href="{{ config('app.url') }}/shop/{{ $item->product->id }}" class="button" style="display: inline-block;">
                        Review {{ $item->product->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <p>If you have any issues with your order, please contact our support team and we'll be happy to help.</p>
        
        <p style="margin-top: 30px;">Thank you for shopping with Smart African Marketplace! We look forward to serving you again.</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Smart African Marketplace. All rights reserved.</p>
        <p>This is an automated email, please do not reply.</p>
    </div>
</body>
</html>

