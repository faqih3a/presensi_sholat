@extends('layouts.app')

@section('title', 'Kehadiran Sholat')

@push('styles')
<style>
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
    .card-stats {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
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
    .filter-select {
        background-color: #f8f9fa;
        border: 1px solid #edf2f9;
        border-radius: 0.5rem;
        padding: 0.4rem 2rem 0.4rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #4d5157;
    }
    .filter-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
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
    
    body.dark-mode .table td, body.dark-mode .table th {
        border-bottom-color: #333;
    }
    body.dark-mode .btn-white {
        background-color: #2c2c2c;
        border-color: #444;
        color: #adb5bd;
    }
    body.dark-mode .btn-white:hover {
        background-color: #333;
        color: #fff;
    }
    body.dark-mode .filter-select {
        background-color: #2c2c2c;
        border-color: #444;
        color: #adb5bd;
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 mb-0 text-dark fw-bold">Rekap Kehadiran Sholat</h1>
        <p class="text-muted mb-0">Pantau detail kehadiran sholat berjamaah santri secara keseluruhan.</p>
    </div>
    <form action="{{ route('dashboard.kehadiran') }}" method="GET" class="no-loader">
        <input type="hidden" name="waktu_sholat" value="{{ request('waktu_sholat') }}">
        <div class="bg-white p-1 rounded-3 shadow-sm d-flex border">
            <button type="submit" name="period" value="today" class="btn btn-sm px-3 {{ $period == 'today' ? 'btn-success shadow-sm' : 'btn-link text-muted text-decoration-none' }} rounded-2 transition-all">Hari Ini</button>
            <button type="submit" name="period" value="week" class="btn btn-sm px-3 {{ $period == 'week' ? 'btn-success shadow-sm' : 'btn-link text-muted text-decoration-none' }} rounded-2 transition-all">Minggu Ini</button>
            <button type="submit" name="period" value="month" class="btn btn-sm px-3 {{ $period == 'month' ? 'btn-success shadow-sm' : 'btn-link text-muted text-decoration-none' }} rounded-2 transition-all">Bulan Ini</button>
        </div>
    </form>
</div>

<div class="card card-stats mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-table text-success me-2"></i>Data Rekap Kehadiran</h6>
        <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center">
            <form id="filterForm" action="{{ route('dashboard.kehadiran') }}" method="GET" class="d-flex flex-wrap align-items-center gap-3 m-0 no-loader">
                <input type="hidden" name="period" value="{{ $period }}">
                <input type="hidden" name="waktu_sholat" id="hidden_waktu_sholat" value="{{ request('waktu_sholat') }}">
                <input type="hidden" name="status" id="hidden_status" value="{{ request('status') }}">
                
                <!-- Custom Dropdown Sholat -->
                <div class="d-flex align-items-center gap-2">
                    <label class="small fw-bold text-muted text-uppercase" style="font-size: 0.65rem;">Sholat</label>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-white border dropdown-toggle fw-semibold px-3 py-2 d-flex align-items-center gap-2" type="button" id="sholatDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 0.75rem; min-width: 130px; background: #fff;">
                            <span>{{ request('waktu_sholat') ?: 'Semua Waktu' }}</span>
                            <i class="bi bi-chevron-down small ms-auto text-muted"></i>
                        </button>
                        <ul class="dropdown-menu shadow-lg border-0" aria-labelledby="sholatDropdown" style="border-radius: 1rem; padding: 0.5rem; margin-top: 10px;">
                            <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == '' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', '')">Semua Waktu</a></li>
                            <li><hr class="dropdown-divider mx-2"></li>
                            <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == 'Subuh' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', 'Subuh')">Subuh</a></li>
                            <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == 'Dzuhur' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', 'Dzuhur')">Dzuhur</a></li>
                            <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == 'Ashar' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', 'Ashar')">Ashar</a></li>
                            <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == 'Maghrib' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', 'Maghrib')">Maghrib</a></li>
                            <li><a class="dropdown-item py-2 {{ request('waktu_sholat') == 'Isya' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('waktu_sholat', 'Isya')">Isya</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Custom Dropdown Status -->
                <div class="d-flex align-items-center gap-2">
                    <label class="small fw-bold text-muted text-uppercase" style="font-size: 0.65rem;">Status</label>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-white border dropdown-toggle fw-semibold px-3 py-2 d-flex align-items-center gap-2" type="button" id="statusDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 0.75rem; min-width: 120px; background: #fff;">
                            <span>{{ request('status') ?: 'Semua Status' }}</span>
                            <i class="bi bi-chevron-down small ms-auto text-muted"></i>
                        </button>
                        <ul class="dropdown-menu shadow-lg border-0" aria-labelledby="statusDropdown" style="border-radius: 1rem; padding: 0.5rem; margin-top: 10px;">
                            <li><a class="dropdown-item py-2 {{ request('status') == '' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('status', '')">Semua Status</a></li>
                            <li><hr class="dropdown-divider mx-2"></li>
                            <li><a class="dropdown-item py-2 {{ request('status') == 'Hadir' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('status', 'Hadir')">Hadir</a></li>
                            <li><a class="dropdown-item py-2 {{ request('status') == 'Alfa' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('status', 'Alfa')">Alpha</a></li>
                        </ul>
                    </div>
                </div>
            </form>
            <a href="{{ route('dashboard.kehadiran.export', request()->query()) }}" class="btn btn-gradient-success btn-sm px-3 fw-bold" data-no-loader="true">
                <i class="bi bi-file-earmark-excel me-1"></i> Download Excel
            </a>
        </div>
    </div>
    
    @push('scripts')
    <script>
        function updateFilter(name, value) {
            document.getElementById('hidden_' + name).value = value;
            document.getElementById('filterForm').submit();
        }
    </script>
    @endpush
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="bg-light">
                    <tr>
                        <th>Nama Santri</th>
                        <th>Kelas</th>
                        <th>Waktu Sholat</th>
                        <th>Waktu Presensi</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presensis as $presensi)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $presensi->santri->nama }}</div>
                        </td>
                        <td>{{ $presensi->santri->kelas }}</td>
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
                            <div class="d-flex align-items-center">
                                @if($presensi->waktu_hadir)
                                    <div class="fw-bold text-dark me-2">{{ \Carbon\Carbon::parse($presensi->waktu_hadir)->format('H:i') }}</div>
                                @else
                                    <div class="fw-bold text-danger me-2">-</div>
                                @endif
                                <div class="small text-muted border-start ps-2">{{ \Carbon\Carbon::parse($presensi->tanggal)->format('d M Y') }}</div>
                            </div>
                        </td>
                        <td class="text-center">
                            @if($presensi->status == 'Alfa')
                                <span class="badge badge-soft badge-soft-danger px-4">Alpha</span>
                            @else
                                <span class="badge badge-soft badge-soft-success px-4">Hadir</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                <h6 class="fw-bold">Belum Ada Data Presensi</h6>
                                <p class="small mb-0">Data kehadiran akan muncul di sini setelah santri melakukan scan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(count($presensis) > 0)
    <div class="card-footer bg-white border-top py-3 text-center text-md-start">
        <div class="small text-muted">
            Menampilkan {{ count($presensis) }} data rekaman kehadiran terbaru.
        </div>
    </div>
    @endif
</div>
@endsection
