@extends('cms.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Payment Details - #{{ $payment->id }}</h1>
    <div>
        <a href="{{ route('cms.payments.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Payments
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Payment Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Payment ID:</strong><br>
                            #{{ $payment->id }}
                        </div>
                        <div class="mb-3">
                            <strong>Amount:</strong><br>
                            <h4 class="text-primary">${{ number_format($payment->amount, 2) }}</h4>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong><br>
                            <span class="badge badge-{{ $payment->status_color }} p-2">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <strong>Transaction ID:</strong><br>
                            @if($payment->transaction_id)
                                <code>{{ $payment->transaction_id }}</code>
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <strong>Payment Method:</strong><br>
                            @if($payment->paymentMethod)
                                <span class="badge badge-info p-2">
                                    {{ ucfirst($payment->paymentMethod->type) }}
                                </span>
                                @if($payment->paymentMethod->is_default)
                                    <span class="badge badge-success ml-1">Default</span>
                                @endif
                            @else
                                <span class="text-muted">Unknown</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <strong>Payment Date:</strong><br>
                            {{ $payment->created_at->format('F j, Y \a\t g:i A') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Associated Order -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Associated Order</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Order ID:</strong><br>
                        <a href="{{ route('cms.orders.show', $payment->order) }}">
                            Order #{{ $payment->order->id }}
                        </a>
                    </div>
                    <div class="col-md-6">
                        <strong>Order Status:</strong><br>
                        {!! $payment->order->status_badge !!}
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <strong>Store:</strong><br>
                        {{ $payment->order->store->name }}
                    </div>
                    <div class="col-md-6">
                        <strong>Order Date:</strong><br>
                        {{ $payment->order->created_at->format('M j, Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Customer Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="icon-circle bg-primary mx-auto mb-3">
                        <span class="text-white font-weight-bold">
                            {{ substr($payment->order->user->name, 0, 1) }}
                        </span>
                    </div>
                    <h5 class="font-weight-bold">{{ $payment->order->user->name }}</h5>
                    <p class="text-muted">{{ $payment->order->user->email }}</p>
                </div>
                <div class="text-center">
                    <a href="{{ route('cms.users.show', $payment->order->user) }}" class="btn btn-outline-primary btn-sm">
                        View Customer Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($payment->isCompleted())
                    <form action="{{ route('cms.payments.refund', $payment) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-block"
                                onclick="return confirm('Process refund for ${{ number_format($payment->amount, 2) }}?')">
                            <i class="fas fa-undo"></i> Process Refund
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('cms.orders.show', $payment->order) }}" class="btn btn-info btn-block">
                        <i class="fas fa-shopping-cart"></i> View Order Details
                    </a>

                    <a href="{{ route('cms.payments.index') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> Back to Payments
                    </a>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Payment Timeline</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <strong>Payment Created</strong>
                            <div class="text-muted small">{{ $payment->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>
                    @if($payment->updated_at->gt($payment->created_at))
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <strong>Status Updated</strong>
                            <div class="text-muted small">{{ $payment->updated_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>
                    @endif
                    @if($payment->status === 'refunded' && $payment->refunded_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <strong>Payment Refunded</strong>
                            <div class="text-muted small">{{ $payment->refunded_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}
.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}
.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
}
.timeline-content {
    padding-left: 1rem;
}
</style>
@endsection
