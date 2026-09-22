<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\Newsletter;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function trackEvent(Request $request)
    {
        $validated = $request->validate([
            'event_name' => ['required', 'string', 'max:120'],
            'event_category' => ['nullable', 'string', 'max:80'],
            'properties' => ['nullable', 'array'],
        ]);

        $event = AnalyticsEvent::create([
            'event_name' => $validated['event_name'],
            'event_category' => $validated['event_category'] ?? 'custom',
            'route_name' => $request->route()?->getName(),
            'path' => $request->path(),
            'url' => $request->fullUrl(),
            'user_id' => optional($request->user())->id,
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->headers->get('referer'),
            'properties' => $validated['properties'] ?? [],
            'occurred_at' => now(),
        ]);

        return response()->json([
            'tracked' => true,
            'event_id' => $event->id,
        ]);
    }

    public function revenueData(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $cacheKey = "admin_analytics_revenue_{$days}";
        $revenue = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($startDate) {
            return Order::where('payment_status', 'paid')
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, SUM(total) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        });

        $labels = [];
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('M d');
            $dayRevenue = $revenue->firstWhere('date', $date);
            $data[] = $dayRevenue ? (float) $dayRevenue->total : 0;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    public function topProducts()
    {
        $topProducts = Cache::remember('admin_analytics_top_products', now()->addMinutes(10), function () {
            return DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.payment_status', 'paid')
                ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();
        });

        return response()->json([
            'labels' => $topProducts->pluck('name'),
            'data' => $topProducts->pluck('total_sold'),
        ]);
    }

    public function categoryDistribution()
    {
        $categories = Cache::remember('admin_analytics_category_distribution', now()->addMinutes(15), function () {
            return DB::table('products')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select('categories.name', DB::raw('COUNT(products.id) as product_count'))
                ->groupBy('categories.id', 'categories.name')
                ->get();
        });

        return response()->json([
            'labels' => $categories->pluck('name'),
            'data' => $categories->pluck('product_count'),
        ]);
    }

    public function customerAnalytics(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $cacheKey = "admin_analytics_customers_{$days}";
        [$newCustomers, $activeCustomers] = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($startDate) {
            $newCustomers = User::where('role', 'customer')
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $activeCustomers = Order::where('created_at', '>=', $startDate)
                ->whereNotNull('user_id')
                ->selectRaw('DATE(created_at) as date, COUNT(DISTINCT user_id) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            return [$newCustomers, $activeCustomers];
        });

        $labels = [];
        $newData = [];
        $activeData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('M d');
            $newData[] = (int) ($newCustomers[$date] ?? 0);
            $activeData[] = (int) ($activeCustomers[$date] ?? 0);
        }

        return response()->json([
            'labels' => $labels,
            'new_customers' => $newData,
            'active_customers' => $activeData,
        ]);
    }

    public function trafficSources(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $cacheKey = "admin_analytics_traffic_sources_{$days}";
        $sources = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($startDate) {
            return Newsletter::query()
                ->where('created_at', '>=', $startDate)
                ->selectRaw("COALESCE(NULLIF(TRIM(utm_source), ''), 'direct') as source, COUNT(*) as total")
                ->groupBy('source')
                ->orderByDesc('total')
                ->limit(8)
                ->get();
        });

        return response()->json([
            'labels' => $sources->pluck('source'),
            'data' => $sources->pluck('total'),
        ]);
    }

    public function salesAnalytics(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $rows = Cache::remember("admin_analytics_sales_{$days}", now()->addMinutes(10), function () use ($startDate) {
            return Order::query()
                ->where('payment_status', 'paid')
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as orders_count, SUM(total) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');
        });

        $labels = [];
        $orders = [];
        $revenue = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('M d');
            $orders[] = (int) ($rows[$date]->orders_count ?? 0);
            $revenue[] = (float) ($rows[$date]->revenue ?? 0);
        }

        return response()->json([
            'labels' => $labels,
            'orders' => $orders,
            'revenue' => $revenue,
        ]);
    }

    public function productPerformance()
    {
        $products = Cache::remember('admin_analytics_product_performance', now()->addMinutes(10), function () {
            return DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.payment_status', 'paid')
                ->select(
                    'products.id',
                    'products.name',
                    DB::raw('SUM(order_items.quantity) as quantity_sold'),
                    DB::raw('SUM(order_items.total) as revenue')
                )
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('revenue')
                ->limit(10)
                ->get();
        });

        return response()->json([
            'labels' => $products->pluck('name'),
            'quantity' => $products->pluck('quantity_sold')->map(fn ($value) => (int) $value),
            'revenue' => $products->pluck('revenue')->map(fn ($value) => (float) $value),
        ]);
    }

    public function customerSegmentation()
    {
        $customers = Cache::remember('admin_analytics_customer_segmentation', now()->addMinutes(15), function () {
            return User::query()
                ->where('role', 'customer')
                ->leftJoin('orders', function ($join) {
                    $join->on('users.id', '=', 'orders.user_id')
                        ->where('orders.payment_status', '=', 'paid');
                })
                ->select(
                    'users.id',
                    DB::raw('COUNT(orders.id) as order_count'),
                    DB::raw('COALESCE(SUM(orders.total), 0) as total_spent')
                )
                ->groupBy('users.id')
                ->get();
        });

        $segments = [
            'new' => 0,
            'active' => 0,
            'vip' => 0,
        ];

        foreach ($customers as $customer) {
            if ($customer->order_count >= 10 || $customer->total_spent >= 1000) {
                $segments['vip']++;
            } elseif ($customer->order_count >= 2) {
                $segments['active']++;
            } else {
                $segments['new']++;
            }
        }

        return response()->json([
            'labels' => ['New', 'Active', 'VIP'],
            'data' => [
                $segments['new'],
                $segments['active'],
                $segments['vip'],
            ],
            'definitions' => [
                'new' => '0-1 paid orders',
                'active' => '2-9 paid orders',
                'vip' => '10+ paid orders or spend >= 1000',
            ],
        ]);
    }

    public function cohortAnalysis(Request $request)
    {
        $months = max(3, min(18, (int) $request->get('months', 6)));
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $cohorts = User::query()
            ->where('role', 'customer')
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as cohort_month, COUNT(*) as total_users")
            ->groupBy('cohort_month')
            ->orderBy('cohort_month')
            ->get()
            ->keyBy('cohort_month');

        $retentionRows = Order::query()
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('users.role', 'customer')
            ->where('orders.payment_status', 'paid')
            ->where('users.created_at', '>=', $start)
            ->selectRaw("
                DATE_FORMAT(users.created_at, '%Y-%m') as cohort_month,
                TIMESTAMPDIFF(MONTH, DATE(users.created_at), DATE(orders.created_at)) as month_offset,
                COUNT(DISTINCT orders.user_id) as retained_users
            ")
            ->groupBy('cohort_month', 'month_offset')
            ->having('month_offset', '>=', 0)
            ->having('month_offset', '<=', $months)
            ->get();

        $rows = [];
        foreach ($cohorts as $month => $cohort) {
            $totals = [];
            for ($offset = 0; $offset <= $months; $offset++) {
                $retained = (int) optional(
                    $retentionRows->first(fn ($row) => $row->cohort_month === $month && (int) $row->month_offset === $offset)
                )->retained_users;

                if ($offset === 0) {
                    $retained = (int) $cohort->total_users;
                }

                $totals[] = [
                    'offset' => $offset,
                    'users' => $retained,
                    'retention_rate' => (int) $cohort->total_users > 0
                        ? round(($retained / (int) $cohort->total_users) * 100, 2)
                        : 0,
                ];
            }

            $rows[] = [
                'cohort_month' => $month,
                'cohort_size' => (int) $cohort->total_users,
                'retention' => $totals,
            ];
        }

        return response()->json([
            'months' => $months,
            'rows' => $rows,
        ]);
    }

    public function conversionFunnel(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $events = AnalyticsEvent::query()
            ->where('occurred_at', '>=', $startDate)
            ->whereIn('event_name', ['page_view', 'cart_add', 'checkout_started'])
            ->selectRaw('event_name, COUNT(*) as total')
            ->groupBy('event_name')
            ->pluck('total', 'event_name');

        $visits = (int) ($events['page_view'] ?? 0);
        $cartAdds = (int) ($events['cart_add'] ?? 0);
        $checkouts = (int) ($events['checkout_started'] ?? 0);
        $purchases = (int) Order::query()
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->count();

        return response()->json([
            'labels' => ['Visits', 'Cart Adds', 'Checkout Started', 'Purchases'],
            'data' => [$visits, $cartAdds, $checkouts, $purchases],
            'conversion' => [
                'visit_to_cart' => $visits > 0 ? round(($cartAdds / $visits) * 100, 2) : 0,
                'cart_to_checkout' => $cartAdds > 0 ? round(($checkouts / $cartAdds) * 100, 2) : 0,
                'checkout_to_purchase' => $checkouts > 0 ? round(($purchases / $checkouts) * 100, 2) : 0,
            ],
            'days' => $days,
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'orders');
        $format = $request->get('format', 'csv');

        if ($type === 'orders') {
            return $this->exportOrders($format);
        } elseif ($type === 'products') {
            return $this->exportProducts($format);
        } elseif ($type === 'revenue') {
            return $this->exportRevenue($format);
        }

        return response()->json(['error' => 'Invalid export type'], 400);
    }

    private function exportOrders($format)
    {
        $orders = Order::with(['user', 'items.product'])->get();

        $data = $orders->map(function ($order) {
            return [
                'Order Number' => $order->order_number,
                'Customer' => $order->user->name,
                'Email' => $order->user->email,
                'Total' => $order->total,
                'Status' => $order->status,
                'Payment Status' => $order->payment_status,
                'Items' => $order->items->count(),
                'Date' => $order->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return $this->generateCsv($data, 'orders-'.date('Y-m-d').'.csv');
    }

    private function exportProducts($format)
    {
        $products = Product::with('category')->get();

        $data = $products->map(function ($product) {
            return [
                'SKU' => $product->sku,
                'Name' => $product->name,
                'Category' => $product->category->name,
                'Price' => $product->price,
                'Stock' => $product->stock_quantity,
                'Status' => $product->is_active ? 'Active' : 'Inactive',
            ];
        });

        return $this->generateCsv($data, 'products-'.date('Y-m-d').'.csv');
    }

    private function exportRevenue($format)
    {
        $revenue = Order::where('payment_status', 'paid')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(90)
            ->get();

        $data = $revenue->map(function ($item) {
            return [
                'Date' => $item->date,
                'Orders' => $item->orders,
                'Revenue' => $item->revenue,
            ];
        });

        return $this->generateCsv($data, 'revenue-'.date('Y-m-d').'.csv');
    }

    private function generateCsv($data, $filename)
    {
        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }

            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
