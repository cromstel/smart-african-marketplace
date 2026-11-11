<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\VendorService;
use App\Models\VendorServiceCategory;
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

        $categoryTypes = VendorServiceCategory::active()
            ->distinct('category_type')
            ->pluck('category_type');

        $genderTargets = VendorServiceCategory::active()
            ->whereNotNull('gender_target')
            ->distinct('gender_target')
            ->pluck('gender_target');

        return view('public.services.index', compact(
            'services',
            'categoryTypes',
            'genderTargets'
        ));
    }

    public function byCategory(Request $request, string $categorySlug)
    {
        $category = VendorServiceCategory::where('slug', $categorySlug)
            ->where('is_active', true)
            ->firstOrFail();

        $services = VendorService::active()
            ->where('vendor_service_category_id', $category->id)
            ->with(['vendor', 'category'])
            ->paginate(12)
            ->withQueryString();

        return view('public.services.category', compact('category', 'services'));
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
