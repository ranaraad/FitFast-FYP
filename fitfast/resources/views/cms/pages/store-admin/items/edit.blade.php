@extends('cms.layouts.store-admin-app')

@section('page-title', 'Edit Item')
@section('page-subtitle', 'Update item information')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Item</h1>
    <a href="{{ route('store-admin.items.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Items
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Edit Item Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('store-admin.items.update', $item) }}" method="POST" id="itemForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Item Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $item->name) }}" required
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
                                        <option value="{{ $store->id }}" {{ old('store_id', $item->store_id) == $store->id ? 'selected' : '' }}>
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
                                        <option value="{{ $category->id }}" {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
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
                                       id="price" name="price" value="{{ old('price', $item->price) }}" required
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
                                  placeholder="Enter item description">{{ old('description', $item->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Image Upload Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">Item Images</h5>
                            <small class="text-muted">Upload additional images for this item. Drag & drop or click to select. Existing images are shown below.</small>

                            <!-- Existing Images Display -->
                            @if($item->images->count() > 0)
                                <div class="mb-4">
                                    <h6 class="text-muted">Current Images (Drag to reorder)</h6>
                                    <div class="row" id="existing-images-container">
                                        @foreach($item->images->sortBy('order') as $image)
                                            <div class="col-lg-2 col-md-3 col-sm-4 col-6 preview-col">
                                                <div class="image-preview existing-image"
                                                     data-image-id="{{ $image->id }}"
                                                     draggable="true">
                                                    <img src="{{ asset('storage/' . $image->image_path) }}"
                                                         alt="{{ $item->name }}"
                                                         class="img-thumbnail">
                                                    @if($image->is_primary)
                                                        <div class="primary-badge">Primary</div>
                                                    @endif
                                                    <button type="button"
                                                            class="remove-existing-image"
                                                            data-image-id="{{ $image->id }}"
                                                            data-item-id="{{ $item->id }}"
                                                            title="Remove image">
                                                        ×
                                                    </button>
                                                    <div class="order-badge">
                                                        #{{ $image->order + 1 }}
                                                    </div>
                                                    <div class="image-info">
                                                        {{ basename($image->image_path) }}
                                                    </div>
                                                    @if(!$image->is_primary)
                                                        <button type="button"
                                                                class="set-primary-image"
                                                                data-image-id="{{ $image->id }}"
                                                                data-item-id="{{ $item->id }}"
                                                                title="Set as primary">
                                                            <i class="fas fa-star"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="form-group">
                                <div class="dropzone-container" id="dropzone-container">
                                    <div class="dropzone-area" id="dropzone-area">
                                        <div class="dropzone-content">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                            <h5>Drag & Drop Images Here</h5>
                                            <p class="text-muted">or click to browse</p>
                                            <small class="text-muted">Supported formats: JPEG, PNG, GIF, WebP. Max file size: 5MB per image.</small>
                                        </div>
                                        <input type="file" class="dropzone-input @error('images.*') is-invalid @enderror"
                                               id="images" name="images[]" multiple
                                               accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                    </div>
                                    @error('images.*')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- New Image Preview Container -->
                                <div id="image-preview-container" class="row mt-3" style="display: none;">
                                    <div class="col-12">
                                        <h6 class="text-muted mb-3">New Images to Upload</h6>
                                        <div class="row" id="image-previews"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Include Color & Size Variants Partial -->
                    @include('cms.pages.items.partials.color-size-variants')

                    <!-- Include Sizing Data Partial (Garment Types & Measurements Only) -->
                    @include('cms.pages.items.partials.sizing-data')

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Item
                            </button>
                            <a href="{{ route('store-admin.items.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <a href="{{ route('store-admin.items.show', $item) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> View Item
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
/* Keep existing styles but add/edit for color-size variants */
.dropzone-container {
    margin-top: 10px;
}

.dropzone-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.dropzone-area:hover,
.dropzone-area.dragover {
    border-color: #007bff;
    background: #e3f2fd;
}

.dropzone-area.has-files {
    border-color: #28a745;
    background: #f8fff9;
}

.dropzone-content {
    pointer-events: none;
}

.dropzone-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.file-count {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.image-preview {
    position: relative;
    margin-bottom: 15px;
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 8px;
    background: #f8f9fc;
    text-align: center;
    transition: all 0.3s ease;
}

.image-preview.existing-image {
    border-color: #17a2b8;
    background: #f0f9ff;
    cursor: grab;
}

.image-preview.existing-image:active {
    cursor: grabbing;
}

.image-preview.existing-image.dragging {
    opacity: 0.5;
    transform: scale(0.95);
}

.image-preview.existing-image.drag-over {
    border: 2px dashed #007bff;
    background-color: #e3f2fd;
}

.image-preview img {
    max-width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.remove-image, .remove-existing-image {
    position: absolute;
    top: 3px;
    right: 3px;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 10px;
    line-height: 1;
    z-index: 10;
}

.remove-image:hover, .remove-existing-image:hover {
    background: #dc3545;
}

.set-primary-image {
    position: absolute;
    bottom: 3px;
    right: 3px;
    background: rgba(23, 162, 184, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 8px;
    line-height: 1;
    z-index: 10;
}

.set-primary-image:hover {
    background: #17a2b8;
}

.primary-badge {
    position: absolute;
    top: 3px;
    left: 3px;
    background: rgba(40, 167, 69, 0.9);
    color: white;
    padding: 1px 4px;
    border-radius: 3px;
    font-size: 8px;
    font-weight: bold;
    z-index: 10;
}

.order-badge {
    position: absolute;
    top: 3px;
    left: 3px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 1px 4px;
    border-radius: 3px;
    font-size: 8px;
    font-weight: bold;
    z-index: 5;
}

.image-info {
    margin-top: 3px;
    font-size: 10px;
    color: #6c757d;
    word-break: break-all;
}

/* Make preview columns smaller */
.preview-col {
    padding: 5px;
}

/* Color-Size Variants specific styling */
.color-size-variant-row .card-header {
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #dee2e6;
}

.size-stock-input {
    text-align: center;
    min-width: 80px;
}

.color-total-stock {
    background-color: #f8f9fa;
    font-weight: bold;
}

/* Drag and drop styling */
.sortable-ghost {
    opacity: 0.4;
}

.sortable-chosen {
    transform: scale(1.02);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>
@endpush

@push('scripts')
<script>
// Standard sizes array from PHP
const standardSizes = @json($standardSizes);
const garmentTypes = @json($garmentTypes);
const categoryToGarmentTypes = @json($categoryToGarmentTypes);
const existingMeasurements = @json($item->sizing_data ? $item->garment_measurements : []);

// Get existing color count from rendered HTML
let colorVariantCounter = document.querySelectorAll('.color-size-variant-row').length;
let draggedImage = null;

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    // Initialize stock calculations
    updateStockCalculations();

    // Initialize garment types if needed
    const garmentTypeSelect = document.getElementById('garment_type');
    if (garmentTypeSelect && garmentTypeSelect.value) {
        updateSizingSection();
    }

    // Set up category change listener for garment types
    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        categorySelect.addEventListener('change', updateGarmentTypeOptions);

        // Initialize garment types based on current category selection
        if (categorySelect.value) {
            updateGarmentTypeOptions();
        }
    }

    // Initialize image reordering
    initializeImageReordering();

    // Add event listeners to all existing color name and size inputs
    initializeExistingColorVariantListeners();
});

// Initialize event listeners for existing color variants
function initializeExistingColorVariantListeners() {
    document.querySelectorAll('.color-name').forEach(input => {
        input.addEventListener('input', updateStockCalculations);
    });

    document.querySelectorAll('.size-stock-input').forEach(input => {
        input.addEventListener('input', updateStockCalculations);
    });

    // Add remove button listeners for non-first color variants
    document.querySelectorAll('.remove-color-size-variant').forEach((btn, index) => {
        if (index > 0) { // Skip first button
            btn.addEventListener('click', function() {
                const variantRow = btn.closest('.color-size-variant-row');
                const sizeInput = variantRow.querySelector('.size-stock-input');
                const colorIndex = sizeInput ? parseInt(sizeInput.getAttribute('data-color-index')) : index;
                removeColorSizeVariant(colorIndex);
            });
        }
    });
}

// ========== COLOR-SIZE VARIANT MANAGEMENT ==========
document.getElementById('add-color-size-variant').addEventListener('click', function() {
    addColorSizeVariant();
});

function addColorSizeVariant() {
    const container = document.getElementById('color-size-variants-container');
    const newIndex = colorVariantCounter++;

    // Clone the first color variant as template
    const firstVariant = container.querySelector('.color-size-variant-row');
    const newVariant = firstVariant.cloneNode(true);

    // Update indices in the new variant
    newVariant.innerHTML = newVariant.innerHTML.replace(/color_variants\[0\]/g, `color_variants[${newIndex}]`);
    newVariant.innerHTML = newVariant.innerHTML.replace(/data-color-index="0"/g, `data-color-index="${newIndex}"`);
    newVariant.innerHTML = newVariant.innerHTML.replace(/status-0-/g, `status-${newIndex}-`);

    // Update header
    const header = newVariant.querySelector('.card-header h6');
    if (header) {
        header.textContent = `Color #${newIndex + 1}`;
    }

    // Update remove button
    const removeBtn = newVariant.querySelector('.remove-color-size-variant');
    if (removeBtn) {
        removeBtn.style.display = 'block';
        const smallElement = removeBtn.closest('.text-right')?.querySelector('small');
        if (smallElement) {
            smallElement.remove();
        }
    }

    // Clear values
    newVariant.querySelector('.color-name').value = '';
    const sizeInputs = newVariant.querySelectorAll('.size-stock-input');
    sizeInputs.forEach(input => {
        input.value = 0;
    });

    // Add event listeners
    newVariant.querySelector('.color-name').addEventListener('input', updateStockCalculations);
    sizeInputs.forEach(input => {
        input.addEventListener('input', updateStockCalculations);
    });

    // Add remove button listener
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            removeColorSizeVariant(newIndex);
        });
    }

    container.appendChild(newVariant);
    updateStockCalculations();
}

function removeColorSizeVariant(index) {
    const variant = document.querySelector(`[data-color-index="${index}"]`)?.closest('.color-size-variant-row');
    if (variant) {
        variant.remove();
        updateStockCalculations();

        // Re-index remaining variants
        const variants = document.querySelectorAll('.color-size-variant-row');
        variants.forEach((variant, newIndex) => {
            const colorNameInput = variant.querySelector('.color-name');
            const sizeInputs = variant.querySelectorAll('.size-stock-input');

            // Update name attribute
            colorNameInput.name = `color_variants[${newIndex}][name]`;

            // Update size inputs
            sizeInputs.forEach((input, sizeIndex) => {
                const size = standardSizes[sizeIndex];
                input.name = `color_variants[${newIndex}][size_stock][${size}]`;
                input.setAttribute('data-color-index', newIndex);
            });

            // Update status element IDs
            sizeInputs.forEach(input => {
                const size = input.getAttribute('data-size');
                const statusElement = variant.querySelector(`#status-${index}-${size}`);
                if (statusElement) {
                    statusElement.id = `status-${newIndex}-${size}`;
                }
            });

            // Update header
            const header = variant.querySelector('.card-header h6');
            if (header) {
                header.textContent = `Color #${newIndex + 1}`;
            }
        });

        colorVariantCounter = variants.length;
    }
}

// ========== STOCK CALCULATIONS ==========
function updateStockCalculations() {
    let totalStock = 0;
    const colorBreakdown = {};
    const sizeBreakdown = {};

    // Initialize size breakdown
    standardSizes.forEach(size => {
        sizeBreakdown[size] = 0;
    });

    // Collect all color variants
    const colorVariants = document.querySelectorAll('.color-size-variant-row');

    colorVariants.forEach((variant, colorIndex) => {
        const colorNameInput = variant.querySelector('.color-name');
        const colorName = colorNameInput.value.trim();
        const sizeInputs = variant.querySelectorAll('.size-stock-input');
        let colorTotal = 0;

        // Calculate color total
        sizeInputs.forEach(input => {
            const stock = parseInt(input.value) || 0;
            const size = input.getAttribute('data-size');

            colorTotal += stock;
            sizeBreakdown[size] += stock;

            // Update status badge
            const statusElement = document.getElementById(`status-${colorIndex}-${size}`);
            if (statusElement) {
                if (stock > 10) {
                    statusElement.innerHTML = '<span class="badge badge-success">In Stock</span>';
                } else if (stock > 0) {
                    statusElement.innerHTML = '<span class="badge badge-warning">Low Stock</span>';
                } else {
                    statusElement.innerHTML = '<span class="badge badge-secondary">Out of Stock</span>';
                }
            }
        });

        // Update color total display
        const colorTotalInput = variant.querySelector('.color-total-stock');
        if (colorTotalInput) {
            colorTotalInput.value = colorTotal;
        }

        // Add to color breakdown
        if (colorName) {
            colorBreakdown[colorName] = colorTotal;
        }

        totalStock += colorTotal;
    });

    // Build aggregated variants array for hidden input
    const variantsArray = [];
    colorVariants.forEach((variant, colorIndex) => {
        const colorName = variant.querySelector('.color-name').value.trim();
        if (colorName) {
            const sizeInputs = variant.querySelectorAll('.size-stock-input');
            sizeInputs.forEach(input => {
                const stock = parseInt(input.value) || 0;
                const size = input.getAttribute('data-size');
                if (stock > 0) {
                    variantsArray.push({
                        color: colorName,
                        size: size,
                        stock: stock
                    });
                }
            });
        }
    });

    // Update hidden inputs
    document.getElementById('variants-input').value = JSON.stringify(variantsArray);
    document.getElementById('total-stock-input').value = totalStock;

    // Update summary displays
    const totalStockElement = document.getElementById('total-stock-summary');
    if (totalStockElement) {
        totalStockElement.textContent = totalStock.toLocaleString();
    }

    // Update color breakdown
    const colorSummary = document.getElementById('color-breakdown-summary');
    if (colorSummary) {
        if (Object.keys(colorBreakdown).length > 0) {
            let colorHtml = '';
            for (const [color, qty] of Object.entries(colorBreakdown)) {
                colorHtml += `<div><small>${color}: <strong>${qty.toLocaleString()}</strong></small></div>`;
            }
            colorSummary.innerHTML = colorHtml;
        } else {
            colorSummary.innerHTML = '<small class="text-muted">No colors added</small>';
        }
    }

    // Update size breakdown
    const sizeSummary = document.getElementById('size-breakdown-summary');
    if (sizeSummary) {
        let hasSizeStock = false;
        let sizeHtml = '';

        for (const [size, qty] of Object.entries(sizeBreakdown)) {
            if (qty > 0) {
                hasSizeStock = true;
                sizeHtml += `<div><small>${size}: <strong>${qty.toLocaleString()}</strong></small></div>`;
            }
        }

        if (hasSizeStock) {
            sizeSummary.innerHTML = sizeHtml;
        } else {
            sizeSummary.innerHTML = '<small class="text-muted">No stock allocated</small>';
        }
    }

    // Update validation message
    const validationMsg = document.getElementById('stock-validation-message');
    if (validationMsg) {
        if (totalStock === 0) {
            validationMsg.innerHTML = '<small class="text-warning"><i class="fas fa-exclamation-triangle"></i> No stock added yet</small>';
        } else if (totalStock > 1000) {
            validationMsg.innerHTML = `<small class="text-warning"><i class="fas fa-exclamation-triangle"></i> High stock quantity (${totalStock.toLocaleString()} units)</small>`;
        } else {
            validationMsg.innerHTML = `<small class="text-success"><i class="fas fa-check-circle"></i> Stock management ready (${totalStock.toLocaleString()} units)</small>`;
        }
    }
}

// ========== GARMENT TYPE FUNCTIONS ==========
function updateGarmentTypeOptions() {
    const categorySelect = document.getElementById('category_id');
    const garmentTypeSelect = document.getElementById('garment_type');
    const categoryId = categorySelect.value;

    if (!garmentTypeSelect) return;

    garmentTypeSelect.innerHTML = '<option value="">Select Garment Type</option>';

    if (!categoryId) {
        garmentTypeSelect.innerHTML += '<option value="" disabled>Select a category first</option>';
        return;
    }

    // Get garment types for the selected category
    const availableGarmentTypes = categoryToGarmentTypes[categoryId] || {};

    if (Object.keys(availableGarmentTypes).length === 0) {
        garmentTypeSelect.innerHTML += '<option value="" disabled>No garment types available for this category</option>';
        return;
    }

    // Add available garment types
    for (const [key, name] of Object.entries(availableGarmentTypes)) {
        const option = document.createElement('option');
        option.value = key;
        option.textContent = name;

        // Preselect if previously selected
        const currentGarmentType = '{{ old('garment_type', $item->garment_type ?? '') }}';
        if (key === currentGarmentType) {
            option.selected = true;
        }

        garmentTypeSelect.appendChild(option);
    }

    // Trigger sizing section update if a garment type is selected
    if (garmentTypeSelect.value) {
        updateSizingSection();
    }
}

function updateSizingSection() {
    const garmentTypeSelect = document.getElementById('garment_type');
    const garmentType = garmentTypeSelect.value;
    const measurementGrid = document.getElementById('measurement-grid');
    const fitCharacteristics = document.getElementById('fit-characteristics');

    if (!garmentType || !measurementGrid || !fitCharacteristics) return;

    if (!garmentType) {
        measurementGrid.style.display = 'none';
        fitCharacteristics.style.display = 'none';
        return;
    }

    const garmentData = garmentTypes[garmentType];
    if (!garmentData) return;

    // Show measurement sections if garment type has measurements
    if (garmentData.measurements && garmentData.measurements.length > 0) {
        measurementGrid.style.display = 'block';
        fitCharacteristics.style.display = 'block';

        // Update measurement table header
        const tableHead = document.querySelector('#measurement-grid thead tr');
        if (tableHead) {
            tableHead.innerHTML = '<th>Size</th>';

            garmentData.measurements.forEach(measurement => {
                const th = document.createElement('th');
                th.textContent = formatMeasurementName(measurement);
                th.title = getMeasurementDescription(measurement);
                tableHead.appendChild(th);
            });

            // Update measurement table rows
            const tableBody = document.getElementById('measurement-rows');
            if (tableBody) {
                tableBody.innerHTML = '';

                standardSizes.forEach(size => {
                    const row = document.createElement('tr');

                    // Size column
                    const sizeCell = document.createElement('td');
                    sizeCell.innerHTML = `<strong>${size}</strong>`;
                    row.appendChild(sizeCell);

                    // Measurement columns
                    garmentData.measurements.forEach(measurement => {
                        const cell = document.createElement('td');
                        const input = document.createElement('input');
                        input.type = 'number';
                        input.step = '0.1';
                        input.min = '0';
                        input.className = 'form-control form-control-sm';
                        input.name = `sizes[${size}][${measurement}]`;
                        input.placeholder = 'cm';

                        // Set existing value if editing - check both old form data and existing item data
                        const oldValue = getOldMeasurementValue(size, measurement);
                        if (oldValue !== null && oldValue !== '') {
                            input.value = oldValue;
                        }

                        cell.appendChild(input);
                        row.appendChild(cell);
                    });

                    tableBody.appendChild(row);
                });
            }
        }
    } else {
        measurementGrid.style.display = 'none';
        fitCharacteristics.style.display = 'none';
    }
}

// Helper function to get measurement value from old form data or existing item data
function getOldMeasurementValue(size, measurement) {
    // First check for old form data (in case of validation errors)
    const oldDataKey = `sizes.${size}.${measurement}`;
    const oldFormValue = getNestedValue(@json(old()), oldDataKey);
    if (oldFormValue !== null && oldFormValue !== '') {
        return oldFormValue;
    }

    // Then check existing item measurements
    if (existingMeasurements && existingMeasurements[size] && existingMeasurements[size][measurement]) {
        return existingMeasurements[size][measurement];
    }

    return null;
}

// Helper function to get nested values from object using dot notation
function getNestedValue(obj, path) {
    return path.split('.').reduce((current, key) => {
        return current && current[key] !== undefined ? current[key] : null;
    }, obj);
}

function formatMeasurementName(measurement) {
    return measurement.split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

function getMeasurementDescription(measurement) {
    const descriptions = {
        'chest_circumference': 'Measure around the fullest part of chest',
        'waist_circumference': 'Measure around natural waistline',
        'hips_circumference': 'Measure around the fullest part of hips',
        'garment_length': 'Measure from highest point of shoulder to bottom hem',
        'sleeve_length': 'Measure from shoulder seam to cuff',
        'shoulder_width': 'Measure from shoulder seam to shoulder seam',
        'inseam_length': 'Measure from crotch to bottom of leg',
        'thigh_circumference': 'Measure around fullest part of thigh',
        'leg_opening': 'Measure circumference of leg opening',
        'rise': 'Measure from crotch to top of waistband',
        'collar_size': 'Measure around neck where collar sits',
        'short_length': 'Measure from waist to bottom of shorts',
        'dress_length': 'Measure from shoulder to bottom hem of dress',
        'shoulder_to_hem': 'Measure from shoulder to hem of dress',
        'skirt_length': 'Measure from waist to bottom hem of skirt',
        'bicep_circumference': 'Measure around fullest part of bicep',
        'hood_height': 'Measure from neckline to top of hood',
        'underbust_circumference': 'Measure around chest under bust',
        'cup_size': 'Bra cup size (A, B, C, etc.)',
        'foot_length': 'Measure length of foot',
        'foot_width': 'Measure width of foot',
        'calf_circumference': 'Measure around fullest part of calf',
        'sock_height': 'Measure height of sock from ankle',
        'bag_width': 'Measure width of bag',
        'bag_height': 'Measure height of bag',
        'bag_depth': 'Measure depth of bag',
        'strap_length': 'Measure length of strap',
        'handle_length': 'Measure length of handle',
        'chain_length': 'Measure length of chain',
        'bracelet_circumference': 'Measure around wrist for bracelet',
        'head_circumference': 'Measure around head',
        'brim_width': 'Measure width of hat brim',
        'hat_height': 'Measure height of hat'
    };

    return descriptions[measurement] || 'Garment measurement';
}

// ========== IMAGE UPLOAD FUNCTIONALITY ==========
const dropzoneArea = document.getElementById('dropzone-area');
const dropzoneInput = document.getElementById('images');
const previewContainer = document.getElementById('image-preview-container');
const previewsContainer = document.getElementById('image-previews');

// Only initialize if elements exist
if (dropzoneArea && dropzoneInput) {
    // Drag and drop events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        dropzoneArea.classList.add('dragover');
    }

    function unhighlight() {
        dropzoneArea.classList.remove('dragover');
    }

    // Handle drop
    dropzoneArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    // Handle file input change
    dropzoneInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    // Handle click on dropzone area
    dropzoneArea.addEventListener('click', function() {
        dropzoneInput.click();
    });

    // Process selected files
    function handleFiles(newFiles) {
        if (newFiles.length > 0) {
            // Get existing files
            const existingFiles = Array.from(dropzoneInput.files);

            // Combine existing and new files
            const allFiles = [...existingFiles, ...newFiles];

            // Update the file input
            const dataTransfer = new DataTransfer();
            allFiles.forEach(file => dataTransfer.items.add(file));
            dropzoneInput.files = dataTransfer.files;

            updatePreviews(allFiles);
        }
    }

    // Update image previews
    function updatePreviews(files) {
        if (!previewsContainer) return;

        // Clear existing previews
        previewsContainer.innerHTML = '';

        if (files.length > 0) {
            if (previewContainer) previewContainer.style.display = 'block';

            // Update dropzone appearance
            dropzoneArea.classList.add('has-files');

            // Add or update file count badge
            let fileCountBadge = dropzoneArea.querySelector('.file-count');
            if (!fileCountBadge) {
                fileCountBadge = document.createElement('div');
                fileCountBadge.className = 'file-count';
                dropzoneArea.appendChild(fileCountBadge);
            }
            fileCountBadge.textContent = files.length;

            Array.from(files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-lg-2 col-md-3 col-sm-4 col-6 preview-col';

                        const preview = document.createElement('div');
                        preview.className = 'image-preview';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Preview ' + (index + 1);
                        img.className = 'img-thumbnail';

                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'remove-image';
                        removeBtn.innerHTML = '×';
                        removeBtn.title = 'Remove image';

                        const primaryBadge = document.createElement('div');
                        primaryBadge.className = 'primary-badge';
                        primaryBadge.textContent = 'New';

                        const info = document.createElement('div');
                        info.className = 'image-info';
                        info.textContent = file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name;
                        info.title = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';

                        // Remove image functionality
                        removeBtn.addEventListener('click', function() {
                            removeImageFromInput(file, col);
                        });

                        preview.appendChild(img);
                        preview.appendChild(removeBtn);
                        preview.appendChild(primaryBadge);
                        preview.appendChild(info);
                        col.appendChild(preview);
                        previewsContainer.appendChild(col);
                    };

                    reader.readAsDataURL(file);
                }
            });
        } else {
            if (previewContainer) previewContainer.style.display = 'none';
            dropzoneArea.classList.remove('has-files');
            const fileCountBadge = dropzoneArea.querySelector('.file-count');
            if (fileCountBadge) {
                fileCountBadge.remove();
            }
        }
    }

    // Remove image from file input and preview
    function removeImageFromInput(fileToRemove, previewElement) {
        const files = Array.from(dropzoneInput.files);
        const updatedFiles = files.filter(file => file !== fileToRemove);

        // Create new FileList
        const dataTransfer = new DataTransfer();
        updatedFiles.forEach(file => dataTransfer.items.add(file));
        dropzoneInput.files = dataTransfer.files;

        // Update previews
        if (updatedFiles.length > 0) {
            updatePreviews(updatedFiles);
        } else {
            if (previewContainer) previewContainer.style.display = 'none';
            dropzoneArea.classList.remove('has-files');
            const fileCountBadge = dropzoneArea.querySelector('.file-count');
            if (fileCountBadge) {
                fileCountBadge.remove();
            }
        }
    }
}

// ========== IMAGE REORDERING FUNCTIONALITY ==========
function initializeImageReordering() {
    const existingImages = document.querySelectorAll('.existing-image');

    existingImages.forEach(image => {
        image.addEventListener('dragstart', function(e) {
            draggedImage = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
        });

        image.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            draggedImage = null;
            document.querySelectorAll('.existing-image').forEach(img => {
                img.classList.remove('drag-over');
            });
        });

        image.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            this.classList.add('drag-over');
        });

        image.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });

        image.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            if (draggedImage && draggedImage !== this) {
                const container = this.parentNode.parentNode;
                const allImages = Array.from(container.querySelectorAll('.preview-col'));
                const draggedIndex = allImages.findIndex(col => col.contains(draggedImage));
                const targetIndex = allImages.findIndex(col => col.contains(this));

                if (draggedIndex < targetIndex) {
                    container.insertBefore(draggedImage.parentNode, this.parentNode.nextSibling);
                } else {
                    container.insertBefore(draggedImage.parentNode, this.parentNode);
                }

                // Update order via AJAX
                updateImageOrder();
            }
        });
    });
}

