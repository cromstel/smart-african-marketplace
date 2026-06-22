<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\GeolocationService;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
    public function index(Request $request, GeolocationService $geoService)
    {
        $query = Product::query()
            ->select([
                'id',
                'vendor_id',
                'category_id',
                'brand_id',
                'name',
                'slug',
                'short_description',
                'price',
                'compare_price',
                'featured_image',
                'sales_count',
                'views_count',
                'latitude',
                'longitude',
                'published_at',
                'is_active',
            ])
            ->with(['category:id,name'])
            ->active()
            ->published();

        if ($request->category) {
            $selectedCategory = Category::select(['id'])
                ->with('children:id,parent_id')
                ->find($request->category);
            if ($selectedCategory) {
                $categoryIds = $selectedCategory->children->pluck('id')->prepend($selectedCategory->id)->all();
                $query->whereIn('category_id', $categoryIds);
            }
        }

        if ($request->brand) {
            $query->where('brand_id', $request->brand);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('short_description', 'like', '%'.$search.'%')
                    ->orWhere('origin_country', 'like', '%'.$search.'%');
            });
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('latitude') && $request->has('longitude') && $request->has('radius')) {
            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;
            $radius = (float) $request->radius;

            $degrees = $radius / 111.32;

            $query->whereBetween('latitude', [$lat - $degrees, $lat + $degrees])
                ->whereBetween('longitude', [$lng - $degrees, $lng + $degrees])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');
        }

        if ($request->city) {
            $query->where('location_city', 'like', '%'.$request->city.'%');
        }

        if ($request->country) {
            $query->where('location_country', 'like', '%'.$request->country.'%');
        }

        if ($request->sort === 'price_low') {
            $query->orderBy('price', 'asc');
        } elseif ($request->sort === 'price_high') {
            $query->orderBy('price', 'desc');
        } elseif ($request->sort === 'popular') {
            $query->orderBy('sales_count', 'desc')
                ->orderBy('views_count', 'desc');
        } elseif ($request->sort === 'nearest' && $request->has('latitude') && $request->has('longitude')) {
            $products = $query->get();

            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;

            $products = $products->map(function ($product) use ($lat, $lng, $geoService) {
                if ($product->latitude && $product->longitude) {
                    $product->distance = $geoService->calculateDistance(
                        $lat,
                        $lng,
                        $product->latitude,
                        $product->longitude
                    );
                } else {
                    $product->distance = 999999;
                }

                return $product;
            })->sortBy('distance')->values();

            $perPage = 12;
            $currentPage = $request->get('page', 1);
            $products = new \Illuminate\Pagination\LengthAwarePaginator(
                $products->forPage($currentPage, $perPage),
                $products->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $query->latest();
        }

        if (! isset($products)) {
            $products = $query->paginate(12);
        }
        $categories = Cache::remember('shop_categories_parent_active', now()->addMinutes(15), function () {
            return Category::select(['id', 'name'])
                ->parents()
                ->active()
                ->orderBy('sort_order')
                ->get();
        });
        $categoryTree = Cache::remember('shop_categories_tree_active', now()->addMinutes(15), function () {
            return Category::with(['children' => function ($query) {
                $query->select(['id', 'parent_id', 'name'])
                    ->active()
                    ->orderBy('name');
            }])
                ->select(['id', 'name'])
                ->parents()
                ->active()
                ->orderBy('sort_order')
                ->get();
        });
        $categoryLookup = Cache::remember('shop_category_lookup_active', now()->addMinutes(15), function () {
            return Category::select(['id', 'name', 'description', 'banner_image'])
                ->active()
                ->get();
        });
        $brands = Cache::remember('shop_brands_active', now()->addMinutes(15), function () {
            return Brand::select(['id', 'name'])
                ->active()
                ->orderBy('name')
                ->get();
        });

        $seo = app(SeoService::class);
        $seo->setTitle('Shop Authentic African Products')
            ->setDescription('Discover authentic African food, clothing, art, and more. Shop from verified vendors across Africa and the diaspora.')
            ->setKeywords(['african products', 'african food', 'african clothing', 'african art', 'african market'])
            ->setCanonical(route('shop.index'));

        return view('shop.index', compact('products', 'categories', 'categoryTree', 'categoryLookup', 'brands'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'vendor', 'brand', 'images', 'variants', 'reviews']);
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->published()
            ->take(4)
            ->get();

        $seo = app(SeoService::class);
        $seo->setTitle($product->name)
            ->setDescription($product->short_description ?? substr(strip_tags($product->description), 0, 160))
            ->setKeywords([$product->name, $product->category->name ?? '', 'african product', $product->origin_country ?? ''])
            ->setCanonical(route('shop.show', $product->slug))
            ->setType('product');

        if ($product->images && $product->images->isNotEmpty()) {
            $seo->setImage(asset('storage/'.$product->images->first()->image_path));
        }

        $seo->addStructuredData($seo->generateProductSchema($product));

        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Shop', 'url' => route('shop.index')],
            ['name' => $product->name, 'url' => route('shop.show', $product->slug)],
        ];
        $seo->addStructuredData($seo->generateBreadcrumbSchema($breadcrumbs));

        return view('shop.show', compact('product', 'relatedProducts'));
    }
}
