<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessVendorPayout;
use App\Models\Payout;
use App\Services\ActivityLogger;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::with('vendor')->latest()->paginate(20);

        return view('admin.payouts.index', compact('payouts'));
    }

    public function show(Payout $payout)
    {
        $payout->load('vendor');

        return view('admin.payouts.show', compact('payout'));
    }

    public function process(Payout $payout)
    {
        if (! in_array($payout->status, ['pending', 'processing'], true)) {
            return redirect()->back()->with('error', 'Payout cannot be processed.');
        }

        // Flip to 'processing' here, synchronously, so the admin sees it
        // immediately instead of waiting for the job to be picked up by a
        // worker. PayoutService::process() sets the same status again as its
        // first step, which is a harmless no-op re-write.
        $payout->update(['status' => 'processing']);

        ProcessVendorPayout::dispatch($payout, Auth::id());

        return redirect()->back()->with('success', 'Payout queued for processing.');
    }

    public function fail(Request $request, Payout $payout)
    {
        app(PayoutService::class)->markFailed($payout, (string) $request->input('reason', 'Marked failed by admin'));
        app(ActivityLogger::class)->log('payout.failed', $payout);

        return redirect()->back()->with('success', 'Payout marked as failed.');
    }
}
