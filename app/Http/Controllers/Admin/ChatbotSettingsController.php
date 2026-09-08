<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatbotSettingsController extends Controller
{
    public function index(): View
    {
        $settings = [
            'chatbot_enabled' => Setting::get('chatbot_enabled', true),
            'chatbot_greeting' => Setting::get('chatbot_greeting', 'Hello! Welcome to '.config('app.name').'. How can I help you today?'),
            'chatbot_openai_model' => Setting::get('chatbot_openai_model', 'gpt-4o-mini'),
            'chatbot_fallback_enabled' => Setting::get('chatbot_fallback_enabled', true),
            'chatbot_show_on_home' => Setting::get('chatbot_show_on_home', true),
            'chatbot_show_on_shop_pages' => Setting::get('chatbot_show_on_shop_pages', true),
            'chatbot_show_on_vendor_storefront' => Setting::get('chatbot_show_on_vendor_storefront', true),
            'support_ticket_inbox_email' => Setting::get('support_ticket_inbox_email', ''),
            'chatbot_context_message_limit' => Setting::get('chatbot_context_message_limit', 8),
            'chatbot_rate_limit_per_minute' => Setting::get('chatbot_rate_limit_per_minute', 60),
        ];

        return view('admin.chatbot.settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'chatbot_enabled' => 'nullable|boolean',
            'chatbot_greeting' => 'required|string|max:500',
            'chatbot_openai_model' => 'required|string|max:100',
            'chatbot_fallback_enabled' => 'nullable|boolean',
            'chatbot_show_on_home' => 'nullable|boolean',
            'chatbot_show_on_shop_pages' => 'nullable|boolean',
            'chatbot_show_on_vendor_storefront' => 'nullable|boolean',
            'support_ticket_inbox_email' => 'nullable|email|max:255',
            'chatbot_context_message_limit' => 'required|integer|min:1|max:20',
            'chatbot_rate_limit_per_minute' => 'required|integer|min:10|max:600',
        ]);

        Setting::set('chatbot_enabled', (bool) ($validated['chatbot_enabled'] ?? false));
        Setting::set('chatbot_greeting', $validated['chatbot_greeting']);
        Setting::set('chatbot_openai_model', $validated['chatbot_openai_model']);
        Setting::set('chatbot_fallback_enabled', (bool) ($validated['chatbot_fallback_enabled'] ?? false));
        Setting::set('chatbot_show_on_home', (bool) ($validated['chatbot_show_on_home'] ?? false));
        Setting::set('chatbot_show_on_shop_pages', (bool) ($validated['chatbot_show_on_shop_pages'] ?? false));
        Setting::set('chatbot_show_on_vendor_storefront', (bool) ($validated['chatbot_show_on_vendor_storefront'] ?? false));
        Setting::set('support_ticket_inbox_email', $validated['support_ticket_inbox_email'] ?? '');
        Setting::set('chatbot_context_message_limit', (int) $validated['chatbot_context_message_limit'], 'integer');
        Setting::set('chatbot_rate_limit_per_minute', (int) $validated['chatbot_rate_limit_per_minute'], 'integer');

        return back()->with('success', 'Chatbot settings updated.');
    }
}
