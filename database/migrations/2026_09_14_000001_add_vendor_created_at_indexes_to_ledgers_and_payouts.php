<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * VendorLedgerController, WalletController and the vendor Payouts index page
 * all run the same shape of query: filter by vendor_id, order by created_at
 * descending, paginate. `vendor_id` alone already has an index (InnoDB adds
 * one automatically for the foreign key), but the ORDER BY isn't covered by
 * it, so for a vendor with a long history each page still means a filesort
 * across all of that vendor's rows before the LIMIT/OFFSET is applied. A
 * composite (vendor_id, created_at) index lets the database satisfy both the
 * filter and the ordering directly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_ledgers', function (Blueprint $table) {
            $table->index(['vendor_id', 'created_at']);
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->index(['vendor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('vendor_ledgers', function (Blueprint $table) {
            $table->dropIndex(['vendor_id', 'created_at']);
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->dropIndex(['vendor_id', 'created_at']);
        });
    }
};
