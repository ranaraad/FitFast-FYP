@extends('cms.layouts.auth')

@section('title', 'CMS Registration')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>CMS Registration
                    </h4>
                    <small class="opacity-75">Create Admin Account</small>
                </div>
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('cms.register') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="name" class="form-label">Full Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text"
                                               class="form-control @error('name') is-invalid @enderror"
                                               id="name"
                                               name="name"
                                               value="{{ old('name') }}"
                                               placeholder="Enter your full name"
                                               required
                                               autofocus>
                                    </div>
                                    @error('name')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               id="email"
                                               name="email"
                                               value="{{ old('email') }}"
                                               placeholder="Enter your email"
                                               required>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-key"></i>
                                        </span>
                                        <input type="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               id="password"
                                               name="password"
                                               placeholder="Create a password"
                                               required>
                                    </div>
                                    @error('password')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Password must be at least 8 characters long.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-key"></i>
                                        </span>
                                        <input type="password"
                                               class="form-control"
                                               id="password_confirmation"
                                               name="password_confirmation"
                                               placeholder="Confirm your password"
                                               required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="role_id" class="form-label">Account Type</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user-tag"></i>
                                        </span>
                                        <select class="form-control @error('role_id') is-invalid @enderror"
                                                id="role_id"
                                                name="role_id"
                                                required>
                                            <option value="">Select Account Type</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('role_id')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Choose between Super Admin or Store Admin access.
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label d-block">&nbsp;</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input @error('terms') is-invalid @enderror"
                                               type="checkbox"
                                               name="terms"
                                               id="terms"
                                               required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" class="text-primary">Terms of Service</a>
                                            and <a href="#" class="text-primary">Privacy Policy</a>
                                        </label>
                                        @error('terms')
                                            <div class="invalid-feedback d-block">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mb-1">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <small class="text-muted">
                        Already have an account?
                        <a href="{{ route('cms.login') }}" class="text-primary">Login here</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.body {
    background: linear-gradient(135deg, #2C3E50 0%, #4A235A 30%, #6A0D3B 70%, #800020 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
}
.card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 15px 35px rgba(128, 0, 32, 0.2);
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}
.card-header {
    border-radius: 15px 15px 0 0 !important;
    border: none;
    background: linear-gradient(135deg, #C44569 0%, #9D2235 50%, #800020 100%) !important;
    box-shadow: 0 4px 15px rgba(128, 0, 32, 0.3);
    position: relative;
    overflow: hidden;
}
.card-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
    animation: shimmer 3s infinite;
}
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.input-group-text {
    background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%);
    border-right: none;
    color: #800020;
    font-weight: 500;
}
.form-control {
    border-left: none;
    padding-left: 0;
    transition: all 0.3s ease;
    background: #F8F9FA;
}
.form-control:focus {
    border-color: #C44569;
    box-shadow: 0 0 0 0.2rem rgba(196, 69, 105, 0.15);
    background: white;
}
.form-control:focus + .input-group-text {
    background: linear-gradient(135deg, #800020 0%, #C44569 100%);
    color: white;
    border-color: #C44569;
}
.btn-primary {
    background: linear-gradient(135deg, #C44569 0%, #9D2235 50%, #800020 100%);
    border: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(128, 0, 32, 0.3);
    font-weight: 600;
    letter-spacing: 0.5px;
}
.btn-primary:hover {
    background: linear-gradient(135deg, #D4537A 0%, #AD2A40 50%, #900024 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(128, 0, 32, 0.4);
}
.text-primary {
    color: #800020 !important;
}
.card-footer {
    background: linear-gradient(135deg, #F8F9FA 0%, #E9ECEF 100%) !important;
    border-top: 1px solid rgba(128, 0, 32, 0.1);
}
.form-check-input:checked {
    background-color: #800020;
    border-color: #800020;
}
.alert-danger {
    background: linear-gradient(135deg, rgba(128, 0, 32, 0.1) 0%, rgba(196, 69, 105, 0.1) 100%);
    border: 1px solid rgba(128, 0, 32, 0.2);
    color: #800020;
    border-radius: 10px;
}
.text-muted {
    color: #6c757d !important;
}
.form-check-label {
    color: #495057;
    font-weight: 500;
}
.input-group {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.form-text {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
@endpush
