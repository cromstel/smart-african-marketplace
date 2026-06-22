<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            if (! Schema::hasColumn('product_attributes', 'enable_archives')) {
                $table->boolean('enable_archives')->default(false)->after('is_visible');
            }

            if (! Schema::hasColumn('product_attributes', 'default_sort_order')) {
                $table->string('default_sort_order', 32)->default('custom_ordering')->after('enable_archives');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            if (Schema::hasColumn('product_attributes', 'default_sort_order')) {
                $table->dropColumn('default_sort_order');
            }

            if (Schema::hasColumn('product_attributes', 'enable_archives')) {
                $table->dropColumn('enable_archives');
            }
        });
    }
};
