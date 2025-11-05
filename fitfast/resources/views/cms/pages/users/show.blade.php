@extends('cms.layouts.app')

@section('page-title', 'Users Management')
@section('page-subtitle', 'Manage system users and permissions')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">User Details</h1>
    <a href="{{ route('cms.users.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Users
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">User Information</h6>
                <div>
                    <a href="{{ route('cms.users.edit', $user) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Name</label>
                            <p class="form-control-plaintext">{{ $user->name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Email</label>
                            <p class="form-control-plaintext">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Role</label>
                            <p class="form-control-plaintext">
                                <span class="badge badge-primary">{{ $user->role->name ?? 'No Role' }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Email Verified</label>
                            <p class="form-control-plaintext">
                                @if($user->email_verified_at)
                                    <span class="badge badge-success">Verified on {{ $user->email_verified_at->format('M j, Y') }}</span>
                                @else
                                    <span class="badge badge-warning">Not Verified</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Created At</label>
                            <p class="form-control-plaintext">{{ $user->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Last Updated</label>
                            <p class="form-control-plaintext">{{ $user->updated_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- User-specific fields (measurements + addresses) -->
                @if($user->role && strtolower($user->role->name) === 'user')
                <!-- Measurements Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="text-primary border-bottom pb-2">Body Measurements</h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Height (cm)</label>
                            <p class="form-control-plaintext">
                                {{ $user->measurements['height_cm'] ?? 'Not set' }}
                                @if(isset($user->measurements['height_cm']))
                                    <small class="text-muted">cm</small>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Weight (kg)</label>
                            <p class="form-control-plaintext">
                                {{ $user->measurements['weight_kg'] ?? 'Not set' }}
                                @if(isset($user->measurements['weight_kg']))
                                    <small class="text-muted">kg</small>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Bust (cm)</label>
                            <p class="form-control-plaintext">
                                {{ $user->measurements['bust_cm'] ?? 'Not set' }}
                                @if(isset($user->measurements['bust_cm']))
                                    <small class="text-muted">cm</small>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Waist (cm)</label>
                            <p class="form-control-plaintext">
                                {{ $user->measurements['waist_cm'] ?? 'Not set' }}
                                @if(isset($user->measurements['waist_cm']))
                                    <small class="text-muted">cm</small>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Hips (cm)</label>
                            <p class="form-control-plaintext">
                                {{ $user->measurements['hips_cm'] ?? 'Not set' }}
                                @if(isset($user->measurements['hips_cm']))
                                    <small class="text-muted">cm</small>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Shoulder Width (cm)</label>
                            <p class="form-control-plaintext">
                                {{ $user->measurements['shoulder_width_cm'] ?? 'Not set' }}
                                @if(isset($user->measurements['shoulder_width_cm']))
                                    <small class="text-muted">cm</small>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Arm Length (cm)</label>
                            <p class="form-control-plaintext">
                                {{ $user->measurements['arm_length_cm'] ?? 'Not set' }}
                                @if(isset($user->measurements['arm_length_cm']))
                                    <small class="text-muted">cm</small>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="font-weight-bold">Inseam (cm)</label>
                            <p class="form-control-plaintext">
                                {{ $user->measurements['inseam_cm'] ?? 'Not set' }}
                                @if(isset($user->measurements['inseam_cm']))
                                    <small class="text-muted">cm</small>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Body Shape</label>
                            <p class="form-control-plaintext">
                                @if(isset($user->measurements['body_shape']))
                                    <span class="badge badge-info text-capitalize">{{ $user->measurements['body_shape'] }}</span>
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Fit Preference</label>
                            <p class="form-control-plaintext">
                                @if(isset($user->measurements['fit_preference']))
                                    <span class="badge badge-info text-capitalize">{{ $user->measurements['fit_preference'] }}</span>
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="text-primary border-bottom pb-2">Address Information</h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="font-weight-bold">Primary Address</label>
                            <p class="form-control-plaintext">{{ $user->address ?? 'Not set' }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Shipping Address</label>
                            <p class="form-control-plaintext">
                                {{ $user->shipping_address ?? 'Same as primary address' }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Billing Address</label>
                            <p class="form-control-plaintext">
                                {{ $user->billing_address ?? 'Same as primary address' }}
                            </p>
                        </div>
                    </div>
                </div>
                @else
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Measurement and address information is only available for regular users.
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Additional Information Card -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">User Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Orders
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $user->orders->count() }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Reviews
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $user->reviews->count() }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-star fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Payment Methods
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $user->paymentMethods->count() }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-credit-card fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Support Tickets
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $user->chatSupportTickets->count() }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-headset fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <a href="{{ route('cms.users.edit', $user) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit User
                </a>
                <a href="{{ route('cms.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                @if($user->id !== auth()->id())
                <form action="{{ route('cms.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete User
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
