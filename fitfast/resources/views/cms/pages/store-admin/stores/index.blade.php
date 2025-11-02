@extends('cms.layouts.store-admin-app')

@section('page-title', 'My Stores')
@section('page-subtitle', 'Manage all your stores in one place')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">My Stores</h1>

    <!-- Summary Stats -->
    <div class="d-flex align-items-center">
        <div class="mr-3 text-right">
            <small class="text-muted d-block">Total Stores</small>
            <span class="h5 mb-0 font-weight-bold text-primary">{{ $summary['total_stores'] }}</span>
        </div>
        <div class="mr-3 text-right">
            <small class="text-muted d-block">Total Items</small>
            <span class="h5 mb-0 font-weight-bold text-info">{{ $summary['total_items'] }}</span>
        </div>
        <div class="mr-3 text-right">
            <small class="text-muted d-block">Pending Orders</small>
            <span class="h5 mb-0 font-weight-bold text-warning">{{ $summary['total_pending_orders'] }}</span>
        </div>
    </div>
</div>

<!-- Stock Alerts Banner -->
@if($summary['total_critical_stock'] > 0 || $summary['total_low_stock'] > 0)
<div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
        <div class="flex-grow-1">
            <strong>Stock Alerts Across Your Stores:</strong>
            @if($summary['total_critical_stock'] > 0)
                <span class="text-danger mx-2">{{ $summary['total_critical_stock'] }} critical items</span>
            @endif
            @if($summary['total_low_stock'] > 0)
                <span class="text-warning mx-2">{{ $summary['total_low_stock'] }} low stock items</span>
            @endif
            @if($summary['total_out_of_stock'] > 0)
                <span class="text-secondary mx-2">{{ $summary['total_out_of_stock'] }} out of stock</span>
            @endif
        </div>
    </div>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">All Stores Overview</h6>
                <div>
                    @if($summary['total_critical_stock'] > 0)
                    <span class="badge badge-danger mr-2">
                        <i class="fas fa-exclamation-circle"></i> {{ $summary['total_critical_stock'] }} Critical
                    </span>
                    @endif
                    @if($summary['total_low_stock'] > 0)
                    <span class="badge badge-warning mr-2">
                        <i class="fas fa-exclamation-triangle"></i> {{ $summary['total_low_stock'] }} Low Stock
                    </span>
                    @endif
                    <span class="badge badge-info">
                        <i class="fas fa-store"></i> {{ $stores->count() }} Stores
                    </span>
                </div>
            </div>
            <div class="card-body">
                @if($stores->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="storesTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Store</th>
                                    <th>Status</th>
                                    <th>Inventory Health</th>
                                    <th>Orders</th>
                                    <th>Stock Alerts</th>
                                    <th>Revenue</th>
                                    <th>Last Activity</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stores as $store)
                                @php
                                    $hasCritical = $store->critical_stock_items_count > 0;
                                    $hasLowStock = $store->low_stock_items_count > 0;
                                    $hasOutOfStock = $store->out_of_stock_items_count > 0;
                                    $totalOrders = $store->pending_orders_count + $store->processing_orders_count + $store->completed_orders_count;
                                    $storeRevenue = $store->orders()->where('status', 'completed')->sum('total_amount');
                                    $lastOrder = $store->orders->first();
                                @endphp
                                <tr class="{{ $hasCritical ? 'table-warning' : ($hasLowStock ? 'table-light-warning' : '') }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-store fa-lg text-primary mr-3"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="text-gray-800">{{ $store->name }}</strong>
                                                @if($store->description)
                                                <br><small class="text-muted">{{ Str::limit($store->description, 60) }}</small>
                                                @endif
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt fa-xs mr-1"></i>
                                                    {{ Str::limit($store->address, 40) ?: 'No address' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($store->status === 'active')
                                            <span class="badge badge-success badge-pill py-1 px-3">
                                                <i class="fas fa-check-circle mr-1"></i> Active
                                            </span>
                                        @else
                                            <span class="badge badge-secondary badge-pill py-1 px-3">
                                                <i class="fas fa-pause-circle mr-1"></i> Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge badge-info mb-1">{{ $store->items_count }} items</span>
                                            <div class="progress mb-1" style="height: 6px;">
                                                @php
                                                    $healthyItems = $store->items_count - $store->low_stock_items_count - $store->out_of_stock_items_count;
                                                    $healthyPercent = $store->items_count > 0 ? ($healthyItems / $store->items_count * 100) : 0;
                                                @endphp
                                                <div class="progress-bar bg-success" role="progressbar"
                                                     style="width: {{ $healthyPercent }}%"
                                                     title="{{ $healthyItems }} healthy items">
                                                </div>
                                            </div>
                                            <small class="text-muted text-center">
                                                {{ number_format($healthyPercent, 1) }}% healthy
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column text-center">
                                            <div class="mb-1">
                                                <span class="font-weight-bold text-primary">{{ $totalOrders }}</span>
                                                <small class="text-muted d-block">total</small>
                                            </div>
                                            <div class="d-flex justify-content-around">
                                                <div>
                                                    <small class="text-warning font-weight-bold">{{ $store->pending_orders_count }}</small>
                                                    <small class="text-muted d-block">pending</small>
                                                </div>
                                                <div>
                                                    <small class="text-success font-weight-bold">{{ $store->completed_orders_count }}</small>
                                                    <small class="text-muted d-block">completed</small>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="stock-alerts">
                                            @if($hasCritical)
                                            <div class="alert alert-danger py-1 mb-1 text-center" role="alert">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <strong>{{ $store->critical_stock_items_count }}</strong> critical
                                            </div>
                                            @endif
                                            @if($hasLowStock)
                                            <div class="alert alert-warning py-1 mb-1 text-center" role="alert">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <strong>{{ $store->low_stock_items_count }}</strong> low
                                            </div>
                                            @endif
                                            @if($hasOutOfStock)
                                            <div class="alert alert-secondary py-1 mb-1 text-center" role="alert">
                                                <i class="fas fa-times-circle"></i>
                                                <strong>{{ $store->out_of_stock_items_count }}</strong> out
                                            </div>
                                            @endif
                                            @if(!$hasCritical && !$hasLowStock && !$hasOutOfStock)
                                            <div class="alert alert-success py-1 text-center" role="alert">
                                                <i class="fas fa-check-circle"></i> All Good
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <strong class="text-success">${{ number_format($storeRevenue, 2) }}</strong>
                                            <br>
                                            <small class="text-muted">total revenue</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($lastOrder)
                                        <div class="text-center">
                                            <small class="text-muted d-block">{{ $lastOrder->created_at->format('M d') }}</small>
                                            <small class="text-muted">{{ $lastOrder->created_at->diffForHumans() }}</small>
                                        </div>
                                        @else
                                        <small class="text-muted">No orders yet</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <a href="{{ route('store-admin.stores.show', $store) }}"
                                               class="btn btn-info mb-1" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('store-admin.items.index', ['store_id' => $store->id]) }}"
                                               class="btn btn-primary mb-1" title="Manage Items">
                                                <i class="fas fa-box"></i>
                                            </a>
                                            <a href="{{ route('store-admin.orders.index', ['store_id' => $store->id]) }}"
                                               class="btn btn-success" title="View Orders">
                                                <i class="fas fa-shopping-cart"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-store fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Stores Assigned</h5>
                        <p class="text-muted mb-4">You don't have any stores assigned to manage yet.</p>
                        <a href="{{ route('cms.stores.index') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i> Request Store Access
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Stores
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['total_stores'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-store fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Healthy Items
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $summary['total_items'] - $summary['total_low_stock'] - $summary['total_out_of_stock'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Needs Attention
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $summary['total_low_stock'] + $summary['total_critical_stock'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Pending Orders
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['total_pending_orders'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-light-warning {
    background-color: #fffbf0 !important;
}

.stock-alerts .alert {
    margin-bottom: 0.25rem;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.progress {
    background-color: #f8f9fa;
}

.btn-group-vertical .btn {
    border-radius: 0.25rem !important;
    margin-bottom: 0.25rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.02);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.badge-pill {
    border-radius: 50rem;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#storesTable').DataTable({
            "pageLength": 25,
            "order": [[0, 'asc']],
            "columnDefs": [
                { "orderable": false, "targets": [4, 7] } // Disable sorting for alerts and actions
            ],
            "language": {
                "emptyTable": "No stores found"
            }
        });

        // Add hover effects to table rows
        $('#storesTable tbody tr').hover(
            function() {
                $(this).addClass('shadow-sm');
            },
            function() {
                $(this).removeClass('shadow-sm');
            }
        );
    });
</script>
@endpush
