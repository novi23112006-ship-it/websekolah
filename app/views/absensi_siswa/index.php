<!-- Absensi Siswa Scanner View -->
<style>
/* Viewport kamera tunggal & proteksi anti duplikasi video/canvas */
#reader {
    position: relative !important;
    width: 100% !important;
    max-width: 500px !important;
    margin: 0 auto !important;
    border-radius: 12px !important;
    overflow: hidden !important;
    background: #000000 !important;
    min-height: 300px;
}
#reader video {
    width: 100% !important;
    height: auto !important;
    max-height: 480px !important;
    object-fit: cover !important;
    display: block !important;
    margin: 0 auto !important;
    border-radius: 12px !important;
}
#reader canvas {
    display: none !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
    opacity: 0 !important;
    pointer-events: none !important;
}
#reader video:not(:first-of-type) {
    display: none !important;
}
#qr-shaded-region {
    border-radius: 12px !important;
}
</style>

<!-- Hasil Scan Alert Area -->
<div id="alertArea" class="mb-3"></div>

<div class="row g-4">

    <!-- Kolom Kiri: Scanner -->
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-camera-video-fill me-2"></i>Scanner Barcode / QR Code</h5>
                <span class="badge bg-light text-dark" id="scannerStatus">Siap</span>
            </div>
            <div class="card-body text-center">
                <!-- Status Jarak & Geofencing (Maksimal 100 Meter) -->
                <div class="alert alert-light border d-flex align-items-center justify-content-between p-2 mb-3 rounded-3" id="gpsStatusBox">
                    <div class="d-flex align-items-center gap-2 text-start">
                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        <div>
                            <span class="fw-semibold small d-block" id="gpsStatusText">Mendeteksi Lokasi GPS...</span>
                            <small class="text-muted" style="font-size: 11px;">Batas Jarak: Maksimal <strong>100 Meter</strong> dari Sekolah</small>
                        </div>
                    </div>
                    <span class="badge bg-secondary" id="gpsDistanceBadge">GPS Standby</span>
                </div>

                <!-- Mode Selector Absensi (Otomatis / Khusus Masuk / Khusus Pulang) -->
                <div class="d-flex justify-content-center mb-3">
                    <div class="btn-group p-1 bg-light rounded-pill border" role="group" id="modeSelectorGroup" aria-label="Mode Absensi">
                        <input type="radio" class="btn-check" name="modeAbsensi" id="modeAuto" value="auto" checked autocomplete="off">
                        <label class="btn btn-sm rounded-pill px-3 py-1 fw-semibold" for="modeAuto">
                            <i class="bi bi-arrow-repeat me-1 text-primary"></i>Otomatis
                        </label>

                        <input type="radio" class="btn-check" name="modeAbsensi" id="modeMasuk" value="masuk" autocomplete="off">
                        <label class="btn btn-sm rounded-pill px-3 py-1 fw-semibold" for="modeMasuk">
                            <i class="bi bi-box-arrow-in-right me-1 text-success"></i>Khusus Masuk
                        </label>

                        <input type="radio" class="btn-check" name="modeAbsensi" id="modePulang" value="pulang" autocomplete="off">
                        <label class="btn btn-sm rounded-pill px-3 py-1 fw-semibold" for="modePulang">
                            <i class="bi bi-box-arrow-left me-1 text-warning"></i>Khusus Pulang
                        </label>
                    </div>
                </div>

                <!-- Area Kamera -->
                <div id="reader" style="width:100%; max-width:500px; margin:0 auto; border-radius: 8px; overflow:hidden; background:#000; min-height: 300px;" class="mb-3">
                    <div class="d-flex align-items-center justify-content-center text-white" style="min-height:300px;">
                        <div>
                            <i class="bi bi-camera" style="font-size:4rem; opacity:0.5;"></i>
                            <p class="mt-2 opacity-50">Klik "Mulai Scanner" untuk mengaktifkan kamera</p>
                        </div>
                    </div>
                </div>

                <!-- Tombol Kontrol Scanner -->
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <button id="btnMulai" class="btn btn-success" onclick="mulaiScanner()">
                        <i class="bi bi-play-circle me-1"></i> Mulai Scanner
                    </button>
                    <button id="btnStop" class="btn btn-warning d-none" onclick="stopScanner()">
                        <i class="bi bi-stop-circle me-1"></i> Stop Scanner
                    </button>
                    <button class="btn btn-outline-secondary" onclick="toggleManual()">
                        <i class="bi bi-keyboard me-1"></i> Input Manual
                    </button>
                </div>

                <!-- Input Manual (tersembunyi secara default) -->
                <div id="manualArea" class="mt-3 d-none">
                    <hr>
                    <label class="form-label fw-semibold">Input Barcode Manual</label>
                    <div class="input-group">
                        <input type="text" id="manualBarcode" class="form-control"
                               placeholder="Ketik atau tempel barcode di sini..."
                               autocomplete="off">
                        <button class="btn btn-primary" type="button" onclick="submitManual()">
                            <i class="bi bi-send"></i> Absen
                        </button>
                    </div>
                    <small class="text-muted">Tekan Enter atau klik Absen untuk memproses.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Hasil Terakhir -->
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-check me-2"></i>Hasil Scan Terakhir</h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center" id="hasilArea">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-qr-code-scan" style="font-size:4rem; opacity:0.3;"></i>
                    <p class="mt-2">Belum ada data scan.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Petunjuk & Info Akses Kamera -->
