@extends('cms.layouts.store-admin-app')

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

                    <!-- Basic Information -->
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

                    <!-- Image Upload Section -->
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

                    <!-- Include Color & Size Variants Partial -->
                    @include('cms.pages.items.partials.color-size-variants')

                    <!-- Include Sizing Data Partial (Garment Types & Measurements Only) -->
                    @include('cms.pages.items.partials.sizing-data')

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
/* Existing styles remain, add these additional styles */
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
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
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
    z-index: 1;
}

.dropzone-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
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
    z-index: 3;
}

#image-preview-container {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
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
    height: 140px; /* Increased height */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.image-preview:hover {
    border-color: #007bff;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.2);
}

.image-preview.dragging {
    opacity: 0.4;
    border-color: #007bff;
    transform: scale(0.98);
}

.image-preview img {
    max-width: 100%;
    max-height: 80px; /* Increased height */
    object-fit: contain;
    border-radius: 4px;
    margin-bottom: 8px;
}

.remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
    line-height: 1;
    transition: all 0.2s ease;
    z-index: 2;
}

.remove-image:hover {
    background: #dc3545;
    transform: scale(1.1);
}

.primary-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(40, 167, 69, 0.9);
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: bold;
    z-index: 2;
}

.image-info {
    margin-top: 5px;
    font-size: 11px;
    color: #6c757d;
    word-break: break-all;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    padding: 0 5px;
}

/* Make preview columns responsive */
.preview-col {
    padding: 8px;
    transition: all 0.3s ease;
}

.preview-col.drag-over {
    border: 2px dashed #007bff;
    background: rgba(0, 123, 255, 0.05);
}

/* Color variants styling */
.color-variant-row {
    border: 1px solid #e3e6f0;
}

.color-variant-row .card-body {
    padding: 1rem;
}

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

/* Error and success states */
.is-invalid {
    border-color: #dc3545 !important;
}

.invalid-feedback {
    display: block;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .preview-col {
        flex: 0 0 50%;
        max-width: 50%;
    }

    .image-preview {
        height: 130px;
    }

    .image-preview img {
        max-height: 70px;
    }
}

@media (max-width: 576px) {
    .preview-col {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .dropzone-area {
        padding: 30px 15px;
        min-height: 150px;
    }

    .dropzone-content h5 {
        font-size: 16px;
    }
}

/* Additional styles for store admin */
.border-right {
    border-right: 1px solid #e3e6f0 !important;
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
// Standard sizes array from PHP
const standardSizes = @json($standardSizes);
const garmentTypes = @json($garmentTypes);
const categoryToGarmentTypes = @json($categoryToGarmentTypes);
const existingMeasurements = @json(isset($item) && $item->sizing_data ? $item->garment_measurements : []);

let colorVariantCounter = {{ old('color_variants') ? count(old('color_variants')) : 1 }};
let draggedImageIndex = null;

// ========== IMAGE UPLOAD VARIABLES ==========
const dropzoneArea = document.getElementById('dropzone-area');
const dropzoneInput = document.getElementById('images');
const previewContainer = document.getElementById('image-preview-container');
const previewsContainer = document.getElementById('image-previews');

let selectedFiles = []; // Store all selected files

// ========== INITIALIZATION ==========
document.addEventListener('DOMContentLoaded', function() {
    // Initialize stock calculations
    updateStockCalculations();

    // Initialize image upload functionality
    initializeImageUpload();

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

    // Initialize event listeners for existing elements
    initializeEventListeners();
});

// ========== IMAGE UPLOAD FUNCTIONALITY ==========
function initializeImageUpload() {
    if (!dropzoneArea || !dropzoneInput) return;

    // Drag and drop events for the dropzone
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, highlightDropzone, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzoneArea.addEventListener(eventName, unhighlightDropzone, false);
    });

    function highlightDropzone() {
        dropzoneArea.classList.add('dragover');
    }

    function unhighlightDropzone() {
        dropzoneArea.classList.remove('dragover');
    }

    // Handle drop
    dropzoneArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = Array.from(dt.files);
        handleFiles(files);
    }

    // Handle file input change
    dropzoneInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        handleFiles(files);
    });

    // Handle click on dropzone area
    dropzoneArea.addEventListener('click', function() {
        dropzoneInput.click();
    });

    // Initialize with any existing files (for form re-submission)
    if (dropzoneInput.files && dropzoneInput.files.length > 0) {
        selectedFiles = Array.from(dropzoneInput.files);
        updatePreviews();
    }
}

