@extends('cms.layouts.store-admin-app')

@section('page-title', 'Order Details')
@section('page-subtitle', 'View order information')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Order Details - #{{ $order->id }}</h1>
    <div>
        <a href="{{ route('store-admin.orders.edit', $order) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Order
        </a>
        <a href="{{ route('store-admin.orders.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
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
                <div>
                    <span class="badge badge-primary">{{ $order->orderItems->count() }} items</span>
                    <span class="badge badge-info">{{ $order->store->name }}</span>
                </div>
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
                                <th>Stock Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $orderItem)
                            @php
                                $item = $orderItem->item;
                                $currentStock = $item->getColorStock($orderItem->selected_color);
                                $isLowStock = $currentStock < 10;
                                $isOutOfStock = $currentStock == 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->image)
                                        <div class="flex-shrink-0">
                                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                                 class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                        </div>
                                        @else
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-tshirt fa-2x text-primary"></i>
                                        </div>
                                        @endif
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $item->name }}</h6>
                                            <small class="text-muted">
                                                SKU: {{ $item->id }}
                                                @if($item->category)
                                                <br>Category: {{ $item->category->name }}
                                                @endif
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
                                <td>
                                    @if($isOutOfStock)
                                        <span class="badge badge-danger" title="Out of stock">
                                            <i class="fas fa-times-circle"></i> Out
                                        </span>
                                    @elseif($isLowStock)
                                        <span class="badge badge-warning" title="Low stock - {{ $currentStock }} remaining">
                                            <i class="fas fa-exclamation-triangle"></i> Low ({{ $currentStock }})
                                        </span>
                                    @else
                                        <span class="badge badge-success" title="In stock - {{ $currentStock }} available">
                                            <i class="fas fa-check-circle"></i> In Stock ({{ $currentStock }})
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-right"><strong>Order Total:</strong></td>
                                <td colspan="2"><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Delivery Information</h6>
                @if($order->delivery)
                <div class="delivery-actions">
                    @if($order->delivery->status === 'pending')
                    <button class="btn btn-sm btn-warning" onclick="updateDeliveryStatus('shipped')">
                        <i class="fas fa-shipping-fast"></i> Mark as Shipped
                    </button>
                    @elseif($order->delivery->status === 'shipped')
                    <button class="btn btn-sm btn-success" onclick="updateDeliveryStatus('delivered')">
                        <i class="fas fa-check"></i> Mark as Delivered
                    </button>
                    @endif
                </div>
                @endif
            </div>
            <div class="card-body">
                @if($order->delivery)
                <div class="row">
                    <div class="col-md-6">
                        <strong>Delivery Status:</strong>
                        <span class="badge badge-{{ $order->delivery->status === 'delivered' ? 'success' : ($order->delivery->status === 'shipped' ? 'warning' : 'secondary') }} badge-pill">
                            {{ ucfirst($order->delivery->status) }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Address:</strong><br>
                        <div class="mt-1 p-2 bg-light rounded">
                            {{ $order->delivery->address }}
                        </div>
                    </div>
                </div>

                @if($order->delivery->tracking_id || $order->delivery->carrier)
                <div class="row mt-3">
                    <div class="col-md-6">
                        @if($order->delivery->tracking_id)
                        <strong>Tracking ID:</strong>
                        <code>{{ $order->delivery->tracking_id }}</code>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if($order->delivery->carrier)
                        <strong>Carrier:</strong> {{ $order->delivery->carrier }}
                        @endif
                    </div>
                </div>
                @endif

                @if(!$order->delivery->tracking_id && $order->delivery->status === 'pending')
                <div class="row mt-3">
                    <div class="col-12">
                        <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#trackingModal">
                            <i class="fas fa-barcode"></i> Add Tracking Information
                        </button>
                    </div>
                </div>
                @endif

                @if($order->delivery->estimated_delivery)
                <div class="row mt-3">
                    <div class="col-12">
                        <strong>Estimated Delivery:</strong>
                        <span class="{{ $order->delivery->estimated_delivery->isPast() ? 'text-danger' : 'text-success' }}">
                            {{ $order->delivery->estimated_delivery->format('M d, Y') }}
                            @if($order->delivery->estimated_delivery->isPast())
                            <small class="text-danger">(Overdue)</small>
                            @endif
                        </span>
                    </div>
                </div>
                @endif
                @else
                <div class="text-center py-3">
                    <i class="fas fa-truck fa-2x text-muted mb-3"></i>
                    <p class="text-muted">No delivery information available</p>
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#deliveryModal">
                        <i class="fas fa-plus"></i> Create Delivery
                    </button>
                </div>
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
                <div class="order-summary-grid">
                    <div class="summary-item">
                        <span class="summary-label">Order ID:</span>
                        <span class="summary-value">#{{ $order->id }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Customer:</span>
                        <span class="summary-value">
                            {{ $order->user->name }}<br>
                            <small class="text-muted">{{ $order->user->email }}</small>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Store:</span>
                        <span class="summary-value">
                            <i class="fas fa-store text-primary mr-1"></i>
                            {{ $order->store->name }}
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Status:</span>
                        <span class="summary-value">
                            {!! $order->status_badge !!}
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Total Items:</span>
                        <span class="summary-value">
                            {{ $order->orderItems->count() }}
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Order Total:</span>
                        <span class="summary-value text-primary font-weight-bold">
                            ${{ number_format($order->total_amount, 2) }}
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Order Date:</span>
                        <span class="summary-value">
                            {{ $order->created_at->format('M d, Y') }}<br>
                            <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Last Updated:</span>
                        <span class="summary-value">
                            {{ $order->updated_at->diffForHumans() }}
                        </span>
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
                <div class="payment-info">
                    <div class="payment-item">
                        <span class="payment-label">Method:</span>
                        <span class="payment-value">
                            <span class="badge badge-{{ $order->payment->paymentMethod->type == 'card' ? 'primary' : 'success' }}">
                                {{ ucfirst($order->payment->paymentMethod->type) }}
                            </span>
                        </span>
                    </div>
                    <div class="payment-item">
                        <span class="payment-label">Status:</span>
                        <span class="payment-value">
                            <span class="badge badge-{{ $order->payment->status === 'completed' ? 'success' : ($order->payment->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($order->payment->status) }}
                            </span>
                        </span>
                    </div>
                    <div class="payment-item">
                        <span class="payment-label">Amount:</span>
                        <span class="payment-value font-weight-bold">
                            ${{ number_format($order->payment->amount, 2) }}
                        </span>
                    </div>
                    @if($order->payment->transaction_id)
                    <div class="payment-item">
                        <span class="payment-label">Transaction ID:</span>
                        <span class="payment-value">
                            <code class="text-sm">{{ $order->payment->transaction_id }}</code>
                        </span>
                    </div>
                    @endif
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
                    <a href="{{ route('store-admin.orders.edit', $order) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Edit Order
                    </a>

                    <!-- Status Update Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-info btn-block dropdown-toggle" type="button" id="statusDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-sync-alt"></i> Update Status
                        </button>
                        <div class="dropdown-menu w-100" aria-labelledby="statusDropdown">
                            @foreach(App\Models\Order::STATUSES as $status => $label)
                                @if($status !== $order->status)
                                <a class="dropdown-item" href="#" onclick="updateOrderStatus('{{ $status }}', '{{ $label }}')">
                                    {{ $label }}
                                </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    @if($order->canBeCancelled())
                    <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Cancel Order
                    </button>
                    @endif

                    <a href="{{ route('store-admin.items.index', ['store_id' => $order->store_id]) }}" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-box"></i> View Store Items
                    </a>
                </div>
                <form id="delete-form" action="{{ route('store-admin.orders.destroy', $order) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
                <form id="status-form" action="{{ route('store-admin.orders.update-status', $order) }}" method="POST" class="d-none">
                    @csrf
                    <input type="hidden" name="status" id="status-input">
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Modal -->
@if($order->delivery)
<div class="modal fade" id="trackingModal" tabindex="-1" role="dialog" aria-labelledby="trackingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="trackingModalLabel">Add Tracking Information</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('store-admin.deliveries.update-tracking', $order->delivery) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tracking_id">Tracking ID</label>
                        <input type="text" class="form-control" id="tracking_id" name="tracking_id"
                               value="{{ $order->delivery->tracking_id }}" required>
                    </div>
                    <div class="form-group">
                        <label for="carrier">Carrier</label>
                        <select class="form-control" id="carrier" name="carrier" required>
                            <option value="">Select Carrier</option>
                            <option value="aramex" {{ $order->delivery->carrier == 'aramex' ? 'selected' : '' }}>Aramex</option>
                            <option value="dhl" {{ $order->delivery->carrier == 'dhl' ? 'selected' : '' }}>DHL</option>
                            <option value="fedex" {{ $order->delivery->carrier == 'fedex' ? 'selected' : '' }}>FedEx</option>
                            <option value="ups" {{ $order->delivery->carrier == 'ups' ? 'selected' : '' }}>UPS</option>
                            <option value="usps" {{ $order->delivery->carrier == 'usps' ? 'selected' : '' }}>USPS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="estimated_delivery">Estimated Delivery</label>
                        <input type="date" class="form-control" id="estimated_delivery" name="estimated_delivery"
                               value="{{ $order->delivery->estimated_delivery ? $order->delivery->estimated_delivery->format('Y-m-d') : '' }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Tracking</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.order-summary-grid {
    display: grid;
    gap: 1.25rem; /* Increased gap between summary items */
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start; /* Changed to flex-start for better alignment */
    padding: 0.1rem 0; /* Increased vertical padding */
    border-bottom: 1px solid #f8f9fa;
    min-height: 2rem; /* Ensure consistent height */
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-label {
    font-weight: bolder;
    color: #6c757d;
    line-height: 1.4;
}

.summary-value {
    text-align: right;
    font-weight: 500;
    line-height: 1.4;
}

.payment-info {
    display: grid;
    gap: 0.25rem; /* Increased gap between payment items */
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0; /* Added vertical padding */
    min-height: 2.5rem; /* Ensure consistent height */
    border-bottom: 1px solid #f8f9fa; /* Added subtle separators */
}

.payment-item:last-child {
    border-bottom: none;
}

.payment-label {
    font-weight: 600;
    color: #6c757d;
    line-height: 1.4;
}

.payment-value {
    font-weight: 500;
    line-height: 1.4;
}

.badge-pill {
    border-radius: 50rem;
}

.delivery-actions .btn {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
}

/* Additional spacing improvements */
.card-body {
    padding: 1.5rem; /* Ensure consistent card padding */
}

.table td, .table th {
    padding: 0.75rem; /* Ensure table cell padding */
}

/* Improved spacing for small screens */
@media (max-width: 768px) {
    .order-summary-grid {
        gap: 1rem;
    }

    .summary-item {
        padding: 0.5rem 0;
        min-height: 2.5rem;
    }

    .payment-info {
        gap: 0.75rem;
    }

    .payment-item {
        padding: 0.5rem 0;
        min-height: 2rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete() {
    Swal.fire({
        title: 'Cancel Order?',
        text: "This will cancel the order and restore item stock!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, cancel order!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form').submit();
        }
    });
}

function updateOrderStatus(status, label) {
    Swal.fire({
        title: 'Update Order Status?',
        html: `Are you sure you want to change the order status to <strong>${label}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, update status!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Set the status value and submit the form
            document.getElementById('status-input').value = status;
            document.getElementById('status-form').submit();
        }
    });
}

function updateDeliveryStatus(status) {
    const statusText = status === 'shipped' ? 'shipped' : 'delivered';
    const statusLabel = status === 'shipped' ? 'Shipped' : 'Delivered';

    Swal.fire({
        title: `Mark as ${statusLabel}?`,
        html: `Are you sure you want to mark this delivery as <strong>${statusLabel}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Yes, mark as ${statusLabel}`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // You'll need to implement this endpoint in your DeliveryController
            fetch(`/store-admin/deliveries/${@json($order->delivery->id)}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: `Delivery marked as ${statusText}`,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Failed to update status', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to update delivery status', 'error');
            });
        }
    });
}
</script>
@endpush
