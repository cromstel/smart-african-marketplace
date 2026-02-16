<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Shipped</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #10b981; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .tracking-box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 2px solid #10b981; text-align: center; }
        .order-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .button { display: inline-block; background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
        .address-box { background: #f3f4f6; padding: 15px; border-radius: 6px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">📦 Your Order is On Its Way!</h1>
        <p style="margin: 10px 0 0 0;">Order #{{ $order->order_number }}</p>
    </div>

    <div class="content">
        <p>Hi {{ $order->user->name }},</p>
        
        <p>Great news! Your order has been shipped and is on its way to you.</p>

        <div class="tracking-box">
            <h2 style="margin-top: 0; color: #10b981;">Shipping Information</h2>
            <p style="margin: 10px 0;"><strong>Order Number:</strong> #{{ $order->order_number }}</p>
            <p style="margin: 10px 0;"><strong>Shipped Date:</strong> {{ $order->shipped_at->format('F d, Y') }}</p>
            @if($order->tracking_number)
                <p style="margin: 10px 0;"><strong>Tracking Number:</strong> {{ $order->tracking_number }}</p>
            @endif
        </div>

        <h3>Order Items</h3>
        <div style="background: white; padding: 15px; border-radius: 8px;">
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
        </div>

        <h3>Shipping To:</h3>
        <div class="address-box">
            <strong>{{ $order->shippingAddress->full_name }}</strong><br>
            {{ $order->shippingAddress->address_line1 }}<br>
            @if($order->shippingAddress->address_line2)
                {{ $order->shippingAddress->address_line2 }}<br>
            @endif
            {{ $order->shippingAddress->city }}, {{ $order->shippingAddress->state }} {{ $order->shippingAddress->postal_code }}<br>
            {{ $order->shippingAddress->country }}<br>
            Phone: {{ $order->shippingAddress->phone }}
        </div>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="button">Track Your Order</a>
        </div>

        <p style="margin-top: 30px;">Your order should arrive soon. We'll send you another email once it's delivered.</p>
        
        <p>Thank you for shopping with African Market!</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} African Market. All rights reserved.</p>
        <p>This is an automated email, please do not reply.</p>
    </div>
</body>
</html>
