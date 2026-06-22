<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_sessions', function (Blueprint $table) {
            $table->boolean('handoff_active')->default(false)->after('last_active_at');
            $table->foreignId('assigned_agent_id')->nullable()->after('handoff_active')->constrained('users')->nullOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE chatbot_messages MODIFY role ENUM('user','assistant','system','agent') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE chatbot_messages MODIFY role ENUM('user','assistant','system') NOT NULL");
        }

        Schema::table('chatbot_sessions', function (Blueprint $table) {
            $table->dropForeign(['assigned_agent_id']);
            $table->dropColumn(['assigned_agent_id', 'handoff_active']);
        });
    }
};
