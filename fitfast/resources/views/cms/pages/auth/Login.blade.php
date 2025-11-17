@extends('cms.layouts.auth')

@section('title', 'CMS Login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fas fa-lock me-2"></i>CMS Login
                    </h4>
                    <small class="opacity-75">Content Management System</small>
                </div>
                <div class="card-body p-5">
                    <form method="POST" action="{{ route('cms.login') }}">
                        @csrf

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
                                       required
                                       autofocus>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

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
                                       placeholder="Enter your password"
                                       required>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="remember"
                                       id="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    Remember Me
                                </label>
                            </div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to CMS
                            </button>
                        </div>

                        <!-- Registration Link -->
                        <div class="text-center">
                            <hr class="mb-3">
                            <p class="text-muted mb-2">Don't have an account?</p>
                            <a href="{{ route('cms.register') }}" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i>Create Admin Account
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Restricted to Super Admin and Store Admin only
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
    background: linear-gradient(135deg, #800020 0%, #6A0D3B 30%, #4A235A 70%, #2C3E50 100%);
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
    background: linear-gradient(135deg, #800020 0%, #9D2235 50%, #C44569 100%) !important;
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
    background: linear-gradient(135deg, #800020 0%, #9D2235 50%, #C44569 100%);
    border: none;
    border-radius: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(128, 0, 32, 0.3);
    font-weight: 600;
    letter-spacing: 0.5px;
}
.btn-primary:hover {
    background: linear-gradient(135deg, #900024 0%, #AD2A40 50%, #D4537A 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(128, 0, 32, 0.4);
}
.btn-outline-primary {
    border: 2px solid #800020;
    color: #800020;
    border-radius: 10px;
    background: transparent;
    font-weight: 500;
    transition: all 0.3s ease;
}
.btn-outline-primary:hover {
    background: linear-gradient(135deg, #800020 0%, #9D2235 50%, #C44569 100%);
    border-color: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(128, 0, 32, 0.3);
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
hr {
    border: none;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, rgba(128, 0, 32, 0.3) 50%, transparent 100%);
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
</style>
@endpush
