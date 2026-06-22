<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\SeoService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function show(Request $request, Brand $brand)
    {
        if (! $brand->is_active) {
            abort(404);
        }

        $brand->loadMissing('vendor');

        $query = Product::with(['category', 'vendor'])
            ->active()
            ->published()
            ->where('brand_id', $brand->id);

        if ($request->category) {
            $selectedCategory = Category::with('children')->find($request->category);
            if ($selectedCategory) {
                $categoryIds = $selectedCategory->children->pluck('id')->prepend($selectedCategory->id)->all();
                $query->whereIn('category_id', $categoryIds);
            }
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
            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;

            $query->selectRaw(
                '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$lat, $lng, $lat]
            )
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('distance');

            $products = $query->paginate(12)->appends(request()->query());
        } else {
            $query->latest();
        }

        if (! isset($products)) {
            $products = $query->paginate(12)->appends(request()->query());
        }

        $categories = Category::parents()->active()->orderBy('sort_order')->get();
        $categoryTree = Category::with(['children' => function ($query) {
            $query->active()->orderBy('name');
        }])->parents()->active()->orderBy('sort_order')->get();
        $categoryLookup = Category::active()->get();
        $brands = Brand::active()->orderBy('name')->get();

        $seo = app(SeoService::class);
        $seo->setTitle($brand->name.' Products')
            ->setDescription($brand->description ?: ('Browse products from '.$brand->name.'.'))
            ->setKeywords([$brand->name, 'brand', 'products'])
            ->setCanonical(route('brands.show', $brand));

        return view('brands.show', compact('brand', 'products', 'categories', 'categoryTree', 'categoryLookup', 'brands'));
    }
}
