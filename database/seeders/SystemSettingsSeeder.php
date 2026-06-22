<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'minimum_withdrawal_amount',
                'value' => '50',
                'type' => 'number',
            ],
            [
                'key' => 'withdrawal_fee_percentage',
                'value' => '2',
                'type' => 'number',
            ],
            [
                'key' => 'platform_commission_rate',
                'value' => '10',
                'type' => 'number',
            ],
            [
                'key' => 'max_withdrawal_amount',
                'value' => '10000',
                'type' => 'number',
            ],
            [
                'key' => 'withdrawal_processing_days',
                'value' => '3',
                'type' => 'number',
            ],
            [
                'key' => 'auto_approve_withdrawals',
                'value' => 'false',
                'type' => 'boolean',
            ],
            [
                'key' => 'rma_return_window_days',
                'value' => '30',
                'type' => 'number',
            ],
            [
                'key' => 'rma_warranty_period_months',
                'value' => '12',
                'type' => 'number',
            ],
            [
                'key' => 'low_stock_threshold',
                'value' => '5',
                'type' => 'number',
            ],
            [
                'key' => 'shipment_default_carrier',
                'value' => 'DHL',
                'type' => 'string',
            ],
            [
                'key' => 'shipment_supported_carriers',
                'value' => json_encode(['DHL', 'FedEx', 'UPS', 'USPS', 'Aramex']),
                'type' => 'array',
            ],
            [
                'key' => 'shipping_enabled',
                'value' => 'true',
                'type' => 'boolean',
            ],
            [
                'key' => 'shipping_mode',
                'value' => 'flat',
                'type' => 'string',
            ],
            [
                'key' => 'shipping_flat_fee',
                'value' => '0',
                'type' => 'float',
            ],
            [
                'key' => 'shipping_free_threshold',
                'value' => '0',
                'type' => 'float',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                ]
            );
        }

        $this->command->info('System settings seeded successfully!');
    }
}
