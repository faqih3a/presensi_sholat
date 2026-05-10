@extends('layouts.app')

@section('title', 'Edit Data Santri')

@push('styles')
<style>
    .card-edit {
        border-radius: 1.25rem;
        border: 1px solid #edf2f9;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .preview-container {
        max-width: 400px;
        margin: 0 auto;
        position: relative;
        border-radius: 1rem;
        overflow: hidden;
        background-color: #f8f9fa;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px dashed #dee2e6;
    }
    #image-preview {
        width: 100%;
        display: block;
    }
    canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    .btn-gradient-success {
        background: linear-gradient(310deg, #198754 0%, #2dc57b 100%);
        border: none;
        color: #fff;
        box-shadow: 0 4px 7px -1px rgba(0,0,0,0.11), 0 2px 4px -1px rgba(0,0,0,0.07);
        transition: all 0.15s ease-in;
    }
    .btn-gradient-success:hover {
        transform: translateY(-1px);
        color: #fff;
        box-shadow: 0 7px 14px rgba(0,0,0,0.1);
    }
    .form-control, .form-select {
        border: 1px solid #edf2f9;
        border-radius: 0.75rem;
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.1);
    }
    body.dark-mode .card-edit {
        background-color: #1e1e1e;
        border-color: #333;
    }
    body.dark-mode .preview-container {
        background-color: #2c2c2c;
        border-color: #444;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-7">
        <div class="mb-4">
            <a href="{{ route('santri.index') }}" class="btn btn-link text-muted text-decoration-none p-0 fw-bold small">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Santri
            </a>
        </div>

        <div class="card card-edit border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 pt-5 pb-0 text-center">
                <div class="bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-person-gear fs-2 text-success"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Edit Profil Santri</h3>
                <p class="text-muted small px-4">Perbarui informasi dasar dan identitas wajah santri untuk akurasi presensi yang lebih baik.</p>
            </div>
            <div class="card-body p-4 p-md-5 pt-4">
                
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 0.75rem;">
                        <ul class="mb-0 small fw-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div id="alert-container"></div>

                <form id="edit-form" action="{{ route('santri.update', $santri) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label for="nama" class="form-label fw-bold small text-muted text-uppercase">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $santri->nama) }}" required placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="col-md-4">
                            <label for="kelas" class="form-label fw-bold small text-muted text-uppercase">Kelas</label>
                            <select class="form-select" id="kelas" name="kelas" required>
                                <option value="7 MTs" {{ $santri->kelas == '7 MTs' ? 'selected' : '' }}>7 MTs</option>
                                <option value="8 MTs" {{ $santri->kelas == '8 MTs' ? 'selected' : '' }}>8 MTs</option>
                                <option value="9 MTs" {{ $santri->kelas == '9 MTs' ? 'selected' : '' }}>9 MTs</option>
                                <option value="10 MA" {{ $santri->kelas == '10 MA' ? 'selected' : '' }}>10 MA</option>
                                <option value="11 MA" {{ $santri->kelas == '11 MA' ? 'selected' : '' }}>11 MA</option>
                                <option value="12 MA" {{ $santri->kelas == '12 MA' ? 'selected' : '' }}>12 MA</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="foto_referensi" class="form-label fw-bold small text-muted text-uppercase">Ganti Foto Wajah (Opsional)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-camera text-muted"></i></span>
                            <input type="file" class="form-control border-start-0" id="foto_referensi" name="foto_referensi" accept="image/jpeg, image/png, image/jpg">
                        </div>
                        <div class="form-text small mt-2">Kosongkan jika tidak ingin mengubah foto. Jika diganti, sistem akan memproses ulang wajah.</div>
                    </div>

                    <div class="mb-4">
                        <div class="preview-container shadow-sm" id="preview-wrapper">
                            @if($santri->foto_referensi)
                                <img id="image-preview" src="{{ asset('storage/santri_fotos/' . $santri->foto_referensi) }}" alt="Foto Saat Ini" />
                            @else
                                <img id="image-preview" src="#" alt="Preview" style="display: none;" />
                                <div id="no-photo" class="text-center text-muted small">Belum ada foto referensi</div>
                            @endif
                        </div>
                        <div id="extraction-status" class="text-center mt-3 small d-none"></div>
                    </div>

                    <input type="hidden" id="face_descriptor" name="face_descriptor">

                    <div class="row g-2 mt-5">
                        <div class="col-sm-6 text-sm-end">
                            <button type="submit" id="submit-btn" class="btn btn-gradient-success px-4 fw-bold">
                                <i class="bi bi-check-circle-fill me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                        <div class="col-sm-6 text-sm-start">
                            <a href="{{ route('santri.index') }}" class="btn btn-light px-4 fw-bold text-muted border">Batal</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
    const fotoInput = document.getElementById('foto_referensi');
    const imagePreview = document.getElementById('image-preview');
    const previewWrapper = document.getElementById('preview-wrapper');
    const extractionStatus = document.getElementById('extraction-status');
    const submitBtn = document.getElementById('submit-btn');
    const faceDescriptorInput = document.getElementById('face_descriptor');
    const alertContainer = document.getElementById('alert-container');
    const form = document.getElementById('edit-form');
    
    let isModelLoaded = false;
    let currentCanvas = null;

    async function loadModels() {
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/models')
            ]);
            isModelLoaded = true;
        } catch (error) {
            console.error('Error loading models:', error);
            showAlert('danger', 'Gagal memuat sistem AI. Fitur deteksi wajah belum siap.');
        }
    }

    loadModels();

    function showAlert(type, message) {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 0.75rem;">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <div class="small fw-medium">${message}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    fotoInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        if (!isModelLoaded) {
            showAlert('warning', 'Sistem AI sedang bersiap, harap tunggu sejenak.');
            fotoInput.value = '';
            return;
        }

        submitBtn.disabled = true;
        faceDescriptorInput.value = '';
        if (currentCanvas) currentCanvas.remove();

        const imgUrl = URL.createObjectURL(file);
        imagePreview.src = imgUrl;
        imagePreview.style.display = 'block';
        
        extractionStatus.classList.remove('d-none');
        extractionStatus.innerHTML = '<span class="spinner-border spinner-border-sm me-2 text-success"></span>Menganalisis wajah baru...';
        extractionStatus.className = 'text-muted mt-2 small fw-medium';

        imagePreview.onload = async () => {
            if(imagePreview.src.startsWith('blob:')) {
                try {
                    const detection = await faceapi.detectSingleFace(imagePreview).withFaceLandmarks().withFaceDescriptor();

                    if (!detection) {
                        throw new Error('Wajah tidak ditemukan. Gunakan foto yang lebih jelas.');
                    }

                    const canvas = faceapi.createCanvasFromMedia(imagePreview);
                    currentCanvas = canvas;
                    previewWrapper.appendChild(canvas);
                    
                    const displaySize = { width: imagePreview.clientWidth, height: imagePreview.clientHeight };
                    faceapi.matchDimensions(canvas, displaySize);
                    const resizedDetections = faceapi.resizeResults(detection, displaySize);
                    
                    const box = resizedDetections.detection.box;
                    const drawBox = new faceapi.draw.DrawBox(box, { label: 'Wajah Terdeteksi', boxColor: '#198754' });
                    drawBox.draw(canvas);

                    faceDescriptorInput.value = JSON.stringify(Array.from(detection.descriptor));

                    extractionStatus.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> Wajah baru berhasil diverifikasi!';
                    extractionStatus.className = 'text-success mt-2 small fw-bold';
                    submitBtn.disabled = false;

                } catch (error) {
                    extractionStatus.innerHTML = `<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> ${error.message}`;
                    extractionStatus.className = 'text-danger mt-2 small fw-bold';
                    fotoInput.value = '';
                    submitBtn.disabled = false; // allow submitting without photo change
                }
            }
        };
    });
    
    form.addEventListener('submit', function(e) {
        if(fotoInput.files.length > 0 && !faceDescriptorInput.value) {
            e.preventDefault();
            showAlert('warning', 'Harap tunggu hingga proses deteksi wajah selesai.');
        }
    });
</script>
@endpush
