<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\SalonService;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with('user')->latest()->paginate(20);

        return view('admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('admin.vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email',
            'business_phone' => 'required|string|max:20',
            'business_address' => 'required|string',
            'business_registration_number' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:pending,approved,suspended,rejected',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'vendor',
                'is_active' => true,
            ]);

            $vendor = Vendor::create([
                'user_id' => $user->id,
                'business_name' => $validated['business_name'],
                'slug' => Str::slug($validated['business_name']),
                'business_email' => $validated['business_email'],
                'business_phone' => $validated['business_phone'],
                'business_address' => $validated['business_address'],
                'business_registration_number' => $validated['business_registration_number'] ?? null,
                'tax_id' => $validated['tax_id'] ?? null,
                'description' => $validated['description'] ?? null,
                'commission_rate' => $validated['commission_rate'],
                'status' => $validated['status'],
                'is_active' => $validated['status'] === 'approved',
                'approved_at' => $validated['status'] === 'approved' ? now() : null,
            ]);

            DB::commit();

            return redirect()->route('admin.vendors.index')
                ->with('success', 'Vendor account created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Failed to create vendor account: '.$e->getMessage());
        }
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['user', 'products' => function ($query) {
            $query->latest()->take(10);
        }]);

        $serviceIds = SalonService::where('user_id', $vendor->user_id)->pluck('id');
        $totalBookings = Booking::whereIn('salon_service_id', $serviceIds)->count();
        $confirmedBookings = Booking::whereIn('salon_service_id', $serviceIds)
            ->where('status', 'confirmed')
            ->count();
        $bookingRevenue = Booking::whereIn('salon_service_id', $serviceIds)
            ->sum('price');

        $stats = [
            'total_products' => $vendor->products()->count(),
            'active_products' => $vendor->products()->where('is_active', true)->count(),
            'total_sales' => $vendor->orderItems()->sum('total'),
            'total_orders' => $vendor->orderItems()->distinct('order_id')->count(),
            'total_bookings' => $totalBookings,
            'confirmed_bookings' => $confirmedBookings,
            'booking_revenue' => $bookingRevenue,
        ];

        $vendorOrders = \App\Models\Order::whereHas('items', function ($query) use ($vendor) {
            $query->where('vendor_id', $vendor->id);
        });

        $cancelledOrders = (clone $vendorOrders)->where('status', 'cancelled')->count();
        $deliveredOrders = (clone $vendorOrders)->whereNotNull('delivered_at')->get();
        $lateShipments = (clone $vendorOrders)
            ->whereNotNull('shipped_at')
            ->whereRaw('TIMESTAMPDIFF(DAY, created_at, shipped_at) > 3')
            ->count();

        $stats['cancellation_rate'] = $stats['total_orders'] > 0
            ? round(($cancelledOrders / $stats['total_orders']) * 100, 1)
            : 0;
        $stats['avg_fulfillment_days'] = round($deliveredOrders->avg(function ($order) {
            return $order->delivered_at->diffInDays($order->created_at);
        }) ?? 0, 1);
        $stats['late_shipments'] = $lateShipments;

        $recentBookings = Booking::with(['salonService', 'user'])
            ->whereIn('salon_service_id', $serviceIds)
            ->latest()
            ->take(5)
            ->get();

        return view('admin.vendors.show', compact('vendor', 'stats', 'recentBookings'));
    }

    public function message(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'channel' => 'required|in:email',
        ]);

        $to = $vendor->business_email ?: $vendor->user?->email;
        if (! $to) {
            return back()->with('error', 'No recipient email found for this vendor.');
        }

        Mail::html(nl2br(e($validated['message'])), function ($mail) use ($to, $validated, $vendor) {
            $mail->to($to)
                ->subject('[Marketplace Admin] '.$validated['subject']);
            if ($vendor->user?->email && $vendor->user->email !== $to) {
                $mail->cc($vendor->user->email);
            }
        });

        return back()->with('success', 'Message sent to vendor successfully.');
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load('user');

        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($vendor->user_id)],
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email',
            'business_phone' => 'required|string|max:20',
            'business_address' => 'required|string',
            'business_registration_number' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:pending,approved,suspended,rejected',
        ]);

        try {
            DB::beginTransaction();

            $vendor->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            $wasApproved = $vendor->status === 'approved';
            $isNowApproved = $validated['status'] === 'approved';

            $vendor->update([
                'business_name' => $validated['business_name'],
                'slug' => Str::slug($validated['business_name']),
                'business_email' => $validated['business_email'],
                'business_phone' => $validated['business_phone'],
                'business_address' => $validated['business_address'],
                'business_registration_number' => $validated['business_registration_number'] ?? null,
                'tax_id' => $validated['tax_id'] ?? null,
                'description' => $validated['description'] ?? null,
                'commission_rate' => $validated['commission_rate'],
                'status' => $validated['status'],
                'is_active' => $isNowApproved,
                'approved_at' => (! $wasApproved && $isNowApproved) ? now() : $vendor->approved_at,
            ]);

            DB::commit();

            return redirect()->route('admin.vendors.index')
                ->with('success', 'Vendor updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Failed to update vendor: '.$e->getMessage());
        }
    }

    public function destroy(Vendor $vendor)
    {
        try {
            DB::beginTransaction();

            if ($vendor->products()->count() > 0) {
                return back()->with('error', 'Cannot delete vendor with existing products. Please remove all products first.');
            }

            $user = $vendor->user;
            $vendor->delete();
            $user->delete();

            DB::commit();

            return redirect()->route('admin.vendors.index')
                ->with('success', 'Vendor deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to delete vendor: '.$e->getMessage());
        }
    }
}
