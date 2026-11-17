<?php $p = $data['profil']; ?>

<div class="container-fluid py-2">
    <!-- Breadcrumb / Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-person-circle text-primary me-2"></i>Profil Siswa</h4>
            <p class="text-muted mb-0">Kelola informasi akun dan foto profil Anda sendiri</p>
        </div>
        <div>
            <a href="<?= BASEURL; ?>/siswa" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <?php Flasher::flash(); ?>

    <div class="row g-4">
        <!-- Kolom Kiri: Foto Profil & Form Upload Foto -->
        <div class="col-lg-4 col-md-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-center mb-4">
                <div class="card-header bg-primary bg-gradient py-4 text-white text-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-camera me-1"></i> Foto Profil Anda</h6>
                </div>
                <div class="card-body p-4">
                    <div class="position-relative d-inline-block mb-3">
                        <?php if (!empty($p['foto'])): ?>
                            <img id="previewImg" src="<?= BASEURL; ?>/uploads/profil/<?= htmlspecialchars($p['foto']); ?>" 
                                 alt="Foto Profil" 
                                 class="rounded-circle border border-3 border-primary shadow-sm" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div id="previewFallback" class="rounded-circle bg-light border border-3 border-secondary-subtle d-inline-flex align-items-center justify-content-center shadow-sm" 
                                 style="width: 150px; height: 150px;">
                                <i class="bi bi-person text-secondary" style="font-size: 75px;"></i>
                            </div>
                            <img id="previewImg" src="" alt="Preview Foto" 
                                 class="rounded-circle border border-3 border-primary shadow-sm d-none" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['nama'] ?? $p['username']); ?></h5>
                    <p class="text-muted small mb-2"><i class="bi bi-mortarboard me-1"></i>Kelas <?= htmlspecialchars($p['nama_kelas'] ?? '-'); ?></p>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill mb-3">
                        <i class="bi bi-check-circle me-1"></i> Siswa Aktif
                    </span>

                    <hr class="my-3">

                    <!-- Form Unggah Foto Sendiri -->
                    <form action="<?= BASEURL; ?>/siswa/update_foto" method="POST" enctype="multipart/form-data" id="formUploadFoto">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                        <div class="mb-3 text-start">
                            <label for="inputFoto" class="form-label small fw-bold text-muted">
                                <i class="bi bi-upload me-1"></i> Pilih Foto Baru:
                            </label>
                            <input class="form-control form-control-sm" type="file" id="inputFoto" name="foto" accept="image/png, image/jpeg, image/jpg, image/webp" required>
                            <div class="form-text text-muted" style="font-size: 11px;">
                                Format JPG, JPEG, PNG, atau WEBP. Maks 2MB.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold py-2" id="btnSimpanFoto">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Simpan Foto Profil
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Informasi & Form Kontak -->
        <div class="col-lg-8 col-md-7">
            <!-- Informasi Akademik (Readonly) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-person-vcard me-2"></i>Informasi Akademik & Identitas</h6>
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
                                    <th class="text-muted fw-normal"><i class="bi bi-credit-card-2-front me-1"></i> NIS / NISN</th>
                                    <td>
                                        <span class="badge bg-light text-dark border me-1">NIS: <?= htmlspecialchars($p['nis'] ?? '-'); ?></span>
                                        <span class="badge bg-light text-dark border">NISN: <?= htmlspecialchars($p['nisn'] ?? '-'); ?></span>
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-building me-1"></i> Kelas</th>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama_kelas'] ?? '-'); ?></td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-normal"><i class="bi bi-gender-ambiguous me-1"></i> Jenis Kelamin</th>
                                    <td>
                                        <?php if (($p['jenis_kelamin'] ?? '') === 'L'): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-gender-male me-1"></i>Laki-laki</span>
                                        <?php elseif (($p['jenis_kelamin'] ?? '') === 'P'): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-gender-female me-1"></i>Perempuan</span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-normal"><i class="bi bi-upc-scan me-1"></i> Kode Barcode</th>
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded text-primary fw-bold"><?= htmlspecialchars($p['barcode'] ?? '-'); ?></code>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Form Edit Kontak & Biodata -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-telephone-inbound me-2"></i>Kontak & Alamat Domisili</h6>
                </div>
                <div class="card-body pt-0">
                    <form action="<?= BASEURL; ?>/siswa/update_bio" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                        
                        <div class="mb-3">
                            <label for="no_hp" class="form-label small fw-bold text-muted">Nomor WhatsApp / HP</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" 
                                       placeholder="Contoh: 08123456789" 
                                       value="<?= htmlspecialchars($p['no_hp'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="alamat" class="form-label small fw-bold text-muted">Alamat Lengkap Tempat Tinggal</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                      placeholder="Masukkan alamat domisili lengkap..."><?= htmlspecialchars($p['alamat'] ?? ''); ?></textarea>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputFoto = document.getElementById('inputFoto');
    const previewImg = document.getElementById('previewImg');
    const previewFallback = document.getElementById('previewFallback');

    if (inputFoto) {
        inputFoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validasi ukuran
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
});
</script>