<div class="card mt-4 shadow-sm border-info">
    <div class="card-body">
        <h6 class="card-title text-info"><i class="bi bi-info-circle me-1"></i>Petunjuk Penggunaan & Akses Kamera</h6>
        <div class="alert alert-info border-0 rounded-3 d-flex align-items-center gap-2 mb-3 py-2 px-3 small">
            <i class="bi bi-shield-check fs-5 text-primary flex-shrink-0"></i>
            <div>
                <strong>Akses Kamera Langsung:</strong> Saat peramban menampilkan jendela izin kamera, pilih <strong>"Allow while visiting the site"</strong> (atau klik ikon kamera di bilah alamat lalu pilih <em>Always allow / Izinkan</em>). Kamera akan selalu langsung aktif otomatis setiap kali halaman dibuka tanpa perlu konfirmasi lagi!
            </div>
        </div>
        <ul class="mb-0 small">
            <li>Klik <strong>Mulai Scanner</strong> untuk mengaktifkan kamera (akan aktif otomatis jika izin kamera sudah disimpan permanen).</li>
            <li>Arahkan QR Code/Barcode kartu siswa ke depan kamera.</li>
            <li>Sistem akan otomatis mendeteksi dan memproses absensi.</li>
            <li>Jika kamera tidak tersedia, gunakan <strong>Input Manual</strong> sebagai alternatif.</li>
            <li>Batas waktu: <strong>07:00 WIB</strong>. Lewat dari itu dianggap Terlambat.</li>
        </ul>
    </div>
</div>

<!-- html5-qrcode CDN -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
const SCAN_URL   = '<?= BASEURL; ?>/absensi_siswa/scan';
const CSRF_TOKEN = '<?= htmlspecialchars($data['csrf_token']); ?>';
const SEKOLAH_LAT = <?= (float)($data['lokasi_sekolah']['latitude'] ?? -6.208763); ?>;
const SEKOLAH_LNG = <?= (float)($data['lokasi_sekolah']['longitude'] ?? 106.845599); ?>;
const MAX_RADIUS_METER = <?= (int)($data['lokasi_sekolah']['radius_meter'] ?? 100); ?>;
const GEOFENCE_ACTIVE = <?= ($data['lokasi_sekolah']['geofence_aktif'] ?? true) ? 'true' : 'false'; ?>;

let html5QrcodeScanner = null;
let isScanning = false;
let isStarting = false;
let lastScanned = '';
let scanCooldown = false;
let userCoords = null;

function hitungJarakClient(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return Math.round(R * c);
}

