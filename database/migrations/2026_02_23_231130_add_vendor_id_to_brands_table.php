<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('brands', 'vendor_id')) {
            return;
        }

        Schema::table('brands', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            if (Schema::hasColumn('brands', 'vendor_id')) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign(['vendor_id']);
                }
                $table->dropColumn('vendor_id');
            }
        });
    }
};
