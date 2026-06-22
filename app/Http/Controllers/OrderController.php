<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->where(function ($query) {
                $query->where('is_parent', true)
                    ->orWhereNull('order_group_id');
            })
            ->with(['items.product', 'group.orders.items'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        $order->load(['items.product', 'billingAddress', 'shippingAddress', 'payment', 'delivery', 'shipment', 'group.orders.items.product']);

        return view('orders.show', compact('order'));
    }
}
