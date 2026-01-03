@extends('cms.layouts.app')

@section('page-title', 'Carts Management')
@section('page-subtitle', 'Manage system carts')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Cart</h1>
    <a href="{{ route('cms.carts.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Carts
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Cart Information</h6>
            </div>
            <div class="card-body">
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {{ session('warning') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form action="{{ route('cms.carts.store') }}" method="POST" id="cartForm">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id">User *</label>
                                <select class="form-control @error('user_id') is-invalid @enderror"
                                        id="user_id" name="user_id" required>
                                    <option value="">Select User</option>
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
                    </div>

                    <!-- Cart Items Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">Cart Items</h5>
                            <div id="cart-items-container">
                                <div class="cart-item-row card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Item *</label>
                                                    <select class="form-control item-select" name="items[0][item_id]" required onchange="updateVariantOptions(this)">
                                                        <option value="">Select Item</option>
                                                        @foreach($items as $item)
                                                            <option value="{{ $item->id }}"
                                                                data-price="{{ $item->price }}"
                                                                data-colors="{{ json_encode($item->available_colors) }}"
                                                                data-color-stock="{{ json_encode($item->color_variants ?? []) }}">
                                                                {{ $item->name }} - ${{ number_format($item->price, 2) }} ({{ $item->store->name }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="form-text text-muted item-stock-info" style="display: none;"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Color *</label>
                                                    <select class="form-control color-select" name="items[0][selected_color]" required onchange="validateColorStock(this)">
                                                        <option value="">Select Color</option>
                                                    </select>
                                                    <small class="form-text text-muted color-stock-info" style="display: none;"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Quantity *</label>
                                                    <input type="number" class="form-control quantity-input"
                                                           name="items[0][quantity]" value="1" min="1" max="99" required
                                                           onchange="validateQuantity(this)">
                                                    <small class="form-text text-muted quantity-info" style="display: none;"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Size</label>
                                                    <select class="form-control size-select" name="items[0][selected_size]" onchange="validateSizeStock(this)">
                                                        <option value="">Select Size</option>
                                                        <option value="XS">XS</option>
                                                        <option value="S">S</option>
                                                        <option value="M">M</option>
                                                        <option value="L">L</option>
                                                        <option value="XL">XL</option>
                                                        <option value="XXL">XXL</option>
                                                    </select>
                                                    <small class="form-text text-muted size-stock-info" style="display: none;"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-block remove-item" style="display: none;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-secondary" id="add-item">
                                <i class="fas fa-plus"></i> Add Another Item
                            </button>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Cart
                            </button>
                            <a href="{{ route('cms.carts.index') }}" class="btn btn-secondary">
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
let itemCounter = 1;

// Load items data with variant information
const itemsData = {!! $items->mapWithKeys(function($item) {
    // Convert variants to a more accessible format for JavaScript
    $variantMap = [];
    if ($item->variants && is_array($item->variants)) {
        foreach ($item->variants as $variant) {
            if (isset($variant['color'], $variant['size'], $variant['stock'])) {
                $key = strtolower($variant['color']) . '_' . strtoupper($variant['size']);
                $variantMap[$key] = $variant['stock'];
            }
        }
    }

    return [
        $item->id => [
            'id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
            'available_colors' => $item->available_colors,
            'color_variants' => $item->color_variants ?? [],
            'stock_quantity' => $item->stock_quantity,
            'size_stock' => $item->size_stock ?? [],
            'available_sizes' => $item->available_sizes,
            'variants' => $item->variants ?? [],
            'variant_map' => $variantMap, // Add this for easy lookup
            'available_variants' => $item->available_variants ?? []
        ]
    ];
})->toJson() !!};

// Helper function to get item data
function getItemData(itemId) {
    return itemsData[itemId];
}

// Update variant options when item is selected
function updateVariantOptions(selectElement) {
    const itemRow = selectElement.closest('.cart-item-row');
    const colorSelect = itemRow.querySelector('.color-select');
    const sizeSelect = itemRow.querySelector('.size-select');
    const stockInfo = itemRow.querySelector('.item-stock-info');
    const quantityInput = itemRow.querySelector('.quantity-input');
    const quantityInfo = itemRow.querySelector('.quantity-info');

    const itemId = selectElement.value;
    const itemData = getItemData(itemId);

    // Reset selects and info
    colorSelect.innerHTML = '<option value="">Select Color</option>';
    sizeSelect.innerHTML = '<option value="">Select Size</option>';
    stockInfo.style.display = 'none';
    quantityInfo.style.display = 'none';
    quantityInput.value = 1;
    quantityInput.disabled = true;

    if (!itemData) return;

    // Show total stock info
    stockInfo.textContent = `Total Stock: ${itemData.stock_quantity} units`;
    stockInfo.style.display = 'block';

    // Populate color options from available variants
    const colorsMap = new Map();

    // Group variants by color to show available colors
    itemData.available_variants.forEach(variant => {
        if (!colorsMap.has(variant.color)) {
            colorsMap.set(variant.color, {
                name: variant.color,
                totalStock: 0,
                sizes: new Set()
            });
        }
        const colorInfo = colorsMap.get(variant.color);
        colorInfo.totalStock += variant.stock;
        colorInfo.sizes.add(variant.size);
    });

    // Add color options
    colorsMap.forEach((colorInfo, colorName) => {
        const option = document.createElement('option');
        option.value = colorName;
        option.textContent = `${colorName} (${colorInfo.totalStock} available across ${colorInfo.sizes.size} sizes)`;

        // Store available sizes for this color
        option.dataset.availableSizes = Array.from(colorInfo.sizes).join(',');

        colorSelect.appendChild(option);
    });

    // Add event listener to color select
    colorSelect.onchange = function() {
        updateSizeOptions(this);
        validateVariantStock(this);
    };
}

// Update size options based on selected color
function updateSizeOptions(colorSelect) {
    const itemRow = colorSelect.closest('.cart-item-row');
    const itemSelect = itemRow.querySelector('.item-select');
    const sizeSelect = itemRow.querySelector('.size-select');
    const quantityInput = itemRow.querySelector('.quantity-input');

    const itemId = itemSelect.value;
    const color = colorSelect.value;
    const itemData = getItemData(itemId);

    // Reset size select
    sizeSelect.innerHTML = '<option value="">Select Size</option>';
    sizeSelect.disabled = !color;
    quantityInput.disabled = !color;

    if (!itemData || !color) return;

    // Get available sizes for this color
    const colorVariants = itemData.available_variants.filter(v => v.color === color);

    if (colorVariants.length === 0) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No sizes available for this color';
        option.disabled = true;
        sizeSelect.appendChild(option);
        sizeSelect.disabled = true;
        quantityInput.disabled = true;
        return;
    }

    // Add size options for this color
    colorVariants.forEach(variant => {
        const option = document.createElement('option');
        option.value = variant.size;
        option.textContent = `${variant.size} (${variant.stock} available)`;
        sizeSelect.appendChild(option);
    });

    // Add event listener to size select
    sizeSelect.onchange = function() {
        validateVariantStock(colorSelect);
        validateQuantity(itemRow.querySelector('.quantity-input'));
    };
}

// Validate variant stock availability
function validateVariantStock(colorSelect) {
    const itemRow = colorSelect.closest('.cart-item-row');
    const itemSelect = itemRow.querySelector('.item-select');
    const sizeSelect = itemRow.querySelector('.size-select');
    const colorStockInfo = itemRow.querySelector('.color-stock-info');
    const quantityInput = itemRow.querySelector('.quantity-input');

    const itemId = itemSelect.value;
    const color = colorSelect.value;
    const size = sizeSelect.value;
    const itemData = getItemData(itemId);

    colorStockInfo.style.display = 'none';

    if (!itemData || !color) return;

    if (size) {
        // Check specific color-size variant
        const variant = itemData.available_variants.find(v =>
            v.color === color && v.size === size
        );

        if (!variant) {
            colorStockInfo.textContent = 'This color-size combination is out of stock';
            colorStockInfo.style.color = 'red';
            colorStockInfo.style.display = 'block';
            quantityInput.disabled = true;
        } else {
            colorStockInfo.textContent = `${variant.stock} units available for ${color}/${size}`;
            colorStockInfo.style.color = 'green';
            colorStockInfo.style.display = 'block';
            quantityInput.disabled = false;
            quantityInput.max = variant.stock;
        }
    } else {
        // Show total stock for this color across all sizes
        const colorVariants = itemData.available_variants.filter(v => v.color === color);
        const totalColorStock = colorVariants.reduce((sum, v) => sum + v.stock, 0);

        if (totalColorStock === 0) {
            colorStockInfo.textContent = 'This color is out of stock in all sizes';
            colorStockInfo.style.color = 'red';
            colorStockInfo.style.display = 'block';
            quantityInput.disabled = true;
        } else {
            colorStockInfo.textContent = `${totalColorStock} units available across ${colorVariants.length} sizes`;
            colorStockInfo.style.color = 'blue';
            colorStockInfo.style.display = 'block';
            quantityInput.disabled = false;
            // Don't set max here since user needs to select a size first
        }
    }
}

// Validate quantity against available stock
function validateQuantity(inputElement) {
    const itemRow = inputElement.closest('.cart-item-row');
    const itemSelect = itemRow.querySelector('.item-select');
    const colorSelect = itemRow.querySelector('.color-select');
    const sizeSelect = itemRow.querySelector('.size-select');
    const quantityInfo = itemRow.querySelector('.quantity-info');

    const itemId = itemSelect.value;
    const color = colorSelect.value;
    const size = sizeSelect.value;
    const quantity = parseInt(inputElement.value) || 0;
    const itemData = getItemData(itemId);

    quantityInfo.style.display = 'none';

    if (!itemData || !color || !size) {
        if (!size) {
            quantityInfo.textContent = 'Please select a size';
            quantityInfo.style.color = 'red';
            quantityInfo.style.display = 'block';
        }
        return;
    }

    // Get available stock using variant_map
    const variantKey = `${color.toLowerCase()}_${size.toUpperCase()}`;
    const availableStock = itemData.variant_map[variantKey] || 0;

    console.log('Checking stock:', {
        itemId, color, size, quantity, availableStock, variantKey,
        variant_map: itemData.variant_map
    });

    if (quantity > availableStock) {
        quantityInfo.textContent = `Only ${availableStock} units available for ${color}/${size}`;
        quantityInfo.style.color = 'red';
        quantityInfo.style.display = 'block';
        inputElement.setCustomValidity('Quantity exceeds available stock for this variant');
    } else {
        inputElement.setCustomValidity('');
        if (availableStock < 10) {
            quantityInfo.textContent = `Only ${availableStock} units left for ${color}/${size}`;
            quantityInfo.style.color = 'orange';
            quantityInfo.style.display = 'block';
        }
    }
}

// Add new item to cart
document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('cart-items-container');
    const newItem = document.querySelector('.cart-item-row').cloneNode(true);

    // Update indices
    const newIndex = itemCounter++;
    newItem.innerHTML = newItem.innerHTML.replace(/items\[0\]/g, `items[${newIndex}]`);

    // Clear values and reset state
    const itemSelect = newItem.querySelector('.item-select');
    const colorSelect = newItem.querySelector('.color-select');
    const sizeSelect = newItem.querySelector('.size-select');
    const quantityInput = newItem.querySelector('.quantity-input');
    const removeBtn = newItem.querySelector('.remove-item');

    itemSelect.value = '';
    colorSelect.innerHTML = '<option value="">Select Color</option>';
    sizeSelect.innerHTML = '<option value="">Select Size</option>';
    quantityInput.value = 1;
    quantityInput.disabled = true;

    // Reset info displays
    newItem.querySelectorAll('.item-stock-info, .color-stock-info, .size-stock-info, .quantity-info').forEach(el => {
        el.style.display = 'none';
    });

    // Show remove button (except for first item)
    removeBtn.style.display = itemCounter > 2 ? 'block' : 'none';

    // Add event listeners
    itemSelect.onchange = function() {
        updateVariantOptions(this);
    };

    colorSelect.onchange = function() {
        updateSizeOptions(this);
        validateVariantStock(this);
    };

    sizeSelect.onchange = function() {
        validateVariantStock(colorSelect);
        validateQuantity(quantityInput);
    };

    quantityInput.onchange = function() {
        validateQuantity(this);
    };

    quantityInput.oninput = function() {
        validateQuantity(this);
    };

    container.appendChild(newItem);
});

// Remove item from cart
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item')) {
        const itemRow = e.target.closest('.cart-item-row');
        if (document.querySelectorAll('.cart-item-row').length > 1) {
            itemRow.remove();
            // Update remove button visibility for remaining items
            const remainingItems = document.querySelectorAll('.cart-item-row');
            remainingItems.forEach((row, index) => {
                const btn = row.querySelector('.remove-item');
                btn.style.display = index === 0 ? 'none' : 'block';
            });
        }
    }
});

