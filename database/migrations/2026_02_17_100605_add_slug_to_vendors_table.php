<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if slug column exists
        if (! \Schema::hasColumn('vendors', 'slug')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('business_name');
            });
        }

        // Generate slugs for existing vendors
        $vendors = \App\Models\Vendor::whereNull('slug')->orWhere('slug', '')->get();
        foreach ($vendors as $vendor) {
            $vendor->slug = \Illuminate\Support\Str::slug($vendor->business_name);
            $vendor->save();
        }

        // Make slug unique after populating
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('vendors', function (Blueprint $table) {
                $table->string('slug')->unique()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
