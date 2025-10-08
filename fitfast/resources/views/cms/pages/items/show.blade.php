@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Item Details</h1>
    <div>
        <a href="{{ route('cms.items.edit', $item) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Item
        </a>
        <a href="{{ route('cms.items.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
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
                        <p class="mb-0 font-weight-bold">Color</p>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge" style="background-color: {{ $item->color }}; color: white; padding: 5px 10px;">
                            {{ $item->color }}
                        </span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Stock Quantity</p>
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
                                @endphp
                                <tr>
                                    <td><strong>{{ $size }}</strong></td>
                                    <td>{{ $sizeStock }}</td>
                                    <td>
                                        @if($sizeStock > 10)
                                            <span class="badge badge-success">In Stock</span>
                                        @elseif($sizeStock > 0)
                                            <span class="badge badge-warning">Low Stock</span>
                                        @else
                                            <span class="badge badge-secondary">Out of Stock</span>
                                        @endif
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

        <!-- Sizing Data Card -->
        @if($item->hasAISizingData())
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">AI Sizing Data</h6>
            </div>
            <div class="card-body">
                @if(!empty($item->sizing_data['measurements_cm']))
                    <h6>Garment Measurements (cm)</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Size</th>
                                    @if(!empty($item->sizing_data['measurements_cm']))
                                        @foreach(array_keys($item->sizing_data['measurements_cm'][array_key_first($item->sizing_data['measurements_cm'])]) as $measurement)
                                            <th>{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $measurement)) }}</th>
                                        @endforeach
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($item->sizing_data['measurements_cm'] as $size => $measurements)
                                    <tr>
                                        <td><strong>{{ $size }}</strong></td>
                                        @foreach($measurements as $measurementValue)
                                            <td>{{ $measurementValue ?? 'N/A' }} cm</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if(!empty($item->sizing_data['fit_characteristics']))
                    <h6 class="mt-4">Fit Characteristics</h6>
                    <div class="row">
                        @foreach($item->sizing_data['fit_characteristics'] as $key => $value)
                            <div class="col-md-4 mb-2">
                                <strong class="text-capitalize">{{ str_replace('_', ' ', $key) }}:</strong>
                                <span class="text-muted">{{ $value }}</span>
                            </div>
                        @endforeach
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
                    <a href="{{ route('cms.items.edit', $item) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Edit Item
                    </a>
                    <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Item
                    </button>
                    <form id="delete-form" action="{{ route('cms.items.destroy', $item) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
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
                            <i class="fas fa-user-circle text-primary"></i>
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