// Form submission validation
document.getElementById('cartForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const userSelect = document.getElementById('user_id');
    const itemRows = document.querySelectorAll('.cart-item-row');

    // Validate user selection
    if (!userSelect.value) {
        Swal.fire('Error', 'Please select a user.', 'error');
        return;
    }

    // Validate at least one item
    let hasValidItems = false;
    const validationErrors = [];

    itemRows.forEach((row, index) => {
        const itemSelect = row.querySelector('.item-select');
        const colorSelect = row.querySelector('.color-select');
        const sizeSelect = row.querySelector('.size-select');
        const quantityInput = row.querySelector('.quantity-input');

        if (itemSelect.value) {
            hasValidItems = true;

            // Check if all required fields are filled
            if (!colorSelect.value) {
                validationErrors.push(`Item ${index + 1}: Please select a color`);
            } else if (!sizeSelect.value) {
                validationErrors.push(`Item ${index + 1}: Please select a size`);
            } else if (!quantityInput.checkValidity()) {
                validationErrors.push(`Item ${index + 1}: Invalid quantity`);
            } else {
                // Validate stock availability
                const itemData = getItemData(itemSelect.value);
                const variant = itemData?.available_variants.find(v =>
                    v.color === colorSelect.value && v.size === sizeSelect.value
                );

                const quantity = parseInt(quantityInput.value) || 0;

                if (!variant) {
                    validationErrors.push(`Item ${index + 1} (${itemData?.name}): Variant ${colorSelect.value}/${sizeSelect.value} is out of stock`);
                } else if (quantity > variant.stock) {
                    validationErrors.push(`Item ${index + 1} (${itemData?.name}): Quantity (${quantity}) exceeds available stock (${variant.stock}) for ${colorSelect.value}/${sizeSelect.value}`);
                }
            }
        }
    });

    // Show errors if any
    if (!hasValidItems) {
        Swal.fire('Error', 'Please add at least one item to the cart.', 'error');
        return;
    }

    if (validationErrors.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Errors',
            html: `<div style="text-align: left; max-height: 300px; overflow-y: auto;">
                   <ul style="margin: 0; padding-left: 20px;">
                     ${validationErrors.map(error => `<li>${error}</li>`).join('')}
                   </ul>
                 </div>`,
            confirmButtonText: 'OK'
        });
        return;
    }

    // Show confirmation
    Swal.fire({
        title: 'Create Cart?',
        text: 'This will reserve stock for the selected items.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, create cart',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Creating Cart...',
                text: 'Please wait while we process your request',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit the form
            this.submit();
        }
    });
});

// Initialize event listeners for the first item
document.addEventListener('DOMContentLoaded', function() {
    const firstItemRow = document.querySelector('.cart-item-row');
    if (firstItemRow) {
        const itemSelect = firstItemRow.querySelector('.item-select');
        const colorSelect = firstItemRow.querySelector('.color-select');
        const sizeSelect = firstItemRow.querySelector('.size-select');
        const quantityInput = firstItemRow.querySelector('.quantity-input');
        const removeBtn = firstItemRow.querySelector('.remove-item');

        // Hide remove button for first item
        removeBtn.style.display = 'none';

        // Add event listeners
        itemSelect.onchange = function() {
            updateVariantOptions(this);
        };

        colorSelect.onchange = function() {
            updateSizeOptions(this);
            validateVariantStock(this);
        };

        sizeSelect.onchange = function() {
            validateVariantStock(colorSelect);
            validateQuantity(quantityInput);
        };

        quantityInput.onchange = function() {
            validateQuantity(this);
        };

        quantityInput.oninput = function() {
            validateQuantity(this);
        };

        // Initialize if there's a pre-selected item
        if (itemSelect.value) {
            updateVariantOptions(itemSelect);
        }
    }
});
</script>
@endpush
