<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorVerification;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorVerificationController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function index(Request $request)
    {
        $query = VendorVerification::with(['vendor.user', 'reviewer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));
            $query->whereHas('vendor', function ($vendorQuery) use ($search) {
                $vendorQuery->where('business_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $verifications = $query->latest()->paginate(20)->withQueryString();

        return view('admin.vendor-verifications.index', compact('verifications'));
    }

    public function show(VendorVerification $verification)
    {
        $verification->load(['vendor.user', 'reviewer']);

        return view('admin.vendor-verifications.show', compact('verification'));
    }

    public function approve(Request $request, VendorVerification $verification)
    {
        // Guard against re-approving (or approving after a rejection) with a
        // stale page open in two tabs, or a resubmitted-but-unreviewed record
        // -- only a still-pending verification should be actionable here.
        // Without this, clicking Approve on an already-decided verification
        // would silently re-stamp reviewed_at/reviewed_by and could revert a
        // vendor a second admin had just rejected back to approved.
        if ($verification->status !== 'pending') {
            return redirect()->route('admin.vendor-verifications.show', $verification)
                ->with('error', 'This verification has already been reviewed.');
        }

        $vendor = $verification->vendor;

        $verification->update([
            'status' => 'approved',
            'verified_at' => now(),
            'reviewed_by' => $request->user()->id,
            'reviewed_ip' => $request->ip(),
            'reviewed_at' => now(),
        ]);

        if ($vendor) {
            $vendor->update([
                'status' => 'approved',
                'is_active' => true,
                'approved_at' => $vendor->approved_at ?? now(),
            ]);

            if ($vendor->user) {
                $vendor->user->update(['role' => 'vendor']);
                $this->subscriptionService->assignFreePlan($vendor->user);
            }
        }

        return redirect()->route('admin.vendor-verifications.show', $verification)
            ->with('success', 'Vendor verification approved.');
    }

    public function reject(Request $request, VendorVerification $verification)
    {
        // Same guard as approve(): a decided verification (approved or
        // rejected) shouldn't be actionable again until the vendor
        // resubmits, which resets it to 'pending' (see
        // Vendor\VerificationController::store()).
        if ($verification->status !== 'pending') {
            return redirect()->route('admin.vendor-verifications.show', $verification)
                ->with('error', 'This verification has already been reviewed.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $verification->update([
            'status' => 'rejected',
            'notes' => $validated['notes'] ?? $verification->notes,
            'reviewed_by' => $request->user()->id,
            'reviewed_ip' => $request->ip(),
            'reviewed_at' => now(),
        ]);

        if ($verification->vendor) {
            $verification->vendor->update([
                'status' => 'rejected',
                'is_active' => false,
            ]);
        }

        return redirect()->route('admin.vendor-verifications.show', $verification)
            ->with('success', 'Vendor verification rejected.');
    }

    public function document(Request $request, VendorVerification $verification, int $index)
    {
        $docs = $verification->documents ?? [];

        $entry = $docs[$index] ?? null;
        if (! $entry) {
            abort(404);
        }

        $path = is_array($entry) ? ($entry['path'] ?? null) : $entry;
        if (! $path) {
            abort(404);
        }
        if (! $this->isSafeStoragePath($path)) {
            abort(404);
        }
        $filename = is_array($entry) ? ($entry['original_name'] ?? basename($path)) : basename($path);

        if (Storage::disk('local')->exists($path)) {
            return response()->download(storage_path('app/'.$path), $filename);
        }

        if (Storage::disk('public')->exists($path)) {
            return response()->download(storage_path('app/public/'.$path), $filename);
        }

        abort(404);
    }

    private function isSafeStoragePath(string $path): bool
    {
        if (Str::contains($path, ['..', "\0"])) {
            return false;
        }

        if (Str::startsWith($path, ['/', '\\'])) {
            return false;
        }

        if (preg_match('/^[A-Za-z]:\\\\/', $path) === 1) {
            return false;
        }

        return true;
    }
}
