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
        $isSqlite = DB::getDriverName() === 'sqlite';

        if ($isSqlite) {
            Schema::table('announcements', function (Blueprint $table) {
                if (! Schema::hasColumn('announcements', 'target')) {
                    $table->string('target')->default('all_vendors')->after('content');
                }
                if (! Schema::hasColumn('announcements', 'vendor_ids')) {
                    $table->json('vendor_ids')->nullable()->after('target');
                }
                if (! Schema::hasColumn('announcements', 'is_pinned')) {
                    $table->boolean('is_pinned')->default(false)->after('is_active');
                }
                if (! Schema::hasColumn('announcements', 'published_at')) {
                    $table->timestamp('published_at')->nullable()->after('is_pinned');
                }
            });

            Schema::table('announcement_reads', function (Blueprint $table) {
                if (! Schema::hasColumn('announcement_reads', 'created_at')) {
                    $table->timestamps();
                }
            });

            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['created_by', 'target_audience']);
            $table->enum('target', ['all_vendors', 'specific_vendors'])->default('all_vendors')->after('content');
            $table->json('vendor_ids')->nullable()->after('target');
            $table->boolean('is_pinned')->default(false)->after('is_active');
            $table->timestamp('published_at')->nullable()->after('is_pinned');
        });

        DB::statement("ALTER TABLE announcements MODIFY COLUMN type ENUM('info', 'warning', 'success', 'danger') NOT NULL DEFAULT 'info'");

        Schema::table('announcement_reads', function (Blueprint $table) {
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        if ($isSqlite) {
            Schema::table('announcements', function (Blueprint $table) {
                $drop = [];
                foreach (['target', 'vendor_ids', 'is_pinned', 'published_at'] as $column) {
                    if (Schema::hasColumn('announcements', $column)) {
                        $drop[] = $column;
                    }
                }
                if (! empty($drop)) {
                    $table->dropColumn($drop);
                }
            });

            Schema::table('announcement_reads', function (Blueprint $table) {
                if (Schema::hasColumn('announcement_reads', 'created_at')) {
                    $table->dropTimestamps();
                }
            });

            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['target', 'vendor_ids', 'is_pinned', 'published_at']);
            $table->enum('target_audience', ['all', 'vendors', 'customers'])->default('vendors');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        });

        DB::statement("ALTER TABLE announcements MODIFY COLUMN type ENUM('info', 'warning', 'success', 'urgent') NOT NULL DEFAULT 'info'");

        Schema::table('announcement_reads', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
