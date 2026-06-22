<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('custom_domain')->nullable()->unique()->after('slug');
            $table->timestamp('custom_domain_verified_at')->nullable()->after('custom_domain');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropUnique('vendors_custom_domain_unique');
            $table->dropColumn(['custom_domain', 'custom_domain_verified_at']);
        });
    }
};
