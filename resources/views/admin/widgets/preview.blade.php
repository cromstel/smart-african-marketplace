@extends('admin.layout')

@section('title', 'Widget Preview')
@section('header', 'Widget Preview')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h3 class="text-2xl font-bold text-white">Widget Preview</h3>
        <p class="text-gray-400 mt-1">Preview widget rendering per page and region.</p>
    </div>
    <a href="{{ route('admin.widgets.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Back to Widgets</a>
</div>

<form method="GET" class="mb-6">
    <div class="flex items-center gap-3">
        <select name="page" class="bg-gray-700 border border-gray-600 text-white rounded px-3 py-2">
            @foreach($pages as $page)
                <option value="{{ $page }}" {{ $selectedPage === $page ? 'selected' : '' }}>{{ $page }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-brand-gradient text-white px-4 py-2 rounded-lg">Preview</button>
    </div>
</form>

<div class="space-y-8">
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
        <div class="text-sm text-gray-400 mb-3">Header Region</div>
        @include('components.widgets-renderer', ['page' => $selectedPage, 'region' => 'header'])
    </div>
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
        <div class="text-sm text-gray-400 mb-3">Main Region</div>
        @include('components.widgets-renderer', ['page' => $selectedPage, 'region' => 'main'])
    </div>
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-4">
        <div class="text-sm text-gray-400 mb-3">Footer Region</div>
        @include('components.widgets-renderer', ['page' => $selectedPage, 'region' => 'footer'])
    </div>
</div>
@endsection

