@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Stores Management</h1>

    <!-- Stock Alert Summary -->
    @php
        $totalLowStock = $stores->sum('low_stock_items_count');
        $totalCritical = $stores->sum('critical_stock_items_count');
        $totalOutOfStock = $stores->sum('out_of_stock_items_count');
        $storesWithAlerts = $stores->filter(function($store) {
            return $store->low_stock_items_count > 0 || $store->critical_stock_items_count > 0 || $store->out_of_stock_items_count > 0;
        })->count();
    @endphp

    <div>
        @if($totalLowStock > 0 || $totalCritical > 0)
        <div class="alert alert-warning alert-dismissible fade show mb-2" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Stock Alerts:</strong>
            @if($totalCritical > 0)
                <span class="text-danger mx-2">{{ $totalCritical }} critical</span>
            @endif
            @if($totalLowStock > 0)
                <span class="text-warning mx-2">{{ $totalLowStock }} low stock</span>
            @endif
            @if($totalOutOfStock > 0)
                <span class="text-secondary mx-2">{{ $totalOutOfStock }} out of stock</span>
            @endif
            across {{ $storesWithAlerts }} store(s)
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="btn-group mr-2">
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-file-export fa-sm text-white-50"></i> Export
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('cms.stores.export') }}">
                    <i class="fas fa-store text-primary"></i> Export All Stores
                </a>
                @if($storesWithAlerts > 0)
                <a class="dropdown-item" href="{{ route('cms.stores.export-alerts') }}">
                    <i class="fas fa-bell text-warning"></i> Export Stores with Alerts
                </a>
                @endif
            </div>
        </div>
        <a href="{{ route('cms.stores.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Store
        </a>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">All Stores</h6>
                <div>
                    @if($storesWithAlerts > 0)
                    <span class="badge badge-warning mr-2">
                        <i class="fas fa-bell"></i> {{ $storesWithAlerts }} Store(s) with Alerts
                    </span>
                    @endif
                    <span class="badge badge-info">
                        <i class="fas fa-store"></i> {{ $stores->total() }} Total Stores
                    </span>
                </div>
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

                <div class="table-responsive">
                    <table class="table table-bordered" id="storesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Inventory Health</th>
                                <th>Address</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stores as $store)
                            @php
                                $hasCritical = $store->critical_stock_items_count > 0;
                                $hasLowStock = $store->low_stock_items_count > 0;
                                $hasOutOfStock = $store->out_of_stock_items_count > 0;
                            @endphp
                            <tr class="{{ $hasCritical ? 'table-warning' : ($hasLowStock ? 'table-light-warning' : '') }}">
                                <td>{{ $store->id }}</td>
                                <td>
                                    <strong>{{ $store->name }}</strong>
                                    @if($store->description)
                                    <br><small class="text-muted">{{ Str::limit($store->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($store->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge badge-info mb-1">{{ $store->items_count }} total items</span>

                                        @if($hasCritical)
                                        <span class="badge badge-danger mb-1"
                                              title="{{ $store->critical_stock_items_count }} items with very low stock">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $store->critical_stock_items_count }} critical
                                        </span>
                                        @endif

                                        @if($hasLowStock)
                                        <span class="badge badge-warning mb-1"
                                              title="{{ $store->low_stock_items_count }} items with low stock">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            {{ $store->low_stock_items_count }} low stock
                                        </span>
                                        @endif

                                        @if($hasOutOfStock)
                                        <span class="badge badge-secondary"
                                              title="{{ $store->out_of_stock_items_count }} items out of stock">
                                            <i class="fas fa-times-circle"></i>
                                            {{ $store->out_of_stock_items_count }} out of stock
                                        </span>
                                        @endif

                                        @if(!$hasCritical && !$hasLowStock && !$hasOutOfStock)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Stock OK
                                        </span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ Str::limit($store->address, 30) }}</td>
                                <td>{{ $store->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('cms.stores.show', $store) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('cms.stores.edit', $store) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $store->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $store->id }}" action="{{ route('cms.stores.destroy', $store) }}" method="POST" class="d-none">
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
.table-light-warning {
    background-color: #fffbf0 !important;
}
.badge {
    font-size: 0.75em;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#storesTable').DataTable({
            "pageLength": 25,
            "order": [[0, 'desc']],
            "columnDefs": [
                { "orderable": false, "targets": [3, 6] } // Disable sorting for inventory and actions columns
            ]
        });
    });

    function confirmDelete(storeId) {
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
                document.getElementById('delete-form-' + storeId).submit();
            }
        });
    }
</script>
@endpush
