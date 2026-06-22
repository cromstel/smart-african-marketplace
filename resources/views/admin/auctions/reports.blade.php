@extends('admin.layout')

@section('title', 'Auction Reports - ' . config('app.name', 'Smart African Marketplace'))

@section('header', 'Auction Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.auctions.index') }}" class="btn btn-outline-secondary">
            ← Back to Auctions
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.auctions.reports') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control" 
                           value="{{ request('from_date') }}">
                </div>
                <div class="col-md-4">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control" 
                           value="{{ request('to_date') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Auctions</h5>
                    <h2>{{ number_format($stats['total_auctions']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Active Auctions</h5>
                    <h2>{{ number_format($stats['active_auctions']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Ended Auctions</h5>
                    <h2>{{ number_format($stats['ended_auctions']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Settled Auctions</h5>
                    <h2>{{ number_format($stats['settled_auctions']) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Additional Statistics</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Total Bids:</th>
                            <td>{{ number_format($stats['total_bids']) }}</td>
                        </tr>
                        <tr>
                            <th>Featured Auctions:</th>
                            <td>{{ number_format($stats['featured_auctions']) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Auction Status Distribution</h5>
        </div>
        <div class="card-body">
            @php
                $total = $stats['total_auctions'];
                $activePercent = $total > 0 ? ($stats['active_auctions'] / $total) * 100 : 0;
                $endedPercent = $total > 0 ? ($stats['ended_auctions'] / $total) * 100 : 0;
                $settledPercent = $total > 0 ? ($stats['settled_auctions'] / $total) * 100 : 0;
            @endphp
            
            <div class="progress mb-3" style="height: 30px;">
                @if($stats['active_auctions'] > 0)
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $activePercent }}%">
                        Active: {{ $stats['active_auctions'] }}
                    </div>
                @endif
                @if($stats['ended_auctions'] > 0)
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $endedPercent }}%">
                        Ended: {{ $stats['ended_auctions'] }}
                    </div>
                @endif
                @if($stats['settled_auctions'] > 0)
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $settledPercent }}%">
                        Settled: {{ $stats['settled_auctions'] }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

