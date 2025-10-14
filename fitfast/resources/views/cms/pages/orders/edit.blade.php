@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Order - #{{ $order->id }}</h1>
    <a href="{{ route('cms.orders.show', $order) }}" class="d-none d-sm-inline-block btn btn-sm btn-info shadow-sm">
        <i class="fas fa-eye fa-sm text-white-50"></i> View Order
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Edit Order Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.orders.update', $order) }}" method="POST" id="orderForm">
                    @csrf
                    @method('PUT')

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id">Customer *</label>
                                <select class="form-control @error('user_id') is-invalid @enderror"
                                        id="user_id" name="user_id" required>
                                    <option value="">Select Customer</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $order->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="store_id">Store *</label>
                                <select class="form-control @error('store_id') is-invalid @enderror"
                                        id="store_id" name="store_id" required>
                                    <option value="">Select Store</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" {{ old('store_id', $order->store_id) == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('store_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Order Status *</label>
                                <select class="form-control @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', $order->status) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_amount">Total Amount *</label>
                                <input type="number" class="form-control @error('total_amount') is-invalid @enderror"
                                       id="total_amount" name="total_amount" 
                                       value="{{ old('total_amount', $order->total_amount) }}" 
                                       min="0" step="0.01" required>
                                @error('total_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Order Items Display (Read-only) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">Order Items</h5>
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
                                                        <small class="text-muted">Store: {{ $orderItem->item->store->name }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>${{ number_format($orderItem->unit_price, 2) }}</td>
                                            <td>{{ $orderItem->quantity }}</td>
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
                                            <td>${{ number_format($orderItem->quantity * $orderItem->unit_price, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">Delivery Information</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="delivery_address">Delivery Address *</label>
                                <textarea class="form-control @error('delivery_address') is-invalid @enderror"
                                          id="delivery_address" name="delivery_address" rows="3" required>{{ old('delivery_address', $order->delivery->address ?? '') }}</textarea>
                                @error('delivery_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="delivery_status">Delivery Status *</label>
                                <select class="form-control @error('delivery_status') is-invalid @enderror"
                                        id="delivery_status" name="delivery_status" required>
                                    <option value="pending" {{ old('delivery_status', $order->delivery->status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="shipped" {{ old('delivery_status', $order->delivery->status ?? '') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ old('delivery_status', $order->delivery->status ?? '') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="failed" {{ old('delivery_status', $order->delivery->status ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
                                </select>
                                @error('delivery_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">Payment Information</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_status">Payment Status *</label>
                                <select class="form-control @error('payment_status') is-invalid @enderror"
                                        id="payment_status" name="payment_status" required>
                                    <option value="pending" {{ old('payment_status', $order->payment->status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="completed" {{ old('payment_status', $order->payment->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="failed" {{ old('payment_status', $order->payment->status ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="refunded" {{ old('payment_status', $order->payment->status ?? '') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                                @error('payment_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Order
                            </button>
                            <a href="{{ route('cms.orders.show', $order) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> View Order
                            </a>
                            <a href="{{ route('cms.orders.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add any necessary JavaScript for the edit form
    document.addEventListener('DOMContentLoaded', function() {
        // You can add any dynamic functionality here if needed
    });
</script>
@endpush