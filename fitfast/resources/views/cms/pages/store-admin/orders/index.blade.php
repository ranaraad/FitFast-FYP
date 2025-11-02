@extends('cms.layouts.store-admin-app')

@section('page-title', 'Orders Management')
@section('page-subtitle', 'Manage orders for your stores')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Orders Management</h1>

    <!-- Quick Stats -->
    <div class="d-flex align-items-center">
        <div class="mr-3 text-right">
            <small class="text-muted d-block">Total Orders</small>
            <span class="h5 mb-0 font-weight-bold text-primary">{{ $summary['total_orders'] }}</span>
        </div>
        <div class="mr-3 text-right">
            <small class="text-muted d-block">Pending</small>
            <span class="h5 mb-0 font-weight-bold text-warning">{{ $summary['pending_orders'] }}</span>
        </div>
        <div class="mr-3 text-right">
            <small class="text-muted d-block">Revenue</small>
            <span class="h5 mb-0 font-weight-bold text-success">${{ number_format($summary['total_revenue'], 2) }}</span>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('store-admin.orders.index') }}" method="GET" id="filterForm">
            <div class="row">
                <!-- Store Filter -->
                @if($stores->count() > 1)
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="store_id">Store</label>
                        <select class="form-control" id="store_id" name="store_id" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Stores</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <!-- Status Filter -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $key => $status)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Search -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="Search orders...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clear Filters -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        @if(request()->anyFilled(['store_id', 'status', 'search']))
                        <div>
                            <a href="{{ route('store-admin.orders.index') }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">All Orders</h6>
                <div class="d-flex align-items-center">
                    @if($summary['pending_orders'] > 0)
                    <span class="badge badge-warning mr-2">
                        <i class="fas fa-clock"></i> {{ $summary['pending_orders'] }} Pending
                    </span>
                    @endif
                    @if($summary['processing_orders'] > 0)
                    <span class="badge badge-info mr-2">
                        <i class="fas fa-cog"></i> {{ $summary['processing_orders'] }} Processing
                    </span>
                    @endif
                    <span class="badge badge-success mr-3">
                        <i class="fas fa-shopping-cart"></i> {{ $summary['total_orders'] }} Total
                    </span>

                    <!-- Export Dropdown -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-file-export fa-sm text-white-50"></i> Export
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ route('store-admin.orders.export') . '?' . http_build_query(request()->query()) }}">
                                <i class="fas fa-file-csv text-primary mr-2"></i> Export Current View (CSV)
                            </a>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exportModal">
                                <i class="fas fa-cogs text-info mr-2"></i> Advanced Export
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if($orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="ordersTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Store</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr class="{{ $order->status === 'pending' ? 'table-warning' : ($order->status === 'processing' ? 'table-info' : '') }}">
                                    <td>
                                        <strong>#{{ $order->id }}</strong>
                                        @if($order->created_at->isToday())
                                        <span class="badge badge-success badge-pill ml-1">New</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-user-circle text-primary mr-2"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="text-gray-800">{{ $order->user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $order->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $order->store->name }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge badge-info mb-1">{{ $order->orderItems->count() }} items</span>
                                            @foreach($order->orderItems->take(2) as $orderItem)
                                            <small class="text-muted">
                                                • {{ Str::limit($orderItem->item->name, 20) }}
                                                <span class="text-primary">(x{{ $orderItem->quantity }})</span>
                                            </small>
                                            @endforeach
                                            @if($order->orderItems->count() > 2)
                                            <small class="text-muted">+{{ $order->orderItems->count() - 2 }} more</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-success">${{ number_format($order->total_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        {!! $order->status_badge !!}
                                        <br>
                                        <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <strong>{{ $order->created_at->format('M d') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $order->created_at->format('Y') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <a href="{{ route('store-admin.orders.show', $order) }}"
                                               class="btn btn-info mb-1" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('store-admin.orders.edit', $order) }}"
                                               class="btn btn-primary mb-1" title="Edit Order">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($order->canBeCancelled())
                                            <button type="button" class="btn btn-danger"
                                                    onclick="confirmDelete({{ $order->id }})" title="Delete Order">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-secondary" disabled title="Cannot delete processed orders">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                        <form id="delete-form-{{ $order->id }}" action="{{ route('store-admin.orders.destroy', $order) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} entries
                        </div>
                        {{ $orders->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Orders Found</h5>
                        <p class="text-muted mb-4">
                            @if(request()->anyFilled(['store_id', 'status', 'search']))
                                No orders match your current filters.
                            @else
                                You don't have any orders yet for your stores.
                            @endif
                        </p>
                        @if(request()->anyFilled(['store_id', 'status', 'search']))
                        <a href="{{ route('store-admin.orders.index') }}" class="btn btn-primary">
                            <i class="fas fa-times mr-1"></i> Clear Filters
                        </a>
                        @endif
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
                            Total Orders
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['total_orders'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['pending_orders'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                            Processing
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary['processing_orders'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cog fa-2x text-gray-300"></i>
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
                            Total Revenue
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">${{ number_format($summary['total_revenue'], 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Advanced Order Export</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('store-admin.orders.export-advanced') }}" method="GET">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="export_type">Export Type</label>
                        <select class="form-control" id="export_type" name="export_type" required>
                            <option value="summary">Summary (One row per order)</option>
                            <option value="detailed">Detailed (One row per order item)</option>
                        </select>
                        <small class="form-text text-muted">
                            Summary export shows one row per order. Detailed export shows one row per order item for granular analysis.
                        </small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                       max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note:</strong> The export will include all current filters and search criteria.
                    </div>
                    <!-- Include current filters -->
                    @if(request('store_id'))
                        <input type="hidden" name="store_id" value="{{ request('store_id') }}">
                    @endif
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download mr-1"></i> Generate Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-warning {
    background-color: #fffbf0 !important;
}

.table-info {
    background-color: #f0f9ff !important;
}

.btn-group-vertical .btn {
    border-radius: 0.25rem !important;
    margin-bottom: 0.25rem;
}

.badge-pill {
    border-radius: 50rem;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.02);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.export-dropdown .dropdown-menu {
    min-width: 200px;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#ordersTable').DataTable({
            "paging": false, // We're using Laravel pagination
            "searching": false, // We have our own search
            "ordering": true,
            "order": [[0, 'desc']],
            "columnDefs": [
                { "orderable": false, "targets": [3, 7] } // Disable sorting for items and actions
            ],
            "info": false,
            "filter": false
        });

        // Add hover effects to table rows
        $('#ordersTable tbody tr').hover(
            function() {
                $(this).addClass('shadow-sm');
            },
            function() {
                $(this).removeClass('shadow-sm');
            }
        );

        // Set end date to today by default
        $('#end_date').val('{{ date('Y-m-d') }}');

        // Set start date to 30 days ago by default
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        $('#start_date').val(thirtyDaysAgo.toISOString().split('T')[0]);

        // Validate date range
        $('#start_date, #end_date').change(function() {
            const startDate = new Date($('#start_date').val());
            const endDate = new Date($('#end_date').val());

            if (startDate && endDate && startDate > endDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date Range',
                    text: 'Start date cannot be after end date.',
                    timer: 3000
                });
                $('#start_date').val('');
            }
        });
    });

    function confirmDelete(orderId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the order and restore item stock!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + orderId).submit();
            }
        });
    }
</script>
@endpush
