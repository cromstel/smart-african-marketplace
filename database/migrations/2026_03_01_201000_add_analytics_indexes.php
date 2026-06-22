<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['payment_status', 'created_at'], 'orders_payment_status_created_at_idx');
            $table->index(['user_id', 'created_at'], 'orders_user_created_at_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['product_id', 'created_at'], 'order_items_product_created_at_idx');
            $table->index(['order_id', 'created_at'], 'order_items_order_created_at_idx');
        });

        Schema::table('newsletters', function (Blueprint $table) {
            $table->index(['utm_source', 'created_at'], 'newsletters_utm_source_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_payment_status_created_at_idx');
            $table->dropIndex('orders_user_created_at_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_product_created_at_idx');
            $table->dropIndex('order_items_order_created_at_idx');
        });

        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropIndex('newsletters_utm_source_created_at_idx');
        });
    }
};
