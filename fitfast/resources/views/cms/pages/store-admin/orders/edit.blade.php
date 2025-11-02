@extends('cms.layouts.store-admin-app')

@section('page-title', 'Edit Order')
@section('page-subtitle', 'Update order information')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Order - #{{ $order->id }}</h1>
    <a href="{{ route('store-admin.orders.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Orders
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
                <form action="{{ route('store-admin.orders.update', $order) }}" method="POST" id="orderForm">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id">Customer *</label>
                                <select class="form-control @error('user_id') is-invalid @enderror"
                                        id="user_id" name="user_id" required>
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status *</label>
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

                    <div class="form-group">
                        <label for="delivery_address">Delivery Address *</label>
                        <textarea class="form-control @error('delivery_address') is-invalid @enderror"
                                  id="delivery_address" name="delivery_address" rows="3" required>{{ old('delivery_address', $order->delivery->address ?? '') }}</textarea>
                        @error('delivery_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_method">Payment Method</label>
                                <select class="form-control @error('payment_method') is-invalid @enderror"
                                        id="payment_method" name="payment_method" disabled>
                                    <option value="cash" {{ old('payment_method', $order->getPaymentMethodType()) == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="card" {{ old('payment_method', $order->getPaymentMethodType()) == 'card' ? 'selected' : '' }}>Card</option>
                                </select>
                                <small class="form-text text-muted">
                                    Payment method cannot be changed for existing orders.
                                </small>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information Display (Read-only) -->
                    @if($order->payment)
                    <div class="card border-left-info shadow mb-4">
                        <div class="card-header py-3 bg-info text-white">
                            <h6 class="m-0 font-weight-bold">Payment Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Payment Status:</strong>
                                    <span class="badge badge-{{ $order->payment->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($order->payment->status) }}
                                    </span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Amount:</strong> ${{ number_format($order->payment->amount, 2) }}
                                </div>
                            </div>
                            @if($order->payment->transaction_id)
                            <div class="row mt-2">
                                <div class="col-12">
                                    <strong>Transaction ID:</strong> {{ $order->payment->transaction_id }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Order
                            </button>
                            <a href="{{ route('store-admin.orders.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <a href="{{ route('store-admin.orders.show', $order) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> View Order
                            </a>
                            @if($order->canBeCancelled())
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Delete Order
                            </button>
                            @endif
                        </div>
                    </div>
                </form>

                @if($order->canBeCancelled())
                <form id="delete-form" action="{{ route('store-admin.orders.destroy', $order) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush
