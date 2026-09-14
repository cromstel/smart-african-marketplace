<?php

namespace App\Services;

use App\Models\Payout;
use App\Models\Setting;
use App\Models\Vendor;
use App\Models\VendorBalance;
use App\Models\VendorLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayoutService
{
    /**
     * The vendor's `available` balance is reserved (deducted) up front, when
     * the payout is first requested -- see PayoutController::store(). This
     * method only ever moves a payout between non-terminal states, so a
     * payout already `completed` or `failed` is left untouched rather than
     * re-run, which would otherwise let a stray retry double-refund or
     * double-record a payout that has already been resolved.
     */
    public function process(Payout $payout): void
    {
        if (in_array($payout->status, ['completed', 'failed'], true)) {
            return;
        }

        $payout->update(['status' => 'processing']);

        if ($payout->method === 'stripe') {
            $this->processStripe($payout);

            return;
        }

        if ($payout->method === 'paystack') {
            $this->processPaystack($payout);

            return;
        }

        $this->markCompleted($payout, 'manual');
    }

    private function processStripe(Payout $payout): void
    {
        if (! Setting::get('stripe_enabled', false)) {
            $this->fail($payout, 'Stripe payouts not enabled');

            return;
        }

        $secret = Setting::get('stripe_secret_key');
        if (! $secret) {
            $this->fail($payout, 'Stripe key missing');

            return;
        }

        $vendor = Vendor::find($payout->vendor_id);
        if (! $vendor || ! $vendor->stripe_account_id) {
            $this->fail($payout, 'Vendor Stripe account ID missing');

            return;
        }

        try {
            $currency = strtolower((string) Setting::get('stripe_payout_currency', 'usd'));
            $amount = (int) round($payout->amount * 100);
            $transfer = Http::withToken($secret)
                ->asForm()
                ->post('https://api.stripe.com/v1/transfers', [
                    'amount' => $amount,
                    'currency' => $currency,
                    'destination' => $vendor->stripe_account_id,
                    'transfer_group' => 'payout-'.$payout->id,
                    'metadata[vendor_id]' => (string) $vendor->id,
                    'metadata[payout_id]' => (string) $payout->id,
                ])->throw()->json();

            $payout->update([
                'provider_ref' => $transfer['id'] ?? 'stripe-'.$payout->id,
                'metadata' => array_merge($payout->metadata ?? [], [
                    'stripe_transfer' => $transfer,
                ]),
            ]);

            $this->markCompleted($payout, 'stripe');
        } catch (\Throwable $e) {
            Log::error('Stripe payout failed: '.$e->getMessage());
            $this->fail($payout, $e->getMessage());
        }
    }

    private function processPaystack(Payout $payout): void
    {
        if (! Setting::get('paystack_enabled', false)) {
            $this->fail($payout, 'Paystack payouts not enabled');

            return;
        }

        $secret = Setting::get('paystack_secret_key');
        if (! $secret) {
            $this->fail($payout, 'Paystack key missing');

            return;
        }

        $vendor = Vendor::find($payout->vendor_id);
        if (! $vendor) {
            $this->fail($payout, 'Vendor not found');

            return;
        }

        $bankCode = $payout->metadata['bank_code'] ?? $vendor->bank_code;
        $bankAccount = $payout->metadata['bank_account_number'] ?? $vendor->bank_account_number;
        $bankAccountName = $payout->metadata['bank_account_name'] ?? $vendor->bank_account_name;

        if (! $bankCode || ! $bankAccount) {
            $this->fail($payout, 'Vendor bank details missing');

            return;
        }

        try {
            $baseUrl = rtrim((string) config('payment.paystack.payment_url', 'https://api.paystack.co'), '/');
            $recipientCode = $vendor->paystack_recipient_code;

            if (! $recipientCode) {
                $recipientResponse = Http::withToken($secret)->post($baseUrl.'/transferrecipient', [
                    'type' => 'nuban',
                    'name' => $bankAccountName ?: $vendor->business_name,
                    'account_number' => $bankAccount,
                    'bank_code' => $bankCode,
                    'currency' => (string) Setting::get('paystack_payout_currency', 'NGN'),
                ])->throw()->json();

                if (! ($recipientResponse['status'] ?? false)) {
                    $this->fail($payout, $recipientResponse['message'] ?? 'Paystack recipient creation failed');

                    return;
                }

                $recipientCode = $recipientResponse['data']['recipient_code'] ?? null;
                if (! $recipientCode) {
                    $this->fail($payout, 'Paystack recipient code missing');

                    return;
                }

                $vendor->update([
                    'paystack_recipient_code' => $recipientCode,
                    'bank_account_name' => $bankAccountName,
                    'bank_account_number' => $bankAccount,
                    'bank_code' => $bankCode,
                ]);
            }

            $transferResponse = Http::withToken($secret)->post($baseUrl.'/transfer', [
                'source' => 'balance',
                'reason' => 'Vendor payout #'.$payout->id,
                'amount' => (int) round($payout->amount * 100),
                'recipient' => $recipientCode,
                'currency' => (string) Setting::get('paystack_payout_currency', 'NGN'),
            ])->throw()->json();

            if (! ($transferResponse['status'] ?? false)) {
                $this->fail($payout, $transferResponse['message'] ?? 'Paystack transfer failed');

                return;
            }

            $providerRef = $transferResponse['data']['transfer_code'] ?? $transferResponse['data']['id'] ?? null;
            $payout->update([
                'provider_ref' => $providerRef ?: 'paystack-'.$payout->id,
                'metadata' => array_merge($payout->metadata ?? [], [
                    'paystack_transfer' => $transferResponse['data'] ?? $transferResponse,
                ]),
            ]);

            $this->markCompleted($payout, 'paystack');
        } catch (\Throwable $e) {
            Log::error('Paystack payout failed: '.$e->getMessage());
            $this->fail($payout, $e->getMessage());
        }
    }

    /**
     * The payout amount was already deducted from `available` when the
     * payout was requested (it is a reservation, not a hope). Completing the
     * transfer does not touch the balance again -- it only records the
     * ledger entry that makes the reservation permanent and marks the payout
     * done. Re-deducting here was the original bug: it let the same money
     * leave the vendor's balance twice, once as an implicit reservation and
     * once as an explicit debit.
     */
    private function markCompleted(Payout $payout, string $provider): void
    {
        $balance = VendorBalance::firstOrCreate(
            ['vendor_id' => $payout->vendor_id],
            ['available' => 0, 'pending' => 0, 'lifetime_earnings' => 0]
        );

        VendorLedger::create([
            'vendor_id' => $payout->vendor_id,
            'order_id' => null,
            'type' => 'debit_payout',
            'amount' => $payout->amount,
            'balance_after' => $balance->available,
            'metadata' => ['provider' => $provider],
        ]);

        $payout->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Marks a payout failed and returns its reserved amount to the vendor's
     * available balance. Guarded against being run twice for the same
     * payout (an admin retry, or two failure paths racing) -- otherwise the
     * same reservation would be refunded more than once, manufacturing
     * balance the vendor never actually had.
     */
    public function markFailed(Payout $payout, string $reason): void
    {
        DB::transaction(function () use ($payout, $reason) {
            $locked = Payout::whereKey($payout->id)->lockForUpdate()->first();

            if (! $locked || $locked->status === 'failed') {
                return;
            }

            $balance = VendorBalance::where('vendor_id', $locked->vendor_id)->lockForUpdate()->first();
            if ($balance) {
                $balance->available += $locked->amount;
                $balance->save();
            }

            $locked->update([
                'status' => 'failed',
                'metadata' => array_merge($locked->metadata ?? [], ['error' => $reason]),
            ]);
        });

        $payout->refresh();
    }

    private function fail(Payout $payout, string $reason): void
    {
        $this->markFailed($payout, $reason);
    }
}
