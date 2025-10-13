@extends('cms.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Edit Order #{{ $order->id }}</h1>
                <a href="{{ route('cms.orders.show', $order) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Order
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
                    <form action="{{ route('cms.orders.update', $order) }}" method="POST" id="orderForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="store_id">Store *</label>
                                    <select name="store_id" id="store_id" class="form-control" required>
                                        <option value="">Select Store</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store->id }}" 
                                                {{ $order->store_id == $store->id ? 'selected' : '' }}>
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
                                                {{ $order->user_id == $user->id ? 'selected' : '' }}>
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
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Order Items Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5>Order Items</h5>
                                <div id="storeItemsContainer" class="mb-3">
                                    <label>Available Items from Store:</label>
                                    <select id="itemSelector" class="form-control">
                                        <option value="">Select Item to Add</option>
                                        @foreach($availableItems as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->name }} - ${{ $item->price }} (Stock: {{ $item->stock_quantity }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" id="addItemBtn" class="btn btn-sm btn-success mt-2">
                                        <i class="fas fa-plus"></i> Add Item
                                    </button>
                                </div>

                                <div id="orderItemsContainer">
                                    <!-- Dynamic order items will be added here -->
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
                                    <i class="fas fa-save"></i> Update Order
                                </button>
                                <a href="{{ route('cms.orders.show', $order) }}" class="btn btn-secondary">Cancel</a>
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
    // Store items data
    let storeItems = @json($availableItems->map(function($item) {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
            'stock_quantity' => $item->stock_quantity,
            'color_variants' => $item->color_variants ?? [],
            'size_stock' => $item->size_stock ?? [],
            'available_sizes' => array_keys(array_filter($item->size_stock ?? [])),
            'available_colors' => Object.keys($item->color_variants ?? [])
        ];
    }));
    
    // Order items data - simplified without use() reference
    let orderItems = [];
    
    // Initialize with existing order items
    @foreach($order->orderItems as $index => $orderItem)
        orderItems.push({
            'id': '{{ $index }}',
            'order_item_id': '{{ $orderItem->id }}',
            'item_id': '{{ $orderItem->item_id }}',
            'name': '{{ $orderItem->item->name }}',
            'price': {{ $orderItem->unit_price }},
            'quantity': {{ $orderItem->quantity }},
            'selected_size': '{{ $orderItem->selected_size }}',
            'selected_color': '{{ $orderItem->selected_color }}',
            'selected_brand': '{{ $orderItem->selected_brand ?? '' }}',
            'available_sizes': @json(array_keys(array_filter($orderItem->item->size_stock ?? []))),
            'available_colors': @json(array_keys($orderItem->item->color_variants ?? [])),
            'unique_key': 'existing_{{ $orderItem->id }}'
        });
    @endforeach
    
    let itemCounter = {{ $order->orderItems->count() }};

    // Initialize the order items display
    $(document).ready(function() {
        updateOrderItemsDisplay();
        updateOrderSummary();
    });

    // Add item to order
    $('#addItemBtn').click(function() {
        const itemId = $('#itemSelector').val();
        if (!itemId) {
            alert('Please select an item to add.');
            return;
        }

        const item = storeItems.find(i => i.id == itemId);
        if (!item) {
            alert('Selected item not found.');
            return;
        }

        // Generate unique ID for this order item
        const uniqueKey = 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

        const orderItem = {
            id: itemCounter++,
            order_item_id: null, // This is a new item
            item_id: parseInt(itemId),
            name: item.name,
            price: parseFloat(item.price),
            quantity: 1,
            selected_size: item.available_sizes.length > 0 ? item.available_sizes[0] : '',
            selected_color: item.available_colors.length > 0 ? item.available_colors[0] : '',
            selected_brand: '',
            available_sizes: item.available_sizes,
            available_colors: item.available_colors,
            unique_key: uniqueKey
        };

        orderItems.push(orderItem);
        updateOrderItemsDisplay();
        $('#itemSelector').val('');
    });

    // Update order items display
    function updateOrderItemsDisplay() {
        const container = $('#orderItemsContainer');
        container.empty();

        if (orderItems.length === 0) {
            container.html('<div class="alert alert-info">No items added to order yet.</div>');
            return;
        }

        orderItems.forEach(item => {
            const itemTotal = item.price * item.quantity;
            const itemHtml = `
                <div class="order-item-card" data-unique-key="${item.unique_key}">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <strong>${item.name}</strong>
                            <br>
                            <small class="text-muted">Price: $${item.price.toFixed(2)}</small>
                            ${item.order_item_id ? `<input type="hidden" name="order_items[${item.unique_key}][id]" value="${item.order_item_id}">` : ''}
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
                                <button type="button" class="btn btn-sm btn-danger remove-item-btn" data-unique-key="${item.unique_key}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="order_items[${item.unique_key}][item_id]" value="${item.item_id}">
                    <input type="hidden" name="order_items[${item.unique_key}][selected_brand]" value="${item.selected_brand}">
                    <input type="hidden" name="order_items[${item.unique_key}][_remove]" value="0" class="remove-flag">
                </div>
            `;
            container.append(itemHtml);
        });

        updateOrderSummary();
    }

    // Remove item from order
    $(document).on('click', '.remove-item-btn', function() {
        const uniqueKey = $(this).data('unique-key');
        
        // Find the item and mark it for removal
        const itemIndex = orderItems.findIndex(item => item.unique_key === uniqueKey);
        if (itemIndex !== -1) {
            // If it's an existing item, mark it for removal in the form
            if (orderItems[itemIndex].order_item_id) {
                $(`[data-unique-key="${uniqueKey}"] .remove-flag`).val('1');
                $(`[data-unique-key="${uniqueKey}"]`).addClass('bg-light');
            } else {
                // If it's a new item, just remove it from the array
                orderItems.splice(itemIndex, 1);
            }
            updateOrderItemsDisplay();
        }
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

    // Update order summary
    function updateOrderSummary() {
        let subtotal = 0;
        
        orderItems.forEach(item => {
            // Only include items not marked for removal
            const removeFlag = $(`[data-unique-key="${item.unique_key}"] .remove-flag`);
            if (removeFlag.length === 0 || removeFlag.val() === '0') {
                subtotal += item.price * item.quantity;
            }
        });

        const tax = subtotal * 0.1; // 10% tax for example
        const total = subtotal + tax;

        $('#subtotal').text('$' + subtotal.toFixed(2));
        $('#tax').text('$' + tax.toFixed(2));
        $('#totalAmount').text('$' + total.toFixed(2));
    }

    // Form submission
    $('#orderForm').submit(function(e) {
        e.preventDefault();
        
        const remainingItems = orderItems.filter(item => {
            const removeFlag = $(`[data-unique-key="${item.unique_key}"] .remove-flag`);
            return removeFlag.length === 0 || removeFlag.val() === '0';
        });

        if (remainingItems.length === 0) {
            alert('Please add at least one item to the order.');
            return false;
        }

        // Submit the form
        this.submit();
    });
</script>
@endpush