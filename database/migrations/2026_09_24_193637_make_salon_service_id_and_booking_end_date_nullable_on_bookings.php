<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `salon_service_id` and `booking_end_date` were both NOT NULL from
     * the original migration -- fine when every booking was a salon
     * service appointment, but ResourceBookingController (the new
     * customer-facing rental flow) has no salon_service_id at all, and
     * uses the separate `end_date` DATE column (already nullable, added
     * alongside `booking_type`) for a rental's return date rather than
     * `booking_end_date` (a DATETIME for a time-slot booking's computed
     * end). Both columns already have real, populated data on every
     * existing row, so this only relaxes future inserts -- no backfill
     * needed.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('salon_service_id')->nullable()->change();
            $table->dateTime('booking_end_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('salon_service_id')->nullable(false)->change();
            $table->dateTime('booking_end_date')->nullable(false)->change();
        });
    }
};
