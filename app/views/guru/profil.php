<?php $p = $data['profil']; ?>

<div class="container-fluid py-2">
    <!-- Breadcrumb / Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-person-circle text-primary me-2"></i>Profil Guru</h4>
            <p class="text-muted mb-0">Kelola informasi akun, foto profil, dan QR Code kartu absensi Anda</p>
        </div>
        <div>
            <a href="<?= BASEURL; ?>/guru" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <?php Flasher::flash(); ?>

    <div class="row g-4">
        <!-- Kolom Kiri: Foto Profil & Kartu QR Barcode -->
        <div class="col-lg-4 col-md-5">
            <!-- Card Foto Profil -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-center mb-4">
                <div class="card-header bg-primary bg-gradient py-4 text-white text-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-camera me-1"></i> Foto Profil Guru</h6>
                </div>
                <div class="card-body p-4">
                    <div class="position-relative d-inline-block mb-3">
                        <?php if (!empty($p['foto'])): ?>
                            <img id="previewImgGuru" src="<?= BASEURL; ?>/uploads/profil/<?= htmlspecialchars($p['foto']); ?>" 
                                 alt="Foto Profil" 
                                 class="rounded-circle border border-3 border-primary shadow-sm" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div id="previewFallbackGuru" class="rounded-circle bg-light border border-3 border-secondary-subtle d-inline-flex align-items-center justify-content-center shadow-sm" 
                                 style="width: 150px; height: 150px;">
                                <i class="bi bi-person text-secondary" style="font-size: 75px;"></i>
                            </div>
                            <img id="previewImgGuru" src="" alt="Preview Foto" 
                                 class="rounded-circle border border-3 border-primary shadow-sm d-none" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['nama'] ?? $p['username']); ?></h5>
                    <p class="text-muted small mb-2"><i class="bi bi-book me-1"></i>Guru <?= htmlspecialchars($p['mata_pelajaran'] ?? '-'); ?></p>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill mb-3">
                        <i class="bi bi-check-circle me-1"></i> Tenaga Pengajar Aktif
                    </span>

                    <hr class="my-3">

                    <!-- Form Unggah Foto Sendiri -->
                    <form action="<?= BASEURL; ?>/guru/update_foto" method="POST" enctype="multipart/form-data" id="formUploadFotoGuru">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                        <div class="mb-3 text-start">
                            <label for="inputFotoGuru" class="form-label small fw-bold text-muted">
                                <i class="bi bi-upload me-1"></i> Ganti / Unggah Foto:
                            </label>
                            <input class="form-control form-control-sm" type="file" id="inputFotoGuru" name="foto" accept="image/png, image/jpeg, image/jpg, image/webp" required>
                            <div class="form-text text-muted" style="font-size: 11px;">
                                Format JPG, JPEG, PNG, atau WEBP. Maks 2MB.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold py-2">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Simpan Foto Profil
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card QR Barcode Absensi Guru -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-center mb-4">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-qr-code-scan me-2"></i>QR Code Barcode Saya</h6>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">Tunjukkan QR Code ini langsung dari layar ponsel Anda ke kamera terminal absensi guru.</p>
                    
                    <div class="p-3 bg-light rounded-4 border d-inline-block shadow-sm mb-3">
                        <div id="qrcodeProfilGuru" class="d-flex justify-content-center"></div>
                    </div>
                    
                    <div>
                        <span class="badge bg-dark px-3 py-2 fs-6 font-monospace mb-3">
                            <?= htmlspecialchars($p['barcode'] ?? '-'); ?>
                        </span>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?= BASEURL; ?>/guru/cetak_barcode" target="_blank" class="btn btn-primary btn-sm rounded-pill py-2 fw-semibold">
                            <i class="bi bi-printer me-1"></i> Cetak Kartu Absensi Guru
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-2" data-bs-toggle="modal" data-bs-target="#modalZoomQr">
                            <i class="bi bi-arrows-fullscreen me-1"></i> Perbesar Layar Penuh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Informasi & Form Kontak -->
        <div class="col-lg-8 col-md-7">
            <!-- Informasi Kepegawaian (Readonly) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-person-vcard me-2"></i>Informasi Kepegawaian</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                <tr class="border-bottom">
                                    <th width="30%" class="text-muted fw-normal"><i class="bi bi-person me-1"></i> Nama Lengkap</th>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama'] ?? '-'); ?></td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-at me-1"></i> Username Akun</th>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($p['username'] ?? '-'); ?></td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-credit-card-2-front me-1"></i> NIP</th>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['nip'] ?? '-'); ?></span></td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-book me-1"></i> Mata Pelajaran</th>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($p['mata_pelajaran'] ?? '-'); ?></td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-upc-scan me-1"></i> Kode Barcode Absensi</th>
                                    <td><code class="bg-light px-2 py-1 rounded text-primary fw-bold font-monospace"><?= htmlspecialchars($p['barcode'] ?? '-'); ?></code></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-normal"><i class="bi bi-shield-check me-1"></i> Status Akun</th>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Form Edit Kontak -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-telephone-inbound me-2"></i>Kontak Guru</h6>
                </div>
                <div class="card-body pt-0">
                    <form action="<?= BASEURL; ?>/guru/update_bio" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                        
                        <div class="mb-4">
                            <label for="no_hp" class="form-label small fw-bold text-muted">Nomor WhatsApp / HP Aktif</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                       placeholder="Contoh: 08123456789" 
                                       value="<?= htmlspecialchars($p['no_hp'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Kontak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Perbesar QR Code Guru -->
<div class="modal fade" id="modalZoomQr" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-qr-code me-2"></i>QR Code Presensi Guru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['nama'] ?? ''); ?></h5>
                <p class="text-muted small mb-3">NIP: <?= htmlspecialchars($p['nip'] ?? '-'); ?> • <?= htmlspecialchars($p['mata_pelajaran'] ?? ''); ?></p>
                
                <div class="p-3 bg-light rounded-4 border d-inline-block shadow-sm mb-3">
                    <div id="qrcodeZoomGuru" class="d-flex justify-content-center"></div>
                </div>

                <div>
                    <span class="badge bg-dark px-3 py-2 fs-6 font-monospace mb-2"><?= htmlspecialchars($p['barcode'] ?? '-'); ?></span>
                </div>
                <small class="text-muted d-block">Arahkan layar ponsel ini ke depan kamera terminal scanner absensi sekolah.</small>
            </div>
        </div>
    </div>
</div>

<!-- Include QRCode JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Preview upload foto guru
    const inputFoto = document.getElementById('inputFotoGuru');
    const previewImg = document.getElementById('previewImgGuru');
    const previewFallback = document.getElementById('previewFallbackGuru');

    if (inputFoto) {
        inputFoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ukuran Terlalu Besar',
                        text: 'Ukuran foto maksimal adalah 2MB. Silakan pilih foto lain.'
                    });
                    inputFoto.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImg.src = event.target.result;
                    previewImg.classList.remove('d-none');
                    if (previewFallback) {
                        previewFallback.classList.add('d-none');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 2. Render QR Code Profil Guru
    const barcodeVal = "<?= addslashes($p['barcode'] ?? ''); ?>";
    if (barcodeVal) {
        const qrEl = document.getElementById("qrcodeProfilGuru");
        if (qrEl) {
            new QRCode(qrEl, {
                text: barcodeVal,
                width: 160,
                height: 160,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        }

        const qrZoomEl = document.getElementById("qrcodeZoomGuru");
        if (qrZoomEl) {
            new QRCode(qrZoomEl, {
                text: barcodeVal,
                width: 220,
                height: 220,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        }
    }
});
</script>
