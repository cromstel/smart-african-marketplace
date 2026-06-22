<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MediaFolder;
use App\Models\SalonService;
use App\Models\SalonServiceCategory;
use App\Models\User;
use App\Services\ImageOptimizationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SalonServiceSeeder extends Seeder
{
    public function run(): void
    {
        $owners = User::whereIn('role', ['salon_owner', 'vendor'])->get();

        if ($owners->isEmpty()) {
            return;
        }

        $salonCategories = Category::where('type', 'salon')->whereNotNull('parent_id')->get();
        $serviceCategories = SalonServiceCategory::active()->get();

        if ($salonCategories->isEmpty() || $serviceCategories->isEmpty()) {
            return;
        }

        $admin = User::where('email', 'admin@africanmarket.com')->first()
            ?? User::where('role', 'admin')->first();

        if ($admin) {
            Auth::login($admin);
        }

        $seedFolderId = MediaFolder::where('slug', 'seeded')->value('id');
        $imageService = app(ImageOptimizationService::class);

        foreach ($serviceCategories as $serviceCategory) {
            for ($i = 1; $i <= 12; $i++) {
                $owner = $owners->random();
                $category = $salonCategories->random();
                $name = $serviceCategory->name.' Service '.$i;
                $slug = Str::slug($serviceCategory->slug.'-'.$i);

                $service = SalonService::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'user_id' => $owner->id,
                        'salon_category_id' => $serviceCategory->id,
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => 'Sample service for '.$serviceCategory->name.'.',
                        'price' => 40 + $i,
                        'duration_minutes' => 60 + ($i * 5),
                        'is_active' => true,
                    ]
                );

                if (! $service->image) {
                    $file = SeedImageHelper::makeImage($name, 800, 600);
                    $result = $imageService->uploadAndOptimize(
                        $file,
                        'salon-services/'.$service->id,
                        800,
                        600,
                        'salon_service',
                        $service->id,
                        true,
                        $seedFolderId
                    );
                    $service->update(['image' => $result['original']]);
                    @unlink($file->getRealPath());
                }
            }
        }

        if ($admin) {
            Auth::logout();
        }
    }
}
