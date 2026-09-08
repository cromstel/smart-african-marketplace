<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Mail\OrderDelivered;
use App\Mail\OrderShipped;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Shipping\ShippingLabelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ShipmentController extends Controller
{
    public function create($orderId)
    {
        $vendorId = $this->resolveVendorId();

        $order = Order::whereHas('items.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with(['shipment', 'shippingAddress', 'user'])->findOrFail($orderId);

        if ($order->shipment) {
            return redirect()->route('vendor.shipments.show', $order->shipment->id)
                ->with('info', 'Shipment already exists for this order.');
        }

        return view('vendor.shipments.create', compact('order'));
    }

    public function store(Request $request, $orderId)
    {
        $request->validate([
            'tracking_number' => 'required|string|unique:shipments,tracking_number',
            'carrier' => 'required|string|max:255',
            'carrier_url' => 'nullable|url',
            'estimated_delivery' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:500',
        ]);

        $vendorId = $this->resolveVendorId();

        $order = Order::whereHas('items.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with(['shipment', 'user', 'shippingAddress'])->findOrFail($orderId);

        if ($order->shipment) {
            return redirect()->route('vendor.shipments.show', $order->shipment->id)
                ->with('info', 'Shipment already exists for this order.');
        }

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'tracking_number' => $request->tracking_number,
            'carrier' => $request->carrier,
            'carrier_url' => $request->carrier_url,
            'status' => 'in_transit',
            'shipped_at' => now(),
            'estimated_delivery' => $request->estimated_delivery,
            'delivery_notes' => $request->notes,
            'tracking_history' => [[
                'status' => 'in_transit',
                'location' => 'Carrier facility',
                'notes' => 'Shipment created and handed to carrier',
                'timestamp' => now()->toDateTimeString(),
            ]],
        ]);

        $order->update([
            'status' => 'shipped',
            'shipped_at' => now(),
            'tracking_number' => $request->tracking_number,
        ]);

        Mail::to($order->user->email)->send(new OrderShipped($order->fresh(['items.product', 'shippingAddress', 'user'])));

        return redirect()->route('vendor.orders.show', $order->id)
            ->with('success', 'Shipment created successfully.');
    }

    public function show($id)
    {
        $vendorId = $this->resolveVendorId();

        $shipment = Shipment::whereHas('order.items.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with(['order.user', 'order.shippingAddress', 'order.items.product'])->findOrFail($id);

        return view('vendor.shipments.show', compact('shipment'));
    }

    public function addTracking(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_transit,out_for_delivery,delivered,failed,returned',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $vendorId = $this->resolveVendorId();

        $shipment = Shipment::whereHas('order.items.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with(['order.user', 'order.items.product'])->findOrFail($id);

        $shipment->status = $request->status;
        $shipment->addTrackingUpdate($request->status, $request->location, $request->notes);

        if ($request->status === 'delivered') {
            $shipment->delivered_at = now();
            $shipment->order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
            $shipment->save();
            Mail::to($shipment->order->user->email)->send(new OrderDelivered($shipment->order->fresh(['items.product', 'user'])));
        } else {
            $shipment->save();
        }

        return redirect()->back()->with('success', 'Tracking information updated successfully.');
    }

    public function generateLabel($id)
    {
        $vendorId = $this->resolveVendorId();

        $shipment = Shipment::whereHas('order.items.product', function ($q) use ($vendorId) {
            $q->where('vendor_id', $vendorId);
        })->with(['order.shippingAddress'])->findOrFail($id);

        $result = app(ShippingLabelService::class)->generateLabel($shipment);
        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        $shipment->update([
            'label_url' => $result['label_url'],
            'label_provider' => $result['provider'],
        ]);

        return redirect()->back()->with('success', 'Shipping label generated.');
    }

    private function resolveVendorId(): int
    {
        $vendorId = auth()->user()?->vendor?->id;
        abort_if(! $vendorId, 403, 'Vendor profile is required.');

        return $vendorId;
    }
}
