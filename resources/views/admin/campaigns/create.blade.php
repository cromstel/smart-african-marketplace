@extends('admin.layout')

@section('title', 'Create Campaign')
@section('header', 'Create Campaign')

@section('content')
<div class="max-w-4xl">
    <form method="POST" action="{{ route('admin.campaigns.store') }}" class="space-y-6">
        @csrf
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-4">
            <div>
                <label class="block text-sm text-gray-300 mb-2">Campaign Name</label>
                <input name="name" value="{{ old('name') }}" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Subject</label>
                <input name="subject" value="{{ old('subject') }}" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Campaign Type</label>
                    <select name="campaign_type" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        @foreach(['standard','welcome','abandoned_cart','promotional','flash_sale','win_back'] as $type)
                            <option value="{{ $type }}" {{ old('campaign_type', 'standard') === $type ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Audience Segment</label>
                    <select name="audience_segment" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        @foreach(['all','new','active','vip','abandoned_cart','newsletter_new','inactive'] as $segment)
                            <option value="{{ $segment }}" {{ old('audience_segment', 'all') === $segment ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $segment)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Promotional Banner URL (optional)</label>
                <input name="banner_image_url" value="{{ old('banner_image_url') }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Scheduled At (optional)</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Flash Sale Ends At (optional)</label>
                <input type="datetime-local" name="flash_sale_ends_at" value="{{ old('flash_sale_ends_at') }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Content</label>
                <textarea name="content" rows="8" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('content') }}</textarea>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.campaigns.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-5 py-2 rounded-lg">Cancel</a>
            <button type="submit" class="bg-brand-gradient text-white px-5 py-2 rounded-lg">Save Campaign</button>
        </div>
    </form>
</div>
@endsection

