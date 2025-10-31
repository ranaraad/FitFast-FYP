@extends('cms.layouts.app')

@section('page-title', 'Users Management')
@section('page-subtitle', 'Manage system users and permissions')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">User Details</h1>
    <div>
        <a href="{{ route('cms.users.edit', $user) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit User
        </a>
        <a href="{{ route('cms.users.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Users
        </a>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">ID</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $user->id }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Name</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $user->name }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Email</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $user->email }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Role</p>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge badge-info">{{ $user->role->name ?? 'No Role' }}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Email Verified</p>
                    </div>
                    <div class="col-sm-9">
                        @if($user->email_verified_at)
                            <span class="badge badge-success">Verified on {{ $user->email_verified_at->format('M d, Y') }}</span>
                        @else
                            <span class="badge badge-warning">Not Verified</span>
                        @endif
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Created At</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $user->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Updated At</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $user->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Measurements Card -->
        @if($user->measurements && count($user->measurements) > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Measurements</h6>
            </div>
            <div class="card-body">
                @foreach($user->measurements as $key => $value)
                    <div class="row">
                        <div class="col-sm-6">
                            <p class="mb-0 font-weight-bold text-capitalize">{{ str_replace('_', ' ', $key) }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted mb-0">
                                @if(is_array($value))
                                    {{ json_encode($value) }}
                                @else
                                    {{ $value }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @if(!$loop->last)
                    <hr class="my-2">
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        @if($user->address || $user->shipping_address || $user->billing_address)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Address Information</h6>
            </div>
            <div class="card-body">
                @if($user->address)
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Primary Address</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $user->address }}</p>
                    </div>
                </div>
                <hr>
                @endif

                @if($user->shipping_address)
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Shipping Address</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $user->shipping_address }}</p>
                    </div>
                </div>
                <hr>
                @endif

                @if($user->billing_address)
                <div class="row">
                    <div class="col-sm-3">
                        <p class="mb-0 font-weight-bold">Billing Address</p>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted mb-0">{{ $user->billing_address }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif


        <!-- Quick Actions Card -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('cms.users.edit', $user) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    <button type="button" class="btn btn-danger btn-block" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete User
                    </button>
                    <form id="delete-form" action="{{ route('cms.users.destroy', $user) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
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
            text: "You won't be able to revert this!",
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
