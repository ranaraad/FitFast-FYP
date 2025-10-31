@extends('cms.layouts.store-admin-app')

@section('page-title', 'Store Admin Dashboard')
@section('page-subtitle', 'Manage your stores and inventory')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Store Admin Dashboard</h1>
    <div class="d-flex align-items-center">
        <span class="badge badge-primary badge-pill py-2 px-3 mr-3">
            <i class="fas fa-store mr-1"></i> {{ $topbarStats['total_stores'] }} Stores
        </span>
        <a href="{{ route('store-admin.stores.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-store fa-sm text-white-50"></i> View All Stores
        </a>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Quick Stats Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Items
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $topbarStats['total_items'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
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
                            Pending Orders
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $topbarStats['pending_orders'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Low Stock Items
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $topbarStats['low_stock_items'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                            Today's Revenue
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($topbarStats['today_revenue'], 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stores Grid -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">My Stores</h6>
                <span class="badge badge-info">
                    <i class="fas fa-store"></i> {{ $stores->count() }} Stores Managed
                </span>
            </div>
            <div class="card-body">
                @if($stores->count() > 0)
                    <div class="row">
                        @foreach($stores as $store)
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card store-card border-left-{{ $store->status === 'active' ? 'success' : 'secondary' }} shadow h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="flex-grow-1">
                                            <h5 class="card-title text-gray-800 mb-1">
                                                <i class="fas fa-store text-primary mr-2"></i>
                                                {{ $store->name }}
                                            </h5>
                                            <span class="badge badge-{{ $store->status === 'active' ? 'success' : 'secondary' }} badge-pill">
                                                {{ ucfirst($store->status) }}
                                            </span>
                                        </div>
                                        <div class="dropdown no-arrow">
                                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink-{{ $store->id }}"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                                aria-labelledby="dropdownMenuLink-{{ $store->id }}">
                                                <a class="dropdown-item" href="{{ route('store-admin.stores.show', $store) }}">
                                                    <i class="fas fa-eye fa-sm mr-2"></i> View Details
                                                </a>
                                                <a class="dropdown-item" href="{{ route('store-admin.items.index', ['store_id' => $store->id]) }}">
                                                    <i class="fas fa-box fa-sm mr-2"></i> Manage Items
                                                </a>
                                                <a class="dropdown-item" href="{{ route('store-admin.orders.index', ['store_id' => $store->id]) }}">
                                                    <i class="fas fa-shopping-cart fa-sm mr-2"></i> View Orders
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    @if($store->description)
                                    <p class="card-text text-muted small mb-3">
                                        {{ Str::limit($store->description, 100) }}
                                    </p>
                                    @endif

                                    <!-- Store Stats -->
                                    <div class="store-stats mb-3">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="stat-item">
                                                    <div class="stat-number text-primary font-weight-bold">{{ $store->items_count }}</div>
                                                    <div class="stat-label text-xs text-muted">Items</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-item">
                                                    <div class="stat-number text-warning font-weight-bold">{{ $store->pending_orders_count }}</div>
                                                    <div class="stat-label text-xs text-muted">Pending</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-item">
                                                    <div class="stat-number text-success font-weight-bold">{{ $store->completed_orders_count }}</div>
                                                    <div class="stat-label text-xs text-muted">Completed</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stock Alerts -->
                                    @if($store->low_stock_items_count > 0 || $store->out_of_stock_items_count > 0)
                                    <div class="stock-alerts mb-3">
                                        @if($store->out_of_stock_items_count > 0)
                                        <div class="alert alert-danger py-1 mb-1" role="alert">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            <strong>{{ $store->out_of_stock_items_count }}</strong> items out of stock
                                        </div>
                                        @endif
                                        @if($store->low_stock_items_count > 0)
                                        <div class="alert alert-warning py-1 mb-1" role="alert">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            <strong>{{ $store->low_stock_items_count }}</strong> items low on stock
                                        </div>
                                        @endif
                                    </div>
                                    @else
                                    <div class="alert alert-success py-1 mb-3" role="alert">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        All items are well stocked
                                    </div>
                                    @endif

                                    <!-- Quick Actions -->
                                    <div class="quick-actions">
                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ route('store-admin.items.index', ['store_id' => $store->id]) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-box"></i>
                                            </a>
                                            <a href="{{ route('store-admin.orders.index', ['store_id' => $store->id]) }}"
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-shopping-cart"></i>
                                            </a>
                                            <a href="{{ route('store-admin.deliveries.index', ['store_id' => $store->id]) }}"
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-truck"></i>
                                            </a>
                                            <a href="{{ route('store-admin.stores.show', $store) }}"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-store fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Stores Assigned</h5>
                        <p class="text-muted mb-4">You don't have any stores assigned to manage yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
@if($stores->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Store Activity</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="recentActivityTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Store</th>
                                <th>Activity</th>
                                <th>Items</th>
                                <th>Orders</th>
                                <th>Last Updated</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stores as $store)
                            <tr>
                                <td>
                                    <strong>{{ $store->name }}</strong>
                                    @if($store->low_stock_items_count > 0)
                                    <span class="badge badge-warning ml-2">Low Stock</span>
                                    @endif
                                </td>
                                <td>
                                    @if($store->pending_orders_count > 0)
                                        <span class="text-warning">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ $store->pending_orders_count }} pending orders
                                        </span>
                                    @else
                                        <span class="text-success">
                                            <i class="fas fa-check mr-1"></i>
                                            No pending orders
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $store->items_count }} total</td>
                                <td>{{ $store->pending_orders_count + $store->completed_orders_count }} total</td>
                                <td>{{ $store->updated_at->diffForHumans() }}</td>
                                <td>
                                    @if($store->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.store-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.store-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.stat-item {
    padding: 5px 0;
}

.stat-number {
    font-size: 1.25rem;
    line-height: 1;
}

.stat-label {
    font-size: 0.75rem;
    margin-top: 2px;
}

.stock-alerts .alert {
    margin-bottom: 0.25rem;
    font-size: 0.8rem;
}

.quick-actions .btn-group .btn {
    border-radius: 0;
    padding: 0.25rem 0.5rem;
}

.quick-actions .btn-group .btn:first-child {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.quick-actions .btn-group .btn:last-child {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}

.border-left-success {
    border-left: 4px solid #1cc88a !important;
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

.border-left-warning {
    border-left: 4px solid #f6c23e !important;
}

.border-left-danger {
    border-left: 4px solid #e74a3b !important;
}

.border-left-secondary {
    border-left: 4px solid #858796 !important;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable for recent activity
        $('#recentActivityTable').DataTable({
            "pageLength": 10,
            "order": [[4, 'desc']], // Sort by last updated
            "language": {
                "emptyTable": "No recent activity found"
            }
        });

        // Store card hover effects
        $('.store-card').hover(
            function() {
                $(this).addClass('shadow-lg');
            },
            function() {
                $(this).removeClass('shadow-lg');
            }
        );
    });
</script>
@endpush
