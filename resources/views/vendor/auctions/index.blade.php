@extends('vendor.layout')

@section('title', 'My Auctions - ' . config('app.name', 'Smart African Marketplace'))
@section('header', 'My Auctions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <p class="text-gray-400">Manage your auction listings</p>
        <a href="{{ route('vendor.auctions.create') }}" class="btn btn-primary">
            + Create Auction
        </a>
    </div>

    <div class="card bg-gray-800 border-gray-700">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Current Price</th>
                            <th>Bids</th>
                            <th>Status</th>
                            <th>Ends At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auctions as $auction)
                            <tr>
                                <td>
                                    <a href="{{ route('vendor.auctions.show', $auction) }}" class="text-white text-decoration-none">
                                        {{ Str::limit($auction->title, 30) }}
                                    </a>
                                </td>
                                <td><span class="badge bg-info">{{ ucfirst($auction->type) }}</span></td>
                                <td>${{ number_format($auction->current_price, 2) }}</td>
                                <td>{{ $auction->total_bids }}</td>
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
                                <td>{{ $auction->ends_at?->format('M j, Y H:i') ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('vendor.auctions.show', $auction) }}" class="btn btn-sm btn-outline-light">
                                            View
                                        </a>
                                        @if(in_array($auction->status, ['draft', 'scheduled']))
                                            <a href="{{ route('vendor.auctions.edit', $auction) }}" class="btn btn-sm btn-outline-primary">
                                                Edit
                                            </a>
                                        @endif
                                        @if($auction->status === 'draft')
                                            <form action="{{ route('vendor.auctions.publish', $auction) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">Publish</button>
                                            </form>
                                        @endif
                                        @if($auction->status === 'active')
                                            <a href="{{ route('vendor.auctions.bids', $auction) }}" class="btn btn-sm btn-outline-info">
                                                Bids
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-gray-400">
                                    No auctions found. Create your first auction!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer border-gray-700">
            {{ $auctions->links() }}
        </div>
    </div>
</div>
@endsection