function updateGpsLocation(callback) {
    const badge = document.getElementById('gpsDistanceBadge');
    const text = document.getElementById('gpsStatusText');

    if (!navigator.geolocation) {
        if (text) text.textContent = 'Geolocation tidak didukung browser.';
        if (badge) { badge.className = 'badge bg-danger'; badge.textContent = 'No GPS'; }
        if (callback) callback(null);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            userCoords = {
                latitude: pos.coords.latitude,
                longitude: pos.coords.longitude,
                accuracy: pos.coords.accuracy
            };
            const jarak = hitungJarakClient(userCoords.latitude, userCoords.longitude, SEKOLAH_LAT, SEKOLAH_LNG);
            userCoords.jarak = jarak;

            if (badge && text) {
                if (!GEOFENCE_ACTIVE) {
                    badge.className = 'badge bg-info text-dark';
                    badge.innerHTML = `<i class="bi bi-geo-alt me-1"></i>${jarak}m (Bebas Radius)`;
                    text.innerHTML = `Batasan Jarak Nonaktif &bull; Terdeteksi <strong>${jarak} meter</strong> dari sekolah`;
                } else if (jarak <= MAX_RADIUS_METER) {
                    badge.className = 'badge bg-success';
                    badge.innerHTML = `<i class="bi bi-check-circle me-1"></i>${jarak}m (Dalam Radius)`;
                    text.innerHTML = `Lokasi Valid &bull; Terdeteksi <strong>${jarak} meter</strong> dari sekolah`;
                } else {
                    badge.className = 'badge bg-danger';
                    badge.innerHTML = `<i class="bi bi-x-circle me-1"></i>${jarak}m (Di Luar Radius)`;
                    text.innerHTML = `<span class="text-danger fw-bold">Di Luar Batas!</span> Jarak Anda <strong>${jarak} meter</strong> (Maks 100m)`;
                    if (isScanning) {
                        stopScanner();
                    }
                    tampilkanDitolakJarakSiswa(jarak);
                }
            }

            if (callback) callback(userCoords);
        },
        (err) => {
            if (badge && text) {
                badge.className = 'badge bg-warning text-dark';
                badge.textContent = 'GPS Nonaktif';
                text.innerHTML = '<span class="text-warning fw-semibold">Mohon izinkan akses lokasi (GPS) pada browser</span>';
            }
            if (callback) callback(null);
        },
        { enableHighAccuracy: true, timeout: 8000, maximumAge: 3000 }
    );
}

// ─── Scanner ─────────────────────────────────────────────────────────────────

async function mulaiScanner() {
    // Cegah eksekusi ganda jika scanner sedang berjalan atau sedang dalam proses start
    if (isScanning || isStarting) {
        return;
    }
    isStarting = true;

    const btnMulai = document.getElementById('btnMulai');
    const btnStop  = document.getElementById('btnStop');
    if (btnMulai) btnMulai.disabled = true;

    if (GEOFENCE_ACTIVE && userCoords && userCoords.jarak > MAX_RADIUS_METER) {
        tampilkanDitolakJarakSiswa(userCoords.jarak);
        if (btnMulai) btnMulai.disabled = false;
        isStarting = false;
        return;
    }

    setStatus('Menyiapkan Kamera...', 'warning');

    // Hentikan dan bersihkan instance kamera sebelumnya jika ada
    if (html5QrcodeScanner) {
        try {
            if (isScanning) {
                await html5QrcodeScanner.stop();
            }
            html5QrcodeScanner.clear();
        } catch (e) {
            console.warn('Pembersihan scanner sebelumnya:', e);
        }
        html5QrcodeScanner = null;
        isScanning = false;
    }

    const readerEl = document.getElementById('reader');
    if (readerEl) {
        readerEl.innerHTML = '';
    }

    try {
        html5QrcodeScanner = new Html5Qrcode("reader");

        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        // Deteksi daftar kamera fisik
        const devices = await Html5Qrcode.getCameras().catch(() => []);
        let cameraSelection = { facingMode: "environment" };

        if (devices && devices.length > 0) {
            const backCam = devices.find(d => /back|rear|environment/i.test(d.label));
            if (backCam) {
                cameraSelection = backCam.id;
            } else {
                cameraSelection = devices[0].id;
            }
        }

        try {
            await html5QrcodeScanner.start(
                cameraSelection,
                config,
                onScanSuccess,
                onScanFailure
            );
            isScanning = true;
            isStarting = false;
            if (btnMulai) {
                btnMulai.disabled = false;
                btnMulai.classList.add('d-none');
            }
            if (btnStop) btnStop.classList.remove('d-none');
            setStatus('Scanning...', 'warning');
        } catch (errCam) {
            // Fallback: coba kamera depan / user
            if (readerEl) readerEl.innerHTML = '';
            await html5QrcodeScanner.start(
                { facingMode: "user" },
                config,
                onScanSuccess,
                onScanFailure
            );
            isScanning = true;
            isStarting = false;
            if (btnMulai) {
                btnMulai.disabled = false;
                btnMulai.classList.add('d-none');
            }
            if (btnStop) btnStop.classList.remove('d-none');
            setStatus('Scanning (Kamera Depan)...', 'warning');
        }
    } catch (err) {
        isScanning = false;
        isStarting = false;
        if (btnMulai) {
            btnMulai.disabled = false;
            btnMulai.classList.remove('d-none');
        }
        if (btnStop) btnStop.classList.add('d-none');
        showAlert('Kamera tidak dapat diakses atau izin kamera ditolak. Silakan periksa izin kamera atau gunakan Input Manual.', 'danger');
        setStatus('Kamera Gagal', 'danger');
        const manualArea = document.getElementById('manualArea');
        if (manualArea) manualArea.classList.remove('d-none');
    }
}

