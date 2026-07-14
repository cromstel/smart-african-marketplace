<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Low Stock Alert</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 700px; margin: 0 auto; padding: 20px; }
        .header { background: #dc2626; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .alert-box { background: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .products-table { width: 100%; background: white; border-collapse: collapse; margin: 20px 0; border-radius: 8px; overflow: hidden; }
        .products-table th { background: #374151; color: white; padding: 12px; text-align: left; font-weight: 600; }
        .products-table td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .products-table tr:last-child td { border-bottom: none; }
        .products-table tr:nth-child(even) { background: #f9fafb; }
        .stock-low { color: #dc2626; font-weight: bold; }
        .stock-critical { color: #991b1b; font-weight: bold; background: #fee2e2; padding: 4px 8px; border-radius: 4px; }
        .button { display: inline-block; background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
        .summary { background: white; padding: 15px; border-radius: 8px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0;">⚠️ Low Stock Alert</h2>
        <p style="margin: 10px 0 0 0;">Inventory Attention Required</p>
    </div>

    <div class="content">
        <div class="alert-box">
            <strong>Alert:</strong> {{ count($lowStockProducts) }} product(s) are running low on stock and need immediate attention.
        </div>

        <div class="summary">
            <p><strong>Low Stock Threshold:</strong> {{ $threshold }} units</p>
            <p><strong>Alert Date:</strong> {{ now()->format('F j, Y g:i A') }}</p>
        </div>

        <h2>Products Requiring Attention</h2>

        <table class="products-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Vendor</th>
                    <th>Stock</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockProducts as $product)
                    <tr>
                        <td><strong>{{ $product['name'] }}</strong></td>
                        <td>{{ $product['sku'] }}</td>
                        <td>{{ $product['category'] }}</td>
                        <td>{{ $product['vendor'] }}</td>
                        <td>
                            @if($product['stock_quantity'] <= 3)
                                <span class="stock-critical">{{ $product['stock_quantity'] }} units</span>
                            @else
                                <span class="stock-low">{{ $product['stock_quantity'] }} units</span>
                            @endif
                        </td>
                        <td>${{ number_format($product['price'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="alert-box">
            <strong>Recommended Action:</strong> 
            <ul style="margin: 10px 0;">
                <li>Review stock levels and reorder from suppliers</li>
                <li>Contact vendors for products with critical stock levels (≤3 units)</li>
                <li>Consider disabling products temporarily if unable to restock</li>
                <li>Update product visibility to prevent overselling</li>
            </ul>
        </div>

        <p style="text-align: center;">
            <a href="{{ url('/admin/products') }}" class="button">Manage Inventory</a>
        </p>

        <p>This is an automated alert sent by the {{ config('app.name') }} inventory management system.</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>You received this email because you are an administrator.</p>
    </div>
</body>
</html>

