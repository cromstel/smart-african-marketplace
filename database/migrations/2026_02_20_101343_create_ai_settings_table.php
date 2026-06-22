<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('ai_settings')->insert([
            [
                'key' => 'ai_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable AI content generation features',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_title_style',
                'value' => 'standard',
                'type' => 'select',
                'description' => 'Default AI title generation style',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_description_style',
                'value' => 'detailed',
                'type' => 'select',
                'description' => 'Default AI description generation style',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'auto_generate_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically suggest AI-generated content when creating products',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
