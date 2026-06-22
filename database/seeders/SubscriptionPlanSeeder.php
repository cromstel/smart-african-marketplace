<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = collect(config('modules.catalog', []));
        $implementedModules = collect(config('modules.implemented_route_modules', []));
        $tiers = config('modules.tiers', []);
        $tierRank = config('modules.tier_rank', []);

        $labelByKey = $catalog->pluck('name', 'key');

        $premiumModuleKeys = $catalog->pluck('key')->values()->all();
        $premiumModuleLabels = $catalog->pluck('name')->values()->all();

        $starterKeys = $tiers['starter'] ?? [];
        $professionalKeys = array_values(array_unique(array_merge($starterKeys, $tiers['professional'] ?? [])));
        $businessKeys = array_values(array_unique(array_merge($professionalKeys, $tiers['business'] ?? [])));
        $enterpriseKeys = $premiumModuleKeys;

        $starterLabels = array_values(array_filter(array_map(fn (string $key) => $labelByKey->get($key), $starterKeys)));
        $professionalLabels = array_values(array_filter(array_map(fn (string $key) => $labelByKey->get($key), $professionalKeys)));
        $businessLabels = array_values(array_filter(array_map(fn (string $key) => $labelByKey->get($key), $businessKeys)));

        $implementedKeysByPlan = [
            'starter' => $this->implementedKeysForTier('starter', $implementedModules, $tierRank),
            'professional' => $this->implementedKeysForTier('professional', $implementedModules, $tierRank),
            'business' => $this->implementedKeysForTier('business', $implementedModules, $tierRank),
            'enterprise' => $this->implementedKeysForTier('enterprise', $implementedModules, $tierRank),
            'free-trial' => $implementedModules->pluck('key')->values()->all(),
        ];

        $plans = [
            [
                'name' => 'Free Trial',
                'slug' => 'free-trial',
                'description' => 'Try our platform risk-free for 30 days with full premium module access',
                'price' => 0.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'features' => $premiumModuleLabels,
                'feature_keys' => array_values(array_unique(array_merge($premiumModuleKeys, $implementedKeysByPlan['free-trial']))),
                'max_products' => 5,
                'max_orders' => 10,
                'commission_rate' => 15.00,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small businesses just getting started',
                'price' => 29.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'features' => $starterLabels,
                'feature_keys' => array_values(array_unique(array_merge($starterKeys, $implementedKeysByPlan['starter']))),
                'max_products' => 50,
                'max_orders' => 100,
                'commission_rate' => 10.00,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Best for growing businesses with higher volume',
                'price' => 79.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'features' => $professionalLabels,
                'feature_keys' => array_values(array_unique(array_merge($professionalKeys, $implementedKeysByPlan['professional']))),
                'max_products' => 200,
                'max_orders' => 500,
                'commission_rate' => 7.00,
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'For scaling vendors who need advanced operations and premium modules',
                'price' => 129.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'features' => $businessLabels,
                'feature_keys' => array_values(array_unique(array_merge($businessKeys, $implementedKeysByPlan['business']))),
                'max_products' => 500,
                'max_orders' => 1000,
                'commission_rate' => 6.00,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large businesses with unlimited needs',
                'price' => 199.00,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_days' => 30,
                'features' => $premiumModuleLabels,
                'feature_keys' => array_values(array_unique(array_merge($enterpriseKeys, $implementedKeysByPlan['enterprise']))),
                'max_products' => -1,
                'max_orders' => -1,
                'commission_rate' => 5.00,
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Subscription plans seeded successfully!');
    }

    private function implementedKeysForTier(string $tier, $implementedModules, array $tierRank): array
    {
        $targetRank = $tierRank[$tier] ?? 0;

        return $implementedModules
            ->filter(function (array $module) use ($targetRank, $tierRank) {
                $minTier = $module['min_tier'] ?? 'starter';
                $minRank = $tierRank[$minTier] ?? 0;

                return $minRank <= $targetRank;
            })
            ->pluck('key')
            ->values()
            ->all();
    }
}
