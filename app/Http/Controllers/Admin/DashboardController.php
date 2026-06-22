<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Referral;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get all statistics
            $now = Carbon::now();
            $nextDay = $now->copy()->addHours(24);

            $stats = [
                'totalRevenue' => Order::where('payment_status', 'paid')->sum('total') ?? 0,
                'paidOrders' => Order::where('payment_status', 'paid')->count() ?? 0,
                'totalOrders' => Order::count() ?? 0,
                'totalProducts' => Product::count() ?? 0,
                'totalVendors' => Vendor::count() ?? 0,
                'totalCategories' => Category::count() ?? 0,
                'totalBlogPosts' => BlogPost::count() ?? 0,
                'totalLoyaltyPoints' => User::sum('loyalty_points_balance') ?? 0,
                'totalReferrals' => Referral::count() ?? 0,
                'bookingsToday' => Booking::whereDate('booking_date', Carbon::today())->count() ?? 0,
                'upcomingBookings' => Booking::whereIn('status', ['pending', 'confirmed'])
                    ->get()
                    ->filter(function (Booking $booking) use ($now, $nextDay) {
                        $slot = Carbon::parse($booking->booking_date->toDateString().' '.$booking->booking_time);

                        return $slot->between($now, $nextDay);
                    })
                    ->count() ?? 0,
                'cancellationsToday' => Booking::where('status', 'cancelled')
                    ->whereDate('updated_at', Carbon::today())
                    ->count() ?? 0,
            ];

            // Get recent orders
            $recentOrders = Order::with('user')->latest()->take(5)->get();

            // Get low stock products
            $lowStockProducts = Product::with('category')
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->take(5)
                ->get();

            // Get top selling products
            $topSellingProducts = Product::where('sales_count', '>', 0)
                ->orderBy('sales_count', 'desc')
                ->take(5)
                ->get();

            // Get pending vendors
            $pendingVendors = Vendor::where('status', 'pending')
                ->latest()
                ->take(5)
                ->get();

            // Get new customers
            $newCustomers = User::where('role', 'customer')
                ->latest()
                ->take(5)
                ->get();

            return view('admin.dashboard', compact(
                'stats',
                'recentOrders',
                'lowStockProducts',
                'topSellingProducts',
                'pendingVendors',
                'newCustomers'
            ));
        } catch (\Exception $e) {
            // If there's an error, return a simple dashboard view with error message
            return view('admin.dashboard')->with('error', $e->getMessage());
        }
    }
}
