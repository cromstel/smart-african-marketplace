@extends('admin.layout')

@section('title', 'Chatbot Settings')
@section('header', 'Chatbot Settings')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.chatbot.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-4">
            <label class="flex items-center gap-3 text-gray-300">
                <input type="checkbox" name="chatbot_enabled" value="1" {{ $settings['chatbot_enabled'] ? 'checked' : '' }}>
                Enable Chatbot
            </label>
            <label class="flex items-center gap-3 text-gray-300">
                <input type="checkbox" name="chatbot_fallback_enabled" value="1" {{ $settings['chatbot_fallback_enabled'] ? 'checked' : '' }}>
                Enable Fallback Responses
            </label>
            <div class="border-t border-gray-700 pt-4 space-y-3">
                <p class="text-sm text-gray-300 font-semibold">Visibility By Page Type</p>
                <p class="text-xs text-gray-400">These page-level options apply only when <strong>Enable Chatbot</strong> is turned on.</p>
                <label class="flex items-center gap-3 text-gray-300">
                    <input type="checkbox" name="chatbot_show_on_home" value="1" {{ $settings['chatbot_show_on_home'] ? 'checked' : '' }}>
                    Show on Home Page
                </label>
                <label class="flex items-center gap-3 text-gray-300">
                    <input type="checkbox" name="chatbot_show_on_shop_pages" value="1" {{ $settings['chatbot_show_on_shop_pages'] ? 'checked' : '' }}>
                    Show on Shop/Public Pages (Pricing, Shop, Salons, Blog, About, Contact)
                </label>
                <label class="flex items-center gap-3 text-gray-300">
                    <input type="checkbox" name="chatbot_show_on_vendor_storefront" value="1" {{ $settings['chatbot_show_on_vendor_storefront'] ? 'checked' : '' }}>
                    Show on Vendor Storefront Pages
                </label>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Greeting Message</label>
                <textarea name="chatbot_greeting" rows="3" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('chatbot_greeting', $settings['chatbot_greeting']) }}</textarea>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">OpenAI Model</label>
                <input name="chatbot_openai_model" value="{{ old('chatbot_openai_model', $settings['chatbot_openai_model']) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Context Message Limit</label>
                    <input type="number" name="chatbot_context_message_limit" min="1" max="20" value="{{ old('chatbot_context_message_limit', $settings['chatbot_context_message_limit']) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Rate Limit (per minute)</label>
                    <input type="number" name="chatbot_rate_limit_per_minute" min="10" max="600" value="{{ old('chatbot_rate_limit_per_minute', $settings['chatbot_rate_limit_per_minute']) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Support Inbox Email</label>
                <input type="email" name="support_ticket_inbox_email" value="{{ old('support_ticket_inbox_email', $settings['support_ticket_inbox_email']) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2" placeholder="support@yourdomain.com">
            </div>
        </div>
        <button type="submit" class="bg-brand-gradient text-white px-5 py-2 rounded-lg">Save Settings</button>
    </form>
</div>
@endsection

