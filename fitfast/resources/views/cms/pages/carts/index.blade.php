@extends('cms.layouts.app')

@section('page-title', 'Carts Management')
@section('page-subtitle', 'Manage system carts')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Carts Management</h1>
    <a href="{{ route('cms.carts.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Create New Cart
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Carts</h6>
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
                    <table class="table table-bordered" id="cartsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Last Updated</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($carts as $cart)
                            <tr>
                                <td>{{ $cart->id }}</td>
                                <td>
                                    <strong>{{ $cart->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $cart->user->email }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $cart->total_items }} items</span>
                                </td>
                                <td>
                                    <strong>${{ number_format($cart->cart_total, 2) }}</strong>
                                </td>
                                <td>{{ $cart->updated_at->diffForHumans() }}</td>
                                <td>
                                    @if($cart->isEmpty())
                                        <span class="badge badge-secondary">Empty</span>
                                    @else
                                        <span class="badge badge-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('cms.carts.show', $cart) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('cms.carts.edit', $cart) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-warning" onclick="clearCart({{ $cart->id }})">
                                            <i class="fas fa-broom"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $cart->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $cart->id }}" action="{{ route('cms.carts.destroy', $cart) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <form id="clear-form-{{ $cart->id }}" action="{{ route('cms.carts.clear', $cart) }}" method="POST" class="d-none">
                                        @csrf
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
        $('#cartsTable').DataTable({
            "pageLength": 25,
            "order": [[0, 'desc']],
            "columnDefs": [
                { "orderable": false, "targets": [6] } // Disable sorting for actions column
            ]
        });
    });

    function confirmDelete(cartId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the cart and all its items!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + cartId).submit();
            }
        });
    }

    function clearCart(cartId) {
        Swal.fire({
            title: 'Clear Cart?',
            text: "This will remove all items from the cart!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, clear it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('clear-form-' + cartId).submit();
            }
        });
    }
</script>
@endpush
