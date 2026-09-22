@extends('vendor.layout')

@section('title', 'Create Auction - ' . config('app.name', 'Smart African Market'))
@section('header', 'Create Auction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('vendor.auctions.index') }}" class="btn btn-outline-light">
            ← Back to Auctions
        </a>
    </div>

    <div class="card bg-gray-800 border-gray-700">
        <div class="card-body">
            <form action="{{ route('vendor.auctions.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label text-gray-300">Auction Title *</label>
                            <input type="text" name="title" id="title" class="form-control bg-gray-700 border-gray-600 text-white" required maxlength="255" value="{{ old('title') }}">
                            @error('title')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label text-gray-300">Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control bg-gray-700 border-gray-600 text-white">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label text-gray-300">Auction Type</label>
                            <select name="type" id="type" class="form-select bg-gray-700 border-gray-600 text-white">
                                <option value="english" {{ old('type') === 'english' ? 'selected' : '' }}>English Auction (Price rises)</option>
                                <option value="dutch" {{ old('type') === 'dutch' ? 'selected' : '' }}>Dutch Auction (Price falls)</option>
                                <option value="sealed" {{ old('type') === 'sealed' ? 'selected' : '' }}>Sealed Bid</option>
                                <option value="buy_now" {{ old('type') === 'buy_now' ? 'selected' : '' }}>Buy Now Only</option>
                            </select>
                            @error('type')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-gray-700 border-gray-600 mb-3">
                            <div class="card-body">
                                <h6 class="text-gray-300 mb-3">Pricing</h6>
                                
                                <div class="mb-3">
                                    <label for="starting_price" class="form-label text-gray-300">Starting Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-gray-600 border-gray-500">$</span>
                                        <input type="number" name="starting_price" id="starting_price" class="form-control bg-gray-600 border-gray-500 text-white" required min="0.01" step="0.01" value="{{ old('starting_price') }}">
                                    </div>
                                    @error('starting_price')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="reserve_price" class="form-label text-gray-300">Reserve Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-gray-600 border-gray-500">$</span>
                                        <input type="number" name="reserve_price" id="reserve_price" class="form-control bg-gray-600 border-gray-500 text-white" min="0" step="0.01" value="{{ old('reserve_price') }}">
                                    </div>
                                    <small class="text-gray-400">Minimum price to sell (hidden)</small>
                                    @error('reserve_price')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="buy_now_price" class="form-label text-gray-300">Buy Now Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-gray-600 border-gray-500">$</span>
                                        <input type="number" name="buy_now_price" id="buy_now_price" class="form-control bg-gray-600 border-gray-500 text-white" min="0" step="0.01" value="{{ old('buy_now_price') }}">
                                    </div>
                                    <small class="text-gray-400">Instant purchase price</small>
                                    @error('buy_now_price')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="bid_increment" class="form-label text-gray-300">Bid Increment</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-gray-600 border-gray-500">$</span>
                                        <input type="number" name="bid_increment" id="bid_increment" class="form-control bg-gray-600 border-gray-500 text-white" min="0.01" step="0.01" value="{{ old('bid_increment', 1.00) }}">
                                    </div>
                                    @error('bid_increment')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card bg-gray-700 border-gray-600 mb-3">
                            <div class="card-body">
                                <h6 class="text-gray-300 mb-3">Schedule</h6>
                                
                                <div class="mb-3">
                                    <label for="starts_at" class="form-label text-gray-300">Start Time *</label>
                                    <input type="datetime-local" name="starts_at" id="starts_at" class="form-control bg-gray-600 border-gray-500 text-white" required value="{{ old('starts_at') }}">
                                    @error('starts_at')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="ends_at" class="form-label text-gray-300">End Time *</label>
                                    <input type="datetime-local" name="ends_at" id="ends_at" class="form-control bg-gray-600 border-gray-500 text-white" required value="{{ old('ends_at') }}">
                                    @error('ends_at')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="extension_minutes" class="form-label text-gray-300">Extension (Anti-sniping)</label>
                                    <select name="extension_minutes" id="extension_minutes" class="form-select bg-gray-600 border-gray-500 text-white">
                                        <option value="1" {{ old('extension_minutes') == 1 ? 'selected' : '' }}>1 minute</option>
                                        <option value="5" {{ old('extension_minutes') == 5 ? 'selected' : '' }}>5 minutes</option>
                                        <option value="10" {{ old('extension_minutes', 15) == 15 ? 'selected' : '' }}>10 minutes</option>
                                        <option value="15" {{ old('extension_minutes', 15) == 15 ? 'selected' : '' }}>15 minutes</option>
                                        <option value="30" {{ old('extension_minutes') == 30 ? 'selected' : '' }}>30 minutes</option>
                                        <option value="60" {{ old('extension_minutes') == 60 ? 'selected' : '' }}>1 hour</option>
                                    </select>
                                    <small class="text-gray-400">Extends auction if bid placed near end</small>
                                    @error('extension_minutes')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('vendor.auctions.index') }}" class="btn btn-outline-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Auction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

