<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorStoreController extends Controller
{
    public function show(Vendor $vendor, Request $request, SeoService $seoService): View
    {
        return $this->renderStore($vendor, $request, $seoService);
    }

    public function showFromHost(Request $request, SeoService $seoService): ?View
    {
        $vendor = Vendor::resolveStorefrontHost($request->getHost());

        if (! $vendor) {
            return null;
        }

        return $this->renderStore($vendor, $request, $seoService);
    }

    public function renderStore(Vendor $vendor, Request $request, SeoService $seoService): View
    {
        if (! $vendor->is_active || $vendor->status !== 'approved') {
            abort(404, 'Store not found or inactive');
        }

        $theme = $vendor->theme;

        $productsQuery = $vendor->products()
            ->active()
            ->published()
            ->with(['category', 'images']);

        if ($request->search) {
            $search = $request->search;
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('short_description', 'like', '%'.$search.'%');
            });
        }

        if ($request->category) {
            $productsQuery->where('category_id', $request->category);
        }

        if ($request->min_price) {
            $productsQuery->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $productsQuery->where('price', '<=', $request->max_price);
        }

        if ($request->sort === 'price_low') {
            $productsQuery->orderBy('price', 'asc');
        } elseif ($request->sort === 'price_high') {
            $productsQuery->orderBy('price', 'desc');
        } elseif ($request->sort === 'popular') {
            $productsQuery->orderBy('sales_count', 'desc');
        } else {
            $productsQuery->latest();
        }

        $products = $productsQuery->paginate(12);

        $categories = $vendor->products()
            ->active()
            ->published()
            ->distinct()
            ->pluck('category_id')
            ->map(fn ($id) => \App\Models\Category::find($id))
            ->filter();

        $followers = $vendor->followers()->count();

        $seo = $seoService
            ->setTitle($vendor->business_name.' - Vendor Store')
            ->setDescription($vendor->description ?? 'Shop products from '.$vendor->business_name)
            ->setKeywords($vendor->business_name.', vendor, marketplace, products')
            ->setCanonical($vendor->storefrontUrl())
            ->setType('website');

        if ($vendor->profile_image) {
            $seo->setImage(asset('storage/'.$vendor->profile_image));
        } elseif ($vendor->logo) {
            $seo->setImage(asset('storage/'.$vendor->logo));
        }

        $seo->addStructuredData([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $vendor->business_name,
            'url' => $vendor->storefrontUrl(),
            'logo' => $vendor->profile_image
                ? asset('storage/'.$vendor->profile_image)
                : ($vendor->logo ? asset('storage/'.$vendor->logo) : asset(config('branding.logo'))),
            'description' => $vendor->description,
        ]);

        return view('vendor-store.show', compact('vendor', 'theme', 'products', 'categories', 'followers', 'seo'));
    }
}
