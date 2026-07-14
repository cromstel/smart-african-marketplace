<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\DataExportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    public function download(Request $request, DataExportRequest $dataExportRequest)
    {
        if ($dataExportRequest->requested_by !== Auth::id()) {
            abort(403);
        }

        if ($dataExportRequest->status !== DataExportRequest::STATUS_READY) {
            abort(404);
        }

        $token = $request->query('token');
        if (! $token || $token !== $dataExportRequest->token) {
            abort(403);
        }

        $path = $dataExportRequest->archive_path;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path);
    }
}
