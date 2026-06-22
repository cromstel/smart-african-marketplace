<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->foreignId('vendor_category_id')->nullable()->after('user_id')->constrained('vendor_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['vendor_category_id']);
            }
            $table->dropColumn('vendor_category_id');
        });
    }
};
