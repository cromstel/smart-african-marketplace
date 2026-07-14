<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4f46e5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .order-summary { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #e5e7eb; }
        .order-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .order-total { font-size: 18px; font-weight: bold; padding-top: 15px; border-top: 2px solid #4f46e5; margin-top: 15px; }
        .button { display: inline-block; background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
        .address-box { background: #f3f4f6; padding: 15px; border-radius: 6px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0;">Order Confirmation</h2>
        <p style="margin: 10px 0 0 0;">Thank you for your order!</p>
    </div>

    <div class="content">
        <p>Hi {{ $order->user->name }},</p>
        
        <p>We've received your order and it's being processed. Your order number is <strong>#{{ $order->order_number }}</strong>.</p>

        <div class="order-summary">
            <h2 style="margin-top: 0;">Order Details</h2>
            
            @php
                $emailItems = $order->items;
                if ($order->is_parent && $order->group) {
                    $emailItems = $order->group->orders->where('is_parent', false)->flatMap->items;
                }
            @endphp
            @foreach($emailItems as $item)
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

            <div class="order-total" style="display: flex; justify-content: space-between;">
                <span>Total Amount:</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>

            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <p style="margin: 5px 0;"><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                <p style="margin: 5px 0;"><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
            </div>
        </div>

        <h3>Shipping Address</h3>
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
            <a href="{{ config('app.url') }}/orders/{{ $order->id }}" class="button">View Order Details</a>
        </div>

        <p style="margin-top: 30px;">We'll send you another email when your order ships.</p>
        
        <p>If you have any questions, please contact our support team.</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Smart African Marketplace. All rights reserved.</p>
        <p>This is an automated email, please do not reply.</p>
    </div>
</body>
</html>

