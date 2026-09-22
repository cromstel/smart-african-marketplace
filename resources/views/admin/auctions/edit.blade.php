@extends('admin.layout')

@section('title', 'Edit Auction - ' . config('app.name', 'Smart African Market'))

@section('header', 'Edit Auction')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.auctions.show', $auction) }}" class="btn btn-outline-secondary">
            ← Back to Auction
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.auctions.update', $auction) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Auction Title</label>
                            <input type="text" name="title" id="title" class="form-control" maxlength="255" value="{{ old('title', $auction->title) }}">
                            @error('title')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control">{{ old('description', $auction->description) }}</textarea>
                            @error('description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Auction Type</label>
                                <select name="type" id="type" class="form-select">
                                    <option value="english" {{ $auction->type === 'english' ? 'selected' : '' }}>English Auction (Price rises)</option>
                                    <option value="dutch" {{ $auction->type === 'dutch' ? 'selected' : '' }}>Dutch Auction (Price falls)</option>
                                    <option value="sealed" {{ $auction->type === 'sealed' ? 'selected' : '' }}>Sealed Bid</option>
                                    <option value="buy_now" {{ $auction->type === 'buy_now' ? 'selected' : '' }}>Buy Now Only</option>
                                </select>
                                @error('type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    @foreach (['draft', 'scheduled', 'active', 'ended', 'cancelled', 'settled'] as $status)
                                        <option value="{{ $status }}" {{ $auction->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="mb-3">Pricing</h6>

                                <div class="mb-3">
                                    <label for="starting_price" class="form-label">Starting Price</label>
                                    <input type="number" name="starting_price" id="starting_price" class="form-control" min="0.01" step="0.01" value="{{ old('starting_price', $auction->starting_price) }}">
                                    @error('starting_price')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="reserve_price" class="form-label">Reserve Price</label>
                                    <input type="number" name="reserve_price" id="reserve_price" class="form-control" min="0" step="0.01" value="{{ old('reserve_price', $auction->reserve_price) }}">
                                    @error('reserve_price')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="buy_now_price" class="form-label">Buy Now Price</label>
                                    <input type="number" name="buy_now_price" id="buy_now_price" class="form-control" min="0" step="0.01" value="{{ old('buy_now_price', $auction->buy_now_price) }}">
                                    @error('buy_now_price')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="bid_increment" class="form-label">Bid Increment</label>
                                    <input type="number" name="bid_increment" id="bid_increment" class="form-control" min="0.01" step="0.01" value="{{ old('bid_increment', $auction->bid_increment) }}">
                                    @error('bid_increment')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="mb-3">Schedule</h6>

                                <div class="mb-3">
                                    <label for="starts_at" class="form-label">Start Time</label>
                                    <input type="datetime-local" name="starts_at" id="starts_at" class="form-control" value="{{ old('starts_at', $auction->starts_at?->format('Y-m-d\TH:i')) }}">
                                    @error('starts_at')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="ends_at" class="form-label">End Time</label>
                                    <input type="datetime-local" name="ends_at" id="ends_at" class="form-control" value="{{ old('ends_at', $auction->ends_at?->format('Y-m-d\TH:i')) }}">
                                    @error('ends_at')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="extension_minutes" class="form-label">Extension (Anti-sniping)</label>
                                    <select name="extension_minutes" id="extension_minutes" class="form-select">
                                        <option value="1" {{ $auction->extension_minutes == 1 ? 'selected' : '' }}>1 minute</option>
                                        <option value="5" {{ $auction->extension_minutes == 5 ? 'selected' : '' }}>5 minutes</option>
                                        <option value="10" {{ $auction->extension_minutes == 10 ? 'selected' : '' }}>10 minutes</option>
                                        <option value="15" {{ $auction->extension_minutes == 15 ? 'selected' : '' }}>15 minutes</option>
                                        <option value="30" {{ $auction->extension_minutes == 30 ? 'selected' : '' }}>30 minutes</option>
                                        <option value="60" {{ $auction->extension_minutes == 60 ? 'selected' : '' }}>1 hour</option>
                                    </select>
                                    @error('extension_minutes')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.auctions.show', $auction) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Auction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
