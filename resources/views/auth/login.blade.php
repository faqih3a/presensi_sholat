@extends('layouts.guest')

@section('title', 'Login')

@push('styles')
<style>
    .login-container {
        min-height: 100vh;
        background-color: #fff;
    }
    .login-card {
        max-width: 450px;
        width: 100%;
        margin: 0 auto;
    }
    .text-success-gradient {
        background: linear-gradient(310deg, #198754 0%, #2dc57b 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .form-control {
        border: 1px solid #edf2f9;
        border-radius: 0.75rem;
        padding: 0.75rem 1rem;
        transition: all 0.2s;
    }
    .form-control:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.1);
    }
    .btn-gradient-success {
        background: linear-gradient(310deg, #198754 0%, #2dc57b 100%);
        border: none;
        border-radius: 0.75rem;
        color: #fff;
        font-weight: 600;
        transition: all 0.2s;
        box-shadow: 0 4px 7px -1px rgba(0,0,0,0.11), 0 2px 4px -1px rgba(0,0,0,0.07);
    }
    .btn-gradient-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(0,0,0,0.1), 0 3px 6px rgba(0,0,0,0.08);
        color: #fff;
    }
    .btn-outline-soft {
        border: 1px solid #edf2f9;
        border-radius: 0.75rem;
        color: #67748e;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-outline-soft:hover {
        background-color: #f8f9fa;
        color: #198754;
        border-color: #198754;
    }
    .right-panel {
        background: linear-gradient(rgba(25, 135, 84, 0.7), rgba(25, 135, 84, 0.8)), url('{{ asset('images/bg-thursina.png') }}');
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #fff;
        padding: 3rem;
    }
    .glass-effect {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 1.5rem;
        padding: 3rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid p-0 overflow-hidden">
    <div class="row g-0 login-container">
        <!-- Left Panel: Form -->
        <div class="col-lg-5 d-flex flex-column justify-content-center px-4 py-5 bg-white">
            <div class="login-card">
                <div class="text-center mb-5">
                    <div class="bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-shield-check fs-1 text-success"></i>
                    </div>
                    <h2 class="fw-800 text-dark mb-1" style="letter-spacing: -1px;">PRESENSI THURSINA</h2>
                    <p class="text-muted">Masuk untuk mengelola kehadiran santri.</p>
                </div>

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
                        <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 ps-3 pe-2 text-muted" style="border-radius: 0.75rem 0 0 0.75rem;">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control border-0 bg-light py-2" style="border-radius: 0 0.75rem 0.75rem 0;" name="email" placeholder="nama@thursina.id" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 ps-3 pe-2 text-muted" style="border-radius: 0.75rem 0 0 0.75rem;">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control border-0 bg-light py-2" style="border-radius: 0;" id="password" name="password" placeholder="••••••••" required>
                            <span class="input-group-text bg-light border-0 pe-3 ps-2 text-muted" id="togglePassword" style="cursor: pointer; border-radius: 0 0.75rem 0.75rem 0;">
                                <i class="bi bi-eye-slash"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe" style="cursor: pointer;">
                            <label class="form-check-label text-muted small" for="rememberMe" style="cursor: pointer;">Ingat Saya</label>
                        </div>
                        <a href="#" class="text-success small fw-bold text-decoration-none">Lupa Password?</a>
                    </div>
                    
                    <div class="d-grid gap-3">
                        <button type="submit" class="btn btn-gradient-success py-2 fs-6">Masuk Ke Sistem</button>
                        <div class="text-center position-relative my-2">
                            <hr class="text-muted opacity-25">
                            <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">ATAU</span>
                        </div>
                        <a href="{{ route('santri.create') }}" class="btn btn-outline-soft py-2 fs-6">Daftar Akun Santri</a>
                    </div>
                </form>

                <div class="mt-5 text-center">
                    <p class="text-muted small mb-0">© 2026 Thursina IIBS. All rights reserved.</p>
                </div>
            </div>
        </div>
        
        <!-- Right Panel: Visual -->
        <div class="col-lg-7 d-none d-lg-flex right-panel">
            <div class="glass-effect">
                <h1 class="display-4 fw-800 mb-3" style="letter-spacing: -2px;">Digital Attendance System</h1>
                <p class="lead mb-4 opacity-75">Sistem presensi berbasis AI Face Recognition untuk kemudahan pemantauan ibadah santri di lingkungan Thursina IIBS.</p>
                <div class="d-flex justify-content-center gap-4 mt-2">
                    <div class="text-center">
                        <h4 class="fw-bold mb-0">100%</h4>
                        <p class="small opacity-50 mb-0">Accurate</p>
                    </div>
                    <div class="border-start opacity-25"></div>
                    <div class="text-center">
                        <h4 class="fw-bold mb-0">AI</h4>
                        <p class="small opacity-50 mb-0">Powered</p>
                    </div>
                    <div class="border-start opacity-25"></div>
                    <div class="text-center">
                        <h4 class="fw-bold mb-0">Live</h4>
                        <p class="small opacity-50 mb-0">Reports</p>
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
