@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        @if(isset($order))
            Order Items - Order #{{ $order->id }}
        @else
            All Order Items
        @endif
    </h1>
    @if(isset($order))
    <a href="{{ route('cms.orders.order-items.create', $order) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Add Order Item
    </a>
    @endif
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            @if(isset($order))
                Items for Order #{{ $order->id }}
            @else
                All Order Items
            @endif
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        @if(!isset($order))
                        <th>Order ID</th>
                        @endif
                        <th>Item</th>
                        <th>Size</th>
                        <th>Color</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderItems as $orderItem)
                    <tr>
                        @if(!isset($order))
                        <td>
                            <a href="{{ route('cms.orders.show', $orderItem->order_id) }}">
                                #{{ $orderItem->order_id }}
                            </a>
                        </td>
                        @endif
                        <td>
                            @if($orderItem->item)
                                {{ $orderItem->item->name }}
                            @else
                                <span class="text-danger">Item Deleted</span>
                            @endif
                        </td>
                        <td>{{ $orderItem->selected_size }}</td>
                        <td>{{ $orderItem->selected_color }}</td>
                        <td>{{ $orderItem->quantity }}</td>
                        <td>${{ number_format($orderItem->unit_price, 2) }}</td>
                        <td>${{ number_format($orderItem->unit_price * $orderItem->quantity, 2) }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @if(isset($order))
                                <a href="{{ route('cms.orders.order-items.show', [$order, $orderItem]) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('cms.orders.order-items.edit', [$order, $orderItem]) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('cms.orders.order-items.destroy', [$order, $orderItem]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @else
                                <a href="{{ route('cms.orders.show', $orderItem->order_id) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i> View Order
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $orderItems->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "searching": true,
            "ordering": true,
            "info": false
        });
    });
</script>
@endpush