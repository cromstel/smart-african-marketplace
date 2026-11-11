@extends('layouts.shop')

@section('title', 'Edit Address')

@section('content')
<x-hero-section
    pageIdentifier="addresses"
    fallbackTitle="Edit Address"
    fallbackSubtitle="Update your saved address details"
    fallbackImage="/images/hero-banners/hero-banner.avif"
    
/>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
        <form action="{{ route('addresses.update', $address) }}" method="POST">
            @include('addresses.partials.form')
        </form>
    </div>
</div>
@endsection
