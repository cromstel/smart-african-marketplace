<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        $stats = $this->getVendorStats();

        return view('vendor.withdrawals.index', compact('withdrawals', 'stats'));
    }

    public function create()
    {
        $stats = $this->getVendorStats();
        $minimumAmount = Setting::where('key', 'minimum_withdrawal_amount')->value('value') ?? 50;

        if ($stats['available_balance'] < $minimumAmount) {
            return redirect()->route('vendor.withdrawals.index')
                ->with('error', "Minimum withdrawal amount is \${$minimumAmount}");
        }

        return view('vendor.withdrawals.create', compact('stats', 'minimumAmount'));
    }

    public function store(Request $request)
    {
        $minimumAmount = Setting::where('key', 'minimum_withdrawal_amount')->value('value') ?? 50;

        $request->validate([
            'amount' => "required|numeric|min:{$minimumAmount}",
            'method' => 'required|in:bank_transfer,stripe,paystack',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'bank_name' => 'required_if:method,bank_transfer|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $stats = $this->getVendorStats();

        if ($request->amount > $stats['available_balance']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Insufficient balance.');
        }

        $hasPending = Withdrawal::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'You have a pending withdrawal request. Please wait for it to be processed.');
        }

        $feePercentage = Setting::where('key', 'withdrawal_fee_percentage')->value('value') ?? 2;
        $fee = ($request->amount * $feePercentage) / 100;
        $netAmount = $request->amount - $fee;

        $withdrawal = Withdrawal::create([
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'method' => $request->method,
            'account_details' => [
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'bank_name' => $request->bank_name,
            ],
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return redirect()->route('vendor.withdrawals.index')
            ->with('success', 'Withdrawal request submitted successfully. Reference: '.$withdrawal->withdrawal_number);
    }

    public function show($id)
    {
        $withdrawal = Withdrawal::where('user_id', auth()->id())
            ->findOrFail($id);

        return view('vendor.withdrawals.show', compact('withdrawal'));
    }

    private function getVendorStats()
    {
        $vendorId = auth()->id();

        $totalEarnings = OrderItem::whereHas('order', function ($q) {
            $q->where('payment_status', 'paid')
                ->whereNotIn('status', ['cancelled', 'refunded']);
        })->whereHas('product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->get()->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $commissionRate = Setting::where('key', 'platform_commission_rate')->value('value') ?? 10;
        $platformCommission = ($totalEarnings * $commissionRate) / 100;
        $netEarnings = $totalEarnings - $platformCommission;

        $totalWithdrawn = Withdrawal::where('user_id', $vendorId)
            ->where('status', 'completed')
            ->sum('amount');

        $pendingWithdrawals = Withdrawal::where('user_id', $vendorId)
            ->where('status', 'pending')
            ->sum('amount');

        $availableBalance = $netEarnings - $totalWithdrawn - $pendingWithdrawals;

        return [
            'total_earnings' => $totalEarnings,
            'platform_commission' => $platformCommission,
            'net_earnings' => $netEarnings,
            'total_withdrawn' => $totalWithdrawn,
            'pending_withdrawals' => $pendingWithdrawals,
            'available_balance' => max(0, $availableBalance),
        ];
    }
}
