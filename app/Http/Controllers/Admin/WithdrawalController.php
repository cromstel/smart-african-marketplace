<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorBalance;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = Withdrawal::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('withdrawal_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $withdrawals = $query->latest()->paginate(20);

        $stats = [
            'total' => Withdrawal::count(),
            'pending' => Withdrawal::where('status', 'pending')->count(),
            'processing' => Withdrawal::where('status', 'processing')->count(),
            'completed' => Withdrawal::where('status', 'completed')->count(),
            'rejected' => Withdrawal::where('status', 'rejected')->count(),
            'total_amount' => Withdrawal::where('status', 'completed')->sum('amount'),
        ];

        return view('admin.withdrawals.index', compact('withdrawals', 'stats'));
    }

    public function show($id)
    {
        $withdrawal = Withdrawal::with('user')->findOrFail($id);

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending withdrawals can be approved.');
        }

        $withdrawal->update([
            'status' => 'processing',
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Withdrawal approved and set to processing.');
    }

    public function complete(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $withdrawal = Withdrawal::findOrFail($id);

        if (! in_array($withdrawal->status, ['pending', 'processing'])) {
            return redirect()->back()->with('error', 'Cannot complete this withdrawal.');
        }

        // The amount was already reserved (deducted from VendorBalance) when
        // the withdrawal was requested -- see Vendor\WithdrawalController::store().
        // Completing it is a pure status transition; it must not touch the
        // balance again, the same reasoning as PayoutService::markCompleted().
        $withdrawal->update([
            'status' => 'completed',
            'processed_at' => now(),
            'notes' => $request->notes ?? $withdrawal->notes,
        ]);

        return redirect()->back()->with('success', 'Withdrawal marked as completed successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $withdrawal = Withdrawal::findOrFail($id);

        if (! in_array($withdrawal->status, ['pending', 'processing'])) {
            return redirect()->back()->with('error', 'Cannot reject this withdrawal.');
        }

        // The reservation made at request time must come back to the
        // vendor's available balance -- otherwise a rejected withdrawal
        // just deletes the vendor's money. Locked and guarded against
        // running twice for the same withdrawal, mirroring
        // PayoutService::markFailed().
        DB::transaction(function () use ($withdrawal, $request) {
            $locked = Withdrawal::whereKey($withdrawal->id)->lockForUpdate()->first();

            if (! $locked || ! in_array($locked->status, ['pending', 'processing'], true)) {
                return;
            }

            $vendorId = Vendor::where('user_id', $locked->user_id)->value('id');

            if ($vendorId) {
                $balance = VendorBalance::where('vendor_id', $vendorId)->lockForUpdate()->first();
                if ($balance) {
                    $balance->available += $locked->amount;
                    $balance->save();
                }
            }

            $locked->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);
        });

        return redirect()->back()->with('success', 'Withdrawal rejected successfully.');
    }
}
