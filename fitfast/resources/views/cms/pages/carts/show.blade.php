@extends('cms.layouts.app')

@section('page-title', 'Carts Management')
@section('page-subtitle', 'Manage system carts')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Cart Details</h1>
    <div>
        <a href="{{ route('cms.carts.edit', $cart) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Cart
        </a>
        <a href="{{ route('cms.carts.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Carts
        </a>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-lg-8">
        <!-- Cart Items Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Cart Items</h6>
                <span class="badge badge-primary">{{ $cart->total_items }} items</span>
            </div>
            <div class="card-body">
                @if($cart->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Cart is Empty</h5>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Size</th>
                                    <th>Color</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cart->cartItems as $cartItem)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-tshirt fa-2x text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0">{{ $cartItem->item->name }}</h6>
                                                <small class="text-muted">Store: {{ $cartItem->item->store->name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>${{ number_format($cartItem->item_price, 2) }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $cartItem->quantity }}</span>
                                    </td>
                                    <td>
                                        @if($cartItem->selected_size)
                                            <span class="badge badge-info">{{ $cartItem->selected_size }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $cartItem->selected_color }}; color: white;">
                                            {{ $cartItem->selected_color }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>${{ number_format($cartItem->total_price, 2) }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Cart Summary Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Cart Summary</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>User:</strong>
                    </div>
                    <div class="col-6 text-right">
                        {{ $cart->user->name }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Email:</strong>
                    </div>
                    <div class="col-6 text-right">
                        {{ $cart->user->email }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Total Items:</strong>
                    </div>
                    <div class="col-6 text-right">
                        {{ $cart->total_items }}
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Subtotal:</strong>
                    </div>
                    <div class="col-6 text-right">
                        ${{ number_format($cart->cart_total, 2) }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>Created:</strong>
                    </div>
                    <div class="col-6 text-right">
                        {{ $cart->created_at->format('M d, Y') }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <strong>Last Updated:</strong>
                    </div>
                    <div class="col-6 text-right">
                        {{ $cart->updated_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('cms.carts.edit', $cart) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Edit Cart
                    </a>
                    @if(!$cart->isEmpty())
                    <button type="button" class="btn btn-warning btn-block" onclick="clearCart()">
                        <i class="fas fa-broom"></i> Clear Cart
                    </button>
                    @endif
                    <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Cart
                    </button>
                    <form id="delete-form" action="{{ route('cms.carts.destroy', $cart) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                    <form id="clear-form" action="{{ route('cms.carts.clear', $cart) }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete() {
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
                document.getElementById('delete-form').submit();
            }
        });
    }

    function clearCart() {
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
                document.getElementById('clear-form').submit();
            }
        });
    }
</script>
@endpush
