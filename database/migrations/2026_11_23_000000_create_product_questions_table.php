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
        Schema::create('product_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('asker_name')->nullable();
            $table->string('asker_email')->nullable();
            $table->text('question');
            $table->enum('status', ['pending', 'answered', 'hidden'])->default('pending');
            $table->boolean('is_public')->default(false);
            $table->text('answer')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->text('moderation_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // product_id needs no single-column index: the compound index below
            // has product_id as its leftmost prefix, which also satisfies the
            // foreign key. is_public is never queried alone -- the public
            // listing is always product_id + status + is_public, which the
            // compound index serves directly.
            $table->index('user_id');
            $table->index('answered_by');
            $table->index('status');
            $table->index(['product_id', 'status', 'is_public']);
            $table->index(['product_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_questions');
    }
};
