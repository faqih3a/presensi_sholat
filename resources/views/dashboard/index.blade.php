@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    .no-caret::after {
        display: none !important;
    }
    .btn-white {
        background-color: #fff;
        color: #67748e;
        border-color: #edf2f9;
    }
    .btn-white:hover {
        background-color: #f8f9fa;
        color: #198754;
    }
    .card-stats {
        border: 1px solid #edf2f9;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
        overflow: visible !important; /* Prevent clipping of dropdowns */
    }
    .card-stats:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        z-index: 10; /* Bring to front on hover */
    }
    .card-stats:focus-within {
        z-index: 20; /* Ensure active dropdowns are on top */
    }
    .activity-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #198754;
        display: inline-block;
        margin-right: 0.5rem;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
    }
    .task-checkbox {
        width: 2rem;
        height: 2rem;
        border: none;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.75rem;
        color: #198754;
        background-color: rgba(25, 135, 84, 0.1);
        transition: all 0.2s;
    }
    .task-checkbox i { font-size: 1rem; }
    
    body.dark-mode .card-stats {
        background-color: #1e1e1e;
        border-color: #333;
    }
    body.dark-mode .task-checkbox {
        background-color: rgba(25, 135, 84, 0.2);
    }
    
    .avatar-group img, .avatar-group .avatar-placeholder {
        width: 35px;
        height: 35px;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-left: -12px;
        transition: all 0.2s ease;
        object-fit: cover;
    }
    .avatar-group img:first-child, .avatar-group .avatar-placeholder:first-child {
        margin-left: 0;
    }
    .avatar-group img:hover {
        transform: translateY(-3px);
        z-index: 5;
        margin-right: 5px;
    }
    .dropdown-menu-list {
        min-width: 320px;
        max-height: 400px;
        overflow-y: auto;
        border-radius: 1rem;
        padding: 0.75rem;
    }
</style>
@endpush

