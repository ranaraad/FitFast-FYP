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
                                {{ number_format($userStats['conversion_rate'] ?? 0, 1) }}% conversion
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
                        <div class="progress-bar bg-primary" style="width: {{ ($orderStats['pending'] / $stats['total_orders']) * 100 }}%"></div>
                        <div class="progress-bar bg-warning" style="width: {{ ($orderStats['processing'] / $stats['total_orders']) * 100 }}%"></div>
                        <div class="progress-bar bg-info" style="width: {{ ($orderStats['shipped'] / $stats['total_orders']) * 100 }}%"></div>
                        <div class="progress-bar bg-success" style="width: {{ ($orderStats['delivered'] / $stats['total_orders']) * 100 }}%"></div>
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