// Process selected files
function handleFiles(newFiles) {
    if (newFiles.length === 0) return;

    // Filter only valid image files
    const validImageFiles = newFiles.filter(file => {
        const isValidImage = file.type.startsWith('image/');
        const isValidSize = file.size <= 5 * 1024 * 1024; // 5MB limit
        const isValidExtension = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'].includes(file.type);

        return isValidImage && isValidSize && isValidExtension;
    });

    if (validImageFiles.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Files',
            text: 'Please select only image files (JPEG, PNG, GIF, WebP) under 5MB each.',
            confirmButtonColor: '#007bff'
        });
        return;
    }

    // Check total file limit (max 10 images)
    const currentFileCount = selectedFiles.length;
    const remainingSlots = 10 - currentFileCount;

    if (remainingSlots <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Maximum Reached',
            text: 'Maximum 10 images allowed. Please remove some images first.',
            confirmButtonColor: '#007bff'
        });
        return;
    }

    const filesToAdd = validImageFiles.slice(0, remainingSlots);

    // Add new files, avoiding duplicates
    filesToAdd.forEach(newFile => {
        const isDuplicate = selectedFiles.some(existingFile =>
            existingFile.name === newFile.name &&
            existingFile.size === newFile.size &&
            existingFile.lastModified === newFile.lastModified
        );

        if (!isDuplicate) {
            selectedFiles.push(newFile);
        }
    });

    // Show info if some files were skipped
    if (validImageFiles.length > remainingSlots) {
        Swal.fire({
            icon: 'info',
            title: 'Some Files Skipped',
            text: `${filesToAdd.length} images added. ${validImageFiles.length - filesToAdd.length} images skipped (maximum 10 allowed).`,
            confirmButtonColor: '#007bff',
            timer: 3000
        });
    }

    // Update the file input and previews
    updateFileInput();
    updatePreviews();
}

// Update the actual file input
function updateFileInput() {
    if (!dropzoneInput) return;

    // Create new FileList using DataTransfer
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    dropzoneInput.files = dataTransfer.files;
}

