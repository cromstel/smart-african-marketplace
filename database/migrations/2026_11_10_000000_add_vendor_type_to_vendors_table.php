<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->enum('vendor_type', [
                'private_painter',
                'corporate_painter',
                'private_facility_management',
                'corporate_facility_management',
                'private_construction',
                'corporate_construction',
                'private_home_assistance',
                'corporate_home_assistance',
                'private_mechanic',
                'corporate_mechanic',
                'private_electrician',
                'corporate_electrician',
                'men_salon',
                'women_salon',
                'car_washing_bay',
                'car_garage',
            ])->default('private_painter')->after('status');
            $table->boolean('is_service_provider')->default(false)->after('vendor_type');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['vendor_type', 'is_service_provider']);
        });
    }
};
