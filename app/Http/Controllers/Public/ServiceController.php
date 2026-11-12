<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\VendorService;
use App\Models\VendorServiceCategory;
use App\Services\SeoService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = VendorService::active()
            ->with(['vendor', 'category']);

        // Filter by category type
        if ($request->filled('category_type')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('category_type', $request->category_type);
            });
        }

        // Filter by gender target
        if ($request->filled('gender_target')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('gender_target', $request->gender_target);
            });
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        $services = $query->paginate(12)->withQueryString();

        // Load parent categories with their children for hierarchical sidebar
        $parentCategories = VendorServiceCategory::active()
            ->parents()
            ->with(['children' => function ($q) {
                $q->active()->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categoryTypes = VendorServiceCategory::active()
            ->distinct('category_type')
            ->pluck('category_type');

        $genderTargets = VendorServiceCategory::active()
            ->whereNotNull('gender_target')
            ->distinct('gender_target')
            ->pluck('gender_target');

        $seo = app(SeoService::class);
        $seo->setTitle('Professional Services')
            ->setDescription('Book vetted painters, mechanics, electricians, salon professionals, car wash and construction services across Africa. Find trusted local experts on Smart African Market.')
            ->setKeywords([
                'services',
                'home services',
                'auto services',
                'beauty and grooming',
                'construction services',
                'book a service',
                'Smart African Market',
            ])
            ->setCanonical(route('services.index'));

        return view('public.services.index', compact(
            'services',
            'parentCategories',
            'categoryTypes',
            'genderTargets'
        ));
    }

    public function byCategory(Request $request, string $category)
    {
        $category = VendorServiceCategory::where('slug', $category)
            ->where('is_active', true)
            ->with(['parent', 'children' => function ($q) {
                $q->active()->orderBy('sort_order')->orderBy('name');
            }])
            ->firstOrFail();

        // Build breadcrumb path
        $breadcrumb = [$category];
        $parent = $category->parent;
        while ($parent) {
            array_unshift($breadcrumb, $parent);
            $parent = $parent->parent;
        }

        // If category has children, show services from all descendant leaf categories
        if ($category->children->count() > 0) {
            $childIds = $category->children->pluck('id');
            $services = VendorService::active()
                ->whereIn('vendor_service_category_id', $childIds)
                ->with(['vendor', 'category'])
                ->paginate(12)
                ->withQueryString();
        } else {
            $services = VendorService::active()
                ->where('vendor_service_category_id', $category->id)
                ->with(['vendor', 'category'])
                ->paginate(12)
                ->withQueryString();
        }

        $parentCategories = VendorServiceCategory::active()
            ->parents()
            ->with(['children' => function ($q) {
                $q->active()->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categoryTypes = VendorServiceCategory::active()
            ->distinct('category_type')
            ->pluck('category_type');

        $genderTargets = VendorServiceCategory::active()
            ->whereNotNull('gender_target')
            ->distinct('gender_target')
            ->pluck('gender_target');

        $seo = app(SeoService::class);
        $seo->setTitle($category->name.' Services')
            ->setDescription('Browse '.$category->name.' services from vetted vendors on Smart African Market. Compare providers and book an appointment online.')
            ->setKeywords(array_filter([
                $category->name,
                $category->category_type,
                $category->gender_target,
                'services',
                'book a service',
                'Smart African Market',
            ]))
            ->setCanonical(route('services.category', $category->slug));

        return view('public.services.category', compact(
            'category',
            'breadcrumb',
            'services',
            'parentCategories',
            'categoryTypes',
            'genderTargets'
        ));
    }

    public function show(VendorService $service)
    {
        $service->load(['vendor', 'category']);

        $relatedServices = VendorService::active()
            ->where('vendor_service_category_id', $service->vendor_service_category_id)
            ->where('id', '!=', $service->id)
            ->limit(4)
            ->get();

        return view('public.services.show', compact('service', 'relatedServices'));
    }
}
