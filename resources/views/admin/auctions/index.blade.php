@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Auction Management</h1>
        <a href="{{ route('admin.auctions.reports') }}" class="btn btn-outline-primary">
            View Reports
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.auctions.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search auctions..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>Ended</option>
                        <option value="settled" {{ request('status') === 'settled' ? 'selected' : '' }}>Settled</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Vendor</th>
                            <th>Type</th>
                            <th>Current Price</th>
                            <th>Bids</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Ends At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auctions as $auction)
                            <tr>
                                <td>{{ $auction->id }}</td>
                                <td>
                                    <a href="{{ route('admin.auctions.show', $auction) }}">
                                        {{ Str::limit($auction->title, 30) }}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.vendors.show', $auction->vendor) }}">
                                        {{ $auction->vendor->name }}
                                    </a>
                                </td>
                                <td>{{ ucfirst($auction->type) }}</td>
                                <td>${{ number_format($auction->current_price, 2) }}</td>
                                <td>{{ $auction->total_bids }}</td>
                                <td>
                                    <span class="badge bg-{{ $auction->status === 'active' ? 'success' : ($auction->status === 'ended' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($auction->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($auction->is_featured)
                                        <span class="badge bg-warning">Yes</span>
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>
                                <td>{{ $auction->ends_at?->format('M j, Y H:i') ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.auctions.show', $auction) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                        <form action="{{ route('admin.auctions.feature', $auction) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-{{ $auction->is_featured ? 'warning' : 'secondary' }}">
                                                {{ $auction->is_featured ? 'Unfeature' : 'Feature' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    No auctions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $auctions->links() }}
        </div>
    </div>
</div>
@endsection