// Update image previews
function updatePreviews() {
    if (!previewsContainer || !previewContainer) return;

    // Clear existing previews
    previewsContainer.innerHTML = '';

    if (selectedFiles.length > 0) {
        // Show preview container
        previewContainer.style.display = 'block';

        // Update dropzone appearance
        if (dropzoneArea) {
            dropzoneArea.classList.add('has-files');

            // Add or update file count badge
            let fileCountBadge = dropzoneArea.querySelector('.file-count');
            if (!fileCountBadge) {
                fileCountBadge = document.createElement('div');
                fileCountBadge.className = 'file-count';
                dropzoneArea.appendChild(fileCountBadge);
            }
            fileCountBadge.textContent = selectedFiles.length;
            fileCountBadge.title = `${selectedFiles.length} image${selectedFiles.length !== 1 ? 's' : ''} selected`;
        }

        // Create previews for each file
        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();

            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-lg-2 col-md-3 col-sm-4 col-6 preview-col';
                col.setAttribute('data-file-index', index);

                const preview = document.createElement('div');
                preview.className = 'image-preview';
                preview.setAttribute('draggable', 'true');
                preview.setAttribute('data-index', index);

                // Image element
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = `Preview ${index + 1}`;
                img.className = 'img-thumbnail';

                // Remove button
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-image';
                removeBtn.innerHTML = '×';
                removeBtn.title = 'Remove this image';

                // Primary badge for first image
                const primaryBadge = document.createElement('div');
                primaryBadge.className = 'primary-badge';
                primaryBadge.textContent = index === 0 ? 'Primary' : '';
                primaryBadge.title = index === 0 ? 'This will be the primary image' : '';

                // File info
                const info = document.createElement('div');
                info.className = 'image-info';

                // Format file name and size
                const fileName = file.name.length > 20 ? file.name.substring(0, 17) + '...' : file.name;
                const fileSize = (file.size / 1024).toFixed(1);
                info.textContent = `${fileName}`;
                info.title = `${file.name} (${fileSize} KB)`;

                // Remove image functionality
                removeBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Remove Image?',
                        text: `Are you sure you want to remove "${file.name}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, remove it',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            removeImageFromInput(index);
                        }
                    });
                });

                // Drag and drop for reordering
                preview.addEventListener('dragstart', handleImageDragStart);
                preview.addEventListener('dragover', handleImageDragOver);
                preview.addEventListener('dragleave', handleImageDragLeave);
                preview.addEventListener('drop', handleImageDrop);
                preview.addEventListener('dragend', handleImageDragEnd);

                // Assemble preview
                preview.appendChild(img);
                preview.appendChild(removeBtn);
                preview.appendChild(primaryBadge);
                preview.appendChild(info);
                col.appendChild(preview);
                previewsContainer.appendChild(col);
            };

            reader.onerror = function() {
                console.error('Error reading file:', file.name);
            };

            reader.readAsDataURL(file);
        });
    } else {
        // No files selected
        previewContainer.style.display = 'none';
        if (dropzoneArea) {
            dropzoneArea.classList.remove('has-files');
            const fileCountBadge = dropzoneArea.querySelector('.file-count');
            if (fileCountBadge) {
                fileCountBadge.remove();
            }
        }
    }
}

// Remove image from selection
function removeImageFromInput(fileIndex) {
    if (fileIndex < 0 || fileIndex >= selectedFiles.length) return;

    // Remove file from array
    selectedFiles.splice(fileIndex, 1);

    // Update the file input
    updateFileInput();

    // Update previews
    updatePreviews();
}

// ========== IMAGE DRAG & DROP FOR REORDERING ==========
function handleImageDragStart(e) {
    draggedImageIndex = parseInt(e.target.closest('.image-preview').getAttribute('data-index'));
    e.target.closest('.image-preview').classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', draggedImageIndex);
}

function handleImageDragOver(e) {
    e.preventDefault();
    const targetPreview = e.target.closest('.image-preview');
    if (targetPreview) {
        targetPreview.parentElement.classList.add('drag-over');
    }
}

function handleImageDragLeave(e) {
    const targetPreview = e.target.closest('.image-preview');
    if (targetPreview) {
        targetPreview.parentElement.classList.remove('drag-over');
    }
}

function handleImageDrop(e) {
    e.preventDefault();

    const targetPreview = e.target.closest('.image-preview');
    if (!targetPreview || draggedImageIndex === null) return;

    const targetIndex = parseInt(targetPreview.getAttribute('data-index'));

    if (draggedImageIndex !== targetIndex) {
        // Reorder files
        const [movedFile] = selectedFiles.splice(draggedImageIndex, 1);
        selectedFiles.splice(targetIndex, 0, movedFile);

        // Update the file input
        updateFileInput();

        // Update previews
        updatePreviews();
    }

    // Remove drag-over class
    const allCols = document.querySelectorAll('.preview-col');
    allCols.forEach(col => col.classList.remove('drag-over'));
}

function handleImageDragEnd(e) {
    // Remove dragging class
    const draggingPreview = document.querySelector('.image-preview.dragging');
    if (draggingPreview) {
        draggingPreview.classList.remove('dragging');
    }

    // Remove drag-over classes
    const allCols = document.querySelectorAll('.preview-col');
    allCols.forEach(col => col.classList.remove('drag-over'));

    draggedImageIndex = null;
}

// ========== COLOR-SIZE VARIANT MANAGEMENT ==========
function addColorSizeVariant() {
    const container = document.getElementById('color-size-variants-container');
    const firstVariant = container.querySelector('.color-size-variant-row');

    if (!firstVariant) {
        console.error('No color variant template found');
        return;
    }

    const newIndex = colorVariantCounter++;

    // Clone the first variant
    const newVariant = firstVariant.cloneNode(true);

    // Clear the color name
    const colorNameInput = newVariant.querySelector('.color-name');
    if (colorNameInput) {
        colorNameInput.value = '';
        colorNameInput.name = `color_variants[${newIndex}][name]`;
    }

    // Update indices in size stock inputs
    const sizeInputs = newVariant.querySelectorAll('.size-stock-input');
    sizeInputs.forEach((input, sizeIndex) => {
        const size = standardSizes[sizeIndex];
        input.value = 0;
        input.name = `color_variants[${newIndex}][size_stock][${size}]`;
        input.setAttribute('data-color-index', newIndex);
        input.setAttribute('data-size', size);

        // Clear any old event listeners and add new one
        input.replaceWith(input.cloneNode(true));
        const newInput = newVariant.querySelectorAll('.size-stock-input')[sizeIndex];
        newInput.addEventListener('input', updateStockCalculations);
    });

    // Update status badge IDs
    const statusBadges = newVariant.querySelectorAll('.stock-status');
    statusBadges.forEach((badge, sizeIndex) => {
        const size = standardSizes[sizeIndex];
        badge.id = `status-${newIndex}-${size}`;
        badge.innerHTML = '<span class="badge badge-secondary">Out of Stock</span>';
    });

    // Update color total input
    const colorTotalInput = newVariant.querySelector('.color-total-stock');
    if (colorTotalInput) {
        colorTotalInput.value = 0;
    }

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

        // Remove old event listener and add new one
        removeBtn.replaceWith(removeBtn.cloneNode(true));
        const newRemoveBtn = newVariant.querySelector('.remove-color-size-variant');
        newRemoveBtn.addEventListener('click', function() {
            removeColorSizeVariant(newIndex);
        });
    }

    // Add new color name event listener
    if (colorNameInput) {
        colorNameInput.addEventListener('input', updateStockCalculations);
    }

    container.appendChild(newVariant);
    updateStockCalculations();
}

function removeColorSizeVariant(index) {
    const variants = document.querySelectorAll('.color-size-variant-row');
    if (variants.length <= 1) {
        Swal.fire('Info', 'You must have at least one color variant.', 'info');
        return;
    }

    const variantToRemove = Array.from(variants).find(variant => {
        const sizeInput = variant.querySelector('.size-stock-input');
        return sizeInput && parseInt(sizeInput.getAttribute('data-color-index')) === index;
    });

    if (variantToRemove) {
        variantToRemove.remove();

        // Re-index remaining variants
        const remainingVariants = document.querySelectorAll('.color-size-variant-row');
        remainingVariants.forEach((variant, newIndex) => {
            const colorNameInput = variant.querySelector('.color-name');
            const sizeInputs = variant.querySelectorAll('.size-stock-input');
            const statusBadges = variant.querySelectorAll('.stock-status');

            // Update color name input
            if (colorNameInput) {
                colorNameInput.name = `color_variants[${newIndex}][name]`;
            }

            // Update size inputs
            sizeInputs.forEach((input, sizeIdx) => {
                const size = standardSizes[sizeIdx];
                input.name = `color_variants[${newIndex}][size_stock][${size}]`;
                input.setAttribute('data-color-index', newIndex);
            });

            // Update status badges
            statusBadges.forEach((badge, sizeIdx) => {
                const size = standardSizes[sizeIdx];
                badge.id = `status-${newIndex}-${size}`;
            });

            // Update header
            const header = variant.querySelector('.card-header h6');
            if (header) {
                header.textContent = `Color #${newIndex + 1}`;
            }

            // Update remove button for first variant
            const removeBtn = variant.querySelector('.remove-color-size-variant');
            if (removeBtn) {
                if (newIndex === 0) {
                    removeBtn.style.display = 'none';
                    const textRightDiv = removeBtn.closest('.text-right');
                    if (textRightDiv && !textRightDiv.querySelector('small')) {
                        const small = document.createElement('small');
                        small.className = 'text-muted';
                        small.textContent = 'First color cannot be removed';
                        textRightDiv.appendChild(small);
                    }
                } else {
                    removeBtn.style.display = 'block';
                }
            }
        });

        colorVariantCounter = remainingVariants.length;
        updateStockCalculations();
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

    colorVariants.forEach((variant) => {
        const colorNameInput = variant.querySelector('.color-name');
        const colorName = colorNameInput ? colorNameInput.value.trim() : '';
        const sizeInputs = variant.querySelectorAll('.size-stock-input');
        let colorTotal = 0;

        // Get the color index from first size input
        const firstSizeInput = sizeInputs[0];
        const colorIndex = firstSizeInput ? firstSizeInput.getAttribute('data-color-index') : '0';

        // Calculate color total
        sizeInputs.forEach(input => {
            const stock = parseInt(input.value) || 0;
            const size = input.getAttribute('data-size');

            if (size) {
                colorTotal += stock;
                sizeBreakdown[size] = (sizeBreakdown[size] || 0) + stock;

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
    colorVariants.forEach((variant) => {
        const colorNameInput = variant.querySelector('.color-name');
        const colorName = colorNameInput ? colorNameInput.value.trim() : '';

        if (colorName) {
            const sizeInputs = variant.querySelectorAll('.size-stock-input');
            sizeInputs.forEach(input => {
                const stock = parseInt(input.value) || 0;
                const size = input.getAttribute('data-size');
                if (stock > 0 && size) {
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
    const variantsInput = document.getElementById('variants-input');
    const totalStockInput = document.getElementById('total-stock-input');

    if (variantsInput) {
        variantsInput.value = JSON.stringify(variantsArray);
    }

    if (totalStockInput) {
        totalStockInput.value = totalStock;
    }

    // Update summary displays
    const totalStockSummary = document.getElementById('total-stock-summary');
    const colorSummary = document.getElementById('color-breakdown-summary');
    const sizeSummary = document.getElementById('size-breakdown-summary');
    const validationMsg = document.getElementById('stock-validation-message');

    if (totalStockSummary) {
        totalStockSummary.textContent = totalStock.toLocaleString();
    }

    // Update color breakdown
    if (colorSummary) {
        if (Object.keys(colorBreakdown).length > 0) {
            let colorHtml = '';
            for (const [color, qty] of Object.entries(colorBreakdown)) {
                if (qty > 0) {
                    colorHtml += `<div><small>${color}: <strong>${qty.toLocaleString()}</strong></small></div>`;
                }
            }
            colorSummary.innerHTML = colorHtml || '<small class="text-muted">No stock allocated to colors</small>';
        } else {
            colorSummary.innerHTML = '<small class="text-muted">No colors added</small>';
        }
    }

    // Update size breakdown
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
            sizeSummary.innerHTML = '<small class="text-muted">No stock allocated by size</small>';
        }
    }

    // Update validation message
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

    if (!categorySelect || !garmentTypeSelect) return;

    const categoryId = categorySelect.value;

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
    const measurementGrid = document.getElementById('measurement-grid');
    const fitCharacteristics = document.getElementById('fit-characteristics');

    if (!garmentTypeSelect || !measurementGrid || !fitCharacteristics) return;

    const garmentType = garmentTypeSelect.value;

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
        }

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

// ========== FORM VALIDATION ==========
const itemForm = document.getElementById('itemForm');
if (itemForm) {
    itemForm.addEventListener('submit', function(e) {
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
        const totalStockInput = document.getElementById('total-stock-input');
        const totalStock = totalStockInput ? parseInt(totalStockInput.value) || 0 : 0;
        if (totalStock === 0) {
            e.preventDefault();
            Swal.fire('Error', 'Please add stock for at least one color-size combination.', 'error');
            return;
        }

        // Check if any size has negative stock
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

        // Image validation (at least one image)
        if (selectedFiles.length === 0) {
            e.preventDefault();
            Swal.fire('Error', 'Please upload at least one image for the item.', 'error');
            return;
        }

        // If all validations pass, show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Item...';
            submitBtn.disabled = true;
        }
    });
}

// ========== DYNAMIC EVENT LISTENERS ==========
// Initialize event listeners for existing elements
function initializeEventListeners() {
    // Add color button
    const addColorBtn = document.getElementById('add-color-size-variant');
    if (addColorBtn) {
        addColorBtn.addEventListener('click', addColorSizeVariant);
    }

    // Existing color name inputs
    document.querySelectorAll('.color-name').forEach(input => {
        input.addEventListener('input', updateStockCalculations);
    });

    // Existing size stock inputs
    document.querySelectorAll('.size-stock-input').forEach(input => {
        input.addEventListener('input', updateStockCalculations);
    });

    // Existing remove buttons
    document.querySelectorAll('.remove-color-size-variant').forEach(btn => {
        const variantRow = btn.closest('.color-size-variant-row');
        if (variantRow) {
            const sizeInput = variantRow.querySelector('.size-stock-input');
            if (sizeInput) {
                const index = sizeInput.getAttribute('data-color-index');
                btn.addEventListener('click', function() {
                    removeColorSizeVariant(parseInt(index));
                });
            }
        }
    });
}

// Add global event listeners for dynamic updates
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
</script>
@endpush
