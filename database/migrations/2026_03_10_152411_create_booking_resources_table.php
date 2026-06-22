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
        Schema::create('booking_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('resource_type'); // equipment, vehicle, room, venue
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sku')->nullable();
            $table->integer('quantity_total')->default(1);
            $table->integer('quantity_available')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('minimum_rental_hours')->default(1);
            $table->enum('pricing_type', ['hourly', 'daily', 'fixed'])->default('daily');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('daily_rate', 10, 2)->nullable();
            $table->decimal('fixed_rate', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->json('specifications')->nullable(); // JSON for additional specs
            $table->timestamps();

            $table->index('vendor_id');
            $table->index('resource_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_resources');
    }
};
