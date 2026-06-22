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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('email');
            $table->string('paystack_customer_code')->nullable()->after('stripe_customer_id');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('paystack_plan_code')->nullable()->after('slug');
            $table->string('stripe_price_id')->nullable()->after('paystack_plan_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_customer_id', 'paystack_customer_code']);
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['paystack_plan_code', 'stripe_price_id']);
        });
    }
};