@section('content')
@php
    $currentPeriod = request('period', 'day');
    $startDate = \Carbon\Carbon::parse($tanggal_mulai);
    $endDate = \Carbon\Carbon::parse($tanggal_akhir);
    
    $startDate->locale('id');
    $endDate->locale('id');
    
    // Always show Month and Year
    $dateLabel = $startDate->translatedFormat('F Y');
    
    $currentYear = $startDate->year;
    $currentMonth = $startDate->month;
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 mb-0 text-dark fw-bold">Dashboard</h1>
        <p class="text-muted mb-0">Selamat datang di sistem presensi sholat</p>
    </div>
    
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end">
        <!-- Period Selection Tab -->
        <div class="btn-group bg-white p-1 rounded-pill border shadow-sm" role="group">
            <button type="button" onclick="changePeriod('day')" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $currentPeriod == 'day' ? 'btn-success text-white' : 'btn-light text-success bg-transparent border-0' }}">Day</button>
            <button type="button" onclick="changePeriod('week')" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $currentPeriod == 'week' ? 'btn-success text-white' : 'btn-light text-success bg-transparent border-0' }}">Week</button>
            <button type="button" onclick="changePeriod('month')" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $currentPeriod == 'month' ? 'btn-success text-white' : 'btn-light text-success bg-transparent border-0' }}">Month</button>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Arrow Left -->
            <button type="button" onclick="navigate(-1)" class="btn btn-white border rounded-3 px-3 py-1.5 shadow-sm text-secondary">
                <i class="bi bi-chevron-left"></i>
            </button>

            <!-- Month Dropdown Toggle (Always Month/Year dropdown) -->
            <div class="dropdown d-inline-block">
                <button class="btn btn-white border rounded-3 px-4 py-1.5 shadow-sm fw-bold text-success dropdown-toggle no-caret" type="button" id="date-label-btn" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.9rem; min-width: 140px;">
                    {{ $dateLabel }}
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3 border-0 shadow-lg rounded-4 mt-2" aria-labelledby="date-label-btn" style="min-width: 280px; z-index: 1050;">
                    <!-- Year Navigation -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-link btn-sm text-dark p-0" id="btn-prev-year"><i class="bi bi-chevron-left"></i></button>
                        <span class="fw-bold text-dark" id="year-display">{{ $currentYear }}</span>
                        <button type="button" class="btn btn-link btn-sm text-dark p-0" id="btn-next-year"><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <!-- Months Grid -->
                    <div class="row g-2 text-center" id="months-grid">
                        @php
                            $months = [
                                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 
                                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt', 
                                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                            ];
                        @endphp
                        @foreach($months as $num => $name)
                            <div class="col-4">
                                <button type="button" class="btn btn-sm w-100 py-2 rounded-3 fw-semibold month-select-btn {{ $num == $currentMonth ? 'btn-success text-white' : 'btn-light text-dark bg-transparent border-0' }}" data-month="{{ $num }}">
                                    {{ $name }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Arrow Right -->
            <button type="button" onclick="navigate(1)" class="btn btn-white border rounded-3 px-3 py-1.5 shadow-sm text-secondary">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <!-- Hidden Form for GET submission -->
        <form id="filter-form" action="{{ route('dashboard') }}" method="GET" class="no-loader d-none">
            <input type="hidden" name="waktu_sholat" value="{{ $waktuSholat }}">
            <input type="hidden" name="period" id="filter-period" value="{{ $currentPeriod }}">
            <input type="hidden" name="tanggal_mulai" id="filter-tanggal-mulai" value="{{ $tanggal_mulai }}">
            <input type="hidden" name="tanggal_akhir" id="filter-tanggal-akhir" value="{{ $tanggal_akhir }}">
        </form>
    </div>
</div>

<!-- 3 Summary Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1 -->
    <div class="col-md-4">
        <div class="card card-stats h-100 p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="text-muted small fw-semibold">Total Santri</div>
                <i class="bi bi-people text-muted"></i>
            </div>
            <div class="h3 mb-1 fw-bold text-dark">{{ number_format($totalSantri) }}</div>
            <div class="small text-muted">
                Tercatat di sistem
            </div>
        </div>
    </div>
    <!-- Card 2 -->
    <div class="col-md-4">
        <div class="card card-stats h-100 p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="text-muted small fw-semibold">Hadir Periode Ini</div>
                <i class="bi bi-person-check text-success"></i>
            </div>
            <div class="h3 mb-1 fw-bold text-dark">{{ number_format($hadirHariIni) }}</div>
            <div class="small text-muted">
                {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/y') }}
            </div>
        </div>
    </div>
    <!-- Card 3 -->
    <div class="col-md-4">
        <div class="card card-stats h-100 p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="text-muted small fw-semibold">Tidak Hadir</div>
                <i class="bi bi-person-x text-danger"></i>
            </div>
            <div class="h3 mb-1 fw-bold text-dark">{{ number_format($tidakHadir) }}</div>
            <div class="small text-muted d-flex justify-content-between align-items-center mt-auto pt-2">
                <span>Total tidak hadir periode ini</span>
                @if($tidakHadir > 0)
                <button type="button" class="btn btn-sm btn-link text-success p-0 text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#modalTidakHadir" style="font-size: 0.75rem;">
                    Lihat Detail <i class="bi bi-arrow-right"></i>
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Row for Izin, Alfa, and Persentase lists/charts -->
<div class="row g-4 mb-4">
    <!-- Izin Card -->
    <div class="col-lg-4">
        <div class="card card-stats h-100 p-3">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-bold text-dark mb-0">Santri Izin (Periode)</h5>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">{{ $izinTodayRecords->count() }} Santri</span>
                </div>
                
                <div class="mt-2">
                    @if($izinTodayRecords->isNotEmpty())
                        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-4">
                            <div class="avatar-group d-flex align-items-center">
                                @foreach($izinTodayRecords->take(6) as $santriId => $records)
                                    @php $santri = $records->first()->santri; @endphp
                                    @if($santri->foto_referensi)
                                        <img src="{{ asset('storage/santri_fotos/' . $santri->foto_referensi) }}" class="rounded-circle" title="{{ $santri->nama }}">
                                    @else
                                        <div class="avatar-placeholder bg-info text-white rounded-circle d-flex align-items-center justify-content-center" title="{{ $santri->nama }}">
                                            <i class="bi bi-person" style="font-size: 0.8rem;"></i>
                                        </div>
                                    @endif
                                @endforeach
                                @if($izinTodayRecords->count() > 6)
                                    <div class="avatar-placeholder bg-white text-muted rounded-circle d-flex align-items-center justify-content-center fw-bold small">
                                        +{{ $izinTodayRecords->count() - 6 }}
                                    </div>
                                @endif
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-white shadow-sm border rounded-3 dropdown-toggle fw-bold text-info py-2 px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-lines-fill me-1"></i> Lihat Daftar
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 dropdown-menu-list">
                                    <li class="px-3 py-2 mb-2 border-bottom">
                                        <div class="fw-bold text-dark">Santri Izin</div>
                                        <div class="small text-muted">{{ $izinTodayRecords->count() }} orang hari ini</div>
                                    </li>
                                    @foreach($izinTodayRecords as $santriId => $records)
                                        @php 
                                            $santri = $records->first()->santri;
                                            $sholats = $records->pluck('waktu_sholat')->toArray();
                                            $isFullDay = in_array($santri->id, $fullDayIzinSantriIds);
                                        @endphp
                                        <li class="px-3 py-2 border-bottom border-light last-child-border-0">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="fw-semibold text-dark small">{{ $loop->iteration }}. {{ $santri->nama }}</div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <span class="x-small text-muted"><i class="bi bi-door-open me-1"></i>{{ $santri->kelas }}</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-info bg-opacity-10 text-info x-small">
                                                        @if($isFullDay)
                                                            Full Day
                                                        @else
                                                            {{ implode(', ', $sholats) }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 bg-light rounded-4">
                            <i class="bi bi-emoji-smile fs-3 text-muted d-block mb-2"></i>
                            <p class="text-muted small mb-0">Tidak ada santri yang izin hari ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Alfa Card -->
    <div class="col-lg-4">
        <div class="card card-stats h-100 p-3">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-bold text-dark mb-0">Santri Alfa (Periode)</h5>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3">{{ $alfaTodayRecords->count() }} Santri</span>
                </div>
                
                <div class="mt-2">
                    @if($alfaTodayRecords->isNotEmpty())
                        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-4">
                            <div class="avatar-group d-flex align-items-center">
                                @foreach($alfaTodayRecords->take(6) as $santriId => $records)
                                    @php $santri = $records->first()->santri; @endphp
                                    @if($santri->foto_referensi)
                                        <img src="{{ asset('storage/santri_fotos/' . $santri->foto_referensi) }}" class="rounded-circle" title="{{ $santri->nama }}">
                                    @else
                                        <div class="avatar-placeholder bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" title="{{ $santri->nama }}">
                                            <i class="bi bi-person" style="font-size: 0.8rem;"></i>
                                        </div>
                                    @endif
                                @endforeach
                                @if($alfaTodayRecords->count() > 6)
                                    <div class="avatar-placeholder bg-white text-muted rounded-circle d-flex align-items-center justify-content-center fw-bold small">
                                        +{{ $alfaTodayRecords->count() - 6 }}
                                    </div>
                                @endif
                            </div>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-white shadow-sm border rounded-3 dropdown-toggle fw-bold text-danger py-2 px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-exclamation-circle me-1"></i> Lihat Daftar
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 dropdown-menu-list">
                                    <li class="px-3 py-2 mb-2 border-bottom">
                                        <div class="fw-bold text-dark">Santri Alfa</div>
                                        <div class="small text-muted">{{ $alfaTodayRecords->count() }} orang tercatat</div>
                                    </li>
                                    @foreach($alfaTodayRecords as $santriId => $records)
                                        @php 
                                            $santri = $records->first()->santri;
                                            $sholats = $records->pluck('waktu_sholat')->toArray();
                                        @endphp
                                        <li class="px-3 py-2 border-bottom border-light last-child-border-0">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="fw-semibold text-dark small">{{ $loop->iteration }}. {{ $santri->nama }}</div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <span class="x-small text-muted"><i class="bi bi-door-open me-1"></i>{{ $santri->kelas }}</span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-danger bg-opacity-10 text-danger x-small">
                                                        {{ implode(', ', $sholats) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 bg-light rounded-4">
                            <i class="bi bi-check-circle fs-3 text-success d-block mb-2"></i>
                            <p class="text-muted small mb-0">Alhamdulillah, tidak ada santri alfa hari ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Persentase Kehadiran Card -->
    <div class="col-lg-4">
        <div class="card card-stats h-100 p-3">
            <div class="card-body p-0 d-flex flex-column justify-content-between" style="min-height: 140px;">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title fw-bold text-dark mb-0">Persentase Kehadiran</h5>
                        <i class="bi bi-graph-up-arrow text-success fs-5"></i>
                    </div>
                    
                    <div class="text-center py-2">
                        <div class="position-relative d-inline-flex align-items-center justify-content-center">
                            <!-- Circular Progress -->
                            <svg width="100" height="100" viewBox="0 0 100 100" style="transform: rotate(-90deg);">
                                <circle cx="50" cy="50" r="42" fill="transparent" stroke="#f3f4f6" stroke-width="8" />
                                <circle cx="50" cy="50" r="42" fill="transparent" stroke="url(#percentageGrad)" stroke-width="8" 
                                    stroke-dasharray="264" stroke-dashoffset="{{ 264 - (264 * $persentase) / 100 }}" stroke-linecap="round" />
                                <defs>
                                    <linearGradient id="percentageGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#198754" />
                                        <stop offset="100%" stop-color="#2dc57b" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="position-absolute text-center">
                                <div class="h3 fw-bold text-dark mb-0">{{ $persentase }}%</div>
                                <div class="text-muted" style="font-size: 0.65rem;">Kehadiran</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-light p-3 rounded-4 mt-auto">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small text-muted" style="font-size: 0.75rem;">Status Kehadiran</div>
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1" style="font-size: 0.7rem;">
                            {{ $persentase >= 85 ? 'Sangat Baik' : ($persentase >= 70 ? 'Baik' : 'Perlu Perhatian') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- Main Chart: Attendance Trend -->
    <div class="col-lg-8">
        <div class="card card-stats h-100 p-3">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-bold text-dark mb-0">Tren Kehadiran</h5>
                    <div class="small text-muted">{{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d M') }}</div>
                </div>
                <div style="height: 300px;">
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Chart: Status Distribution -->
    <div class="col-lg-4">
        <div class="card card-stats h-100 p-3">
            <div class="card-body p-0">
                <h5 class="card-title fw-bold text-dark mb-4">Distribusi Status</h5>
                <div style="height: 300px; position: relative;">
                    <canvas id="statusDistributionChart"></canvas>
                </div>
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted"><i class="bi bi-circle-fill text-success me-2" style="font-size: 0.6rem;"></i> Hadir</span>
                        <span class="fw-bold small">{{ $statusData[0] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted"><i class="bi bi-circle-fill text-info me-2" style="font-size: 0.6rem;"></i> Izin</span>
                        <span class="fw-bold small">{{ $statusData[1] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small text-muted"><i class="bi bi-circle-fill text-danger me-2" style="font-size: 0.6rem;"></i> Alfa</span>
                        <span class="fw-bold small">{{ $statusData[2] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Section: Recent Activity & Upcoming Tasks -->
<div class="row g-4">
    <!-- Recent Activity -->
    <div class="col-lg-6">
        <div class="card card-stats h-100 p-3">
            <div class="card-body p-0">
                <h5 class="card-title fw-bold text-dark mb-4">Aktivitas Terbaru</h5>
                
                @forelse($latestActivities as $index => $act)
                <div class="d-flex {{ $index < count($latestActivities) - 1 ? 'mb-4' : '' }}">
                    <div class="mt-1"><span class="activity-indicator"></span></div>
                    <div>
                        <div class="fw-semibold text-dark">{{ $act['title'] }}</div>
                        <div class="small text-muted">{{ $act['subtitle'] }}</div>
                        <div class="small text-black-50">
                            {{ $act['time'] instanceof \Carbon\Carbon ? $act['time']->diffForHumans() : \Carbon\Carbon::parse($act['time'])->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-info-circle fs-3 mb-2 d-block"></i>
                    <p class="small mb-0">Belum ada aktivitas terbaru</p>
                </div>
                @endforelse

            </div>
        </div>
    </div>

    <!-- Upcoming Tasks -->
    <div class="col-lg-6">
        <div class="card card-stats h-100 p-3">
            <div class="card-body p-0">
                <h5 class="card-title fw-bold text-dark mb-4">Jadwal Sholat</h5>
                <div class="small text-muted mb-3">Berdasarkan tanggal: {{ \Carbon\Carbon::parse($tanggal_akhir)->format('d/m/Y') }}</div>
                
                @if($jadwal)
                    <!-- Subuh -->
                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-light">
                        <div class="task-checkbox me-3"><i class="bi bi-moon-stars-fill"></i></div>
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div class="fw-semibold text-dark">Subuh</div>
                            <div class="badge-soft badge-soft-success py-1 px-3">{{ $jadwal['Fajr'] ?? '-' }}</div>
                        </div>
                    </div>
                    <!-- Dzuhur -->
                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-light">
                        <div class="task-checkbox me-3"><i class="bi bi-sun-fill"></i></div>
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div class="fw-semibold text-dark">Dzuhur</div>
                            <div class="badge-soft badge-soft-success py-1 px-3">{{ $jadwal['Dhuhr'] ?? '-' }}</div>
                        </div>
                    </div>
                    <!-- Ashar -->
                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-light">
                        <div class="task-checkbox me-3"><i class="bi bi-sun-fill"></i></div>
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div class="fw-semibold text-dark">Ashar</div>
                            <div class="badge-soft badge-soft-success py-1 px-3">{{ $jadwal['Asr'] ?? '-' }}</div>
                        </div>
                    </div>
                    <!-- Maghrib -->
                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-light">
                        <div class="task-checkbox me-3"><i class="bi bi-moon-stars-fill"></i></div>
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div class="fw-semibold text-dark">Maghrib</div>
                            <div class="badge-soft badge-soft-success py-1 px-3">{{ $jadwal['Maghrib'] ?? '-' }}</div>
                        </div>
                    </div>
                    <!-- Isya -->
                    <div class="d-flex align-items-center">
                        <div class="task-checkbox me-3"><i class="bi bi-moon-stars-fill"></i></div>
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div class="fw-semibold text-dark">Isya</div>
                            <div class="badge-soft badge-soft-success py-1 px-3">{{ $jadwal['Isha'] ?? '-' }}</div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>
                        Gagal memuat jadwal sholat.
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- Modal Tidak Hadir -->
@if(isset($absentSantris))
<div class="modal fade" id="modalTidakHadir" tabindex="-1" aria-labelledby="modalTidakHadirLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h6 class="modal-title fw-bold" id="modalTidakHadirLabel">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    @if($waktuSholat)
                        Daftar Tidak Hadir - {{ $waktuSholat }}
                    @else
                        Daftar Santri Tidak Hadir Periode Ini
                    @endif
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($absentSantris as $santri)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center gap-3">
                                @if($santri->foto_referensi)
                                    <img src="{{ asset('storage/santri_fotos/' . $santri->foto_referensi) }}" alt="Foto" class="rounded-circle object-fit-cover" style="width: 40px; height: 40px;">
                                @else
                                    <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person fs-5"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-semibold text-dark">{{ $santri->nama }}</div>
                                    <div class="small text-muted"><i class="bi bi-easel me-1"></i>Kelas {{ $santri->kelas }}</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if(($santri->current_status ?? 'Alfa') == 'Izin')
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">Izin</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3">Alpha</span>
                                @endif
                                
                                <div class="d-flex gap-1 ms-2">
                                    <button type="button" class="btn btn-sm btn-white border px-2 py-1" onclick="editStatus('{{ $santri->id }}', '{{ $tanggal_akhir }}', '{{ $waktuSholat ?: 'Subuh' }}', '{{ $santri->current_status ?? 'Alfa' }}')">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-white border px-2 py-1" onclick="deletePresensi('{{ $santri->id }}', '{{ $tanggal_akhir }}', '{{ $waktuSholat ?: 'Subuh' }}')">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-5">
                            <i class="bi bi-check-circle fs-1 text-success d-block mb-3"></i>
                            @if($waktuSholat)
                                Alhamdulillah, semua santri hadir pada waktu sholat ini.
                            @else
                                Semua santri sudah melakukan presensi hari ini.
                            @endif
                        </li>
                    @endforelse
                </ul>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Filter variables and functions
        window.currentPeriod = "{{ $currentPeriod }}";
        window.startDateStr = "{{ $tanggal_mulai }}";
        window.endDateStr = "{{ $tanggal_akhir }}";
        window.currentYear = parseInt("{{ $currentYear }}");

        window.formatDateISO = function(date) {
            let yyyy = date.getFullYear();
            let mm = String(date.getMonth() + 1).padStart(2, '0');
            let dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        window.changePeriod = function(period) {
            let date = new Date(window.startDateStr);
            if (isNaN(date.getTime())) {
                date = new Date();
            }
            
            if (period === 'day') {
                let dateStr = window.formatDateISO(date);
                document.getElementById('filter-period').value = 'day';
                document.getElementById('filter-tanggal-mulai').value = dateStr;
                document.getElementById('filter-tanggal-akhir').value = dateStr;
            } else if (period === 'week') {
                let day = date.getDay();
                let diffToMonday = date.getDate() - day + (day === 0 ? -6 : 1);
                let monday = new Date(date.setDate(diffToMonday));
                let sunday = new Date(monday);
                sunday.setDate(monday.getDate() + 6);
                
                document.getElementById('filter-period').value = 'week';
                document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(monday);
                document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(sunday);
            } else if (period === 'month') {
                let firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
                let lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
                
                document.getElementById('filter-period').value = 'month';
                document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(firstDay);
                document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(lastDay);
            }
            document.getElementById('filter-form').submit();
        }

        window.navigate = function(direction) {
            let date = new Date(window.startDateStr);
            if (isNaN(date.getTime())) {
                date = new Date();
            }
            
            if (window.currentPeriod === 'day') {
                date.setDate(date.getDate() + direction);
                let dateStr = window.formatDateISO(date);
                document.getElementById('filter-tanggal-mulai').value = dateStr;
                document.getElementById('filter-tanggal-akhir').value = dateStr;
            } else if (window.currentPeriod === 'week') {
                date.setDate(date.getDate() + (direction * 7));
                let day = date.getDay();
                let diffToMonday = date.getDate() - day + (day === 0 ? -6 : 1);
                let monday = new Date(date.setDate(diffToMonday));
                let sunday = new Date(monday);
                sunday.setDate(monday.getDate() + 6);
                
                document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(monday);
                document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(sunday);
            } else if (window.currentPeriod === 'month') {
                date.setMonth(date.getMonth() + direction);
                let firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
                let lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
                
                document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(firstDay);
                document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(lastDay);
            }
            document.getElementById('filter-form').submit();
        }

        const yearDisplay = document.getElementById('year-display');
        const btnPrevYear = document.getElementById('btn-prev-year');
        const btnNextYear = document.getElementById('btn-next-year');
        
        if (yearDisplay && btnPrevYear && btnNextYear) {
            btnPrevYear.addEventListener('click', (e) => {
                e.stopPropagation();
                window.currentYear--;
                yearDisplay.textContent = window.currentYear;
                updateMonthGridActiveYear();
            });
            
            btnNextYear.addEventListener('click', (e) => {
                e.stopPropagation();
                window.currentYear++;
                yearDisplay.textContent = window.currentYear;
                updateMonthGridActiveYear();
            });
        }

        const monthButtons = document.querySelectorAll('.month-select-btn');
        monthButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const monthNum = parseInt(btn.getAttribute('data-month'));
                
                if (window.currentPeriod === 'day') {
                    const firstDay = new Date(window.currentYear, monthNum - 1, 1);
                    document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(firstDay);
                    document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(firstDay);
                } else if (window.currentPeriod === 'week') {
                    const firstDay = new Date(window.currentYear, monthNum - 1, 1);
                    const seventhDay = new Date(window.currentYear, monthNum - 1, 7);
                    document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(firstDay);
                    document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(seventhDay);
                } else {
                    const firstDay = new Date(window.currentYear, monthNum - 1, 1);
                    const lastDay = new Date(window.currentYear, monthNum, 0);
                    document.getElementById('filter-tanggal-mulai').value = window.formatDateISO(firstDay);
                    document.getElementById('filter-tanggal-akhir').value = window.formatDateISO(lastDay);
                }
                
                document.getElementById('filter-period').value = window.currentPeriod;
                document.getElementById('filter-form').submit();
            });
        });

        function updateMonthGridActiveYear() {
            const phpMonth = parseInt("{{ $currentMonth }}");
            const phpYear = parseInt("{{ $currentYear }}");
            
            monthButtons.forEach(btn => {
                const m = parseInt(btn.getAttribute('data-month'));
                if (window.currentYear === phpYear && m === phpMonth) {
                    btn.classList.remove('btn-light', 'bg-transparent', 'border-0', 'text-dark');
                    btn.classList.add('btn-success', 'text-white');
                } else {
                    btn.classList.add('btn-light', 'bg-transparent', 'border-0', 'text-dark');
                    btn.classList.remove('btn-success', 'text-white');
                }
            });
        }

        // Attendance Trend Chart
        const trendCtx = document.getElementById('attendanceTrendChart').getContext('2d');
        const trendGradient = trendCtx.createLinearGradient(0, 0, 0, 300);
        trendGradient.addColorStop(0, 'rgba(25, 135, 84, 0.2)');
        trendGradient.addColorStop(1, 'rgba(25, 135, 84, 0)');

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Kehadiran',
                    data: {!! json_encode($chartData) !!},
                    borderColor: '#198754',
                    backgroundColor: trendGradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#198754',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#333',
                        bodyColor: '#666',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' Kehadiran';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#adb5bd' }
                    },
                    y: {
                        grid: { color: '#f8f9fa', drawBorder: false },
                        ticks: {
                            color: '#adb5bd',
                            callback: function(value) { return value; }
                        },
                        min: 0,
                        suggestedMax: 5
                    }
                }
            }
        });

        // Status Distribution Chart
        const distCtx = document.getElementById('statusDistributionChart').getContext('2d');
        new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Izin', 'Alfa'],
                datasets: [{
                    data: {!! json_encode($statusData) !!},
                    backgroundColor: ['#198754', '#0dcaf0', '#dc3545'],
                    hoverOffset: 4,
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#333',
                        bodyColor: '#666',
                        borderColor: '#ddd',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: true,
                        boxWidth: 8,
                        boxHeight: 8,
                        usePointStyle: true,
                    }
                }
            }
        });
    });
</script>
@endpush
