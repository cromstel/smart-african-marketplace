<?php

namespace App\Services\Shipping;

use App\Models\Order;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\Vendor;
use Illuminate\Support\Facades\Http;

class ShippingLabelService
{
    public function generateLabel(Shipment $shipment): array
    {
        $provider = Setting::get('shipping_label_provider', 'manual');
        if ($provider === 'manual') {
            return [
                'success' => false,
                'message' => 'Label provider not configured.',
            ];
        }

        $order = $shipment->order()->with(['shippingAddress', 'items.product.vendor'])->first();
        if (! $order || ! $order->shippingAddress) {
            return [
                'success' => false,
                'message' => 'Shipping address missing.',
            ];
        }

        $vendor = $this->resolveVendor($order);
        if (! $vendor || ! $this->hasVendorAddress($vendor)) {
            return [
                'success' => false,
                'message' => 'Vendor address missing for label generation.',
            ];
        }

        $parcel = [
            'length' => (float) Setting::get('label_parcel_length', 10),
            'width' => (float) Setting::get('label_parcel_width', 8),
            'height' => (float) Setting::get('label_parcel_height', 4),
            'distance_unit' => (string) Setting::get('label_parcel_distance_unit', 'in'),
            'weight' => (float) Setting::get('label_parcel_weight', 2),
            'mass_unit' => (string) Setting::get('label_parcel_mass_unit', 'lb'),
        ];

        if ($provider === 'shippo') {
            return $this->generateShippoLabel($shipment, $order, $vendor, $parcel);
        }

        if ($provider === 'easypost') {
            return $this->generateEasyPostLabel($shipment, $order, $vendor, $parcel);
        }

        return [
            'success' => false,
            'message' => 'Unsupported label provider.',
        ];
    }

    private function resolveVendor(Order $order): ?Vendor
    {
        $item = $order->items->first();
        if ($item && $item->product && $item->product->vendor_id) {
            return Vendor::find($item->product->vendor_id);
        }

        return $order->items->first()?->vendor;
    }

    private function hasVendorAddress(Vendor $vendor): bool
    {
        return ! empty($vendor->business_address)
            && ! empty($vendor->city)
            && ! empty($vendor->state)
            && ! empty($vendor->postal_code)
            && ! empty($vendor->country);
    }

    private function generateShippoLabel(Shipment $shipment, Order $order, Vendor $vendor, array $parcel): array
    {
        $apiKey = Setting::get('shippo_api_key');
        if (! $apiKey) {
            return [
                'success' => false,
                'message' => 'Shippo API key missing.',
            ];
        }

        $baseUrl = 'https://api.goshippo.com';
        $from = [
            'name' => $vendor->business_name,
            'street1' => $vendor->business_address,
            'city' => $vendor->city,
            'state' => $vendor->state,
            'zip' => $vendor->postal_code,
            'country' => $vendor->country,
            'phone' => $vendor->business_phone,
            'email' => $vendor->business_email,
        ];

        $toAddress = $order->shippingAddress;
        $to = [
            'name' => $toAddress->full_name,
            'street1' => $toAddress->address_line1,
            'street2' => $toAddress->address_line2,
            'city' => $toAddress->city,
            'state' => $toAddress->state,
            'zip' => $toAddress->postal_code,
            'country' => $toAddress->country,
            'phone' => $toAddress->phone,
            'email' => $order->user?->email,
        ];

        try {
            $shipmentResponse = Http::withToken($apiKey)->post($baseUrl.'/shipments/', [
                'address_from' => $from,
                'address_to' => $to,
                'parcels' => [[
                    'length' => $parcel['length'],
                    'width' => $parcel['width'],
                    'height' => $parcel['height'],
                    'distance_unit' => $parcel['distance_unit'],
                    'weight' => $parcel['weight'],
                    'mass_unit' => $parcel['mass_unit'],
                ]],
            ])->throw()->json();

            $rates = $shipmentResponse['rates'] ?? [];
            $rate = $rates[0] ?? null;
            if (! $rate || ! isset($rate['object_id'])) {
                return [
                    'success' => false,
                    'message' => 'No Shippo rates available.',
                ];
            }

            $transaction = Http::withToken($apiKey)->post($baseUrl.'/transactions/', [
                'rate' => $rate['object_id'],
                'label_file_type' => 'PDF',
            ])->throw()->json();

            if (($transaction['status'] ?? '') !== 'SUCCESS') {
                return [
                    'success' => false,
                    'message' => $transaction['messages'][0]['text'] ?? 'Shippo label creation failed.',
                ];
            }

            return [
                'success' => true,
                'label_url' => $transaction['label_url'] ?? null,
                'provider' => 'shippo',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Shippo error: '.$e->getMessage(),
            ];
        }
    }

    private function generateEasyPostLabel(Shipment $shipment, Order $order, Vendor $vendor, array $parcel): array
    {
        $apiKey = Setting::get('easypost_api_key');
        if (! $apiKey) {
            return [
                'success' => false,
                'message' => 'EasyPost API key missing.',
            ];
        }

        $baseUrl = 'https://api.easypost.com/v2';
        $toAddress = $order->shippingAddress;

        try {
            $shipmentResponse = Http::withBasicAuth($apiKey, '')->post($baseUrl.'/shipments', [
                'shipment' => [
                    'to_address' => [
                        'name' => $toAddress->full_name,
                        'street1' => $toAddress->address_line1,
                        'street2' => $toAddress->address_line2,
                        'city' => $toAddress->city,
                        'state' => $toAddress->state,
                        'zip' => $toAddress->postal_code,
                        'country' => $toAddress->country,
                        'phone' => $toAddress->phone,
                        'email' => $order->user?->email,
                    ],
                    'from_address' => [
                        'name' => $vendor->business_name,
                        'street1' => $vendor->business_address,
                        'city' => $vendor->city,
                        'state' => $vendor->state,
                        'zip' => $vendor->postal_code,
                        'country' => $vendor->country,
                        'phone' => $vendor->business_phone,
                        'email' => $vendor->business_email,
                    ],
                    'parcel' => [
                        'length' => $parcel['length'],
                        'width' => $parcel['width'],
                        'height' => $parcel['height'],
                        'weight' => $parcel['weight'],
                    ],
                ],
            ])->throw()->json();

            $rates = $shipmentResponse['rates'] ?? [];
            $rate = collect($rates)->sortBy('rate')->first();
            if (! $rate || ! isset($rate['id'])) {
                return [
                    'success' => false,
                    'message' => 'No EasyPost rates available.',
                ];
            }

            $buyResponse = Http::withBasicAuth($apiKey, '')->post($baseUrl.'/shipments/'.$shipmentResponse['id'].'/buy', [
                'rate' => [
                    'id' => $rate['id'],
                ],
            ])->throw()->json();

            $labelUrl = $buyResponse['postage_label']['label_url'] ?? null;
            if (! $labelUrl) {
                return [
                    'success' => false,
                    'message' => 'EasyPost label missing from response.',
                ];
            }

            return [
                'success' => true,
                'label_url' => $labelUrl,
                'provider' => 'easypost',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'EasyPost error: '.$e->getMessage(),
            ];
        }
    }
}
