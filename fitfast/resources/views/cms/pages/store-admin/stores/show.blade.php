@extends('cms.layouts.store-admin-app')

@section('page-title', $store->name)
@section('page-subtitle', 'Store Details & Analytics')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ $store->name }}</h1>
    <div>
        <a href="{{ route('store-admin.stores.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Stores
        </a>
        <a href="{{ route('store-admin.items.index', ['store_id' => $store->id]) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-box fa-sm text-white-50"></i> Manage Items
        </a>
    </div>
</div>

<!-- Store Status Alert -->
@if($store->status === 'inactive')
<div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    <strong>This store is currently inactive.</strong> Some features may be limited.
</div>
@endif

<!-- Content Row -->
<div class="row">
    <!-- Left Column - Store Info & Quick Stats -->
    <div class="col-xl-4 col-lg-5">
        <!-- Store Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">Store Information</h6>
            </div>
            <div class="card-body">
                <div class="store-info-grid">
                    <div class="info-item">
                        <div class="info-label-container">
                            <i class="fas fa-fingerprint text-primary"></i>
                            <span class="info-label font-weight-bold">Store ID</span>
                        </div>
                        <span class="info-value text-dark font-weight-semibold">#{{ $store->id }}</span>
                    </div>

                    <div class="info-item">
                        <div class="info-label-container">
                            <i class="fas fa-store text-primary"></i>
                            <span class="info-label font-weight-bold">Store Name</span>
                        </div>
                        <span class="info-value text-dark font-weight-semibold">{{ $store->name }}</span>
                    </div>

                    <div class="info-item">
                        <div class="info-label-container">
                            <i class="fas fa-circle text-primary"></i>
                            <span class="info-label font-weight-bold">Status</span>
                        </div>
                        <span class="info-value">
                            @if($store->status === 'active')
                                <span class="badge badge-success badge-pill py-2 px-3">
                                    <i class="fas fa-check-circle mr-1"></i> Active
                                </span>
                            @else
                                <span class="badge badge-secondary badge-pill py-2 px-3">
                                    <i class="fas fa-pause-circle mr-1"></i> Inactive
                                </span>
                            @endif
                        </span>
                    </div>

                    <div class="info-item">
                        <div class="info-label-container">
                            <i class="fas fa-align-left text-primary"></i>
                            <span class="info-label font-weight-bold">Description</span>
                        </div>
                        <span class="info-value text-muted">
                            {{ $store->description ?: 'No description provided' }}
                        </span>
                    </div>

                    <div class="info-item">
                        <div class="info-label-container">
                            <i class="fas fa-map-marker-alt text-primary"></i>
                            <span class="info-label font-weight-bold">Address</span>
                        </div>
                        <span class="info-value text-muted">
                            {{ $store->address ?: 'No address provided' }}
                        </span>
                    </div>

                    <div class="info-item">
                        <div class="info-label-container">
                            <i class="fas fa-phone text-primary"></i>
                            <span class="info-label font-weight-bold">Contact Info</span>
                        </div>
                        <span class="info-value text-muted">
                            {{ $store->contact_info ?: 'No contact information provided' }}
                        </span>
                    </div>

                    <div class="info-item">
                        <div class="info-label-container">
                            <i class="fas fa-calendar-plus text-primary"></i>
                            <span class="info-label font-weight-bold">Created At</span>
                        </div>
                        <span class="info-value text-muted">
                            <div class="d-flex flex-column">
                                <span class="font-weight-semibold">{{ $store->created_at->format('M d, Y') }}</span>
                                <small class="text-muted">{{ $store->created_at->format('H:i A') }}</small>
                            </div>
                        </span>
                    </div>

                    <div class="info-item">
                        <div class="info-label-container">
                            <i class="fas fa-calendar-check text-primary"></i>
                            <span class="info-label font-weight-bold">Updated At</span>
                        </div>
                        <span class="info-value text-muted">
                            <div class="d-flex flex-column">
                                <span class="font-weight-semibold">{{ $store->updated_at->format('M d, Y') }}</span>
                                <small class="text-muted">{{ $store->updated_at->format('H:i A') }}</small>
                            </div>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards in Grid -->
        <div class="row">
            <!-- Total Items -->
            <div class="col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Items
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $store->items->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-boxes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Orders -->
            <div class="col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Orders
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $store->orders->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Statistics -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success text-white">
                <h6 class="m-0 font-weight-bold">Revenue Overview</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h4 class="text-success font-weight-bold mb-1">${{ number_format($storeStats['total_revenue'], 2) }}</h4>
                    <p class="text-muted small mb-3">Total Revenue</p>

                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <h6 class="text-primary font-weight-bold mb-1">${{ number_format($storeStats['avg_order_value'] ?? 0, 2) }}</h6>
                                <small class="text-muted">Avg Order Value</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="text-info font-weight-bold mb-1">{{ $storeStats['total_customers'] }}</h6>
                            <small class="text-muted">Unique Customers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Summary -->
        @php
            $lowStockCount = $store->low_stock_items->count();
            $outOfStockCount = $store->out_of_stock_items->count();
            $healthyStockCount = $store->items->count() - $lowStockCount - $outOfStockCount;
            $totalItems = $store->items->count();
        @endphp

        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">Stock Overview</h6>
            </div>
            <div class="card-body">
                @if($totalItems > 0)
                <!-- Stock Chart -->
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="stockChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-3">
                        <i class="fas fa-circle text-success"></i> Healthy ({{ $healthyStockCount }})
                    </span>
                    <span class="mr-3">
                        <i class="fas fa-circle text-warning"></i> Low ({{ $lowStockCount }})
                    </span>
                    <span class="mr-3">
                        <i class="fas fa-circle text-danger"></i> Out ({{ $outOfStockCount }})
                    </span>
                </div>

                <!-- Stock Progress Bars -->
                <div class="mt-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-sm text-success font-weight-semibold">
                                <i class="fas fa-check-circle mr-1"></i> Healthy Stock
                            </span>
                            <span class="text-sm font-weight-bold">{{ $healthyStockCount }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ $totalItems > 0 ? ($healthyStockCount / $totalItems * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-sm text-warning font-weight-semibold">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Low Stock
                            </span>
                            <span class="text-sm font-weight-bold">{{ $lowStockCount }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar"
                                 style="width: {{ $totalItems > 0 ? ($lowStockCount / $totalItems * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-sm text-danger font-weight-semibold">
                                <i class="fas fa-times-circle mr-1"></i> Out of Stock
                            </span>
                            <span class="text-sm font-weight-bold">{{ $outOfStockCount }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" role="progressbar"
                                 style="width: {{ $totalItems > 0 ? ($outOfStockCount / $totalItems * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted mb-2">No Stock Data Available</h6>
                    <p class="text-muted small mb-3">Add items to this store to see stock analytics</p>
                    <a href="{{ route('store-admin.items.create') }}?store_id={{ $store->id }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus mr-1"></i> Add First Item
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow">
            <div class="card-header py-3 bg-warning text-dark">
                <h6 class="m-0 font-weight-bold">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="{{ route('store-admin.items.create') }}?store_id={{ $store->id }}" class="btn btn-success btn-block py-2">
                        <i class="fas fa-plus mr-2"></i> Add New Item
                    </a>
                    <a href="{{ route('store-admin.items.index') }}?store_id={{ $store->id }}" class="btn btn-info btn-block py-2">
                        <i class="fas fa-list mr-2"></i> Manage All Items
                    </a>
                    <a href="{{ route('store-admin.orders.index') }}?store_id={{ $store->id }}" class="btn btn-primary btn-block py-2">
                        <i class="fas fa-shopping-cart mr-2"></i> View Orders
                    </a>
                    <a href="{{ route('store-admin.deliveries.index') }}?store_id={{ $store->id }}" class="btn btn-secondary btn-block py-2">
                        <i class="fas fa-truck mr-2"></i> Manage Deliveries
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Items & Analytics -->
    <div class="col-xl-8 col-lg-7">
        <!-- Recent Items Table Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                <h6 class="m-0 font-weight-bold text-primary">Recent Items ({{ $store->items->count() }})</h6>
                <div class="stock-indicators">
                    @if($lowStockCount > 0)
                        <span class="badge badge-warning mr-2">
                            <i class="fas fa-exclamation-triangle"></i> {{ $lowStockCount }} Low Stock
                        </span>
                    @endif
                    @if($outOfStockCount > 0)
                        <span class="badge badge-danger mr-2">
                            <i class="fas fa-times-circle"></i> {{ $outOfStockCount }} Out of Stock
                        </span>
                    @endif
                    <a href="{{ route('store-admin.items.create') }}?store_id={{ $store->id }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add Item
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($store->items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="itemsTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($store->items->take(10) as $item)
                                <tr class="{{ $item->stock_quantity == 0 ? 'table-danger' : ($item->stock_quantity < 10 ? 'table-warning' : '') }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-cube text-primary mr-2"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong>{{ $item->name }}</strong>
                                                @if($item->description)
                                                    <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->category)
                                            <span class="badge badge-info">{{ $item->category->name }}</span>
                                        @else
                                            <span class="badge badge-secondary">No Category</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->price)
                                            <strong class="text-success">${{ number_format($item->price, 2) }}</strong>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="font-weight-bold {{ $item->stock_quantity == 0 ? 'text-danger' : ($item->stock_quantity < 10 ? 'text-warning' : 'text-success') }}">
                                                {{ $item->stock_quantity }}
                                            </span>
                                            @if($item->stock_quantity == 0)
                                                <i class="fas fa-times-circle text-danger ml-2" title="Out of Stock"></i>
                                            @elseif($item->stock_quantity < 5)
                                                <i class="fas fa-exclamation-circle text-danger ml-2" title="Critical Stock"></i>
                                            @elseif($item->stock_quantity < 10)
                                                <i class="fas fa-exclamation-triangle text-warning ml-2" title="Low Stock"></i>
                                            @else
                                                <i class="fas fa-check-circle text-success ml-2" title="In Stock"></i>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->stock_quantity == 0)
                                            <span class="badge badge-danger">Out of Stock</span>
                                        @elseif($item->stock_quantity < 5)
                                            <span class="badge badge-danger">Critical</span>
                                        @elseif($item->stock_quantity < 10)
                                            <span class="badge badge-warning">Low Stock</span>
                                        @else
                                            <span class="badge badge-success">In Stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $item->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('store-admin.items.show', $item) }}" class="btn btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('store-admin.items.edit', $item) }}" class="btn btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($store->items->count() > 10)
                    <div class="text-center mt-3">
                        <a href="{{ route('store-admin.items.index') }}?store_id={{ $store->id }}" class="btn btn-outline-primary btn-sm">
                            View All {{ $store->items->count() }} Items
                        </a>
                    </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No Items Found</h5>
                        <p class="text-muted mb-4">This store doesn't have any items yet.</p>
                        <a href="{{ route('store-admin.items.create') }}?store_id={{ $store->id }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Add First Item
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Orders Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                <a href="{{ route('store-admin.orders.index') }}?store_id={{ $store->id }}" class="btn btn-sm btn-outline-primary">
                    View All Orders
                </a>
            </div>
            <div class="card-body">
                @if($store->orders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="ordersTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($store->orders->take(10) as $order)
                                <tr>
                                    <td>
                                        <strong>#{{ $order->id }}</strong>
                                    </td>
                                    <td>
                                        @if($order->user)
                                            <div>
                                                <strong>{{ $order->user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $order->user->email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Guest</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-success">${{ number_format($order->total_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($order->status === 'completed')
                                            <span class="badge badge-success">{{ ucfirst($order->status) }}</span>
                                        @elseif($order->status === 'pending')
                                            <span class="badge badge-warning">{{ ucfirst($order->status) }}</span>
                                        @elseif($order->status === 'processing')
                                            <span class="badge badge-info">{{ ucfirst($order->status) }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                                        <br>
                                        <small class="text-muted">{{ $order->created_at->format('H:i A') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('store-admin.orders.show', $order) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted mb-2">No Orders Yet</h6>
                        <p class="text-muted small">Orders will appear here once customers start purchasing.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Analytics Cards Row -->
        <div class="row">
            <!-- Top Items by Stock -->
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 bg-success text-white">
                        <h6 class="m-0 font-weight-bold">Top Items by Stock</h6>
                    </div>
                    <div class="card-body">
                        @foreach($store->items->sortByDesc('stock_quantity')->take(5) as $item)
                            <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded {{ $item->stock_quantity == 0 ? 'bg-light' : '' }}">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 text-sm">{{ Str::limit($item->name, 25) }}</h6>
                                    <small class="text-muted">Stock: {{ $item->stock_quantity }}</small>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-{{ $item->stock_quantity == 0 ? 'danger' : ($item->stock_quantity < 10 ? 'warning' : 'success') }}">
                                        ${{ number_format($item->price, 2) }}
                                    </span>
                                </div>
                            </div>
                            @if(!$loop->last)<hr class="my-2">@endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">Low Stock Alerts</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $lowStockItems = $store->items->where('stock_quantity', '<', 10)->sortBy('stock_quantity')->take(5);
                        @endphp

                        @if($lowStockItems->count() > 0)
                            @foreach($lowStockItems as $item)
                                <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded bg-light">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-sm text-danger">{{ Str::limit($item->name, 25) }}</h6>
                                        <small class="text-muted">Only {{ $item->stock_quantity }} left</small>
                                    </div>
                                    <div class="text-right">
                                        <a href="{{ route('store-admin.items.edit', $item) }}" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-edit"></i> Restock
                                        </a>
                                    </div>
                                </div>
                                @if(!$loop->last)<hr class="my-2">@endif
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <p class="text-muted mb-0">No low stock items</p>
                                <small class="text-muted">All items are well stocked</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.css">
<style>
.store-info-grid {
    display: grid;
    gap: 1.5rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label-container {
    display: flex;
    align-items: center;
    flex: 0 0 140px;
}

.info-label-container .fas {
    width: 16px;
    margin-right: 0.75rem;
    font-size: 0.875rem;
}

.info-label {
    color: #6c757d;
    font-size: 0.875rem;
}

.info-value {
    flex: 1;
    text-align: right;
    font-size: 0.9rem;
    line-height: 1.4;
}

.font-weight-semibold {
    font-weight: 600;
}

.chart-pie {
    position: relative;
    height: 200px;
    width: 100%;
}

.stock-indicators {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.progress {
    border-radius: 10px;
    background-color: #f8f9fa;
}

.progress-bar {
    border-radius: 10px;
}

.badge-pill {
    border-radius: 50rem;
}

/* Hover effects */
.info-item:hover {
    background-color: #f8f9fa;
    margin: 0 -1rem;
    padding: 0.75rem 1rem;
    border-radius: 0.35rem;
    border-bottom: none;
}

.card {
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.04);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.card-header.bg-primary,
.card-header.bg-success,
.card-header.bg-info,
.card-header.bg-warning,
.card-header.bg-danger {
    border-bottom: none;
}

.border-right {
    border-right: 1px solid #e3e6f0 !important;
}
</style>
@endpush

@push('scripts')
<!-- Chart.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables
    $('#itemsTable').DataTable({
        "pageLength": 5,
        "order": [[3, 'desc']],
        "searching": false,
        "info": false,
        "paging": false
    });

    $('#ordersTable').DataTable({
        "pageLength": 5,
        "order": [[4, 'desc']],
        "searching": false,
        "info": false,
        "paging": false
    });

    // Stock Chart
    @if($store->items->count() > 0)
    var ctx = document.getElementById("stockChart");
    if (ctx) {
        var stockChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ["Healthy Stock", "Low Stock", "Out of Stock"],
                datasets: [{
                    data: [{{ $healthyStockCount }}, {{ $lowStockCount }}, {{ $outOfStockCount }}],
                    backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#17a673', '#dda20a', '#be2617'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 70,
            },
        });
    }
    @endif
});
</script>
@endpush
