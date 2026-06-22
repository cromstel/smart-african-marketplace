<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vendors')) {
            return;
        }
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('logo');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('vendors')) {
            return;
        }
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'profile_image')) {
                $table->dropColumn('profile_image');
            }
        });
    }
};
