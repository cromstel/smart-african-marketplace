@extends('admin.layout')

@section('title', 'Edit Campaign')
@section('header', 'Edit Campaign')

@section('content')
<div class="max-w-4xl">
    <form method="POST" action="{{ route('admin.campaigns.update', $campaign) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-4">
            <div>
                <label class="block text-sm text-gray-300 mb-2">Campaign Name</label>
                <input name="name" value="{{ old('name', $campaign->name) }}" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Subject</label>
                <input name="subject" value="{{ old('subject', $campaign->subject) }}" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Campaign Type</label>
                    <select name="campaign_type" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        @foreach(['standard','welcome','abandoned_cart','promotional','flash_sale','win_back'] as $type)
                            <option value="{{ $type }}" {{ old('campaign_type', $campaign->campaign_type ?? 'standard') === $type ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Audience Segment</label>
                    <select name="audience_segment" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        @foreach(['all','new','active','vip','abandoned_cart','newsletter_new','inactive'] as $segment)
                            <option value="{{ $segment }}" {{ old('audience_segment', $campaign->audience_segment ?? 'all') === $segment ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $segment)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Promotional Banner URL</label>
                <input name="banner_image_url" value="{{ old('banner_image_url', $campaign->banner_image_url) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Status</label>
                    <select name="status" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        @foreach(['draft','scheduled','sending','sent'] as $status)
                            <option value="{{ $status }}" {{ old('status', $campaign->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Scheduled At</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', optional($campaign->scheduled_at)->format('Y-m-d\TH:i')) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Flash Sale Ends At</label>
                <input type="datetime-local" name="flash_sale_ends_at" value="{{ old('flash_sale_ends_at', optional($campaign->flash_sale_ends_at)->format('Y-m-d\TH:i')) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-2">Content</label>
                <textarea name="content" rows="8" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">{{ old('content', $campaign->content) }}</textarea>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.campaigns.show', $campaign) }}" class="bg-gray-700 hover:bg-gray-600 text-white px-5 py-2 rounded-lg">Cancel</a>
            <button type="submit" class="bg-brand-gradient text-white px-5 py-2 rounded-lg">Update Campaign</button>
        </div>
    </form>
</div>
@endsection

