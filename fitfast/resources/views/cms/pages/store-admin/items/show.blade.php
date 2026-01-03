@extends('cms.layouts.store-admin-app')

@section('page-title', 'Item Management')
@section('page-subtitle', 'Manage item inventory')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Item Details</h1>
    <div>
        <a href="{{ route('store-admin.items.edit', $item) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Item
        </a>
        <a href="{{ route('store-admin.items.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Items
        </a>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-lg-8">
        <!-- Item Images Card -->
        @if($item->images->count() > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Item Images</h6>
                <small class="text-muted">{{ $item->images->count() }} image(s) available</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Primary Image (Larger) -->
                    @if($item->primary_image)
                    <div class="col-md-6 mb-4">
                        <div class="text-center">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-star text-warning"></i> Primary Image
                            </h6>
                            <div class="image-container position-relative">
                                <img src="{{ asset('storage/' . $item->primary_image->image_path) }}"
                                     alt="{{ $item->name }}"
                                     class="img-fluid rounded shadow-sm cursor-pointer"
                                     style="max-height: 300px; object-fit: contain;"
                                     onclick="showImageModal('{{ asset('storage/' . $item->primary_image->image_path) }}', '{{ $item->name }} - Primary Image')">
                                <div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-warning text-dark">Primary</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Additional Images -->
                    @if($item->images->count() > 1)
                    <div class="{{ $item->primary_image ? 'col-md-6' : 'col-12' }}">
                        <h6 class="text-muted mb-3">Additional Images</h6>
                        <div class="row">
                            @foreach($item->images as $image)
                                @if(!$image->is_primary)
                                <div class="col-6 col-sm-4 col-md-6 col-lg-4 mb-3">
                                    <div class="image-container position-relative">
                                        <img src="{{ asset('storage/' . $image->image_path) }}"
                                             alt="{{ $item->name }}"
                                             class="img-thumbnail cursor-pointer w-100"
                                             style="height: 120px; object-fit: cover;"
                                             onclick="showImageModal('{{ asset('storage/' . $image->image_path) }}', '{{ $item->name }} - Image {{ $loop->iteration }}')">
                                        <div class="position-absolute top-0 end-0 m-1">
                                            <small class="badge bg-secondary">#{{ $image->order + 1 }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Image Gallery Grid -->
                @if($item->images->count() > 1)
                <div class="mt-4">
                    <h6 class="text-muted mb-3">All Images</h6>
                    <div class="row">
                        @foreach($item->images as $image)
                        <div class="col-4 col-sm-3 col-md-2 mb-3">
                            <div class="image-container position-relative">
                                <img src="{{ asset('storage/' . $image->image_path) }}"
                                     alt="{{ $item->name }}"
                                     class="img-thumbnail cursor-pointer w-100"
                                     style="height: 100px; object-fit: cover;"
                                     onclick="showImageModal('{{ asset('storage/' . $image->image_path) }}', '{{ $item->name }} - Image {{ $loop->iteration }}')">
                                @if($image->is_primary)
                                    <div class="position-absolute top-0 start-0 m-1">
                                        <i class="fas fa-star text-warning" title="Primary Image"></i>
                                    </div>
                                @endif
                                <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-50 text-white text-center py-1">
                                    <small>#{{ $image->order + 1 }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">Item Images</h6>
            </div>
            <div class="card-body text-center py-5">
                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Images Available</h5>
                <p class="text-muted">This item doesn't have any images yet.</p>
                <a href="{{ route('store-admin.items.edit', $item) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Images
                </a>
            </div>
        </div>
        @endif

        <!-- Item Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Item Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">ID</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $item->id }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Name</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $item->name }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Store</p>
                    </div>
                    <div class col-sm-9">
                        <p class="text-muted mb-0">
                            {{ $item->store->name ?? 'No store assigned' }}
                        </p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Price</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">${{ number_format($item->price, 2) }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Category</p>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge badge-info">{{ $item->category->name ?? 'No category' }}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Garment Type</p>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge badge-secondary">{{ $item->garment_type_display_name ?? 'Not specified' }}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Total Stock</p>
                    </div>
                    <div class="col-sm-9">
                        @if($item->stock_quantity > 10)
                            <span class="badge badge-success">{{ $item->stock_quantity }} in stock</span>
                        @elseif($item->stock_quantity > 0)
                            <span class="badge badge-warning">{{ $item->stock_quantity }} low stock</span>
                        @else
                            <span class="badge badge-danger">Out of stock</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Description</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $item->description ?? 'No description provided' }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Created At</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $item->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Updated At</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $item->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Color-Size Variants Card (NEW) -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Color & Size Variants</h6>
                <span class="badge badge-info">{{ is_array($item->available_variants) ? count($item->available_variants) : 0 }} variants in stock</span>
            </div>
            <div class="card-body">
                @php
                    // Get variants data
                    $variants = $item->variants ?? [];
                    $availableVariants = $item->available_variants ?? [];
                    $variantsByColor = $item->variants_by_color ?? [];
                    $variantsBySize = $item->variants_by_size ?? [];

                    // Group variants by color
                    $groupedVariants = [];
                    $totalStock = 0;

                    foreach ($availableVariants as $variant) {
                        if (!isset($groupedVariants[$variant['color']])) {
                            $groupedVariants[$variant['color']] = [];
                        }
                        $groupedVariants[$variant['color']][] = $variant;
                        $totalStock += $variant['stock'];
                    }
                @endphp

                @if(count($availableVariants) > 0)
                    <!-- Stock Summary -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Total Stock:</strong>
                                <div class="h5">{{ $totalStock }} units</div>
                            </div>
                            <div class="col-md-4">
                                <strong>Colors:</strong>
                                <div class="h5">{{ count($groupedVariants) }}</div>
                            </div>
                            <div class="col-md-4">
                                <strong>Active Variants:</strong>
                                <div class="h5">{{ count($availableVariants) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Color Breakdown -->
                    <h6 class="text-primary mb-3">Stock by Color</h6>
                    <div class="row mb-4">
                        @foreach($groupedVariants as $color => $colorVariants)
                            @php
                                $colorStock = array_sum(array_column($colorVariants, 'stock'));
                                $colorSizes = array_unique(array_column($colorVariants, 'size'));
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="card border-left-primary h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="font-weight-bold text-primary mb-0">{{ $color }}</h6>
                                            <span class="badge badge-success">{{ $colorStock }} units</span>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Available sizes:</small>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach($colorSizes as $size)
                                                    @php
                                                        $sizeVariant = collect($colorVariants)->firstWhere('size', $size);
                                                        $sizeStock = $sizeVariant['stock'] ?? 0;
                                                    @endphp
                                                    <span class="badge badge-light border">
                                                        {{ $size }}: {{ $sizeStock }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Detailed Variant Table -->
                    <h6 class="text-primary mb-3">Detailed Variant Breakdown</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Color</th>
                                    @foreach(\App\Models\Item::STANDARD_SIZES as $size)
                                        <th class="text-center">{{ $size }}</th>
                                    @endforeach
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sizeTotals = array_fill_keys(\App\Models\Item::STANDARD_SIZES, 0);
                                    $colorTotals = [];
                                @endphp

                                @foreach($groupedVariants as $color => $colorVariants)
                                    @php
                                        $colorRowTotal = 0;
                                        $colorStockBySize = [];
                                        foreach ($colorVariants as $variant) {
                                            $colorStockBySize[$variant['size']] = $variant['stock'];
                                            $colorRowTotal += $variant['stock'];
                                            $sizeTotals[$variant['size']] += $variant['stock'];
                                        }
                                        $colorTotals[$color] = $colorRowTotal;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $color }}</strong></td>
                                        @foreach(\App\Models\Item::STANDARD_SIZES as $size)
                                            @php
                                                $stock = $colorStockBySize[$size] ?? 0;
                                            @endphp
                                            <td class="text-center">
                                                @if($stock > 10)
                                                    <span class="badge badge-success">{{ $stock }}</span>
                                                @elseif($stock > 0)
                                                    <span class="badge badge-warning">{{ $stock }}</span>
                                                @else
                                                    <span class="badge badge-secondary">0</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center font-weight-bold">
                                            {{ $colorRowTotal }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-info">
                                <tr>
                                    <td><strong>Total by Size</strong></td>
                                    @foreach(\App\Models\Item::STANDARD_SIZES as $size)
                                        <td class="text-center font-weight-bold">
                                            {{ $sizeTotals[$size] }}
                                        </td>
                                    @endforeach
                                    <td class="text-center font-weight-bold">
                                        {{ array_sum($sizeTotals) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Size Stock Visualization -->
                    <div class="mt-4">
                        <h6 class="text-primary mb-3">Stock Distribution by Size</h6>
                        <div class="row">
                            @foreach(\App\Models\Item::STANDARD_SIZES as $size)
                                @php
                                    $sizeStock = $sizeTotals[$size];
                                    $percentage = $totalStock > 0 ? ($sizeStock / $totalStock) * 100 : 0;
                                @endphp
                                <div class="col-md-2 mb-3">
                                    <div class="card text-center">
                                        <div class="card-body p-2">
                                            <h6 class="mb-1">{{ $size }}</h6>
                                            <div class="progress mb-1" style="height: 20px;">
                                                <div class="progress-bar
                                                    @if($sizeStock > 10) bg-success
                                                    @elseif($sizeStock > 0) bg-warning
                                                    @else bg-secondary
                                                    @endif"
                                                    role="progressbar"
                                                    style="width: {{ min($percentage, 100) }}%"
                                                    aria-valuenow="{{ $sizeStock }}"
                                                    aria-valuemin="0"
                                                    aria-valuemax="{{ $totalStock }}">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $sizeStock }} units</small>
                                            <br>
                                            <small class="text-muted">{{ number_format($percentage, 1) }}%</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>No stock available!</strong> This item has no color-size variants with stock.
                        <a href="{{ route('store-admin.items.edit', $item) }}" class="alert-link">Add stock variants</a>
                    </div>
                @endif

                <!-- Raw Variants Data (for debugging/development) -->
                @if(app()->environment('local') && !empty($variants))
                    <div class="mt-4">
                        <h6 class="text-muted mb-2">
                            <small>Raw Variants Data ({{ count($variants) }} total entries)</small>
                        </h6>
                        <div class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                            <pre class="mb-0"><code>{{ json_encode($variants, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Size Measurements Card -->
        @if(!empty($item->sizing_data) && isset($item->sizing_data['measurements_cm']) && !empty($item->sizing_data['measurements_cm']))
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Size Measurements</h6>
                <small class="text-muted">Detailed measurements for each size (in centimeters)</small>
            </div>
            <div class="card-body">
                @php
                    $measurementsData = $item->sizing_data['measurements_cm'];

                    // Get all possible measurements from all sizes (not just first one)
                    $allMeasurements = [];
                    foreach ($measurementsData as $size => $sizeMeasurements) {
                        if (is_array($sizeMeasurements) && !empty($sizeMeasurements)) {
                            $allMeasurements = array_merge($allMeasurements, array_keys($sizeMeasurements));
                        }
                    }
                    $measurements = array_unique($allMeasurements);

                    // Get sizes that actually have measurements
                    $sizesWithMeasurements = [];
                    foreach ($measurementsData as $size => $sizeMeasurements) {
                        if (is_array($sizeMeasurements) && !empty(array_filter($sizeMeasurements))) {
                            $sizesWithMeasurements[] = $size;
                        }
                    }
                @endphp

                @if(!empty($measurements) && !empty($sizesWithMeasurements))
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Size</th>
                                @foreach($measurements as $measurement)
                                    <th>
                                        {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $measurement)) }}
                                        <small class="d-block text-muted">{{ $item->getMeasurementDescription($measurement) }}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sizesWithMeasurements as $size)
                                @php
                                    $sizeMeasurements = $measurementsData[$size] ?? [];
                                @endphp
                                <tr>
                                    <td><strong>{{ $size }}</strong></td>
                                    @foreach($measurements as $measurement)
                                        <td>
                                            @if(isset($sizeMeasurements[$measurement]) && $sizeMeasurements[$measurement] !== '')
                                                <span class="font-weight-bold">{{ $sizeMeasurements[$measurement] }}</span> cm
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Measurement Summary -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h6>Measurement Range</h6>
                        <div class="list-group list-group-flush">
                            @foreach($measurements as $measurement)
                                @php
                                    $values = [];
                                    foreach ($measurementsData as $sizeMeasurements) {
                                        if (isset($sizeMeasurements[$measurement]) && $sizeMeasurements[$measurement] !== '') {
                                            $values[] = $sizeMeasurements[$measurement];
                                        }
                                    }
                                    $min = !empty($values) ? min($values) : null;
                                    $max = !empty($values) ? max($values) : null;
                                @endphp
                                @if($min && $max)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $measurement)) }}</span>
                                        <span class="badge badge-info badge-pill">{{ $min }} - {{ $max }} cm</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Sizes with Measurements</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($sizesWithMeasurements as $size)
                                <span class="badge badge-success">{{ $size }}</span>
                            @endforeach
                        </div>
                        @if(count($sizesWithMeasurements) < count($measurementsData))
                            <div class="mt-2">
                                <small class="text-muted">
                                    {{ count($measurementsData) - count($sizesWithMeasurements) }} sizes without measurements
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
                @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Measurement data structure exists, but no sizes have measurement values entered.
                    @if($item->garment_type)
                        @php
                            $requiredMeasurements = \App\Models\Item::getRequiredMeasurements($item->garment_type);
                        @endphp
                        @if(!empty($requiredMeasurements))
                            <div class="mt-2">
                                <strong>Expected measurements for {{ $item->garment_type_display_name }}:</strong>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($requiredMeasurements as $measurement)
                                        <span class="badge badge-light">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $measurement)) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">Size Measurements</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle"></i> No Measurement Data</h6>
                    <p class="mb-0">
                        No size measurement data has been entered for this item.
                        @if($item->garment_type)
                            <a href="{{ route('store-admin.items.edit', $item) }}" class="alert-link">Add measurements</a>
                            to enable better size recommendations.
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Fit Characteristics Card -->
        @if(!empty($item->sizing_data) && !empty($item->sizing_data['fit_characteristics']))
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Fit Characteristics</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($item->sizing_data['fit_characteristics'] as $key => $value)
                        @if(!empty($value))
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center py-3">
                                        <h6 class="card-title text-capitalize text-muted mb-1">
                                            {{ str_replace('_', ' ', $key) }}
                                        </h6>
                                        <p class="card-text font-weight-bold text-primary mb-0">
                                            {{ ucfirst($value) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                @if(!empty($item->sizing_data['size_system']))
                    <div class="mt-3 p-3 bg-light rounded">
                        <strong>Size System:</strong>
                        <span class="badge badge-info">{{ $item->sizing_data['size_system'] }}</span>
                    </div>
                @endif

                @if(!empty($item->sizing_data['last_updated']))
                    <div class="mt-2 text-right">
                        <small class="text-muted">
                            Last updated: {{ \Carbon\Carbon::parse($item->sizing_data['last_updated'])->format('M d, Y H:i') }}
                        </small>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Statistics Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Item Statistics</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h4>{{ $item->users->count() }}</h4>
                        <p class="text-muted">Associated Users</p>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <i class="fas fa-star fa-2x text-warning mb-2"></i>
                        <h4>{{ $item->averageRating() }}</h4>
                        <p class="text-muted">Average Rating</p>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <i class="fas fa-comment fa-2x text-info mb-2"></i>
                        <h4>{{ $item->reviewCount() }}</h4>
                        <p class="text-muted">Total Reviews</p>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <i class="fas fa-image fa-2x text-success mb-2"></i>
                        <h4>{{ $item->images->count() }}</h4>
                        <p class="text-muted">Total Images</p>
                    </div>
                    <hr>
                    <div class="mb-4">
                        @php
                            $hasMeasurements = !empty($item->sizing_data) &&
                                            isset($item->sizing_data['measurements_cm']) &&
                                            !empty($item->sizing_data['measurements_cm']);
                            $measurementCount = 0;
                            if ($hasMeasurements) {
                                foreach ($item->sizing_data['measurements_cm'] as $sizeMeasurements) {
                                    if (is_array($sizeMeasurements) && !empty(array_filter($sizeMeasurements))) {
                                        $measurementCount++;
                                    }
                                }
                            }
                        @endphp
                        <i class="fas fa-ruler-combined fa-2x text-success mb-2"></i>
                        <h4>{{ $measurementCount }}</h4>
                        <p class="text-muted">Sizes with Measurements</p>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <i class="fas fa-palette fa-2x text-info mb-2"></i>
                        <h4>{{ count($item->variants_by_color ?? []) }}</h4>
                        <p class="text-muted">Active Colors</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('store-admin.items.edit', $item) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Edit Item
                    </a>
                    <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Item
                    </button>
                    <form id="delete-form" action="{{ route('store-admin.items.destroy', $item) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>

        <!-- Variant Summary Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Variant Summary</h6>
            </div>
            <div class="card-body">
                @php
                    $availableVariants = is_array($item->available_variants ?? []) ? $item->available_variants : [];
                    $totalStock = !empty($availableVariants) ? array_sum(array_column($availableVariants, 'stock')) : 0;
                    $colorCount = is_array($item->variants_by_color ?? []) ? count($item->variants_by_color) : 0;
                    $sizeCount = is_array($item->variants_by_size ?? []) ? count($item->variants_by_size) : 0;
                @endphp

                <div class="mb-3">
                    <strong>Active Variants:</strong>
                    <span class="float-right badge badge-info">{{ count($availableVariants) }}</span>
                </div>

                <div class="mb-3">
                    <strong>Colors with Stock:</strong>
                    <span class="float-right badge badge-success">{{ $colorCount }}</span>
                </div>

                <div class="mb-3">
                    <strong>Sizes with Stock:</strong>
                    <span class="float-right badge badge-warning">{{ $sizeCount }}</span>
                </div>

                <div class="mb-3">
                    <strong>Total Stock Value:</strong>
                    <span class="float-right text-success font-weight-bold">
                        ${{ number_format($totalStock * $item->price, 2) }}
                    </span>
                </div>

                @if(count($availableVariants) > 0)
                    <hr>
                    <h6 class="text-muted mb-2">Most Stocked Variants</h6>
                    @php
                        $topVariants = !empty($availableVariants)
                            ? collect($availableVariants)->sortByDesc('stock')->take(3)->all()
                            : [];
                    @endphp
                    <div class="list-group list-group-flush">
                        @foreach($topVariants as $variant)
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between">
                                    <span class="font-weight-bold">{{ $variant['color'] }}/{{ $variant['size'] }}</span>
                                    <span class="badge
                                        @if($variant['stock'] > 10) badge-success
                                        @elseif($variant['stock'] > 0) badge-warning
                                        @else badge-secondary
                                        @endif">
                                        {{ $variant['stock'] }} units
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Garment Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Garment Information</h6>
            </div>
            <div class="card-body">
                @if($item->garment_type)
                    <div class="mb-3">
                        <strong>Type:</strong>
                        <span class="float-right">{{ $item->garment_type_display_name }}</span>
                    </div>

                    @php
                        $hasMeasurements = !empty($item->sizing_data) &&
                                        isset($item->sizing_data['measurements_cm']) &&
                                        !empty($item->sizing_data['measurements_cm']);
                        $sizesWithData = 0;
                        if ($hasMeasurements) {
                            foreach ($item->sizing_data['measurements_cm'] as $sizeMeasurements) {
                                if (is_array($sizeMeasurements) && !empty(array_filter($sizeMeasurements))) {
                                    $sizesWithData++;
                                }
                            }
                        }
                    @endphp

                    <div class="mb-3">
                        <strong>Measurements:</strong>
                        <span class="float-right badge {{ $sizesWithData > 0 ? 'badge-success' : 'badge-warning' }}">
                            {{ $sizesWithData > 0 ? $sizesWithData . ' sizes' : 'Not Available' }}
                        </span>
                    </div>

                    @php
                        $requiredMeasurements = \App\Models\Item::getRequiredMeasurements($item->garment_type);
                    @endphp
                    @if(!empty($requiredMeasurements))
                        <div class="mt-3">
                            <strong>Required Measurements:</strong>
                            <ul class="list-unstyled mt-2">
                                @foreach($requiredMeasurements as $measurement)
                                    <li class="mb-1">
                                        <i class="fas fa-ruler-vertical text-muted mr-2"></i>
                                        <small class="text-capitalize">{{ str_replace('_', ' ', $measurement) }}</small>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-tshirt fa-2x mb-2"></i>
                        <p>No garment type specified</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Associated Users Card -->
        @if($item->users->count() > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Associated Users</h6>
            </div>
            <div class="card-body">
                @foreach($item->users->take(5) as $user)
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">{{ $user->name }}</h6>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                    </div>
                    @if(!$loop->last)
                    <hr class="my-2">
                    @endif
                @endforeach
                @if($item->users->count() > 5)
                    <div class="text-center mt-3">
                        <small class="text-muted">+{{ $item->users->count() - 5 }} more users</small>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Item Image</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="" class="img-fluid rounded" style="max-height: 70vh; object-fit: contain;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.cursor-pointer {
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
}

.cursor-pointer:hover {
    transform: scale(1.02);
}

.image-container {
    border-radius: 8px;
    overflow: hidden;
}

.badge {
    font-size: 0.75em;
}

.progress-bar {
    transition: width 0.6s ease;
}

.table th, .table td {
    vertical-align: middle;
}

.border-left-primary {
    border-left: 4px solid #4e73df !important;
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete() {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this! All associated data will be affected!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form').submit();
        }
    });
}

function showImageModal(imageSrc, title) {
    $('#modalImage').attr('src', imageSrc);
    $('#modalImage').attr('alt', title);
    $('#imageModalLabel').text(title);
    $('#imageModal').modal('show');
}
</script>
@endpush
