<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_library', function (Blueprint $table) {
            $table->foreignId('vendor_id')
                ->nullable()
                ->after('folder_id')
                ->constrained('vendors')
                ->onDelete('set null');

            $table->index(['vendor_id', 'folder_id']);
        });
    }

    public function down(): void
    {
        Schema::table('media_library', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropIndex(['vendor_id', 'folder_id']);
            $table->dropColumn('vendor_id');
        });
    }
};
