@extends('cms.layouts.app')

@section('page-title', 'Payment Management')
@section('page-subtitle', 'Manage user payments')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Payment Transactions</h1>

    <div>
        <!-- Export Dropdown -->
        <div class="btn-group mr-2">
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-file-export fa-sm text-white-50"></i> Export
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('cms.payments.export') }}">
                    <i class="fas fa-file-csv text-success"></i> Export All Payments
                </a>
                <div class="dropdown-divider"></div>
                <h6 class="dropdown-header">Export by Status</h6>
                <a class="dropdown-item" href="{{ route('cms.payments.export-by-status', 'completed') }}">
                    <i class="fas fa-check-circle text-success"></i> Completed Payments
                </a>
                <a class="dropdown-item" href="{{ route('cms.payments.export-by-status', 'pending') }}">
                    <i class="fas fa-clock text-warning"></i> Pending Payments
                </a>
                <a class="dropdown-item" href="{{ route('cms.payments.export-by-status', 'failed') }}">
                    <i class="fas fa-times-circle text-danger"></i> Failed Payments
                </a>
                <a class="dropdown-item" href="{{ route('cms.payments.export-by-status', 'refunded') }}">
                    <i class="fas fa-undo text-info"></i> Refunded Payments
                </a>
            </div>
        </div>

        <!-- Date Range Export Form -->
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#dateRangeModal">
            <i class="fas fa-calendar-alt fa-sm text-white-50"></i> Export by Date
        </button>
    </div>
</div>

<!-- Date Range Modal -->
<div class="modal fade" id="dateRangeModal" tabindex="-1" role="dialog" aria-labelledby="dateRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dateRangeModalLabel">Export Payments by Date Range</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('cms.payments.export-by-date') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="{{ date('Y-m-d', strtotime('-30 days')) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download"></i> Export Payments
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Payments
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_payments'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-receipt fa-2x text-gray-300"></i>
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
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            ${{ number_format($stats['total_revenue'], 2) }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                            Pending Payments
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_payments'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                            Failed Payments
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['failed_payments'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Search Payments</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('cms.payments.search') }}" method="GET">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="transaction_id">Transaction ID</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id"
                               value="{{ request('transaction_id') }}" placeholder="Enter transaction ID">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search Payments
                    </button>
                    <a href="{{ route('cms.payments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Payment Transactions</h6>
        <div>
            @if(request()->hasAny(['transaction_id', 'status']))
            <span class="badge badge-warning mr-2">Filtered Results</span>
            @endif
            <span class="badge badge-primary">Showing {{ $payments->count() }} of {{ $payments->total() }} payments</span>
        </div>
    </div>
    <div class="card-body">
        @if(session('export_success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('export_success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($payments->isEmpty())
        <div class="text-center py-4">
            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No payments found</h5>
            <p class="text-muted">No payment transactions match your search criteria.</p>
            <a href="{{ route('cms.payments.index') }}" class="btn btn-primary">View All Payments</a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Transaction ID</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td class="font-weight-bold">#{{ $payment->id }}</td>
                        <td>
                            <a href="{{ route('cms.orders.show', $payment->order) }}" class="text-primary">
                                Order #{{ $payment->order->id }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="icon-circle bg-primary" style="width: 30px; height: 30px; font-size: 12px;">
                                        <span class="text-white">{{ substr($payment->order->user->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <strong>{{ $payment->order->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $payment->order->user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong class="text-success">${{ number_format($payment->amount, 2) }}</strong>
                        </td>
                        <td>
                            @if($payment->paymentMethod)
                                <span class="badge badge-{{ $payment->paymentMethod->type === 'cash' ? 'success' : 'primary' }}">
                                    {{ ucfirst($payment->paymentMethod->type) }}
                                </span>
                            @else
                                <span class="badge badge-secondary">Not specified</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $payment->status_color }} p-2">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td>
                            @if($payment->transaction_id)
                                <code class="bg-light p-1 rounded">{{ $payment->transaction_id }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-nowrap">
                                {{ $payment->created_at->format('M j, Y') }}
                                <br>
                                <small class="text-muted">{{ $payment->created_at->format('g:i A') }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('cms.payments.show', $payment) }}"
                                   class="btn btn-info btn-sm" title="View Payment Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($payment->isCompleted())
                                <form action="{{ route('cms.payments.refund', $payment) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm"
                                            onclick="return confirm('Process refund for ${{ number_format($payment->amount, 2) }}?')"
                                            title="Refund Payment">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} entries
            </div>
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.icon-circle {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.075);
}
</style>
@endpush