// Update image order via AJAX
function updateImageOrder() {
    const container = document.getElementById('existing-images-container');
    if (!container) return;

    const imageElements = container.querySelectorAll('.existing-image');
    const imageIds = Array.from(imageElements).map(el => el.dataset.imageId);

    fetch(`/store-admin/items/{{ $item->id }}/images/reorder`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            image_ids: imageIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update order numbers in UI
            imageElements.forEach((el, index) => {
                const orderBadge = el.querySelector('.order-badge');
                if (orderBadge) {
                    orderBadge.textContent = `#${index + 1}`;
                }
            });
            showToast('Images reordered successfully', 'success');
        } else {
            showToast('Failed to reorder images', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to reorder images', 'error');
    });
}

// Remove existing image via AJAX
document.addEventListener('click', function(e) {
    const target = e.target.classList.contains('remove-existing-image') ? e.target : e.target.closest('.remove-existing-image');
    if (target) {
        const imageId = target.dataset.imageId;
        const itemId = target.dataset.itemId;

        Swal.fire({
            title: 'Are you sure?',
            text: "This image will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/store-admin/items/${itemId}/images/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        target.closest('.preview-col').remove();
                        showToast('Image deleted successfully', 'success');
                        // Reinitialize reordering after removal
                        initializeImageReordering();
                    } else {
                        showToast(data.message || 'Failed to delete image', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Failed to delete image', 'error');
                });
            }
        });
    }
});

// Set primary image via AJAX
document.addEventListener('click', function(e) {
    const target = e.target.classList.contains('set-primary-image') ? e.target : e.target.closest('.set-primary-image');
    if (target) {
        const imageId = target.dataset.imageId;
        const itemId = target.dataset.itemId;

        fetch(`/store-admin/items/${itemId}/images/${imageId}/set-primary`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI to reflect new primary image
                document.querySelectorAll('.primary-badge').forEach(badge => {
                    badge.remove();
                });
                document.querySelectorAll('.set-primary-image').forEach(btn => {
                    btn.style.display = 'block';
                });

                // Add primary badge to the new primary image and hide its set-primary button
                const primaryImageContainer = target.closest('.image-preview');
                const setPrimaryBtn = primaryImageContainer.querySelector('.set-primary-image');
                if (setPrimaryBtn) setPrimaryBtn.style.display = 'none';

                const primaryBadge = document.createElement('div');
                primaryBadge.className = 'primary-badge';
                primaryBadge.textContent = 'Primary';
                primaryImageContainer.appendChild(primaryBadge);

                showToast('Primary image updated successfully', 'success');
            } else {
                showToast(data.message || 'Failed to set primary image', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to set primary image', 'error');
        });
    }
});

// ========== FORM VALIDATION ==========
document.getElementById('itemForm').addEventListener('submit', function(e) {
    // Validate color names
    const colorNames = new Set();
    let hasDuplicateColors = false;
    let hasValidColors = false;
    let hasEmptyColorName = false;

    document.querySelectorAll('.color-name').forEach(input => {
        const colorName = input.value.trim();
        if (colorName) {
            hasValidColors = true;
            const lowerColor = colorName.toLowerCase();
            if (colorNames.has(lowerColor)) {
                hasDuplicateColors = true;
            }
            colorNames.add(lowerColor);
        } else {
            hasEmptyColorName = true;
        }
    });

    // Color validation
    if (!hasValidColors) {
        e.preventDefault();
        Swal.fire('Error', 'Please add at least one color with a name.', 'error');
        return;
    }

    if (hasEmptyColorName) {
        e.preventDefault();
        Swal.fire('Error', 'All colors must have a name.', 'error');
        return;
    }

    if (hasDuplicateColors) {
        e.preventDefault();
        Swal.fire('Error', 'Please remove duplicate color names.', 'error');
        return;
    }

    // Stock validation
    const totalStock = parseInt(document.getElementById('total-stock-input').value) || 0;
    if (totalStock === 0) {
        e.preventDefault();
        Swal.fire('Error', 'Please add stock for at least one color-size combination.', 'error');
        return;
    }

    // Check if any size has negative stock (shouldn't happen with min=0 but just in case)
    let hasNegativeStock = false;
    document.querySelectorAll('.size-stock-input').forEach(input => {
        if (parseInt(input.value) < 0) {
            hasNegativeStock = true;
        }
    });

    if (hasNegativeStock) {
        e.preventDefault();
        Swal.fire('Error', 'Stock quantities cannot be negative.', 'error');
        return;
    }

    // Garment type validation
    const garmentTypeSelect = document.getElementById('garment_type');
    if (!garmentTypeSelect || !garmentTypeSelect.value) {
        e.preventDefault();
        Swal.fire('Error', 'Please select a garment type.', 'error');
        return;
    }

    // Store and category validation
    const storeSelect = document.getElementById('store_id');
    const categorySelect = document.getElementById('category_id');
    if (!storeSelect || !storeSelect.value || !categorySelect || !categorySelect.value) {
        e.preventDefault();
        Swal.fire('Error', 'Please select both store and category.', 'error');
        return;
    }

    // Price validation
    const priceInput = document.getElementById('price');
    if (!priceInput || !priceInput.value || parseFloat(priceInput.value) < 0) {
        e.preventDefault();
        Swal.fire('Error', 'Please enter a valid price (0 or greater).', 'error');
        return;
    }

    // Name validation
    const nameInput = document.getElementById('name');
    if (!nameInput || !nameInput.value.trim()) {
        e.preventDefault();
        Swal.fire('Error', 'Please enter an item name.', 'error');
        return;
    }

    // If all validations pass, show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating Item...';
        submitBtn.disabled = true;
    }
});

// ========== DYNAMIC EVENT LISTENERS ==========
// Add event listeners for dynamic updates
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('color-name') || e.target.classList.contains('size-stock-input')) {
        updateStockCalculations();
    }
});

// Add event listeners for garment type change
const garmentTypeSelect = document.getElementById('garment_type');
if (garmentTypeSelect) {
    garmentTypeSelect.addEventListener('change', updateSizingSection);
}

// Add event listeners for category change
const categorySelect = document.getElementById('category_id');
if (categorySelect) {
    categorySelect.addEventListener('change', updateGarmentTypeOptions);
}

// Helper function for toast messages
function showToast(message, type = 'info') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });

    Toast.fire({
        icon: type,
        title: message
    });
}
</script>
@endpush
