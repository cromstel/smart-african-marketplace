<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Vendor;
use App\Models\VendorVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorApplicationController extends Controller
{
    public function create(Request $request)
    {
        $vendor = $request->user()->vendor;

        if ($vendor) {
            return redirect()->route('vendor.application.status');
        }

        return view('vendor.apply');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->vendor) {
            return redirect()->route('vendor.application.status');
        }

        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email',
            'business_phone' => 'required|string|max:20',
            'business_address' => 'required|string|max:255',
            'business_registration_number' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ]);

        $commissionRate = (float) Setting::get('platform_commission_rate', 10);

        DB::beginTransaction();

        try {
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
                'commission_rate' => $commissionRate,
                'status' => 'pending',
                'is_active' => false,
            ]);

            VendorVerification::firstOrCreate(['vendor_id' => $vendor->id], [
                'status' => 'pending',
                'submitted_ip' => $request->ip(),
                'submitted_user_agent' => (string) $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('vendor.application.status')
                ->with('success', 'Application submitted successfully. Please upload verification documents.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Failed to submit application: '.$e->getMessage());
        }
    }

    public function status(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (! $vendor) {
            return redirect()->route('vendor.apply')->with('error', 'You do not have a vendor application yet.');
        }

        // 'status' is passed explicitly (matching the migration's column
        // default) so a freshly created record's in-memory model has it
        // set immediately -- firstOrCreate()'s create() path doesn't
        // re-fetch the row, so relying on the DB-level default alone left
        // $verification->status null on a vendor's first status check.
        $verification = VendorVerification::firstOrCreate(['vendor_id' => $vendor->id], ['status' => 'pending']);

        return view('vendor.application-status', compact('vendor', 'verification'));
    }

    public function storeDocuments(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (! $vendor) {
            return redirect()->route('vendor.apply');
        }

        $verification = VendorVerification::firstOrCreate(['vendor_id' => $vendor->id], ['status' => 'pending']);

        $validated = $request->validate([
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $docs = $verification->documents ?? [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store("vendor-kyc/{$vendor->id}", 'local');
                $docs[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'sha256' => hash_file('sha256', $file->getRealPath()),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

        $verification->update([
            'documents' => $docs,
            'notes' => $validated['notes'] ?? $verification->notes,
            'status' => 'pending',
            'submitted_ip' => $verification->submitted_ip ?? $request->ip(),
            'submitted_user_agent' => $verification->submitted_user_agent ?? (string) $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Verification documents submitted.');
    }
}
