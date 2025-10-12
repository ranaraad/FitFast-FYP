@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Cart</h1>
    <a href="{{ route('cms.carts.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Carts
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Edit Cart Information</h6>
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

                <form action="{{ route('cms.carts.update', $cart) }}" method="POST" id="cartForm">
                    @csrf
                    @method('PUT')

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="user_id">User *</label>
                                <select class="form-control @error('user_id') is-invalid @enderror"
                                        id="user_id" name="user_id" required>
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $cart->user_id) == $user->id ? 'selected' : '' }}>
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
                                @php
                                    $itemCounter = 0;
                                @endphp

                                @foreach($cart->cartItems as $cartItem)
                                @php
                                    $currentItem = $items->firstWhere('id', $cartItem->item_id);
                                @endphp
                                <div class="cart-item-row card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Item *</label>
                                                    <input type="hidden" name="items[{{ $itemCounter }}][id]" value="{{ $cartItem->id }}">
                                                    <select class="form-control item-select" name="items[{{ $itemCounter }}][item_id]" required onchange="updateVariantOptions(this)">
                                                        <option value="">Select Item</option>
                                                        @foreach($items as $item)
                                                            <option value="{{ $item->id }}"
                                                                data-price="{{ $item->price }}"
                                                                data-colors="{{ json_encode($item->available_colors) }}"
                                                                data-color-stock="{{ json_encode($item->color_variants ?? []) }}"
                                                                {{ old("items.$itemCounter.item_id", $cartItem->item_id) == $item->id ? 'selected' : '' }}>
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
                                                    <select class="form-control color-select" name="items[{{ $itemCounter }}][selected_color]" required onchange="validateColorStock(this)">
                                                        <option value="">Select Color</option>
                                                        @if($currentItem)
                                                            @foreach($currentItem->available_colors as $colorCode => $colorName)
                                                                @php
                                                                    $colorStock = $currentItem->color_variants[$colorCode]['stock'] ?? 0;
                                                                    $isSelected = old("items.$itemCounter.selected_color", $cartItem->selected_color) == $colorCode;
                                                                @endphp
                                                                <option value="{{ $colorCode }}"
                                                                    {{ $isSelected ? 'selected' : '' }}
                                                                    {{ $colorStock == 0 && !$isSelected ? 'disabled' : '' }}>
                                                                    {{ $colorName }}
                                                                    @if($isSelected && $colorStock == 0)
                                                                        (Currently in cart)
                                                                    @elseif($colorStock == 0)
                                                                        (Out of Stock)
                                                                    @else
                                                                        ({{ $colorStock }} available)
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <small class="form-text text-muted color-stock-info" style="display: none;"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Quantity *</label>
                                                    <input type="number" class="form-control quantity-input"
                                                           name="items[{{ $itemCounter }}][quantity]"
                                                           value="{{ old("items.$itemCounter.quantity", $cartItem->quantity) }}"
                                                           min="1" max="99" required
                                                           onchange="validateQuantity(this)">
                                                    <small class="form-text text-muted quantity-info" style="display: none;"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Size</label>
                                                    <select class="form-control size-select" name="items[{{ $itemCounter }}][selected_size]" onchange="validateSizeStock(this)">
                                                        <option value="">Select Size</option>
                                                        @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size)
                                                            @php
                                                                $sizeStock = $currentItem ? ($currentItem->size_stock[$size] ?? 0) : 0;
                                                                // Fix: Use in_array() for arrays instead of contains() for Collections
                                                                $isAvailable = $currentItem ? in_array($size, $currentItem->available_sizes) : false;
                                                                $isSelected = old("items.$itemCounter.selected_size", $cartItem->selected_size) == $size;
                                                            @endphp
                                                            <option value="{{ $size }}"
                                                                {{ $isSelected ? 'selected' : '' }}
                                                                {{ !$isAvailable && !$isSelected ? 'disabled' : '' }}>
                                                                {{ $size }}
                                                                @if($isSelected && !$isAvailable)
                                                                    (Currently in cart)
                                                                @elseif(!$isAvailable)
                                                                    (Out of Stock)
                                                                @else
                                                                    ({{ $sizeStock }} available)
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="form-text text-muted size-stock-info" style="display: none;"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-block remove-item">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @php $itemCounter++; @endphp
                                @endforeach
                            </div>

                            <button type="button" class="btn btn-sm btn-secondary" id="add-item">
                                <i class="fas fa-plus"></i> Add New Item
                            </button>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Cart
                            </button>
                            <a href="{{ route('cms.carts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <a href="{{ route('cms.carts.show', $cart) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> View Cart
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Template for new items (outside the form) -->
<div id="cart-item-template" style="display: none;">
    <div class="cart-item-row card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Item *</label>
                        <select class="form-control item-select" name="items[new_index][item_id]" required onchange="updateVariantOptions(this)">
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
                        <select class="form-control color-select" name="items[new_index][selected_color]" required onchange="validateColorStock(this)">
                            <option value="">Select Color</option>
                        </select>
                        <small class="form-text text-muted color-stock-info" style="display: none;"></small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" class="form-control quantity-input"
                               name="items[new_index][quantity]" value="1" min="1" max="99" required
                               onchange="validateQuantity(this)">
                        <small class="form-text text-muted quantity-info" style="display: none;"></small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Size</label>
                        <select class="form-control size-select" name="items[new_index][selected_size]" onchange="validateSizeStock(this)">
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
                        <button type="button" class="btn btn-danger btn-block remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let newItemCounter = 0;

const itemsData = {!! $items->mapWithKeys(function($item) {
    return [
        $item->id => [
            'id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
            'available_colors' => $item->available_colors,
            'color_variants' => $item->color_variants ?? [],
            'stock_quantity' => $item->stock_quantity,
            'size_stock' => $item->size_stock ?? [],
            'available_sizes' => $item->available_sizes
        ]
    ];
})->toJson() !!};

function getItemData(itemId) {
    return itemsData[itemId];
}

function updateVariantOptions(selectElement) {
    const itemRow = selectElement.closest('.cart-item-row');
    const colorSelect = itemRow.querySelector('.color-select');
    const sizeSelect = itemRow.querySelector('.size-select');
    const stockInfo = itemRow.querySelector('.item-stock-info');
    const quantityInput = itemRow.querySelector('.quantity-input');
    const quantityInfo = itemRow.querySelector('.quantity-info');

    const itemId = selectElement.value;
    const itemData = getItemData(itemId);

    // Store current values before resetting
    const currentColor = colorSelect.value;
    const currentSize = sizeSelect.value;

    // Reset selects but preserve current values
    colorSelect.innerHTML = '<option value="">Select Color</option>';
    sizeSelect.innerHTML = '<option value="">Select Size</option>';
    stockInfo.style.display = 'none';
    quantityInfo.style.display = 'none';

    if (!itemData) return;

    // Show total stock info
    stockInfo.textContent = `Total Stock: ${itemData.stock_quantity} units`;
    stockInfo.style.display = 'block';

    // Update color options based on availability
    const availableColors = itemData.available_colors;

    for (const [colorCode, colorName] of Object.entries(availableColors)) {
        const option = document.createElement('option');
        option.value = colorCode;

        const colorStock = itemData.color_variants[colorCode]?.stock || 0;
        const isCurrent = colorCode === currentColor;

        // Allow currently selected color even if out of stock (for editing)
        if (isCurrent) {
            option.textContent = `${colorName} (Currently in cart - ${colorStock} available)`;
            option.selected = true;
        } else if (colorStock === 0) {
            option.disabled = true;
            option.textContent = `${colorName} (Out of Stock)`;
        } else {
            option.textContent = `${colorName} (${colorStock} available)`;
        }

        colorSelect.appendChild(option);
    }

    // If no color was selected from current values, try to select the first available color
    if (currentColor && !colorSelect.value) {
        const firstAvailableOption = colorSelect.querySelector('option:not([disabled])');
        if (firstAvailableOption) {
            firstAvailableOption.selected = true;
        }
    }

    // Update size options based on availability
    const sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    sizes.forEach(size => {
        const option = document.createElement('option');
        option.value = size;

        const sizeStock = itemData.size_stock[size] || 0;
        // Fix: Use includes() for JavaScript arrays
        const isAvailable = itemData.available_sizes.includes(size);
        const isCurrent = size === currentSize;

        // Allow currently selected size even if out of stock (for editing)
        if (isCurrent) {
            option.textContent = `${size} (Currently in cart - ${sizeStock} available)`;
            option.selected = true;
        } else if (!isAvailable) {
            option.disabled = true;
            option.textContent = `${size} (Out of Stock)`;
        } else {
            option.textContent = `${size} (${sizeStock} available)`;
        }

        sizeSelect.appendChild(option);
    });

    // If no size was selected from current values, try to select the first available size
    if (currentSize && !sizeSelect.value) {
        const firstAvailableOption = sizeSelect.querySelector('option:not([disabled])');
        if (firstAvailableOption) {
            firstAvailableOption.selected = true;
        }
    }

    // Validate initial quantity
    validateQuantity(quantityInput);

    // Trigger validation for color and size
    validateColorStock(colorSelect);
    validateSizeStock(sizeSelect);
}

function validateColorStock(selectElement) {
    const itemRow = selectElement.closest('.cart-item-row');
    const itemSelect = itemRow.querySelector('.item-select');
    const colorStockInfo = itemRow.querySelector('.color-stock-info');
    const quantityInput = itemRow.querySelector('.quantity-input');

    const itemId = itemSelect.value;
    const color = selectElement.value;
    const itemData = getItemData(itemId);

    colorStockInfo.style.display = 'none';
    quantityInput.disabled = false;

    if (!itemData || !color) return;

    const colorStock = itemData.color_variants[color]?.stock || 0;

    if (colorStock === 0) {
        colorStockInfo.textContent = 'This color is out of stock';
        colorStockInfo.style.color = 'red';
        colorStockInfo.style.display = 'block';
        quantityInput.disabled = true;
    } else {
        colorStockInfo.textContent = `${colorStock} units available`;
        colorStockInfo.style.color = 'green';
        colorStockInfo.style.display = 'block';
        quantityInput.disabled = false;
        validateQuantity(quantityInput);
    }
}

function validateSizeStock(selectElement) {
    const itemRow = selectElement.closest('.cart-item-row');
    const itemSelect = itemRow.querySelector('.item-select');
    const sizeStockInfo = itemRow.querySelector('.size-stock-info');
    const quantityInput = itemRow.querySelector('.quantity-input');

    const itemId = itemSelect.value;
    const size = selectElement.value;
    const itemData = getItemData(itemId);

    sizeStockInfo.style.display = 'none';
    quantityInput.disabled = false;

    if (!itemData || !size) return;

    const sizeStock = itemData.size_stock[size] || 0;
    // Fix: Use includes() for JavaScript arrays
    const isAvailable = itemData.available_sizes.includes(size);

    if (!isAvailable) {
        sizeStockInfo.textContent = 'This size is out of stock';
        sizeStockInfo.style.color = 'red';
        sizeStockInfo.style.display = 'block';
        quantityInput.disabled = true;
    } else {
        sizeStockInfo.textContent = `${sizeStock} units available`;
        sizeStockInfo.style.color = 'green';
        sizeStockInfo.style.display = 'block';
        validateQuantity(quantityInput);
    }
}

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
    inputElement.setCustomValidity('');

    if (!itemData || !color) return;

    // Get available stock for the selected color
    const colorStock = itemData.color_variants[color]?.stock || 0;

    // Get available stock for the selected size (if size is selected)
    const sizeStock = size ? (itemData.size_stock[size] || 0) : null;

    // Determine the limiting stock - use the smaller of color stock and size stock (if size is selected)
    let availableStock = colorStock;
    let limitingFactor = 'color';

    if (size && sizeStock !== null && sizeStock < colorStock) {
        availableStock = sizeStock;
        limitingFactor = 'size';
    }

    // For existing items, we need to consider they might already be in cart
    // So we add back the current quantity to available stock for validation
    const hiddenId = itemRow.querySelector('input[type="hidden"][name*="[id]"]');
    const isExistingItem = hiddenId && hiddenId.value;

    if (quantity > availableStock) {
        if (limitingFactor === 'color') {
            quantityInfo.textContent = `Only ${availableStock} units available for this color`;
        } else {
            quantityInfo.textContent = `Only ${availableStock} units available for this size`;
        }
        quantityInfo.style.color = 'red';
        quantityInfo.style.display = 'block';
        inputElement.setCustomValidity(`Quantity exceeds available stock for this ${limitingFactor}`);
    } else {
        inputElement.setCustomValidity('');
        if (availableStock < 10) {
            if (limitingFactor === 'color') {
                quantityInfo.textContent = `Only ${availableStock} units left for this color`;
            } else {
                quantityInfo.textContent = `Only ${availableStock} units left for this size`;
            }
            quantityInfo.style.color = 'orange';
            quantityInfo.style.display = 'block';
        }
    }
}

// Function to check for duplicate items
function checkForDuplicates() {
    const itemRows = document.querySelectorAll('.cart-item-row');
    const itemCombinations = new Map(); // Map to track item_id + color combinations

    let hasDuplicates = false;
    let duplicateErrors = [];

    itemRows.forEach((row, index) => {
        const itemSelect = row.querySelector('.item-select');
        const colorSelect = row.querySelector('.color-select');

        if (itemSelect.value && colorSelect.value) {
            const combination = `${itemSelect.value}-${colorSelect.value}`;

            if (itemCombinations.has(combination)) {
                const existingIndex = itemCombinations.get(combination);
                const itemData = getItemData(itemSelect.value);
                const colorName = itemData.available_colors[colorSelect.value];

                duplicateErrors.push(`"${itemData.name}" in ${colorName} (appears in items ${existingIndex + 1} and ${index + 1})`);
                hasDuplicates = true;

                // Highlight duplicate rows
                row.style.border = '2px solid #dc3545';
                const existingRow = itemRows[existingIndex];
                existingRow.style.border = '2px solid #dc3545';
            } else {
                itemCombinations.set(combination, index);
                row.style.border = '';
            }
        }
    });

    return { hasDuplicates, duplicateErrors };
}

document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('cart-items-container');
    const template = document.getElementById('cart-item-template');
    const newItem = template.cloneNode(true);

    // Update indices for new item
    const newIndex = 'new_' + newItemCounter++;
    newItem.innerHTML = newItem.innerHTML.replace(/items\[new_index\]/g, `items[${newIndex}]`);

    // Show the new item and remove the template wrapper
    const itemContent = newItem.querySelector('.cart-item-row');
    container.appendChild(itemContent);

    // Check for duplicates after adding
    checkForDuplicates();
});

