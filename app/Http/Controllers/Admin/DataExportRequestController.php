<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataExportRequest;
use App\Services\DataExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DataExportRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = DataExportRequest::with(['vendor.user', 'requester']);

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $exportRequests = $query->latest()->paginate(20)->withQueryString();

        return view('admin.data_exports.index', compact('exportRequests'));
    }

    public function show(DataExportRequest $dataExportRequest)
    {
        $dataExportRequest->load(['vendor.user', 'requester', 'approver']);

        return view('admin.data_exports.show', compact('dataExportRequest'));
    }

    public function approve(DataExportRequest $dataExportRequest, DataExportService $exportService)
    {
        if ($dataExportRequest->status !== DataExportRequest::STATUS_PENDING) {
            return back()->withErrors(['status' => 'Only pending requests can be approved.']);
        }

        $user = $dataExportRequest->requester ?? $dataExportRequest->vendor?->user;
        if (! $user) {
            return back()->withErrors(['user' => 'Request lacks an associated user.']);
        }

        $payload = $exportService->export($user);
        $path = 'data-exports/data-export-'.$dataExportRequest->id.'-'.now()->format('YmdHis').'.json';
        Storage::disk('local')->put($path, $payload);

        $dataExportRequest->markReady($path, Auth::id());

        return back()->with('success', 'Export approved and ready for download.');
    }

    public function reject(Request $request, DataExportRequest $dataExportRequest)
    {
        if ($dataExportRequest->status !== DataExportRequest::STATUS_PENDING) {
            return back()->withErrors(['status' => 'Only pending requests can be rejected.']);
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'max:1000'],
        ]);

        $dataExportRequest->markRejected($validated['admin_notes'], Auth::id());

        return back()->with('success', 'Export request rejected.');
    }

    public function download(Request $request, DataExportRequest $dataExportRequest)
    {
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
