@extends('layouts.app')

@section('title', 'Data Santri')

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
    .avatar-sm {
        width: 48px;
        height: 48px;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .badge-soft-success {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.2);
    }
    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }
    .action-btn:hover {
        transform: translateY(-2px);
    }
    
    /* Registration Modal Styles */
    .preview-container {
        max-width: 100%;
        margin: 0 auto;
        position: relative;
        border-radius: 0.75rem;
        overflow: hidden;
        background: #f8f9fa;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #image-preview {
        width: 100%;
        display: none;
        border-radius: 0.75rem;
    }
    .preview-container canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    
    body.dark-mode .table td, body.dark-mode .table th {
        border-bottom-color: #333;
    }
    body.dark-mode .table-light {
        background-color: #2c2c2c !important;
    }
    body.dark-mode .avatar-sm {
        border-color: #444;
    }
    body.dark-mode .preview-container {
        background: #2c2c2c;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1 text-dark fw-bold">Data Santri</h1>
            <p class="text-muted small mb-0">Kelola daftar santri yang terdaftar dalam sistem presensi wajah.</p>
        </div>
        <button type="button" class="btn btn-gradient-success px-4 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#registerModal">
            <i class="bi bi-person-plus-fill me-2"></i> Tambah Santri
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 0.75rem;">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card card-stats overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="m-0 fw-bold text-dark"><i class="bi bi-people-fill text-success me-2"></i>Daftar Santri</h6>
            <div class="small text-muted">{{ count($santris) }} Santri Terdaftar</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" width="80">No</th>
                            <th>Foto</th>
                            <th>Nama Lengkap</th>
                            <th>Kelas</th>
                            <th>Waktu Daftar</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($santris as $index => $santri)
                        <tr>
                            <td class="text-center">
                                <span class="text-muted fw-bold small">{{ $index + 1 }}</span>
                            </td>
                            <td>
                                @if($santri->foto_referensi)
                                    <img src="{{ asset('storage/santri_fotos/' . $santri->foto_referensi) }}" alt="{{ $santri->nama }}" class="avatar-sm rounded-circle object-fit-cover">
                                @else
                                    <div class="avatar-sm bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center border">
                                        <i class="bi bi-person fs-5"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $santri->nama }}</div>
                                <div class="small text-muted">{{ $santri->user->email ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-soft-success px-3 py-2 rounded-pill fw-bold" style="font-size: 0.7rem;">
                                    {{ $santri->kelas }}
                                </span>
                            </td>
                            <td>
                                <div class="text-dark small">{{ $santri->created_at->format('d M Y') }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $santri->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('santri.edit', $santri) }}" class="action-btn bg-info bg-opacity-10 text-info" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('santri.destroy', $santri) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data santri ini? Semua data presensi terkait juga akan dihapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn bg-danger bg-opacity-10 text-danger border-0" title="Hapus">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-person-exclamation text-muted" style="font-size: 4rem;"></i>
                                    <h5 class="mt-3 fw-bold text-dark">Belum Ada Data</h5>
                                    <p class="text-muted mb-4">Silakan daftarkan santri baru untuk mulai menggunakan sistem presensi.</p>
                                    <button type="button" class="btn btn-gradient-success px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#registerModal">
                                        <i class="bi bi-plus-lg me-1"></i> Daftarkan Santri Pertama
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(count($santris) > 0)
        <div class="card-footer bg-white border-top py-3">
            <div class="small text-muted">
                Menampilkan 1 sampai {{ count($santris) }} dari {{ count($santris) }} data santri.
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal Registrasi Santri -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem;">
            <div class="modal-header bg-white border-bottom-0 pb-0 pt-4 px-4">
                <div class="text-center w-100">
                    <h4 class="fw-bold text-dark mb-1" id="registerModalLabel">
                        <i class="bi bi-person-plus-fill me-2 text-success"></i>Registrasi Santri Baru
                    </h4>
                    <p class="text-muted small">Lengkapi data di bawah untuk mendaftarkan santri ke sistem.</p>
                </div>
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="modal-alert-container"></div>
                
                <form id="register-form" action="{{ route('santri.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama" class="form-label fw-bold small text-muted text-uppercase">Nama Lengkap</label>
                                <input type="text" class="form-control py-2" id="nama" name="nama" required placeholder="Ahmad Al-Faqih">
                            </div>
                            <div class="mb-3">
                                <label for="kelas" class="form-label fw-bold small text-muted text-uppercase">Kelas</label>
                                <select class="form-select py-2" id="kelas" name="kelas" required>
                                    <option value="" selected disabled>-- Pilih Kelas --</option>
                                    <option value="7 MTs">7 MTs</option>
                                    <option value="8 MTs">8 MTs</option>
                                    <option value="9 MTs">9 MTs</option>
                                    <option value="10 MA">10 MA</option>
                                    <option value="11 MA">11 MA</option>
                                    <option value="12 MA">12 MA</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold small text-muted text-uppercase">Email</label>
                                <input type="email" class="form-control py-2" id="email" name="email" required placeholder="santri@thursina.id">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold small text-muted text-uppercase">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control py-2" id="password" name="password" required minlength="5">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="foto_referensi" class="form-label fw-bold small text-muted text-uppercase">Foto Referensi Wajah</label>
                                <input type="file" class="form-control py-2" id="foto_referensi" name="foto_referensi" accept="image/jpeg, image/png, image/jpg" required>
                            </div>
                            <div class="preview-container mb-2" id="preview-wrapper">
                                <img id="image-preview" src="#" alt="Preview" />
                                <div id="preview-placeholder" class="text-center text-muted">
                                    <i class="bi bi-camera fs-1 d-block mb-2"></i>
                                    <span class="small">Belum ada foto dipilih</span>
                                </div>
                            </div>
                            <div id="extraction-status" class="text-center mt-2 small d-none"></div>
                        </div>
                    </div>

                    <input type="hidden" id="face_descriptor" name="face_descriptor" required>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" id="submit-btn" class="btn btn-gradient-success flex-grow-1 py-2 fw-bold" disabled>
                            <i class="bi bi-check-circle-fill me-2"></i>Simpan Data Santri
                        </button>
                        <button type="button" class="btn btn-light px-4 py-2 fw-bold text-muted" data-bs-dismiss="modal">Batal</button>
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
    const previewPlaceholder = document.getElementById('preview-placeholder');
    const extractionStatus = document.getElementById('extraction-status');
    const submitBtn = document.getElementById('submit-btn');
    const faceDescriptorInput = document.getElementById('face_descriptor');
    const form = document.getElementById('register-form');
    const alertContainer = document.getElementById('modal-alert-container');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const registerModal = document.getElementById('registerModal');
    
    let isModelLoaded = false;
    let currentCanvas = null;

    // Load AI Models
    async function loadModels() {
        try {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/models')
            ]);
            isModelLoaded = true;
            console.log('AI Models Loaded successfully');
        } catch (error) {
            console.error('Error loading models:', error);
            showAlert('danger', 'Gagal memuat AI Model. Pastikan folder /models tersedia.');
        }
    }

    loadModels();

    function showAlert(type, message) {
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show border-0" role="alert" style="border-radius: 0.75rem;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
    }

    // Reset Modal on Close
    registerModal.addEventListener('hidden.bs.modal', function () {
        form.reset();
        imagePreview.style.display = 'none';
        imagePreview.src = '#';
        previewPlaceholder.classList.remove('d-none');
        if (currentCanvas) {
            currentCanvas.remove();
            currentCanvas = null;
        }
        extractionStatus.classList.add('d-none');
        extractionStatus.innerHTML = '';
        faceDescriptorInput.value = '';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Simpan Data Santri';
        alertContainer.innerHTML = '';
        passwordInput.setAttribute('type', 'password');
        togglePassword.innerHTML = '<i class="bi bi-eye"></i>';
    });

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    });

    fotoInput.addEventListener('change', async (e) => {
        if (!isModelLoaded) {
            showAlert('warning', 'Model AI masih dalam proses pemuatan, silakan tunggu beberapa saat.');
            fotoInput.value = '';
            return;
        }

        const file = e.target.files[0];
        if (!file) return;

        // Reset state
        submitBtn.disabled = true;
        faceDescriptorInput.value = '';
        if (currentCanvas) {
            currentCanvas.remove();
            currentCanvas = null;
        }

        // Show image preview
        const imgUrl = URL.createObjectURL(file);
        imagePreview.src = imgUrl;
        imagePreview.style.display = 'block';
        previewPlaceholder.classList.add('d-none');
        
        extractionStatus.classList.remove('d-none');
        extractionStatus.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses wajah...';
        extractionStatus.className = 'text-muted mt-2 small';

        imagePreview.onload = async () => {
            try {
                const detection = await faceapi.detectSingleFace(imagePreview).withFaceLandmarks().withFaceDescriptor();

                if (!detection) {
                    throw new Error('Wajah tidak terdeteksi. Gunakan foto yang lebih jelas.');
                }

                const canvas = faceapi.createCanvasFromMedia(imagePreview);
                currentCanvas = canvas;
                previewWrapper.appendChild(canvas);
                
                const displaySize = { width: imagePreview.clientWidth, height: imagePreview.clientHeight };
                faceapi.matchDimensions(canvas, displaySize);
                const resizedDetections = faceapi.resizeResults(detection, displaySize);
                
                const box = resizedDetections.detection.box;
                const drawBox = new faceapi.draw.DrawBox(box, { label: 'Terdeteksi', boxColor: '#198754' });
                drawBox.draw(canvas);

                const descriptorArray = Array.from(detection.descriptor);
                faceDescriptorInput.value = JSON.stringify(descriptorArray);

                extractionStatus.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> Wajah terdeteksi!';
                extractionStatus.className = 'text-success mt-2 small fw-bold';
                submitBtn.disabled = false;

            } catch (error) {
                console.error(error);
                extractionStatus.innerHTML = `<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> ${error.message}`;
                extractionStatus.className = 'text-danger mt-2 small fw-bold';
                fotoInput.value = '';
                previewPlaceholder.classList.remove('d-none');
                imagePreview.style.display = 'none';
            }
        };
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (submitBtn.disabled) return;

        const formData = new FormData(form);
        submitBtn.disabled = true;
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok) {
                // Success: Refresh page to see new data or close modal and update table
                location.reload(); 
            } else {
                let errorMsg = result.message || 'Gagal menyimpan data.';
                if (result.errors) {
                    errorMsg += '<ul class="mb-0 mt-2">';
                    for (const key in result.errors) {
                        errorMsg += `<li>${result.errors[key][0]}</li>`;
                    }
                    errorMsg += '</ul>';
                }
                showAlert('danger', errorMsg);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        } catch (error) {
            console.error(error);
            showAlert('danger', 'Terjadi kesalahan sistem.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
</script>
@endpush
