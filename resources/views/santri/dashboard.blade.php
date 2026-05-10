@extends('layouts.app')

@section('title', 'Dashboard Santri')

@push('styles')
<style>
    .card-stats {
        border: 1px solid #edf2f9;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        overflow: visible !important;
    }
    .card-stats:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        z-index: 10;
    }
    .btn-gradient-success {
        background: linear-gradient(310deg, #198754 0%, #2dc57b 100%);
        border: none;
        color: #fff;
        box-shadow: 0 4px 7px -1px rgba(0,0,0,0.11), 0 2px 4px -1px rgba(0,0,0,0.07);
        transition: all 0.15s ease-in;
    }
    .btn-gradient-success:hover {
        transform: scale(1.02);
        color: #fff;
    }
    .badge-soft {
        font-weight: 700;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.75rem;
    }
    .badge-soft-success {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.2);
    }
    .badge-soft-danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .badge-soft-info {
        background-color: rgba(58, 176, 255, 0.1);
        color: #3ab0ff;
        border: 1px solid rgba(58, 176, 255, 0.2);
    }
    .btn-white {
        background-color: #fff;
        color: #67748e;
        border-color: #edf2f9;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.2s;
    }
    .btn-white:hover {
        background-color: #f8f9fa;
        border-color: #d1d9e6;
        color: #333;
    }
    .table th {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #67748e;
        padding: 1rem;
    }
    .table td {
        padding: 1rem;
        color: #67748e;
        font-size: 0.875rem;
    }
    body.dark-mode .card-stats, body.dark-mode .card {
        background-color: #1e1e1e;
        border-color: #333;
    }
    body.dark-mode .btn-white {
        background-color: #2c2c2c;
        border-color: #444;
        color: #adb5bd;
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h1 class="h3 mb-0 text-dark fw-bold">Dashboard Presensi Saya</h1>
        <p class="text-muted mb-0">Selamat datang kembali, mari pantau kehadiran ibadah Anda.</p>
    </div>
    <form action="{{ route('santri.dashboard') }}" method="GET" class="no-loader">
        <input type="hidden" name="waktu_sholat" value="{{ $waktuSholat }}">
        <div class="bg-white p-1 rounded-3 shadow-sm d-flex border">
            <button type="submit" name="period" value="today" class="btn btn-sm px-3 {{ $period == 'today' ? 'btn-success shadow-sm' : 'btn-link text-muted text-decoration-none' }} rounded-2 transition-all">Hari Ini</button>
            <button type="submit" name="period" value="week" class="btn btn-sm px-3 {{ $period == 'week' ? 'btn-success shadow-sm' : 'btn-link text-muted text-decoration-none' }} rounded-2 transition-all">Minggu Ini</button>
            <button type="submit" name="period" value="month" class="btn btn-sm px-3 {{ $period == 'month' ? 'btn-success shadow-sm' : 'btn-link text-muted text-decoration-none' }} rounded-2 transition-all">Bulan Ini</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <!-- Profile Card -->
    <div class="col-xl-4 col-md-12">
        <div class="card card-stats h-100 p-3">
            <div class="card-body p-0 d-flex align-items-center gap-3">
                @if($user->santri && $user->santri->foto_referensi)
                    <img src="{{ asset('storage/santri_fotos/' . $user->santri->foto_referensi) }}" alt="Foto Profil" class="rounded-circle shadow-sm" style="width: 60px; height: 60px; object-fit: cover; border: 3px solid #f8f9fa;">
                @else
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; border: 3px solid #f8f9fa;">
                        <i class="bi bi-person fs-3"></i>
                    </div>
                @endif
                <div>
                    <h5 class="mb-0 fw-bold text-dark">{{ $user->name }}</h5>
                    <p class="mb-0 text-muted small">
                        <span class="badge badge-soft badge-soft-success">Kelas {{ $user->santri ? $user->santri->kelas : '-' }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Hadir -->
    <div class="col-xl-4 col-md-6">
        <div class="card card-stats h-100 p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Kehadiran {{ $period == 'today' ? 'Hari Ini' : ($period == 'week' ? 'Minggu Ini' : 'Bulan Ini') }}</div>
                <div class="task-checkbox"><i class="bi bi-calendar-check text-success"></i></div>
            </div>
            <div class="h3 mb-1 fw-bold text-dark">{{ $totalHadir }}x Hadir</div>
            <div class="small text-muted">Presensi berhasil dicatat</div>
        </div>
    </div>

    <!-- Stats Alfa -->
    <div class="col-xl-4 col-md-6">
        <div class="card card-stats h-100 p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem;">Alpha {{ $period == 'today' ? 'Hari Ini' : ($period == 'week' ? 'Minggu Ini' : 'Bulan Ini') }}</div>
                <div class="task-checkbox" style="background-color: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="bi bi-calendar-x"></i></div>
            </div>
            <div class="h3 mb-1 fw-bold text-dark">{{ $totalAlfa }}x Alpha</div>
            <div class="small text-muted">Belum melakukan presensi</div>
        </div>
    </div>
</div>

<div class="card card-stats mb-4">
    <div class="card-header py-3 bg-white border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-clock-history text-success me-2"></i>Riwayat Presensi Anda</h6>
        <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center">
            <form id="sholatFilterForm" action="{{ route('santri.dashboard') }}" method="GET" class="d-flex align-items-center gap-2 m-0 no-loader">
                <input type="hidden" name="period" value="{{ $period }}">
                <input type="hidden" name="waktu_sholat" id="hidden_waktu_sholat" value="{{ $waktuSholat }}">
                
                <label class="small fw-bold text-muted text-uppercase" style="font-size: 0.65rem;">Filter Waktu</label>
                <div class="dropdown">
                    <button class="btn btn-sm btn-white border dropdown-toggle fw-semibold px-3 py-2 d-flex align-items-center gap-2" type="button" id="sholatDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 0.75rem; min-width: 140px;">
                        <span>{{ request('waktu_sholat') ?: 'Semua Waktu' }}</span>
                        <i class="bi bi-chevron-down small ms-auto text-muted"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="sholatDropdown" style="border-radius: 1rem; padding: 0.5rem; margin-top: 10px;">
                        <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == '' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', '')">Semua Waktu</a></li>
                        <li><hr class="dropdown-divider mx-2"></li>
                        <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == 'Subuh' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', 'Subuh')">Subuh</a></li>
                        <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == 'Dzuhur' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', 'Dzuhur')">Dzuhur</a></li>
                        <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == 'Ashar' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', 'Ashar')">Ashar</a></li>
                        <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == 'Maghrib' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', 'Maghrib')">Maghrib</a></li>
                        <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == 'Isya' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', 'Isya')">Isya</a></li>
                    </ul>
                </div>
            </form>
            <a href="{{ route('santri.dashboard.export', request()->query()) }}" class="btn btn-sm btn-gradient-success px-3 fw-bold" data-no-loader="true">
                <i class="bi bi-file-earmark-excel me-1"></i> Download Laporan
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Waktu Sholat</th>
                        <th>Waktu Hadir</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presensis as $presensi)
                    <tr>
                        <td class="fw-bold text-dark">{{ \Carbon\Carbon::parse($presensi->tanggal)->format('d M Y') }}</td>
                        <td>
                            <span class="badge badge-soft badge-soft-info">
                                @if(in_array($presensi->waktu_sholat, ['Dzuhur', 'Ashar']))
                                    <i class="bi bi-sun-fill me-1 small"></i>
                                @else
                                    <i class="bi bi-moon-stars-fill me-1 small"></i>
                                @endif
                                {{ $presensi->waktu_sholat }}
                            </span>
                        </td>
                        <td>
                            @if($presensi->waktu_hadir)
                                <div class="fw-bold text-dark">
                                    <i class="bi bi-clock me-1 text-muted small"></i> {{ \Carbon\Carbon::parse($presensi->waktu_hadir)->format('H:i') }}
                                </div>
                            @else
                                <span class="text-muted opacity-50">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($presensi->status == 'Hadir')
                                <span class="badge badge-soft badge-soft-success px-4">Hadir</span>
                            @else
                                <span class="badge badge-soft badge-soft-danger px-4">Alpha</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <div class="py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-25"></i>
                                <h6 class="fw-bold">Belum Ada Riwayat</h6>
                                <p class="small mb-0">Silakan lakukan presensi tepat waktu di masjid.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateFilter(name, value) {
        document.getElementById('hidden_' + name).value = value;
        document.getElementById('sholatFilterForm').submit();
    }
</script>
@endpush
@endsection
