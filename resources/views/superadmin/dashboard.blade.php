@extends('layouts.app')

@section('title', 'Dashboard Super Admin')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <h4 class="fw-800 text-dark mb-1">Dashboard Super Admin</h4>
        <p class="text-muted small">Selamat datang kembali! Berikut ringkasan data sistem hari ini.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex align-items-center gap-4">
                <div class="bg-primary-subtle p-3 rounded-4 text-primary">
                    <i class="bi bi-person-workspace fs-1"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold">TOTAL ASATIDZ</div>
                    <div class="fs-2 fw-800">{{ $stats['total_asatidz'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <div class="d-flex align-items-center gap-4">
                <div class="bg-success-subtle p-3 rounded-4 text-success">
                    <i class="bi bi-people-fill fs-1"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold">TOTAL SANTRI</div>
                    <div class="fs-2 fw-800">{{ $stats['total_santri'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-4">Aksi Cepat</h5>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('asatidz.create') }}" class="btn btn-primary rounded-3 px-4 py-2">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Asatidz
                </a>
                <a href="{{ route('santri.create') }}" class="btn btn-success rounded-3 px-4 py-2">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Santri
                </a>
                <a href="{{ route('asatidz.index') }}" class="btn btn-outline-primary rounded-3 px-4 py-2">
                    <i class="bi bi-list-task me-2"></i>Lihat Semua Asatidz
                </a>
                <a href="{{ route('santri.index') }}" class="btn btn-outline-success rounded-3 px-4 py-2">
                    <i class="bi bi-list-task me-2"></i>Lihat Semua Santri
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
