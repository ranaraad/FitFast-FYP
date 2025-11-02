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
                    <div class="col-sm-9">
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
                        <p class="mb-0 font-weight-bold">Color Variants</p>
                    </div>
                    <div class="col-sm-9">
                        @if(!empty($item->color_variants))
                            @foreach($item->color_variants as $colorKey => $colorData)
                                <span class="badge badge-light border mr-2 mb-2 p-2">
                                    {{ $colorData['name'] ?? $colorKey }}: {{ $colorData['stock'] ?? 0 }} units
                                </span>
                            @endforeach
                        @else
                            <span class="text-muted">No color variants</span>
                        @endif
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

        <!-- Stock by Size Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Stock by Size</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Size</th>
                                <th>Stock Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Item::STANDARD_SIZES as $size)
                                @php
                                    $sizeStock = $item->getSizeStock($size);
                                    $sizeStatus = $item->getSizeStockStatus($size);
                                    $statusClass = [
                                        'in_stock' => 'badge-success',
                                        'low_stock' => 'badge-warning',
                                        'out_of_stock' => 'badge-secondary'
                                    ][$sizeStatus];
                                    $statusText = [
                                        'in_stock' => 'In Stock',
                                        'low_stock' => 'Low Stock',
                                        'out_of_stock' => 'Out of Stock'
                                    ][$sizeStatus];
                                @endphp
                                <tr>
                                    <td><strong>{{ $size }}</strong></td>
                                    <td>{{ $sizeStock }}</td>
                                    <td>
                                        <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td><strong>Total Stock</strong></td>
                                <td colspan="2">
                                    <strong>{{ $item->stock_quantity }}</strong> units
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
@endsection

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
</script>
@endpush
