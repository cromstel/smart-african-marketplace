<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $searchTerm = trim((string) $request->query('q', ''));

        $notificationsQuery = UserNotification::where('user_id', auth()->id());

        if ($filter === 'unread') {
            $notificationsQuery->where('is_read', false);
        }

        if ($searchTerm !== '') {
            $notificationsQuery->where(function ($builder) use ($searchTerm) {
                $builder->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('message', 'like', "%{$searchTerm}%");
            });
        }

        $notifications = $notificationsQuery->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $unreadCount = auth()->user()->userNotifications()->where('is_read', false)->count();
        $preferences = $this->effectivePreferences(auth()->user()->notification_preferences);

        return view('notifications.index', compact('notifications', 'filter', 'searchTerm', 'unreadCount', 'preferences'));
    }

    public function markRead(UserNotification $notification)
    {
        $this->ensureOwnership($notification);
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function markAll(Request $request)
    {
        auth()->user()->userNotifications()->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'in_app_enabled' => 'nullable|boolean',
            'push_enabled' => 'nullable|boolean',
            'email_enabled' => 'nullable|boolean',
            'sms_enabled' => 'nullable|boolean',
            'booking_enabled' => 'nullable|boolean',
            'order_enabled' => 'nullable|boolean',
            'marketing_enabled' => 'nullable|boolean',
        ]);

        $preferences = $this->effectivePreferences([]);
        foreach (array_keys($preferences) as $key) {
            $preferences[$key] = (bool) ($validated[$key] ?? false);
        }

        auth()->user()->update([
            'notification_preferences' => $preferences,
        ]);

        return redirect()->back()->with('success', 'Notification preferences updated.');
    }

    public function updatePushToken(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'nullable|string|max:5000',
        ]);

        auth()->user()->update([
            'fcm_token' => $validated['fcm_token'] ?: null,
        ]);

        return redirect()->back()->with('success', 'Push token saved.');
    }

    private function ensureOwnership(UserNotification $notification): void
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }
    }

    private function effectivePreferences($preferences): array
    {
        $defaults = [
            'in_app_enabled' => true,
            'push_enabled' => false,
            'email_enabled' => true,
            'sms_enabled' => false,
            'booking_enabled' => true,
            'order_enabled' => true,
            'marketing_enabled' => false,
        ];

        if (! is_array($preferences)) {
            return $defaults;
        }

        return array_merge($defaults, array_intersect_key($preferences, $defaults));
    }
}
