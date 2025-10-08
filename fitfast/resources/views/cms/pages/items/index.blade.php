@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Items Management</h1>
    <a href="{{ route('cms.items.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add New Item
    </a>
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
                                <th>Color</th>
                                <th>Stock</th>
                                <th>Users</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
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
                                    <span class="badge" style="background-color: {{ $item->color }}; color: white;">
                                        {{ $item->color }}
                                    </span>
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

@push('scripts')
<script>
    $(document).ready(function() {
        $('#itemsTable').DataTable({
            "pageLength": 25,
            "order": [[0, 'desc']],
            "columnDefs": [
                { "orderable": false, "targets": [8, 9] } // Disable sorting for users and actions columns
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
