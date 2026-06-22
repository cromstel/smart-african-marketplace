<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->string('utm_source')->nullable()->after('status');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
            $table->string('utm_term')->nullable()->after('utm_campaign');
            $table->string('utm_content')->nullable()->after('utm_term');
            $table->text('referrer')->nullable()->after('utm_content');
            $table->string('ip_address')->nullable()->after('referrer');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('session_id')->nullable()->after('user_agent');
            $table->foreignId('user_id')->nullable()->after('session_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
                'referrer',
                'ip_address',
                'user_agent',
                'session_id',
            ]);
        });
    }
};
