<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->string('submitted_ip', 64)->nullable()->after('documents');
            $table->string('submitted_user_agent', 255)->nullable()->after('submitted_ip');
            $table->foreignId('reviewed_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->string('reviewed_ip', 64)->nullable()->after('reviewed_by');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_ip');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['submitted_ip', 'submitted_user_agent', 'reviewed_ip', 'reviewed_at']);
        });
    }
};
