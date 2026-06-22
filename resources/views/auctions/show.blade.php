@extends('layouts.shop')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h1>{{ $auction->title }}</h1>
                    <p class="text-muted">
                        by <a href="{{ route('vendor.store', $auction->vendor) }}">{{ $auction->vendor->name }}</a>
                    </p>
                    
                    @if($auction->is_featured)
                        <span class="badge bg-warning mb-3">Featured</span>
                    @endif

                    <div class="mb-4">
                        <span class="badge bg-{{ $auction->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($auction->status) }}
                        </span>
                        <span class="badge bg-info">{{ ucfirst($auction->type) }}</span>
                    </div>

                    <div class="mb-4">
                        <h3>Description</h3>
                        <p>{{ $auction->description }}</p>
                    </div>

                    <div class="mb-4">
                        <h3>Bid History</h3>
                        @if($auction->bids->isEmpty())
                            <p class="text-muted">No bids yet. Be the first to bid!</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Bidder</th>
                                            <th>Amount</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($auction->bids->take(10) as $bid)
                                            <tr>
                                                <td>{{ $bid->is_auto_bid ? 'Auto-bid' : ($bid->user->name ?? 'Anonymous') }}</td>
                                                <td>${{ number_format($bid->amount, 2) }}</td>
                                                <td>{{ $bid->created_at->diffForHumans() }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($auction->total_bids > 10)
                                <a href="{{ route('auctions.bids', $auction) }}" class="btn btn-link">View all {{ $auction->total_bids }} bids</a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4 sticky-top" style="top: 20px;">
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted">Current Bid</span>
                        <h2 class="text-primary">${{ number_format($auction->current_price, 2) }}</h2>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted">Total Bids</span>
                        <h4>{{ $auction->total_bids }}</h4>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted">Minimum Next Bid</span>
                        <h4>${{ number_format($minimumBid, 2) }}</h4>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted">Auction Ends</span>
                        <div class="countdown" data-ends-at="{{ $auction->ends_at }}">
                            <h4>{{ $auction->ends_at->diffForHumans() }}</h4>
                        </div>
                    </div>

                    @if($auction->buy_now_price)
                        <div class="mb-3">
                            <span class="text-muted">Buy Now Price</span>
                            <h4>${{ number_format($auction->buy_now_price, 2) }}</h4>
                        </div>
                    @endif

                    @auth
                        @if($auction->isActive() && $auction->vendor->user_id !== auth()->id())
                            <div class="mb-3">
                                @if($isWinning)
                                    <div class="alert alert-success">
                                        You are the highest bidder!
                                    </div>
                                @elseif($userBid)
                                    <div class="alert alert-warning">
                                        You've been outbid! Current: ${{ number_format($userBid->amount, 2) }}
                                    </div>
                                @endif
                                
                                <form action="{{ route('api.auctions.bid', $auction) }}" method="POST" id="bid-form">
                                    @csrf
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="amount" class="form-control" 
                                               value="{{ $minimumBid }}" 
                                               min="{{ $minimumBid }}" 
                                               step="0.01">
                                        <button type="submit" class="btn btn-primary">Place Bid</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <a href="{{ route('login') }}">Login</a> to place a bid.
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Update countdown every second
    function updateCountdowns() {
        document.querySelectorAll('.countdown').forEach(el => {
            const endsAt = new Date(el.dataset.endsAt).getTime();
            const now = Date.now();
            const diff = endsAt - now;

            if (diff <= 0) {
                el.textContent = 'Ended';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            let text = '';
            if (days > 0) text += `${days}d `;
            if (hours > 0) text += `${hours}h `;
            text += `${minutes}m ${seconds}s`;

            el.textContent = text;
        });
    }

    updateCountdowns();
    setInterval(updateCountdowns, 1000);
</script>
@endpush

