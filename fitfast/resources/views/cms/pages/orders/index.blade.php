@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Orders Management</h1>
    <a href="{{ route('cms.orders.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Create New Order
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Orders</h6>
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
                    <table class="table table-bordered" id="ordersTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Store</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>
                                    <strong>{{ $order->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $order->user->email }}</small>
                                </td>
                                <td>{{ $order->store->name }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $order->total_items }} items</span>
                                </td>
                                <td>
                                    <strong>${{ number_format($order->total_amount, 2) }}</strong>
                                </td>
                                <td>
                                    {!! $order->status_badge !!}
                                </td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('cms.orders.show', $order) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('cms.orders.edit', $order) }}" class="btn btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($order->canBeCancelled())
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $order->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                    <form id="delete-form-{{ $order->id }}" action="{{ route('cms.orders.destroy', $order) }}" method="POST" class="d-none">
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
        $('#ordersTable').DataTable({
            "pageLength": 25,
            "order": [[0, 'desc']],
            "columnDefs": [
                { "orderable": false, "targets": [7] } // Disable sorting for actions column
            ]
        });
    });

    function confirmDelete(orderId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the order!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + orderId).submit();
            }
        });
    }
</script>
@endpush