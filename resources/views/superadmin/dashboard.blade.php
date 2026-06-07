@extends('layouts.app')

@section('title', 'Dashboard Super Admin')

@push('styles')
<style>
    .card-stats {
        border: 1px solid #edf2f9;
        border-radius: 1.25rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
        background: #fff;
    }
    .card-stats:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.05);
    }
    .icon-container {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1rem;
        color: #fff;
    }
    .action-card {
        border: 1px solid #edf2f9;
        border-radius: 1.25rem;
        background: #fff;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.06);
        border-color: rgba(25, 135, 84, 0.3);
    body.dark-mode .card-stats, body.dark-mode .action-card {
        background-color: #1e1e1e;
        border-color: #333;
    }
    body.dark-mode .text-dark {
        color: #fff !important;
    }
</style>
@endpush

@section('content')
<!-- Header Section -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 mb-0 text-dark fw-bold">Dashboard Super Admin</h1>
        <p class="text-muted mb-0">Selamat datang kembali! Berikut ringkasan data sistem hari ini.</p>
    </div>
</div>

<!-- Stats Section -->
<div class="row g-4 mb-5">
    <!-- Total Asatidz Card -->
    <div class="col-md-6">
        <div class="card card-stats border-0 p-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-container" style="background: linear-gradient(310deg, #198754 0%, #105c36 100%);">
                    <i class="bi bi-person-workspace fs-2"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Total Asatidz</div>
                    <div class="h2 fw-bold text-dark mb-0 mt-1">{{ number_format($stats['total_asatidz']) }}</div>
                    <div class="small text-muted mt-1">Akun pengajar terdaftar</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Santri Card -->
    <div class="col-md-6">
        <div class="card card-stats border-0 p-4">
            <div class="d-flex align-items-center gap-4">
                <div class="icon-container" style="background: linear-gradient(310deg, #2dc57b 0%, #198754 100%);">
                    <i class="bi bi-people-fill fs-2"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Total Santri</div>
                    <div class="h2 fw-bold text-dark mb-0 mt-1">{{ number_format($stats['total_santri']) }}</div>
                    <div class="small text-muted mt-1">Santri aktif dalam sistem</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="mb-4">
    <h4 class="fw-bold text-dark mb-4">Aksi Cepat</h4>
    
    <div class="row g-4">
        <!-- Tambah Asatidz -->
        <div class="col-md-6 col-xl-3">
            <div class="card action-card border-0 p-4">
                <div class="d-flex flex-column h-100">
                    <div class="text-success mb-3">
                        <i class="bi bi-person-plus-fill fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Tambah Asatidz</h5>
                    <p class="text-muted small mb-4 flex-grow-1">Registrasikan akun pengajar (ustadz) baru ke dalam sistem.</p>
                    <a href="{{ route('asatidz.create') }}" class="btn btn-outline-theme-success rounded-3 w-100 mt-auto py-2">
                        <i class="bi bi-plus-circle me-2"></i>Mulai Tambah
                    </a>
                </div>
            </div>
        </div>

        <!-- Kelola Asatidz -->
        <div class="col-md-6 col-xl-3">
            <div class="card action-card border-0 p-4">
                <div class="d-flex flex-column h-100">
                    <div class="text-success mb-3">
                        <i class="bi bi-person-workspace fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Kelola Asatidz</h5>
                    <p class="text-muted small mb-4 flex-grow-1">Lihat daftar lengkap pengajar, edit profil, atau hapus akun.</p>
                    <a href="{{ route('asatidz.index') }}" class="btn btn-outline-theme-success rounded-3 w-100 mt-auto py-2">
                        <i class="bi bi-arrow-right-circle me-2"></i>Buka Pengaturan
                    </a>
                </div>
            </div>
        </div>

        <!-- Tambah Santri -->
        <div class="col-md-6 col-xl-3">
            <div class="card action-card border-0 p-4">
                <div class="d-flex flex-column h-100">
                    <div class="text-success mb-3">
                        <i class="bi bi-person-fill-add fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Tambah Santri</h5>
                    <p class="text-muted small mb-4 flex-grow-1">Registrasikan data santri baru beserta foto wajah untuk presensi.</p>
                    <a href="{{ route('santri.create') }}" class="btn btn-outline-theme-success rounded-3 w-100 mt-auto py-2">
                        <i class="bi bi-plus-circle me-2"></i>Mulai Tambah
                    </a>
                </div>
            </div>
        </div>

        <!-- Kelola Santri -->
        <div class="col-md-6 col-xl-3">
            <div class="card action-card border-0 p-4">
                <div class="d-flex flex-column h-100">
                    <div class="text-success mb-3">
                        <i class="bi bi-people-fill fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Kelola Santri</h5>
                    <p class="text-muted small mb-4 flex-grow-1">Lihat daftar santri, edit profil, dan kelola foto deteksi wajah.</p>
                    <a href="{{ route('santri.index') }}" class="btn btn-outline-theme-success rounded-3 w-100 mt-auto py-2">
                        <i class="bi bi-arrow-right-circle me-2"></i>Buka Pengaturan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
