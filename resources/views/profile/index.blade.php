@extends('layouts.app')

@section('title', 'Profil Saya')

@push('styles')
<style>
    .profile-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    .profile-card {
        border-radius: 1.5rem;
        border: none;
        box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        background: #fff;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    body.dark-mode .profile-card {
        background: #1e1e1e;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .profile-header-bg {
        background: linear-gradient(135deg, #198754 0%, #2dc57b 100%);
        height: 180px;
        position: relative;
    }
    .profile-avatar-wrapper {
        position: relative;
        margin-top: -90px;
        margin-left: 40px;
        display: inline-block;
    }
    .profile-avatar-img {
        width: 150px;
        height: 150px;
        border-radius: 2.5rem;
        border: 6px solid #fff;
        object-fit: cover;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        background: #f8f9fa;
    }
    body.dark-mode .profile-avatar-img {
        border-color: #1e1e1e;
        background: #2c2c2c;
    }
    .avatar-edit-btn {
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #fff;
        color: #198754;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: all 0.2s;
        border: none;
    }
    .avatar-edit-btn:hover {
        transform: scale(1.1);
        background: #198754;
        color: #fff;
    }
    .profile-nav {
        background: #f8f9fa;
        padding: 0.5rem;
        border-radius: 1rem;
        display: inline-flex;
        gap: 0.5rem;
    }
    body.dark-mode .profile-nav {
        background: #2c2c2c;
    }
    .profile-nav .nav-link {
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        font-weight: 600;
        color: #67748e;
        transition: all 0.2s;
    }
    .profile-nav .nav-link.active {
        background: #fff;
        color: #198754;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    body.dark-mode .profile-nav .nav-link.active {
        background: #1e1e1e;
        color: #2dc57b;
    }
    .form-label-custom {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        color: #8898aa;
        margin-bottom: 0.5rem;
    }
    .form-control-custom {
        border-radius: 0.8rem;
        padding: 0.75rem 1rem;
        border: 1px solid #e9ecef;
        background-color: #fff;
        color: #495057;
        transition: all 0.2s;
    }
    .input-group > .form-control-custom {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
    body.dark-mode .toggle-password {
        background-color: #1e1e1e;
        border-color: #444 !important;
        color: #adb5bd;
    }
    body.dark-mode .toggle-password:hover {
        background-color: #2c2c2c;
        color: #fff;
    }
    body.dark-mode .form-control-custom {
        background-color: #2c2c2c;
        border-color: #444;
        color: #e9ecef;
    }
    .form-control-custom:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.1);
    }
    .btn-save {
        background: linear-gradient(310deg, #198754 0%, #2dc57b 100%);
        border: none;
        border-radius: 0.8rem;
        padding: 0.75rem 2rem;
        font-weight: 700;
        color: #fff;
        box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
        transition: all 0.2s;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 20px rgba(25, 135, 84, 0.4);
        color: #fff;
    }
    .animate-up {
        animation: fadeInUp 0.5s ease-out forwards;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="profile-container animate-up">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center" style="border-radius: 1rem;">
            <i class="bi bi-check-circle-fill me-3 fs-4"></i>
            <div>
                <div class="fw-bold">Berhasil!</div>
                <div class="small">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <div class="card profile-card">
        <div class="profile-header-bg"></div>
        <div class="card-body pt-0">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-end gap-4 mb-5">
                <div class="profile-avatar-wrapper">
                    @php
                        $avatarUrl = null;
                        if($user->role === 'santri' && $user->santri && $user->santri->foto_referensi) {
                            $avatarUrl = asset('storage/santri_fotos/' . $user->santri->foto_referensi);
                        } elseif ($user->avatar) {
                            $avatarUrl = asset('storage/avatars/' . $user->avatar);
                        }
                    @endphp
                    
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="Avatar" class="profile-avatar-img" id="avatarPreview">
                    @else
                        <div class="profile-avatar-img d-flex align-items-center justify-content-center bg-success text-white fs-1 fw-bold">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                    
                    <label for="avatarInput" class="avatar-edit-btn" title="Ganti Foto">
                        <i class="bi bi-camera-fill"></i>
                    </label>
                </div>
                <div class="mb-2">
                    <h3 class="fw-800 text-dark mb-0">{{ $user->name }}</h3>
                    <p class="text-muted fw-600 mb-0">
                        <span class="badge {{ $user->role === 'asatidz' ? 'bg-primary' : 'bg-success' }} rounded-pill px-3 py-2 mt-2">
                            {{ strtoupper($user->role) }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="px-md-4">
                <ul class="nav profile-nav mb-5" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-pane" type="button" role="tab">
                            <i class="bi bi-person-fill me-2"></i>Informasi Profil
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security-pane" type="button" role="tab">
                            <i class="bi bi-shield-lock-fill me-2"></i>Keamanan Akun
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="profileTabContent">
                    <!-- Tab Info Profil -->
                    <div class="tab-pane fade show active" id="info-pane" role="tabpanel" aria-labelledby="info-tab">
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*">
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-custom">Nama Lengkap</label>
                                    <input type="text" name="name" class="form-control form-control-custom @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Alamat Email</label>
                                    <input type="email" name="email" class="form-control form-control-custom @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label-custom">Nomor WhatsApp</label>
                                    <input type="text" name="wa_number" class="form-control form-control-custom @error('wa_number') is-invalid @enderror" value="{{ old('wa_number', $user->wa_number) }}" placeholder="628123456789">
                                    @error('wa_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text small opacity-75">Gunakan format 628xxx (tanpa +). Digunakan untuk notifikasi sistem.</div>
                                </div>
                                
                                @if($user->role === 'santri' && $user->santri)
                                    <div class="col-md-6">
                                        <label class="form-label-custom">Kelas</label>
                                        <input type="text" name="kelas" class="form-control form-control-custom @error('kelas') is-invalid @enderror" value="{{ old('kelas', $user->santri->kelas) }}" required>
                                        @error('kelas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <div class="form-text small opacity-75">Silakan isi kelas Anda saat ini.</div>
                                    </div>
                                @endif

                                <div class="col-12 mt-5">
                                    <button type="submit" class="btn btn-save px-5">
                                        <i class="bi bi-check2-circle me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Keamanan -->
                    <div class="tab-pane fade" id="security-pane" role="tabpanel" aria-labelledby="security-tab">
                        <form action="{{ route('profile.password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label-custom">Password Saat Ini</label>
                                    <div class="input-group">
                                        <input type="password" name="current_password" class="form-control form-control-custom @error('current_password') is-invalid @enderror" required placeholder="••••••••" id="current_password">
                                        <button class="btn btn-outline-secondary border-start-0 border-end border-top border-bottom rounded-end-pill px-3 toggle-password" type="button" data-target="current_password" style="border-color: #e9ecef !important; border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @error('current_password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Password Baru</label>
                                    <div class="input-group">
                                        <input type="password" name="password" class="form-control form-control-custom @error('password') is-invalid @enderror" required placeholder="Minimal 5 karakter" id="password">
                                        <button class="btn btn-outline-secondary border-start-0 border-end border-top border-bottom rounded-end-pill px-3 toggle-password" type="button" data-target="password" style="border-color: #e9ecef !important; border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Konfirmasi Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" class="form-control form-control-custom" required placeholder="Ulangi password baru" id="password_confirmation">
                                        <button class="btn btn-outline-secondary border-start-0 border-end border-top border-bottom rounded-end-pill px-3 toggle-password" type="button" data-target="password_confirmation" style="border-color: #e9ecef !important; border-top-left-radius: 0 !important; border-bottom-left-radius: 0 !important;">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12 mt-5">
                                    <button type="submit" class="btn btn-save px-5">
                                        <i class="bi bi-shield-check me-2"></i>Perbarui Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Avatar Preview
        const avatarInput = document.getElementById('avatarInput');
        const avatarPreview = document.getElementById('avatarPreview');
        
        if (avatarInput && avatarPreview) {
            avatarInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        // Handle Tab Activation via Query Param
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab === 'security') {
            const securityTab = document.getElementById('security-tab');
            if (securityTab) {
                bootstrap.Tab.getOrCreateInstance(securityTab).show();
            }
        }

        // Toggle Password Visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });
        });
    });
</script>
@endpush
@endsection
