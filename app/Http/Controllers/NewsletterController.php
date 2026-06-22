<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $leadContext = (array) session('lead_context', []);

        Newsletter::updateOrCreate(
            ['email' => $validated['email']],
            [
                'status' => 'subscribed',
                'utm_source' => $leadContext['utm_source'] ?? $request->query('utm_source'),
                'utm_medium' => $leadContext['utm_medium'] ?? $request->query('utm_medium'),
                'utm_campaign' => $leadContext['utm_campaign'] ?? $request->query('utm_campaign'),
                'utm_term' => $leadContext['utm_term'] ?? $request->query('utm_term'),
                'utm_content' => $leadContext['utm_content'] ?? $request->query('utm_content'),
                'referrer' => $leadContext['referrer'] ?? $request->headers->get('referer'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->session()->getId(),
                'user_id' => auth()->id(),
            ]
        );

        return back()->with('success', __('messages.newsletter_subscribed'));
    }

    public function unsubscribe(Request $request)
    {
        $email = $request->input('email');

        Newsletter::where('email', $email)->update(['status' => 'unsubscribed']);

        return back()->with('success', __('messages.newsletter_unsubscribed'));
    }
}