async function stopScanner() {
    isStarting = false;
    const btnMulai = document.getElementById('btnMulai');
    const btnStop  = document.getElementById('btnStop');

    if (html5QrcodeScanner) {
        try {
            if (isScanning) {
                await html5QrcodeScanner.stop();
            }
            html5QrcodeScanner.clear();
        } catch (e) {
            console.warn('Pembersihan scanner:', e);
        }
        html5QrcodeScanner = null;
    }
    isScanning = false;

    const readerEl = document.getElementById('reader');
    if (readerEl) {
        readerEl.innerHTML = `
            <div class="d-flex align-items-center justify-content-center text-muted" style="min-height:300px;">
                <div class="text-center">
                    <i class="bi bi-camera" style="font-size:4rem; opacity:0.3;"></i>
                    <p class="mt-2">Scanner dihentikan.</p>
                </div>
            </div>`;
    }

    if (btnMulai) {
        btnMulai.disabled = false;
        btnMulai.classList.remove('d-none');
    }
    if (btnStop) btnStop.classList.add('d-none');
    setStatus('Siap', 'secondary');
}

function tampilkanDitolakJarakSiswa(jarak) {
    setStatus('Di Luar Radius', 'danger');
    const jarakStr = jarak ? `${jarak} meter` : 'Tidak terdeteksi';
    showAlert(`Absensi ditolak: Anda berada di luar area sekolah! Jarak Anda saat ini ${jarakStr} (Batas maksimal adalah ${MAX_RADIUS_METER} meter).`, 'danger');
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: `Di Luar Batas Jarak Absensi (Maks ${MAX_RADIUS_METER}m)`,
            html: `
                <div class="text-center py-2">
                    <i class="bi bi-geo-alt-fill text-danger fs-1 mb-2 d-block"></i>
                    <p class="text-secondary mb-2">Absensi tidak dapat diproses karena perangkat Anda berada di luar area sekolah.</p>
                    <div class="alert alert-danger py-2 px-3 small rounded-3 mb-2">
                        Jarak terdeteksi: <strong>${jarakStr}</strong><br>
                        Batas maksimal yang diizinkan: <strong>${MAX_RADIUS_METER} meter</strong>
                    </div>
                    <small class="text-muted">Pastikan Anda sudah berada di lingkungan sekolah (maksimal ${MAX_RADIUS_METER} meter) dan GPS perangkat aktif.</small>
                </div>
            `,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Tutup'
        });
    }
}

function onScanSuccess(decodedText) {
    // Cooldown 3 detik — mencegah duplikasi scan cepat
    if (scanCooldown) return;
    if (decodedText === lastScanned) return;

    if (GEOFENCE_ACTIVE && userCoords && userCoords.jarak > MAX_RADIUS_METER) {
        tampilkanDitolakJarakSiswa(userCoords.jarak);
        return;
    }

    lastScanned = decodedText;
    scanCooldown = true;
    setTimeout(() => {
        scanCooldown = false;
        lastScanned = '';
    }, 3000);

    kirimBarcode(decodedText);
}

function onScanFailure() { /* diabaikan */ }

// ─── Manual Input ─────────────────────────────────────────────────────────────

