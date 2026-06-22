@extends('admin.layout')

@section('title', 'Edit Widget')
@section('header', 'Edit Widget')

@section('content')
<form action="{{ route('admin.widgets.update', $widget) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')
    @include('admin.widgets._form')
    <div class="flex gap-4">
        <button type="submit" class="bg-brand-gradient text-white px-6 py-3 rounded-lg font-medium">Update Widget</button>
        <a href="{{ route('admin.widgets.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">Cancel</a>
    </div>
</form>
@endsection

