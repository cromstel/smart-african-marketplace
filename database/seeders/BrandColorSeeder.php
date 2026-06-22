<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class BrandColorSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'brand_color_from'],
            ['value' => '#ea580c', 'type' => 'string']
        );

        Setting::updateOrCreate(
            ['key' => 'brand_color_to'],
            ['value' => '#dc2626', 'type' => 'string']
        );

        $this->command->info('✅ Brand colors set: #ea580c (orange) -> #dc2626 (red)');
    }
}
