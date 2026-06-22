<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payment\PaystackPaymentService;
use App\Services\Payment\StripePaymentService;

class BookingPaymentService
{
    public function __construct(
        protected StripePaymentService $stripeService,
        protected PaystackPaymentService $paystackService,
        protected ?SwishService $swishService = null
    ) {
        $this->swishService = $swishService ?? new SwishService;
    }

    /**
     * Process payment for a booking
     */
    public function processPayment(Booking $booking, string $provider, array $paymentData = []): Payment
    {
        $user = $booking->user;

        if (! $user) {
            throw new \InvalidArgumentException('Booking must have a user');
        }

        // Determine currency based on payment provider
        $currency = match ($provider) {
            'swish' => 'SEK',  // Swish always uses Swedish Krona
            default => config('currency.code', 'USD'),
        };

        $paymentData = array_merge($paymentData, [
            'amount' => $booking->price * 100, // Convert to cents
            'currency' => $currency,
            'description' => "Booking #{$booking->booking_number}",
            'metadata' => [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'customer_email' => $user?->email,
            ],
        ]);

        $result = match ($provider) {
            'stripe' => $this->stripeService->createPaymentIntent($paymentData),
            'paystack' => $this->paystackService->createPayment($paymentData),
            'swish' => $this->swishService ? $this->handleSwishBookingPayment($booking, $paymentData) : throw new \InvalidArgumentException('Swish not configured'),
            default => throw new \InvalidArgumentException("Payment provider {$provider} not supported"),
        };

        return Payment::create([
            'booking_id' => $booking->id,
            'user_id' => $user?->id,
            'provider' => $provider,
            'amount' => $booking->price,
            'currency' => $paymentData['currency'],
            'status' => 'pending',
            'transaction_id' => $result['transaction_id'] ?? null,
            'payment_reference' => $result['reference'] ?? null,
            'metadata' => $result['metadata'] ?? [],
        ]);
    }

    /**
     * Verify payment status
     */
    public function verifyPayment(Payment $payment): bool
    {
        $result = match ($payment->provider) {
            'stripe' => $this->stripeService->retrievePaymentIntent($payment->transaction_id),
            'paystack' => $this->paystackService->verifyTransaction($payment->payment_reference),
            default => throw new \InvalidArgumentException("Payment provider {$payment->provider} not supported"),
        };

        $isSuccessful = $result['status'] === 'succeeded' || $result['status'] === 'success';

        if ($isSuccessful) {
            $payment->update([
                'status' => 'completed',
                'metadata' => array_merge($payment->metadata ?? [], ['verified_at' => now()->toISOString()]),
            ]);

            // Update booking status
            $payment->booking->update(['status' => 'confirmed']);
        }

        return $isSuccessful;
    }

    /**
     * Process refund for a cancelled booking
     */
    public function processRefund(Booking $booking, ?float $amount = null): bool
    {
        $payment = $booking->payments()->where('status', 'completed')->first();

        if (! $payment) {
            return false;
        }

        $refundAmount = $amount ?? $payment->amount;

        $result = match ($payment->provider) {
            'stripe' => $this->stripeService->createRefund([
                'payment_intent' => $payment->transaction_id,
                'amount' => (int) ($refundAmount * 100),
            ]),
            'paystack' => $this->paystackService->refundTransaction($payment->payment_reference, (int) ($refundAmount * 100)),
            default => throw new \InvalidArgumentException("Payment provider {$payment->provider} not supported"),
        };

        if ($result['status'] === 'succeeded' || $result['status'] === 'success') {
            Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'provider' => $payment->provider,
                'amount' => -$refundAmount,
                'currency' => $payment->currency,
                'status' => 'refunded',
                'transaction_id' => $result['refund_id'] ?? null,
                'payment_reference' => $payment->payment_reference,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Get payment initialization URL
     */
    public function getPaymentUrl(Payment $payment): string
    {
        return match ($payment->provider) {
            'stripe' => $this->stripeService->getPaymentUrl($payment),
            'paystack' => $this->paystackService->getPaymentUrl($payment),
            default => throw new \InvalidArgumentException("Payment provider {$payment->provider} not supported"),
        };
    }

    /**
     * Calculate refund amount based on cancellation policy
     */
    public function calculateRefundAmount(Booking $booking): float
    {
        $hoursUntilBooking = now()->diffInHours($booking->booking_date);

        // Full refund if cancelled 24+ hours before
        if ($hoursUntilBooking >= 24) {
            return (float) $booking->price;
        }

        // 50% refund if cancelled 12-24 hours before
        if ($hoursUntilBooking >= 12) {
            return (float) $booking->price * 0.5;
        }

        // No refund if cancelled less than 12 hours before
        return 0.0;
    }

    /**
     * Handle Swish payment for bookings
     * SwishService::createPaymentRequest() expects Order, but we're using Booking
     * Create a simple pending payment - actual Swish payment processed via SwishPaymentController
     */
    protected function handleSwishBookingPayment(Booking $booking, array $paymentData): array
    {
        // Swish uses Order, not Booking - create a simple pending payment
        // The actual Swish payment will be processed separately via SwishPaymentController
        return [
            'transaction_id' => 'SWISH-PENDING-'.$booking->booking_number,
            'status' => 'pending',
        ];
    }
}
