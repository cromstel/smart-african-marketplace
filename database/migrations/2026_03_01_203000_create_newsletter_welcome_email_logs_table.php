<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_welcome_email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_id')->nullable()->constrained('newsletters')->nullOnDelete();
            $table->string('email');
            $table->unsignedTinyInteger('series_step');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['email', 'series_step'], 'newsletter_welcome_email_unique');
            $table->index(['newsletter_id', 'series_step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_welcome_email_logs');
    }
};
