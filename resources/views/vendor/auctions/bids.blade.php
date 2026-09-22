@extends('vendor.layout')

@section('title', 'Auction Bids - ' . config('app.name', 'Smart African Market'))
@section('header', 'Auction Bids')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('vendor.auctions.show', $auction) }}" class="btn btn-outline-light">
            ← Back to Auction
        </a>
    </div>

    <div class="card bg-gray-800 border-gray-700 mb-4">
        <div class="card-body">
            <h5 class="text-white">{{ $auction->title }}</h5>
            <div class="d-flex gap-4 text-gray-400">
                <span>Current Price: <strong class="text-success">${{ number_format($auction->current_price, 2) }}</strong></span>
                <span>Total Bids: <strong>{{ $auction->total_bids }}</strong></span>
                <span>Status: <span class="badge bg-{{ match($auction->status) {
                    'active' => 'success',
                    'ended' => 'warning',
                    default => 'secondary'
                } }}">{{ ucfirst($auction->status) }}</span></span>
            </div>
        </div>
    </div>

    <div class="card bg-gray-800 border-gray-700">
        <div class="card-header border-gray-700">
            <h5 class="mb-0">Bid History ({{ $bids->count() }} bids)</h5>
        </div>
        <div class="card-body">
            @if($bids->count() > 0)
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Bidder</th>
                                <th>Email</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bids as $bid)
                                <tr>
                                    <td>{{ $bid->user->name }}</td>
                                    <td class="text-gray-400">{{ $bid->user->email }}</td>
                                    <td><strong class="text-success">${{ number_format($bid->amount, 2) }}</strong></td>
                                    <td>{{ $bid->is_auto_bid ? 'Auto-bid' : 'Manual' }}</td>
                                    <td>{{ $bid->created_at->format('M j, Y H:i:s') }}</td>
                                    <td>
                                        @if($bid->is_winning)
                                            <span class="badge bg-success">Winning</span>
                                        @else
                                            <span class="badge bg-secondary">Outbid</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-gray-400 py-4">No bids yet</p>
            @endif
        </div>
    </div>
</div>
@endsection

