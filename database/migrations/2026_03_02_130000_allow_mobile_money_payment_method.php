<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->recreatePaymentsTable(true);

            return;
        }

        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('stripe','paystack','cash_on_delivery','mobile_money') NOT NULL DEFAULT 'stripe'");
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->recreatePaymentsTable(false);

            return;
        }

        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('stripe','paystack','cash_on_delivery') NOT NULL DEFAULT 'stripe'");
    }

    private function recreatePaymentsTable(bool $includeMobileMoney): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::rename('payments', 'payments_tmp_old');

        DB::statement('DROP INDEX IF EXISTS payments_transaction_id_unique');
        DB::statement('DROP INDEX IF EXISTS payments_order_id_index');
        DB::statement('DROP INDEX IF EXISTS payments_transaction_id_index');
        DB::statement('DROP INDEX IF EXISTS payments_status_payment_method_index');

        Schema::create('payments', function (Blueprint $table) use ($includeMobileMoney) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->unique()->nullable();
            $methods = ['stripe', 'paystack', 'cash_on_delivery'];
            if ($includeMobileMoney) {
                $methods[] = 'mobile_money';
            }
            $table->enum('payment_method', $methods)->default('stripe');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->text('payment_data')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        $rows = DB::table('payments_tmp_old')->get();
        foreach ($rows as $row) {
            $method = (string) $row->payment_method;
            if (! $includeMobileMoney && $method === 'mobile_money') {
                $method = 'paystack';
            }
            DB::table('payments')->insert([
                'id' => $row->id,
                'order_id' => $row->order_id,
                'transaction_id' => $row->transaction_id,
                'payment_method' => $method,
                'amount' => $row->amount,
                'currency' => $row->currency,
                'status' => $row->status,
                'payment_data' => $row->payment_data,
                'error_message' => $row->error_message,
                'processed_at' => $row->processed_at,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop('payments_tmp_old');

        Schema::table('payments', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('transaction_id');
            $table->index(['status', 'payment_method']);
        });
    }
};
