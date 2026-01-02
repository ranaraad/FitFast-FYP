<!-- Sizing Data Partial (Garment Types & Measurements Only) -->
<div class="sizing-data-section card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Garment Sizing Data</h5>
        <small class="text-muted">Optional measurements for AI size recommendations. Stock is managed separately in Color & Size section.</small>
    </div>
    <div class="card-body">
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
