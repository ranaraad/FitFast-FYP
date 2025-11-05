@extends('cms.layouts.app')

@section('page-title', 'Users Management')
@section('page-subtitle', 'Manage system users and permissions')

@section('content')
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit User</h1>
    <a href="{{ route('cms.users.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Users
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Edit User Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('cms.users.update', $user) }}" method="POST" id="userForm">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password (Leave blank to keep current)</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password</label>
                                <input type="password" class="form-control"
                                       id="password_confirmation" name="password_confirmation">
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
                                        <option value="{{ $role->id }}"
                                                {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="measurements_height_cm">Height (cm) *</label>
                                    <input type="number" step="0.1" class="form-control @error('measurements.height_cm') is-invalid @enderror"
                                           id="measurements_height_cm" name="measurements[height_cm]" value="{{ old('measurements.height_cm', $user->measurements['height_cm'] ?? '') }}"
                                           placeholder="e.g., 175.5" min="100" max="250">
                                    @error('measurements.height_cm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="measurements_weight_kg">Weight (kg) *</label>
                                    <input type="number" step="0.1" class="form-control @error('measurements.weight_kg') is-invalid @enderror"
                                           id="measurements_weight_kg" name="measurements[weight_kg]" value="{{ old('measurements.weight_kg', $user->measurements['weight_kg'] ?? '') }}"
                                           placeholder="e.g., 70.5" min="30" max="200">
                                    @error('measurements.weight_kg')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="measurements_bust_cm">Bust (cm)</label>
                                    <input type="number" step="0.1" class="form-control @error('measurements.bust_cm') is-invalid @enderror"
                                           id="measurements_bust_cm" name="measurements[bust_cm]" value="{{ old('measurements.bust_cm', $user->measurements['bust_cm'] ?? '') }}"
                                           placeholder="e.g., 95.5" min="50" max="150">
                                    @error('measurements.bust_cm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="measurements_waist_cm">Waist (cm)</label>
                                    <input type="number" step="0.1" class="form-control @error('measurements.waist_cm') is-invalid @enderror"
                                           id="measurements_waist_cm" name="measurements[waist_cm]" value="{{ old('measurements.waist_cm', $user->measurements['waist_cm'] ?? '') }}"
                                           placeholder="e.g., 75.5" min="40" max="150">
                                    @error('measurements.waist_cm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="measurements_hips_cm">Hips (cm)</label>
                                    <input type="number" step="0.1" class="form-control @error('measurements.hips_cm') is-invalid @enderror"
                                           id="measurements_hips_cm" name="measurements[hips_cm]" value="{{ old('measurements.hips_cm', $user->measurements['hips_cm'] ?? '') }}"
                                           placeholder="e.g., 105.5" min="50" max="200">
                                    @error('measurements.hips_cm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="measurements_shoulder_width_cm">Shoulder Width (cm)</label>
                                    <input type="number" step="0.1" class="form-control @error('measurements.shoulder_width_cm') is-invalid @enderror"
                                           id="measurements_shoulder_width_cm" name="measurements[shoulder_width_cm]" value="{{ old('measurements.shoulder_width_cm', $user->measurements['shoulder_width_cm'] ?? '') }}"
                                           placeholder="e.g., 45.5" min="30" max="70">
                                    @error('measurements.shoulder_width_cm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="measurements_arm_length_cm">Arm Length (cm)</label>
                                    <input type="number" step="0.1" class="form-control @error('measurements.arm_length_cm') is-invalid @enderror"
                                           id="measurements_arm_length_cm" name="measurements[arm_length_cm]" value="{{ old('measurements.arm_length_cm', $user->measurements['arm_length_cm'] ?? '') }}"
                                           placeholder="e.g., 60.5" min="40" max="80">
                                    @error('measurements.arm_length_cm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="measurements_inseam_cm">Inseam (cm)</label>
                                    <input type="number" step="0.1" class="form-control @error('measurements.inseam_cm') is-invalid @enderror"
                                           id="measurements_inseam_cm" name="measurements[inseam_cm]" value="{{ old('measurements.inseam_cm', $user->measurements['inseam_cm'] ?? '') }}"
                                           placeholder="e.g., 80.5" min="50" max="100">
                                    @error('measurements.inseam_cm')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="measurements_body_shape">Body Shape</label>
                                    <select class="form-control @error('measurements.body_shape') is-invalid @enderror"
                                            id="measurements_body_shape" name="measurements[body_shape]">
                                        <option value="">Select Body Shape</option>
                                        <option value="hourglass" {{ old('measurements.body_shape', $user->measurements['body_shape'] ?? '') == 'hourglass' ? 'selected' : '' }}>Hourglass</option>
                                        <option value="pear" {{ old('measurements.body_shape', $user->measurements['body_shape'] ?? '') == 'pear' ? 'selected' : '' }}>Pear</option>
                                        <option value="apple" {{ old('measurements.body_shape', $user->measurements['body_shape'] ?? '') == 'apple' ? 'selected' : '' }}>Apple</option>
                                        <option value="rectangle" {{ old('measurements.body_shape', $user->measurements['body_shape'] ?? '') == 'rectangle' ? 'selected' : '' }}>Rectangle</option>
                                        <option value="inverted triangle" {{ old('measurements.body_shape', $user->measurements['body_shape'] ?? '') == 'inverted triangle' ? 'selected' : '' }}>Inverted Triangle</option>
                                    </select>
                                    @error('measurements.body_shape')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="measurements_fit_preference">Fit Preference</label>
                                    <select class="form-control @error('measurements.fit_preference') is-invalid @enderror"
                                            id="measurements_fit_preference" name="measurements[fit_preference]">
                                        <option value="">Select Fit Preference</option>
                                        <option value="tight" {{ old('measurements.fit_preference', $user->measurements['fit_preference'] ?? '') == 'tight' ? 'selected' : '' }}>Tight</option>
                                        <option value="regular" {{ old('measurements.fit_preference', $user->measurements['fit_preference'] ?? '') == 'regular' ? 'selected' : '' }}>Regular</option>
                                        <option value="loose" {{ old('measurements.fit_preference', $user->measurements['fit_preference'] ?? '') == 'loose' ? 'selected' : '' }}>Loose</option>
                                    </select>
                                    @error('measurements.fit_preference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                              placeholder="Enter your primary address">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shipping_address">Shipping Address</label>
                                    <textarea class="form-control @error('shipping_address') is-invalid @enderror"
                                              id="shipping_address" name="shipping_address" rows="3"
                                              placeholder="Enter shipping address (if different from primary)">{{ old('shipping_address', $user->shipping_address) }}</textarea>
                                    @error('shipping_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Leave blank to use primary address</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_address">Billing Address</label>
                                    <textarea class="form-control @error('billing_address') is-invalid @enderror"
                                              id="billing_address" name="billing_address" rows="3"
                                              placeholder="Enter billing address (if different from primary)">{{ old('billing_address', $user->billing_address) }}</textarea>
                                    @error('billing_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Leave blank to use primary address</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update User
                            </button>
                            <a href="{{ route('cms.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <a href="{{ route('cms.users.show', $user) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> View User
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
        document.getElementById('measurements_height_cm').required = true;
        document.getElementById('measurements_weight_kg').required = true;
        document.getElementById('address').required = true;
    } else {
        userFieldsSection.style.display = 'none';
        // Remove required attribute for non-user roles
        document.getElementById('measurements_height_cm').required = false;
        document.getElementById('measurements_weight_kg').required = false;
        document.getElementById('measurements_bust_cm').required = false;
        document.getElementById('measurements_waist_cm').required = false;
        document.getElementById('measurements_hips_cm').required = false;
        document.getElementById('measurements_shoulder_width_cm').required = false;
        document.getElementById('measurements_arm_length_cm').required = false;
        document.getElementById('measurements_inseam_cm').required = false;
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

    if (password && password !== confirmPassword) {
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
