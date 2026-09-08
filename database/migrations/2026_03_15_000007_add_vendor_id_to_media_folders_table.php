<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_folders', function (Blueprint $table) {
            // Add vendor_id if it doesn't exist
            if (! Schema::hasColumn('media_folders', 'vendor_id')) {
                $table->foreignId('vendor_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('vendors')
                    ->onDelete('cascade');

                // Add composite index for vendor_id and parent_id
                $table->index(['vendor_id', 'parent_id']);
            }
        });

        // Add owner index only if it doesn't exist
        try {
            $indexes = DB::select('SHOW INDEX FROM media_folders');
            $hasOwnerIndex = false;
            foreach ($indexes as $index) {
                if ($index->Key_name === 'media_folders_owner_type_owner_id_index') {
                    $hasOwnerIndex = true;
                    break;
                }
            }
            if (! $hasOwnerIndex) {
                Schema::table('media_folders', function (Blueprint $table) {
                    $table->index(['owner_type', 'owner_id']);
                });
            }
        } catch (Exception $e) {
            // Index might already exist, ignore error
        }
    }

    public function down(): void
    {
        Schema::table('media_folders', function (Blueprint $table) {
            // Only drop vendor_id if it exists and we added it
            if (Schema::hasColumn('media_folders', 'vendor_id')) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            }
        });
    }
};
