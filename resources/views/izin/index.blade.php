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
            <a href="{{ route('izin.create') }}" class="btn btn-success rounded-3 px-4 fw-semibold shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Ajukan Izin Baru
            </a>
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
                                    <div class="text-muted x-small">
                                        @php
                                            $diff = $izin->tanggal_mulai->diffInDays($izin->tanggal_selesai) + 1;
                                        @endphp
                                        {{ $diff }} Hari
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
                                                        <div class="col-6">
                                                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Mulai</label>
                                                            <p class="mb-0 fw-medium">{{ $izin->tanggal_mulai->format('l, d M Y') }}</p>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Selesai</label>
                                                            <p class="mb-0 fw-medium">{{ $izin->tanggal_selesai->format('l, d M Y') }}</p>
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
                                        <a href="{{ route('izin.create') }}" class="btn btn-sm btn-outline-success mt-3 rounded-pill px-3">
                                            Ajukan Sekarang
                                        </a>
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
</style>
@endsection
