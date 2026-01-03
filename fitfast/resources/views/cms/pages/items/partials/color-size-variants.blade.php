<!-- Color-Size Variants Partial -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-primary">Color & Size Stock Management *</h5>
                <small class="text-muted">Add stock for each color and size combination. The total stock will be calculated automatically.</small>
            </div>
            <div class="card-body">
                <!-- Color-Size Variants Container -->
                <div id="color-size-variants-container">
                    @if(old('color_variants') && count(old('color_variants')) > 0)
                        <!-- Use old form data first (validation errors) -->
                        @foreach(old('color_variants') as $colorIndex => $colorData)
                            <div class="color-size-variant-row card mb-3">
                                <div class="card-header bg-light">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-0">Color #{{ $loop->iteration }}</h6>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            @if($loop->first)
                                                <small class="text-muted">First color cannot be removed</small>
                                            @else
                                                <button type="button" class="btn btn-sm btn-danger remove-color-size-variant">
                                                    <i class="fas fa-times"></i> Remove Color
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Color Name -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Color Name *</label>
                                                <input type="text"
                                                       class="form-control color-name @error("color_variants.{$colorIndex}.name") is-invalid @enderror"
                                                       name="color_variants[{{ $colorIndex }}][name]"
                                                       value="{{ $colorData['name'] ?? '' }}"
                                                       placeholder="e.g., Red, Blue, Black"
                                                       required>
                                                @error("color_variants.{$colorIndex}.name")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Color Total Stock *</label>
                                                <div class="input-group">
                                                    <input type="number"
                                                           class="form-control color-total-stock"
                                                           value="{{ $colorData['total_stock'] ?? 0 }}"
                                                           readonly>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">units</span>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Auto-calculated from size distribution</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Size Stock Grid -->
                                    <div class="row">
                                        <div class="col-12">
                                            <h6 class="text-muted mb-3">Stock by Size</h6>
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
                                                                       name="color_variants[{{ $colorIndex }}][size_stock][{{ $size }}]"
                                                                       class="form-control form-control-sm size-stock-input"
                                                                       value="{{ old("color_variants.{$colorIndex}.size_stock.{$size}", 0) }}"
                                                                       min="0"
                                                                       required
                                                                       data-color-index="{{ $colorIndex }}"
                                                                       data-size="{{ $size }}">
                                                            </td>
                                                            <td>
                                                                <span class="stock-status" id="status-{{ $colorIndex }}-{{ $size }}">
                                                                    @php
                                                                        $stock = old("color_variants.{$colorIndex}.size_stock.{$size}", 0);
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
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @elseif(isset($colorVariantsData) && count($colorVariantsData) > 0)
                        <!-- Use existing item data for editing -->
                        @foreach($colorVariantsData as $colorIndex => $colorData)
                            <div class="color-size-variant-row card mb-3">
                                <div class="card-header bg-light">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-0">Color #{{ $loop->iteration }}</h6>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            @if($loop->first)
                                                <small class="text-muted">First color cannot be removed</small>
                                            @else
                                                <button type="button" class="btn btn-sm btn-danger remove-color-size-variant">
                                                    <i class="fas fa-times"></i> Remove Color
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Color Name -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Color Name *</label>
                                                <input type="text"
                                                       class="form-control color-name @error("color_variants.{$colorIndex}.name") is-invalid @enderror"
                                                       name="color_variants[{{ $colorIndex }}][name]"
                                                       value="{{ old("color_variants.{$colorIndex}.name", $colorData['name'] ?? '') }}"
                                                       placeholder="e.g., Red, Blue, Black"
                                                       required>
                                                @error("color_variants.{$colorIndex}.name")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Color Total Stock *</label>
                                                <div class="input-group">
                                                    @php
                                                        $colorTotal = 0;
                                                        if (isset($colorData['size_stock']) && is_array($colorData['size_stock'])) {
                                                            $colorTotal = array_sum($colorData['size_stock']);
                                                        }
                                                    @endphp
                                                    <input type="number"
                                                           class="form-control color-total-stock"
                                                           value="{{ $colorTotal }}"
                                                           readonly>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">units</span>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Auto-calculated from size distribution</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Size Stock Grid -->
                                    <div class="row">
                                        <div class="col-12">
                                            <h6 class="text-muted mb-3">Stock by Size</h6>
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
                                                                @php
                                                                    $stock = $colorData['size_stock'][$size] ?? 0;
                                                                    $oldStock = old("color_variants.{$colorIndex}.size_stock.{$size}", $stock);
                                                                @endphp
                                                                <input type="number"
                                                                       name="color_variants[{{ $colorIndex }}][size_stock][{{ $size }}]"
                                                                       class="form-control form-control-sm size-stock-input"
                                                                       value="{{ $oldStock }}"
                                                                       min="0"
                                                                       required
                                                                       data-color-index="{{ $colorIndex }}"
                                                                       data-size="{{ $size }}">
                                                            </td>
                                                            <td>
                                                                <span class="stock-status" id="status-{{ $colorIndex }}-{{ $size }}">
                                                                    @if($oldStock > 10)
                                                                        <span class="badge badge-success">In Stock</span>
                                                                    @elseif($oldStock > 0)
                                                                        <span class="badge badge-warning">Low Stock</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">Out of Stock</span>
                                                                    @endif
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Default first color variant (for new items or no existing data) -->
                        <div class="color-size-variant-row card mb-3">
                            <div class="card-header bg-light">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-0">Color #1</h6>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <small class="text-muted">First color cannot be removed</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Color Name -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Color Name *</label>
                                            <input type="text"
                                                   class="form-control color-name"
                                                   name="color_variants[0][name]"
                                                   value="{{ old('color_variants.0.name', '') }}"
                                                   placeholder="e.g., Red, Blue, Black"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Color Total Stock *</label>
                                            <div class="input-group">
                                                <input type="number"
                                                       class="form-control color-total-stock"
                                                       value="0"
                                                       readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">units</span>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">Auto-calculated from size distribution</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Size Stock Grid -->
                                <div class="row">
                                    <div class="col-12">
                                        <h6 class="text-muted mb-3">Stock by Size</h6>
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
                                                                   name="color_variants[0][size_stock][{{ $size }}]"
                                                                   class="form-control form-control-sm size-stock-input"
                                                                   value="{{ old("color_variants.0.size_stock.{$size}", 0) }}"
                                                                   min="0"
                                                                   required
                                                                   data-color-index="0"
                                                                   data-size="{{ $size }}">
                                                        </td>
                                                        <td>
                                                            <span class="stock-status" id="status-0-{{ $size }}">
                                                                @php
                                                                    $stock = old("color_variants.0.size_stock.{$size}", 0);
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
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Add Color Button -->
                <div class="row">
                    <div class="col-12 text-center">
                        <button type="button" class="btn btn-sm btn-secondary" id="add-color-size-variant">
                            <i class="fas fa-plus"></i> Add Another Color
                        </button>
                    </div>
                </div>

                <!-- Stock Summary -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Stock Summary</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Total Items:</strong>
                                    <div class="h4" id="total-stock-summary">0</div>
                                    <small class="text-muted">Across all colors and sizes</small>
                                </div>
                                <div class="col-md-4">
                                    <strong>By Color:</strong>
                                    <div id="color-breakdown-summary" class="mb-2">
                                        <small class="text-muted">No colors added</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <strong>By Size:</strong>
                                    <div id="size-breakdown-summary" class="mb-2">
                                        <small class="text-muted">No stock allocated</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2" id="stock-validation-message">
                                <small class="text-success"><i class="fas fa-check-circle"></i> Stock management ready</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden input for aggregated variants -->
                <input type="hidden" name="variants" id="variants-input" value="{{ old('variants', $item->variants ? json_encode($item->variants) : '[]') }}">
                <input type="hidden" name="stock_quantity" id="total-stock-input" value="{{ old('stock_quantity', $item->stock_quantity) }}">
            </div>
        </div>
    </div>
</div>
