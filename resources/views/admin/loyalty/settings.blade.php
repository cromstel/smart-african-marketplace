@extends('admin.layout')

@section('title', 'Loyalty Settings')
@section('header', 'Loyalty Settings')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.loyalty.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-4">
            <div>
                <label class="block text-sm text-gray-300 mb-2">Points Per Dollar</label>
                <input
                    type="number"
                    name="loyalty_points_per_dollar"
                    min="1"
                    max="100"
                    value="{{ old('loyalty_points_per_dollar', \App\Models\Setting::getValue('loyalty_points_per_dollar', 1)) }}"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                    required
                >
            </div>

            <div>
                <label class="block text-sm text-gray-300 mb-2">Point Value (in currency)</label>
                <input
                    type="number"
                    name="loyalty_points_value"
                    step="0.001"
                    min="0.001"
                    max="1"
                    value="{{ old('loyalty_points_value', \App\Models\Setting::getValue('loyalty_points_value', 0.01)) }}"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2"
                    required
                >
            </div>

            @if($errors->any())
                <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.loyalty.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-5 py-2 rounded-lg">Back</a>
            <button type="submit" class="bg-brand-gradient text-white px-5 py-2 rounded-lg">Save Settings</button>
        </div>
    </form>
</div>
@endsection

