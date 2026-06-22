<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\DataExportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataExportRequestController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $existing = DataExportRequest::where('requested_by', $user->id)
            ->whereIn('status', [DataExportRequest::STATUS_PENDING, DataExportRequest::STATUS_READY])
            ->latest()
            ->first();

        return view('vendor.data-export.request', [
            'existing' => $existing,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $openRequest = DataExportRequest::where('requested_by', $user->id)
            ->whereIn('status', [DataExportRequest::STATUS_PENDING, DataExportRequest::STATUS_READY])
            ->exists();

        if ($openRequest) {
            return back()->withErrors(['reason' => 'You already have an open export request.'])->withInput();
        }

        DataExportRequest::create([
            'vendor_id' => $user->vendor?->id,
            'requested_by' => $user->id,
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'status' => DataExportRequest::STATUS_PENDING,
        ]);

        return redirect()->route('vendor.data-export.request')->with('success', 'Export request submitted. An admin will review it.');
    }
}
