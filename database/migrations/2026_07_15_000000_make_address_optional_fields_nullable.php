<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Make optional address fields nullable to match the API validation rules
     * (state, postal_code and phone are optional for international addresses).
     */
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE addresses '
            .'MODIFY COLUMN state VARCHAR(255) NULL, '
            .'MODIFY COLUMN postal_code VARCHAR(255) NULL, '
            .'MODIFY COLUMN phone VARCHAR(255) NULL'
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE addresses '
            .'MODIFY COLUMN state VARCHAR(255) NOT NULL, '
            .'MODIFY COLUMN postal_code VARCHAR(255) NOT NULL, '
            .'MODIFY COLUMN phone VARCHAR(255) NOT NULL'
        );
    }
};
