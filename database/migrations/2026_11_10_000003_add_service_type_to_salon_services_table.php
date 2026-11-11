<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salon_services', function (Blueprint $table) {
            $table->foreignId('vendor_service_category_id')->nullable()->constrained('vendor_service_categories')->onDelete('cascade')->after('salon_category_id');
            $table->enum('service_type', ['men', 'women', 'unisex', 'automotive', 'home', 'commercial'])->default('unisex')->after('vendor_service_category_id');
            $table->boolean('is_service_provider')->default(false)->after('service_type');
        });
    }

    public function down(): void
    {
        Schema::table('salon_services', function (Blueprint $table) {
            $table->dropForeign(['vendor_service_category_id']);
            $table->dropColumn(['vendor_service_category_id', 'service_type', 'is_service_provider']);
        });
    }
};
