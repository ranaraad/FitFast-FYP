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

    // Reset selects
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
        option.textContent = colorName;

        const colorStock = itemData.color_variants[colorCode]?.stock || 0;
        option.textContent += ` (${colorStock} available)`;

        colorSelect.appendChild(option);
    }

    // Update size options based on availability
    const sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    sizes.forEach(size => {
        const option = document.createElement('option');
        option.value = size;
        option.textContent = size;

        const sizeStock = itemData.size_stock[size] || 0;
        const isAvailable = itemData.available_sizes.includes(size);

        if (!isAvailable) {
            option.disabled = true;
            option.textContent += ' (Out of Stock)';
        } else {
            option.textContent += ` (${sizeStock} available)`;
        }

        sizeSelect.appendChild(option);
    });

    // Validate initial quantity
    validateQuantity(quantityInput);
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

    if (!itemData || !size) return;

    const sizeStock = itemData.size_stock[size] || 0;

    if (sizeStock === 0) {
        sizeStockInfo.textContent = 'This size is out of stock';
        sizeStockInfo.style.color = 'red';
        sizeStockInfo.style.display = 'block';
        quantityInput.disabled = true;
    } else {
        sizeStockInfo.textContent = `${sizeStock} units available`;
        sizeStockInfo.style.color = 'green';
        sizeStockInfo.style.display = 'block';
        quantityInput.disabled = false;
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

    if (!itemData || !color) return;

    // Get available stock for the selected color
    let availableStock = itemData.color_variants[color]?.stock || 0;

    if (quantity > availableStock) {
        quantityInfo.textContent = `Only ${availableStock} units available for this color`;
        quantityInfo.style.color = 'red';
        quantityInfo.style.display = 'block';
        inputElement.setCustomValidity('Quantity exceeds available stock for this color');
    } else {
        inputElement.setCustomValidity('');
        if (availableStock < 10) {
            quantityInfo.textContent = `Only ${availableStock} units left for this color`;
            quantityInfo.style.color = 'orange';
            quantityInfo.style.display = 'block';
        }
    }
}

document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('cart-items-container');
    const newItem = document.querySelector('.cart-item-row').cloneNode(true);

    // Update indices
    const newIndex = itemCounter++;
    newItem.innerHTML = newItem.innerHTML.replace(/items\[0\]/g, `items[${newIndex}]`);

    // Clear values
    newItem.querySelector('.item-select').value = '';
    newItem.querySelector('.color-select').innerHTML = '<option value="">Select Color</option>';
    newItem.querySelector('.size-select').innerHTML = '<option value="">Select Size</option>';
    newItem.querySelector('.quantity-input').value = 1;

    // Reset info displays
    newItem.querySelector('.item-stock-info').style.display = 'none';
    newItem.querySelector('.color-stock-info').style.display = 'none';
    newItem.querySelector('.size-stock-info').style.display = 'none';
    newItem.querySelector('.quantity-info').style.display = 'none';

    // Show remove button
    newItem.querySelector('.remove-item').style.display = 'block';

    container.appendChild(newItem);
});

// Remove item
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-item')) {
        const itemRow = e.target.closest('.cart-item-row');
        if (document.querySelectorAll('.cart-item-row').length > 1) {
            itemRow.remove();
        }
    }
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

    itemSelects.forEach((select, index) => {
        if (select.value) {
            hasItems = true;

            const itemRow = select.closest('.cart-item-row');
            const quantityInput = itemRow.querySelector('.quantity-input');
            const colorSelect = itemRow.querySelector('.color-select');

            const itemData = getItemData(select.value);
            const quantity = parseInt(quantityInput.value) || 0;
            const color = colorSelect.value;

            if (!color) {
                stockErrors.push(`"${itemData.name}" (please select a color)`);
                return;
            }

            const availableStock = itemData.color_variants[color]?.stock || 0;

            if (quantity > availableStock) {
                stockErrors.push(`"${itemData.name}" in ${color} (quantity: ${quantity}, available: ${availableStock})`);
            }
        }
    });

    if (!hasItems) {
        e.preventDefault();
        Swal.fire('Error', 'Please add at least one item to the cart.', 'error');
        return;
    }

    if (stockErrors.length > 0) {
        e.preventDefault();
        Swal.fire('Stock Error', `The following items exceed available stock:\n${stockErrors.join('\n')}`, 'error');
        return;
    }
});
</script>
@endpush
