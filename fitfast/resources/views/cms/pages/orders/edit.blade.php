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
                            <div class="card border-left-primary">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="payment_method">Payment Method *</label>
                                                <select class="form-control @error('payment_method') is-invalid @enderror"
                                                        id="payment_method" name="payment_method" required onchange="toggleCardFields()">
                                                    <option value="">Select Payment Method</option>
                                                    <option value="cash" {{ old('payment_method', $order->payment && $order->payment->paymentMethod ? $order->payment->paymentMethod->type : '') == 'cash' ? 'selected' : '' }}>Cash</option>
                                                    <option value="card" {{ old('payment_method', $order->payment && $order->payment->paymentMethod ? $order->payment->paymentMethod->type : '') == 'card' ? 'selected' : '' }}>Credit/Debit Card</option>
                                                </select>
                                                @error('payment_method')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Fields (Hidden by default) -->
                                    <div id="card-fields" style="{{ (old('payment_method') ?: ($order->payment && $order->payment->paymentMethod ? $order->payment->paymentMethod->type : '')) == 'card' ? '' : 'display: none;' }}">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="card_number">Card Number</label>
                                                    <input type="text" class="form-control" 
                                                           id="card_number" name="card_number" 
                                                           value="{{ old('card_number') }}"
                                                           placeholder="1234 5678 9012 3456"
                                                           maxlength="19">
                                                    <small class="form-text text-muted">Enter 16-digit card number</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="expiry_date">Expiry Date</label>
                                                    <input type="text" class="form-control" 
                                                           id="expiry_date" name="expiry_date" 
                                                           value="{{ old('expiry_date') }}"
                                                           placeholder="MM/YY"
                                                           maxlength="5">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="cvv">CVV</label>
                                                    <input type="text" class="form-control" 
                                                           id="cvv" name="cvv" 
                                                           value="{{ old('cvv') }}"
                                                           placeholder="123"
                                                           maxlength="3">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="card_holder">Card Holder Name</label>
                                                    <input type="text" class="form-control" 
                                                           id="card_holder" name="card_holder" 
                                                           value="{{ old('card_holder') }}"
                                                           placeholder="John Doe">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Demo Only:</strong> This is a mock payment form. No real payments will be processed.
                                        </div>
                                    </div>
                                </div>
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
function toggleCardFields() {
    const paymentMethod = document.getElementById('payment_method').value;
    const cardFields = document.getElementById('card-fields');
    
    if (paymentMethod === 'card') {
        cardFields.style.display = 'block';
    } else {
        cardFields.style.display = 'none';
    }
}

// Format card number input
document.getElementById('card_number')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = '';
    
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formattedValue += ' ';
        }
        formattedValue += value[i];
    }
    
    e.target.value = formattedValue.substring(0, 19);
});

// Format expiry date input
document.getElementById('expiry_date')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\//g, '').replace(/[^0-9]/gi, '');
    
    if (value.length >= 2) {
        e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
    } else {
        e.target.value = value;
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleCardFields();
});
</script>
@endpush