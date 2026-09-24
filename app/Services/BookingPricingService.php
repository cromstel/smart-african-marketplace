<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingType;
use App\Models\ServicePricingTier;
use Carbon\Carbon;

class BookingPricingService
{
    /**
     * Calculate pricing for a booking
     */
    public function calculatePricing(array $data): array
    {
        $bookingType = $data['booking_type'] ?? 'appointment';
        $quantity = $data['quantity'] ?? 1;
        $duration = $data['duration_minutes'] ?? 60;
        $basePrice = $data['base_price'] ?? 0;

        // Get applicable pricing tier
        $pricingTier = $this->getApplicablePricingTier(
            $data['vendor_id'] ?? null,
            $data['service_id'] ?? null,
            Carbon::parse($data['booking_date'] ?? now()),
            Carbon::parse($data['start_time'] ?? now())
        );

        // Calculate base subtotal
        $subtotal = $this->calculateSubtotal($bookingType, $basePrice, $quantity, $duration);

        // Apply pricing tier adjustments
        if ($pricingTier) {
            $subtotal = $this->applyPricingTier($subtotal, $pricingTier);
        }

        // Calculate tax
        $taxRate = $data['tax_rate'] ?? config('settings.tax_rate', 0);
        $taxAmount = $this->calculateTax($subtotal, $taxRate);

        // Apply discount if provided
        $discountAmount = $data['discount_amount'] ?? 0;

        // Calculate total
        $total = $subtotal + $taxAmount - $discountAmount;

        // Calculate deposit if applicable
        $depositAmount = 0;
        if ($this->requiresDeposit($bookingType, $total)) {
            $depositPercentage = $this->getDepositPercentage($bookingType);
            $depositAmount = round($total * ($depositPercentage / 100), 2);
        }

        return [
            'unit_price' => $basePrice,
            'quantity' => $quantity,
            'duration_minutes' => $duration,
            'subtotal' => round($subtotal, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2),
            'discount_amount' => round($discountAmount, 2),
            'total_amount' => round($total, 2),
            'deposit_amount' => round($depositAmount, 2),
            'pricing_tier_id' => $pricingTier?->id,
            'pricing_tier_name' => $pricingTier?->name,
        ];
    }

    /**
     * Calculate subtotal based on booking type
     */
    protected function calculateSubtotal(string $type, float $basePrice, int $quantity, int $duration): float
    {
        return match ($type) {
            'rental' => $this->calculateRentalPrice($basePrice, $duration) * $quantity,
            'reservation' => $this->calculateReservationPrice($basePrice, $duration) * $quantity,
            'class' => $this->calculateClassPrice($basePrice, $quantity),
            'event' => $this->calculateEventPrice($basePrice, $duration, $quantity),
            default => $basePrice * max(1, ceil($duration / 60)), // Appointment/Consultation: hourly rounding
        };
    }

    /**
     * Calculate rental price (hourly or daily)
     */
    protected function calculateRentalPrice(float $hourlyRate, int $durationMinutes): float
    {
        if ($durationMinutes >= 1440) {
            // Daily rate (24 hours)
            $days = ceil($durationMinutes / 1440);

            return $hourlyRate * 24 * $days * 0.8; // 20% discount for daily
        }

        // Hourly rate
        $hours = ceil($durationMinutes / 60);

        return $hourlyRate * $hours;
    }

    /**
     * Calculate reservation price (usually flat rate for duration)
     */
    protected function calculateReservationPrice(float $basePrice, int $durationMinutes): float
    {
        $hours = ceil($durationMinutes / 60);

        return $basePrice * $hours;
    }

    /**
     * Calculate class price (per person)
     */
    protected function calculateClassPrice(float $basePrice, int $quantity): float
    {
        return $basePrice * $quantity;
    }

    /**
     * Calculate event price (multi-factor)
     */
    protected function calculateEventPrice(float $basePrice, int $durationMinutes, int $quantity): float
    {
        $hours = ceil($durationMinutes / 60);

        // Events have base + hourly + per-person components
        $hourlyComponent = $basePrice * $hours;

        // Add per-person charge if quantity > base (e.g., > 10 guests)
        $perPersonCharge = max(0, $quantity - 10) * ($basePrice * 0.1);

        return $hourlyComponent + $perPersonCharge;
    }

