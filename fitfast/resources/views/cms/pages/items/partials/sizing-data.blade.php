<div class="sizing-data-section card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Inventory & Sizing Data</h5>
        <small class="text-muted">Stock management for sizes and colors. Measurements are optional for AI recommendations.</small>
    </div>
    <div class="card-body">
        <!-- Stock Information Display -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> Stock Management</h6>
                    <p class="mb-0">
                        <strong>Total Color Stock:</strong> <span id="total-color-stock">0</span> units (from color variants)<br>
                        <strong>Total Size Stock:</strong> <span id="total-size-stock-display">0</span> units (across all sizes)<br>
                        <small class="text-muted" id="stock-validation-message">The total size stock should match the total color stock.</small>
                    </p>
                </div>
            </div>
        </div>

        <!-- Garment Type Selection -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="garment_type">Garment Type *</label>
                    <select class="form-control @error('garment_type') is-invalid @enderror"
                            id="garment_type" name="garment_type" required
                            onchange="updateSizingSection()">
                        <option value="">Select Garment Type</option>
                        @if(isset($item) && $item->category)
                            @php
                                $categoryGarmentTypes = \App\Models\Item::getGarmentTypesForCategory(strtolower($item->category->name));
                            @endphp
                            @foreach($categoryGarmentTypes as $key => $name)
                                <option value="{{ $key }}" {{ old('garment_type', $item->garment_type ?? '') == $key ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        @elseif(old('category_id'))
                            @foreach($categoryToGarmentTypes[old('category_id')] ?? [] as $key => $name)
                                <option value="{{ $key }}" {{ old('garment_type') == $key ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        @else
                            <option value="" disabled>Select a category first</option>
                        @endif
                    </select>
                    @error('garment_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Standard Sizes Section - Always shows all standard sizes -->
        <div class="row mb-4" id="standard-sizes-section">
            <div class="col-12">
                <h6>Stock by Size *</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Size</th>
                                <th>Stock Quantity *</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($standardSizes as $size)
                            <tr>
                                <td><strong>{{ $size }}</strong></td>
                                <td>
                                    <input type="number"
                                           name="size_stock[{{ $size }}]"
                                           class="form-control form-control-sm stock-quantity"
                                           value="{{ old("size_stock.$size", isset($item) ? $item->getSizeStock($size) : 0) }}"
                                           min="0"
                                           required
                                           onchange="updateStockCalculations()">
                                </td>
                                <td>
                                    <span class="stock-status" id="status-{{ $size }}">
                                        @php
                                            $stock = old("size_stock.$size", isset($item) ? $item->getSizeStock($size) : 0);
                                        @endphp
                                        @if($stock > 10)
                                            <span class="badge badge-success">In Stock</span>
                                        @elseif($stock > 0)
                                            <span class="badge badge-warning">Low Stock</span>
                                        @else
                                            <span class="badge badge-secondary">Out of Stock</span>
                                        @endif
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td><strong>Total Size Stock</strong></td>
                                <td colspan="2">
                                    <strong id="total-size-stock">0</strong> units
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Dynamic Measurement Grid (OPTIONAL) -->
        <div id="measurement-grid" style="display: none;">
            <h6>Garment Measurements (in centimeters) - Optional</h6>
            <small class="text-muted mb-3 d-block">
                These measurements improve AI size recommendations. Leave blank if unavailable.
            </small>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Size</th>
                            <!-- Measurement columns will be dynamically added here -->
                        </tr>
                    </thead>
                    <tbody id="measurement-rows">
                        <!-- Rows will be dynamically added here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Fit Characteristics -->
        <div class="row mt-4" id="fit-characteristics" style="display: none;">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="fit_type">Fit Type</label>
                    <select class="form-control" id="fit_type" name="fit_type">
                        <option value="slim" {{ (old('fit_type', $item->sizing_data['fit_characteristics']['fit_type'] ?? '') == 'slim') ? 'selected' : '' }}>Slim</option>
                        <option value="regular" {{ (old('fit_type', $item->sizing_data['fit_characteristics']['fit_type'] ?? 'regular') == 'regular') ? 'selected' : '' }}>Regular</option>
                        <option value="loose" {{ (old('fit_type', $item->sizing_data['fit_characteristics']['fit_type'] ?? '') == 'loose') ? 'selected' : '' }}>Loose</option>
                        <option value="oversized" {{ (old('fit_type', $item->sizing_data['fit_characteristics']['fit_type'] ?? '') == 'oversized') ? 'selected' : '' }}>Oversized</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="ease">Ease Level</label>
                    <select class="form-control" id="ease" name="ease">
                        <option value="tight" {{ (old('ease', $item->sizing_data['fit_characteristics']['ease'] ?? '') == 'tight') ? 'selected' : '' }}>Tight Fit</option>
                        <option value="fitted" {{ (old('ease', $item->sizing_data['fit_characteristics']['ease'] ?? '') == 'fitted') ? 'selected' : '' }}>Fitted</option>
                        <option value="standard" {{ (old('ease', $item->sizing_data['fit_characteristics']['ease'] ?? 'standard') == 'standard') ? 'selected' : '' }}>Standard</option>
                        <option value="relaxed" {{ (old('ease', $item->sizing_data['fit_characteristics']['ease'] ?? '') == 'relaxed') ? 'selected' : '' }}>Relaxed</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="stretch">Stretch Level</label>
                    <select class="form-control" id="stretch" name="stretch">
                        <option value="none" {{ (old('stretch', $item->sizing_data['fit_characteristics']['stretch'] ?? '') == 'none') ? 'selected' : '' }}>No Stretch</option>
                        <option value="low" {{ (old('stretch', $item->sizing_data['fit_characteristics']['stretch'] ?? '') == 'low') ? 'selected' : '' }}>Low Stretch</option>
                        <option value="medium" {{ (old('stretch', $item->sizing_data['fit_characteristics']['stretch'] ?? 'medium') == 'medium') ? 'selected' : '' }}>Medium Stretch</option>
                        <option value="high" {{ (old('stretch', $item->sizing_data['fit_characteristics']['stretch'] ?? '') == 'high') ? 'selected' : '' }}>High Stretch</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Store garment type data for JavaScript
const garmentTypes = @json($garmentTypes);
const standardSizes = @json($standardSizes);
const categoryToGarmentTypes = @json($categoryToGarmentTypes);

// Store existing measurements data for editing
const existingMeasurements = @json(isset($item) && $item->sizing_data ? $item->garment_measurements : []);

// Function to calculate total color stock from color variants
function calculateTotalColorStock() {
    let total = 0;
    document.querySelectorAll('.color-stock').forEach(input => {
        total += parseInt(input.value) || 0;
    });
    return total;
}

// Function to calculate total size stock from standard sizes
function calculateTotalSizeStock() {
    let total = 0;
    document.querySelectorAll('.stock-quantity').forEach(input => {
        total += parseInt(input.value) || 0;
    });
    return total;
}

// Function to update all stock calculations and validations
function updateStockCalculations() {
    const totalColorStock = calculateTotalColorStock();
    const totalSizeStock = calculateTotalSizeStock();

    // Update displays
    document.getElementById('total-color-stock').textContent = totalColorStock;
    document.getElementById('total-size-stock').textContent = totalSizeStock;
    document.getElementById('total-size-stock-display').textContent = totalSizeStock;

    // Update individual size statuses
    document.querySelectorAll('.stock-quantity').forEach(input => {
        const stock = parseInt(input.value) || 0;
        const size = input.name.match(/\[(.*?)\]/)[1];
        const statusElement = document.getElementById(`status-${size}`);

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

    // Validate stock consistency
    const validationMessage = document.getElementById('stock-validation-message');
    if (totalColorStock === totalSizeStock) {
        validationMessage.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Stock levels match</span>';
    } else if (totalColorStock > totalSizeStock) {
        validationMessage.innerHTML = `<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Color stock (${totalColorStock}) exceeds size distribution (${totalSizeStock})</span>`;
    } else {
        validationMessage.innerHTML = `<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Size distribution (${totalSizeStock}) exceeds total color stock (${totalColorStock})</span>`;
    }
}

// Function to update garment type options based on selected category
function updateGarmentTypeOptions() {
    const categorySelect = document.getElementById('category_id');
    const garmentTypeSelect = document.getElementById('garment_type');
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
    const garmentType = garmentTypeSelect.value;
    const measurementGrid = document.getElementById('measurement-grid');
    const fitCharacteristics = document.getElementById('fit-characteristics');

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
        tableHead.innerHTML = '<th>Size</th>';

        garmentData.measurements.forEach(measurement => {
            const th = document.createElement('th');
            th.textContent = formatMeasurementName(measurement);
            th.title = getMeasurementDescription(measurement);
            tableHead.appendChild(th);
        });

        // Update measurement table rows
        const tableBody = document.getElementById('measurement-rows');
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set up category change listener
    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        categorySelect.addEventListener('change', updateGarmentTypeOptions);

        // Initialize garment types based on current category selection
        if (categorySelect.value) {
            updateGarmentTypeOptions();
        }
    }

    // Initialize if garment type is already selected
    const garmentTypeSelect = document.getElementById('garment_type');
    if (garmentTypeSelect && garmentTypeSelect.value) {
        updateSizingSection();
    }

    // Initial stock calculation
    updateStockCalculations();
});
</script>
@endpush
