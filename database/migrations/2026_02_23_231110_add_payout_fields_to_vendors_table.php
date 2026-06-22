<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'stripe_account_id')) {
                $table->string('stripe_account_id')->nullable()->after('logo');
            }
            if (! Schema::hasColumn('vendors', 'paystack_recipient_code')) {
                $table->string('paystack_recipient_code')->nullable()->after('stripe_account_id');
            }
            if (! Schema::hasColumn('vendors', 'bank_account_name')) {
                $table->string('bank_account_name')->nullable()->after('paystack_recipient_code');
            }
            if (! Schema::hasColumn('vendors', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_account_name');
            }
            if (! Schema::hasColumn('vendors', 'bank_code')) {
                $table->string('bank_code')->nullable()->after('bank_account_number');
            }
            if (! Schema::hasColumn('vendors', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('bank_code');
            }
            if (! Schema::hasColumn('vendors', 'momo_provider')) {
                $table->string('momo_provider')->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('vendors', 'momo_number')) {
                $table->string('momo_number')->nullable()->after('momo_provider');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $columns = [
                'stripe_account_id',
                'paystack_recipient_code',
                'bank_account_name',
                'bank_account_number',
                'bank_code',
                'bank_name',
                'momo_provider',
                'momo_number',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('vendors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
