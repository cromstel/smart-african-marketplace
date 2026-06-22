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
        Schema::table('bookings', function (Blueprint $table) {
            // Booking type (appointment, reservation, rental, consultation, class, event)
            $table->enum('booking_type', [
                'appointment',
                'reservation',
                'rental',
                'consultation',
                'class',
                'event',
            ])->default('appointment')->after('booking_number');

            // Rename salon_notes to staff_notes for consistency
            $table->renameColumn('salon_notes', 'staff_notes');

            // End date for rentals/events
            $table->date('end_date')->nullable()->after('booking_date');

            // Time fields (separate from datetime for flexibility)
            $table->time('start_time')->nullable()->after('end_date');
            $table->time('end_time')->nullable()->after('start_time');

            // Quantity for group bookings/rentals
            $table->integer('quantity')->default(1)->after('duration_minutes');
            $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');

            // Guest booking fields
            $table->string('guest_name')->nullable()->after('user_id');
            $table->string('guest_email')->nullable()->after('guest_name');
            $table->string('guest_phone')->nullable()->after('guest_email');

            // Payment status
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded'])->default('pending')->after('price');
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->decimal('deposit_amount', 10, 2)->nullable()->after('payment_method');
            $table->decimal('deposit_paid', 10, 2)->nullable()->after('deposit_amount');
            $table->decimal('total_amount', 10, 2)->nullable()->after('deposit_paid');

            // Recurring booking fields
            $table->boolean('is_recurring')->default(false)->after('total_amount');
            $table->enum('recurring_frequency', ['daily', 'weekly', 'monthly'])->nullable()->after('is_recurring');
            $table->date('recurring_end_date')->nullable()->after('recurring_frequency');
            $table->foreignId('parent_booking_id')->nullable()->after('recurring_end_date')->constrained('bookings')->onDelete('set null');

            // Resource fields
            $table->string('resource_name')->nullable()->after('parent_booking_id');

            // Confirmation
            $table->string('confirmation_method')->default('manual')->after('resource_name');
            $table->string('confirmation_token')->nullable()->unique()->after('confirmation_method');

            // Cancellation details
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            $table->decimal('cancellation_fee', 10, 2)->nullable()->after('cancellation_reason');

            // Custom fields JSON
            $table->json('custom_fields')->nullable()->after('cancellation_fee');
            $table->json('metadata')->nullable()->after('custom_fields');

            // Add indexes for new fields
            $table->index('booking_type');
            $table->index('payment_status');
            $table->index('is_recurring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['booking_type']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['is_recurring']);

            // Drop foreign keys
            $table->dropForeign(['parent_booking_id']);

            // Drop columns
            $table->dropColumn([
                'booking_type',
                'end_date',
                'start_time',
                'end_time',
                'quantity',
                'unit_price',
                'guest_name',
                'guest_email',
                'guest_phone',
                'payment_status',
                'payment_method',
                'deposit_amount',
                'deposit_paid',
                'total_amount',
                'is_recurring',
                'recurring_frequency',
                'recurring_end_date',
                'parent_booking_id',
                'resource_name',
                'confirmation_method',
                'confirmation_token',
                'cancellation_reason',
                'cancellation_fee',
                'custom_fields',
                'metadata',
            ]);

            // Rename staff_notes back to salon_notes
            $table->renameColumn('staff_notes', 'salon_notes');
        });
    }
};
