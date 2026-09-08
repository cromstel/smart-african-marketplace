<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates Sanctum's personal_access_tokens table.
 *
 * laravel/sanctum is required in composer.json and the API is authenticated
 * with `auth:sanctum`, but the package migration was never published. Without
 * this table every call to createToken() fails, so mobile login and
 * registration returned 500 and no API token could be issued at all.
 *
 * Timestamped immediately after the core framework tables so it exists before
 * anything that issues a token. Guarded, so a database where it was created by
 * hand is left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('personal_access_tokens')) {
            return;
        }

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
