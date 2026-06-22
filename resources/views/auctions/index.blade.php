@extends('layouts.shop')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1>Auctions</h1>
            <p class="text-muted">Discover unique items through our bidding system</p>
        </div>
        <div class="col-md-6">
            <form action="{{ route('auctions.search') }}" method="GET" class="d-flex">
                <input type="text" name="q" class="form-control me-2" placeholder="Search auctions...">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="{{ route('auctions.index') }}" class="btn btn-outline-primary active">All Active</a>
                <a href="{{ route('auctions.featured') }}" class="btn btn-outline-primary">Featured</a>
                <a href="{{ route('auctions.ending-soon') }}" class="btn btn-outline-primary">Ending Soon</a>
            </div>
        </div>
    </div>

    @if($auctions->isEmpty())
        <div class="alert alert-info">
            No active auctions at the moment. Check back later!
        </div>
    @else
        <div class="row">
            @foreach($auctions as $auction)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="position-absolute top-0 end-0 m-2">
                            @if($auction->is_featured)
                                <span class="badge bg-warning">Featured</span>
                            @endif
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('auctions.show', $auction) }}">{{ $auction->title }}</a>
                            </h5>
                            <p class="card-text text-muted">{{ Str::limit($auction->description, 100) }}</p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Current Bid</span>
                                <span class="h5 text-primary">${{ number_format($auction->current_price, 2) }}</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Bids</span>
                                <span>{{ $auction->total_bids }}</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Ends In</span>
                                <span class="countdown" data-ends-at="{{ $auction->ends_at }}">
                                    {{ $auction->ends_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <a href="{{ route('auctions.show', $auction) }}" class="btn btn-primary w-100">View Auction</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $auctions->links() }}
        </div>
    @endif
</div>
@endsection

