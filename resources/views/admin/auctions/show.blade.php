@extends('admin.layout')

@section('title', 'Auction Details - ' . config('app.name', 'Smart African Marketplace'))

@section('header', 'Auction Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.auctions.index') }}" class="btn btn-outline-secondary">
            ← Back to Auctions
        </a>
        <div class="btn-group">
            @if($auction->status === 'active')
                <form action="{{ route('admin.auctions.feature', $auction) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-{{ $auction->is_featured ? 'warning' : 'outline-warning' }}">
                        {{ $auction->is_featured ? 'Unfeature' : 'Feature' }}
                    </button>
                </form>
            @endif
            @if($auction->status === 'ended' && !$auction->winner)
                <form action="{{ route('admin.auctions.settle', $auction) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        Settle Auction
                    </button>
                </form>
            @endif
            @if(in_array($auction->status, ['draft', 'scheduled', 'active']))
                <form action="{{ route('admin.auctions.destroy', $auction) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this auction?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Auction Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
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
                        <tr>
                            <th>Featured:</th>
                            <td>{{ $auction->is_featured ? 'Yes' : 'No' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Pricing</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Starting Price:</th>
                            <td>${{ number_format($auction->starting_price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Current Price:</th>
                            <td><strong>${{ number_format($auction->current_price, 2) }}</strong></td>
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

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Timing</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Starts At:</th>
                            <td>{{ $auction->starts_at?->format('M j, Y H:i') ?? 'Not set' }}</td>
                        </tr>
                        <tr>
                            <th>Ends At:</th>
                            <td>{{ $auction->ends_at?->format('M j, Y H:i') ?? 'Not set' }}</td>
                        </tr>
                        <tr>
                            <th>Original End Time:</th>
                            <td>{{ $auction->original_ends_at?->format('M j, Y H:i') ?? 'Not extended' }}</td>
                        </tr>
                        <tr>
                            <th>Extension Minutes:</th>
                            <td>{{ $auction->extension_minutes }} minutes</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Total Bids:</th>
                            <td>{{ $auction->total_bids }}</td>
                        </tr>
                        <tr>
                            <th>View Count:</th>
                            <td>{{ $auction->view_count }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Vendor</h5>
                </div>
                <div class="card-body">
                    @if($auction->vendor)
                        <p><strong>{{ $auction->vendor->name }}</strong></p>
                        <a href="{{ route('admin.vendors.show', $auction->vendor) }}" class="btn btn-sm btn-outline-primary">
                            View Vendor
                        </a>
                    @else
                        <p class="text-muted">No vendor</p>
                    @endif
                </div>
            </div>

            @if($auction->winner)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Winner</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>{{ $auction->winner->name }}</strong></p>
                        <p class="text-muted">{{ $auction->winner->email }}</p>
                        <p><strong>Winning Bid:</strong> ${{ number_format($auction->current_price, 2) }}</p>
                    </div>
                </div>
            @endif

            @if($auction->product)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Linked Product</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>{{ $auction->product->name }}</strong></p>
                        <a href="{{ route('admin.products.show', $auction->product) }}" class="btn btn-sm btn-outline-primary">
                            View Product
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($auction->bids->count() > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Bid History ({{ $auction->total_bids }} bids)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auction->bids->take(10) as $bid)
                            <tr>
                                <td>
                                    {{ $bid->user->name }}
                                    @if($bid->is_winning)
                                        <span class="badge bg-success ms-1">Winning</span>
                                    @endif
                                </td>
                                <td>${{ number_format($bid->amount, 2) }}</td>
                                <td>{{ $bid->is_auto_bid ? 'Auto-bid' : 'Manual' }}</td>
                                <td>{{ $bid->created_at->format('M j, Y H:i:s') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($auction->bids->count() > 10)
                <p class="text-muted text-center">Showing last 10 bids</p>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

