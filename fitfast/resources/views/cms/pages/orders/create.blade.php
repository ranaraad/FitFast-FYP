@extends('cms.layouts.app')

@section('page-title', 'Order Management')
@section('page-subtitle', 'Manage user orders')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Order</h1>
    <a href="{{ route('cms.orders.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Orders
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.orders.store') }}" method="POST" id="orderForm">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id">Customer *</label>
                                <select class="form-control @error('user_id') is-invalid @enderror"
                                        id="user_id" name="user_id" required>
                                    <option value="">Select Customer</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
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
                                        id="store_id" name="store_id" required onchange="loadStoreCarts()">
                                    <option value="">Select Store</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
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

                    <!-- Cart Selection (Only shows after store selection) -->
                    <div class="row mb-4" id="cart-section" style="display: none;">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="cart_id">Select Cart *</label>
                                <select class="form-control @error('cart_id') is-invalid @enderror"
                                        id="cart_id" name="cart_id" required onchange="loadCartItems()">
                                    <option value="">Select a Cart</option>
                                </select>
                                @error('cart_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Carts from the selected store will appear here</small>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items Display -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">Order Items</h5>
                            <div id="order-items-container" class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Select a store and cart to view items</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Order Summary
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <strong>Total Items:</strong>
                                                </div>
                                                <div class="col-6 text-right">
                                                    <span id="total-items" class="h5 mb-0 font-weight-bold text-gray-800">0</span>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6">
                                                    <strong>Order Total:</strong>
                                                </div>
                                                <div class="col-6 text-right">
                                                    <span id="order-total" class="h5 mb-0 font-weight-bold text-success">$0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">Payment Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_method">Payment Method *</label>
                                        <select class="form-control @error('payment_method') is-invalid @enderror"
                                                id="payment_method" name="payment_method" required onchange="toggleCardFields()">
                                            <option value="">Select Payment Method</option>
                                            <option value="cash">Cash</option>
                                            <option value="card">Credit/Debit Card</option>
                                        </select>
                                        @error('payment_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Card Fields (Hidden by default) -->
                            <div id="card-fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="card_number">Card Number *</label>
                                            <input type="text" class="form-control"
                                                   id="card_number" name="card_number"
                                                   placeholder="1234 5678 9012 3456"
                                                   maxlength="19">
                                            <small class="form-text text-muted">Enter 16-digit card number</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="expiry_date">Expiry Date *</label>
                                            <input type="text" class="form-control"
                                                   id="expiry_date" name="expiry_date"
                                                   placeholder="MM/YY"
                                                   maxlength="5">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="cvv">CVV *</label>
                                            <input type="text" class="form-control"
                                                   id="cvv" name="cvv"
                                                   placeholder="123"
                                                   maxlength="3">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="card_holder">Card Holder Name *</label>
                                            <input type="text" class="form-control"
                                                   id="card_holder" name="card_holder"
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

                    <!-- Delivery Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">Delivery Information</h5>
                            <div class="form-group">
                                <label for="delivery_address">Delivery Address *</label>
                                <textarea class="form-control @error('delivery_address') is-invalid @enderror"
                                          id="delivery_address" name="delivery_address" rows="3" required
                                          placeholder="Enter full delivery address including street, city, and zip code">{{ old('delivery_address') }}</textarea>
                                @error('delivery_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                <i class="fas fa-save"></i> Create Order
                            </button>
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
function loadStoreCarts() {
    const storeId = document.getElementById('store_id').value;
    const userId = document.getElementById('user_id').value;
    const cartSelect = document.getElementById('cart_id');
    const cartSection = document.getElementById('cart-section');
    const submitBtn = document.getElementById('submit-btn');

    cartSelect.innerHTML = '<option value="">Select a Cart</option>';
    submitBtn.disabled = true;

    if (storeId && userId) {
        cartSection.style.display = 'block';

        // Load user's carts for this store via AJAX
        fetch(`/cms/carts/user/${userId}?store_id=${storeId}`)
            .then(response => response.json())
            .then(carts => {
                if (carts.length === 0) {
                    cartSelect.innerHTML = '<option value="">No carts available for this store</option>';
                } else {
                    carts.forEach(cart => {
                        const option = document.createElement('option');
                        option.value = cart.id;
                        option.textContent = `Cart #${cart.id} - ${cart.total_items} items - $${cart.formatted_total}`;
                        cartSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading carts:', error);
                cartSelect.innerHTML = '<option value="">Error loading carts</option>';
            });
    } else {
        cartSection.style.display = 'none';
        document.getElementById('order-items-container').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <p class="text-muted">Select a store and cart to view items</p>
            </div>
        `;
        updateOrderSummary();
    }
}

function toggleCardFields() {
    const paymentMethod = document.getElementById('payment_method').value;
    const cardFields = document.getElementById('card-fields');

    if (paymentMethod === 'card') {
        cardFields.style.display = 'block';
        // Make card fields required
        document.getElementById('card_number').required = true;
        document.getElementById('expiry_date').required = true;
        document.getElementById('cvv').required = true;
        document.getElementById('card_holder').required = true;
    } else {
        cardFields.style.display = 'none';
        // Remove required from card fields
        document.getElementById('card_number').required = false;
        document.getElementById('expiry_date').required = false;
        document.getElementById('cvv').required = false;
        document.getElementById('card_holder').required = false;
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

// Rest of your existing functions (loadCartItems, addHiddenItemInputs, updateOrderSummary) remain the same
function loadCartItems() {
    const cartId = document.getElementById('cart_id').value;
    const submitBtn = document.getElementById('submit-btn');
    const container = document.getElementById('order-items-container');

    console.log('Loading cart items for cart ID:', cartId);

    if (cartId) {
        // Show loading state
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading cart items...</p>
            </div>
        `;

        fetch(`/cms/orders/cart/${cartId}/items`)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(items => {
                console.log('Successfully loaded items:', items);

                if (!items || items.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <p class="text-warning">No items found in this cart</p>
                        </div>
                    `;
                    submitBtn.disabled = true;
                    return;
                }

                // Display items in a table
                let itemsHTML = `
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
                `;

                let totalItems = 0;
                let orderTotal = 0;

                items.forEach(item => {
                    const itemTotal = item.quantity * item.unit_price;
                    totalItems += item.quantity;
                    orderTotal += itemTotal;

                    itemsHTML += `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-tshirt fa-2x text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">${item.name}</h6>
                                        <small class="text-muted">Store: ${item.store_name}</small>
                                    </div>
                                </div>
                            </td>
                            <td>$${item.unit_price.toFixed(2)}</td>
                            <td>
                                <span class="badge badge-secondary">${item.quantity}</span>
                            </td>
                            <td>
                                ${item.selected_size ? `<span class="badge badge-info">${item.selected_size}</span>` : '<span class="text-muted">N/A</span>'}
                            </td>
                            <td>
                                <span class="badge" style="background-color: ${item.selected_color}; color: white;">
                                    ${item.selected_color}
                                </span>
                            </td>
                            <td>
                                <strong>$${itemTotal.toFixed(2)}</strong>
                            </td>
                        </tr>
                    `;
                });

                itemsHTML += `
                            </tbody>
                        </table>
                    </div>
                `;

                container.innerHTML = itemsHTML;

                // Update summary
                document.getElementById('total-items').textContent = totalItems;
                document.getElementById('order-total').textContent = `$${orderTotal.toFixed(2)}`;

                // Enable submit button
                submitBtn.disabled = false;

                // Add hidden inputs for items
                addHiddenItemInputs(items);
            })
            .catch(error => {
                console.error('Error loading cart items:', error);
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <p class="text-danger">Error loading cart items</p>
                        <small class="text-muted">${error.message}</small>
                    </div>
                `;
                submitBtn.disabled = true;
            });
    } else {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <p class="text-muted">Select a cart to view items</p>
            </div>
        `;
        updateOrderSummary();
        submitBtn.disabled = true;
    }
}

function addHiddenItemInputs(items) {
    // Remove any existing hidden inputs
    document.querySelectorAll('input[name^="items"]').forEach(input => input.remove());

    // Add hidden inputs for each item
    const form = document.getElementById('orderForm');

    items.forEach((item, index) => {
        const fields = [
            { name: `items[${index}][item_id]`, value: item.item_id },
            { name: `items[${index}][quantity]`, value: item.quantity },
            { name: `items[${index}][selected_size]`, value: item.selected_size || '' },
            { name: `items[${index}][selected_color]`, value: item.selected_color },
            { name: `items[${index}][unit_price]`, value: item.unit_price }
        ];

        fields.forEach(field => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = field.name;
            input.value = field.value;
            form.appendChild(input);
        });
    });
}

function updateOrderSummary() {
    document.getElementById('total-items').textContent = '0';
    document.getElementById('order-total').textContent = '$0.00';
}

// Form validation
document.getElementById('orderForm').addEventListener('submit', function(e) {
    const cartId = document.getElementById('cart_id').value;
    const paymentMethod = document.getElementById('payment_method').value;

    if (!cartId) {
        e.preventDefault();
        Swal.fire('Error', 'Please select a cart to create order from.', 'error');
        return;
    }

    if (paymentMethod === 'card') {
        // Validate card fields
        const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
        const expiryDate = document.getElementById('expiry_date').value;
        const cvv = document.getElementById('cvv').value;
        const cardHolder = document.getElementById('card_holder').value;

        if (cardNumber.length !== 16) {
            e.preventDefault();
            Swal.fire('Error', 'Please enter a valid 16-digit card number.', 'error');
            return;
        }

        if (!expiryDate.match(/^\d{2}\/\d{2}$/)) {
            e.preventDefault();
            Swal.fire('Error', 'Please enter a valid expiry date (MM/YY).', 'error');
            return;
        }

        if (cvv.length !== 3) {
            e.preventDefault();
            Swal.fire('Error', 'Please enter a valid 3-digit CVV.', 'error');
            return;
        }

        if (!cardHolder.trim()) {
            e.preventDefault();
            Swal.fire('Error', 'Please enter card holder name.', 'error');
            return;
        }
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for user change to reload carts
    document.getElementById('user_id').addEventListener('change', function() {
        if (document.getElementById('store_id').value) {
            loadStoreCarts();
        }
    });
});
</script>
@endpush
