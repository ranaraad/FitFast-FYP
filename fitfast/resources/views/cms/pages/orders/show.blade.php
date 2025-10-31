@extends('cms.layouts.app')

@section('page-title', 'Order Management')
@section('page-subtitle', 'Manage user orders')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Order Details - #{{ $order->id }}</h1>
    <div>
        <a href="{{ route('cms.orders.edit', $order) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Order
        </a>
        <a href="{{ route('cms.orders.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Orders
        </a>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-lg-8">
        <!-- Order Items Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Order Items</h6>
                <span class="badge badge-primary">{{ $order->total_items }} items</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Size</th>
                                <th>Color</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $orderItem)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-tshirt fa-2x text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $orderItem->item->name }}</h6>
                                            <small class="text-muted">
                                                Store: {{ $orderItem->item->store->name }}
                                                <br>
                                                SKU: {{ $orderItem->item->id }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>${{ number_format($orderItem->unit_price, 2) }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ $orderItem->quantity }}</span>
                                </td>
                                <td>
                                    @if($orderItem->selected_size)
                                        <span class="badge badge-info">{{ $orderItem->selected_size }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $orderItem->selected_color }}; color: white;">
                                        {{ $orderItem->selected_color }}
                                    </span>
                                </td>
                                <td>
                                    <strong>${{ number_format($orderItem->quantity * $orderItem->unit_price, 2) }}</strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right"><strong>Order Total:</strong></td>
                                <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Delivery Information</h6>
            </div>
            <div class="card-body">
                @if($order->delivery)
                <div class="row">
                    <div class="col-md-6">
                        <strong>Delivery Status:</strong>
                        <span class="badge badge-{{ $order->delivery->status === 'delivered' ? 'success' : ($order->delivery->status === 'shipped' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($order->delivery->status) }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Address:</strong><br>
                        {{ $order->delivery->address }}
                    </div>
                </div>
                @if($order->delivery->tracking_id)
                <div class="row mt-3">
                    <div class="col-12">
                        <strong>Tracking ID:</strong> {{ $order->delivery->tracking_id }}<br>
                        <strong>Carrier:</strong> {{ $order->delivery->carrier ?? 'N/A' }}<br>
                        @if($order->delivery->estimated_delivery)
                        <strong>Estimated Delivery:</strong> {{ $order->delivery->estimated_delivery->format('M d, Y') }}
                        @endif
                    </div>
                </div>
                @endif
                @else
                <p class="text-muted">No delivery information available.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Order Summary Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Summary</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Order ID:</strong>
                    </div>
                    <div class="col-6 text-right">
                        #{{ $order->id }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Customer:</strong>
                    </div>
                    <div class="col-6 text-right">
                        {{ $order->user->name }}<br>
                        <small class="text-muted">{{ $order->user->email }}</small>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Store:</strong>
                    </div>
                    <div class="col-6 text-right">
                        {{ $order->store->name }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-6 text-right">
                        {!! $order->status_badge !!}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Total Items:</strong>
                    </div>
                    <div class="col-6 text-right">
                        {{ $order->total_items }}
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Order Total:</strong>
                    </div>
                    <div class="col-6 text-right">
                        <h5 class="text-primary">${{ number_format($order->total_amount, 2) }}</h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Order Date:</strong>
                    </div>
                    <div class="col-6 text-right">
                        {{ $order->created_at->format('M d, Y') }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <strong>Last Updated:</strong>
                    </div>
                    <div class="col-6 text-right">
                        {{ $order->updated_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
            </div>
            <div class="card-body">
                @if($order->payment && $order->payment->paymentMethod)
                <div class="row">
                    <div class="col-md-6">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Payment Method:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <span class="badge badge-{{ $order->payment->paymentMethod->type == 'card' ? 'primary' : 'success' }}">
                                    {{ ucfirst($order->payment->paymentMethod->type) }}
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Payment Status:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <span class="badge badge-{{ $order->payment->status === 'completed' ? 'success' : ($order->payment->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($order->payment->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Amount:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <strong>${{ number_format($order->payment->amount, 2) }}</strong>
                            </div>
                        </div>
                        @if($order->payment->transaction_id)
                        <div class="row">
                            <div class="col-6">
                                <strong>Transaction ID:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <code>{{ $order->payment->transaction_id }}</code>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i>
                    <strong>Demo Only:</strong> This is a mock payment system. No real payments were processed.
                </div>
                @else
                <div class="text-center py-3">
                    <i class="fas fa-credit-card fa-2x text-muted mb-3"></i>
                    <p class="text-muted">No payment information available</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('cms.orders.edit', $order) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Edit Order
                    </a>

                    <!-- Status Update Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-info btn-block dropdown-toggle" type="button" id="statusDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-sync-alt"></i> Update Status
                        </button>
                        <div class="dropdown-menu" aria-labelledby="statusDropdown">
                            @foreach(App\Models\Order::STATUSES as $status => $label)
                                @if($status !== $order->status)
                                <form action="{{ route('cms.orders.update-status', $order) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $status }}">
                                    <button type="submit" class="dropdown-item" onclick="return confirm('Change status to {{ $label }}?')">
                                        {{ $label }}
                                    </button>
                                </form>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    @if($order->canBeCancelled())
                    <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Order
                    </button>
                    @endif
                </div>
                <form id="delete-form" action="{{ route('cms.orders.destroy', $order) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete() {
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
                document.getElementById('delete-form').submit();
            }
        });
    }
</script>
@endpush
