<?php

namespace Database\Seeders;

use App\Models\VendorServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VendorServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // PARENT CATEGORIES (Top-level service types)
        // =====================================================================

        $beautyGrooming = VendorServiceCategory::updateOrCreate(
            ['slug' => 'beauty-grooming'],
            [
                'name' => 'Beauty & Grooming',
                'slug' => 'beauty-grooming',
                'description' => 'Hair, beauty, and personal grooming services for men and women',
                'category_type' => 'beauty',
                'gender_target' => 'unisex',
                'icon' => 'scissors',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $automotive = VendorServiceCategory::updateOrCreate(
            ['slug' => 'automotive'],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'description' => 'Car washing, repair, and maintenance services',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'car',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $homeServices = VendorServiceCategory::updateOrCreate(
            ['slug' => 'home-services'],
            [
                'name' => 'Home Services',
                'slug' => 'home-services',
                'description' => 'Painting, electrical, and home assistance services',
                'category_type' => 'home',
                'gender_target' => 'unisex',
                'icon' => 'home',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $commercialServices = VendorServiceCategory::updateOrCreate(
            ['slug' => 'commercial-services'],
            [
                'name' => 'Commercial Services',
                'slug' => 'commercial-services',
                'description' => 'Facility management, construction, and fleet services',
                'category_type' => 'commercial',
                'gender_target' => 'unisex',
                'icon' => 'building',
                'parent_id' => null,
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        // =====================================================================
        // SUB-CATEGORIES (Mapped to vendor types)
        // =====================================================================

        // Beauty & Grooming sub-categories
        $mensSalon = VendorServiceCategory::updateOrCreate(
            ['slug' => 'mens-salon-services'],
            [
                'name' => "Men's Salon Services",
                'slug' => 'mens-salon-services',
                'description' => "Professional men's grooming and barbering services",
                'category_type' => 'beauty',
                'gender_target' => 'men',
                'icon' => 'cut',
                'parent_id' => $beautyGrooming->id,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $womensSalon = VendorServiceCategory::updateOrCreate(
            ['slug' => 'womens-salon-services'],
            [
                'name' => "Women's Salon Services",
                'slug' => 'womens-salon-services',
                'description' => "Professional women's beauty and hair services",
                'category_type' => 'beauty',
                'gender_target' => 'women',
                'icon' => 'sparkles',
                'parent_id' => $beautyGrooming->id,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // Automotive sub-categories
        $carWashingBay = VendorServiceCategory::updateOrCreate(
            ['slug' => 'car-washing-bay'],
            [
                'name' => 'Car Washing Bay',
                'slug' => 'car-washing-bay',
                'description' => 'Car washing and detailing services',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'car',
                'parent_id' => $automotive->id,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $carGarage = VendorServiceCategory::updateOrCreate(
            ['slug' => 'car-garage'],
            [
                'name' => 'Car Garage',
                'slug' => 'car-garage',
                'description' => 'Car repair and maintenance services',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'wrench',
                'parent_id' => $automotive->id,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $mechanicServices = VendorServiceCategory::updateOrCreate(
            ['slug' => 'mechanic-services'],
            [
                'name' => 'Mechanic Services',
                'slug' => 'mechanic-services',
                'description' => 'General and fleet vehicle maintenance',
                'category_type' => 'automotive',
                'gender_target' => 'unisex',
                'icon' => 'wrench',
                'parent_id' => $automotive->id,
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        // Home Services sub-categories
        $painterServices = VendorServiceCategory::updateOrCreate(
            ['slug' => 'painter-services'],
            [
                'name' => 'Painter Services',
                'slug' => 'painter-services',
                'description' => 'Interior and exterior painting services',
                'category_type' => 'home',
                'gender_target' => 'unisex',
                'icon' => 'paint-brush',
                'parent_id' => $homeServices->id,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $electricianServices = VendorServiceCategory::updateOrCreate(
            ['slug' => 'electrician-services'],
            [
                'name' => 'Electrician Services',
                'slug' => 'electrician-services',
                'description' => 'Residential and commercial electrical services',
                'category_type' => 'home',
                'gender_target' => 'unisex',
                'icon' => 'zap',
                'parent_id' => $homeServices->id,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $homeAssistance = VendorServiceCategory::updateOrCreate(
            ['slug' => 'home-assistance-services'],
            [
                'name' => 'Home Assistance Services',
                'slug' => 'home-assistance-services',
                'description' => 'Cleaning, cooking, and caregiving services',
                'category_type' => 'home',
                'gender_target' => 'unisex',
                'icon' => 'cleaning-set',
                'parent_id' => $homeServices->id,
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        // Commercial Services sub-categories
        $facilityManagement = VendorServiceCategory::updateOrCreate(
            ['slug' => 'facility-management-services'],
            [
                'name' => 'Facility Management Services',
                'slug' => 'facility-management-services',
                'description' => 'Building cleaning and facility maintenance',
                'category_type' => 'commercial',
                'gender_target' => 'unisex',
                'icon' => 'building',
                'parent_id' => $commercialServices->id,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $constructionServices = VendorServiceCategory::updateOrCreate(
            ['slug' => 'construction-services'],
            [
                'name' => 'Construction Services',
                'slug' => 'construction-services',
                'description' => 'Residential and commercial construction',
                'category_type' => 'commercial',
                'gender_target' => 'unisex',
                'icon' => 'home',
                'parent_id' => $commercialServices->id,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        // =====================================================================
        // LEAF SERVICE TYPES (Individual services under each sub-category)
        // =====================================================================

        // Men's Salon Services leaf nodes
        $mensSalonServices = [
            ["Men's Haircut", "Professional men's haircut and styling", 'cut', 1],
            ["Men's Shave & Beard Trim", 'Traditional shave and beard grooming', 'razor', 2],
            ["Men's Grooming Package", "Complete men's grooming package", 'sparkles', 3],
        ];

        foreach ($mensSalonServices as [$name, $desc, $icon, $sort]) {
            VendorServiceCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'category_type' => 'beauty',
                    'gender_target' => 'men',
                    'icon' => $icon,
                    'parent_id' => $mensSalon->id,
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }

        // Women's Salon Services leaf nodes
        $womensSalonServices = [
            ["Women's Haircut", "Professional women's haircut and styling", 'scissors', 1],
            ["Women's Hair Coloring", 'Hair coloring and highlights', 'palette', 2],
            ["Women's Hair Treatment", 'Deep conditioning and treatment', 'droplet', 3],
            ["Women's Makeup", 'Professional makeup application', 'lipstick', 4],
            ["Women's Nail Services", 'Manicure and pedicure', 'nail-polish', 5],
        ];

        foreach ($womensSalonServices as [$name, $desc, $icon, $sort]) {
            VendorServiceCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'category_type' => 'beauty',
                    'gender_target' => 'women',
                    'icon' => $icon,
                    'parent_id' => $womensSalon->id,
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }

        // Car Washing Bay leaf nodes
        $carWashServices = [
            ['Exterior Car Wash', 'Full exterior wash and dry', 'car', 1],
            ['Interior Car Detailing', 'Interior vacuum and cleaning', 'chair', 2],
            ['Car Wax & Polish', 'Protective wax and polish', 'sparkles', 3],
            ['Full Car Detailing', 'Complete interior and exterior detailing', 'sparkles', 4],
        ];

        foreach ($carWashServices as [$name, $desc, $icon, $sort]) {
            VendorServiceCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'category_type' => 'automotive',
                    'gender_target' => 'unisex',
                    'icon' => $icon,
                    'parent_id' => $carWashingBay->id,
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }

        // Car Garage leaf nodes
        $carGarageServices = [
            ['Oil Change Service', 'Engine oil change and filter', 'oil-can', 1],
            ['Brake Service', 'Brake pad replacement and service', 'stop-circle', 2],
            ['AC Service & Repair', 'Air conditioning service and repair', 'snowflake', 3],
            ['Engine Diagnostics', 'Computer engine diagnostics', 'microchip', 4],
        ];

        foreach ($carGarageServices as [$name, $desc, $icon, $sort]) {
            VendorServiceCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'category_type' => 'automotive',
                    'gender_target' => 'unisex',
                    'icon' => $icon,
                    'parent_id' => $carGarage->id,
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }

        // Mechanic Services leaf nodes
        $mechanicServicesLeaf = [
            ['General Vehicle Maintenance', 'Routine vehicle maintenance', 'wrench', 1],
            ['Fleet Maintenance', 'Commercial fleet vehicle maintenance', 'truck', 2],
        ];

        foreach ($mechanicServicesLeaf as [$name, $desc, $icon, $sort]) {
            VendorServiceCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'category_type' => 'automotive',
                    'gender_target' => 'unisex',
                    'icon' => $icon,
                    'parent_id' => $mechanicServices->id,
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }

        // Painter Services leaf nodes
        $painterServicesLeaf = [
            ['Interior Painting', 'Residential interior painting', 'paint-brush', 1],
            ['Exterior Painting', 'Building exterior painting', 'building', 2],
        ];

        foreach ($painterServicesLeaf as [$name, $desc, $icon, $sort]) {
            VendorServiceCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'category_type' => 'home',
                    'gender_target' => 'unisex',
                    'icon' => $icon,
                    'parent_id' => $painterServices->id,
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }

        // Electrician Services leaf nodes
        $electricianServicesLeaf = [
            ['Home Electrical Wiring', 'Residential electrical wiring and repairs', 'zap', 1],
            ['Commercial Electrical', 'Commercial building electrical systems', 'zap', 2],
        ];

        foreach ($electricianServicesLeaf as [$name, $desc, $icon, $sort]) {
            VendorServiceCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'category_type' => 'home',
                    'gender_target' => 'unisex',
                    'icon' => $icon,
                    'parent_id' => $electricianServices->id,
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }

        // Home Assistance Services leaf nodes
        $homeAssistanceLeaf = [
            ['Home Cleaning', 'Residential cleaning services', 'cleaning-set', 1],
            ['Caregiving Services', 'Elderly and child caregiving', 'heart', 2],
            ['Home Cooking', 'Meal preparation and cooking', 'utensils', 3],
        ];

        foreach ($homeAssistanceLeaf as [$name, $desc, $icon, $sort]) {
            VendorServiceCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'category_type' => 'home',
                    'gender_target' => 'unisex',
                    'icon' => $icon,
                    'parent_id' => $homeAssistance->id,
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }

        // Facility Management Services leaf nodes
        $facilityManagementLeaf = [
            ['Building Cleaning', 'Commercial building cleaning services', 'cleaning-set', 1],
            ['Facility Maintenance', 'Ongoing facility operations and maintenance', 'wrench', 2],
        ];

        foreach ($facilityManagementLeaf as [$name, $desc, $icon, $sort]) {
            VendorServiceCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'category_type' => 'commercial',
                    'gender_target' => 'unisex',
                    'icon' => $icon,
                    'parent_id' => $facilityManagement->id,
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }

        // Construction Services leaf nodes
        $constructionServicesLeaf = [
            ['Residential Construction', 'Home building and renovation', 'home', 1],
            ['Commercial Construction', 'Commercial building development', 'building', 2],
        ];

        foreach ($constructionServicesLeaf as [$name, $desc, $icon, $sort]) {
            VendorServiceCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $desc,
                    'category_type' => 'commercial',
                    'gender_target' => 'unisex',
                    'icon' => $icon,
                    'parent_id' => $constructionServices->id,
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }
    }
}
