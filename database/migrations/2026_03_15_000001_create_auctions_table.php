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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('starting_price', 12, 2);
            $table->decimal('reserve_price', 12, 2)->nullable();
            $table->decimal('buy_now_price', 12, 2)->nullable();
            $table->decimal('current_price', 12, 2);
            $table->decimal('bid_increment', 12, 2)->default(1.00);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('original_ends_at')->nullable();
            $table->tinyInteger('extension_minutes')->default(15);
            $table->enum('status', ['draft', 'scheduled', 'active', 'ended', 'cancelled', 'settled'])->default('draft');
            $table->enum('type', ['english', 'dutch', 'sealed', 'buy_now'])->default('english');
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('winning_bid_id')->nullable();
            $table->integer('total_bids')->default(0);
            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('slug')->unique();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'ends_at']);
            $table->index(['vendor_id', 'status']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
