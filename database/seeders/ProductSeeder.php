<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MediaFolder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use App\Services\ImageOptimizationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::where('status', 'approved')->get();
        $categories = Category::whereNotNull('parent_id')
            ->whereIn('type', ['food', 'clothing'])
            ->get();

        if ($vendors->isEmpty() || $categories->isEmpty()) {
            return;
        }

        $admin = \App\Models\User::where('email', 'admin@africanmarket.com')->first()
            ?? \App\Models\User::where('role', 'admin')->first();

        if ($admin) {
            Auth::login($admin);
        }

        $seedFolderId = MediaFolder::where('slug', 'seeded')->value('id');
        $imageService = app(ImageOptimizationService::class);

        foreach ($categories as $category) {
            for ($i = 1; $i <= 12; $i++) {
                $vendor = $vendors->random();
                $name = $category->name.' Sample '.$i;
                $slug = Str::slug($category->slug.'-'.$i);

                $product = Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'vendor_id' => $vendor->id,
                        'category_id' => $category->id,
                        'name' => $name,
                        'sku' => 'CAT'.$category->id.'-'.str_pad($i, 3, '0', STR_PAD_LEFT),
                        'description' => 'Sample product for '.$category->name.'.',
                        'short_description' => 'Sample '.$category->name.' product',
                        'price' => 19.99 + $i,
                        'compare_price' => 24.99 + $i,
                        'cost_price' => 9.99 + $i,
                        'stock_quantity' => 50 + $i,
                        'low_stock_threshold' => 10,
                        'stock_status' => 'in_stock',
                        'origin_country' => 'Ghana',
                        'is_featured' => $i <= 2,
                        'is_active' => true,
                        'published_at' => now(),
                    ]
                );

                if ($product->images()->count() === 0) {
                    $file = SeedImageHelper::makeImage($name, 800, 800);
                    $result = $imageService->uploadAndOptimize(
                        $file,
                        'products/'.$product->id,
                        800,
                        800,
                        'product',
                        $product->id,
                        true,
                        $seedFolderId
                    );

                    $productImage = ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $result['original'],
                        'alt_text' => $name,
                        'sort_order' => 0,
                        'is_primary' => true,
                    ]);

                    $product->update(['featured_image' => $productImage->image_path]);
                    @unlink($file->getRealPath());
                }
            }
        }

        if ($admin) {
            Auth::logout();
        }
    }
}
