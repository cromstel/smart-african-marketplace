<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlert;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckLowStock extends Command
{
    protected $signature = 'inventory:check-low-stock';

    protected $description = 'Check for low stock products and send email alerts to admins';

    public function handle()
    {
        $threshold = (int) Setting::get('low_stock_threshold', 10);

        $lowStockProducts = Product::where('stock_quantity', '<=', $threshold)
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->with(['category', 'vendor'])
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock_quantity' => $product->stock_quantity,
                    'category' => $product->category->name,
                    'vendor' => $product->vendor?->business_name ?? 'Direct',
                    'price' => $product->price,
                ];
            })
            ->toArray();

        if (empty($lowStockProducts)) {
            $this->info('No low stock products found.');

            return 0;
        }

        $this->warn('Found '.count($lowStockProducts).' products with low stock.');

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new LowStockAlert($lowStockProducts, $threshold));
            $this->info("Alert sent to: {$admin->email}");
        }

        $this->info('Low stock alerts sent successfully.');

        return 0;
    }
}
