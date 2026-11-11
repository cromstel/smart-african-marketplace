<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_service_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')
                ->constrained('vendor_service_categories')
                ->onDelete('cascade');
            $table->index('parent_id');
            $table->index(['category_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('vendor_service_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['vendor_service_categories_parent_id_index']);
            $table->dropIndex(['vendor_service_categories_category_type_is_active_index']);
            $table->dropColumn('parent_id');
        });
    }
};