// Remove item
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item')) {
        const itemRow = e.target.closest('.cart-item-row');
        if (document.querySelectorAll('.cart-item-row').length > 1) {
            itemRow.remove();
            // Re-check duplicates after removal
            checkForDuplicates();
        } else {
            Swal.fire('Error', 'Cart must have at least one item.', 'error');
        }
    }
});

// Check for duplicates when items or colors change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('item-select') || e.target.classList.contains('color-select')) {
        checkForDuplicates();
    }
});

// Initialize variant options for existing items on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.item-select').forEach(select => {
        if (select.value) {
            // Store current values before updating
            const itemRow = select.closest('.cart-item-row');
            const colorSelect = itemRow.querySelector('.color-select');
            const sizeSelect = itemRow.querySelector('.size-select');

            // Store current selections
            const currentColor = colorSelect.value;
            const currentSize = sizeSelect.value;

            // Update the options
            updateVariantOptions(select);

            // Restore selections if they exist
            if (currentColor) {
                colorSelect.value = currentColor;
            }
            if (currentSize) {
                sizeSelect.value = currentSize;
            }

            // Trigger validation
            validateColorStock(colorSelect);
            validateSizeStock(sizeSelect);
        }
    });

    // Check for duplicates on page load
    checkForDuplicates();
});

