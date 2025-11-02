@extends('cms.layouts.store-admin-app')

@section('page-title', 'Delivery Management')
@section('page-subtitle', 'Manage deliveries for your stores')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Delivery Management</h1>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Deliveries
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_deliveries'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_deliveries'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            In Transit
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_deliveries'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Delivered
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['delivered_deliveries'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Search Deliveries</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('store-admin.deliveries.search') }}" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tracking_id">Tracking ID</label>
                        <input type="text" class="form-control" id="tracking_id" name="tracking_id"
                               value="{{ request('tracking_id') }}" placeholder="Enter tracking ID">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="out_for_delivery" {{ request('status') == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="carrier">Carrier</label>
                        <select class="form-control" id="carrier" name="carrier">
                            <option value="">All Carriers</option>
                            @foreach($carriers as $key => $value)
                                <option value="{{ $key }}" {{ request('carrier') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="store_id">Store</label>
                        <select class="form-control" id="store_id" name="store_id">
                            <option value="">All Stores</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search Deliveries
                    </button>
                    <a href="{{ route('store-admin.deliveries.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Deliveries Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">My Store Deliveries</h6>
        <div>
            @if($stats['pending_deliveries'] > 0)
            <span class="badge badge-warning mr-2">
                <i class="fas fa-clock"></i> {{ $stats['pending_deliveries'] }} Pending
            </span>
            @endif
            <span class="badge badge-primary">
                <i class="fas fa-truck"></i> {{ $deliveries->total() }} Total
            </span>
        </div>
    </div>
    <div class="card-body">
        @if($deliveries->isEmpty())
        <div class="text-center py-4">
            <i class="fas fa-truck fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No deliveries found</h5>
            <p class="text-muted">No deliveries match your search criteria.</p>
            <a href="{{ route('store-admin.deliveries.index') }}" class="btn btn-primary">View All Deliveries</a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Tracking ID</th>
                        <th>Order</th>
                        <th>Store</th>
                        <th>Customer</th>
                        <th>Carrier</th>
                        <th>Status</th>
                        <th>Est. Delivery</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliveries as $delivery)
                    <tr>
                        <td>
                            @if($delivery->tracking_id)
                                <strong>{{ $delivery->tracking_id }}</strong>
                                <br>
                                <small>
                                    <a href="#" class="text-primary" data-toggle="modal" data-target="#editTrackingModal{{ $delivery->id }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </small>
                            @else
                                <span class="text-muted">No tracking</span>
                                <br>
                                <small>
                                    <a href="#" class="text-primary" data-toggle="modal" data-target="#addTrackingModal{{ $delivery->id }}">
                                        <i class="fas fa-plus-circle"></i> Add Tracking
                                    </a>
                                </small>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">${{ number_format($delivery->order->total_amount, 2) }}</small>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $delivery->order->store->name }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="icon-circle bg-primary" style="width: 30px; height: 30px; font-size: 12px;">
                                        <span class="text-white">{{ substr($delivery->order->user->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <strong>{{ $delivery->order->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $delivery->order->user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($delivery->carrier)
                                <span class="badge badge-secondary">{{ $delivery->carrier }}</span>
                            @else
                                <span class="text-muted">Not set</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $delivery->status === 'delivered' ? 'success' : ($delivery->status === 'failed' ? 'danger' : ($delivery->status === 'pending' ? 'warning' : 'info')) }} p-2">
                                {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                            </span>
                            <br>
                            <small>
                                <a href="#" class="text-primary" data-toggle="modal" data-target="#updateStatusModal{{ $delivery->id }}">
                                    <i class="fas fa-sync"></i> Change
                                </a>
                            </small>
                        </td>
                        <td>
                            @if($delivery->estimated_delivery)
                                {{ $delivery->estimated_delivery->format('M j, Y') }}
                                @if($delivery->estimated_delivery->isPast() && !$delivery->isCompleted())
                                    <br><small class="text-danger">Overdue</small>
                                @endif
                            @else
                                <span class="text-muted">Not set</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                @if(!$delivery->isCompleted())
                                    @if(!$delivery->tracking_id)
                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#addTrackingModal{{ $delivery->id }}" title="Add Tracking">
                                        <i class="fas fa-shipping-fast"></i>
                                    </button>
                                    @endif
                                    <form action="{{ route('store-admin.deliveries.mark-delivered', $delivery) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm mark-delivered-btn"
                                                data-delivery-id="{{ $delivery->id }}"
                                                title="Mark as Delivered">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                @if(in_array($delivery->status, ['pending', 'failed']))
                                <form action="{{ route('store-admin.deliveries.destroy', $delivery) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm delete-delivery-btn"
                                            data-delivery-id="{{ $delivery->id }}"
                                            title="Delete Delivery">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Add Tracking Modal -->
                    <div class="modal fade" id="addTrackingModal{{ $delivery->id }}" tabindex="-1" role="dialog" aria-labelledby="addTrackingModalLabel{{ $delivery->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addTrackingModalLabel{{ $delivery->id }}">Add Tracking Information</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('store-admin.deliveries.add-tracking', $delivery) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="tracking_id{{ $delivery->id }}">Tracking ID *</label>
                                            <input type="text" class="form-control" id="tracking_id{{ $delivery->id }}" name="tracking_id" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="carrier{{ $delivery->id }}">Carrier *</label>
                                            <select class="form-control" id="carrier{{ $delivery->id }}" name="carrier" required>
                                                <option value="">Select Carrier</option>
                                                @foreach($carriers as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Add Tracking</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Tracking Modal -->
                    <div class="modal fade" id="editTrackingModal{{ $delivery->id }}" tabindex="-1" role="dialog" aria-labelledby="editTrackingModalLabel{{ $delivery->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editTrackingModalLabel{{ $delivery->id }}">Edit Tracking Information</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('store-admin.deliveries.update-tracking', $delivery) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="edit_tracking_id{{ $delivery->id }}">Tracking ID *</label>
                                            <input type="text" class="form-control" id="edit_tracking_id{{ $delivery->id }}" name="tracking_id" value="{{ $delivery->tracking_id }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="edit_carrier{{ $delivery->id }}">Carrier *</label>
                                            <select class="form-control" id="edit_carrier{{ $delivery->id }}" name="carrier" required>
                                                <option value="">Select Carrier</option>
                                                @foreach($carriers as $key => $value)
                                                    <option value="{{ $key }}" {{ $delivery->carrier == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update Tracking</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Update Status Modal -->
                    <div class="modal fade" id="updateStatusModal{{ $delivery->id }}" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel{{ $delivery->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateStatusModalLabel{{ $delivery->id }}">Update Delivery Status</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('store-admin.deliveries.update-status', $delivery) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="status{{ $delivery->id }}">Status *</label>
                                            <select class="form-control" id="status{{ $delivery->id }}" name="status" required>
                                                <option value="pending" {{ $delivery->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="shipped" {{ $delivery->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                <option value="in_transit" {{ $delivery->status == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                                <option value="out_for_delivery" {{ $delivery->status == 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                                <option value="delivered" {{ $delivery->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                <option value="failed" {{ $delivery->status == 'failed' ? 'selected' : '' }}>Failed</option>
                                            </select>
                                        </div>
                                        @if(!$delivery->tracking_id)
                                        <div class="form-group">
                                            <label for="status_tracking_id{{ $delivery->id }}">Tracking ID</label>
                                            <input type="text" class="form-control" id="status_tracking_id{{ $delivery->id }}" name="tracking_id" placeholder="Optional: Add tracking ID">
                                        </div>
                                        <div class="form-group">
                                            <label for="status_carrier{{ $delivery->id }}">Carrier</label>
                                            <select class="form-control" id="status_carrier{{ $delivery->id }}" name="carrier">
                                                <option value="">Select Carrier (Optional)</option>
                                                @foreach($carriers as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update Status</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing {{ $deliveries->firstItem() ?? 0 }} to {{ $deliveries->lastItem() ?? 0 }} of {{ $deliveries->total() }} entries
            </div>
            {{ $deliveries->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.icon-circle {
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.075);
}
</style>
@endpush

@push('scripts')
<script>
// SweetAlert for delete confirmation
document.querySelectorAll('.delete-delivery-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const form = this.closest('form');
        const deliveryId = this.getAttribute('data-delivery-id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This delivery will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

// SweetAlert for mark as delivered confirmation
document.querySelectorAll('.mark-delivered-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const form = this.closest('form');
        const deliveryId = this.getAttribute('data-delivery-id');

        Swal.fire({
            title: 'Mark as Delivered?',
            text: "This will mark the delivery as completed.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, mark as delivered!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

// Show success messages with SweetAlert
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        timer: 4000,
        showConfirmButton: true
    });
@endif
</script>
@endpush
