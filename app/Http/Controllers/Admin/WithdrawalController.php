<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

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

        $withdrawal->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->back()->with('success', 'Withdrawal rejected successfully.');
    }
}
