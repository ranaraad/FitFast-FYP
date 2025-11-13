@extends('cms.layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>Verify Your Email
                    </h4>
                    <small class="opacity-75">Email Verification Required</small>
                </div>
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-envelope-open-text fa-4x text-primary mb-3"></i>
                        <h5 class="text-dark">Verification Required</h5>
                    </div>

                    <p class="text-muted mb-4">
                        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?
                    </p>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success mb-4">
                            <i class="fas fa-check-circle me-2"></i>
                            A new verification link has been sent to your email address.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Resend Verification Email
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('cms.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-sign-out-alt me-2"></i>Log Out
                        </button>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Check your spam folder if you didn't receive the email
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
.alert-success {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.2) 100%);
    border: 1px solid rgba(40, 167, 69, 0.3);
    color: #155724;
    border-radius: 10px;
}
.fa-envelope-open-text {
    background: linear-gradient(135deg, #800020 0%, #C44569 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.text-muted {
    color: #6c757d !important;
}
</style>
@endpush
