<div class="sizing-data-section card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Inventory & Sizing Data</h5>
        <small class="text-muted">Stock by size is required. Measurements are optional for AI recommendations.</small>
    </div>
    <div class="card-body">
        <!-- Stock by Size Section (REQUIRED) -->
        <div class="row mb-4">
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
                            @foreach(\App\Models\Item::STANDARD_SIZES as $size)
                            <tr>
                                <td><strong>{{ $size }}</strong></td>
                                <td>
                                    <input type="number"
                                           name="size_stock[{{ $size }}]"
                                           class="form-control form-control-sm stock-quantity"
                                           value="{{ old("size_stock.$size", isset($item) ? $item->getSizeStock($size) : 0) }}"
                                           min="0"
                                           required
                                           onchange="updateTotalStock()">
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
                                <td><strong>Total Stock</strong></td>
                                <td colspan="2">
                                    <strong id="total-stock">0</strong> units
                                </td>
                            </tr>
                        </tfoot>
                    </table>
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
                            onchange="updateMeasurementGrid()">
                        <option value="">Select Garment Type</option>
                        @if(isset($item) && $item->category)
                            @php
                                $categoryGarmentTypes = \App\Models\Item::getGarmentTypesForCategory($item->category->slug);
                            @endphp
                            @foreach($categoryGarmentTypes as $key => $name)
                                <option value="{{ $key }}"
                                    {{ old('garment_type', $item->garment_type ?? '') == $key ? 'selected' : '' }}>
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
                        <option value="slim">Slim</option>
                        <option value="regular" selected>Regular</option>
                        <option value="loose">Loose</option>
                        <option value="oversized">Oversized</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="ease">Ease Level</label>
                    <select class="form-control" id="ease" name="ease">
                        <option value="tight">Tight Fit</option>
                        <option value="fitted">Fitted</option>
                        <option value="standard" selected>Standard</option>
                        <option value="relaxed">Relaxed</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="stretch">Stretch Level</label>
                    <select class="form-control" id="stretch" name="stretch">
                        <option value="none">No Stretch</option>
                        <option value="low">Low Stretch</option>
                        <option value="medium" selected>Medium Stretch</option>
                        <option value="high">High Stretch</option>
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

// Function to update total stock calculation
function updateTotalStock() {
    let total = 0;
    document.querySelectorAll('.stock-quantity').forEach(input => {
        total += parseInt(input.value) || 0;
    });
    document.getElementById('total-stock').textContent = total;

    // Update individual stock statuses
    document.querySelectorAll('.stock-quantity').forEach(input => {
        const stock = parseInt(input.value) || 0;
        const statusElement = document.getElementById('status-' + input.name.match(/\[(.*?)\]/)[1]);

        if (stock > 10) {
            statusElement.innerHTML = '<span class="badge badge-success">In Stock</span>';
        } else if (stock > 0) {
            statusElement.innerHTML = '<span class="badge badge-warning">Low Stock</span>';
        } else {
            statusElement.innerHTML = '<span class="badge badge-secondary">Out of Stock</span>';
        }
    });
}

// Function to update garment type options based on selected category
function updateGarmentTypeOptions(categoryId) {
    const garmentTypeSelect = document.getElementById('garment_type');

    // Clear existing options
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

        // Preselect if editing and matches
        @if(isset($item) && $item->garment_type)
            if (key === '{{ $item->garment_type }}') {
                option.selected = true;
            }
        @endif

        garmentTypeSelect.appendChild(option);
    }

    // Trigger measurement grid update if a garment type is selected
    if (garmentTypeSelect.value) {
        updateMeasurementGrid();
    } else {
        // Hide measurement grid if no garment type selected
        document.getElementById('measurement-grid').style.display = 'none';
        document.getElementById('fit-characteristics').style.display = 'none';
    }
}

function updateMeasurementGrid() {
    const garmentTypeSelect = document.getElementById('garment_type');
    const garmentType = garmentTypeSelect.value;
    const measurementGrid = document.getElementById('measurement-grid');
    const fitCharacteristics = document.getElementById('fit-characteristics');

    if (!garmentType) {
        measurementGrid.style.display = 'none';
        fitCharacteristics.style.display = 'none';
        return;
    }

    // Show sections
    measurementGrid.style.display = 'block';
    fitCharacteristics.style.display = 'block';

    const garmentData = garmentTypes[garmentType];
    if (!garmentData) return;

    // Update table header with measurement columns
    const tableHead = document.querySelector('#measurement-grid thead tr');
    tableHead.innerHTML = '<th>Size</th>';

    garmentData.measurements.forEach(measurement => {
        const th = document.createElement('th');
        th.textContent = formatMeasurementName(measurement);
        th.title = getMeasurementDescription(measurement);
        tableHead.appendChild(th);
    });

    // Update table rows
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
            // CHANGED: Remove required attribute to make optional

            // Set existing value if editing
            @if(isset($item) && $item->sizing_data)
                const existingMeasurements = @json($item->garment_measurements ?? []);
                if (existingMeasurements[size] && existingMeasurements[size][measurement]) {
                    input.value = existingMeasurements[size][measurement];
                }
            @endif

            cell.appendChild(input);
            row.appendChild(cell);
        });

        tableBody.appendChild(row);
    });
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
    };

    return descriptions[measurement] || 'Garment measurement';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize total stock calculation
    updateTotalStock();

    // Set up category change listener
    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            updateGarmentTypeOptions(categoryId);
        });

        // Initialize garment types based on current category selection
        if (categorySelect.value) {
            updateGarmentTypeOptions(categorySelect.value);
        }
    }

    // Initialize measurement grid if garment type is already selected
    const garmentTypeSelect = document.getElementById('garment_type');
    if (garmentTypeSelect && garmentTypeSelect.value) {
        updateMeasurementGrid();
    }
});
</script>
@endpush
