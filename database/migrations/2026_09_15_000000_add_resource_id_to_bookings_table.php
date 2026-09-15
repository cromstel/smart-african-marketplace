<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `App\Models\Booking` has had `resource_id` in its $fillable/casts and
     * a `resource()` belongsTo(BookingResource::class, 'resource_id')
     * relationship since the rental-resources feature was added, and
     * `BookingResourceService` (getResourceBookings, getUnavailableDates,
     * getResourceUtilizationStats, isResourceAvailable, ...) queries
     * `Booking::where('resource_id', ...)` directly. But
     * 2026_03_10_151851_add_booking_type_to_bookings_table only ever added
     * `resource_name` to this table -- `resource_id` was never migrated.
     * Every one of those queries throws "Unknown column 'resource_id'",
     * which is why the vendor booking-resources "show" (utilization
     * stats) and "availability" pages 500 for every vendor.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('resource_id')->nullable()->after('resource_name')
                ->constrained('booking_resources')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['resource_id']);
            $table->dropColumn('resource_id');
        });
    }
};
