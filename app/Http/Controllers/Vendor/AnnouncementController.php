<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $announcements = Announcement::active()
            ->forVendor($userId)
            ->with('reads')
            ->latest('is_pinned')
            ->latest('published_at')
            ->paginate(15);

        $unreadCount = Announcement::active()
            ->forVendor($userId)
            ->whereDoesntHave('reads', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->count();

        return view('vendor.announcements.index', compact('announcements', 'unreadCount'));
    }

    public function show(Announcement $announcement)
    {
        $userId = auth()->id();

        if (! $announcement->isPublished() || $announcement->isExpired()) {
            abort(404);
        }

        if ($announcement->target === 'specific_vendors' &&
            ! in_array($userId, $announcement->vendor_ids ?? [])) {
            abort(403);
        }

        $announcement->markAsReadBy($userId);

        return view('vendor.announcements.show', compact('announcement'));
    }

    public function markAsRead(Announcement $announcement)
    {
        $announcement->markAsReadBy(auth()->id());

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $userId = auth()->id();

        $announcements = Announcement::active()
            ->forVendor($userId)
            ->get();

        foreach ($announcements as $announcement) {
            $announcement->markAsReadBy($userId);
        }

        return redirect()
            ->route('vendor.announcements.index')
            ->with('success', 'All announcements marked as read');
    }
}
