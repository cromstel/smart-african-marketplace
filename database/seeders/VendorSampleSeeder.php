<?php

namespace Database\Seeders;

use App\Models\MediaFolder;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Services\ImageOptimizationService;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorSampleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@africanmarket.com')->first()
            ?? User::where('role', 'admin')->first();

        if (! $admin) {
            return;
        }

        Auth::login($admin);

        $seedFolder = MediaFolder::where('slug', 'seeded')->first();
        $seedFolderId = $seedFolder?->id;

        $faker = Factory::create();
        $categories = VendorCategory::all();

        foreach ($categories as $category) {
            for ($i = 1; $i <= 12; $i++) {
                $email = 'vendor+'.$category->slug.'-'.$i.'@example.com';
                $name = $category->name.' Vendor '.$i;

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => Hash::make('Password123!'),
                        'role' => 'vendor',
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]
                );

                $businessName = $name.' Store';
                $vendor = Vendor::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'vendor_category_id' => $category->id,
                        'business_name' => $businessName,
                        'slug' => $this->uniqueSlug($businessName, $user->id),
                        'business_email' => $email,
                        'business_phone' => $faker->phoneNumber,
                        'business_address' => $faker->address,
                        'business_registration_number' => strtoupper(Str::random(10)),
                        'tax_id' => strtoupper(Str::random(8)),
                        'description' => $faker->sentence(12),
                        'commission_rate' => 12.50,
                        'status' => 'approved',
                        'is_active' => true,
                        'approved_at' => now(),
                    ]
                );

                if (! $vendor->logo) {
                    $file = SeedImageHelper::makeImage($vendor->business_name, 600, 600);
                    $result = app(ImageOptimizationService::class)->uploadAndOptimize(
                        $file,
                        'vendors/'.$vendor->id,
                        600,
                        600,
                        'vendor_logo',
                        $vendor->id,
                        true,
                        $seedFolderId
                    );
                    $vendor->update(['logo' => $result['original']]);
                    @unlink($file->getRealPath());
                }
            }
        }

        Auth::logout();
    }

    private function uniqueSlug(string $businessName, int $userId): string
    {
        $baseSlug = Str::slug($businessName);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Vendor::where('slug', $slug)
                ->where('user_id', '!=', $userId)
                ->exists()
        ) {
            $counter++;
            $slug = $baseSlug.'-'.$counter;
        }

        return $slug;
    }
}