function toggleManual() {
    const area = document.getElementById('manualArea');
    area.classList.toggle('d-none');
    if (!area.classList.contains('d-none')) {
        document.getElementById('manualBarcode').focus();
    }
}

function submitManual() {
    const barcode = document.getElementById('manualBarcode').value.trim();
    if (!barcode) {
        showAlert('Masukkan barcode terlebih dahulu.', 'warning');
        return;
    }

    if (GEOFENCE_ACTIVE && userCoords && userCoords.jarak > MAX_RADIUS_METER) {
        tampilkanDitolakJarakSiswa(userCoords.jarak);
        return;
    }

    kirimBarcode(barcode);
    document.getElementById('manualBarcode').value = '';
    document.getElementById('manualBarcode').focus();
}

document.addEventListener('DOMContentLoaded', function() {
    const manualEl = document.getElementById('manualBarcode');
    if (manualEl) {
        manualEl.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') submitManual();
        });
    }

    // Auto-start scanner jika izin kamera sudah diizinkan (granted) di browser
    if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({ name: 'camera' }).then(permissionStatus => {
            if (permissionStatus.state === 'granted') {
                if (!isScanning && !isStarting) {
                    mulaiScanner();
                }
            }
            permissionStatus.onchange = function() {
                if (this.state === 'granted' && !isScanning && !isStarting) {
                    mulaiScanner();
                }
            };
        }).catch(() => {});
    }

    // Bersihkan stream kamera saat halaman ditutup atau berpindah
    window.addEventListener('beforeunload', function() {
        if (html5QrcodeScanner && isScanning) {
            try { html5QrcodeScanner.stop(); } catch(e){}
        }
    });
});

// ─── AJAX ke Backend ──────────────────────────────────────────────────────────

function kirimBarcode(barcode) {
    if (GEOFENCE_ACTIVE && userCoords && userCoords.jarak > MAX_RADIUS_METER) {
        tampilkanDitolakJarakSiswa(userCoords.jarak);
        return;
    }

    const executeSend = (coords) => {
        if (GEOFENCE_ACTIVE && coords && coords.jarak > MAX_RADIUS_METER) {
            tampilkanDitolakJarakSiswa(coords.jarak);
            return;
        }

        setStatus('Memproses...', 'info');
        showAlert('<span class="spinner-border spinner-border-sm me-2"></span>Memvalidasi lokasi & barcode...', 'info');

        const formData = new FormData();
        formData.append('barcode', barcode);
        formData.append('csrf_token', CSRF_TOKEN);

        const modeInput = document.querySelector('input[name="modeAbsensi"]:checked');
        const selectedMode = modeInput ? modeInput.value : 'auto';
        formData.append('mode', selectedMode);

        if (coords) {
            formData.append('latitude', coords.latitude);
            formData.append('longitude', coords.longitude);
        }

        fetch(SCAN_URL, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(res => {
            if (!res.ok && res.status !== 400 && res.status !== 403) {
                throw new Error('HTTP ' + res.status);
            }
            return res.json();
        })
        .then(data => {
            tampilkanHasil(data);
            setStatus('Siap', 'light text-dark');
        })
        .catch(() => {
            showAlert('Terjadi kesalahan jaringan. Periksa koneksi dan coba lagi.', 'danger');
            setStatus('Error', 'danger');
        });
    };

    if (userCoords) {
        executeSend(userCoords);
    } else {
        updateGpsLocation((coords) => {
            if (!coords && GEOFENCE_ACTIVE) {
                showAlert('Akses lokasi (GPS) diperlukan untuk memverifikasi jarak maksimal 100 meter dari sekolah.', 'danger');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Lokasi (GPS) Wajib',
                        text: 'Absensi ditolak: Koordinat GPS tidak terdeteksi. Silakan aktifkan GPS dan izinkan akses lokasi pada browser Anda.',
                        confirmButtonColor: '#dc3545'
                    });
                }
                return;
            }
            executeSend(coords);
        });
    }
}

// ─── Tampilkan Hasil ──────────────────────────────────────────────────────────

