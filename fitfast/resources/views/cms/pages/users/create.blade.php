@extends('cms.layouts.app')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Create New User</h1>
    <a href="{{ route('cms.users.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Users
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.users.store') }}" method="POST" id="userForm">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password *</label>
                                <input type="password" class="form-control"
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role_id">Role *</label>
                                <select class="form-control @error('role_id') is-invalid @enderror"
                                        id="role_id" name="role_id" required onchange="toggleUserFields()">
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}"
                                       placeholder="e.g., +1 (555) 123-4567">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- User-specific fields (measurements + addresses) -->
                    <div id="user-fields-section" style="display: none;">
                        <!-- Measurements Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="text-primary">Body Measurements (Required for Users)</h5>
                                <p class="text-muted">These measurements are required for clothing recommendations.</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="height_cm">Height (cm) *</label>
                                    <input type="number" step="0.1" class="form-control @error('height_cm') is-invalid @enderror"
                                           id="height_cm" name="height_cm" value="{{ old('height_cm') }}"
                                           placeholder="e.g., 175.5" min="100" max="250">
                                    @error('height_cm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="weight_kg">Weight (kg) *</label>
                                    <input type="number" step="0.1" class="form-control @error('weight_kg') is-invalid @enderror"
                                           id="weight_kg" name="weight_kg" value="{{ old('weight_kg') }}"
                                           placeholder="e.g., 70.5" min="30" max="200">
                                    @error('weight_kg')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="shoe_size">Shoe Size *</label>
                                    <input type="number" step="0.5" class="form-control @error('shoe_size') is-invalid @enderror"
                                           id="shoe_size" name="shoe_size" value="{{ old('shoe_size') }}"
                                           placeholder="e.g., 40.5" min="30" max="50">
                                    @error('shoe_size')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror>
                                </div>
                            </div>
                        </div>

                        <!-- Address Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="text-primary">Address Information (Required for Users)</h5>
                                <p class="text-muted">These addresses are required for order delivery and billing.</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="address">Primary Address *</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror"
                                              id="address" name="address" rows="3"
                                              placeholder="Enter your primary address" required>{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shipping_address">Shipping Address *</label>
                                    <textarea class="form-control @error('shipping_address') is-invalid @enderror"
                                              id="shipping_address" name="shipping_address" rows="3"
                                              placeholder="Enter shipping address (if different from primary)">{{ old('shipping_address') }}</textarea>
                                    @error('shipping_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror>
                                    <small class="form-text text-muted">Leave blank to use primary address</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_address">Billing Address *</label>
                                    <textarea class="form-control @error('billing_address') is-invalid @enderror"
                                              id="billing_address" name="billing_address" rows="3"
                                              placeholder="Enter billing address (if different from primary)">{{ old('billing_address') }}</textarea>
                                    @error('billing_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror>
                                    <small class="form-text text-muted">Leave blank to use primary address</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create User
                            </button>
                            <a href="{{ route('cms.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleUserFields() {
    const roleSelect = document.getElementById('role_id');
    const userFieldsSection = document.getElementById('user-fields-section');
    const selectedOption = roleSelect.options[roleSelect.selectedIndex];
    const selectedRoleText = selectedOption.text.toLowerCase().trim();

    // Show user-specific fields only for 'user' role
    const isUserRole = selectedRoleText === 'user';

    if (isUserRole) {
        userFieldsSection.style.display = 'block';
        // Make user fields required
        document.getElementById('height_cm').required = true;
        document.getElementById('weight_kg').required = true;
        document.getElementById('shoe_size').required = true;
        document.getElementById('address').required = true;
    } else {
        userFieldsSection.style.display = 'none';
        // Remove required attribute for non-user roles
        document.getElementById('height_cm').required = false;
        document.getElementById('weight_kg').required = false;
        document.getElementById('shoe_size').required = false;
        document.getElementById('address').required = false;
        document.getElementById('shipping_address').required = false;
        document.getElementById('billing_address').required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleUserFields();
});

// Form validation
$('#userForm').on('submit', function() {
    const password = $('#password').val();
    const confirmPassword = $('#password_confirmation').val();

    if (password !== confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Password Mismatch',
            text: 'Password and confirmation password do not match.'
        });
        return false;
    }

    return true;
});
</script>
@endpush
