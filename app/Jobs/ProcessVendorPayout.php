<?php

namespace App\Jobs;

use App\Models\Payout;
use App\Services\ActivityLogger;
use App\Services\PayoutService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Roadmap 3.2: Admin\PayoutController::process() used to call
 * PayoutService::process() synchronously inside the admin's HTTP request --
 * a live blocking Stripe/Paystack transfer call that risked timing out the
 * request, and left no automatic recovery if the web worker died mid-call.
 *
 * PayoutService::process() catches its own gateway exceptions internally and
 * marks the payout 'failed' rather than rethrowing (see its own comments),
 * so queuing this does not add automatic retries for a gateway rejecting the
 * transfer -- that was already a fail-fast, admin-must-retry design before
 * this change, and stays that way. What queuing adds: the admin's request
 * now returns immediately, and if the *worker process itself* dies mid-call
 * (before the gateway responds), the job is never acknowledged and the
 * database queue's retry_after redelivers it -- PayoutService::process()'s
 * own guard (skip if already completed/failed) is what makes that safe to
 * redeliver without double-transferring.
 */
class ProcessVendorPayout implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [300, 1800, 3600];

    public function __construct(public Payout $payout, public ?int $actorId = null)
    {
        $this->onQueue('payouts');
    }

    public function handle(PayoutService $payoutService, ActivityLogger $activityLogger): void
    {
        $payout = $this->payout->fresh();

        if (! $payout || ! in_array($payout->status, ['pending', 'processing'], true)) {
            return;
        }

        $payoutService->process($payout);
        $activityLogger->log('payout.processed', $payout, [], $this->actorId);
    }
}