    /**
     * Get applicable pricing tier for the given time
     */
    public function getApplicablePricingTier(?int $vendorId, ?int $serviceId, Carbon $date, Carbon $time): ?ServicePricingTier
    {
        $dayOfWeek = $date->dayOfWeek;
        $hour = (int) $time->format('H');

        $query = ServicePricingTier::where('is_active', true)
            ->where(function ($q) use ($vendorId, $serviceId) {
                $q->where('vendor_id', $vendorId)
                    ->orWhere('service_id', $serviceId)
                    ->orWhereNull('vendor_id');
            })
            ->where(function ($q) use ($dayOfWeek) {
                $q->whereJsonContains('days_of_week', (string) $dayOfWeek)
                    ->orWhereJsonContains('days_of_week', '0'); // All days
            })
            ->where(function ($q) use ($hour) {
                $q->where(function ($q2) use ($hour) {
                    $q2->where('start_hour', '<=', $hour)
                        ->where('end_hour', '>=', $hour);
                })->orWhereNull('start_hour');
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('discount_percentage', 'desc')
            ->first();

        return $query;
    }

    /**
     * Apply pricing tier discount/premium
     */
    protected function applyPricingTier(float $price, ServicePricingTier $tier): float
    {
        if ($tier->discount_percentage > 0) {
            // Apply discount
            return $price * (1 - ($tier->discount_percentage / 100));
        }

        if ($tier->premium_percentage > 0) {
            // Apply premium
            return $price * (1 + ($tier->premium_percentage / 100));
        }

        return $price;
    }

    /**
     * Calculate tax amount
     */
    protected function calculateTax(float $subtotal, float $taxRate): float
    {
        return $subtotal * ($taxRate / 100);
    }

    /**
     * Check if deposit is required for booking type
     */
    public function requiresDeposit(string $bookingType, float $totalAmount): bool
    {
        $bookingTypeModel = BookingType::where('slug', $bookingType)->first();

        if (! $bookingTypeModel) {
            return false;
        }

        // Require deposit for large events and rentals
        $minAmountForDeposit = match ($bookingType) {
            'event' => 500,
            'rental' => 200,
            default => 0,
        };

        return $totalAmount >= $minAmountForDeposit;
    }

    /**
     * Get deposit percentage for booking type
     */
    public function getDepositPercentage(string $bookingType): float
    {
        return match ($bookingType) {
            'event' => 50,
            'rental' => 30,
            'class' => 100, // Full payment for classes
            default => 0,
        };
    }

    /**
     * Calculate refund amount for cancellation
     */
    public function calculateRefund(Booking $booking, ?Carbon $cancelledAt = null): array
    {
        $cancelledAt = $cancelledAt ?? now();
        $bookingDate = Carbon::parse($booking->booking_date);

        $hoursUntilBooking = $cancelledAt->diffInHours($bookingDate);

        $totalPaid = $booking->total_amount;
        $refundPercentage = 100;

        // Calculate refund based on cancellation timing
        if ($hoursUntilBooking >= 48) {
            $refundPercentage = 100; // Full refund
        } elseif ($hoursUntilBooking >= 24) {
            $refundPercentage = 50; // 50% refund
        } elseif ($hoursUntilBooking >= 12) {
            $refundPercentage = 25; // 25% refund
        } else {
            $refundPercentage = 0; // No refund
        }

        // Check booking type for custom cancellation policy
        $bookingType = $booking->bookingType;
        if ($bookingType && $bookingType->cancellation_policy_hours) {
            $policyHours = $bookingType->cancellation_policy_hours;

            if ($hoursUntilBooking >= $policyHours) {
                $refundPercentage = 100;
            } elseif ($hoursUntilBooking >= ($policyHours / 2)) {
                $refundPercentage = 50;
            }
        }

        $refundAmount = $totalPaid * ($refundPercentage / 100);

        return [
            'total_paid' => $totalPaid,
            'refund_percentage' => $refundPercentage,
            'refund_amount' => round($refundAmount, 2),
            'hours_until_booking' => $hoursUntilBooking,
        ];
    }

    /**
     * Get price breakdown for display
     */
    public function getPriceBreakdown(Booking $booking): array
    {
        return [
            'booking_type' => $booking->booking_type,
            'quantity' => $booking->quantity,
            'duration' => $booking->duration_minutes,
            'unit_price' => $booking->unit_price,
            'subtotal' => $booking->subtotal,
            'tax_amount' => $booking->tax_amount,
            'discount_amount' => $booking->discount_amount,
            'total' => $booking->total_amount,
            'deposit_required' => $booking->deposit_amount > 0,
            'deposit_amount' => $booking->deposit_amount,
            'deposit_paid' => $booking->deposit_paid,
            'payment_status' => $booking->payment_status,
        ];
    }
}
