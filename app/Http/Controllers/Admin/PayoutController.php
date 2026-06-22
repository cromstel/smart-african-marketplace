<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Services\ActivityLogger;
use App\Services\PayoutService;
use Illuminate\Http\Request;

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

        app(PayoutService::class)->process($payout);
        app(ActivityLogger::class)->log('payout.processed', $payout);

        return redirect()->back()->with('success', 'Payout processed.');
    }

    public function fail(Request $request, Payout $payout)
    {
        $payout->update([
            'status' => 'failed',
            'metadata' => array_merge($payout->metadata ?? [], ['reason' => $request->input('reason')]),
        ]);

        app(ActivityLogger::class)->log('payout.failed', $payout);

        return redirect()->back()->with('success', 'Payout marked as failed.');
    }
}
