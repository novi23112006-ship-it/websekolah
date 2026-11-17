<?php $p = $data['profil']; ?>

<div class="container-fluid py-2">
    <!-- Breadcrumb / Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-person-workspace text-primary me-2"></i>Profil Kepala Sekolah</h4>
            <p class="text-muted mb-0">Informasi profil akun dan foto resmi pimpinan sekolah</p>
        </div>
        <div>
            <a href="<?= BASEURL; ?>/kepala_sekolah" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    <?php Flasher::flash(); ?>

    <div class="row g-4">
        <!-- Kolom Kiri: Foto Profil -->
        <div class="col-lg-4 col-md-5">
            <!-- Card Foto Profil -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-center mb-4">
                <div class="card-header bg-primary bg-gradient py-4 text-white text-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-camera me-1"></i> Foto Profil</h6>
                </div>
                <div class="card-body p-4">
                    <div class="position-relative d-inline-block mb-3">
                        <?php if (!empty($p['foto'])): ?>
                            <img id="previewImgKepsek" src="<?= BASEURL; ?>/uploads/profil/<?= htmlspecialchars($p['foto']); ?>" 
                                 alt="Foto Profil" 
                                 class="rounded-circle border border-3 border-primary shadow-sm" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div id="previewFallbackKepsek" class="rounded-circle bg-light border border-3 border-secondary-subtle d-inline-flex align-items-center justify-content-center shadow-sm" 
                                 style="width: 150px; height: 150px;">
                                 <i class="bi bi-person text-secondary" style="font-size: 75px;"></i>
                            </div>
                            <img id="previewImgKepsek" src="" alt="Preview Foto" 
                                 class="rounded-circle border border-3 border-primary shadow-sm d-none" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['nama'] ?? $p['username']); ?></h5>
                    <p class="text-muted small mb-2"><i class="bi bi-shield-check me-1"></i>Kepala Sekolah</p>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill mb-3">
                        <i class="bi bi-check-circle me-1"></i> Akun Kepemimpinan Aktif
                    </span>

                    <hr class="my-3">

                    <!-- Form Unggah Foto Sendiri -->
                    <form action="<?= BASEURL; ?>/kepala_sekolah/update_foto" method="POST" enctype="multipart/form-data" id="formUploadFotoKepsek">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                        <div class="mb-3 text-start">
                            <label for="inputFotoKepsek" class="form-label small fw-bold text-muted">
                                <i class="bi bi-upload me-1"></i> Ganti / Unggah Foto:
                            </label>
                            <input class="form-control form-control-sm" type="file" id="inputFotoKepsek" name="foto" accept="image/png, image/jpeg, image/jpg, image/webp" required>
                            <div class="form-text text-muted" style="font-size: 11px;">
                                Format JPG, JPEG, PNG, atau WEBP. Maks 2MB.
                            </div>
                        </div>
                        <div class="d-flex flex-column gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold py-2" id="btnSimpanFotoKepsek">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Simpan Foto Profil
                            </button>
                            <?php if (!empty($p['foto'])): ?>
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-pill fw-semibold py-2" id="btnHapusFotoKepsek">
                                    <i class="bi bi-trash3 me-1"></i> Hapus Foto Saat Ini
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if (!empty($p['foto'])): ?>
                    <form action="<?= BASEURL; ?>/kepala_sekolah/hapus_foto" method="POST" id="formHapusFotoKepsek" class="d-none">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Detail Informasi -->
        <div class="col-lg-8 col-md-7">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-person-lines-fill me-2"></i>Informasi Akun Kepemimpinan</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                <tr class="border-bottom">
                                    <th width="32%" class="text-muted fw-normal"><i class="bi bi-person me-1"></i> Nama Lengkap</th>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($p['nama'] ?? 'Kepala Sekolah'); ?></td>
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
                                    <th class="text-muted fw-normal"><i class="bi bi-shield-lock me-1"></i> Peran (Role)</th>
                                    <td><span class="badge bg-primary">Kepala Sekolah</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-normal"><i class="bi bi-check2-circle me-1"></i> Status Akun</th>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputFoto = document.getElementById('inputFotoKepsek');
    const previewImg = document.getElementById('previewImgKepsek');
    const previewFallback = document.getElementById('previewFallbackKepsek');

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

                const btnSimpan = document.getElementById('btnSimpanFotoKepsek');
                if (btnSimpan) {
                    btnSimpan.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simpan Foto Baru';
                }
            }
        });
    }

    const btnHapusFotoKepsek = document.getElementById('btnHapusFotoKepsek');
    const formHapusFotoKepsek = document.getElementById('formHapusFotoKepsek');
    if (btnHapusFotoKepsek && formHapusFotoKepsek) {
        btnHapusFotoKepsek.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Hapus Foto Profil?',
                text: 'Foto profil Kepala Sekolah akan dihapus dan dikembalikan ke avatar default.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    formHapusFotoKepsek.submit();
                }
            });
        });
    }
});
</script>
