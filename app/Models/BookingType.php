<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BookingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'default_duration_minutes',
        'min_duration_minutes',
        'max_duration_minutes',
        'booking_window_days',
        'cancellation_policy_hours',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_duration_minutes' => 'integer',
        'min_duration_minutes' => 'integer',
        'max_duration_minutes' => 'integer',
        'booking_window_days' => 'integer',
        'cancellation_policy_hours' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (BookingType $bookingType) {
            if (empty($bookingType->slug)) {
                $bookingType->slug = Str::slug($bookingType->name);
            }
        });

        static::updating(function (BookingType $bookingType) {
            if ($bookingType->isDirty('name') && empty($bookingType->slug)) {
                $bookingType->slug = Str::slug($bookingType->name);
            }
        });
    }

    /**
     * Get the bookings for this booking type.
     *
     * `bookings.booking_type_id`, which this used to reference, was never
     * migrated -- it doesn't exist. (BookingService::createBooking()/
     * updateBooking() and BookingPricingService also reference it and are
     * equally broken, but dead: nothing calls them. The live booking flow,
     * BookingController::store(), never sets a type at all; it relies on
     * `bookings.booking_type`'s DB default of 'appointment'.) That column
     * -- a plain enum, not a foreign key -- is the one actually populated
     * on every real booking, and its values (appointment/reservation/
     * rental/consultation/class/event) are exactly this model's expected
     * `name` values (see supportsFeature() and getIconHtmlAttribute()
     * below, both keyed the same way). Matching on it by name is the
     * correct fix, using data that already exists, rather than inventing
     * a booking_type_id column nothing would ever populate.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'booking_type', 'name');
    }

    // A services() relation used to live here (hasMany(SalonService::class)),
    // but salon_services has no booking_type_id column and never has -- the
    // hasMany's implicit FK guess doesn't correspond to anything real.
    // Unlike bookings() above, there's no existing column to match on
    // instead. What actually connects a customer to a BookingType now is
    // the browse-by-type flow (BookingTypeController, routes: booking-
    // types.index/.show) -- appointment/consultation list SalonServices
    // and hand off into the existing salon-service booking flow, rental
    // lists BookingResources and hands off into
    // ResourceBookingController's own create/store. That superseded the
    // previous AJAX/Alpine scaffold this comment used to describe
    // (BookingController's api.bookings.* endpoints,
    // <x-booking.booking-form>, bookings/vendor-booking.blade.php) --
    // removed rather than kept alongside a second, working
    // implementation; see BookingTypesAndResourceBookingTest for coverage
    // and the commit that added it for why the old scaffold couldn't
    // just be repaired in place (its <form> posted to a hardcoded,
    // always-404 service id).

    /**
     * Scope to get only active booking types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Check if a booking type supports a specific feature.
     */
    public function supportsFeature(string $feature): bool
    {
        $features = [
            'appointment' => ['time_slots', 'staff_required', 'duration'],
            'reservation' => ['period_based', 'duration', 'capacity'],
            'rental' => ['daily_slots', 'inventory', 'quantity'],
            'consultation' => ['time_slots', 'staff_required', 'duration'],
            'class' => ['session_based', 'staff_required', 'max_participants'],
            'event' => ['date_range', 'staff_required', 'venue_capacity'],
        ];

        return in_array($feature, $features[$this->name] ?? []);
    }

    /**
     * Get the icon HTML.
     */
    public function getIconHtmlAttribute(): string
    {
        $icons = [
            'appointment' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
            'reservation' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
            'rental' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            'consultation' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>',
            'class' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
            'event' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
        ];

        return $icons[$this->name] ?? '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
    }
}
