@extends('vendor.layout')

@section('title', $auction->title . ' - ' . config('app.name', 'Smart African Marketplace'))
@section('header', 'Auction Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('vendor.auctions.index') }}" class="btn btn-outline-light">
            ← Back to Auctions
        </a>
        <div class="btn-group">
            @if($auction->status === 'draft')
                <form action="{{ route('vendor.auctions.publish', $auction) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Publish</button>
                </form>
            @endif
            @if(in_array($auction->status, ['draft', 'scheduled']))
                <a href="{{ route('vendor.auctions.edit', $auction) }}" class="btn btn-primary">Edit</a>
            @endif
            @if($auction->status === 'active')
                <a href="{{ route('vendor.auctions.bids', $auction) }}" class="btn btn-info">View Bids</a>
                <form action="{{ route('vendor.auctions.extend', $auction) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">Extend 1 Hour</button>
                </form>
            @endif
            @if($auction->status === 'ended' && !$auction->winner)
                <form action="{{ route('vendor.auctions.settle', $auction) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Settle Auction</button>
                </form>
            @endif
            @if(in_array($auction->status, ['draft', 'scheduled', 'active']))
                <form action="{{ route('vendor.auctions.destroy', $auction) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card bg-gray-800 border-gray-700 mb-4">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0">Auction Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless text-gray-300">
                        <tr>
                            <th width="200">Title:</th>
                            <td>{{ $auction->title }}</td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $auction->description ?? 'No description' }}</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td><span class="badge bg-info">{{ ucfirst($auction->type) }}</span></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ match($auction->status) {
                                    'active' => 'success',
                                    'ended' => 'warning',
                                    'settled' => 'primary',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                } }}">
                                    {{ ucfirst($auction->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card bg-gray-800 border-gray-700 mb-4">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0">Pricing</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless text-gray-300">
                        <tr>
                            <th width="200">Starting Price:</th>
                            <td>${{ number_format($auction->starting_price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Current Price:</th>
                            <td><strong class="text-success">${{ number_format($auction->current_price, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Reserve Price:</th>
                            <td>{{ $auction->reserve_price ? '$' . number_format($auction->reserve_price, 2) : 'Not set' }}</td>
                        </tr>
                        <tr>
                            <th>Buy Now Price:</th>
                            <td>{{ $auction->buy_now_price ? '$' . number_format($auction->buy_now_price, 2) : 'Not set' }}</td>
                        </tr>
                        <tr>
                            <th>Bid Increment:</th>
                            <td>${{ number_format($auction->bid_increment, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card bg-gray-800 border-gray-700">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0">Timing</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless text-gray-300">
                        <tr>
                            <th width="200">Starts At:</th>
                            <td>{{ $auction->starts_at?->format('M j, Y H:i') ?? 'Not set' }}</td>
                        </tr>
                        <tr>
                            <th>Ends At:</th>
                            <td>{{ $auction->ends_at?->format('M j, Y H:i') ?? 'Not set' }}</td>
                        </tr>
                        <tr>
                            <th>Extension Minutes:</th>
                            <td>{{ $auction->extension_minutes }} minutes</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-gray-800 border-gray-700 mb-4">
                <div class="card-header border-gray-700">
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h3 class="text-success">${{ number_format($auction->current_price, 2) }}</h3>
                        <p class="text-gray-400">Current Price</p>
                        <hr class="border-gray-700">
                        <div class="d-flex justify-content-around">
                            <div>
                                <h4>{{ $auction->total_bids }}</h4>
                                <p class="text-gray-400 small">Bids</p>
                            </div>
                            <div>
                                <h4>{{ $auction->view_count }}</h4>
                                <p class="text-gray-400 small">Views</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($auction->winner)
                <div class="card bg-gray-800 border-gray-700">
                    <div class="card-header border-gray-700">
                        <h5 class="mb-0">Winner</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>{{ $auction->winner->name }}</strong></p>
                        <p class="text-gray-400">{{ $auction->winner->email }}</p>
                        <p><strong>Winning Bid:</strong> ${{ number_format($auction->current_price, 2) }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

