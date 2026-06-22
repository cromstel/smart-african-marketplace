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
        Schema::table('media_library', function (Blueprint $table) {
            $table->boolean('is_optimized')->default(false)->after('file_size');
            $table->string('original_file_path')->nullable()->after('file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_library', function (Blueprint $table) {
            $table->dropColumn(['is_optimized', 'original_file_path']);
        });
    }
};
