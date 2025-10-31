@extends('cms.layouts.app')

@section('page-title', 'Create New Item')
@section('page-subtitle', 'Add new item to your store inventory')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Item</h1>
    <a href="{{ route('store-admin.items.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Items
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Item Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('store-admin.items.store') }}" method="POST" id="itemForm" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Item Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="Enter item name">
                                @error('name')
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

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id">Category *</label>
                                <select class="form-control @error('category_id') is-invalid @enderror"
                                        id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">Price ($) *</label>
                                <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror"
                                       id="price" name="price" value="{{ old('price') }}" required
                                       placeholder="0.00" min="0">
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3"
                                  placeholder="Enter item description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Color Variants Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">Color Variants *</h5>
                            <small class="text-muted">Add all available colors for this item with their stock quantities.</small>
                            <div id="color-variants-container">
                                <div class="color-variant-row card mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Color Name *</label>
                                                    <input type="text" class="form-control color-name"
                                                           name="color_variants[0][name]" value="{{ old('color_variants.0.name', '') }}"
                                                           placeholder="e.g., Red, Blue, Black" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Stock Quantity *</label>
                                                    <input type="number" class="form-control color-stock"
                                                           name="color_variants[0][stock]" value="{{ old('color_variants.0.stock', 0) }}"
                                                           min="0" required>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-block remove-color" style="display: none;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-secondary" id="add-color">
                                <i class="fas fa-plus"></i> Add Another Color
                            </button>
                        </div>
                    </div>

                    <!-- Include the partial that contains Garment Type, Stock by Size, and Measurements -->
                    @include('cms.pages.items.partials.sizing-data')

                    <!-- Stock Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-left-info">
                                <div class="card-body">
                                    <h6 class="card-title text-info">
                                        <i class="fas fa-info-circle mr-2"></i>Stock Summary
                                    </h6>
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="border-right">
                                                <div class="h4 mb-1 text-primary" id="total-color-stock">0</div>
                                                <small class="text-muted">Total Color Stock</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border-right">
                                                <div class="h4 mb-1 text-success" id="total-size-stock">0</div>
                                                <small class="text-muted">Total Size Stock</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div id="stock-match-indicator">
                                                <div class="h4 mb-1 text-danger" id="stock-difference">0</div>
                                                <small class="text-muted">Stock Difference</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2" id="stock-validation-message"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Item
                            </button>
                            <a href="{{ route('store-admin.items.index') }}" class="btn btn-secondary">
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

@push('styles')
<style>
.color-variant-row {
    border-left: 4px solid #4e73df;
}

.border-right {
    border-right: 1px solid #e3e6f0 !important;
}

#stock-match-indicator .match {
    color: #1cc88a;
}

#stock-match-indicator .mismatch {
    color: #e74a3b;
}

.stock-alert {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 0.35rem;
    padding: 0.75rem;
    margin-top: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
const garmentTypesByCategory = @json($categoryToGarmentTypes);
let colorCounter = 1;

// Color variants management
document.getElementById('add-color').addEventListener('click', function() {
    const container = document.getElementById('color-variants-container');
    const newColor = document.querySelector('.color-variant-row').cloneNode(true);

    // Update indices
    const newIndex = colorCounter++;
    newColor.innerHTML = newColor.innerHTML.replace(/color_variants\[0\]/g, `color_variants[${newIndex}]`);

    // Clear values
    newColor.querySelector('.color-name').value = '';
    newColor.querySelector('.color-stock').value = 0;

    // Show remove button
    newColor.querySelector('.remove-color').style.display = 'block';

    container.appendChild(newColor);
    updateStockCalculations();
});

// Remove color variant
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-color')) {
        const colorRow = e.target.closest('.color-variant-row');
        if (document.querySelectorAll('.color-variant-row').length > 1) {
            colorRow.remove();
            updateStockCalculations();
        }
    }
});

// Update file input label
document.getElementById('image').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Choose image file...';
    e.target.nextElementSibling.textContent = fileName;
});

// Form validation
document.getElementById('itemForm').addEventListener('submit', function(e) {
    const colorNames = new Set();
    let hasDuplicateColors = false;

    document.querySelectorAll('.color-name').forEach(input => {
        const colorName = input.value.trim().toLowerCase();
        if (colorName && colorNames.has(colorName)) {
            hasDuplicateColors = true;
        }
        colorNames.add(colorName);
    });

    if (hasDuplicateColors) {
        e.preventDefault();
        Swal.fire('Error', 'Please remove duplicate color names.', 'error');
        return;
    }

    // Check if at least one color has stock
    let hasStock = false;
    document.querySelectorAll('.color-stock').forEach(input => {
        if (parseInt(input.value) > 0) {
            hasStock = true;
        }
    });

    if (!hasStock) {
        e.preventDefault();
        Swal.fire('Error', 'Please add stock for at least one color variant.', 'error');
        return;
    }

    // Validate stock consistency
    const totalColorStock = calculateTotalColorStock();
    const totalSizeStock = calculateTotalSizeStock();

    if (totalColorStock !== totalSizeStock) {
        e.preventDefault();
        Swal.fire('Error', `Total color stock (${totalColorStock}) must match total size stock (${totalSizeStock}). Please adjust your stock levels.`, 'error');
        return;
    }
});

// Stock calculation functions
function calculateTotalColorStock() {
    let total = 0;
    document.querySelectorAll('.color-stock').forEach(input => {
        total += parseInt(input.value) || 0;
    });
    return total;
}

function calculateTotalSizeStock() {
    let total = 0;
    document.querySelectorAll('.size-stock-input').forEach(input => {
        total += parseInt(input.value) || 0;
    });
    return total;
}

function updateStockCalculations() {
    const totalColorStock = calculateTotalColorStock();
    const totalSizeStock = calculateTotalSizeStock();
    const difference = totalColorStock - totalSizeStock;

    // Update display
    document.getElementById('total-color-stock').textContent = totalColorStock;
    document.getElementById('total-size-stock').textContent = totalSizeStock;
    document.getElementById('stock-difference').textContent = difference;

    // Update styling based on match
    const indicator = document.getElementById('stock-match-indicator');
    const message = document.getElementById('stock-validation-message');

    if (difference === 0) {
        indicator.classList.remove('mismatch');
        indicator.classList.add('match');
        document.getElementById('stock-difference').className = 'h4 mb-1 text-success';
        message.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> Stock levels match!</small>';
    } else {
        indicator.classList.remove('match');
        indicator.classList.add('mismatch');
        document.getElementById('stock-difference').className = 'h4 mb-1 text-danger';
        message.innerHTML = `<small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Stock levels don't match. Please adjust color or size stock quantities.</small>`;
    }
}

// Listen for stock changes and update calculations
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('color-stock') || e.target.classList.contains('size-stock-input')) {
        updateStockCalculations();
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateStockCalculations();

    // Show remove button if there are existing colors from old input
    const colorRows = document.querySelectorAll('.color-variant-row');
    if (colorRows.length > 1) {
        colorRows.forEach(row => {
            row.querySelector('.remove-color').style.display = 'block';
        });
    }
});
</script>
@endpush
