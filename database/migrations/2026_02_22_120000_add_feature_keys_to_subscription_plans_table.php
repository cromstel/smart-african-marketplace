<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->json('feature_keys')->nullable()->after('features');
        });

        $defaultsBySlug = [
            'free-trial' => [
                'vendor_support_module', 'subscriptions', 'seller_badge', 'stripe_express', 'request_for_quotation', 'report_abuse',
                'follow_store', 'geolocation', 'eu_compliance_fields', 'ajax_live_search', 'color_scheme_customizer', 'export_import',
                'live_chat', 'product_enquiry', 'return_and_warranty_request', 'seller_verification', 'store_support', 'vendor_review',
                'vendor_staff_manager', 'wholesale', 'vendor_analytics', 'product_qa',
                'multiple_product_category', 'product_brands_sync', 'order_management', 'manual_order_creation', 'vendor_shipping_status',
                'vendor_dashboard_announcements', 'vendor_independent_store', 'automate_workload_with_ai', 'vendor_rma_request',
            ],
            'starter' => [
                'ajax_live_search', 'color_scheme_customizer', 'multiple_product_category', 'product_brands_sync', 'order_management',
                'manual_order_creation', 'vendor_shipping_status', 'vendor_dashboard_announcements', 'vendor_independent_store',
                'automate_workload_with_ai',
            ],
            'professional' => [
                'ajax_live_search', 'color_scheme_customizer', 'vendor_support_module', 'subscriptions', 'seller_badge', 'stripe_express',
                'report_abuse', 'follow_store', 'eu_compliance_fields', 'return_and_warranty_request', 'seller_verification', 'store_support',
                'vendor_review', 'multiple_product_category', 'product_brands_sync', 'order_management', 'manual_order_creation',
                'vendor_shipping_status', 'vendor_dashboard_announcements', 'vendor_independent_store', 'automate_workload_with_ai',
                'vendor_rma_request',
            ],
            'business' => [
                'ajax_live_search', 'color_scheme_customizer', 'vendor_support_module', 'subscriptions', 'seller_badge', 'stripe_express',
                'report_abuse', 'follow_store', 'eu_compliance_fields', 'return_and_warranty_request', 'seller_verification', 'store_support',
                'vendor_review', 'request_for_quotation', 'geolocation', 'export_import', 'live_chat', 'product_enquiry',
                'vendor_staff_manager', 'wholesale', 'vendor_analytics', 'product_qa', 'multiple_product_category', 'product_brands_sync',
                'order_management', 'manual_order_creation', 'vendor_shipping_status', 'vendor_dashboard_announcements',
                'vendor_independent_store', 'automate_workload_with_ai', 'vendor_rma_request',
            ],
            'enterprise' => [
                'vendor_support_module', 'subscriptions', 'seller_badge', 'stripe_express', 'request_for_quotation', 'report_abuse',
                'follow_store', 'geolocation', 'eu_compliance_fields', 'ajax_live_search', 'color_scheme_customizer', 'export_import',
                'live_chat', 'product_enquiry', 'return_and_warranty_request', 'seller_verification', 'store_support', 'vendor_review',
                'vendor_staff_manager', 'wholesale', 'vendor_analytics', 'product_qa',
                'multiple_product_category', 'product_brands_sync', 'order_management', 'manual_order_creation', 'vendor_shipping_status',
                'vendor_dashboard_announcements', 'vendor_independent_store', 'automate_workload_with_ai', 'vendor_rma_request',
            ],
        ];

        foreach ($defaultsBySlug as $slug => $featureKeys) {
            DB::table('subscription_plans')->where('slug', $slug)->update([
                'feature_keys' => json_encode(array_values(array_unique($featureKeys))),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('feature_keys');
        });
    }
};
