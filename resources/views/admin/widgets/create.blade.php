@extends('admin.layout')

@section('title', 'Add Widget')
@section('header', 'Add Widget')

@section('content')
<form action="{{ route('admin.widgets.store') }}" method="POST" class="space-y-6">
    @csrf
    @include('admin.widgets._form')
    <div class="flex gap-4">
        <button type="submit" class="bg-brand-gradient text-white px-6 py-3 rounded-lg font-medium">Create Widget</button>
        <a href="{{ route('admin.widgets.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium">Cancel</a>
    </div>
</form>
@endsection

