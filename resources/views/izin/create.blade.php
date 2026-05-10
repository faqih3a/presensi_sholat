@extends('layouts.app')

@section('title', 'Ajukan Izin')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success-subtle p-2 rounded-3 me-3">
                        <i class="bi bi-file-earmark-plus-fill text-success fs-4"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0 fw-bold">Pengajuan Izin / Sakit</h5>
                        <small class="text-muted">Silakan lengkapi form berikut untuk mengajukan izin</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

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

                        <script>
                            function selectJenisIzin(val, text) {
                                document.getElementById('jenis_izin_input').value = val;
                                document.getElementById('selected-jenis-text').innerText = text;
                                
                                // Update active state in dropdown
                                const items = document.querySelectorAll('.premium-select-wrapper .dropdown-item');
                                items.forEach(item => {
                                    if (item.innerText === text) {
                                        item.classList.add('active');
                                    } else {
                                        item.classList.remove('active');
                                    }
                                });
                            }
                        </script>

                        <div class="col-md-6">
                            <label for="tanggal_mulai" class="form-label fw-semibold">Tanggal Mulai</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control border-start-0 @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                            </div>
                            @error('tanggal_mulai')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_selesai" class="form-label fw-semibold">Tanggal Selesai</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control border-start-0 @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai', date('Y-m-d')) }}" required>
                            </div>
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

                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-success px-4 rounded-3 fw-semibold">
                            <i class="bi bi-send-fill me-2"></i> Ajukan Izin
                        </button>
                        <a href="{{ route('izin.index') }}" class="btn btn-light px-4 rounded-3 fw-semibold">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
