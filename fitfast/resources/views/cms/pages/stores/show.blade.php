@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Store Details</h1>
    <div>
        <a href="{{ route('cms.stores.edit', $store) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Store
        </a>
        <a href="{{ route('cms.stores.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Stores
        </a>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-lg-8">
        <!-- Store Information Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Store Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">ID</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $store->id }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Name</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $store->name }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Status</p>
                    </div>
                    <div class="col-sm-9">
                        @if($store->status === 'active')
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Description</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $store->description ?? 'No description provided' }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Address</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $store->address ?? 'No address provided' }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Contact Info</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $store->contact_info ?? 'No contact information provided' }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Created At</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $store->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Updated At</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $store->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Statistics Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Store Statistics</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                        <h4>{{ $store->items->count() }}</h4>
                        <p class="text-muted">Total Items</p>
                    </div>
                    <hr>
                    <div class="mb-4">
                        <i class="fas fa-shopping-cart fa-2x text-success mb-2"></i>
                        <h4>{{ $store->orders->count() }}</h4>
                        <p class="text-muted">Total Orders</p>
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
                    <a href="{{ route('cms.stores.edit', $store) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Edit Store
                    </a>
                    <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete Store
                    </button>
                    <form id="delete-form" action="{{ route('cms.stores.destroy', $store) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>

        <!-- Items Preview Card -->
        @if($store->items->count() > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Items</h6>
            </div>
            <div class="card-body">
                @foreach($store->items->take(5) as $item)
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-cube text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">{{ $item->name }}</h6>
                            <small class="text-muted">{{ $item->price ? '$' . number_format($item->price, 2) : 'Price not set' }}</small>
                        </div>
                    </div>
                    @if(!$loop->last)
                    <hr class="my-2">
                    @endif
                @endforeach
                @if($store->items->count() > 5)
                    <div class="text-center mt-3">
                        <a href="#" class="btn btn-sm btn-outline-primary">View All Items</a>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this! All associated items and orders will be affected!",
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
</script>
@endpush
