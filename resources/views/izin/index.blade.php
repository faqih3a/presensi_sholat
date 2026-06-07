@extends('layouts.app')

@section('title', 'Daftar Izin Saya')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Izin Saya</h4>
                <p class="text-muted mb-0">Kelola dan pantau status pengajuan izin Anda</p>
            </div>
            <button type="button" class="btn btn-success rounded-3 px-4 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#createIzinModal">
                <i class="bi bi-plus-lg me-2"></i> Ajukan Izin Baru
            </button>
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
                            <th class="ps-4 border-0">Jenis Izin</th>
                            <th class="border-0">Tanggal</th>
                            <th class="border-0">Keterangan</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($izins as $izin)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded-3 me-3 
                                            @if($izin->jenis_izin == 'Sakit') bg-danger-subtle text-danger 
                                            @elseif($izin->jenis_izin == 'Izin') bg-info-subtle text-info 
                                            @else bg-warning-subtle text-warning @endif">
                                            @if($izin->jenis_izin == 'Sakit') <i class="bi bi-heart-pulse-fill"></i>
                                            @elseif($izin->jenis_izin == 'Izin') <i class="bi bi-file-earmark-text-fill"></i>
                                            @else <i class="bi bi-briefcase-fill"></i> @endif
                                        </div>
                                        <span class="fw-semibold">{{ $izin->jenis_izin }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="small fw-medium">
                                        {{ $izin->tanggal_mulai->format('d M Y') }} - {{ $izin->tanggal_selesai->format('d M Y') }}
                                    </div>
                                    <div class="d-flex align-items-center x-small text-muted">
                                        @php
                                            $diff = $izin->tanggal_mulai->diffInDays($izin->tanggal_selesai) + 1;
                                        @endphp
                                        <span class="me-2">{{ $diff }} Hari</span>
                                        @if($izin->waktu_sholat && $izin->waktu_sholat !== 'Full Day')
                                            <span class="badge bg-secondary-subtle text-secondary py-0 px-1" style="font-size: 0.65rem;">{{ $izin->waktu_sholat }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $izin->keterangan }}">
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
                                <td class="pe-4">
                                    <button type="button" class="btn btn-sm btn-light rounded-3" data-bs-toggle="modal" data-bs-target="#detailModal{{ $izin->id }}">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>

                                    <!-- Detail Modal -->
                                    <div class="modal fade" id="detailModal{{ $izin->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header border-bottom py-3">
                                                    <h5 class="modal-title fw-bold">Detail Pengajuan Izin</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="mb-4">
                                                        <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Jenis Izin</label>
                                                        <p class="mb-0 fw-semibold fs-5">{{ $izin->jenis_izin }}</p>
                                                    </div>
                                                    <div class="row mb-4">
                                                        <div class="col-4">
                                                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Waktu</label>
                                                            <p class="mb-0 fw-medium text-success">{{ $izin->waktu_sholat ?? 'Full Day' }}</p>
                                                        </div>
                                                        <div class="col-4">
                                                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Mulai</label>
                                                            <p class="mb-0 fw-medium">{{ $izin->tanggal_mulai->format('d M Y') }}</p>
                                                        </div>
                                                        <div class="col-4">
                                                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Selesai</label>
                                                            <p class="mb-0 fw-medium">{{ $izin->tanggal_selesai->format('d M Y') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-4">
                                                        <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Keterangan / Alasan</label>
                                                        <p class="mb-0 p-3 bg-light rounded-3">{{ $izin->keterangan }}</p>
                                                    </div>
                                                    @if($izin->lampiran)
                                                        <div class="mb-4">
                                                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Lampiran</label>
                                                            <a href="{{ asset('storage/' . $izin->lampiran) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-3 w-100 py-2">
                                                                <i class="bi bi-file-earmark-arrow-down me-2"></i> Lihat Lampiran
                                                            </a>
                                                        </div>
                                                    @endif
                                                    @if($izin->status == 'Ditolak' && $izin->keterangan_admin)
                                                        <div class="mb-0">
                                                            <label class="text-danger small text-uppercase fw-bold mb-1 d-block">Alasan Penolakan</label>
                                                            <p class="mb-0 p-3 bg-danger-subtle text-danger rounded-3 fw-medium">{{ $izin->keterangan_admin }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer border-top p-3">
                                                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-file-earmark-x text-muted fs-1 mb-3 d-block"></i>
                                        <p class="text-muted mb-0">Belum ada pengajuan izin.</p>
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

<!-- Modal Ajukan Izin Baru -->
<div class="modal fade" id="createIzinModal" tabindex="-1" aria-labelledby="createIzinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success-subtle p-2 rounded-3 me-3">
                        <i class="bi bi-file-earmark-plus-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold" id="createIzinModalLabel">Pengajuan Izin / Sakit</h5>
                        <small class="text-muted">Silakan lengkapi form berikut untuk mengajukan izin</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('izin.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Jenis Izin</label>
                            <div class="premium-select-wrapper">
                                <button class="premium-select-btn dropdown-toggle @error('jenis_izin') is-invalid @enderror" type="button" id="jenisIzinDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="selected-jenis-text">{{ old('jenis_izin') ? (old('jenis_izin') == 'Izin' ? 'Izin (Kepentingan Keluarga, dll)' : old('jenis_izin')) : 'Pilih Jenis Izin' }}</span>
                                    <i class="bi bi-chevron-down small text-muted"></i>
                                </button>
                                <ul class="dropdown-menu shadow border-0" aria-labelledby="jenisIzinDropdown">
                                    <li><a class="dropdown-item py-2 {{ old('jenis_izin') == 'Sakit' ? 'active' : '' }}" href="javascript:void(0)" onclick="selectJenisIzin('Sakit', 'Sakit')">Sakit</a></li>
                                    <li><a class="dropdown-item py-2 {{ old('jenis_izin') == 'Izin' ? 'active' : '' }}" href="javascript:void(0)" onclick="selectJenisIzin('Izin', 'Izin (Kepentingan Keluarga, dll)')">Izin (Kepentingan Keluarga, dll)</a></li>
                                    <li><a class="dropdown-item py-2 {{ old('jenis_izin') == 'Kegiatan Luar' ? 'active' : '' }}" href="javascript:void(0)" onclick="selectJenisIzin('Kegiatan Luar', 'Kegiatan di Luar')">Kegiatan di Luar</a></li>
                                </ul>
                                <input type="hidden" name="jenis_izin" id="jenis_izin_input" value="{{ old('jenis_izin') }}" required>
                            </div>
                            @error('jenis_izin')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Waktu Izin</label>
                            <div class="premium-select-wrapper">
                                <button class="premium-select-btn dropdown-toggle @error('waktu_sholat') is-invalid @enderror" type="button" id="waktuSholatDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="selected-sholat-text">{{ old('waktu_sholat') ? (old('waktu_sholat') == 'Full Day' ? 'Full Day (Semua Waktu)' : old('waktu_sholat')) : 'Full Day (Semua Waktu)' }}</span>
                                    <i class="bi bi-chevron-down small text-muted"></i>
                                </button>
                                <ul class="dropdown-menu shadow border-0" aria-labelledby="waktuSholatDropdown">
                                    <li><a class="dropdown-item py-2 {{ !old('waktu_sholat') || old('waktu_sholat') == 'Full Day' ? 'active' : '' }}" href="javascript:void(0)" onclick="selectWaktuSholat('Full Day', 'Full Day (Semua Waktu)')">Full Day (Semua Waktu)</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item py-2 {{ old('waktu_sholat') == 'Subuh' ? 'active' : '' }}" href="javascript:void(0)" onclick="selectWaktuSholat('Subuh', 'Subuh')">Subuh</a></li>
                                    <li><a class="dropdown-item py-2 {{ old('waktu_sholat') == 'Dzuhur' ? 'active' : '' }}" href="javascript:void(0)" onclick="selectWaktuSholat('Dzuhur', 'Dzuhur')">Dzuhur</a></li>
                                    <li><a class="dropdown-item py-2 {{ old('waktu_sholat') == 'Ashar' ? 'active' : '' }}" href="javascript:void(0)" onclick="selectWaktuSholat('Ashar', 'Ashar')">Ashar</a></li>
                                    <li><a class="dropdown-item py-2 {{ old('waktu_sholat') == 'Maghrib' ? 'active' : '' }}" href="javascript:void(0)" onclick="selectWaktuSholat('Maghrib', 'Maghrib')">Maghrib</a></li>
                                    <li><a class="dropdown-item py-2 {{ old('waktu_sholat') == 'Isya' ? 'active' : '' }}" href="javascript:void(0)" onclick="selectWaktuSholat('Isya', 'Isya')">Isya</a></li>
                                </ul>
                                <input type="hidden" name="waktu_sholat" id="waktu_sholat_input" value="{{ old('waktu_sholat', 'Full Day') }}">
                            </div>
                            @error('waktu_sholat')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6" id="durasi_container">
                            <label for="durasi_hari" class="form-label fw-semibold">Jumlah Hari</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-clock-history"></i></span>
                                <input type="number" name="durasi_hari" id="durasi_hari" class="form-control border-start-0 @error('durasi_hari') is-invalid @enderror" value="{{ old('durasi_hari', 1) }}" min="1" oninput="calculateEndDate()">
                                <span class="input-group-text bg-white border-start-0 text-muted">Hari</span>
                            </div>
                            @error('durasi_hari')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_mulai" class="form-label fw-semibold">Tanggal Mulai</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control border-start-0 @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required onchange="calculateEndDate()">
                            </div>
                            @error('tanggal_mulai')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_selesai" class="form-label fw-semibold">Tanggal Selesai</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control border-start-0 @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai', date('Y-m-d')) }}" required readonly>
                            </div>
                            <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i> Otomatis terhitung dari jumlah hari</small>
                            @error('tanggal_selesai')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="keterangan" class="form-label fw-semibold">Keterangan / Alasan</label>
                            <textarea name="keterangan" id="keterangan" rows="4" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Jelaskan alasan izin Anda secara detail..." required>{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="lampiran" class="form-label fw-semibold">Lampiran (Opsional)</label>
                            <input type="file" name="lampiran" id="lampiran" class="form-control @error('lampiran') is-invalid @enderror">
                            <div class="form-text mt-2">
                                <i class="bi bi-info-circle me-1"></i> Format: PDF, JPG, PNG (Max: 2MB). Unggah surat keterangan dokter atau bukti kegiatan.
                            </div>
                            @error('lampiran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4 pt-3 border-top justify-content-end">
                        <button type="button" class="btn btn-light px-4 rounded-3 fw-semibold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success px-4 rounded-3 fw-semibold">
                            <i class="bi bi-send-fill me-2"></i> Ajukan Izin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function selectJenisIzin(val, text) {
        document.getElementById('jenis_izin_input').value = val;
        document.getElementById('selected-jenis-text').innerText = text;
        
        const items = document.querySelectorAll('#jenisIzinDropdown + .dropdown-menu .dropdown-item');
        items.forEach(item => {
            if (item.innerText === text) item.classList.add('active');
            else item.classList.remove('active');
        });
    }

    function selectWaktuSholat(val, text) {
        document.getElementById('waktu_sholat_input').value = val;
        document.getElementById('selected-sholat-text').innerText = text;
        
        const items = document.querySelectorAll('#waktuSholatDropdown + .dropdown-menu .dropdown-item');
        items.forEach(item => {
            if (item.innerText === text) item.classList.add('active');
            else item.classList.remove('active');
        });

        // If not Full Day, set durasi to 1 and potentially hide/disable it
        if (val !== 'Full Day') {
            document.getElementById('durasi_hari').value = 1;
            document.getElementById('durasi_hari').readOnly = true;
            document.getElementById('durasi_container').style.opacity = '0.7';
        } else {
            document.getElementById('durasi_hari').readOnly = false;
            document.getElementById('durasi_container').style.opacity = '1';
        }
        calculateEndDate();
    }

    function calculateEndDate() {
        const startDate = document.getElementById('tanggal_mulai').value;
        const duration = parseInt(document.getElementById('durasi_hari').value) || 1;
        
        if (startDate) {
            const date = new Date(startDate);
            date.setDate(date.getDate() + (duration - 1));
            
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            
            document.getElementById('tanggal_selesai').value = `${year}-${month}-${day}`;
        }
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        calculateEndDate();
        const currentWaktu = document.getElementById('waktu_sholat_input').value;
        if (currentWaktu !== 'Full Day') {
            document.getElementById('durasi_hari').readOnly = true;
            document.getElementById('durasi_container').style.opacity = '0.7';
        }
    });
</script>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var createModal = new bootstrap.Modal(document.getElementById('createIzinModal'));
        createModal.show();
    });
</script>
@endif

<style>
    .x-small { font-size: 0.75rem; }
    .bg-danger-subtle { background-color: rgba(239, 68, 68, 0.1); }
    .bg-info-subtle { background-color: rgba(58, 176, 255, 0.1); }
    .bg-warning-subtle { background-color: rgba(245, 158, 11, 0.1); }
    .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
</style>
@endsection
