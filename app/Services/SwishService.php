<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SwishService
{
    private string $environment;

    private string $merchantId;

    private ?string $certificatePath;

    private ?string $certificatePassword;

    private string $callbackUrl;

    // Nordic country codes
    private const NORDIC_COUNTRIES = ['SE', 'NO', 'DK', 'FI', 'IS'];

    public function __construct()
    {
        $this->environment = Setting::getValue('swish_environment', 'sandbox');
        $this->merchantId = Setting::getValue('swish_merchant_id', '');
        $this->certificatePath = Setting::getValue('swish_certificate');
        $this->certificatePassword = Setting::getValue('swish_private_key');
        $this->callbackUrl = rtrim(config('app.url'), '/').'/webhooks/swish/callback';
    }

    /**
     * Check if SWISH is enabled and configured
     */
    public function isEnabled(): bool
    {
        return in_array(Setting::getValue('swish_enabled'), [true, 'true', '1', 1], true)
            && ! empty($this->merchantId);
    }

    /**
     * Check if a country code is in the Nordic region
     */
    public static function isNordicCountry(?string $countryCode): bool
    {
        if (! $countryCode) {
            return false;
        }

        return in_array(strtoupper($countryCode), self::NORDIC_COUNTRIES);
    }

    /**
     * Get list of Nordic country codes
     */
    public static function getNordicCountries(): array
    {
        return self::NORDIC_COUNTRIES;
    }

    /**
     * Get the base URL for SWISH API based on environment
     */
    private function getBaseUrl(): string
    {
        return $this->environment === 'production'
            ? config('services.swish.production_url', 'https://swish.paymentiq.io')
            : config('services.swish.sandbox_url', 'https://sandbox.swishpayment.com');
    }

    /**
     * Create a SWISH payment request
     */
    public function createPaymentRequest(Order $order, string $payerPhone): array
    {
        $instructionUUID = Str::uuid()->toString();
        $amount = number_format((float) $order->total, 2, '.', '');
        $currency = 'SEK'; // SWISH always uses Swedish Krona
        $baseUrl = $this->getBaseUrl();

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->post($baseUrl.'/api/v1/paymentrequests', [
                    'instructionUUID' => $instructionUUID,
                    'amount' => $amount,
                    'currency' => $currency,
                    'payeeAlias' => $this->merchantId,
                    'payeeNote' => "Order #{$order->order_number}",
                    'payerAlias' => $payerPhone,
                    'message' => "Payment for order {$order->order_number}",
                    'callbackUrl' => $this->callbackUrl,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'payment_request_id' => $data['id'] ?? $instructionUUID,
                    'instruction_uuid' => $instructionUUID,
                    'status' => $data['status'] ?? 'PENDING',
                ];
            }

            Log::error('SWISH payment request failed', [
                'order_id' => $order->id,
                'response' => $response->json(),
            ]);

            $errorData = $response->json();

            return [
                'success' => false,
                'error' => $errorData['message'] ?? 'Failed to create SWISH payment request',
            ];
        } catch (\Exception $e) {
            Log::error('SWISH payment request exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to process SWISH payment: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $paymentRequestId): array
    {
        $baseUrl = $this->getBaseUrl();

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->get($baseUrl.'/api/v1/paymentrequests/'.$paymentRequestId);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'UNKNOWN',
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to check payment status',
            ];
        } catch (\Exception $e) {
            Log::error('SWISH status check exception', [
                'payment_request_id' => $paymentRequestId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate QR code data for SWISH payment
     */
    public function generateQRCodeData(Order $order): string
    {
        if (empty($this->merchantId)) {
            throw new \RuntimeException('SWISH merchant ID is not configured');
        }

        $merchantToken = $this->generateMerchantToken();
        if (empty($merchantToken)) {
            throw new \RuntimeException('SWISH merchant token is not configured. Please configure swish_merchant_token in settings.');
        }

        // SWISH QR code format (PME - Payment with easy entry)
        $data = [
            'version' => '1.0',
            'charset' => 'UTF-8',
            'encoding' => 'UTF-8',
            'txType' => 'PURCHASE',
            'txVer' => '1.0',
            'msgType' => 'PME',
            'merchantId' => $this->merchantId,
            'merchantToken' => $merchantToken,
            'amount' => number_format((float) $order->total, 2, '.', ''),
            'currency' => 'SEK',
            'specVer' => 'PME1.0',
            'payeeNote' => "Order {$order->order_number}",
        ];

        return 'swish:'.http_build_query($data);
    }

    /**
     * Generate merchant token for QR codes
     */
    private function generateMerchantToken(): string
    {
        return Setting::getValue('swish_merchant_token', '');
    }

    /**
     * Handle webhook callback from SWISH
     */
    public function handleCallback(array $data): array
    {
        $status = $data['status'] ?? '';

        // Map SWISH status to our payment status
        $paymentStatus = match ($status) {
            'PAID' => 'completed',
            'DECLINED' => 'failed',
            'CANCELLED' => 'cancelled',
            'ERROR' => 'failed',
            default => 'pending',
        };

        return [
            'payment_request_id' => $data['id'] ?? null,
            'status' => $paymentStatus,
            'original_status' => $status,
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? 'SEK',
        ];
    }
}