function tampilkanHasil(data) {
    const area = document.getElementById('hasilArea');

    if (!data.status) {
        if (data.di_luar_radius) {
            tampilkanDitolakJarakSiswa(data.jarak);
            return;
        }

        const isSudahAbsen = data.sudah_absen === true;
        const isLengkap    = data.sudah_lengkap === true;
        showAlert(escHtml(data.pesan), isLengkap ? 'info' : (isSudahAbsen ? 'info' : 'danger'));

        if (isLengkap && data.siswa) {
            const jamMasukStr = data.jam_masuk ? escHtml(data.jam_masuk.substring(0, 5)) : '-';
            const jamPulangStr = data.jam_pulang ? escHtml(data.jam_pulang.substring(0, 5)) : '-';
            area.innerHTML = kartuSiswaPulang(data.siswa, jamMasukStr, jamPulangStr, 'Absensi Lengkap', 'success');
        } else if (data.siswa) {
            area.innerHTML = kartuSiswa(
                data.siswa, data.jam || data.jam_masuk || null,
                isSudahAbsen ? 'Sudah Absen' : 'Gagal',
                isSudahAbsen ? 'info' : 'danger'
            );
        } else {
            area.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-x-circle text-danger" style="font-size:3rem;"></i>
                    <p class="mt-2 fw-semibold text-danger">${escHtml(data.pesan)}</p>
                </div>`;
        }

        if (typeof Swal !== 'undefined') {
            const swalTitle = isLengkap ? 'Absensi Hari Ini Lengkap' : (isSudahAbsen ? 'Sudah Absen Hari Ini' : 'Absensi Gagal');
            const jamMasukStr = data.jam_masuk ? escHtml(data.jam_masuk.substring(0, 5)) : '-';
            const jamPulangStr = data.jam_pulang ? escHtml(data.jam_pulang.substring(0, 5)) : '-';

            Swal.fire({
                icon: isLengkap || isSudahAbsen ? 'info' : 'error',
                title: swalTitle,
                html: isLengkap ? `
                    <div class="text-center py-2">
                        <p class="text-muted mb-3">${escHtml(data.pesan)}</p>
                        <div class="p-3 bg-light rounded-3 text-start small border">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Jam Masuk:</span>
                                <strong class="text-success"><i class="bi bi-box-arrow-in-right me-1"></i>${jamMasukStr} WIB</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Jam Pulang:</span>
                                <strong class="text-primary"><i class="bi bi-box-arrow-left me-1"></i>${jamPulangStr} WIB</strong>
                            </div>
                        </div>
                    </div>
                ` : `<div class="text-center py-2"><p class="mb-0 text-muted">${escHtml(data.pesan)}</p></div>`,
                confirmButtonColor: isLengkap || isSudahAbsen ? '#0d6efd' : '#dc3545',
                confirmButtonText: 'Tutup',
                timer: 4000,
                timerProgressBar: true
            });
        }
        return;
    }

    // Sukses Absen Pulang
    if (data.tipe_absen === 'pulang') {
        const jamMasukStr  = data.jam_masuk ? escHtml(data.jam_masuk.substring(0, 5)) : '-';
        const jamPulangStr = data.jam_pulang ? escHtml(data.jam_pulang.substring(0, 5)) : (data.jam ? escHtml(data.jam.substring(0, 5)) : '-');

        showAlert(
            `&#10003; <strong>${escHtml(data.siswa.nama)}</strong> — <span class="badge bg-primary">Absen Pulang Berhasil</span> &nbsp;|&nbsp; Pulang: ${jamPulangStr} WIB (Masuk: ${jamMasukStr} WIB)`,
            'primary'
        );
        area.innerHTML = kartuSiswaPulang(data.siswa, jamMasukStr, jamPulangStr, 'Absen Pulang Berhasil', 'primary');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Absen Pulang Berhasil!',
                html: `
                    <div class="text-center py-2">
                        <h5 class="fw-bold mb-1 text-dark">${escHtml(data.siswa.nama)}</h5>
                        <p class="text-muted small mb-3">Kelas: <b>${escHtml(data.siswa.nama_kelas || '-')}</b> | NIS: ${escHtml(data.siswa.nis)}</p>
                        <div class="p-3 bg-light rounded-3 text-start small border mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Jam Masuk:</span>
                                <strong class="text-success"><i class="bi bi-box-arrow-in-right me-1"></i>${jamMasukStr} WIB</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Jam Pulang:</span>
                                <strong class="text-primary"><i class="bi bi-box-arrow-left me-1"></i>${jamPulangStr} WIB</strong>
                            </div>
                        </div>
                        <div class="text-success small fw-semibold">
                            <i class="bi bi-house-door-fill me-1"></i>Selamat beristirahat & hati-hati di jalan!
                        </div>
                    </div>
                `,
                timer: 4000,
                timerProgressBar: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'OK'
            });
        }
        return;
    }

    // Sukses Absen Masuk
    const isHadir    = data.status_kehadiran === 'hadir';
    const badgeClass = isHadir ? 'success' : 'warning';
    const badgeText  = isHadir ? 'Hadir (Tepat Waktu)' : 'Terlambat';

    showAlert(
        `&#10003; <strong>${escHtml(data.siswa.nama)}</strong> — <span class="badge bg-${badgeClass}">${badgeText}</span> &nbsp;|&nbsp; ${escHtml(data.jam)}`,
        isHadir ? 'success' : 'warning'
    );
    area.innerHTML = kartuSiswa(data.siswa, data.jam, badgeText, badgeClass);

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: isHadir ? 'success' : 'warning',
            title: isHadir ? 'Absen Masuk Berhasil (Tepat Waktu)!' : 'Absen Masuk Berhasil (Terlambat)!',
            html: `
                <div class="text-center py-2">
                    <h5 class="fw-bold mb-1 text-dark">${escHtml(data.siswa.nama)}</h5>
                    <p class="text-muted small mb-3">Kelas: <b>${escHtml(data.siswa.nama_kelas || '-')}</b> | NIS: ${escHtml(data.siswa.nis)}</p>
                    <div class="mb-2">
                        <span class="badge bg-${badgeClass} fs-6 px-3 py-2">${badgeText}</span>
                    </div>
                    <div class="text-secondary small mt-2">
                        <i class="bi bi-clock me-1"></i>Pukul <b>${escHtml(data.jam)} WIB</b>
                    </div>
                </div>
            `,
            timer: 3500,
            timerProgressBar: true,
            confirmButtonColor: isHadir ? '#198754' : '#ffc107',
            confirmButtonText: 'OK'
        });
    }
}

