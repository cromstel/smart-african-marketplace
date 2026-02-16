<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SalonService;
use Illuminate\Http\Request;

class SalonController extends Controller
{
    public function index(Request $request)
    {
        $query = SalonService::with(['category', 'user'])->active();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'duration':
                    $query->orderBy('duration_minutes', 'asc');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        $services = $query->paginate(12);
        $categories = Category::whereHas('salonServices')->withCount('salonServices')->get();

        return view('salons.index', compact('services', 'categories'));
    }

    public function show(SalonService $service)
    {
        $service->load(['category', 'user', 'bookings' => function ($query) {
            $query->where('status', 'confirmed')
                ->where('booking_date', '>=', now()->toDateString());
        }]);

        return view('salons.show', compact('service'));
    }
}
