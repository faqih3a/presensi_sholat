@extends('layouts.guest')

@section('title', 'Login')

@push('styles')
<style>
    .login-container {
        min-height: 100vh;
        background-color: #f8f9fa;
    }
    .login-card {
        max-width: 440px;
        width: 100%;
        background: #ffffff;
        border-radius: 1.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        padding: 2.5rem 2.25rem;
        border: 1px solid #edf2f9;
    }
    
    /* Custom Input Group styling to match mockup exactly */
    .custom-input-group {
        background-color: #f8f9fa;
        border: 1px solid #edf2f9;
        border-radius: 0.75rem;
        overflow: hidden;
        transition: all 0.2s ease-in-out;
    }
    .custom-input-group:focus-within {
        border-color: #10b981;
        background-color: #ffffff;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }
    .custom-input-group .input-group-text {
        background-color: transparent;
        border: none;
        color: #9ca3af;
        padding-left: 1.25rem;
        padding-right: 0.5rem;
        font-size: 1.1rem;
    }
    .custom-input-group .form-control {
        background-color: transparent;
        border: none;
        padding: 0.75rem 1rem 0.75rem 0.5rem;
        font-size: 0.9rem;
        color: #1f2937;
    }
    .custom-input-group .form-control:focus {
        box-shadow: none;
        background-color: transparent;
    }
    .custom-input-group .form-control::placeholder {
        color: #9ca3af;
        opacity: 1;
    }
    
    /* Buttons styling */
    .btn-primary-custom {
        background-color: #10b981;
        border: none;
        color: #ffffff;
        font-weight: 600;
        border-radius: 0.75rem;
        padding: 0.75rem 1.25rem;
        transition: all 0.2s;
        font-size: 0.95rem;
    }
    .btn-primary-custom:hover {
        background-color: #059669;
        color: #ffffff;
    }
    
    .btn-outline-custom {
        background-color: transparent;
        border: 1px solid #10b981;
        color: #10b981;
        font-weight: 600;
        border-radius: 0.75rem;
        padding: 0.75rem 1.25rem;
        transition: all 0.2s;
        font-size: 0.95rem;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }
    .btn-outline-custom:hover {
        background-color: rgba(16, 185, 129, 0.05);
        border-color: #10b981;
        color: #059669;
    }
    
    /* Right visual panel */
    .right-panel {
        position: relative;
        background-image: url('{{ asset('images/bg-thursina.png') }}');
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        padding: 3rem;
    }
    .right-panel::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.75) 0%, rgba(4, 120, 87, 0.85) 100%);
        z-index: 1;
    }
    .glass-effect {
        position: relative;
        z-index: 2;
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 1.5rem;
        padding: 3.5rem 2.5rem;
        width: 100%;
        max-width: 560px;
        text-align: center;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.08);
    }
    
    /* Custom Checkbox */
    .custom-checkbox {
        cursor: pointer;
    }
    .custom-checkbox:checked {
        background-color: #10b981;
        border-color: #10b981;
    }
</style>
@endpush

@section('content')
<div class="container-fluid p-0 overflow-hidden">
    <div class="row g-0 login-container">
        <!-- Left Panel: Form -->
        <div class="col-lg-6 d-flex flex-column justify-content-center align-items-center px-4 py-5" style="background-color: #f8f9fa;">
            <div class="login-card">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 56px; height: 56px; background-color: #e6f7ef;">
                        <i class="bi bi-shield-check fs-2" style="color: #10b981;"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">PRESENSI THURSINA</h3>
                    <p class="text-muted small mb-0">Masuk untuk mengelola kehadiran santri.</p>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger border-0 shadow-sm mb-4 small d-flex align-items-center" style="border-radius: 0.75rem;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success border-0 shadow-sm mb-4 small d-flex align-items-center" style="border-radius: 0.75rem;">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm mb-4 small d-flex align-items-center" style="border-radius: 0.75rem;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div class="mb-0">
                            @foreach ($errors->all() as $error)
                                <span>{{ $error }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 700; color: #6b7280; letter-spacing: 0.5px;">EMAIL ADDRESS</label>
                        <div class="input-group custom-input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" name="email" placeholder="Masukkan Email Anda" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 700; color: #6b7280; letter-spacing: 0.5px;">PASSWORD</label>
                        <div class="input-group custom-input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password Anda" required>
                            <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input custom-checkbox" type="checkbox" name="remember" id="rememberMe">
                            <label class="form-check-label text-muted" for="rememberMe" style="font-size: 0.85rem; cursor: pointer;">Ingat Saya</label>
                        </div>
                        <a href="#" class="small fw-semibold text-decoration-none" style="color: #10b981;">Lupa Password?</a>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-primary-custom py-2">Masuk Ke Sistem</button>
                        <div class="text-center position-relative my-2">
                            <hr class="text-muted opacity-25">
                            <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted" style="font-size: 0.75rem; font-weight: 600;">ATAU</span>
                        </div>
                        <a href="{{ route('santri.create') }}" class="btn btn-outline-custom py-2">Daftar Akun Santri</a>
                    </div>
                </form>
            </div>
            
            <p class="text-muted text-center mt-4 mb-0" style="font-size: 0.8rem;">&copy; 2026 Thursina IIBS. All rights reserved.</p>
        </div>
        
        <!-- Right Panel: Visual -->
        <div class="col-lg-6 d-none d-lg-flex right-panel">
            <div class="glass-effect">
                <h1 class="display-5 fw-bold mb-3" style="letter-spacing: -1.5px;">Digital Attendance System</h1>
                <p class="lead mb-5 opacity-90" style="font-size: 1rem; line-height: 1.6;">Sistem presensi berbasis AI Face Recognition untuk kemudahan pemantauan ibadah santri di lingkungan Thursina IIBS.</p>
                <div class="d-flex justify-content-center gap-4 mt-2">
                    <div class="text-center px-3">
                        <h3 class="fw-bold mb-0">100%</h3>
                        <p class="small opacity-75 mb-0" style="font-size: 0.8rem;">Accurate</p>
                    </div>
                    <div class="border-start opacity-25"></div>
                    <div class="text-center px-3">
                        <h3 class="fw-bold mb-0">AI</h3>
                        <p class="small opacity-75 mb-0" style="font-size: 0.8rem;">Powered</p>
                    </div>
                    <div class="border-start opacity-25"></div>
                    <div class="text-center px-3">
                        <h3 class="fw-bold mb-0">Live</h3>
                        <p class="small opacity-75 mb-0" style="font-size: 0.8rem;">Reports</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        if (togglePassword && password) {
            togglePassword.addEventListener('click', function (e) {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                const icon = this.querySelector('i');
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        }
    });
</script>
@endpush
@endsection
