@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Items Management</h1>
    <div>
        <div class="btn-group mr-2">
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-file-export fa-sm text-white-50"></i> Export
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('cms.items.export') }}">
                    <i class="fas fa-file-csv text-success"></i> Export All Items
                </a>
                <a class="dropdown-item" href="{{ route('cms.items.export-low-stock') }}">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Export Low Stock Items
                </a>
            </div>
        </div>
        <a href="{{ route('cms.items.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Item
        </a>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Items</h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('export_success'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('export_success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Low Stock Warning -->
                @php
                    $lowStockCount = \App\Models\Item::where('stock_quantity', '<', 10)->count();
                    $outOfStockCount = \App\Models\Item::where('stock_quantity', 0)->count();
                @endphp

                @if($lowStockCount > 0)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Stock Alert:</strong>
                    {{ $lowStockCount }} item(s) have low stock
                    @if($outOfStockCount > 0)
                        (including {{ $outOfStockCount }} out of stock)
                    @endif
                    .
                    <a href="{{ route('cms.items.export-low-stock') }}" class="alert-link">Export low stock report</a>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Store</th>
                                <th>Price</th>
                                <th>Category</th>
                                <th>Garment Type</th>
                                <th>Colors</th>
                                <th>Stock</th>
                                <th>Users</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr class="{{ $item->stock_quantity == 0 ? 'table-danger' : ($item->stock_quantity < 10 ? 'table-warning' : '') }}">
                                <td>{{ $item->id }}</td>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                    @if($item->description)
                                    <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>{{ $item->store->name ?? 'N/A' }}</td>
                                <td>${{ number_format($item->price, 2) }}</td>
                                <td>
                                    @if($item->category)
                                        <span class="badge badge-info">{{ $item->category->name }}</span>
                                    @else
                                        <span class="badge badge-secondary">No Category</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->garment_type)
                                        <span class="badge badge-primary">{{ \App\Models\Item::getGarmentTypeName($item->garment_type) }}</span>
                                    @else
                                        <span class="badge badge-secondary">Not Set</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->color_variants && count($item->color_variants) > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($item->color_variants as $colorCode => $colorData)
                                                @php
                                                    $colorName = $colorData['name'] ?? $colorCode;
                                                    // Generate a color hash for consistent badge colors
                                                    $colorHash = '#' . substr(md5($colorName), 0, 6);
                                                @endphp
                                                <span class="badge" style="background-color: {{ $colorHash }}; color: white;" title="{{ $colorName }}">
                                                    {{ $colorName }}
                                                    @if(($colorData['stock'] ?? 0) > 0)
                                                        <small>({{ $colorData['stock'] }})</small>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                        <small class="text-muted">{{ count($item->color_variants) }} color(s)</small>
                                    @else
                                        <span class="text-muted">No colors</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->stock_quantity > 10)
                                        <span class="badge badge-success">{{ $item->stock_quantity }} in stock</span>
                                    @elseif($item->stock_quantity > 0)
                                        <span class="badge badge-warning">{{ $item->stock_quantity }} low stock</span>
                                    @else
                                        <span class="badge badge-danger">Out of stock</span>
                                    @endif
                                    @if($item->size_stock)
                                        <br><small class="text-muted">
                                            @php
                                                $availableSizes = array_filter($item->size_stock ?? [], function($stock) {
                                                    return $stock > 0;
                                                });
                                            @endphp
                                            {{ count($availableSizes) }} sizes available
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $item->users->count() }} users</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('cms.items.show', $item) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('cms.items.edit', $item) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $item->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $item->id }}" action="{{ route('cms.items.destroy', $item) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
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
@endsection

@push('styles')
<style>
.badge {
    font-size: 0.75em;
    margin: 1px;
}
.gap-1 {
    gap: 0.25rem;
}
.table-warning {
    background-color: #fff3cd !important;
}
.table-danger {
    background-color: #f8d7da !important;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#itemsTable').DataTable({
            "pageLength": 25,
            "order": [[0, 'desc']],
            "columnDefs": [
                { "orderable": false, "targets": [6, 8, 9] } // Disable sorting for colors, users and actions columns
            ]
        });
    });

    function confirmDelete(itemId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + itemId).submit();
            }
        });
    }
</script>
@endpush
