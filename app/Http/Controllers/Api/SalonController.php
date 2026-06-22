<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalonService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalonController extends Controller
{
    public function index(Request $request)
    {
        $query = SalonService::with(['category', 'salon', 'stylist', 'user'])
            ->active();

        if ($request->filled('category')) {
            $category = $request->category;
            if (is_numeric($category)) {
                $query->where('category_id', $category);
            } else {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('slug', $category);
                });
            }
        }

        if ($request->filled('salon_category_id')) {
            $query->where('salon_category_id', $request->salon_category_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $hasGeo = $request->filled('lat') && $request->filled('lng');
        $driver = DB::connection()->getDriverName();

        if ($hasGeo && $driver === 'sqlite') {
            $services = $query->get();
            $filtered = $this->filterByDistance(
                $services,
                (float) $request->lat,
                (float) $request->lng,
                (float) ($request->radius ?? 50)
            );

            return response()->json($this->paginateCollection($filtered, $request));
        }

        if ($hasGeo) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            $radius = (float) ($request->radius ?? 50);

            $query->where(function ($q) use ($lat, $lng, $radius) {
                $q->whereHas('salon', function ($salonQuery) use ($lat, $lng, $radius) {
                    $salonQuery->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->whereRaw(
                            '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
                            [$lat, $lng, $lat, $radius]
                        );
                })->orWhereHas('user', function ($userQuery) use ($lat, $lng, $radius) {
                    $userQuery->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->whereRaw(
                            '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
                            [$lat, $lng, $lat, $radius]
                        );
                });
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

        $services = $query->paginate(12)->withQueryString();

        return response()->json($services);
    }

    private function filterByDistance($services, float $lat, float $lng, float $radius)
    {
        return $services->filter(function ($service) use ($lat, $lng, $radius) {
            $coords = null;

            if ($service->salon && $service->salon->latitude !== null && $service->salon->longitude !== null) {
                $coords = [(float) $service->salon->latitude, (float) $service->salon->longitude];
            } elseif ($service->user && $service->user->latitude !== null && $service->user->longitude !== null) {
                $coords = [(float) $service->user->latitude, (float) $service->user->longitude];
            }

            if (! $coords) {
                return false;
            }

            $distance = $this->haversineDistance($lat, $lng, $coords[0], $coords[1]);

            return $distance <= $radius;
        })->values();
    }

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function paginateCollection($collection, Request $request): LengthAwarePaginator
    {
        $perPage = 12;
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );
    }
}