// Form validation
document.getElementById('cartForm').addEventListener('submit', function(e) {
    const userSelect = document.getElementById('user_id');
    const itemSelects = document.querySelectorAll('.item-select');

    if (!userSelect.value) {
        e.preventDefault();
        Swal.fire('Error', 'Please select a user.', 'error');
        return;
    }

    let hasItems = false;
    let stockErrors = [];
    let validationErrors = [];

    itemSelects.forEach((select) => {
        if (select.value) {
            hasItems = true;

            const itemRow = select.closest('.cart-item-row');
            const quantityInput = itemRow.querySelector('.quantity-input');
            const colorSelect = itemRow.querySelector('.color-select');
            const sizeSelect = itemRow.querySelector('.size-select');

            const itemData = getItemData(select.value);
            const quantity = parseInt(quantityInput.value) || 0;
            const color = colorSelect.value;
            const size = sizeSelect.value;

            if (!color) {
                validationErrors.push(`"${itemData.name}" (please select a color)`);
                return;
            }

            const colorStock = itemData.color_variants[color]?.stock || 0;
            const sizeStock = size ? (itemData.size_stock[size] || 0) : null;

            // Determine the limiting stock
            let availableStock = colorStock;
            let limitingFactor = 'color';

            if (size && sizeStock !== null && sizeStock < colorStock) {
                availableStock = sizeStock;
                limitingFactor = 'size';
            }

            if (quantity > availableStock) {
                if (limitingFactor === 'color') {
                    stockErrors.push(`"${itemData.name}" in ${itemData.available_colors[color]} (quantity: ${quantity}, available: ${availableStock} for this color)`);
                } else {
                    stockErrors.push(`"${itemData.name}" in ${itemData.available_colors[color]} size ${size} (quantity: ${quantity}, available: ${availableStock} for this size)`);
                }
            }
        }
    });

    // Check for duplicate items
    const { hasDuplicates, duplicateErrors } = checkForDuplicates();

    if (!hasItems) {
        e.preventDefault();
        Swal.fire('Error', 'Please add at least one item to the cart.', 'error');
        return;
    }

    if (validationErrors.length > 0) {
        e.preventDefault();
        Swal.fire('Validation Error', `Please fix the following errors:\n${validationErrors.join('\n')}`, 'error');
        return;
    }

    if (stockErrors.length > 0) {
        e.preventDefault();
        Swal.fire('Stock Error', `The following items exceed available stock:\n${stockErrors.join('\n')}`, 'error');
        return;
    }

    if (hasDuplicates) {
        e.preventDefault();
        Swal.fire('Duplicate Items', `You cannot add the same item with the same color multiple times:\n${duplicateErrors.join('\n')}`, 'error');
        return;
    }
});
</script>
@endpush
