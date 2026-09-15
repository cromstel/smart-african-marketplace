@extends('layouts.shop')

@section('title', 'Add Address')

@section('content')
<x-hero-section
    pageIdentifier="addresses"
    fallbackTitle="Add Address"
    fallbackSubtitle="Save a new shipping or billing address"
    fallbackImage="/images/hero-banners/hero-banner.avif"
    :animated="true"
/>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <form action="{{ route('addresses.store') }}" method="POST">
            @include('addresses.partials.form')
        </form>
    </div>
</div>
@endsection
