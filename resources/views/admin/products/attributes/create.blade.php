@extends('admin.layout')

@section('title', 'Create Attribute')
@section('header', 'Create Attribute')

@section('content')
<section class="bg-gradient-to-b from-gray-900 to-gray-800 rounded-xl border border-gray-700 p-6 max-w-4xl">
    <header class="mb-6">
        <h1 class="text-3xl font-semibold text-white">Attributes</h1>
        <p class="text-sm text-gray-400 mt-1">Add new attribute</p>
    </header>

    <form method="POST" action="{{ route('admin.products.attributes.store') }}" class="space-y-6">
        @csrf
        @include('admin.products.attributes._form')

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="rounded bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Create Attribute</button>
            <a href="{{ route('admin.products.attributes.index') }}" class="rounded bg-gray-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-600">Cancel</a>
        </div>
    </form>
</section>
@endsection


