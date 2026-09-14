<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * `product_variants` was superseded by `product_variations` --
 * 2026_02_23_235000_update_variant_foreign_keys_to_product_variations
 * already repointed cart_items.product_variant_id and
 * order_items.product_variant_id at the new table. `product_variants`
 * itself was never dropped: it's been sitting empty (confirmed 0 rows) with
 * zero references anywhere in application code since. Nothing else FKs to
 * it (the FK migration above moved both referencing columns away from it),
 * so dropping it is safe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('product_variants');
    }

    public function down(): void
    {
        // Intentionally not restored: the table was already orphaned and
        // empty before this migration. If product variants ever need this
        // shape again, model it fresh rather than reviving dead schema.
    }
};
