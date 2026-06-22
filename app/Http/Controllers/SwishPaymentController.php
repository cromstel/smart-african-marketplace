<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\QrCodeService;
use App\Services\SwishService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SwishPaymentController extends Controller
{
    protected SwishService $swishService;

    protected QrCodeService $qrCodeService;

    public function __construct(SwishService $swishService, QrCodeService $qrCodeService)
    {
        $this->swishService = $swishService;
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Show SWISH payment page
     */
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Prevent accessing already-paid orders
        if ($order->payment_status === 'paid') {
            return redirect()->route('payment.success', $order);
        }

        if (! $this->swishService->isEnabled()) {
            return redirect()->route('cart.index')
                ->with('error', 'SWISH payment is currently unavailable');
        }

        // Generate QR code locally for security (no external API calls)
        $qrCodeData = $this->swishService->generateQRCodeData($order);
        $qrCodeImage = $this->qrCodeService->generateAsDataUri($qrCodeData, 200);

        return view('payment.swish', compact('order', 'qrCodeImage'));
    }

    /**
     * Process SWISH payment
     */
    public function process(Request $request, Order $order)
    {
        // First normalize the phone number (remove spaces, dashes, etc.)
        $rawPhone = $request->input('phone_number', '');
        $normalizedPhone = preg_replace('/[^0-9]/', '', $rawPhone);

        // Validate that the normalized phone has valid Swedish format (10 digits after +46 or 0)
        $request->merge(['phone_normalized' => $normalizedPhone]);

        $validated = $request->validate([
            'phone_number' => 'required|string|min:10',
            'phone_normalized' => 'required|regex:/^(?:\+46|0)[0-9]{9}$/',
        ]);

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $this->swishService->isEnabled()) {
            return redirect()->route('cart.index')
                ->with('error', 'SWISH payment is currently unavailable');
        }

        // Use normalized phone number with country code
        $phoneNumber = $normalizedPhone;

        // Ensure phone has country code (assume Sweden +46 if not provided)
        if (! str_starts_with($phoneNumber, '+') && ! str_starts_with($phoneNumber, '00')) {
            if (strlen($phoneNumber) <= 10) {
                $phoneNumber = '+46'.ltrim($phoneNumber, '0');
            }
        }

        $result = $this->swishService->createPaymentRequest($order, $phoneNumber);

        if (! $result['success']) {
            return redirect()->back()
                ->with('error', $result['error'] ?? 'Failed to initiate SWISH payment');
        }

        try {
            DB::beginTransaction();

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $result['payment_request_id'],
                'payment_method' => 'swish',
                'amount' => $order->total,
                'currency' => 'SEK',
                'status' => 'pending',
                'payment_data' => json_encode([
                    'instruction_uuid' => $result['instruction_uuid'],
                    'payer_phone' => $phoneNumber,
                ]),
                'processed_at' => now(),
            ]);

            $order->update([
                'payment_status' => 'pending',
            ]);

            if ($order->order_group_id) {
                $children = Order::where('order_group_id', $order->order_group_id)
                    ->where('is_parent', false)
                    ->get();
                foreach ($children as $child) {
                    $child->update(['payment_status' => 'pending']);
                }
            }

            DB::commit();

            // Redirect to payment page with polling (show method has status polling)
            return redirect()->route('payment.swish', $order)
                ->with('success', 'SWISH payment initiated. Please check your phone to approve.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SWISH payment creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create payment. Please try again.');
        }
    }

    /**
     * Check payment status (for AJAX polling)
     */
    public function status(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $payment = Payment::where('order_id', $order->id)
            ->where('payment_method', 'swish')
            ->latest()
            ->first();

        if (! $payment) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No payment found',
            ]);
        }

        // If already completed or failed, return immediately
        if (in_array($payment->status, ['completed', 'failed', 'cancelled'])) {
            return response()->json([
                'status' => $payment->status,
                'message' => $this->getStatusMessage($payment->status),
            ]);
        }

        // Check with SWISH API
        $paymentData = json_decode($payment->payment_data, true);
        $result = $this->swishService->checkPaymentStatus(
            $paymentData['instruction_uuid'] ?? $payment->transaction_id
        );

        if ($result['success'] && isset($result['status'])) {
            $newStatus = $this->mapSwishStatus($result['status']);

            if ($newStatus !== $payment->status) {
                $payment->update(['status' => $newStatus]);

                if ($newStatus === 'completed') {
                    $this->completeOrder($order);
                } elseif ($newStatus === 'failed') {
                    $order->update(['payment_status' => 'failed']);
                }
            }

            return response()->json([
                'status' => $newStatus,
                'message' => $this->getStatusMessage($newStatus),
            ]);
        }

        return response()->json([
            'status' => $payment->status,
            'message' => $this->getStatusMessage($payment->status),
        ]);
    }

    /**
     * Handle SWISH webhook callback
     */
    public function callback(Request $request)
    {
        // Validate webhook signature for security - reject if no secret configured
        $webhookSecret = Setting::getValue('swish_webhook_secret');

        if (empty($webhookSecret)) {
            Log::error('SWISH callback rejected: webhook secret not configured');

            return response()->json(['error' => 'Webhook not configured'], 500);
        }

        $signature = $request->header('X-Swish-Signature');
        $payload = $request->getContent();

        if (empty($signature)) {
            Log::error('SWISH callback rejected: missing signature header');

            return response()->json(['error' => 'Missing signature'], 400);
        }

        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if (! hash_equals($computedSignature, $signature)) {
            Log::error('SWISH callback invalid signature');

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = $request->all();

        Log::info('SWISH callback received', $data);

        $result = $this->swishService->handleCallback($data);

        if (! $result['payment_request_id']) {
            Log::warning('SWISH callback missing payment request ID', $data);

            return response()->json(['error' => 'Missing payment request ID'], 400);
        }

        $payment = Payment::where('transaction_id', $result['payment_request_id'])
            ->orWhere('transaction_id', $data['id'] ?? null)
            ->first();

        if (! $payment) {
            Log::warning('SWISH callback payment not found', [
                'transaction_id' => $result['payment_request_id'],
            ]);

            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Verify the payment has an associated order
        $order = $payment->order;
        if (! $order) {
            Log::warning('SWISH callback: order not found for payment', [
                'payment_id' => $payment->id,
            ]);

            return response()->json(['error' => 'Order not found'], 404);
        }

        $payment->update([
            'status' => $result['status'],
            'payment_data' => json_encode(array_merge(
                json_decode($payment->payment_data, true) ?? [],
                $data
            )),
        ]);

        if ($result['status'] === 'completed') {
            $this->completeOrder($order);
        } elseif ($result['status'] === 'failed') {
            $order->update(['payment_status' => 'failed']);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Map SWISH status to our status
     */
    private function mapSwishStatus(string $swishStatus): string
    {
        return match ($swishStatus) {
            'PAID', 'COMPLETED' => 'completed',
            'DECLINED' => 'failed',
            'CANCELLED' => 'cancelled',
            'ERROR' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Get human-readable status message
     */
    private function getStatusMessage(string $status): string
    {
        return match ($status) {
            'completed' => 'Payment completed successfully!',
            'failed' => 'Payment failed. Please try again.',
            'cancelled' => 'Payment was cancelled.',
            'pending' => 'Waiting for payment approval...',
            default => 'Processing payment...',
        };
    }

    /**
     * Complete the order after successful payment
     */
    private function completeOrder(Order $order): void
    {
        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'status' => 'processing',
        ]);

        if ($order->order_group_id) {
            $children = Order::where('order_group_id', $order->order_group_id)
                ->where('is_parent', false)
                ->get();
            foreach ($children as $child) {
                $child->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'status' => 'processing',
                ]);
            }
        }

        // Clear cart
        $cart = Cart::where('user_id', $order->user_id)->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
        }

        // Send order confirmation email
        if ($order->user) {
            Mail::to($order->user->email)->send(new OrderPlaced($order));
        }
    }
}
