<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('refund_requests', 'vendor_status')) {
                $table->enum('vendor_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            }
            if (! Schema::hasColumn('refund_requests', 'vendor_notes')) {
                $table->text('vendor_notes')->nullable()->after('vendor_status');
            }
            if (! Schema::hasColumn('refund_requests', 'vendor_responded_at')) {
                $table->timestamp('vendor_responded_at')->nullable()->after('vendor_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            if (Schema::hasColumn('refund_requests', 'vendor_status')) {
                $table->dropColumn('vendor_status');
            }
            if (Schema::hasColumn('refund_requests', 'vendor_notes')) {
                $table->dropColumn('vendor_notes');
            }
            if (Schema::hasColumn('refund_requests', 'vendor_responded_at')) {
                $table->dropColumn('vendor_responded_at');
            }
        });
    }
};
