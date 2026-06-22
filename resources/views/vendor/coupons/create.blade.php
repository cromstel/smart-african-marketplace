@extends('vendor.layout')

@section('title', 'Create Coupon')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-white">Create Coupon</h2>
</div>

<form method="POST" action="{{ route('vendor.coupons.store') }}" class="bg-gray-800 rounded-lg border border-gray-700 p-6 space-y-4">
    @csrf
    @include('vendor.coupons.partials.form', ['coupon' => null])
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Save</button>
</form>
@endsection

