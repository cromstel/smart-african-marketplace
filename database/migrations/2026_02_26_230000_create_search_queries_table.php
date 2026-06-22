<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query')->unique();
            $table->unsignedInteger('hits')->default(0);
            $table->unsignedInteger('results_count')->default(0);
            $table->timestamp('last_searched_at')->nullable()->index();
            $table->foreignId('last_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->ipAddress('last_ip')->nullable();
            $table->string('last_user_agent', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_queries');
    }
};
