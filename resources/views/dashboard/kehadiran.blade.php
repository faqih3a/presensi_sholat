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
    .no-caret::after {
        display: none !important;
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
        <h1 class="h3 mb-0 text-dark fw-bold">Rekap Kehadiran Sholat</h1>
        <p class="text-muted mb-0">Pantau detail kehadiran sholat berjamaah santri secara keseluruhan.</p>
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
        <form id="filter-form" action="{{ route('dashboard.kehadiran') }}" method="GET" class="no-loader d-none">
            <input type="hidden" name="waktu_sholat" value="{{ request('waktu_sholat') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="period" id="filter-period" value="{{ $currentPeriod }}">
            <input type="hidden" name="tanggal_mulai" id="filter-tanggal-mulai" value="{{ $tanggal_mulai }}">
            <input type="hidden" name="tanggal_akhir" id="filter-tanggal-akhir" value="{{ $tanggal_akhir }}">
        </form>
    </div>
</div>

<div class="card card-stats mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <h6 class="m-0 fw-bold text-dark"><i class="bi bi-table text-success me-2"></i>Data Rekap Kehadiran</h6>
        <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center">
            <form id="filterForm" action="{{ route('dashboard.kehadiran') }}" method="GET" class="d-flex flex-wrap align-items-center gap-3 m-0 no-loader">
                <input type="hidden" name="tanggal_mulai" value="{{ $tanggal_mulai }}">
                <input type="hidden" name="tanggal_akhir" value="{{ $tanggal_akhir }}">
                <input type="hidden" name="period" value="{{ request('period', 'day') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
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
                            <li><a class="dropdown-item py-2 {{ request('status') == 'Izin' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateFilter('status', 'Izin')">Izin</a></li>
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
        });
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
                        <th class="text-center">Aksi</th>
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
                            @elseif($presensi->status == 'Izin')
                                <span class="badge badge-soft badge-soft-info px-4">Izin</span>
                            @else
                                <span class="badge badge-soft badge-soft-success px-4">Hadir</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-white border px-2 py-1 rounded-2 shadow-sm" title="Edit Status" onclick="editStatus('{{ $presensi->santri_id }}', '{{ $presensi->tanggal }}', '{{ $presensi->waktu_sholat }}', '{{ $presensi->status }}')">
                                    <i class="bi bi-pencil-square text-primary"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-white border px-2 py-1 rounded-2 shadow-sm" title="Hapus" onclick="deletePresensi('{{ $presensi->santri_id }}', '{{ $presensi->tanggal }}', '{{ $presensi->waktu_sholat }}')">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
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
