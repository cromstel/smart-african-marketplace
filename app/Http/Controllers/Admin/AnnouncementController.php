<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::withCount('reads')
            ->latest()
            ->paginate(20);

        $stats = [
            'total' => Announcement::count(),
            'active' => Announcement::active()->count(),
            'pinned' => Announcement::pinned()->count(),
            'expired' => Announcement::whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count(),
        ];

        return view('admin.announcements.index', compact('announcements', 'stats'));
    }

    public function create()
    {
        $vendors = User::where('role', 'vendor')->get();

        return view('admin.announcements.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,warning,success,danger',
            'target' => 'required|in:all_vendors,specific_vendors',
            'vendor_ids' => 'nullable|array',
            'vendor_ids.*' => 'exists:users,id',
            'is_active' => 'boolean',
            'is_pinned' => 'boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        if ($validated['target'] === 'all_vendors') {
            $validated['vendor_ids'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_pinned'] = $request->boolean('is_pinned', false);

        Announcement::create($validated);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully');
    }

    public function edit(Announcement $announcement)
    {
        $vendors = User::where('role', 'vendor')->get();

        return view('admin.announcements.edit', compact('announcement', 'vendors'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,warning,success,danger',
            'target' => 'required|in:all_vendors,specific_vendors',
            'vendor_ids' => 'nullable|array',
            'vendor_ids.*' => 'exists:users,id',
            'is_active' => 'boolean',
            'is_pinned' => 'boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        if ($validated['target'] === 'all_vendors') {
            $validated['vendor_ids'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active', $announcement->is_active);
        $validated['is_pinned'] = $request->boolean('is_pinned', $announcement->is_pinned);

        $announcement->update($validated);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully');
    }

    public function toggle(Announcement $announcement)
    {
        $announcement->update(['is_active' => ! $announcement->is_active]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement status updated successfully');
    }

    public function togglePin(Announcement $announcement)
    {
        $announcement->update(['is_pinned' => ! $announcement->is_pinned]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement pin status updated successfully');
    }
}
