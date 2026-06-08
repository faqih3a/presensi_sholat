@extends('layouts.app')

@section('title', 'Kelola Izin Santri')

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

<div class="row">
    <div class="col-md-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold mb-0">Kelola Izin / Sakit</h4>
                <p class="text-muted mb-0">Tinjau dan proses pengajuan izin dari santri</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end">
                <!-- Period Selection Tab -->
                <div class="btn-group bg-white p-1 rounded-pill border shadow-sm" role="group">
                    <button type="button" onclick="changePeriod('day')" class="btn rounded-pill px-4 py-2 fw-bold {{ $currentPeriod == 'day' ? 'btn-success text-white' : 'btn-light text-success bg-transparent border-0' }}" style="font-size: 0.9rem; min-width: 85px;">Day</button>
                    <button type="button" onclick="changePeriod('week')" class="btn rounded-pill px-4 py-2 fw-bold {{ $currentPeriod == 'week' ? 'btn-success text-white' : 'btn-light text-success bg-transparent border-0' }}" style="font-size: 0.9rem; min-width: 85px;">Week</button>
                    <button type="button" onclick="changePeriod('month')" class="btn rounded-pill px-4 py-2 fw-bold {{ $currentPeriod == 'month' ? 'btn-success text-white' : 'btn-light text-success bg-transparent border-0' }}" style="font-size: 0.9rem; min-width: 85px;">Month</button>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <!-- Arrow Left -->
                    <button type="button" onclick="navigate(-1)" class="btn btn-white border rounded-3 px-3.5 py-2 shadow-sm text-secondary" style="display: flex; align-items: center; justify-content: center; height: 38px;">
                        <i class="bi bi-chevron-left"></i>
                    </button>

                    <!-- Month Dropdown Toggle (Always Month/Year dropdown) -->
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-white border rounded-3 px-4 py-2 shadow-sm fw-bold text-success dropdown-toggle no-caret" type="button" id="date-label-btn" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.9rem; min-width: 170px; height: 38px;">
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
                    <button type="button" onclick="navigate(1)" class="btn btn-white border rounded-3 px-3.5 py-2 shadow-sm text-secondary" style="display: flex; align-items: center; justify-content: center; height: 38px;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>

                <!-- Hidden Form for GET submission -->
                <form id="filter-form" action="{{ route('izin.manage') }}" method="GET" class="no-loader d-none">
                    <input type="hidden" name="period" id="filter-period" value="{{ $currentPeriod }}">
                    <input type="hidden" name="tanggal_mulai" id="filter-tanggal-mulai" value="{{ $tanggal_mulai }}">
                    <input type="hidden" name="tanggal_akhir" id="filter-tanggal-akhir" value="{{ $tanggal_akhir }}">
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 border-0">Santri</th>
                            <th class="border-0">Jenis Izin</th>
                            <th class="border-0">Tanggal</th>
                            <th class="border-0">Keterangan</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($izins as $izin)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        @if($izin->user->role === 'santri' && $izin->user->santri && $izin->user->santri->foto_referensi && \Illuminate\Support\Facades\Storage::disk('public')->exists('santri_fotos/' . $izin->user->santri->foto_referensi))
                                            <img src="{{ asset('storage/santri_fotos/' . $izin->user->santri->foto_referensi) }}" alt="Profile" class="rounded-circle me-3 shadow-sm object-fit-cover" style="width: 40px; height: 40px; border: 2px solid #fff;">
                                        @else
                                            <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 40px; height: 40px;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold text-dark">{{ $izin->user->name }}</div>
                                            <div class="small text-muted">{{ $izin->user->role }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge 
                                            @if($izin->jenis_izin == 'Sakit') bg-danger-subtle text-danger 
                                            @elseif($izin->jenis_izin == 'Izin') bg-info-subtle text-info 
                                            @else bg-warning-subtle text-warning @endif 
                                            px-2 py-1 rounded-3">
                                            {{ $izin->jenis_izin }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-medium">
                                        {{ $izin->tanggal_mulai->format('d/m/y') }} - {{ $izin->tanggal_selesai->format('d/m/y') }}
                                    </div>
                                    <div class="d-flex align-items-center x-small text-muted">
                                        <span class="me-2">{{ $izin->tanggal_mulai->diffInDays($izin->tanggal_selesai) + 1 }} Hari</span>
                                        @if($izin->waktu_sholat && $izin->waktu_sholat !== 'Full Day')
                                            <span class="badge bg-secondary-subtle text-secondary py-0 px-1" style="font-size: 0.65rem;">{{ $izin->waktu_sholat }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $izin->keterangan }}">
                                        {{ $izin->keterangan }}
                                    </span>
                                </td>
                                <td>
                                    @if($izin->status == 'Pending')
                                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-medium">
                                            <i class="bi bi-clock-history me-1"></i> Pending
                                        </span>
                                    @elseif($izin->status == 'Disetujui')
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-medium">
                                            <i class="bi bi-check-circle me-1"></i> Disetujui
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-medium">
                                            <i class="bi bi-x-circle me-1"></i> Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($izin->lampiran)
                                            <a href="{{ asset('storage/' . $izin->lampiran) }}" target="_blank" class="btn btn-sm btn-light rounded-3 shadow-sm border-0 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Lihat Lampiran">
                                                <i class="bi bi-paperclip"></i>
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-sm bg-success-subtle text-success rounded-3 px-3 fw-semibold border-0 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#actionModal{{ $izin->id }}" style="height: 32px;">
                                            <i class="bi bi-check2-circle"></i> Proses
                                        </button>
                                    </div>

                                    <!-- Action Modal -->
                                    <div class="modal fade" id="actionModal{{ $izin->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header border-bottom py-3">
                                                    <h5 class="modal-title fw-bold">Proses Izin: {{ $izin->user->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('izin.update-status', $izin->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body p-4 text-start">
                                                        <div class="bg-light p-3 rounded-3 mb-4">
                                                            <div class="small text-muted mb-1 text-uppercase fw-bold">Detail Pengajuan</div>
                                                            <div class="fw-bold mb-1">{{ $izin->jenis_izin }} ({{ $izin->tanggal_mulai->format('d M') }} - {{ $izin->tanggal_selesai->format('d M Y') }})</div>
                                                            @if($izin->waktu_sholat && $izin->waktu_sholat !== 'Full Day')
                                                                <div class="badge bg-success text-white mb-2">{{ $izin->waktu_sholat }}</div>
                                                            @endif
                                                            <div class="small text-muted">{{ $izin->keterangan }}</div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Keputusan</label>
                                                            <div class="premium-select-wrapper">
                                                                <button class="premium-select-btn dropdown-toggle" type="button" id="statusDropdown{{ $izin->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                    <span id="selected-status-text{{ $izin->id }}">{{ $izin->status == 'Disetujui' ? 'Setujui' : ($izin->status == 'Ditolak' ? 'Tolak' : 'Pilih Keputusan') }}</span>
                                                                    <i class="bi bi-chevron-down small text-muted"></i>
                                                                </button>
                                                                <ul class="dropdown-menu shadow border-0" aria-labelledby="statusDropdown{{ $izin->id }}">
                                                                    <li><a class="dropdown-item py-2 {{ $izin->status == 'Disetujui' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateManageStatus({{ $izin->id }}, 'Disetujui', 'Setujui')">Setujui</a></li>
                                                                    <li><a class="dropdown-item py-2 {{ $izin->status == 'Ditolak' ? 'active' : '' }}" href="javascript:void(0)" onclick="updateManageStatus({{ $izin->id }}, 'Ditolak', 'Tolak')">Tolak</a></li>
                                                                </ul>
                                                                <input type="hidden" name="status" id="status_input{{ $izin->id }}" value="{{ $izin->status }}" required>
                                                            </div>
                                                        </div>

                                                        <div class="mb-0">
                                                            <label class="form-label fw-bold">Keterangan Admin (Opsional)</label>
                                                            <textarea name="keterangan_admin" rows="3" class="form-control rounded-3" placeholder="Tambahkan catatan atau alasan penolakan...">{{ $izin->keterangan_admin }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top p-3">
                                                        <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-success rounded-3 px-4">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-inbox text-muted fs-1 mb-3 d-block"></i>
                                        <p class="text-muted mb-0">Belum ada pengajuan izin yang perlu diproses.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.75rem; }
    .bg-danger-subtle { background-color: rgba(239, 68, 68, 0.1); }
    .bg-info-subtle { background-color: rgba(58, 176, 255, 0.1); }
    .bg-warning-subtle { background-color: rgba(245, 158, 11, 0.1); }
    .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
    .avatar-sm { font-size: 1.2rem; }
</style>
<script>
    function updateManageStatus(id, val, text) {
        document.getElementById('status_input' + id).value = val;
        document.getElementById('selected-status-text' + id).innerText = text;
        
        // Update active state in that specific dropdown
        const dropdown = document.querySelector('[aria-labelledby="statusDropdown' + id + '"]');
        const items = dropdown.querySelectorAll('.dropdown-item');
        items.forEach(item => {
            if (item.innerText === text) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
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
@endsection
