<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Mail\OrderDelivered;
use App\Mail\OrderShipped;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = auth()->id();

        $query = Order::where('is_parent', false)->whereHas('items.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with(['user', 'items.product', 'billingAddress', 'shippingAddress']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest()->paginate(20);

        $statusCounts = Order::where('is_parent', false)->whereHas('items.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('vendor.orders.index', compact('orders', 'statusCounts'));
    }

    public function show($id)
    {
        $vendorId = auth()->id();

        $order = Order::where('is_parent', false)->whereHas('items.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with([
            'user',
            'items.product.images',
            'billingAddress',
            'shippingAddress',
            'notes.user',
            'shipment',
        ])->findOrFail($id);

        $vendorItems = $order->items->filter(function ($item) use ($vendorId) {
            return $item->product->vendor_id == $vendorId;
        });

        $vendorTotal = $vendorItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return view('vendor.orders.show', compact('order', 'vendorItems', 'vendorTotal'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $vendorId = auth()->id();

        $order = Order::where('is_parent', false)->whereHas('items.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->findOrFail($id);

        $oldStatus = $order->status;
        $order->status = $request->status;

        if ($request->status === 'shipped') {
            $order->shipped_at = now();
            Mail::to($order->user->email)->send(new OrderShipped($order));
        }

        if ($request->status === 'delivered') {
            $order->delivered_at = now();
            Mail::to($order->user->email)->send(new OrderDelivered($order));
        }

        if ($request->status === 'cancelled') {
            $order->cancelled_at = now();
        }

        $order->save();

        OrderNote::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'note' => "Order status changed from {$oldStatus} to {$request->status}",
            'note_type' => 'system',
            'is_customer_visible' => true,
        ]);

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    public function addNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
            'is_customer_visible' => 'boolean',
        ]);

        $vendorId = auth()->id();

        $order = Order::whereHas('items.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->findOrFail($id);

        OrderNote::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'note' => $request->note,
            'note_type' => 'vendor',
            'is_customer_visible' => $request->boolean('is_customer_visible', false),
        ]);

        return redirect()->back()->with('success', 'Note added successfully.');
    }

    public function create()
    {
        $customers = User::where('role', 'customer')->latest()->get();
        $products = Product::where('vendor_id', auth()->id())
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->get();

        return view('vendor.orders.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $subtotal = 0;
            $items = [];

            foreach ($request->products as $productData) {
                $product = Product::where('vendor_id', auth()->id())
                    ->findOrFail($productData['id']);

                if ($product->stock_quantity < $productData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $itemTotal = $product->price * $productData['quantity'];
                $subtotal += $itemTotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $productData['quantity'],
                    'price' => $product->price,
                    'total' => $itemTotal,
                ];
            }

            $shippingCost = $request->shipping_cost ?? 0;
            $discount = $request->discount ?? 0;
            $total = $subtotal + $shippingCost - $discount;

            $customer = User::findOrFail($request->customer_id);
            $defaultAddress = $customer->addresses()->first();

            $order = Order::create([
                'order_number' => 'ORD-'.strtoupper(uniqid()),
                'user_id' => $request->customer_id,
                'billing_address_id' => $defaultAddress->id ?? null,
                'shipping_address_id' => $defaultAddress->id ?? null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'total' => $total,
                'payment_status' => 'pending',
                'status' => 'pending',
                'payment_method' => 'manual',
                'admin_notes' => $request->notes,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                $item['product']->decrement('stock_quantity', $item['quantity']);
            }

            OrderNote::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'note' => 'Order created manually by vendor',
                'note_type' => 'system',
                'is_customer_visible' => false,
            ]);

            DB::commit();

            return redirect()->route('vendor.orders.show', $order->id)
                ->with('success', 'Order created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create order: '.$e->getMessage());
        }
    }
}
