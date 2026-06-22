<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('campaign_type')->default('standard')->after('channel');
            $table->string('audience_segment')->default('all')->after('campaign_type');
            $table->string('banner_image_url')->nullable()->after('content');
            $table->timestamp('flash_sale_ends_at')->nullable()->after('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'campaign_type',
                'audience_segment',
                'banner_image_url',
                'flash_sale_ends_at',
            ]);
        });
    }
};
