@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Order Item Details</h1>
    <div>
        <a href="{{ route('cms.orders.order-items.edit', [$order, $orderItem]) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Item
        </a>
        <a href="{{ route('cms.orders.order-items.index', $order) }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Items
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Item Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Item Name:</strong><br>
                            @if($orderItem->item)
                                {{ $orderItem->item->name }}
                            @else
                                <span class="text-danger">Item Deleted</span>
                            @endif
                        </p>
                        
                        <p><strong>Size:</strong><br>{{ $orderItem->selected_size }}</p>
                        <p><strong>Color:</strong><br>{{ $orderItem->selected_color }}</p>
                    </div>
                    
                    <div class="col-md-6">
                        <p><strong>Quantity:</strong><br>{{ $orderItem->quantity }}</p>
                        <p><strong>Unit Price:</strong><br>${{ number_format($orderItem->unit_price, 2) }}</p>
                        <p><strong>Total Price:</strong><br>${{ number_format($orderItem->unit_price * $orderItem->quantity, 2) }}</p>
                    </div>
                </div>
                
                @if($orderItem->selected_brand)
                <div class="row">
                    <div class="col-12">
                        <p><strong>Brand:</strong><br>{{ $orderItem->selected_brand }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Information</h6>
            </div>
            <div class="card-body">
                <p><strong>Order ID:</strong> 
                    <a href="{{ route('cms.orders.show', $order) }}">#{{ $order->id }}</a>
                </p>
                <p><strong>Customer:</strong> {{ $order->user->name ?? 'N/A' }}</p>
                <p><strong>Store:</strong> {{ $order->store->name ?? 'N/A' }}</p>
                <p><strong>Order Total:</strong> ${{ number_format($order->total_amount, 2) }}</p>
                <p><strong>Status:</strong> 
                    <span class="badge badge-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </p>
            </div>
        </div>
        
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('cms.orders.order-items.edit', [$order, $orderItem]) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Item
                    </a>
                    <form action="{{ route('cms.orders.order-items.destroy', [$order, $orderItem]) }}" method="POST" class="d-grid">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this order item?')">
                            <i class="fas fa-trash"></i> Delete Item
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection