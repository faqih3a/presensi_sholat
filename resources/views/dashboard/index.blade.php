@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
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
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 mb-0 text-dark fw-bold">Dashboard</h1>
        <p class="text-muted mb-0">Selamat datang di sistem presensi sholat</p>
    </div>
    <form action="{{ route('dashboard') }}" method="GET" class="no-loader d-flex flex-wrap gap-2 align-items-center">
        <input type="hidden" name="waktu_sholat" value="{{ $waktuSholat }}">
        <div class="bg-white p-1 rounded-3 shadow-sm d-flex border align-items-center px-2">
            <span class="small fw-bold text-muted me-2">DARI:</span>
            <input type="date" name="tanggal_mulai" value="{{ $tanggal_mulai }}" class="form-control form-control-sm border-0 p-0 shadow-none fw-bold text-success" style="width: 130px; font-size: 0.8rem;" onchange="this.form.submit()">
        </div>
        <div class="bg-white p-1 rounded-3 shadow-sm d-flex border align-items-center px-2">
            <span class="small fw-bold text-muted me-2">SAMPAI:</span>
            <input type="date" name="tanggal_akhir" value="{{ $tanggal_akhir }}" class="form-control form-control-sm border-0 p-0 shadow-none fw-bold text-success" style="width: 130px; font-size: 0.8rem;" onchange="this.form.submit()">
        </div>
    </form>
</div>

<!-- 4 Summary Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1 -->
    <div class="col-xl-3 col-md-6">
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
    <div class="col-xl-3 col-md-6">
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
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats h-100 p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="text-muted small fw-semibold">Tidak Hadir</div>
                <form id="sholatFilterForm" action="{{ route('dashboard') }}" method="GET" class="m-0 no-loader">
                    <input type="hidden" name="tanggal_mulai" value="{{ $tanggal_mulai }}">
                    <input type="hidden" name="tanggal_akhir" value="{{ $tanggal_akhir }}">
                    <input type="hidden" name="waktu_sholat" id="hidden_waktu_sholat" value="{{ $waktuSholat }}">
                    
                    <div class="dropdown">
                        <button class="btn btn-sm bg-light text-muted fw-bold border-0 dropdown-toggle py-0 px-2 d-flex align-items-center gap-1" type="button" id="sholatDashboardDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.7rem; border-radius: 0.5rem; height: 24px;">
                            {{ $waktuSholat ?: 'Semua Waktu ' }} <i class="bi bi-chevron-down" style="font-size: 0.6rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="sholatDashboardDropdown" style="border-radius: 0.75rem; padding: 0.4rem; font-size: 0.8rem;">
                            <li><a class="dropdown-item py-1 {{ $waktuSholat == '' ? 'active' : '' }}" href="javascript:void(0)" onclick="submitSholatFilter('')">Semua Waktu</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-1 {{ $waktuSholat == 'Subuh' ? 'active' : '' }}" href="javascript:void(0)" onclick="submitSholatFilter('Subuh')">Subuh</a></li>
                            <li><a class="dropdown-item py-1 {{ $waktuSholat == 'Dzuhur' ? 'active' : '' }}" href="javascript:void(0)" onclick="submitSholatFilter('Dzuhur')">Dzuhur</a></li>
                            <li><a class="dropdown-item py-1 {{ $waktuSholat == 'Ashar' ? 'active' : '' }}" href="javascript:void(0)" onclick="submitSholatFilter('Ashar')">Ashar</a></li>
                            <li><a class="dropdown-item py-1 {{ $waktuSholat == 'Maghrib' ? 'active' : '' }}" href="javascript:void(0)" onclick="submitSholatFilter('Maghrib')">Maghrib</a></li>
                            <li><a class="dropdown-item py-1 {{ $waktuSholat == 'Isya' ? 'active' : '' }}" href="javascript:void(0)" onclick="submitSholatFilter('Isya')">Isya</a></li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="h3 mb-1 fw-bold text-dark">{{ number_format($tidakHadir) }}</div>
            <div class="small text-muted d-flex justify-content-between align-items-center mt-auto pt-2">
                <span>{{ $waktuSholat ? 'Pada waktu ' . $waktuSholat : 'Total tidak hadir periode ini' }}</span>
                @if($tidakHadir > 0)
                <button type="button" class="btn btn-sm btn-link text-success p-0 text-decoration-none fw-bold" data-bs-toggle="modal" data-bs-target="#modalTidakHadir" style="font-size: 0.75rem;">
                    Lihat Detail <i class="bi bi-arrow-right"></i>
                </button>
                @endif
            </div>

            <script>
                function submitSholatFilter(val) {
                    document.getElementById('hidden_waktu_sholat').value = val;
                    document.getElementById('sholatFilterForm').submit();
                }
            </script>
        </div>
    </div>
    <!-- Card 4 -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-stats h-100 p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="text-muted small fw-semibold">Persentase Kehadiran</div>
                <i class="bi bi-graph-up-arrow text-success"></i>
            </div>
            <div class="h3 mb-1 fw-bold text-dark">{{ $persentase }}%</div>
            <div class="small text-muted">
                Dari total keseluruhan
            </div>
        </div>
    </div>
</div>

<!-- Row for Izin and Alfa lists -->
<div class="row g-4 mb-4">
    <!-- Izin Card -->
    <div class="col-lg-6">
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
                                                    @if(!$isFullDay)
                                                        @foreach($records as $rec)
                                                            <div class="d-flex gap-1 ms-2">
                                                                <button type="button" class="btn btn-xs btn-outline-info p-0 px-1" style="font-size: 0.6rem;" onclick="editStatus('{{ $santri->id }}', '{{ $rec->tanggal }}', '{{ $rec->waktu_sholat }}', '{{ $rec->status }}')" title="Edit {{ $rec->waktu_sholat }}">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-xs btn-outline-danger p-0 px-1" style="font-size: 0.6rem;" onclick="deletePresensi('{{ $santri->id }}', '{{ $rec->tanggal }}', '{{ $rec->waktu_sholat }}')" title="Hapus {{ $rec->waktu_sholat }}">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    @endif
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
    <div class="col-lg-6">
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
                                                    @foreach($records as $rec)
                                                        <div class="d-flex gap-1 ms-2">
                                                            <button type="button" class="btn btn-xs btn-outline-info p-0 px-1" style="font-size: 0.6rem;" onclick="editStatus('{{ $santri->id }}', '{{ $rec->tanggal }}', '{{ $rec->waktu_sholat }}', '{{ $rec->status }}')" title="Edit {{ $rec->waktu_sholat }}">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-xs btn-outline-danger p-0 px-1" style="font-size: 0.6rem;" onclick="deletePresensi('{{ $santri->id }}', '{{ $rec->tanggal }}', '{{ $rec->waktu_sholat }}')" title="Hapus {{ $rec->waktu_sholat }}">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
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
                
                <div class="d-flex mb-4">
                    <div class="mt-1"><span class="activity-indicator"></span></div>
                    <div>
                        <div class="fw-semibold text-dark">Santri baru didaftarkan</div>
                        <div class="small text-muted">Ahmad dari Kelas 10A</div>
                        <div class="small text-black-50">2 jam yang lalu</div>
                    </div>
                </div>

                <div class="d-flex mb-4">
                    <div class="mt-1"><span class="activity-indicator"></span></div>
                    <div>
                        <div class="fw-semibold text-dark">Presensi Subuh selesai</div>
                        <div class="small text-muted">450 santri hadir tepat waktu</div>
                        <div class="small text-black-50">5 jam yang lalu</div>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="mt-1"><span class="activity-indicator"></span></div>
                    <div>
                        <div class="fw-semibold text-dark">Laporan mingguan diunduh</div>
                        <div class="small text-muted">Oleh Ust. Budi</div>
                        <div class="small text-black-50">1 hari yang lalu</div>
                    </div>
                </div>

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
