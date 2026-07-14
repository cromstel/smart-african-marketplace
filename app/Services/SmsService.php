<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsService
{
    protected $client;

    protected $fromNumber;

    protected $enabled;

    public function __construct()
    {
        $accountSid = Setting::get('twilio_account_sid');
        $authToken = Setting::get('twilio_auth_token');
        $this->fromNumber = Setting::get('twilio_phone_number');
        $this->enabled = Setting::get('sms_enabled', false);

        if ($accountSid && $authToken && $this->enabled) {
            try {
                $this->client = new Client($accountSid, $authToken);
            } catch (\Exception $e) {
                Log::error('Twilio client initialization failed: '.$e->getMessage());
                $this->enabled = false;
            }
        }
    }

    public function sendOrderConfirmation(string $phoneNumber, string $orderNumber, float $total)
    {
        $message = "Your order #{$orderNumber} has been confirmed! Total: $".number_format($total, 2).'. Thank you for shopping at ' . config('app.name') . '!';

        return $this->send($phoneNumber, $message);
    }

    public function sendOrderShipped(string $phoneNumber, string $orderNumber, ?string $trackingNumber = null)
    {
        $message = "Great news! Your order #{$orderNumber} has been shipped.";
        if ($trackingNumber) {
            $message .= " Tracking: {$trackingNumber}";
        }
        $message .= ' You will receive it soon. - ' . config('app.name');

        return $this->send($phoneNumber, $message);
    }

    public function sendOrderDelivered(string $phoneNumber, string $orderNumber)
    {
        $message = "Your order #{$orderNumber} has been delivered! We hope you enjoy your purchase. - " . config('app.name');

        return $this->send($phoneNumber, $message);
    }

    public function sendBookingConfirmation(string $phoneNumber, string $serviceName, string $bookingDate)
    {
        $message = "Booking confirmed for {$serviceName} on {$bookingDate}. We look forward to seeing you! - " . config('app.name');

        return $this->send($phoneNumber, $message);
    }

    public function sendBookingReminder(string $phoneNumber, string $serviceName, string $bookingDate)
    {
        $message = "Reminder: You have a booking for {$serviceName} tomorrow at {$bookingDate}. See you soon! - " . config('app.name');

        return $this->send($phoneNumber, $message);
    }

    public function sendBookingCancellation(string $phoneNumber, string $serviceName, string $bookingDate)
    {
        $message = "Your booking for {$serviceName} on {$bookingDate} has been cancelled. Please contact us if you need help. - " . config('app.name');

        return $this->send($phoneNumber, $message);
    }

    public function send(string $to, string $message)
    {
        if (! $this->enabled || ! $this->client || ! $this->fromNumber) {
            Log::info('SMS not sent (disabled or not configured): '.$message);

            return false;
        }

        if (! $this->isValidPhoneNumber($to)) {
            Log::error("Invalid phone number format: {$to}");

            return false;
        }

        try {
            $this->client->messages->create(
                $to,
                [
                    'from' => $this->fromNumber,
                    'body' => $message,
                ]
            );

            Log::info("SMS sent to {$to}: {$message}");

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send SMS to {$to}: ".$e->getMessage());

            return false;
        }
    }

    protected function isValidPhoneNumber(string $phone): bool
    {
        $pattern = '/^\+?[1-9]\d{1,14}$/';

        return preg_match($pattern, $phone) === 1;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
