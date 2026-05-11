@extends('layouts.app')

@section('title', 'Scan Presensi')

@push('styles')
<style>
    .scanner-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2rem;
        max-width: 800px;
        margin: 0 auto;
    }

    .video-wrapper {
        position: relative;
        width: 100%;
        max-width: 640px;
        border-radius: 1.5rem;
        overflow: hidden;
        background: #000;
        aspect-ratio: 4/3;
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.2);
        border: 4px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    #video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .scan-line {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, transparent, #198754, transparent);
        box-shadow: 0 0 15px rgba(25, 135, 84, 0.8);
        animation: scan 2.5s ease-in-out infinite;
        z-index: 5;
        opacity: 0.8;
    }

    @keyframes scan {
        0% { top: 5%; }
        50% { top: 95%; }
        100% { top: 5%; }
    }

    /* Custom Radio Buttons */
    .sholat-option {
        position: relative;
        cursor: pointer;
    }
    
    .sholat-option input {
        display: none;
    }
    
    .sholat-option span {
        display: block;
        padding: 0.75rem 1.5rem;
        border-radius: 0.75rem;
        border: 2px solid #edf2f9;
        color: #67748e;
        font-weight: 600;
        transition: all 0.2s ease;
        background: #fff;
        text-align: center;
        min-width: 100px;
    }
    
    .sholat-option input:checked + span {
        background: linear-gradient(310deg, #198754 0%, #2dc57b 100%);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transform: translateY(-2px);
    }
    
    .sholat-option:hover span {
        border-color: #198754;
        color: #198754;
    }
    
    .sholat-option input:checked:hover span {
        color: #fff;
    }

    .btn-gradient-success {
        background: linear-gradient(310deg, #198754 0%, #2dc57b 100%);
        border: none;
        color: #fff;
        box-shadow: 0 4px 7px -1px rgba(0,0,0,0.11), 0 2px 4px -1px rgba(0,0,0,0.07);
        transition: all 0.15s ease-in;
    }
    
    .btn-gradient-success:hover:not(:disabled) {
        transform: scale(1.02);
        box-shadow: 0 4px 7px -1px rgba(0,0,0,0.11), 0 2px 4px -1px rgba(0,0,0,0.07);
        color: #fff;
    }

    .btn-gradient-success:active {
        transform: scale(0.98);
    }

    .btn-gradient-success:disabled {
        background: #e9ecef;
        color: #adb5bd;
    }

    /* Dark Mode Adjustments */
    body.dark-mode .sholat-option span {
        background: #2c2c2c;
        border-color: #444;
        color: #adb5bd;
    }
    
    body.dark-mode .sholat-option input:checked + span {
        background: linear-gradient(310deg, #198754 0%, #2dc57b 100%);
        color: #fff;
    }

    body.dark-mode .video-wrapper {
        border-color: rgba(255, 255, 255, 0.05);
    }
</style>
@endpush

@section('content')
@php
    $sholatList = ['Subuh', 'Dzuhur', 'Ashar', 'Maghrib', 'Isya'];
    $jadwalInfo = [];
    
    if ($jadwal) {
        $getStart = function($timeStr) {
            try {
                return \Carbon\Carbon::createFromFormat('H:i', $timeStr)->subMinutes(30)->format('H:i');
            } catch (\Exception $e) {
                return $timeStr;
            }
        };

        $apiTimes = [
            'Subuh' => $jadwal['Fajr'],
            'Dzuhur' => $jadwal['Dhuhr'],
            'Ashar' => $jadwal['Asr'],
            'Maghrib' => $jadwal['Maghrib'],
            'Isya' => $jadwal['Isha'],
        ];

        foreach ($sholatList as $sholat) {
            $start = $getStart($apiTimes[$sholat]);
            $end = match($sholat) {
                'Subuh' => $apiTimes['Dzuhur'],
                'Dzuhur' => $apiTimes['Ashar'],
                'Ashar' => $apiTimes['Maghrib'],
                'Maghrib' => $apiTimes['Isya'],
                'Isya' => $getStart($apiTimes['Subuh']), // Ends 30 mins before next Subuh
            };
            $jadwalInfo[$sholat] = ['start' => $start, 'end' => $end];
        }
    }
@endphp
<div class="scanner-container py-4">
    <div class="text-center mb-2">
        <h1 class="h2 fw-bold text-dark">Kamera Presensi Sholat</h1>
        <p class="text-muted">Pilih waktu sholat terlebih dahulu, lalu arahkan wajah Anda ke kamera.</p>
    </div>

    <!-- Pilihan Waktu Sholat -->
    <div class="card shadow-sm border-0 w-100 mb-4" id="sholat-selector" style="max-width: 640px; border-radius: 1.25rem;">
        <div class="card-body p-4 p-md-5">
            <h5 class="card-title fw-bold text-center mb-4 text-dark">Pilih Waktu Sholat</h5>
            <div class="d-flex flex-wrap justify-content-center gap-3 mb-5">
                @foreach($sholatList as $sholat)
                <label class="sholat-option">
                    <input type="radio" name="waktu_sholat" value="{{ $sholat }}" {{ $suggestedSholat == $sholat ? 'checked' : '' }}>
                    <span class="d-flex flex-column align-items-center py-2 px-3">
                        <span class="sholat-name">{{ $sholat }}</span>
                        @if(isset($jadwalInfo[$sholat]))
                            <span class="sholat-time x-small opacity-75 fw-normal mt-1" style="font-size: 0.7rem;">
                                {{ $jadwalInfo[$sholat]['start'] }} - {{ $jadwalInfo[$sholat]['end'] }}
                            </span>
                        @endif
                    </span>
                </label>
                @endforeach
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-gradient-success btn-lg fw-bold py-3" id="btn-mulai-presensi" disabled style="border-radius: 1rem;">
                    <i class="bi bi-camera-fill me-2"></i>Mulai Presensi
                </button>
            </div>
        </div>
    </div>

    <!-- Area Kamera (Awalnya Disembunyikan) -->
    <div id="camera-area" class="w-100 d-none" style="max-width: 640px;">
        <div class="video-wrapper mb-4" id="video-wrapper">
            <video id="video" autoplay muted playsinline disablePictureInPicture></video>
            <div class="scan-line" id="scan-line"></div>
        </div>

        <div class="card shadow-sm border-0 w-100" style="border-radius: 1.25rem;">
            <div class="card-body text-center p-4">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold" id="selected-sholat-badge">
                        <i class="bi bi-clock-fill me-1"></i> Waktu: -
                    </span>
                </div>
                
                <h4 class="text-dark fw-bold mb-2" id="status-title">
                    <span class="spinner-border spinner-border-sm me-2 text-success" role="status" aria-hidden="true"></span>
                    Memuat Sistem...
                </h4>
                <p class="text-muted mb-0" id="status-desc">Sedang mengunduh model AI dan data wajah santri.</p>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <button class="btn btn-link text-muted text-decoration-none fw-semibold" id="btn-batal-presensi">
                <i class="bi bi-arrow-left me-1"></i> Kembali / Pilih Ulang
            </button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="liveToast" class="toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 1rem;">
        <div class="d-flex p-2">
            <div class="toast-body d-flex flex-column" id="toast-body-content">
                <!-- Content injected via JS -->
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/face-api.min.js') }}"></script>
<script>
    const video = document.getElementById('video');
    const container = document.getElementById('video-wrapper');
    const statusTitle = document.getElementById('status-title');
    const statusDesc = document.getElementById('status-desc');
    const toastEl = document.getElementById('liveToast');
    const toastBody = document.getElementById('toast-body-content');
    const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
    
    let faceMatcher = null;
    let labeledFaceDescriptors = [];
    const cooldowns = new Map(); // Untuk mencegah spam request
    let selectedWaktuSholat = null;
    let isModelsLoaded = false;
    let scanInterval = null;

    const sholatSelector = document.getElementById('sholat-selector');
    const cameraArea = document.getElementById('camera-area');
    const btnMulai = document.getElementById('btn-mulai-presensi');
    const btnBatal = document.getElementById('btn-batal-presensi');
    const radioButtons = document.querySelectorAll('input[name="waktu_sholat"]');
    const selectedBadge = document.getElementById('selected-sholat-badge');

    function showNotification(title, msg, type = 'success') {
        toastEl.className = `toast align-items-center text-white border-0 bg-${type}`;
        toastBody.innerHTML = `<strong class="me-auto fs-6 mb-1">${title}</strong><span>${msg}</span>`;
        bsToast.show();
    }

    // Event listener untuk pilihan sholat
    radioButtons.forEach(radio => {
        radio.addEventListener('change', (e) => {
            selectedWaktuSholat = e.target.value;
            btnMulai.disabled = false;
        });
    });
    
    // Auto-select if already checked (from backend suggestion)
    const checkedRadio = document.querySelector('input[name="waktu_sholat"]:checked');
    if (checkedRadio) {
        selectedWaktuSholat = checkedRadio.value;
        btnMulai.disabled = false;
    }

    btnMulai.addEventListener('click', () => {
        sholatSelector.classList.add('d-none');
        cameraArea.classList.remove('d-none');
        selectedBadge.textContent = `Sholat: ${selectedWaktuSholat}`;
        
        if (!isModelsLoaded) {
            loadDataAndModels();
        } else {
            startVideo();
        }
    });

    btnBatal.addEventListener('click', () => {
        // Hentikan kamera
        if (video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
        if (scanInterval) {
            clearInterval(scanInterval);
        }
        // Bersihkan canvas
        const canvas = container.querySelector('canvas');
        if (canvas) {
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
            canvas.remove();
        }
        
        cameraArea.classList.add('d-none');
        sholatSelector.classList.remove('d-none');
        
        // Reset status
        statusTitle.className = 'text-success fw-bold mb-2';
        statusTitle.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Sistem Aktif';
        statusDesc.textContent = 'Silakan pilih waktu sholat dan mulai presensi.';
    });

    async function loadDataAndModels() {
        try {
            // 1. Load Models
            statusDesc.textContent = 'Memuat AI Models...';
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/models')
            ]);

            // 2. Load Santri Data
            statusDesc.textContent = 'Memuat Data Santri...';
            const response = await fetch('/api/santris');
            const santris = await response.json();

            if(santris.length === 0) {
                statusTitle.className = 'text-danger fw-bold mb-2';
                statusTitle.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Tidak Ada Data';
                statusDesc.textContent = 'Belum ada santri terdaftar di sistem.';
                return;
            }

            // Convert to LabeledFaceDescriptors
            labeledFaceDescriptors = santris.map(santri => {
                const descriptorArray = JSON.parse(santri.face_descriptor);
                const float32Array = new Float32Array(descriptorArray);
                // Kita gunakan santri_id sebagai label
                return new faceapi.LabeledFaceDescriptors(
                    santri.id.toString(), 
                    [float32Array]
                );
            });

            faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6); // Threshold 0.6

            statusTitle.className = 'text-success fw-bold mb-2';
            statusTitle.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Sistem Aktif';
            statusDesc.textContent = `${santris.length} wajah santri berhasil dimuat. Silakan menghadap kamera.`;
            isModelsLoaded = true;
            
            startVideo();

        } catch (error) {
            console.error(error);
            statusTitle.className = 'text-danger fw-bold mb-2';
            statusTitle.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i>Terjadi Kesalahan';
            statusDesc.textContent = 'Gagal memuat sistem. Cek koneksi atau reload halaman.';
        }
    }

    function startVideo() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            statusTitle.className = 'text-danger fw-bold mb-2';
            statusTitle.innerHTML = '<i class="bi bi-shield-lock-fill me-2"></i>Akses Tidak Aman';
            statusDesc.textContent = 'Browser memblokir akses kamera. Pastikan menggunakan HTTPS atau localhost.';
            return;
        }

        const constraints = { 
            video: true 
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then(stream => { 
                video.srcObject = stream; 
                statusTitle.className = 'text-success fw-bold mb-2';
                statusTitle.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Kamera Aktif';
                statusDesc.textContent = 'Arahkan wajah Anda ke kamera untuk presensi.';
            })
            .catch(err => {
                console.error("Camera Error:", err);
                statusTitle.className = 'text-danger fw-bold mb-2';
                statusTitle.innerHTML = '<i class="bi bi-camera-video-off-fill me-2"></i>Kamera Gagal';
                
                if (err.name === 'NotAllowedError') {
                    statusDesc.textContent = 'Izin kamera ditolak. Silakan aktifkan izin kamera di pengaturan browser Anda.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    statusDesc.textContent = 'Kamera tidak ditemukan. Pastikan kamera terpasang dengan benar.';
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    statusDesc.textContent = 'Kamera sedang digunakan oleh aplikasi lain.';
                } else {
                    statusDesc.textContent = 'Gagal mengakses kamera. Silakan periksa pengaturan browser atau perangkat Anda.';
                }
            });
    }

    // Function untuk kirim request presensi
    async function catatPresensi(santriId) {
        // Cek cooldown (minimal 10 detik antar request dari santri yg sama)
        if (cooldowns.has(santriId) && Date.now() - cooldowns.get(santriId) < 10000) {
            return; 
        }
        cooldowns.set(santriId, Date.now());

        try {
            const response = await fetch('{{ route("presensi.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    santri_id: santriId,
                    waktu_sholat: selectedWaktuSholat 
                })
            });

            const result = await response.json();

            if (response.ok) {
                showNotification(`Berhasil: ${result.data.santri.nama}`, result.message, 'success');
            } else {
                cooldowns.set(santriId, Date.now() + 20000); 
                showNotification('Gagal', result.message, 'danger');
            }

        } catch (error) {
            console.error(error);
        }
    }

    video.addEventListener('play', () => {
        // Hapus canvas lama jika ada untuk mencegah penumpukan
        const oldCanvas = container.querySelector('canvas');
        if (oldCanvas) oldCanvas.remove();

        const canvas = faceapi.createCanvasFromMedia(video);
        container.appendChild(canvas);
        const displaySize = { width: video.clientWidth, height: video.clientHeight };
        faceapi.matchDimensions(canvas, displaySize);

        scanInterval = setInterval(async () => {
            if(!faceMatcher) return;

            const detections = await faceapi.detectAllFaces(video).withFaceLandmarks().withFaceDescriptors();
            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
            
            const results = resizedDetections.map(d => faceMatcher.findBestMatch(d.descriptor));

            results.forEach((result, i) => {
                const box = resizedDetections[i].detection.box;
                const drawBox = new faceapi.draw.DrawBox(box, { 
                    label: result.label === 'unknown' ? 'Tidak Dikenal' : 'Mencocokkan...',
                    boxColor: result.label === 'unknown' ? '#ef4444' : '#198754'
                });
                drawBox.draw(canvas);

                if (result.label !== 'unknown') {
                    catatPresensi(result.label);
                }
            });

        }, 500); // Scan tiap 500ms
    });

    // Jangan mulai secara otomatis saat load
    // loadDataAndModels();
</script>
@endpush
