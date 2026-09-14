<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\VendorBalance;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $vendorId = auth()->user()?->vendor?->id;

        if (! $vendorId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A vendor profile is required to request a withdrawal.');
        }

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

        // Withdrawal and Payout used to check and reserve balance
        // completely independently -- Withdrawal against a live OrderItem
        // sum, Payout against VendorBalance -- so a vendor could draw the
        // same earnings through both request types at once. They now share
        // one locked balance: VendorBalance.available, reserved here the
        // same way PayoutController::store() reserves it. The pending-
        // withdrawal check is re-run under the same lock for the same
        // reason it always was -- two concurrent submissions must not both
        // pass the checks and both create a request.
        $withdrawal = DB::transaction(function () use ($request, $vendorId, $fee, $netAmount) {
            Withdrawal::where('user_id', auth()->id())->lockForUpdate()->get();

            if (Withdrawal::where('user_id', auth()->id())->where('status', 'pending')->exists()) {
                return null;
            }

            VendorBalance::firstOrCreate(
                ['vendor_id' => $vendorId],
                ['available' => 0, 'pending' => 0, 'lifetime_earnings' => 0]
            );

            $balance = VendorBalance::where('vendor_id', $vendorId)->lockForUpdate()->first();

            if ($request->amount > $balance->available) {
                return null;
            }

            $balance->available -= $request->amount;
            $balance->save();

            return Withdrawal::create([
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
        });

        if (! $withdrawal) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Withdrawal could not be created. Check your available balance and any pending request.');
        }

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
        $userId = auth()->id();

        // products.vendor_id references vendors.id, not users.id. This
        // previously passed auth()->id() -- a user id -- so earnings were
        // computed from whichever *other* vendor happened to share that
        // number. Vendor ids and user ids diverge for every vendor in
        // practice, so the balance a vendor saw, and could withdraw against,
        // belonged to someone else.
        //
        // withdrawals.user_id genuinely is a user id, which is why the same
        // variable read correctly two lines further down and the bug survived.
        $vendorId = auth()->user()?->vendor?->id;

        if (! $vendorId) {
            return [
                'total_earnings' => 0,
                'platform_commission' => 0,
                'net_earnings' => 0,
                'total_withdrawn' => 0,
                'pending_withdrawals' => 0,
                'available_balance' => 0,
            ];
        }

        // Informational lifetime breakdown only, shown on the dashboard as
        // "your earnings" context. This is NOT what gates a withdrawal --
        // see available_balance below -- so it's left as the historical,
        // platform-wide-rate estimate rather than rebuilt against each
        // vendor's own commission_rate.
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

        $totalWithdrawn = Withdrawal::where('user_id', $userId)
            ->where('status', 'completed')
            ->sum('amount');

        $pendingWithdrawals = Withdrawal::where('user_id', $userId)
            ->where('status', 'pending')
            ->sum('amount');

        // The actual gating figure. Withdrawal and Payout now read the same
        // number -- VendorBalance.available -- credited by
        // VendorLedgerService only once an order reaches status=delivered
        // (see OrderObserver). That's a real, deliberate tightening versus
        // the old OrderItem-sum calculation, which treated money as
        // withdrawable as soon as an order was paid, before delivery.
        $availableBalance = (float) (VendorBalance::where('vendor_id', $vendorId)->value('available') ?? 0);

        return [
            'total_earnings' => $totalEarnings,
            'platform_commission' => $platformCommission,
            'net_earnings' => $netEarnings,
            'total_withdrawn' => $totalWithdrawn,
            'pending_withdrawals' => $pendingWithdrawals,
            'available_balance' => $availableBalance,
        ];
    }
}
