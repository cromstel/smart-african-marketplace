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
        Schema::create('vendor_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');

            $table->string('primary_color', 7)->default('#ea580c');
            $table->string('secondary_color', 7)->default('#dc2626');
            $table->string('accent_color', 7)->default('#f97316');
            $table->string('text_color', 7)->default('#1f2937');
            $table->string('background_color', 7)->default('#ffffff');

            $table->string('font_family', 100)->default('Inter');
            $table->string('font_heading', 100)->default('Inter');

            $table->string('banner_image')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();

            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();

            $table->enum('layout_style', ['grid', 'list', 'masonry'])->default('grid');
            $table->boolean('show_categories')->default(true);
            $table->boolean('show_filters')->default(true);
            $table->boolean('show_search')->default(true);

            $table->text('about_text')->nullable();
            $table->text('contact_info')->nullable();
            $table->json('social_links')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_themes');
    }
};
