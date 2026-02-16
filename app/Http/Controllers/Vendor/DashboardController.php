<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;

        if (! $vendor) {
            return redirect()->route('home')->with('error', 'Vendor profile not found');
        }

        $totalProducts = $vendor->products()->count();
        $activeProducts = $vendor->products()->where('is_active', true)->count();
        $lowStockProducts = $vendor->products()->where('stock_quantity', '<=', 10)->count();

        $totalOrders = $vendor->orderItems()->distinct('order_id')->count('order_id');
        $pendingOrders = $vendor->orderItems()
            ->whereHas('order', function ($query) {
                $query->where('status', 'pending');
            })
            ->distinct('order_id')
            ->count('order_id');

        $totalRevenue = $vendor->orderItems()
            ->whereHas('order', function ($query) {
                $query->where('payment_status', 'paid');
            })
            ->sum('total');

        $recentOrders = Order::whereHas('items', function ($query) use ($vendor) {
            $query->where('vendor_id', $vendor->id);
        })
            ->with(['user', 'items' => function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id)->with('product');
            }])
            ->latest()
            ->take(10)
            ->get();

        $topProducts = $vendor->products()
            ->where('sales_count', '>', 0)
            ->orderBy('sales_count', 'desc')
            ->take(5)
            ->get();

        return view('vendor.dashboard', compact(
            'vendor',
            'totalProducts',
            'activeProducts',
            'lowStockProducts',
            'totalOrders',
            'pendingOrders',
            'totalRevenue',
            'recentOrders',
            'topProducts'
        ));
    }
}
