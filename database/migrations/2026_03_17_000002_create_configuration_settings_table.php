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
        Schema::create('configuration_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuration_category_id')
                ->constrained('configuration_categories')
                ->onDelete('cascade');
            $table->string('key')->unique(); // e.g., "stripe_secret_key"
            $table->string('label');          // e.g., "Secret Key"
            $table->text('description')->nullable();
            $table->enum('type', ['text', 'textarea', 'number', 'boolean', 'select', 'json', 'file', 'encrypted']);
            $table->text('value')->nullable();
            $table->text('default_value')->nullable();
            $table->json('options')->nullable(); // for select type: ["sandbox", "production"]
            $table->json('validation_rules')->nullable(); // e.g., ["required", "min:8"]
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_public')->default(false); // accessible without auth
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('key');
            $table->index('configuration_category_id');
            $table->index(['is_active', 'is_encrypted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuration_settings');
    }
};
