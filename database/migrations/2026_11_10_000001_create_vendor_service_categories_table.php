<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('category_type', ['beauty', 'automotive', 'home', 'commercial']);
            $table->enum('gender_target', ['men', 'women', 'unisex'])->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('slug');
            $table->index('category_type');
            $table->index('gender_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_service_categories');
    }
};
