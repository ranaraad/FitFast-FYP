@extends('cms.layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome to FitFast Admin')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Overview</h1>
    <div class="d-none d-sm-inline-block">
        <span class="text-muted">Last updated: {{ now()->format('M j, Y g:i A') }}</span>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Revenue Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Revenue
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            ${{ number_format($revenueStats['total_revenue'], 2) }}
                        </div>
                        <div class="mt-2">
                            <span class="text-xs {{ $revenueStats['revenue_trend'] >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="fas fa-arrow-{{ $revenueStats['revenue_trend'] >= 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($revenueStats['revenue_trend']), 1) }}%
                            </span>
                            <span class="text-xs text-muted ml-1">vs last month</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Orders
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_orders'] }}</div>
                        <div class="mt-2">
                            <span class="badge badge-success">{{ $orderStats['delivered'] }} delivered</span>
                            <span class="badge badge-warning ml-1">{{ $orderStats['processing'] }} processing</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Users
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                        <div class="mt-2">
                            <span class="text-xs text-success">
                                +{{ $userStats['new_today'] }} today
                            </span>
                            <span class="text-xs text-muted ml-1">
                                {{ number_format($orderStats['conversion_rate'], 1) }}% conversion
                            </span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Support Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Support
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_support'] }}</div>
                        <div class="mt-2">
                            <a href="{{ route('cms.chat-support.index') }}" class="text-xs text-warning">
                                <i class="fas fa-comments"></i> View tickets
                            </a>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-comments fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Store Performance Metrics -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Store Performance Metrics</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                         aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">View Options:</div>
                        <a class="dropdown-item" href="#" onclick="toggleStoreMetrics('daily')">Daily</a>
                        <a class="dropdown-item" href="#" onclick="toggleStoreMetrics('weekly')">Weekly</a>
                        <a class="dropdown-item" href="#" onclick="toggleStoreMetrics('monthly')">Monthly</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <!-- Store Revenue Performance -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Store Revenue
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ${{ number_format($storeStats['total_revenue'], 2) }}
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-xs {{ $storeStats['revenue_growth']['growth_rate'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                <i class="fas fa-arrow-{{ $storeStats['revenue_growth']['growth_rate'] >= 0 ? 'up' : 'down' }}"></i>
                                                {{ number_format(abs($storeStats['revenue_growth']['growth_rate']), 1) }}%
                                            </span>
                                            <span class="text-xs text-muted ml-1">growth</span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-store fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Stores -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Active Stores
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $storeStats['active_stores'] }}
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-xs text-muted">
                                                {{ $storeStats['stores_with_orders'] }} with orders
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-store-alt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Items -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Total Items
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $storeStats['total_items_across_stores'] }}
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-xs text-muted">
                                                {{ number_format($storeStats['average_items_per_store'], 1) }} avg/store
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-tshirt fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Alert -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Stock Alert
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $storeStats['low_stock_stores'] }}
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge badge-danger">Critical</span>
                                            <span class="text-xs text-muted ml-1">stores</span>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Stores Section -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary mb-3">
                                    <i class="fas fa-trophy mr-2"></i>Top Stores by Revenue
                                </h6>
                                <div class="list-group list-group-flush">
                                    @foreach($storeStats['top_stores_by_revenue'] as $store)
                                    <div class="list-group-item d-flex align-items-center px-0">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-success">
                                                <i class="fas fa-store text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="font-weight-bold">{{ $store['name'] }}</div>
                                            <div class="small text-muted">
                                                {{ $store['order_count'] }} orders
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-weight-bold text-success">
                                                ${{ number_format($store['revenue'], 2) }}
                                            </div>
                                            <span class="badge badge-{{ $store['status'] === 'active' ? 'success' : 'secondary' }} badge-sm">
                                                {{ $store['status'] }}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary mb-3">
                                    <i class="fas fa-chart-line mr-2"></i>Revenue Performance
                                </h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="text-xs font-weight-bold text-muted text-uppercase">Today</div>
                                        <div class="h5 font-weight-bold text-success">${{ number_format($storeStats['revenue_today'], 2) }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-xs font-weight-bold text-muted text-uppercase">This Week</div>
                                        <div class="h5 font-weight-bold text-primary">${{ number_format($storeStats['revenue_this_week'], 2) }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-xs font-weight-bold text-muted text-uppercase">This Month</div>
                                        <div class="h5 font-weight-bold text-info">${{ number_format($storeStats['revenue_this_month'], 2) }}</div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row text-center mt-3">
                                    <div class="col-6">
                                        <div class="text-xs font-weight-bold text-muted text-uppercase">Current Month</div>
                                        <div class="h6 font-weight-bold text-dark">${{ number_format($storeStats['revenue_growth']['current_month'], 2) }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-xs font-weight-bold text-muted text-uppercase">Last Month</div>
                                        <div class="h6 font-weight-bold text-dark">${{ number_format($storeStats['revenue_growth']['last_month'], 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Store Inventory & Orders -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary mb-3">
                                    <i class="fas fa-boxes mr-2"></i>Store Inventory
                                </h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="text-xs font-weight-bold text-muted text-uppercase">Total Items</div>
                                        <div class="h5 font-weight-bold text-dark">{{ $storeStats['total_items_across_stores'] }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-xs font-weight-bold text-muted text-uppercase">Avg/Store</div>
                                        <div class="h5 font-weight-bold text-info">{{ number_format($storeStats['average_items_per_store'], 1) }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-xs font-weight-bold text-muted text-uppercase">Low Stock</div>
                                        <div class="h5 font-weight-bold text-warning">{{ $storeStats['low_stock_stores'] }}</div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h6 class="font-weight-bold text-muted mb-2">Stores with Most Items</h6>
                                    @foreach($storeStats['stores_with_most_items'] as $store)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-sm">{{ $store->name }}</span>
                                        <span class="badge badge-primary">{{ $store->items_count }} items</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="font-weight-bold text-primary mb-3">
                                    <i class="fas fa-shopping-cart mr-2"></i>Store Orders
                                </h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="text-xs font-weight-bold text-muted text-uppercase">Stores with Orders</div>
                                        <div class="h5 font-weight-bold text-success">{{ $storeStats['stores_with_orders'] }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-xs font-weight-bold text-muted text-uppercase">Total Orders</div>
                                        <div class="h5 font-weight-bold text-primary">{{ $stats['total_orders'] }}</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-xs font-weight-bold text-muted text-uppercase">Avg/Store</div>
                                        <div class="h5 font-weight-bold text-info">
                                            @php
                                                $storesWithOrders = $storeStats['stores_with_orders'] > 0 ? $storeStats['stores_with_orders'] : 1;
                                                $avgOrdersPerStore = $stats['total_orders'] / $storesWithOrders;
                                            @endphp
                                            {{ number_format($avgOrdersPerStore, 1) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h6 class="font-weight-bold text-muted mb-2">Stores with Most Orders</h6>
                                    @foreach($storeStats['top_stores_by_revenue'] as $store)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-sm">{{ $store['name'] }}</span>
                                        <span class="badge badge-success">{{ $store['order_count'] }} orders</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue & Orders Row -->
<div class="row">
    <!-- Revenue Breakdown -->
    <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Revenue Analytics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-right">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($revenueStats['today'], 2) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-right">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">This Week</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($revenueStats['this_week'], 2) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">This Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($revenueStats['this_month'], 2) }}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">Avg Order Value</div>
                            <div class="h6 font-weight-bold text-gray-800">
                                ${{ number_format($revenueStats['average_order_value'], 2) }}
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">Conversion Rate</div>
                            <div class="h6 font-weight-bold text-gray-800">
                                {{ number_format($orderStats['conversion_rate'], 1) }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status Breakdown -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-success">Order Status Overview</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orderStats['pending'] }}</div>
                        </div>
                        <div class="col-3">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Processing</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orderStats['processing'] }}</div>
                        </div>
                        <div class="col-3">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Shipped</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orderStats['shipped'] }}</div>
                        </div>
                        <div class="col-3">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Delivered</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orderStats['delivered'] }}</div>
                        </div>
                    </div>
                    <hr>
                    <div class="mt-3">
                        <div class="text-xs font-weight-bold text-muted text-uppercase mb-1">Order Distribution</div>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: {{ ($orderStats['pending'] / max($stats['total_orders'], 1)) * 100 }}%"></div>
                            <div class="progress-bar bg-warning" style="width: {{ ($orderStats['processing'] / max($stats['total_orders'], 1)) * 100 }}%"></div>
                            <div class="progress-bar bg-info" style="width: {{ ($orderStats['shipped'] / max($stats['total_orders'], 1)) * 100 }}%"></div>
                            <div class="progress-bar bg-success" style="width: {{ ($orderStats['delivered'] / max($stats['total_orders'], 1)) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Row -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                    <a href="{{ route('cms.orders.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($recentActivity['recent_orders'] as $order)
                        <div class="list-group-item d-flex align-items-center">
                            <div class="mr-3">
                                <div class="icon-circle bg-primary">
                                    <i class="fas fa-shopping-cart text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">
                                    <a href="{{ route('cms.orders.show', $order) }}">Order #{{ $order->id }}</a>
                                </div>
                                <div class="small text-muted">
                                    {{ $order->user->name }} • ${{ number_format($order->total_amount, 2) }}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'pending' ? 'warning' : 'primary') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <div class="small text-muted">{{ $order->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users & Support -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-info">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <!-- New Users -->
                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-user-plus mr-2"></i>New Users
                    </h6>
                    <div class="list-group list-group-flush mb-4">
                        @foreach($recentActivity['recent_users'] as $user)
                        <div class="list-group-item d-flex align-items-center">
                            <div class="mr-3">
                                <div class="icon-circle bg-success">
                                    <span class="text-white">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold">{{ $user->name }}</div>
                                <div class="small text-muted">{{ $user->email }}</div>
                            </div>
                            <div class="text-right">
                                <div class="small text-muted">{{ $user->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Support Tickets -->
                    <h6 class="font-weight-bold text-warning mb-3">
                        <i class="fas fa-comments mr-2"></i>Pending Support
                    </h6>
                    <div class="list-group list-group-flush">
                        @foreach($recentActivity['pending_support'] as $ticket)
                        <div class="list-group-item d-flex align-items-center">
                            <div class="mr-3">
                                <div class="icon-circle bg-warning">
                                    <i class="fas fa-question text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="font-weight-bold text-truncate">{{ $ticket->subject }}</div>
                                <div class="small text-muted">{{ $ticket->user->name }}</div>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-warning">Pending</span>
                                <div class="small text-muted">{{ $ticket->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2 col-6 mb-3">
                            <div class="border-right">
                                <div class="text-xs font-weight-bold text-muted text-uppercase">Active Carts</div>
                                <div class="h4 font-weight-bold text-primary">{{ $stats['active_carts'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="border-right">
                                <div class="text-xs font-weight-bold text-muted text-uppercase">Total Items</div>
                                <div class="h4 font-weight-bold text-info">{{ $stats['total_items'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="border-right">
                                <div class="text-xs font-weight-bold text-muted text-uppercase">Stores</div>
                                <div class="h4 font-weight-bold text-success">{{ $stats['total_stores'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="border-right">
                                <div class="text-xs font-weight-bold text-muted text-uppercase">New Users (Week)</div>
                                <div class="h4 font-weight-bold text-warning">{{ $userStats['new_this_week'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="border-right">
                                <div class="text-xs font-weight-bold text-muted text-uppercase">Active Users</div>
                                <div class="h4 font-weight-bold text-dark">{{ $userStats['active_users'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="text-xs font-weight-bold text-muted text-uppercase">Cancelled Orders</div>
                            <div class="h4 font-weight-bold text-danger">{{ $orderStats['cancelled'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.icon-circle {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}
.progress {
    height: 8px;
}
.list-group-item {
    border: none;
    padding: 0.75rem 0;
}
.border-right {
    border-right: 1px solid #e3e6f0 !important;
}
.card-border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.card-border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.card-border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.card-border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
@media (max-width: 768px) {
    .border-right {
        border-right: none !important;
        border-bottom: 1px solid #e3e6f0 !important;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function toggleStoreMetrics(period) {
    // Add your AJAX call here to update metrics based on selected period
    console.log('Switching to ' + period + ' metrics');
    // Example: fetch('/cms/dashboard/metrics?period=' + period)
    //          .then(response => response.json())
    //          .then(data => updateMetrics(data));

    // Show loading state
    const cardBody = document.querySelector('.card-body');
    const originalContent = cardBody.innerHTML;
    cardBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Loading ' + period + ' metrics...</div></div>';

    // Simulate API call
    setTimeout(() => {
        cardBody.innerHTML = originalContent;
        showAlert('Metrics updated to ' + period + ' view', 'success');
    }, 1000);
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    document.querySelector('.card-body').insertBefore(alert, document.querySelector('.card-body').firstChild);
}
</script>
@endpush
