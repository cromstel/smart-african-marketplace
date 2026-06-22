<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->float('spam_score')->default(0)->after('approved_at');
            $table->boolean('is_spam')->default(false)->after('spam_score');
            $table->timestamp('flagged_at')->nullable()->after('is_spam');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['spam_score', 'is_spam', 'flagged_at']);
        });
    }
};
