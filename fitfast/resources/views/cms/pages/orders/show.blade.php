@extends('cms.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Order #{{ $order->id }}</h1>
                <div>
                    <a href="{{ route('cms.orders.edit', $order) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Order
                    </a>
                    <a href="{{ route('cms.orders.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <!-- Order Details -->
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Order Information</h6>
                            <span class="badge badge-{{ $order->getStatusBadgeClass() }} badge-lg">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Store Information</h6>
                                    <p class="mb-1"><strong>Store:</strong> {{ $order->store->name }}</p>
                                    <p class="mb-1"><strong>Address:</strong> {{ $order->store->address }}</p>
                                    <p class="mb-1"><strong>Contact:</strong> {{ $order->store->contact_info }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Customer Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> {{ $order->user->name }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $order->user->email }}</p>
                                    <p class="mb-1"><strong>Phone:</strong> {{ $order->user->mobile ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Order Items</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Size</th>
                                            <th>Color</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->orderItems as $orderItem)
                                        <tr>
                                            <td>
                                                <strong>{{ $orderItem->item->name }}</strong>
                                                @if($orderItem->selected_brand)
                                                    <br><small class="text-muted">Brand: {{ $orderItem->selected_brand }}</small>
                                                @endif
                                            </td>
                                            <td>${{ number_format($orderItem->unit_price, 2) }}</td>
                                            <td>{{ $orderItem->quantity }}</td>
                                            <td>{{ $orderItem->selected_size }}</td>
                                            <td>{{ $orderItem->selected_color }}</td>
                                            <td>${{ number_format($orderItem->unit_price * $orderItem->quantity, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-right"><strong>Subtotal:</strong></td>
                                            <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary & Actions -->
                <div class="col-lg-4">
                    <!-- Order Summary -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Order Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax:</span>
                                <span>$0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between font-weight-bold">
                                <span>Total:</span>
                                <span>${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Order Actions</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('cms.orders.update-status', $order) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="form-group">
                                    <label for="status">Update Status</label>
                                    <select name="status" id="status" class="form-control">
                                        @foreach(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $status)
                                            <option value="{{ $status }}" 
                                                {{ $order->status == $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    Update Status
                                </button>
                            </form>

                            <!-- Delivery Information -->
                            @if($order->delivery)
                            <div class="mt-3">
                                <h6>Delivery Information</h6>
                                <p class="mb-1"><strong>Status:</strong> {{ ucfirst($order->delivery->status) }}</p>
                                <p class="mb-1"><strong>Carrier:</strong> {{ $order->delivery->carrier ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Tracking ID:</strong> {{ $order->delivery->tracking_id ?? 'N/A' }}</p>
                                @if($order->delivery->estimated_delivery)
                                    <p class="mb-1"><strong>Estimated Delivery:</strong> 
                                        {{ \Carbon\Carbon::parse($order->delivery->estimated_delivery)->format('M d, Y') }}
                                    </p>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Order Timeline -->
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Order Timeline</h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item {{ $order->status == 'delivered' ? 'completed' : '' }}">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Order Created</h6>
                                        <p>{{ $order->created_at->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                                @if(in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered']))
                                <div class="timeline-item {{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'completed' : '' }}">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Confirmed</h6>
                                        <p>Order confirmed</p>
                                    </div>
                                </div>
                                @endif
                                @if(in_array($order->status, ['shipped', 'delivered']))
                                <div class="timeline-item {{ $order->status == 'delivered' ? 'completed' : '' }}">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Shipped</h6>
                                        <p>Order shipped to customer</p>
                                    </div>
                                </div>
                                @endif
                                @if($order->status == 'delivered')
                                <div class="timeline-item completed">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Delivered</h6>
                                        <p>Order delivered successfully</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #e3e6f0;
        border: 3px solid #fff;
    }
    .timeline-item.completed .timeline-marker {
        background: #1cc88a;
    }
    .timeline-content h6 {
        margin-bottom: 5px;
        font-weight: 600;
    }
    .timeline-content p {
        margin-bottom: 0;
        color: #6e707e;
        font-size: 0.875rem;
    }
    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
</style>
@endpush