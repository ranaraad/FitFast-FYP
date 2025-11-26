@extends('cms.layouts.app')

@section('page-title', 'Item Management')
@section('page-subtitle', 'Manage item inventory')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New Item</h1>
    <a href="{{ route('cms.items.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
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
                <form action="{{ route('cms.items.store') }}" method="POST" id="itemForm" enctype="multipart/form-data">
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

                    <!-- Image Upload Section - Dropzone Style -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary">Item Images</h5>
                            <small class="text-muted">Upload multiple images for this item. The first image will be set as primary. Drag & drop or click to select.</small>

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

                                <!-- Image Preview Container -->
                                <div id="image-preview-container" class="row mt-3" style="display: none;">
                                    <div class="col-12">
                                        <h6 class="text-muted mb-3">Selected Images</h6>
                                        <div class="row" id="image-previews"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Item
                            </button>
                            <a href="{{ route('cms.items.index') }}" class="btn btn-secondary">
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
}

.image-preview img {
    max-width: 100%;
    height: 80px; /* Smaller preview size */
    object-fit: cover;
    border-radius: 4px;
}

.remove-image {
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
}

.remove-image:hover {
    background: #dc3545;
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

/* Color variants styling */
.color-variant-row {
    border: 1px solid #e3e6f0;
}

.color-variant-row .card-body {
    padding: 1rem;
}
</style>
@endpush

@push('scripts')
<script>
const garmentTypesByCategory = @json($categoryToGarmentTypes);
let colorCounter = 1;

// Dropzone functionality
const dropzoneArea = document.getElementById('dropzone-area');
const dropzoneInput = document.getElementById('images');
const previewContainer = document.getElementById('image-preview-container');
const previewsContainer = document.getElementById('image-previews');

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

// Process selected files - FIXED: Now appends files instead of replacing
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
    // Clear existing previews
    previewsContainer.innerHTML = '';

    if (files.length > 0) {
        previewContainer.style.display = 'block';

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
                    primaryBadge.textContent = index === 0 ? 'Primary' : '';

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
        previewContainer.style.display = 'none';
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
        previewContainer.style.display = 'none';
        dropzoneArea.classList.remove('has-files');
        const fileCountBadge = dropzoneArea.querySelector('.file-count');
        if (fileCountBadge) {
            fileCountBadge.remove();
        }
    }
}

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
});

// Remove color variant
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-color')) {
        const colorRow = e.target.closest('.color-variant-row');
        if (document.querySelectorAll('.color-variant-row').length > 1) {
            colorRow.remove();
            // Trigger stock calculation update after removal
            if (typeof updateStockCalculations === 'function') {
                updateStockCalculations();
            }
        }
    }
});

// Form validation
document.getElementById('itemForm').addEventListener('submit', function(e) {
    // Validate color names
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

    // Validate stock consistency - use functions from the partial
    if (typeof calculateTotalColorStock === 'function' && typeof calculateTotalSizeStock === 'function') {
        const totalColorStock = calculateTotalColorStock();
        const totalSizeStock = calculateTotalSizeStock();

        if (totalColorStock !== totalSizeStock) {
            e.preventDefault();
            Swal.fire('Error', `Total color stock (${totalColorStock}) must match total size stock (${totalSizeStock}). Please adjust your stock levels.`, 'error');
            return;
        }
    }

    // Images are optional, no validation needed
});

// Listen for color stock changes and update calculations
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('color-stock')) {
        // Trigger stock calculation update
        if (typeof updateStockCalculations === 'function') {
            updateStockCalculations();
        }
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize stock calculations if the function exists
    if (typeof updateStockCalculations === 'function') {
        updateStockCalculations();
    }
});
</script>
@endpush
