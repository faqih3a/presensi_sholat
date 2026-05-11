@extends('layouts.app')

@section('title', 'Kelola Izin Santri')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Kelola Izin / Sakit</h4>
                <p class="text-muted mb-0">Tinjau dan proses pengajuan izin dari santri</p>
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
                                        @if($izin->user->role === 'santri' && $izin->user->santri && $izin->user->santri->foto_referensi)
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
                                    <div class="x-small text-muted">
                                        {{ $izin->tanggal_mulai->diffInDays($izin->tanggal_selesai) + 1 }} Hari
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
</script>
@endsection
