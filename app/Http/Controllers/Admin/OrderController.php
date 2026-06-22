<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('user')
            ->where(function ($query) {
                $query->where('is_parent', true)
                    ->orWhereNull('order_group_id');
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q');
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', '%'.$search.'%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.orders.index')->with('error', 'Orders are created through customer or vendor checkout flows.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return redirect()->route('admin.orders.index')->with('error', 'Orders are created through customer or vendor checkout flows.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items', 'shippingAddress', 'billingAddress', 'notes.user', 'group.orders.items']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $order->load(['user', 'items', 'shippingAddress']);

        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'tracking_number' => 'nullable|string|max:255',
            'admin_notes' => 'nullable|string',
        ]);

        $order->status = $validated['status'];
        $order->payment_status = $validated['payment_status'];
        $order->tracking_number = $validated['tracking_number'] ?? $order->tracking_number;
        $order->admin_notes = $validated['admin_notes'] ?? null;

        if ($validated['status'] === 'shipped' && ! $order->shipped_at) {
            $order->shipped_at = Carbon::now();
        }

        if ($validated['status'] === 'delivered' && ! $order->delivered_at) {
            $order->delivered_at = Carbon::now();
            $order->shipped_at = $order->shipped_at ?? Carbon::now();
        }

        if ($validated['status'] === 'cancelled' && ! $order->cancelled_at) {
            $order->cancelled_at = Carbon::now();
        }

        if ($validated['status'] !== 'cancelled') {
            $order->cancelled_at = null;
        }

        $order->save();

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        if ($order->status !== 'cancelled') {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }

        return redirect()->route('admin.orders.index')->with('success', 'Order was cancelled successfully.');
    }
}
