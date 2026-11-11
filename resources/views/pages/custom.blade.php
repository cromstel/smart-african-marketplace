@extends('layouts.shop')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-3xl font-bold text-gray-900">{{ $page->title }}</h2>
            @if($page->excerpt)
                <p class="text-gray-600 mt-3">{{ $page->excerpt }}</p>
            @endif
            <div class="prose prose-gray max-w-none mt-6">
                {!! nl2br(e($page->content)) !!}
            </div>
        </div>
    </div>
</div>
@endsection

