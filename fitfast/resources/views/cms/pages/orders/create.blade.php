@extends('cms.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Create New Order</h1>
                <a href="{{ route('cms.orders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Information</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('cms.orders.store') }}" method="POST" id="orderForm">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store_id">Store *</label>
                                    <select name="store_id" id="store_id" class="form-control" required>
                                        <option value="">Select Store</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store->id }}" 
                                                {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                                {{ $store->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_id">Customer *</label>
                                    <select name="user_id" id="user_id" class="form-control" required>
                                        <option value="">Select Customer</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" 
                                                {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status *</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="processing" {{ old('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="shipped" {{ old('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="delivered" {{ old('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Order Items Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5>Order Items</h5>
                                <div id="storeItemsContainer" class="mb-3" style="display: none;">
                                    <label>Available Items from Store:</label>
                                    <select id="itemSelector" class="form-control">
                                        <option value="">Select Item to Add</option>
                                    </select>
                                    <button type="button" id="addItemBtn" class="btn btn-sm btn-success mt-2">
                                        <i class="fas fa-plus"></i> Add Item
                                    </button>
                                </div>

                                <div id="orderItemsContainer">
                                    <!-- Dynamic order items will be added here -->
                                </div>

                                <div id="noItemsMessage" class="alert alert-info">
                                    Please select a store first to add items to the order.
                                </div>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Order Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span id="subtotal">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Tax:</span>
                                            <span id="tax">$0.00</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between font-weight-bold">
                                            <span>Total:</span>
                                            <span id="totalAmount">$0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Order
                                </button>
                                <a href="{{ route('cms.orders.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .order-item-card {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f8f9fc;
    }
    .remove-item {
        color: #e74a3b;
        cursor: pointer;
    }
    .remove-item:hover {
        color: #be2617;
    }
</style>
@endpush

@push('scripts')
<script>
    let storeItems = [];
    let orderItems = [];
    let itemCounter = 0;

    // Load store items when store is selected
    $('#store_id').change(function() {
        const storeId = $(this).val();
        
        if (storeId) {
            $('#noItemsMessage').hide();
            $('#storeItemsContainer').show();
            
            // Load items from the selected store
            $.get(`/cms/stores/${storeId}/items`, function(data) {
                storeItems = data;
                updateItemSelector();
            });
        } else {
            $('#storeItemsContainer').hide();
            $('#noItemsMessage').show();
            orderItems = [];
            updateOrderItemsDisplay();
        }
    });

    // Update item selector dropdown
    function updateItemSelector() {
        const selector = $('#itemSelector');
        selector.empty().append('<option value="">Select Item to Add</option>');
        
        storeItems.forEach(item => {
            selector.append(`<option value="${item.id}">${item.name} - $${item.price} (Stock: ${item.stock_quantity})</option>`);
        });
    }

    // Add item to order
    $('#addItemBtn').click(function() {
        const itemId = $('#itemSelector').val();
        if (!itemId) return;

        const item = storeItems.find(i => i.id == itemId);
        if (!item) return;

        // Generate a unique ID for this order item
        const orderItemId = itemCounter++;

        const orderItem = {
            id: orderItemId,
            item_id: item.id,
            name: item.name,
            price: item.price,
            quantity: 1,
            selected_size: item.available_sizes.length > 0 ? item.available_sizes[0] : '',
            selected_color: item.available_colors.length > 0 ? item.available_colors[0] : '',
            selected_brand: '',
            available_sizes: item.available_sizes,
            available_colors: item.available_colors,
            // Add unique identifier for this specific combination
            unique_key: `${item.id}_${orderItemId}`
        };

        orderItems.push(orderItem);
        updateOrderItemsDisplay();
        $('#itemSelector').val('');
        
        // Re-calculate available stock for all items
        updateAvailableStock();
    });

    // Update order items display
    function updateOrderItemsDisplay() {
        const container = $('#orderItemsContainer');
        container.empty();

        if (orderItems.length === 0) {
            container.html('<div class="alert alert-info">No items added to order yet.</div>');
            updateOrderSummary();
            return;
        }

        orderItems.forEach(item => {
            const itemTotal = item.price * item.quantity;
            const itemHtml = `
                <div class="order-item-card" data-item-id="${item.id}" data-unique-key="${item.unique_key}">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <strong>${item.name}</strong>
                            <br>
                            <small class="text-muted">Price: $${item.price}</small>
                            <br>
                            <small class="text-info">Available Stock: <span class="available-stock" data-item-id="${item.item_id}" data-size="${item.selected_size}" data-color="${item.selected_color}">Calculating...</span></small>
                        </div>
                        <div class="col-md-2">
                            <label class="small">Quantity</label>
                            <input type="number" name="order_items[${item.unique_key}][quantity]" 
                                   value="${item.quantity}" min="1" class="form-control form-control-sm item-quantity"
                                   data-unique-key="${item.unique_key}">
                        </div>
                        <div class="col-md-2">
                            <label class="small">Size</label>
                            <select name="order_items[${item.unique_key}][selected_size]" class="form-control form-control-sm item-size"
                                    data-unique-key="${item.unique_key}">
                                ${item.available_sizes.map(size => 
                                    `<option value="${size}" ${item.selected_size === size ? 'selected' : ''}>${size}</option>`
                                ).join('')}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small">Color</label>
                            <select name="order_items[${item.unique_key}][selected_color]" class="form-control form-control-sm item-color"
                                    data-unique-key="${item.unique_key}">
                                ${item.available_colors.map(color => 
                                    `<option value="${color}" ${item.selected_color === color ? 'selected' : ''}>${color}</option>`
                                ).join('')}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="small">Total</label>
                            <div class="item-total font-weight-bold">$${itemTotal.toFixed(2)}</div>
                        </div>
                        <div class="col-md-1">
                            <label class="small">&nbsp;</label>
                            <div>
                                <i class="fas fa-times remove-item text-danger" data-unique-key="${item.unique_key}" style="cursor: pointer;"></i>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="order_items[${item.unique_key}][item_id]" value="${item.item_id}">
                    <input type="hidden" name="order_items[${item.unique_key}][selected_brand]" value="${item.selected_brand}">
                </div>
            `;
            container.append(itemHtml);
        });

        updateOrderSummary();
        updateAvailableStock();
    }

    // Remove item from order
    $(document).on('click', '.remove-item', function() {
        const uniqueKey = $(this).data('unique-key');
        orderItems = orderItems.filter(item => item.unique_key !== uniqueKey);
        updateOrderItemsDisplay();
    });

    // Update quantity
    $(document).on('change', '.item-quantity', function() {
        const uniqueKey = $(this).data('unique-key');
        const quantity = parseInt($(this).val()) || 1;
        
        const item = orderItems.find(i => i.unique_key === uniqueKey);
        if (item) {
            item.quantity = quantity;
            updateOrderItemsDisplay();
        }
    });

    // Update size
    $(document).on('change', '.item-size', function() {
        const uniqueKey = $(this).data('unique-key');
        const size = $(this).val();
        
        const item = orderItems.find(i => i.unique_key === uniqueKey);
        if (item) {
            item.selected_size = size;
            updateOrderItemsDisplay();
        }
    });

    // Update color
    $(document).on('change', '.item-color', function() {
        const uniqueKey = $(this).data('unique-key');
        const color = $(this).val();
        
        const item = orderItems.find(i => i.unique_key === uniqueKey);
        if (item) {
            item.selected_color = color;
            updateOrderItemsDisplay();
        }
    });

    // Calculate available stock considering all order items
    function updateAvailableStock() {
        orderItems.forEach(item => {
            const originalItem = storeItems.find(i => i.id == item.item_id);
            if (!originalItem) return;

            // Calculate total quantity of this item+size+color combination in the current order
            const totalOrderedForThisCombination = orderItems
                .filter(orderItem => 
                    orderItem.item_id === item.item_id && 
                    orderItem.selected_size === item.selected_size && 
                    orderItem.selected_color === item.selected_color
                )
                .reduce((sum, orderItem) => sum + orderItem.quantity, 0);

            // Get available stock from the original item
            const sizeStock = originalItem.size_stock[item.selected_size] || 0;
            const colorStock = originalItem.color_variants[item.selected_color]?.stock || 0;
            
            // Available stock is the minimum of size stock and color stock
            const availableStock = Math.min(sizeStock, colorStock);
            
            // Remaining available stock after considering all orders for this combination
            const remainingStock = Math.max(0, availableStock - totalOrderedForThisCombination + item.quantity);
            
            // Update the display
            $(`.available-stock[data-item-id="${item.item_id}"][data-size="${item.selected_size}"][data-color="${item.selected_color}"]`)
                .text(remainingStock)
                .toggleClass('text-danger', remainingStock < item.quantity)
                .toggleClass('text-success', remainingStock >= item.quantity);
        });
    }

    // Update order summary
    function updateOrderSummary() {
        let subtotal = 0;
        
        orderItems.forEach(item => {
            subtotal += item.price * item.quantity;
        });

        const tax = subtotal * 0.1; // 10% tax for example
        const total = subtotal + tax;

        $('#subtotal').text('$' + subtotal.toFixed(2));
        $('#tax').text('$' + tax.toFixed(2));
        $('#totalAmount').text('$' + total.toFixed(2));
    }

    // Form submission with stock validation
    $('#orderForm').submit(function(e) {
        if (orderItems.length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the order.');
            return false;
        }

        // Validate stock for all items
        let hasStockIssues = false;
        let errorMessages = [];

        orderItems.forEach(item => {
            const originalItem = storeItems.find(i => i.id == item.item_id);
            if (!originalItem) return;

            const sizeStock = originalItem.size_stock[item.selected_size] || 0;
            const colorStock = originalItem.color_variants[item.selected_color]?.stock || 0;
            const availableStock = Math.min(sizeStock, colorStock);

            // Calculate total ordered for this combination
            const totalOrderedForThisCombination = orderItems
                .filter(orderItem => 
                    orderItem.item_id === item.item_id && 
                    orderItem.selected_size === item.selected_size && 
                    orderItem.selected_color === item.selected_color
                )
                .reduce((sum, orderItem) => sum + orderItem.quantity, 0);

            if (totalOrderedForThisCombination > availableStock) {
                hasStockIssues = true;
                errorMessages.push(
                    `"${item.name}" in ${item.selected_size}/${item.selected_color}: ` +
                    `Requested ${totalOrderedForThisCombination}, but only ${availableStock} available.`
                );
            }
        });

        if (hasStockIssues) {
            e.preventDefault();
            alert('Stock issues found:\n\n' + errorMessages.join('\n'));
            return false;
        }

        return true;
    });
</script>
@endpush