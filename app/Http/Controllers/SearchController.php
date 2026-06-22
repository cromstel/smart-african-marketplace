<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SearchQuery;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $filters = $this->normalizeFilters($request);
        $searchQuery = $this->buildSearchBase($query);

        $productQuery = clone $searchQuery;
        $this->applyFilters($productQuery, $filters);

        $products = $productQuery->with('images')->paginate(12)->withQueryString();

        $priceStatsQuery = clone $searchQuery;
        $this->applyFilters($priceStatsQuery, $filters);
        $priceMin = (float) ($priceStatsQuery->min('price') ?? 0);
        $priceMax = (float) ($priceStatsQuery->max('price') ?? 0);
        if ($priceMax <= $priceMin) {
            $priceMax = $priceMin + 1;
        }

        $categoryCounts = $this->facetCounts($searchQuery, $filters, 'categories');
        $brandCounts = $this->facetCounts($searchQuery, $filters, 'brands');
        $vendorCounts = $this->facetCounts($searchQuery, $filters, 'vendors');

        $facetCategories = Category::where('is_active', true)->orderBy('name')->get()->map(fn ($category) => [
            'id' => $category->id,
            'name' => $category->name,
            'count' => $categoryCounts[$category->id] ?? 0,
        ]);

        $facetBrands = Brand::where('is_active', true)->orderBy('name')->get()->map(fn ($brand) => [
            'id' => $brand->id,
            'name' => $brand->name,
            'count' => $brandCounts[$brand->id] ?? 0,
        ]);

        $facetVendors = Vendor::active()->approved()->orderBy('business_name')->get()->map(fn ($vendor) => [
            'id' => $vendor->id,
            'name' => $vendor->business_name,
            'count' => $vendorCounts[$vendor->id] ?? 0,
        ]);

        $ratingCounts = [];
        for ($rating = 5; $rating >= 1; $rating--) {
            $ratingQuery = clone $searchQuery;
            $this->applyFilters($ratingQuery, $filters);
            $ratingCounts[$rating] = (clone $ratingQuery)
                ->where('average_rating', '>=', $rating)
                ->count();
        }

        $posts = collect();
        $categoryMatches = collect();
        if ($query !== '') {
            $posts = BlogPost::where(function ($builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
                ->where('status', 'published')
                ->where('published_at', '<=', now())
                ->take(5)
                ->get();

            $categoryMatches = Category::where('name', 'like', "%{$query}%")
                ->take(5)
                ->get();
        }

        $selectedCategoryNames = collect();
        if (! empty($filters['categories'])) {
            $selectedCategoryNames = Category::whereIn('id', $filters['categories'])->pluck('name', 'id');
        }

        $selectedBrandNames = collect();
        if (! empty($filters['brands'])) {
            $selectedBrandNames = Brand::whereIn('id', $filters['brands'])->pluck('name', 'id');
        }

        $selectedVendorNames = collect();
        if (! empty($filters['vendors'])) {
            $selectedVendorNames = Vendor::whereIn('id', $filters['vendors'])->pluck('business_name', 'id');
        }

        $topQueries = SearchQuery::orderByDesc('hits')->limit(6)->get();
        $this->logSearch($query, $products->total(), $request);

        return view('pages.search', compact(
            'query',
            'products',
            'filters',
            'facetCategories',
            'facetBrands',
            'facetVendors',
            'priceMin',
            'priceMax',
            'ratingCounts',
            'topQueries',
            'posts',
            'categoryMatches',
            'selectedCategoryNames',
            'selectedBrandNames',
            'selectedVendorNames'
        ));
    }

    public function suggestions(Request $request)
    {
        $term = trim((string) $request->input('q', ''));
        if ($term === '') {
            return response()->json([]);
        }

        $suggestions = SearchQuery::where('query', 'like', $term.'%')
            ->orderByDesc('hits')
            ->limit(8)
            ->pluck('query');

        if ($suggestions->isEmpty()) {
            $suggestions = Product::where('name', 'like', $term.'%')
                ->where('is_active', true)
                ->limit(8)
                ->pluck('name');
        }

        return response()->json($suggestions->values());
    }

    private function normalizeFilters(Request $request): array
    {
        return [
            'categories' => array_values(array_filter((array) $request->input('categories', []))),
            'brands' => array_values(array_filter((array) $request->input('brands', []))),
            'vendors' => array_values(array_filter((array) $request->input('vendors', []))),
            'min_price' => $this->normalizeNumeric($request->input('min_price')),
            'max_price' => $this->normalizeNumeric($request->input('max_price')),
            'location_city' => trim((string) $request->input('location_city', '')),
            'location_country' => trim((string) $request->input('location_country', '')),
            'rating_min' => $this->normalizeNumeric($request->input('rating_min')),
        ];
    }

    private function normalizeNumeric($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function buildSearchBase(string $query): Builder
    {
        $builder = Product::query()->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($query !== '') {
            $builder->where(function ($sub) use ($query) {
                $sub->where('name', 'like', "%{$query}%")
                    ->orWhere('short_description', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('origin_country', 'like', "%{$query}%");
            });
        }

        return $builder;
    }

    private function applyFilters(Builder $query, array $filters, array $exclude = []): void
    {
        if (! in_array('categories', $exclude, true) && ! empty($filters['categories'])) {
            $query->whereIn('category_id', $filters['categories']);
        }

        if (! in_array('brands', $exclude, true) && ! empty($filters['brands'])) {
            $query->whereIn('brand_id', $filters['brands']);
        }

        if (! in_array('vendors', $exclude, true) && ! empty($filters['vendors'])) {
            $query->whereIn('vendor_id', $filters['vendors']);
        }

        if ($filters['min_price'] !== null) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if ($filters['max_price'] !== null) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if ($filters['location_city'] !== '') {
            $query->where('location_city', 'like', "%{$filters['location_city']}%");
        }

        if ($filters['location_country'] !== '') {
            $query->where('location_country', 'like', "%{$filters['location_country']}%");
        }

        if ($filters['rating_min'] !== null) {
            $query->where('average_rating', '>=', $filters['rating_min']);
        }
    }

    private function facetCounts(Builder $base, array $filters, string $facet): array
    {
        $column = match ($facet) {
            'categories' => 'category_id',
            'brands' => 'brand_id',
            'vendors' => 'vendor_id',
            default => null,
        };

        if ($column === null) {
            return [];
        }

        $builder = clone $base;
        $this->applyFilters($builder, $filters, [$facet]);

        return $builder->select($column, DB::raw('count(*) as total'))
            ->groupBy($column)
            ->pluck('total', $column)
            ->toArray();
    }

    private function logSearch(string $query, int $resultsCount, Request $request): void
    {
        if ($query === '') {
            return;
        }

        $entry = SearchQuery::firstOrCreate(['query' => $query], ['hits' => 0]);
        $entry->hits = ($entry->hits ?? 0) + 1;
        $entry->results_count = $resultsCount;
        $entry->last_searched_at = now();
        $entry->last_user_id = auth()->id();
        $entry->last_ip = $request->ip();
        $entry->last_user_agent = substr((string) $request->userAgent(), 0, 255);
        $entry->save();
    }
}
