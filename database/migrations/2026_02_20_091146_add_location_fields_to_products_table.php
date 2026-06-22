<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('origin_country');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('location_address', 500)->nullable()->after('longitude');
            $table->string('location_city', 100)->nullable()->after('location_address');
            $table->string('location_state', 100)->nullable()->after('location_city');
            $table->string('location_country', 100)->nullable()->after('location_state');
            $table->string('location_postal_code', 20)->nullable()->after('location_country');

            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropColumn([
                'latitude',
                'longitude',
                'location_address',
                'location_city',
                'location_state',
                'location_country',
                'location_postal_code',
            ]);
        });
    }
};
