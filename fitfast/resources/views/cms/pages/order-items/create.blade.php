@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Order</h1>
    <a href="{{ route('cms.orders.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Orders
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.orders.store') }}" method="POST" id="orderForm">
                    @csrf
                    
                    <!-- Basic Order Information -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="user_id">Customer *</label>
                                <select name="user_id" id="user_id" class="form-control select2" required>
                                    <option value="">Select Customer</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="store_id">Store *</label>
                                <select name="store_id" id="store_id" class="form-control select2" required>
                                    <option value="">Select Store</option>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status *</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="shipped" {{ old('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ old('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Items Selection Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3 text-gray-800">Order Items</h5>
                            
                            <!-- Store Items Selection -->
                            <div class="form-group" id="storeItemsSection" style="display: none;">
                                <label>Add Items from Store</label>
                                <select id="storeItems" class="form-control select2">
                                    <option value="">Select an item to add...</option>
                                </select>
                                <small class="form-text text-muted">Select store first to see available items</small>
                            </div>

                            <!-- Items Table -->
                            <div class="table-responsive mt-3">
                                <table class="table table-bordered" id="itemsTable" style="display: none;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Item</th>
                                            <th>Size</th>
                                            <th>Color</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-right">Unit Price</th>
                                            <th class="text-right">Total</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        <!-- Items will be added here dynamically -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td colspan="4" class="text-right">Order Total:</td>
                                            <td colspan="2" class="text-right text-success" id="orderTotal">$0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div id="noItemsMessage" class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">No items added yet. Select a store and add items above.</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-check-circle mr-2"></i> Create Order
                        </button>
                        <a href="{{ route('cms.orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let storeItems = [];
    let selectedItems = [];

    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        // When store is selected, load its items
        $('#store_id').change(function() {
            const storeId = $(this).val();
            if (storeId) {
                loadStoreItems(storeId);
                $('#storeItemsSection').show();
            } else {
                $('#storeItemsSection').hide();
                $('#storeItems').empty().append('<option value="">Select an item to add...</option>');
            }
        });

        // When item is selected from store items
        $('#storeItems').change(function() {
            const itemId = $(this).val();
            if (itemId) {
                addItemToOrder(itemId);
                $(this).val('').trigger('change');
            }
        });

        // Update order total
        function updateOrderTotal() {
            let total = 0;
            selectedItems.forEach(item => {
                total += item.quantity * item.unit_price;
            });
            $('#orderTotal').text('$' + total.toFixed(2));
            
            // Enable/disable submit button
            $('#submitBtn').prop('disabled', selectedItems.length === 0);
        }

        // Load store items via AJAX
        function loadStoreItems(storeId) {
            $('#storeItems').html('<option value="">Loading items...</option>');
            
            $.get(`/cms/orders/store-items/${storeId}`, function(data) {
                storeItems = data;
                $('#storeItems').empty().append('<option value="">Select an item to add...</option>');
                
                if (data.length === 0) {
                    $('#storeItems').append('<option value="" disabled>No items available in this store</option>');
                } else {
                    data.forEach(item => {
                        $('#storeItems').append(
                            `<option value="${item.id}">${item.name} - $${item.price} (Stock: ${item.stock_quantity})</option>`
                        );
                    });
                }
            }).fail(function() {
                $('#storeItems').empty().append('<option value="" disabled>Error loading items</option>');
            });
        }

        // Add item to order - SUPPORTS MULTIPLE QUANTITIES OF SAME ITEM
        function addItemToOrder(itemId) {
            const item = storeItems.find(i => i.id == itemId);
            if (!item) return;

            // Check if item with same size and color already exists
            const existingItemIndex = selectedItems.findIndex(i => 
                i.item_id == itemId && 
                i.selected_size === item.available_sizes[0] && 
                i.selected_color === Object.keys(item.available_colors)[0]
            );

            if (existingItemIndex !== -1) {
                // Increase quantity of existing item
                const existingItem = selectedItems[existingItemIndex];
                const newQuantity = existingItem.quantity + 1;
                
                // Check stock availability
                if (newQuantity > getMaxAvailableQuantity(item, existingItem.selected_size, existingItem.selected_color)) {
                    showStockAlert(item, existingItem.selected_size, existingItem.selected_color);
                    return;
                }
                
                selectedItems[existingItemIndex].quantity = newQuantity;
            } else {
                // Add new item
                const newItem = {
                    item_id: item.id,
                    name: item.name,
                    unit_price: parseFloat(item.price),
                    quantity: 1,
                    selected_size: item.available_sizes.length > 0 ? item.available_sizes[0] : '',
                    selected_color: Object.keys(item.available_colors).length > 0 ? Object.keys(item.available_colors)[0] : '',
                    available_sizes: item.available_sizes,
                    available_colors: item.available_colors,
                    max_quantity: getMaxAvailableQuantity(item, item.available_sizes[0], Object.keys(item.available_colors)[0])
                };

                selectedItems.push(newItem);
            }

            renderItemsTable();
            updateOrderTotal();
        }

        // Get maximum available quantity for an item variant
        function getMaxAvailableQuantity(item, size, color) {
            // For now, we'll use the overall stock quantity
            // In a real system, you'd check size_stock and color_variants
            return item.stock_quantity;
        }

        // Show stock alert
        function showStockAlert(item, size, color) {
            const maxQty = getMaxAvailableQuantity(item, size, color);
            Swal.fire({
                icon: 'warning',
                title: 'Insufficient Stock',
                html: `Only <strong>${maxQty}</strong> units available for:<br>
                      <strong>${item.name}</strong> (${size}, ${color})`,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        }

        // Remove item from order
        function removeItem(index) {
            Swal.fire({
                title: 'Remove Item?',
                text: "This item will be removed from the order.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    selectedItems.splice(index, 1);
                    renderItemsTable();
                    updateOrderTotal();
                }
            });
        }

        // Update item quantity with stock validation
        function updateQuantity(index, quantity) {
            if (quantity < 1) quantity = 1;
            
            const item = selectedItems[index];
            const storeItem = storeItems.find(i => i.id == item.item_id);
            
            if (storeItem) {
                const maxQty = getMaxAvailableQuantity(storeItem, item.selected_size, item.selected_color);
                if (quantity > maxQty) {
                    showStockAlert(storeItem, item.selected_size, item.selected_color);
                    quantity = maxQty;
                }
            }
            
            selectedItems[index].quantity = parseInt(quantity);
            updateOrderTotal();
            updateRowTotal(index);
        }

        // Update item size
        function updateSize(index, size) {
            selectedItems[index].selected_size = size;
            
            // Update max quantity when size changes
            const item = selectedItems[index];
            const storeItem = storeItems.find(i => i.id == item.item_id);
            if (storeItem) {
                item.max_quantity = getMaxAvailableQuantity(storeItem, size, item.selected_color);
                if (item.quantity > item.max_quantity) {
                    item.quantity = item.max_quantity;
                    renderItemsTable();
                    updateOrderTotal();
                }
            }
        }

        // Update item color
        function updateColor(index, color) {
            selectedItems[index].selected_color = color;
            
            // Update max quantity when color changes
            const item = selectedItems[index];
            const storeItem = storeItems.find(i => i.id == item.item_id);
            if (storeItem) {
                item.max_quantity = getMaxAvailableQuantity(storeItem, item.selected_size, color);
                if (item.quantity > item.max_quantity) {
                    item.quantity = item.max_quantity;
                    renderItemsTable();
                    updateOrderTotal();
                }
            }
        }

        // Update row total
        function updateRowTotal(index) {
            const item = selectedItems[index];
            const total = item.quantity * item.unit_price;
            $(`#rowTotal-${index}`).text('$' + total.toFixed(2));
        }

        // Render items table
        function renderItemsTable() {
            const tbody = $('#itemsTableBody');
            tbody.empty();

            if (selectedItems.length === 0) {
                $('#noItemsMessage').show();
                $('#itemsTable').hide();
                return;
            }

            $('#noItemsMessage').hide();
            $('#itemsTable').show();

            selectedItems.forEach((item, index) => {
                const rowTotal = item.quantity * item.unit_price;
                const storeItem = storeItems.find(i => i.id == item.item_id);
                const maxQty = storeItem ? getMaxAvailableQuantity(storeItem, item.selected_size, item.selected_color) : item.quantity;
                
                const row = `
                    <tr>
                        <td class="align-middle">
                            <strong>${item.name}</strong>
                            <br>
                            <small class="text-muted">Stock: ${maxQty} available</small>
                        </td>
                        <td class="align-middle">
                            <select name="items[${index}][selected_size]" 
                                    class="form-control form-control-sm" 
                                    onchange="updateSize(${index}, this.value)">
                                ${item.available_sizes.map(size => 
                                    `<option value="${size}" ${item.selected_size === size ? 'selected' : ''}>${size}</option>`
                                ).join('')}
                            </select>
                        </td>
                        <td class="align-middle">
                            <select name="items[${index}][selected_color]" 
                                    class="form-control form-control-sm" 
                                    onchange="updateColor(${index}, this.value)">
                                ${Object.entries(item.available_colors).map(([key, value]) => 
                                    `<option value="${key}" ${item.selected_color === key ? 'selected' : ''}>${value}</option>`
                                ).join('')}
                            </select>
                        </td>
                        <td class="align-middle text-center">
                            <div class="input-group input-group-sm" style="max-width: 120px; margin: 0 auto;">
                                <div class="input-group-prepend">
                                    <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(${index}, ${item.quantity - 1})">-</button>
                                </div>
                                <input type="number" 
                                       name="items[${index}][quantity]" 
                                       class="form-control text-center" 
                                       min="1" 
                                       max="${maxQty}"
                                       value="${item.quantity}" 
                                       onchange="updateQuantity(${index}, this.value)"
                                       onblur="updateQuantity(${index}, this.value)">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(${index}, ${item.quantity + 1})">+</button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Max: ${maxQty}</small>
                        </td>
                        <td class="align-middle text-right font-weight-bold">
                            $${item.unit_price.toFixed(2)}
                        </td>
                        <td class="align-middle text-right font-weight-bold text-success">
                            <span id="rowTotal-${index}">$${rowTotal.toFixed(2)}</span>
                        </td>
                        <td class="align-middle text-center">
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });

            // Update hidden inputs for form submission
            updateHiddenInputs();
        }

        // Update hidden inputs for form submission
        function updateHiddenInputs() {
            $('#orderForm').find('input[name^="items"]').remove();
            selectedItems.forEach((item, index) => {
                $('#orderForm').append(
                    `<input type="hidden" name="items[${index}][item_id]" value="${item.item_id}">` +
                    `<input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">` +
                    `<input type="hidden" name="items[${index}][selected_size]" value="${item.selected_size}">` +
                    `<input type="hidden" name="items[${index}][selected_color]" value="${item.selected_color}">` +
                    `<input type="hidden" name="items[${index}][selected_brand]" value="">`
                );
            });
        }

        // Make functions global for inline event handlers
        window.removeItem = removeItem;
        window.updateQuantity = updateQuantity;
        window.updateSize = updateSize;
        window.updateColor = updateColor;
    });
</script>
@endpush