function kartuSiswaPulang(siswa, jamMasuk, jamPulang, badgeTitle = 'Absen Pulang', badgeClass = 'primary') {
    return `
        <div class="text-center py-3">
            <i class="bi bi-person-check-fill text-${badgeClass}" style="font-size:4rem;"></i>
            <h5 class="fw-bold mt-2 mb-1">${escHtml(siswa.nama)}</h5>
            <p class="text-muted mb-1 small">NIS: ${escHtml(siswa.nis)}</p>
            <p class="text-muted mb-2 small">Kelas: ${escHtml(siswa.nama_kelas || '-')}</p>
            <span class="badge bg-${badgeClass} fs-6 mb-3">${escHtml(badgeTitle)}</span>
            <div class="d-flex justify-content-center gap-2">
                <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-box-arrow-in-right text-success me-1"></i>Masuk: ${escHtml(jamMasuk)} WIB</span>
                <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-box-arrow-left text-primary me-1"></i>Pulang: ${escHtml(jamPulang)} WIB</span>
            </div>
        </div>`;
}

function kartuSiswa(siswa, jam, statusText, badgeClass) {
    const jamHtml = jam
        ? `<div class="mt-2"><span class="badge bg-secondary"><i class="bi bi-clock me-1"></i>${escHtml(jam)} WIB</span></div>`
        : '';
    return `
        <div class="text-center py-3">
            <i class="bi bi-person-circle text-primary" style="font-size:4rem;"></i>
            <h5 class="fw-bold mt-2 mb-1">${escHtml(siswa.nama)}</h5>
            <p class="text-muted mb-1 small">NIS: ${escHtml(siswa.nis)}</p>
            <p class="text-muted mb-2 small">Kelas: ${escHtml(siswa.nama_kelas || '-')}</p>
            <span class="badge bg-${badgeClass} fs-6">${escHtml(statusText)}</span>
            ${jamHtml}
        </div>`;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function showAlert(pesan, type) {
    document.getElementById('alertArea').innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${pesan}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
}

function setStatus(text, cls) {
    const el = document.getElementById('scannerStatus');
    el.className = 'badge bg-' + cls;
    el.textContent = text;
}

function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
