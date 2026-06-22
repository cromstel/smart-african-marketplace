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
        Schema::create('service_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_service_id')->constrained()->onDelete('cascade');
            $table->string('tier_name'); // Peak, Off-peak, Weekend, Holiday, etc.
            $table->integer('day_of_week')->nullable(); // 0-6 (Sunday-Saturday), null for all days
            $table->time('start_time')->nullable(); // Time-based pricing
            $table->time('end_time')->nullable();
            $table->decimal('price_modifier', 5, 2)->default(0); // Percentage or fixed amount
            $table->enum('price_modifier_type', ['percentage', 'fixed'])->default('percentage');
            $table->integer('minimum_duration')->nullable(); // Minimum duration in minutes
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('salon_service_id');
            $table->index(['day_of_week', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_pricing_tiers');
    }
};
