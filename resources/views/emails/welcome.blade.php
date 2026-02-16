<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to African Market</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4f46e5; color: white; padding: 40px 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .feature-box { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #4f46e5; }
        .button { display: inline-block; background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 32px;">Welcome to African Market!</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px;">Your gateway to authentic African products</p>
    </div>

    <div class="content">
        <p>Hi {{ $user->name }},</p>
        
        <p>Welcome to <strong>African Market</strong>! We're thrilled to have you join our community.</p>

        <p>You now have access to an amazing collection of authentic African food products, clothing, and salon services, all in one place.</p>

        <h2 style="color: #4f46e5; margin-top: 30px;">What You Can Do:</h2>

        <div class="feature-box">
            <h3 style="margin-top: 0;">🛍️ Shop African Products</h3>
            <p style="margin-bottom: 0;">Browse our wide selection of authentic African food, traditional clothing, and beauty products.</p>
        </div>

        <div class="feature-box">
            <h3 style="margin-top: 0;">💝 Create Your Wishlist</h3>
            <p style="margin-bottom: 0;">Save your favorite products and get notified when they're back in stock or on sale.</p>
        </div>

        <div class="feature-box">
            <h3 style="margin-top: 0;">⭐ Leave Reviews</h3>
            <p style="margin-bottom: 0;">Share your experience with other shoppers and help build our community.</p>
        </div>

        <div class="feature-box">
            <h3 style="margin-top: 0;">🚚 Track Your Orders</h3>
            <p style="margin-bottom: 0;">Stay updated on every step of your order from checkout to delivery.</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.url') }}/shop" class="button">Start Shopping Now</a>
        </div>

        <p>If you have any questions, our support team is always here to help.</p>
        
        <p>Happy shopping!</p>
        <p style="margin-top: 30px;"><strong>The African Market Team</strong></p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} African Market. All rights reserved.</p>
        <p>You received this email because you signed up for an account.</p>
    </div>
</body>
</html>
