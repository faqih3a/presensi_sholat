<!-- Modal Edit Status -->
<div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-white border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark" id="editStatusModalLabel">Edit Status Kehadiran</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('presensi.update-status') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="santri_id" id="edit_santri_id">
                <input type="hidden" name="tanggal" id="edit_tanggal">
                <input type="hidden" name="waktu_sholat" id="edit_waktu_sholat">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Status Kehadiran</label>
                        <select name="status" id="edit_status" class="form-select border-light bg-light rounded-3">
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Alfa">Alpha</option>
                        </select>
                        <div class="form-text small mt-2">
                            Mengubah status menjadi <strong>Hadir</strong> akan mencatat waktu kehadiran saat ini.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-white btn-sm px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deletePresensiForm" action="{{ route('presensi.delete-by-params') }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
    <input type="hidden" name="santri_id" id="delete_santri_id">
    <input type="hidden" name="tanggal" id="delete_tanggal">
    <input type="hidden" name="waktu_sholat" id="delete_waktu_sholat">
</form>

@push('scripts')
<script>
    function editStatus(santriId, tanggal, waktuSholat, currentStatus) {
        document.getElementById('edit_santri_id').value = santriId;
        document.getElementById('edit_tanggal').value = tanggal;
        document.getElementById('edit_waktu_sholat').value = waktuSholat;
        document.getElementById('edit_status').value = currentStatus;
        
        var editModal = new bootstrap.Modal(document.getElementById('editStatusModal'));
        editModal.show();
    }

    function deletePresensi(santriId, tanggal, waktuSholat) {
        if (confirm('Hapus data presensi ini?')) {
            document.getElementById('delete_santri_id').value = santriId;
            document.getElementById('delete_tanggal').value = tanggal;
            document.getElementById('delete_waktu_sholat').value = waktuSholat;
            document.getElementById('deletePresensiForm').submit();
        }
    }
</script>
@endpush
