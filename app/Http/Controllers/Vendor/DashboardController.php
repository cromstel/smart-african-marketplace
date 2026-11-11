<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\DataExportRequest;
use App\Models\Order;
use App\Models\ProductEnquiry;
use App\Models\RmaRequest;
use App\Models\VendorBalance;
use App\Models\VendorFollower;
use App\Models\VendorService;
use App\Models\VendorVerification;
use App\Models\Withdrawal;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $vendor = $user->vendor;

        if (! $vendor) {
            return redirect()->route('home')->with('error', 'Vendor profile not found');
        }

        $subscription = $user->effectiveSubscription();

        $canProducts = $user->hasModuleAccess('multiple_product_category');
        $canOrders = $user->hasModuleAccess('order_management');
        $canWithdrawals = true;
        $canEnquiries = $user->hasModuleAccess('product_enquiry');
        $canRma = $user->hasModuleAccess('vendor_rma_request');
        $canFollowers = $user->hasModuleAccess('follow_store');
        $canAnnouncements = $user->hasModuleAccess('vendor_dashboard_announcements');
        $canStoreTheme = $user->hasModuleAccess('vendor_independent_store');
        $canManualOrder = $user->hasModuleAccess('manual_order_creation');

        $totalProducts = $canProducts ? $vendor->products()->count() : 0;
        $activeProducts = $canProducts ? $vendor->products()->where('is_active', true)->count() : 0;
        $lowStockProducts = $canProducts ? $vendor->products()->where('stock_quantity', '<=', 10)->count() : 0;

        $totalOrders = $canOrders ? $vendor->orderItems()->distinct('order_id')->count('order_id') : 0;
        $pendingOrders = $canOrders
            ? $vendor->orderItems()
                ->whereHas('order', function ($query) {
                    $query->where('status', 'pending');
                })
                ->distinct('order_id')
                ->count('order_id')
            : 0;

        $totalRevenue = $canOrders
            ? $vendor->orderItems()
                ->whereHas('order', function ($query) {
                    $query->where('payment_status', 'paid');
                })
                ->sum('total')
            : 0;

        $availableBalance = $canWithdrawals
            ? $vendor->orderItems()
                ->whereHas('order', function ($query) {
                    $query->where('payment_status', 'paid');
                })
                ->sum('total')
            : 0;

        $commissionRate = $vendor->commission_rate ?? config('marketplace.default_commission_rate', 10);
        $availableBalance = $availableBalance * (1 - $commissionRate / 100);

        $totalWithdrawals = $canWithdrawals
            ? Withdrawal::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'processing'])
                ->sum('amount')
            : 0;

        $availableBalance -= $totalWithdrawals;

        $balanceRecord = VendorBalance::where('vendor_id', $vendor->id)->first();
        if ($balanceRecord) {
            $availableBalance = (float) $balanceRecord->available;
        }

        $pendingWithdrawals = $canWithdrawals
            ? Withdrawal::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count()
            : 0;

        $pendingEnquiries = $canEnquiries
            ? ProductEnquiry::whereHas('product', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })->where('status', 'pending')->count()
            : 0;

        $pendingRmaRequests = $canRma
            ? RmaRequest::whereHas('orderItem', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })->where('status', 'pending')->count()
            : 0;

        $totalFollowers = $canFollowers ? VendorFollower::where('vendor_id', $vendor->id)->count() : 0;

        $totalServices = VendorService::where('vendor_id', $vendor->id)->count();
        $activeServices = VendorService::where('vendor_id', $vendor->id)->where('status', 'available')->count();

        $recentOrders = $canOrders
            ? Order::whereHas('items', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })
                ->with(['user', 'items' => function ($query) use ($vendor) {
                    $query->where('vendor_id', $vendor->id)->with('product');
                }])
                ->latest()
                ->take(10)
                ->get()
            : collect();

        $topProducts = $canProducts
            ? $vendor->products()
                ->where('sales_count', '>', 0)
                ->orderBy('sales_count', 'desc')
                ->take(5)
                ->get()
            : collect();

        $unreadAnnouncements = $canAnnouncements
            ? Announcement::active()
                ->forVendor($user->id)
                ->unreadBy(auth()->id())
                ->latest()
                ->take(3)
                ->get()
            : collect();

        $performance = [
            'cancellation_rate' => 0,
            'avg_fulfillment_days' => 0,
            'late_shipments' => 0,
        ];

        if ($canOrders && $totalOrders > 0) {
            $cancelledOrders = Order::whereHas('items', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })->where('status', 'cancelled')->count();

            $delivered = Order::whereHas('items', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })->whereNotNull('delivered_at')->get();

            $avgFulfillment = $delivered->avg(function ($order) {
                return $order->delivered_at->diffInDays($order->created_at);
            }) ?? 0;

            $lateShipments = Order::whereHas('items', function ($query) use ($vendor) {
                $query->where('vendor_id', $vendor->id);
            })->whereNotNull('shipped_at')
                ->whereRaw('TIMESTAMPDIFF(DAY, created_at, shipped_at) > 3')
                ->count();

            $performance = [
                'cancellation_rate' => round(($cancelledOrders / $totalOrders) * 100, 1),
                'avg_fulfillment_days' => round($avgFulfillment, 1),
                'late_shipments' => $lateShipments,
            ];
        }

        $existingRequest = DataExportRequest::where('requested_by', $user->id)
            ->latest()
            ->first();

        // Pass the 'pending' status explicitly (matching the migration's column
        // default) so a freshly created record's in-memory model already has
        // it set -- firstOrCreate()'s create() path does not re-fetch the row,
        // so relying on the DB-level default alone left $verification->status
        // null on a vendor's very first dashboard visit, rendering a blank
        // "Current status: " in the view.
        $verification = VendorVerification::firstOrCreate(
            ['vendor_id' => $vendor->id],
            ['status' => 'pending']
        );

        return view('vendor.dashboard', compact(
            'vendor',
            'subscription',
            'totalProducts',
            'activeProducts',
            'lowStockProducts',
            'totalOrders',
            'pendingOrders',
            'totalRevenue',
            'availableBalance',
            'pendingWithdrawals',
            'pendingEnquiries',
            'pendingRmaRequests',
            'totalFollowers',
            'totalServices',
            'activeServices',
            'recentOrders',
            'topProducts',
            'unreadAnnouncements',
            'canProducts',
            'canOrders',
            'canWithdrawals',
            'canEnquiries',
            'canRma',
            'canFollowers',
            'canAnnouncements',
            'canStoreTheme',
            'canManualOrder',
            'performance',
            'existingRequest',
            'verification'
        ));
    }
}
