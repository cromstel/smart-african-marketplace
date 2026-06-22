<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salon_services', function (Blueprint $table) {
            if (! Schema::hasColumn('salon_services', 'salon_id')) {
                $table->foreignId('salon_id')->nullable()->after('user_id')->constrained('salons')->nullOnDelete();
            }
            if (! Schema::hasColumn('salon_services', 'stylist_id')) {
                $table->foreignId('stylist_id')->nullable()->after('salon_id')->constrained('salon_stylists')->nullOnDelete();
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'stylist_id')) {
                $table->foreignId('stylist_id')->nullable()->after('salon_service_id')->constrained('salon_stylists')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('salon_services', function (Blueprint $table) {
            if (Schema::hasColumn('salon_services', 'stylist_id')) {
                $table->dropConstrainedForeignId('stylist_id');
            }
            if (Schema::hasColumn('salon_services', 'salon_id')) {
                $table->dropConstrainedForeignId('salon_id');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'stylist_id')) {
                $table->dropConstrainedForeignId('stylist_id');
            }
        });
    }
};
