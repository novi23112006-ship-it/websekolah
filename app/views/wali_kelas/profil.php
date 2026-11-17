<?php $p = $data['profil']; ?>

<div class="container-fluid py-2">
    <!-- Breadcrumb / Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-person-badge text-primary me-2"></i>Profil Wali Kelas</h4>
            <p class="text-muted mb-0">Informasi profil akun, penugasan kelas, dan QR Code barcode absensi Anda</p>
        </div>
        <div>
            <a href="<?= BASEURL; ?>/wali_kelas" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <?php Flasher::flash(); ?>

    <div class="row g-4">
        <!-- Kolom Kiri: Foto Profil & Kartu QR Barcode -->
        <div class="col-lg-4 col-md-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-center mb-4">
                <div class="card-header bg-primary bg-gradient py-4 text-white text-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-camera me-1"></i> Foto Profil</h6>
                </div>
                <div class="card-body p-4">
                    <div class="position-relative d-inline-block mb-3">
                        <?php if (!empty($p['foto'])): ?>
                            <img id="previewImgWali" src="<?= BASEURL; ?>/uploads/profil/<?= htmlspecialchars($p['foto']); ?>" 
                                 alt="Foto Profil" 
                                 class="rounded-circle border border-3 border-primary shadow-sm" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div id="previewFallbackWali" class="rounded-circle bg-light border border-3 border-secondary-subtle d-inline-flex align-items-center justify-content-center shadow-sm" 
                                 style="width: 150px; height: 150px;">
                                <i class="bi bi-person text-secondary" style="font-size: 75px;"></i>
                            </div>
                            <img id="previewImgWali" src="" alt="Preview Foto" 
                                 class="rounded-circle border border-3 border-primary shadow-sm d-none" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['nama'] ?? $p['username']); ?></h5>
                    <p class="text-muted small mb-2"><i class="bi bi-award me-1"></i>Wali Kelas <?= htmlspecialchars($p['nama_kelas'] ?? '-'); ?></p>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill mb-3">
                        <i class="bi bi-check-circle me-1"></i> Akun Pengajar Aktif
                    </span>

                    <hr class="my-3">

                    <!-- Form Unggah Foto Sendiri -->
                    <form action="<?= BASEURL; ?>/wali_kelas/update_foto" method="POST" enctype="multipart/form-data" id="formUploadFotoWali">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                        <div class="mb-3 text-start">
                            <label for="inputFotoWali" class="form-label small fw-bold text-muted">
                                <i class="bi bi-upload me-1"></i> Ganti / Unggah Foto:
                            </label>
                            <input class="form-control form-control-sm" type="file" id="inputFotoWali" name="foto" accept="image/png, image/jpeg, image/jpg, image/webp" required>
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

            <!-- Card QR Barcode Absensi Wali Kelas -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-center mb-4">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-qr-code-scan me-2"></i>QR Code Barcode Saya</h6>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">Tunjukkan QR Code ini pada layar ponsel ke kamera scanner terminal guru.</p>
                    
                    <div class="p-3 bg-light rounded-4 border d-inline-block shadow-sm mb-3">
                        <div id="qrcodeProfilWali" class="d-flex justify-content-center"></div>
                    </div>
                    
                    <div>
                        <span class="badge bg-dark px-3 py-2 fs-6 font-monospace mb-3">
                            <?= htmlspecialchars($p['barcode'] ?? '-'); ?>
                        </span>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?= BASEURL; ?>/wali_kelas/cetak_barcode" target="_blank" class="btn btn-primary btn-sm rounded-pill py-2 fw-semibold">
                            <i class="bi bi-printer me-1"></i> Cetak Kartu Absensi
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-2" data-bs-toggle="modal" data-bs-target="#modalZoomQrWali">
                            <i class="bi bi-arrows-fullscreen me-1"></i> Perbesar Layar Penuh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Informasi -->
        <div class="col-lg-8 col-md-7">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-person-lines-fill me-2"></i>Informasi Akun & Penugasan</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                <tr class="border-bottom">
                                    <th width="32%" class="text-muted fw-normal"><i class="bi bi-person me-1"></i> Nama Lengkap</th>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama'] ?? '-'); ?></td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-at me-1"></i> Username Akun</th>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($p['username'] ?? '-'); ?></td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-shield-lock me-1"></i> Peran (Role)</th>
                                    <td><span class="badge bg-primary">Wali Kelas</span></td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-credit-card-2-front me-1"></i> NIP</th>
                                    <td><?= htmlspecialchars($p['nip'] ?? '-'); ?></td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-building me-1"></i> Kelas Diampu</th>
                                    <td>
                                        <?php if (!empty($p['nama_kelas'])): ?>
                                            <span class="badge bg-success fs-6"><i class="bi bi-check2 me-1"></i>Kelas <?= htmlspecialchars($p['nama_kelas']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Belum ter-assign kelas</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-book me-1"></i> Mata Pelajaran</th>
                                    <td><?= htmlspecialchars($p['mata_pelajaran'] ?? '-'); ?></td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-upc-scan me-1"></i> Kode Barcode Absensi</th>
                                    <td><code class="bg-light px-2 py-1 rounded text-primary fw-bold font-monospace"><?= htmlspecialchars($p['barcode'] ?? '-'); ?></code></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-normal"><i class="bi bi-telephone me-1"></i> Nomor HP / WA</th>
                                    <td><?= htmlspecialchars($p['no_hp'] ?? '-'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Perbesar QR Code Wali Kelas -->
<div class="modal fade" id="modalZoomQrWali" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-qr-code me-2"></i>QR Code Presensi Guru & Wali Kelas</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['nama'] ?? ''); ?></h5>
                <p class="text-muted small mb-3">NIP: <?= htmlspecialchars($p['nip'] ?? '-'); ?> • <?= htmlspecialchars($p['nama_kelas'] ? ('Wali Kelas ' . $p['nama_kelas']) : 'Wali Kelas'); ?></p>
                
                <div class="p-3 bg-light rounded-4 border d-inline-block shadow-sm mb-3">
                    <div id="qrcodeZoomWali" class="d-flex justify-content-center"></div>
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
    const inputFoto = document.getElementById('inputFotoWali');
    const previewImg = document.getElementById('previewImgWali');
    const previewFallback = document.getElementById('previewFallbackWali');

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

    // Render QR Code Profil Wali Kelas
    const barcodeVal = "<?= addslashes($p['barcode'] ?? ''); ?>";
    if (barcodeVal) {
        const qrEl = document.getElementById("qrcodeProfilWali");
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

        const qrZoomEl = document.getElementById("qrcodeZoomWali");
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
