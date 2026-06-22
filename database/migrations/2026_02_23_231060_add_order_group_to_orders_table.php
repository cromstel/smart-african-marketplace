<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('order_group_id')->nullable()->after('user_id')->constrained('order_groups')->nullOnDelete();
            $table->boolean('is_parent')->default(false)->after('order_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_group_id');
            $table->dropColumn('is_parent');
        });
    }
};
