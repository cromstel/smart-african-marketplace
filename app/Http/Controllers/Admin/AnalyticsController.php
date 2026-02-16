<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function revenueData(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $revenue = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

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
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return response()->json([
            'labels' => $topProducts->pluck('name'),
            'data' => $topProducts->pluck('total_sold'),
        ]);
    }

    public function categoryDistribution()
    {
        $categories = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COUNT(products.id) as product_count'))
            ->groupBy('categories.id', 'categories.name')
            ->get();

        return response()->json([
            'labels' => $categories->pluck('name'),
            'data' => $categories->pluck('product_count'),
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
                'Total' => $order->total_amount,
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
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
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
