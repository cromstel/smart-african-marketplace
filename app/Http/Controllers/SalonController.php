<?php

namespace App\Http\Controllers;

use App\Models\SalonService;
use App\Models\SalonServiceCategory;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalonController extends Controller
{
    public function index(Request $request)
    {
        $query = SalonService::with(['category', 'salonCategory', 'user'])->active();

        if ($request->filled('category')) {
            $query->where('salon_category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
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

            $services = $this->paginateCollection($filtered, $request);
        } elseif ($hasGeo) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            $radius = (float) ($request->radius ?? 50);

            $query->whereHas('user', function ($q) use ($lat, $lng, $radius) {
                $q->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereRaw(
                        '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
                        [$lat, $lng, $lat, $radius]
                    );
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

        if (! isset($services)) {
            $services = $query->paginate(12);
        }
        $categories = SalonServiceCategory::active()
            ->withCount('services')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $seo = app(SeoService::class);
        $seo->setTitle('African Salon & Beauty Services')
            ->setDescription('Book authentic African hair styling, braiding, beauty treatments, and spa services. Professional salons specializing in African hair care.')
            ->setKeywords(['african salon', 'african hair braiding', 'african beauty', 'african hair care', 'african spa'])
            ->setCanonical(route('salon.index'));

        return view('salon.index', compact('services', 'categories'));
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

    public function show(SalonService $service)
    {
        $service->load(['category', 'user', 'salon', 'stylist', 'bookings' => function ($query) {
            $query->where('status', 'confirmed')
                ->where('booking_date', '>=', now()->toDateString());
        }]);

        $seo = app(SeoService::class);
        $seo->setTitle($service->name)
            ->setDescription($service->description ? substr(strip_tags($service->description), 0, 160) : "Book {$service->name} service online.")
            ->setKeywords([$service->name, 'african salon', 'beauty service', 'hair care'])
            ->setCanonical(route('salon.show', $service->slug))
            ->setType('service');

        if ($service->image) {
            $seo->setImage(asset('storage/'.$service->image));
        }

        $serviceSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $service->name,
            'description' => $service->description ?? '',
            'offers' => [
                '@type' => 'Offer',
                'url' => route('salon.show', $service->slug),
                'priceCurrency' => 'USD',
                'price' => $service->price,
            ],
            'provider' => [
                '@type' => 'LocalBusiness',
                'name' => $service->user->name ?? config('app.name'),
            ],
        ];
        $seo->addStructuredData($serviceSchema);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Salon', 'url' => route('salon.index')],
            ['name' => $service->name, 'url' => route('salon.show', $service->slug)],
        ];
        $seo->addStructuredData($seo->generateBreadcrumbSchema($breadcrumbs));

        return view('salon.show', compact('service'));
    }
}
