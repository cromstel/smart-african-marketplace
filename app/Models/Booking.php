<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * `$fillable` used to include `booking_type_id`, `guest_count`,
 * `recurring_interval`, and `recurring_count` -- none of which are real
 * columns on `bookings` (confirmed via Schema::getColumnListing()).
 * Harmless as long as nothing explicitly passed those keys to create()/
 * update(), since Eloquent only inserts keys actually present in the
 * data array, but a landmine for the next call site that does (exactly
 * what BookingService::createBooking() did -- still broken, still
 * unreachable; nothing in the new booking-type/resource-booking flow
 * below calls it). Removed; `recurring_frequency`/`recurring_end_date`
 * (the columns that actually exist) added in their place.
 */
class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_number',
        'user_id',
        'salon_service_id',
        'salon_owner_id',
        'stylist_id',
        'booking_date',
        'booking_time',
        'booking_end_date',
        'duration_minutes',
        'price',
        'status',
        'customer_notes',
        'staff_notes',
        'cancelled_at',
        'completed_at',
        // New fields from migration
        'booking_type',
        'start_time',
        'end_time',
        'end_date',
        'quantity',
        'unit_price',
        'total_amount',
        'deposit_amount',
        'deposit_paid',
        'payment_status',
        'guest_name',
        'guest_email',
        'guest_phone',
        'is_recurring',
        'recurring_frequency',
        'recurring_end_date',
        'parent_booking_id',
        'resource_name',
        'resource_id',
        'custom_fields',
        'metadata',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_end_date' => 'datetime',
        'duration_minutes' => 'integer',
        'price' => 'decimal:2',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        // New casts
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'end_date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'deposit_paid' => 'boolean',
        'is_recurring' => 'boolean',
        'recurring_end_date' => 'date',
        'custom_fields' => 'array',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salonService(): BelongsTo
    {
        return $this->belongsTo(SalonService::class);
    }

    public function stylist(): BelongsTo
    {
        return $this->belongsTo(SalonStylist::class, 'stylist_id');
    }

    /**
     * Relationship to BookingType. Laravel's default belongsTo() foreign
     * key would be booking_type_id, which doesn't exist -- matches on the
     * real `booking_type` enum column against BookingType's `name` by
     * string instead, same fix as BookingType::bookings() (the inverse
     * side) uses.
     */
    public function bookingType(): BelongsTo
    {
        return $this->belongsTo(BookingType::class, 'booking_type', 'name');
    }

    /**
     * Relationship to BookingResource (for rentals)
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(BookingResource::class, 'resource_id');
    }

    /**
     * Parent booking for recurring bookings
     */
    public function parentBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'parent_booking_id');
    }

    /**
     * Child bookings for recurring series
     */
    public function childBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'parent_booking_id');
    }

    /**
     * Check if booking is for a specific type
     */
    public function isType(string $type): bool
    {
        return $this->booking_type === $type;
    }

    /**
     * Check if booking is an appointment type
     */
    public function isAppointment(): bool
    {
        return $this->booking_type === 'appointment';
    }

    /**
     * Check if booking is a rental type
     */
    public function isRental(): bool
    {
        return $this->booking_type === 'rental';
    }

    /**
     * Check if booking is a reservation type
     */
    public function isReservation(): bool
    {
        return $this->booking_type === 'reservation';
    }

    /**
     * Check if booking is a class type
     */
    public function isClass(): bool
    {
        return $this->booking_type === 'class';
    }

    /**
     * Check if booking is an event type
     */
    public function isEvent(): bool
    {
        return $this->booking_type === 'event';
    }

    /**
     * Check if booking is a consultation type
     */
    public function isConsultation(): bool
    {
        return $this->booking_type === 'consultation';
    }

    /**
     * Get the display name for the booking type
     */
    public function getBookingTypeNameAttribute(): string
    {
        return match ($this->booking_type) {
            'appointment' => 'Appointment',
            'reservation' => 'Reservation',
            'rental' => 'Rental',
            'consultation' => 'Consultation',
            'class' => 'Class',
            'event' => 'Event',
            default => 'Booking',
        };
    }

    /**
     * Check if this is a multi-day booking
     */
    public function isMultiDay(): bool
    {
        return $this->end_date && Carbon::parse($this->booking_date)->ne(Carbon::parse($this->end_date));
    }

    /**
     * Get total duration in minutes for multi-slot bookings
     */
    public function getTotalDurationMinutesAttribute(): int
    {
        if ($this->end_time && $this->start_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }

        return $this->duration_minutes ?? 0;
    }

    /**
     * Check if booking requires payment
     */
    public function requiresPayment(): bool
    {
        return $this->total_amount > 0;
    }

    /**
     * Check if deposit is required
     */
    public function requiresDeposit(): bool
    {
        return $this->deposit_amount > 0 && ! $this->deposit_paid;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
