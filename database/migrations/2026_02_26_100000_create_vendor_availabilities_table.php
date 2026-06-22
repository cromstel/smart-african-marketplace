<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week')->unsigned();
            $table->time('start_time');
            $table->time('end_time');
            $table->smallInteger('slot_interval_minutes')->default(30);
            $table->smallInteger('min_lead_time_minutes')->default(60);
            $table->smallInteger('buffer_minutes')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['vendor_id', 'day_of_week', 'start_time', 'end_time'], 'vendor_availability_unique_range');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_availabilities');
    }
};
