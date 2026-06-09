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
        /* Fix hardware acceleration clipping bug on mobile */
        -webkit-transform: translate3d(0, 0, 0);
        transform: translate3d(0, 0, 0);
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
    }

    #video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 1.25rem;
    }

    canvas {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 1.25rem;
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

        $getEnd = function($timeStr) {
            try {
                return \Carbon\Carbon::createFromFormat('H:i', $timeStr)->addMinutes(10)->format('H:i');
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
            $end = $getEnd($apiTimes[$sholat]);
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
    
    const jadwalInfo = @json($jadwalInfo ?? []);
    let faceMatcher = null;
    let labeledFaceDescriptors = [];
    const cooldowns = new Map(); // Untuk mencegah spam request
    let selectedWaktuSholat = null;
    let isModelsLoaded = false;
    let scanInterval = null;
    let shouldStartVideo = false; // Flag to play video when models finish loading
 
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
        // Cek apakah waktu sholat valid berdasarkan jadwalInfo
        // TEMPORARY BYPASS FOR TESTING: Changed condition to false
        if (false && jadwalInfo && jadwalInfo[selectedWaktuSholat]) {
            const windowTimes = jadwalInfo[selectedWaktuSholat];
            
            // Dapatkan waktu saat ini di Asia/Jakarta
            const formatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Jakarta',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            const timeParts = formatter.formatToParts(new Date());
            const hour = timeParts.find(p => p.type === 'hour').value;
            const minute = timeParts.find(p => p.type === 'minute').value;
            
            const currentMin = parseInt(hour, 10) * 60 + parseInt(minute, 10);
            
            const [startH, startM] = windowTimes.start.split(':').map(Number);
            const startMin = startH * 60 + startM;
            
            const [endH, endM] = windowTimes.end.split(':').map(Number);
            const endMin = endH * 60 + endM;
            
            if (currentMin < startMin || currentMin > endMin) {
                showNotification(
                    'Presensi Belum Dibuka / Sudah Ditutup',
                    `Presensi sholat ${selectedWaktuSholat} hanya diperbolehkan dari pukul ${windowTimes.start} sampai ${windowTimes.end}.`,
                    'danger'
                );
                return; // Batalkan dan jangan aktifkan kamera
            }
        }

        sholatSelector.classList.add('d-none');
        cameraArea.classList.remove('d-none');
        selectedBadge.textContent = `Sholat: ${selectedWaktuSholat}`;
        
        if (!isModelsLoaded) {
            shouldStartVideo = true;
            // statusTitle dan statusDesc sudah otomatis diupdate oleh loadDataAndModels() yang berjalan di background
        } else {
            startVideo();
        }
    });

    function stopCameraAndReturn() {
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
        
        // Reset status dan flag
        shouldStartVideo = false;
        statusTitle.className = 'text-success fw-bold mb-2';
        statusTitle.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Sistem Aktif';
        statusDesc.textContent = 'Silakan pilih waktu sholat dan mulai presensi.';
    }

    btnBatal.addEventListener('click', stopCameraAndReturn);

    async function loadDataAndModels() {
        try {
            // 1. Load Models
            statusDesc.textContent = 'Memuat AI Models...';
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
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

            faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.45); // Threshold 0.45 for stricter matching

            statusTitle.className = 'text-success fw-bold mb-2';
            statusTitle.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Sistem Aktif';
            statusDesc.textContent = `${santris.length} wajah santri berhasil dimuat. Silakan menghadap kamera.`;
            isModelsLoaded = true;
            
            if (shouldStartVideo) {
                startVideo();
            }

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
            video: {
                facingMode: 'user'
            }
        };

        navigator.mediaDevices.getUserMedia(constraints)
            .then(stream => { 
                video.srcObject = stream; 
                video.setAttribute('playsinline', true);
                video.play().catch(e => console.error("Error playing video:", e));
                statusTitle.className = 'text-success fw-bold mb-2';
                statusTitle.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Kamera Aktif';
                statusDesc.textContent = 'Arahkan wajah Anda ke kamera untuk presensi.';
            })
            .catch(err => {
                console.warn("Kamera depan gagal, mencoba kamera default...", err);
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        video.srcObject = stream;
                        video.setAttribute('playsinline', true);
                        video.play().catch(e => console.error("Error playing video:", e));
                        statusTitle.className = 'text-success fw-bold mb-2';
                        statusTitle.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Kamera Aktif';
                        statusDesc.textContent = 'Arahkan wajah Anda ke kamera untuk presensi.';
                    })
                    .catch(fallbackErr => {
                        console.error("Camera Error:", fallbackErr);
                        statusTitle.className = 'text-danger fw-bold mb-2';
                        statusTitle.innerHTML = '<i class="bi bi-camera-video-off-fill me-2"></i>Kamera Gagal';
                        
                        if (fallbackErr.name === 'NotAllowedError') {
                            statusDesc.textContent = 'Izin kamera ditolak. Silakan aktifkan izin kamera di pengaturan browser Anda.';
                        } else if (fallbackErr.name === 'NotFoundError' || fallbackErr.name === 'DevicesNotFoundError') {
                            statusDesc.textContent = 'Kamera tidak ditemukan. Pastikan kamera terpasang dengan benar.';
                        } else if (fallbackErr.name === 'NotReadableError' || fallbackErr.name === 'TrackStartError') {
                            statusDesc.textContent = 'Kamera sedang digunakan oleh aplikasi lain.';
                        } else {
                            statusDesc.textContent = 'Gagal mengakses kamera. Silakan periksa pengaturan browser atau perangkat Anda.';
                        }
                    });
            });
    }

    // Function untuk kirim request presensi
    async function catatPresensi(santriId) {
        // Cek cooldown (minimal 10 detik antar request dari santri yg sama)
        if (cooldowns.has(santriId) && Date.now() - cooldowns.get(santriId) < 10000) {
            return; 
        }
        cooldowns.set(santriId, Date.now());

        // Stop scanning immediately to prevent sending more requests while this is processing
        if (scanInterval) {
            clearInterval(scanInterval);
        }

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

            // Immediately stop camera and return to selector page
            stopCameraAndReturn();

        } catch (error) {
            console.error(error);
            showNotification('Error', 'Terjadi kesalahan sistem.', 'danger');
            stopCameraAndReturn();
        }
    }

    video.addEventListener('play', () => {
        // Hapus canvas lama jika ada untuk mencegah penumpukan
        const oldCanvas = container.querySelector('canvas');
        if (oldCanvas) oldCanvas.remove();

        const canvas = faceapi.createCanvasFromMedia(video);
        container.appendChild(canvas);
        let displaySize = { width: video.clientWidth, height: video.clientHeight };
        if (displaySize.width > 0 && displaySize.height > 0) {
            faceapi.matchDimensions(canvas, displaySize);
        }

        scanInterval = setInterval(async () => {
            if(!faceMatcher) return;

            // Re-initialize displaySize if it was 0 when event fired
            if (displaySize.width === 0 || displaySize.height === 0) {
                displaySize = { width: video.clientWidth, height: video.clientHeight };
                if (displaySize.width > 0 && displaySize.height > 0) {
                    faceapi.matchDimensions(canvas, displaySize);
                }
            }

            const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.3 })).withFaceLandmarks().withFaceDescriptor();
            
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
            
            if (detection && displaySize.width > 0 && displaySize.height > 0) {
                const resizedDetection = faceapi.resizeResults(detection, displaySize);
                const result = faceMatcher.findBestMatch(resizedDetection.descriptor);
                
                const box = resizedDetection.detection.box;
                const drawBox = new faceapi.draw.DrawBox(box, { 
                    label: result.label === 'unknown' ? 'Tidak Dikenal' : 'Mencocokkan...',
                    boxColor: result.label === 'unknown' ? '#ef4444' : '#198754'
                });
                drawBox.draw(canvas);

                if (result.label !== 'unknown') {
                    catatPresensi(result.label);
                }
            }

        }, 400); // Scan tiap 400ms
    });

    // Mulai memuat models dan data di background saat halaman dibuka
    loadDataAndModels();
</script>
@endpush
