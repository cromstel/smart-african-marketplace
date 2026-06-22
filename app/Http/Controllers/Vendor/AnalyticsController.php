<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductEnquiry;
use App\Models\VendorFollower;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $vendor = $user->vendor;

        if (! $vendor) {
            abort(404);
        }

        $days = 30;
        $startDate = now()->subDays($days)->startOfDay();

        $ordersQuery = Order::query()
            ->whereHas('items', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            });

        $totalOrders = (clone $ordersQuery)->count();
        $paidOrders = (clone $ordersQuery)->where('payment_status', 'paid')->count();
        $cancelledOrders = (clone $ordersQuery)->where('status', 'cancelled')->count();
        $deliveredOrders = (clone $ordersQuery)->where('status', 'delivered')->count();

        $revenue = $vendor->orderItems()
            ->whereHas('order', function ($query) use ($startDate) {
                $query->where('payment_status', 'paid')->where('created_at', '>=', $startDate);
            })
            ->sum('total');

        $ordersSeries = (clone $ordersQuery)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(function ($row) {
                return ['day' => $row->day, 'total' => (int) $row->total];
            });

        $topProducts = $vendor->products()
            ->orderByDesc('sales_count')
            ->orderByDesc('views_count')
            ->limit(5)
            ->get(['id', 'name', 'sales_count', 'views_count', 'price']);

        $totalEnquiries = ProductEnquiry::query()
            ->whereHas('product', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->count();

        $resolvedEnquiries = ProductEnquiry::query()
            ->whereHas('product', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
            ->whereIn('status', ['answered', 'resolved'])
            ->count();

        $newFollowersLast30Days = VendorFollower::query()
            ->where('vendor_id', $vendor->id)
            ->where('created_at', '>=', $startDate)
            ->count();

        $metrics = [
            'total_orders' => $totalOrders,
            'paid_orders' => $paidOrders,
            'cancelled_orders' => $cancelledOrders,
            'delivered_orders' => $deliveredOrders,
            'conversion_rate' => $totalOrders > 0 ? round(($paidOrders / $totalOrders) * 100, 1) : 0,
            'cancellation_rate' => $totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 1) : 0,
            'delivery_rate' => $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100, 1) : 0,
            'revenue_30_days' => (float) $revenue,
            'enquiry_response_rate' => $totalEnquiries > 0 ? round(($resolvedEnquiries / $totalEnquiries) * 100, 1) : 0,
            'new_followers_30_days' => $newFollowersLast30Days,
            'last_updated' => Carbon::now()->format('M d, Y H:i'),
        ];

        return view('vendor.analytics.index', compact('vendor', 'metrics', 'ordersSeries', 'topProducts', 'days'));
    }
}
