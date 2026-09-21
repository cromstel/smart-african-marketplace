<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorVerification;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function index()
    {
        $vendorId = auth()->user()->vendor?->id;
        // Pass 'status' explicitly on create so a freshly created record's
        // in-memory model has it set immediately -- firstOrCreate()'s create()
        // path doesn't re-fetch the row, so relying on the migration's
        // DB-level default alone left $verification->status null on a
        // vendor's first visit to this page.
        $verification = VendorVerification::firstOrCreate(
            ['vendor_id' => $vendorId],
            ['status' => 'pending']
        );

        return view('vendor.verification.index', compact('verification'));
    }

    public function store(Request $request)
    {
        $vendorId = auth()->user()->vendor?->id;
        $verification = VendorVerification::firstOrCreate(
            ['vendor_id' => $vendorId],
            ['status' => 'pending']
        );

        $validated = $request->validate([
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $docs = $verification->documents ?? [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store("vendor-kyc/{$vendorId}", 'local');
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
