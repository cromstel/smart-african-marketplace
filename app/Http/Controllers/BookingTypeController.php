<?php

namespace App\Http\Controllers;

use App\Models\BookingResource;
use App\Models\BookingType;
use App\Models\SalonService;
use App\Services\SeoService;

/**
 * The customer-facing "start a booking" entry point: pick a booking type
 * first, then see what's actually bookable under it. This is the live
 * wiring for BookingType, which previously had a complete admin CRUD UI
 * (see admin/booking-types/*) and nothing on the customer side pointing at
 * it at all.
 *
 * Only two of the six seeded types have a real backing model to list:
 * appointment/consultation -> SalonService, rental -> BookingResource.
 * reservation/class/event describe entities (venues, class sessions,
 * private events) that don't exist anywhere in this app's schema -- no
 * amount of wiring fixes that, so they render an honest "not available
 * yet" state instead of an empty or fake listing.
 */
class BookingTypeController extends Controller
{
    private const SERVICE_BACKED = ['appointment', 'consultation'];

    private const RESOURCE_BACKED = ['rental'];

    public function index()
    {
        $bookingTypes = BookingType::active()->get();

        $seo = app(SeoService::class);
        $seo->setTitle('Book a Service or Rental')
            ->setDescription('Browse appointments, consultations, and equipment rentals available to book online.')
            ->setCanonical(route('booking-types.index'));

        return view('booking-types.index', compact('bookingTypes'));
    }

    public function show(BookingType $bookingType)
    {
        $seo = app(SeoService::class);
        $seo->setTitle('Book: '.ucfirst($bookingType->name))
            ->setDescription($bookingType->description ?? "Browse {$bookingType->name} options available to book online.")
            ->setCanonical(route('booking-types.show', $bookingType));

        if (in_array($bookingType->name, self::SERVICE_BACKED, true)) {
            $services = SalonService::active()
                ->with(['category', 'salonCategory', 'user'])
                ->latest()
                ->paginate(12);

            return view('booking-types.services', compact('bookingType', 'services'));
        }

        if (in_array($bookingType->name, self::RESOURCE_BACKED, true)) {
            $resources = BookingResource::active()
                ->with('vendor')
                ->orderBy('name')
                ->paginate(12);

            return view('booking-types.resources', compact('bookingType', 'resources'));
        }

        return view('booking-types.unavailable', compact('bookingType'));
    }
}
