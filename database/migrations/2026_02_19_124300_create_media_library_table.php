<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_library', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('webp_path')->nullable();
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->enum('type', ['image', 'document', 'video', 'other'])->default('image');
            $table->string('uploaded_by_type')->nullable();
            $table->unsignedBigInteger('uploaded_by_id')->nullable();
            $table->string('context')->nullable();
            $table->unsignedBigInteger('context_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['uploaded_by_type', 'uploaded_by_id']);
            $table->index(['context', 'context_id']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_library');
    }
};
