<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'theme_container_width')
            ->where('value', '1280')
            ->update([
                'value' => '1600',
                'type' => 'integer',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left non-reversible to avoid overriding intentional admin customization.
    }
};
