<?php

use App\Models\ProductTag;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        ProductTag::whereNotNull('vendor_id')->update(['vendor_id' => null]);
    }

    public function down(): void
    {
        // irreversible: vendor ownership not restored
    }
};
