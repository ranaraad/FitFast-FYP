@extends('cms.layouts.store-admin-app')

@section('page-title', 'Manage Items')
@section('page-subtitle', 'Manage inventory across your stores')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Items Management</h1>

    <div class="d-flex align-items-center">
        <!-- Quick Stats -->
        <div class="mr-3">
            <span class="badge badge-primary badge-pill py-2 px-3 mr-2">
                <i class="fas fa-box"></i> {{ $summary['total_items'] }} Items
            </span>
            @if($summary['low_stock_count'] > 0)
            <span class="badge badge-warning badge-pill py-2 px-3 mr-2">
                <i class="fas fa-exclamation-triangle"></i> {{ $summary['low_stock_count'] }} Low Stock
            </span>
            @endif
            @if($summary['out_of_stock_count'] > 0)
            <span class="badge badge-danger badge-pill py-2 px-3">
                <i class="fas fa-times-circle"></i> {{ $summary['out_of_stock_count'] }} Out of Stock
            </span>
            @endif
        </div>

        <a href="{{ route('store-admin.items.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Item
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('store-admin.items.index') }}" method="GET" id="filterForm">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="store_id">Store</label>
                        <select class="form-control" id="store_id" name="store_id" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Stores</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="stock_status">Stock Status</label>
                        <select class="form-control" id="stock_status" name="stock_status" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Items</option>
                            <option value="low_stock" {{ request('low_stock') ? 'selected' : '' }}>Low Stock (< 10)</option>
                            <option value="out_of_stock" {{ request('out_of_stock') ? 'selected' : '' }}>Out of Stock</option>
                            <option value="in_stock" {{ request('in_stock') ? 'selected' : '' }}>In Stock (≥ 10)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="Search items...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @if(request()->anyFilled(['store_id', 'category_id', 'search', 'low_stock', 'out_of_stock']))
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('store-admin.items.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                </div>
            </div>
            @endif
        </form>
    </div>
</div>

<!-- Items Table Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">All Items</h6>
        <div class="export-buttons">
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-file-export fa-sm"></i> Export
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" onclick="exportItems()">
                        <i class="fas fa-file-csv text-primary"></i> Export All Items
                    </a>
                    @if($summary['low_stock_count'] > 0)
                    <a class="dropdown-item" href="#" onclick="exportLowStock()">
                        <i class="fas fa-exclamation-triangle text-warning"></i> Export Low Stock
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($items->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="itemsTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>Item</th>
                            <th>Store</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        @php
                            $stockStatus = $item->stock_quantity == 0 ? 'out_of_stock' :
                                         ($item->stock_quantity < 5 ? 'critical' :
                                         ($item->stock_quantity < 10 ? 'low_stock' : 'in_stock'));
                            $statusColors = [
                                'out_of_stock' => 'danger',
                                'critical' => 'danger',
                                'low_stock' => 'warning',
                                'in_stock' => 'success'
                            ];
                            $statusIcons = [
                                'out_of_stock' => 'fa-times-circle',
                                'critical' => 'fa-exclamation-circle',
                                'low_stock' => 'fa-exclamation-triangle',
                                'in_stock' => 'fa-check-circle'
                            ];
                        @endphp
                        <tr class="table-{{ $stockStatus == 'out_of_stock' ? 'danger' : ($stockStatus == 'critical' ? 'danger' : ($stockStatus == 'low_stock' ? 'warning' : '')) }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($item->image)
                                    <div class="flex-shrink-0 mr-3">
                                        <img src="{{ Storage::disk('public')->url($item->image) }}"
                                             alt="{{ $item->name }}"
                                             class="rounded"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </div>
                                    @else
                                    <div class="flex-shrink-0 mr-3">
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-cube text-muted"></i>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <strong class="text-gray-800">{{ $item->name }}</strong>
                                        @if($item->description)
                                        <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                        @endif
                                        @if($item->color)
                                        <br><small class="text-muted"><i class="fas fa-palette"></i> {{ $item->color }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <i class="fas fa-store"></i> {{ $item->store->name }}
                                </span>
                            </td>
                            <td>
                                @if($item->category)
                                    <span class="badge badge-secondary">{{ $item->category->name }}</span>
                                @else
                                    <span class="text-muted">No Category</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">${{ number_format($item->price, 2) }}</strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="font-weight-bold text-{{ $statusColors[$stockStatus] }} mr-2">
                                        {{ $item->stock_quantity }}
                                    </span>
                                    <button class="btn btn-sm btn-outline-primary"
                                            onclick="updateStock({{ $item->id }}, {{ $item->stock_quantity }})"
                                            title="Update Stock">
                                        <i class="fas fa-edit fa-xs"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $statusColors[$stockStatus] }}">
                                    <i class="fas {{ $statusIcons[$stockStatus] }} mr-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $stockStatus)) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $item->updated_at->format('M d, Y') }}</small>
                                <br>
                                <small class="text-muted">{{ $item->updated_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('store-admin.items.show', $item) }}" class="btn btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('store-admin.items.edit', $item) }}" class="btn btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger"
                                            onclick="confirmDelete({{ $item->id }}, '{{ $item->name }}')"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} items
                </div>
                {{ $items->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Items Found</h5>
                <p class="text-muted mb-4">
                    @if(request()->anyFilled(['store_id', 'category_id', 'search']))
                        No items match your current filters.
                    @else
                        You haven't added any items to your stores yet.
                    @endif
                </p>
                <a href="{{ route('store-admin.items.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Add Your First Item
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1" role="dialog" aria-labelledby="stockModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockModalLabel">Update Stock Quantity</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="stockForm">
                    @csrf
                    <input type="hidden" id="item_id" name="item_id">
                    <div class="form-group">
                        <label for="current_stock">Current Stock</label>
                        <input type="text" class="form-control" id="current_stock" readonly>
                    </div>
                    <div class="form-group">
                        <label for="action">Action</label>
                        <select class="form-control" id="action" name="action" required>
                            <option value="set">Set to specific quantity</option>
                            <option value="add">Add to current stock</option>
                            <option value="subtract">Subtract from current stock</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="stock_quantity">Quantity</label>
                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitStockUpdate()">Update Stock</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table-danger {
    background-color: #f8d7da !important;
}
.table-warning {
    background-color: #fff3cd !important;
}
.table-hover tbody tr:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#itemsTable').DataTable({
            "paging": false,
            "searching": false,
            "info": false,
            "ordering": true,
            "order": [[4, 'asc']],
            "columnDefs": [
                { "orderable": false, "targets": [7] }
            ]
        });
    });

    function updateStock(itemId, currentStock) {
        $('#item_id').val(itemId);
        $('#current_stock').val(currentStock);
        $('#stock_quantity').val('');
        $('#action').val('set');
        $('#stockModal').modal('show');
    }

    function submitStockUpdate() {
        const itemId = $('#item_id').val();
        const formData = {
            _token: '{{ csrf_token() }}',
            stock_quantity: $('#stock_quantity').val(),
            action: $('#action').val()
        };

        $.ajax({
            url: '/store-admin/items/' + itemId + '/update-stock',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Something went wrong'
                });
            }
        });
    }

    function confirmDelete(itemId, itemName) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete "${itemName}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#delete-form-' + itemId).submit();
            }
        });
    }

    function exportItems() {
        // Get current filters
        const storeId = document.getElementById('store_id').value;
        const categoryId = document.getElementById('category_id').value;
        const search = document.getElementById('search').value;

        // Build query string
        let queryParams = [];
        if (storeId) queryParams.push(`store_id=${storeId}`);
        if (categoryId) queryParams.push(`category_id=${categoryId}`);
        if (search) queryParams.push(`search=${encodeURIComponent(search)}`);

        const queryString = queryParams.length ? '?' + queryParams.join('&') : '';

        // Show loading state
        Swal.fire({
            title: 'Preparing Export',
            text: 'Generating CSV file...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Trigger download
        setTimeout(() => {
            window.location.href = '{{ route("store-admin.items.export") }}' + queryString;
            Swal.close();
        }, 1000);
    }

    function exportLowStock() {
        // Get current filters
        const storeId = document.getElementById('store_id').value;
        const categoryId = document.getElementById('category_id').value;

        // Build query string
        let queryParams = [];
        if (storeId) queryParams.push(`store_id=${storeId}`);
        if (categoryId) queryParams.push(`category_id=${categoryId}`);

        const queryString = queryParams.length ? '?' + queryParams.join('&') : '';

        // Show loading state
        Swal.fire({
            title: 'Preparing Export',
            text: 'Generating low stock CSV file...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Trigger download
        setTimeout(() => {
            window.location.href = '{{ route("store-admin.items.export-low-stock") }}' + queryString;
            Swal.close();
        }, 1000);
    }

    // Show loading state when exporting
    document.addEventListener('DOMContentLoaded', function() {
        const exportLinks = document.querySelectorAll('a[href*="export"]');
        exportLinks.forEach(link => {
            link.addEventListener('click', function() {
                Swal.fire({
                    title: 'Preparing Export',
                    text: 'Your CSV file is being generated...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            });
        });
    });
</script>

<!-- Delete Forms (hidden) -->
@foreach($items as $item)
<form id="delete-form-{{ $item->id }}" action="{{ route('store-admin.items.destroy', $item) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endforeach
@endpush
