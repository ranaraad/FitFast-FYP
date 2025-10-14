@extends('cms.layouts.app')

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
                                        id="user_id" name="user_id" required onchange="loadUserCarts()">
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
                                        id="store_id" name="store_id" required>
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

                    <!-- Cart Selection -->
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
                                <small class="form-text text-muted">Select a cart to create order from</small>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items Display -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">Order Items</h5>
                            <div id="order-items-container" class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Select a cart to view items</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="m-0 font-weight-bold text-primary">Order Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <strong>Total Items:</strong>
                                        </div>
                                        <div class="col-6 text-right">
                                            <span id="total-items">0</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>Order Total:</strong>
                                        </div>
                                        <div class="col-6 text-right">
                                            <strong id="order-total">$0.00</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment & Delivery -->
                    <div class="row mb-4">
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="delivery_address">Delivery Address *</label>
                                <textarea class="form-control @error('delivery_address') is-invalid @enderror"
                                          id="delivery_address" name="delivery_address" rows="3" required
                                          placeholder="Enter full delivery address">{{ old('delivery_address') }}</textarea>
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
function loadUserCarts() {
    const userId = document.getElementById('user_id').value;
    const cartSelect = document.getElementById('cart_id');
    const cartSection = document.getElementById('cart-section');
    const submitBtn = document.getElementById('submit-btn');

    cartSelect.innerHTML = '<option value="">Select a Cart</option>';
    submitBtn.disabled = true;
    
    if (userId) {
        cartSection.style.display = 'block';
        
        // Load user's carts via AJAX
        fetch(`/cms/carts/user/${userId}`)
            .then(response => response.json())
            .then(carts => {
                if (carts.length === 0) {
                    cartSelect.innerHTML = '<option value="">No carts available for this user</option>';
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
                <p class="text-muted">Select a cart to view items</p>
            </div>
        `;
        updateOrderSummary();
    }
}

function loadCartItems() {
    const cartId = document.getElementById('cart_id').value;
    const submitBtn = document.getElementById('submit-btn');
    const container = document.getElementById('order-items-container');
    
    if (cartId) {
        fetch(`/cms/orders/cart/${cartId}/items`)
            .then(response => response.json())
            .then(items => {
                if (items.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <p class="text-warning">No items found in this cart</p>
                        </div>
                    `;
                    submitBtn.disabled = true;
                } else {
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
                }
            })
            .catch(error => {
                console.error('Error loading cart items:', error);
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <p class="text-danger">Error loading cart items</p>
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
    
    if (!cartId) {
        e.preventDefault();
        Swal.fire('Error', 'Please select a cart to create order from.', 'error');
        return;
    }
});
</script>
@endpush