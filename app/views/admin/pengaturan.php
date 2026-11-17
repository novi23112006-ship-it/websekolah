<?php 
$admin = $data['admin']; 
$lokasi = $data['lokasi'] ?? [];
?>

<style>
/* Styling Navigasi Tab Pengaturan */
#settingsTab .list-group-item {
    border: none;
    border-bottom: 1px solid var(--border-subtle, #f0f0f0);
    transition: all 0.2s ease-in-out;
}
#settingsTab .list-group-item:last-child {
    border-bottom: none;
}
#settingsTab .list-group-item:hover:not(.active) {
    background-color: var(--color-primary-subtle, #eff6ff);
}
#settingsTab .list-group-item.active {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    border-color: transparent !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}
#settingsTab .list-group-item.active i,
#settingsTab .list-group-item.active .fw-bold,
#settingsTab .list-group-item.active small,
#settingsTab .list-group-item.active .text-muted {
    color: #ffffff !important;
    opacity: 0.95;
}
#settingsTab .list-group-item.active .badge {
    background-color: rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.4);
}
</style>

<!-- Flash Alert -->
<div class="mb-3">
    <?php Flasher::flash(); ?>
</div>

<!-- Header Page -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-gear-wide-connected text-primary me-2"></i>Pengaturan Akun & Sistem</h4>
        <p class="text-muted mb-0 small">Kelola identitas akun administrator, keamanan kata sandi, preferensi tema terang/gelap, dan pengawasan akun</p>
    </div>
    <div>
        <a href="<?= BASEURL; ?>/admin" class="btn btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Profil Singkat & Navigasi Tab -->
    <div class="col-lg-4 col-md-5">
        <!-- Card Ringkasan Akun -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 text-center">
            <div class="card-header bg-primary bg-gradient py-4 text-white">
                <div class="position-relative d-inline-block">
                    <?php if (!empty($admin['foto'])): ?>
                        <img src="<?= BASEURL; ?>/uploads/profil/<?= htmlspecialchars($admin['foto']); ?>" 
                             alt="Foto Admin" class="rounded-circle border border-3 border-white shadow-sm" 
                             style="width: 100px; height: 100px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center shadow-sm" 
                             style="width: 100px; height: 100px; font-size: 45px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold text-white mb-0 mt-3"><?= htmlspecialchars($admin['nama'] ?? 'Administrator'); ?></h5>
                <span class="badge bg-white text-primary rounded-pill px-3 py-1 mt-1 font-monospace">@<?= htmlspecialchars($admin['username'] ?? 'admin'); ?></span>
            </div>
            <div class="card-body p-3">
                <div class="d-flex justify-content-around py-2 border-bottom">
                    <div>
                        <span class="text-muted small d-block">Hak Akses</span>
                        <span class="badge bg-dark mt-1">Super Admin</span>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Status Akun</span>
                        <span class="badge bg-success mt-1">Aktif & Terverifikasi</span>
                    </div>
                </div>
                <div class="pt-3 text-muted" style="font-size: 11px;">
                    <i class="bi bi-clock-history me-1"></i>Terdaftar sejak: <?= date('d M Y', strtotime($admin['created_at'] ?? 'now')) ?>
                </div>
            </div>
        </div>

        <!-- Tab Navigasi List Group -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="list-group list-group-flush rounded-4" id="settingsTab" role="tablist">
                <button class="list-group-item list-group-item-action active p-3 d-flex align-items-center gap-3" id="tab-profil-btn" data-bs-toggle="pill" data-bs-target="#tab-profil" type="button" role="tab">
                    <i class="bi bi-person-vcard fs-5 text-primary"></i>
                    <div>
                        <span class="fw-bold d-block">Identitas & Foto</span>
                        <small class="text-muted" style="font-size: 11px;">Nama lengkap, username & foto profil</small>
                    </div>
                </button>
                <button class="list-group-item list-group-item-action p-3 d-flex align-items-center gap-3" id="tab-keamanan-btn" data-bs-toggle="pill" data-bs-target="#tab-keamanan" type="button" role="tab">
                    <i class="bi bi-shield-lock fs-5 text-danger"></i>
                    <div>
                        <span class="fw-bold d-block">Keamanan & Password</span>
                        <small class="text-muted" style="font-size: 11px;">Ganti kata sandi akun admin</small>
                    </div>
                </button>
                <button class="list-group-item list-group-item-action p-3 d-flex align-items-center gap-3" id="tab-tema-btn" data-bs-toggle="pill" data-bs-target="#tab-tema" type="button" role="tab">
                    <i class="bi bi-palette fs-5 text-warning"></i>
                    <div>
                        <span class="fw-bold d-block">Tema Tampilan (Terang / Gelap)</span>
                        <small class="text-muted" style="font-size: 11px;">Pilihan mode malam atau terang</small>
                    </div>
                </button>
                <button class="list-group-item list-group-item-action p-3 d-flex align-items-center gap-3" id="tab-users-btn" data-bs-toggle="pill" data-bs-target="#tab-users" type="button" role="tab">
                    <i class="bi bi-people fs-5 text-success"></i>
                    <div>
                        <span class="fw-bold d-block">Monitoring Akun Pengguna</span>
                        <small class="text-muted" style="font-size: 11px;">Daftar akun siswa, guru, kepsek</small>
                    </div>
                </button>
                <button class="list-group-item list-group-item-action p-3 d-flex align-items-center gap-3" id="tab-lokasi-btn" data-bs-toggle="pill" data-bs-target="#tab-lokasi" type="button" role="tab">
                    <i class="bi bi-geo-alt-fill fs-5 text-danger"></i>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold">Lokasi & Radius Absen</span>
                            <span class="badge bg-danger rounded-pill" style="font-size: 10px;">Maks 100m</span>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Koordinat GPS & batas radius 100 meter</small>
                    </div>
                </button>
                <button class="list-group-item list-group-item-action p-3 d-flex align-items-center gap-3" id="tab-chatbot-btn" data-bs-toggle="pill" data-bs-target="#tab-chatbot" type="button" role="tab">
                    <i class="bi bi-robot fs-5 text-primary"></i>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold">API Key Chatbot AI</span>
                            <?php if (!empty($data['chatbot']['is_configured'])): ?>
                                <span class="badge bg-success rounded-pill" style="font-size: 10px;">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill" style="font-size: 10px;">Belum Diatur</span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Google Gemini API & Uji Koneksi</small>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Tab Content -->
    <div class="col-lg-8 col-md-7">
        <div class="tab-content" id="settingsTabContent">
            
            <!-- ══════════ TAB 1: IDENTITAS & FOTO AKUN ══════════ -->
            <div class="tab-pane fade show active" id="tab-profil" role="tabpanel">
                <!-- Form Update Identitas -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-person-gear me-2 text-primary"></i>Perbarui Identitas Akun</h6>
                    </div>
                    <div class="card-body pt-0">
                        <form action="<?= BASEURL; ?>/admin/update_akun" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                            
                            <div class="mb-3">
                                <label for="nama" class="form-label small fw-bold text-muted">Nama Lengkap Administrator</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="nama" name="nama" 
                                           value="<?= htmlspecialchars($admin['nama'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="username" class="form-label small fw-bold text-muted">Username Login</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">@</span>
                                    <input type="text" class="form-control font-monospace" id="username" name="username" 
                                           value="<?= htmlspecialchars($admin['username'] ?? '') ?>" required>
                                </div>
                                <div class="form-text text-muted" style="font-size: 11px;">
                                    Username digunakan untuk masuk ke portal sistem.
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                    <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Identitas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Form Upload / Ganti / Hapus Foto Profil -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-camera me-2 text-primary"></i>Foto Profil Akun</h6>
                        <?php if (!empty($admin['foto'])): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small">
                                <i class="bi bi-check-circle me-1"></i>Foto Tersedia
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1 small">
                                <i class="bi bi-person me-1"></i>Avatar Default
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body pt-0">
                        <form action="<?= BASEURL; ?>/admin/update_foto" method="POST" enctype="multipart/form-data" id="formUpdateFotoAdmin">
                            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                            <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/admin/pengaturan">

                            <div class="row align-items-center g-3 mb-3">
                                <div class="col-auto">
                                    <div class="position-relative">
                                        <?php if (!empty($admin['foto'])): ?>
                                            <img id="imgPreviewAdmin" src="<?= BASEURL; ?>/uploads/profil/<?= htmlspecialchars($admin['foto']); ?>" 
                                                 class="rounded-circle border border-3 border-primary shadow-sm" 
                                                 style="width: 78px; height: 78px; object-fit: cover;">
                                        <?php else: ?>
                                            <div id="fallbackPreviewAdmin" class="rounded-circle bg-light border border-3 border-secondary-subtle d-flex align-items-center justify-content-center shadow-sm" 
                                                 style="width: 78px; height: 78px;">
                                                <i class="bi bi-person text-secondary fs-1"></i>
                                            </div>
                                            <img id="imgPreviewAdmin" src="" class="rounded-circle border border-3 border-primary shadow-sm d-none" 
                                                 style="width: 78px; height: 78px; object-fit: cover;">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col">
                                    <label for="inputFotoSettings" class="form-label small fw-bold text-dark mb-1 d-flex align-items-center justify-content-between">
                                        <span>Pilih Berkas Foto Baru:</span>
                                        <small class="text-muted fw-normal">JPG, PNG, WEBP (Maks 2MB)</small>
                                    </label>
                                    <input type="file" class="form-control" id="inputFotoSettings" name="foto" accept="image/png, image/jpeg, image/jpg, image/webp" required>
                                    <div class="form-text text-muted mt-1" style="font-size: 11px;">
                                        Foto ini akan tampil di sudut navbar atas dan kartu profil administrator.
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
                                <div>
                                    <?php if (!empty($admin['foto'])): ?>
                                        <button type="button" class="btn btn-outline-danger rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2" id="btnHapusFotoAdmin">
                                            <i class="bi bi-trash3"></i>
                                            <span>Hapus Foto</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm" id="btnSimpanFotoAdmin">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                        <span>Simpan Foto Profil</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <?php if (!empty($admin['foto'])): ?>
                        <!-- Form Tersembunyi untuk Hapus Foto -->
                        <form action="<?= BASEURL; ?>/admin/hapus_foto" method="POST" id="formHapusFotoAdmin" class="d-none">
                            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                            <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/admin/pengaturan">
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ══════════ TAB 2: KEAMANAN & PASSWORD ══════════ -->
            <div class="tab-pane fade" id="tab-keamanan" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold text-danger mb-0"><i class="bi bi-key-fill me-2"></i>Ganti Kata Sandi (Password)</h6>
                    </div>
                    <div class="card-body pt-0">
                        <form action="<?= BASEURL; ?>/admin/ganti_password" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">

                            <div class="mb-3">
                                <label for="password_lama" class="form-label small fw-bold text-muted">Password Saat Ini <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control pwd-input" id="password_lama" name="password_lama" placeholder="Masukkan password saat ini" required>
                                    <button class="btn btn-outline-secondary btnToggleEye" type="button"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password_baru" class="form-label small fw-bold text-muted">Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" class="form-control pwd-input" id="password_baru" name="password_baru" placeholder="Minimal 6 karakter" required minlength="6">
                                    <button class="btn btn-outline-secondary btnToggleEye" type="button"><i class="bi bi-eye"></i></button>
                                </div>
                                <div class="form-text text-muted" style="font-size: 11px;">Gunakan kombinasi huruf, angka, dan simbol untuk keamanan maksimal.</div>
                            </div>

                            <div class="mb-4">
                                <label for="konfirmasi_password" class="form-label small fw-bold text-muted">Ulangi Password Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-shield-check"></i></span>
                                    <input type="password" class="form-control pwd-input" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password baru" required minlength="6">
                                    <button class="btn btn-outline-secondary btnToggleEye" type="button"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold">
                                    <i class="bi bi-check2-shield me-1"></i> Perbarui Kata Sandi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ══════════ TAB 3: TEMA TAMPILAN TERANG / GELAP ══════════ -->
            <div class="tab-pane fade" id="tab-tema" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-palette-fill me-2 text-warning"></i>Preferensi Tema Antarmuka</h6>
                    </div>
                    <div class="card-body pt-0">
                        <p class="text-muted small">Pilih tema tampilan yang paling nyaman untuk mata Anda saat mengelola data absensi.</p>
                        
                        <div class="row g-3">
                            <!-- Card Mode Terang -->
                            <div class="col-md-6">
                                <div class="card border rounded-4 p-3 h-100 theme-selector-card cursor-pointer" id="selectThemeLight" style="cursor: pointer;">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-sun-fill text-warning fs-3"></i>
                                            <h6 class="fw-bold mb-0 text-dark">Mode Terang (Light)</h6>
                                        </div>
                                        <span class="badge bg-light text-muted border check-indicator" id="checkLight"><i class="bi bi-check-lg"></i></span>
                                    </div>
                                    <p class="text-muted small mb-3" style="font-size: 12px;">Tampilan bersih dan kontras tinggi dengan latar putih elegan.</p>
                                    <div class="p-2 rounded bg-light border border-dashed text-center small text-muted">
                                        <span class="badge bg-primary me-1">Primary</span>
                                        <span class="badge bg-success me-1">Success</span>
                                        <span class="badge bg-dark">Dark</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Mode Gelap -->
                            <div class="col-md-6">
                                <div class="card border rounded-4 p-3 h-100 theme-selector-card cursor-pointer bg-dark text-white" id="selectThemeDark" style="cursor: pointer;">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-moon-stars-fill text-info fs-3"></i>
                                            <h6 class="fw-bold mb-0 text-white">Mode Gelap (Dark)</h6>
                                        </div>
                                        <span class="badge bg-secondary text-white border check-indicator" id="checkDark"><i class="bi bi-check-lg"></i></span>
                                    </div>
                                    <p class="text-white-50 small mb-3" style="font-size: 12px;">Mengurangi ketegangan mata saat bekerja di malam hari dan menghemat baterai.</p>
                                    <div class="p-2 rounded bg-secondary bg-opacity-25 border border-secondary text-center small">
                                        <span class="badge bg-primary me-1">Primary</span>
                                        <span class="badge bg-success me-1">Success</span>
                                        <span class="badge bg-light text-dark">Light</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-3 rounded-4 bg-light border d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle text-primary fs-5"></i>
                                <small class="text-muted">Tema yang Anda pilih akan otomatis tersimpan di peramban ini.</small>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="btnSimpanTema">
                                <i class="bi bi-check2"></i> Terapkan Sekarang
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Informasi Lingkungan Server -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-hdd-network me-2 text-primary"></i>Informasi Lingkungan Sistem</h6>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-2" style="font-size: 12px;">
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Versi PHP:</span>
                                <strong><?= phpversion() ?></strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Web Server:</span>
                                <strong><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Apache') ?></strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Waktu Server:</span>
                                <strong><?= date('d M Y, H:i:s') ?></strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Status Database:</span>
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Terhubung</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════ TAB 4: MONITORING AKUN PENGGUNA ══════════ -->
            <div class="tab-pane fade" id="tab-users" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill me-2 text-success"></i>Daftar Akun Pengguna Sistem</h6>
                        <span class="badge bg-light text-muted border"><?= count($data['users_list']) ?> Akun Ditampilkan</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">No</th>
                                    <th width="35%">Nama Lengkap & Username</th>
                                    <th width="20%">Role / Peran</th>
                                    <th width="15%">Status</th>
                                    <th width="25%">Terdaftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($data['users_list'] as $u): ?>
                                    <tr>
                                        <td class="text-center text-muted"><?= $no++ ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if (!empty($u['foto'])): ?>
                                                    <img src="<?= BASEURL; ?>/uploads/profil/<?= htmlspecialchars($u['foto']); ?>" class="rounded-circle border" style="width: 28px; height: 28px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;">
                                                        <i class="bi bi-person-fill"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($u['nama'] ?? $u['username']) ?></span>
                                                    <code class="text-muted" style="font-size: 11px;">@<?= htmlspecialchars($u['username']) ?></code>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                                $roleBadges = [
                                                    'admin' => 'bg-dark text-white',
                                                    'guru' => 'bg-primary',
                                                    'siswa' => 'bg-info text-dark',
                                                    'wali_kelas' => 'bg-success',
                                                    'kepala_sekolah' => 'bg-warning text-dark'
                                                ];
                                                $rBadge = $roleBadges[$u['role']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?= $rBadge ?>" style="font-size: 10px;">
                                                <?= ucfirst(str_replace('_', ' ', $u['role'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($u['status'] === 'active'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 10px;">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted small">
                                            <?= date('d M Y, H:i', strtotime($u['created_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ══════════ TAB 5: LOKASI & RADIUS ABSENSI (100 METER) ══════════ -->
            <div class="tab-pane fade" id="tab-lokasi" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-geo-alt-fill me-2 text-danger"></i>Titik Lokasi & Batasan Jarak Absensi</h6>
                            <p class="text-muted small mb-0">Atur titik koordinat sekolah dan batas radius absensi guru & siswa (Maksimal 100 meter).</p>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill fs-6 fw-bold">
                            <i class="bi bi-shield-lock me-1"></i>Radius Maksimal 100m
                        </span>
                    </div>
                    <div class="card-body pt-0">
                        <!-- Alert Ketentuan 100 Meter -->
                        <div class="alert alert-info border-0 rounded-4 p-3 mb-4 d-flex align-items-start gap-3">
                            <div class="p-2 bg-info bg-opacity-25 rounded-circle text-info">
                                <i class="bi bi-info-circle-fill fs-4"></i>
                            </div>
                            <div class="small">
                                <strong class="d-block mb-1 text-dark">Ketentuan Geofencing Absensi:</strong>
                                Siswa dan guru hanya dapat melakukan absensi jika perangkat mereka berada dalam radius <strong>maksimal 100 meter</strong> dari titik koordinat sekolah. Jika jarak melebihi 100 meter, absensi akan <strong>otomatis ditolak</strong> oleh sistem dengan notifikasi peringatan.
                            </div>
                        </div>

                        <!-- Card Live Preview Lokasi Saat Ini -->
                        <div class="card border rounded-4 bg-light p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <span class="fw-bold small text-dark"><i class="bi bi-broadcast me-1 text-primary"></i>Pendeteksi GPS Admin Saat Ini</span>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnAmbilLokasiGPS">
                                    <i class="bi bi-crosshair me-1"></i>Ambil Koordinat Saya Sekarang
                                </button>
                            </div>
                            <div id="liveAdminGpsStatus" class="small text-muted">
                                <i class="bi bi-geo me-1"></i>Klik "Ambil Koordinat Saya Sekarang" untuk mengisi otomatis Latitude & Longitude dari GPS Anda.
                            </div>
                            <div id="liveDistanceComparison" class="mt-2 pt-2 border-top d-none small">
                                <span>Perkiraan jarak dari perangkat Anda ke titik sekolah: </span>
                                <strong id="valLiveDistance" class="badge bg-secondary">-</strong>
                            </div>
                        </div>

                        <!-- Form Pengaturan Lokasi -->
                        <form action="<?= BASEURL; ?>/admin/update_lokasi" method="POST" id="formPengaturanLokasi">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>">

                            <div class="mb-3">
                                <label for="nama_sekolah_lokasi" class="form-label small fw-bold text-muted">Nama Lokasi / Sekolah</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                    <input type="text" class="form-control" id="nama_sekolah_lokasi" name="nama_sekolah" 
                                           value="<?= htmlspecialchars($lokasi['nama_sekolah'] ?? 'SMK Negeri'); ?>" required>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="latitude_lokasi" class="form-label small fw-bold text-muted">Latitude Titik Sekolah <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-compass"></i></span>
                                        <input type="number" step="any" class="form-control" id="latitude_lokasi" name="latitude" 
                                               value="<?= htmlspecialchars($lokasi['latitude'] ?? -6.208763); ?>" required placeholder="-6.208763">
                                    </div>
                                    <div class="form-text text-muted" style="font-size: 11px;">Garis lintang titik tengah gerbang / gedung sekolah.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="longitude_lokasi" class="form-label small fw-bold text-muted">Longitude Titik Sekolah <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-compass"></i></span>
                                        <input type="number" step="any" class="form-control" id="longitude_lokasi" name="longitude" 
                                               value="<?= htmlspecialchars($lokasi['longitude'] ?? 106.845599); ?>" required placeholder="106.845599">
                                    </div>
                                    <div class="form-text text-muted" style="font-size: 11px;">Garis bujur titik tengah gerbang / gedung sekolah.</div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="radius_meter_lokasi" class="form-label small fw-bold text-muted">
                                        Batas Radius Absensi (Meter) <span class="badge bg-danger ms-1">Maks 100m</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-arrows-fullscreen"></i></span>
                                        <input type="number" class="form-control fw-bold" id="radius_meter_lokasi" name="radius_meter" 
                                               value="<?= (int)($lokasi['radius_meter'] ?? 100); ?>" min="10" max="100" required>
                                        <span class="input-group-text">Meter</span>
                                    </div>
                                    <div class="form-text text-danger" style="font-size: 11px;">
                                        <i class="bi bi-shield-exclamation me-1"></i>Sesuai aturan, batasan jarak maksimal adalah <strong>100 meter</strong> (tidak boleh lebih).
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted">Status Validasi Geofencing</label>
                                    <div class="p-2 border rounded-3 bg-light">
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" role="switch" id="geofence_aktif" name="geofence_aktif" value="1" <?= (!isset($lokasi['geofence_aktif']) || $lokasi['geofence_aktif']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label fw-semibold small" for="geofence_aktif">
                                                Aktifkan Pembatasan Radius 100 Meter
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-text text-muted" style="font-size: 11px;">Jika aktif, absensi hanya diterima di dalam radius 100 meter.</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top">
                                <div class="text-muted small">
                                    <?php if (!empty($lokasi['updated_at'])): ?>
                                        <i class="bi bi-clock-history me-1"></i>Terakhir diperbarui: <?= htmlspecialchars($lokasi['updated_at']); ?>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="btnSimpanLokasi">
                                    <i class="bi bi-save me-1"></i> Simpan Titik & Radius Lokasi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ══════════ TAB 6: MULTI-PROVIDER AI CHATBOT ══════════ -->
            <div class="tab-pane fade" id="tab-chatbot" role="tabpanel">
                <!-- Status Banner Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                                    <i class="bi bi-robot fs-2"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-1 text-dark">Integrasi Multi-Provider AI Chatbot</h5>
                                    <p class="text-muted small mb-0">Asisten cerdas interaktif bertenaga Multi-Provider AI (Google Gemini, OpenAI, Custom/Groq/DeepSeek) untuk absensi & informasi sekolah.</p>
                                </div>
                            </div>
                            <div>
                                <?php if (!empty($data['chatbot']['is_configured']) && !empty($data['chatbot']['is_active'])): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fs-6 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-check-circle-fill"></i> Chatbot AI Aktif
                                    </span>
                                <?php elseif (!empty($data['chatbot']['is_configured'])): ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill fs-6 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-pause-circle-fill"></i> Dinonaktifkan Admin
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-pill fs-6 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-x-circle"></i> Belum Dikonfigurasi
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-3 opacity-10">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 bg-light bg-opacity-75 rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                                        <i class="bi bi-hdd-network-fill text-primary"></i>
                                        <span>Penyedia AI Aktif</span>
                                    </div>
                                    <div class="fw-bold text-dark fs-6" id="labelActiveProvider">
                                        <?= htmlspecialchars($data['chatbot']['provider_label'] ?? 'Google Gemini AI') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light bg-opacity-75 rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                                        <i class="bi bi-shield-lock-fill text-warning"></i>
                                        <span>Kunci API Aktif</span>
                                    </div>
                                    <div class="fw-bold text-dark font-monospace text-truncate" id="labelMaskedApiKey" title="<?= htmlspecialchars($data['chatbot']['api_key_masked'] ?? 'Belum Diatur') ?>" style="font-size: 13.5px;">
                                        <?= htmlspecialchars($data['chatbot']['api_key_masked'] ?? 'Belum Diatur') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light bg-opacity-75 rounded-3 border h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center gap-2 mb-2 text-muted small">
                                        <i class="bi bi-robot text-success"></i>
                                        <span>Status Layanan Chatbot</span>
                                    </div>
                                    <div class="fw-bold <?= !empty($data['chatbot']['is_active']) ? 'text-success' : 'text-secondary' ?>" style="font-size: 13.5px;">
                                        <i class="bi <?= !empty($data['chatbot']['is_active']) ? 'bi-check-circle-fill text-success' : 'bi-x-circle text-muted' ?> me-1"></i>
                                        <?= !empty($data['chatbot']['is_active']) ? 'Aktif & Siap Digunakan' : 'Dinonaktifkan' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════ DAFTAR KONFIGURASI API KEY (MANAJEMEN MULTI-KEY) ══════════ -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">
                                <i class="bi bi-key-fill me-2 text-primary"></i>Manajemen Konfigurasi & Kunci API Chatbot
                            </h6>
                            <p class="text-muted small mb-0">Kelola beberapa API Key dari Google Gemini, OpenAI, atau Custom Provider secara terpisah dan fleksibel.</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahAiConfig">
                            <i class="bi bi-plus-circle-fill fs-6"></i>
                            <span>Tambah API Key Baru</span>
                        </button>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tableAiConfigs">
                                <thead class="table-light text-muted small">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="30%">Nama / Label Konfigurasi</th>
                                        <th width="20%">Penyedia (Provider)</th>
                                        <th width="20%">Kunci API Tersimpan</th>
                                        <th width="12%" class="text-center">Status Koneksi</th>
                                        <th width="13%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($data['chatbot']['all_configurations'])): ?>
                                        <?php $idx = 1; foreach ($data['chatbot']['all_configurations'] as $cfg): ?>
                                            <tr id="row-ai-<?= $cfg['id'] ?>" class="<?= !empty($cfg['is_active']) ? 'table-primary bg-opacity-25' : '' ?>">
                                                <td class="text-center text-muted fw-bold"><?= $idx++; ?></td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($cfg['label']) ?></div>
                                                    <?php if (!empty($cfg['is_active'])): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0 mt-1" style="font-size: 11px;">
                                                            <i class="bi bi-star-fill text-warning me-1"></i>Aktif Digunakan Chatbot
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($cfg['provider'] === 'gemini'): ?>
                                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace" style="font-size: 11px;">
                                                            <i class="bi bi-google me-1"></i>Google Gemini AI
                                                        </span>
                                                    <?php elseif ($cfg['provider'] === 'openai'): ?>
                                                        <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 font-monospace" style="font-size: 11px;">
                                                            <i class="bi bi-cpu me-1"></i>OpenAI ChatGPT
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info bg-opacity-10 text-dark border border-info border-opacity-25 font-monospace" style="font-size: 11px;">
                                                            <i class="bi bi-hdd-network me-1"></i><?= htmlspecialchars(strtoupper($cfg['provider'])) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <code class="font-monospace small bg-light px-2 py-1 rounded border text-muted d-inline-block text-truncate" style="max-width: 170px;" title="<?= htmlspecialchars($cfg['api_key_masked']) ?>">
                                                        <?= htmlspecialchars($cfg['api_key_masked']) ?>
                                                    </code>
                                                </td>
                                                <td class="text-center" id="col-status-<?= $cfg['id'] ?>">
                                                    <?php if ($cfg['last_status'] === 'success'): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill">
                                                            <i class="bi bi-check-circle-fill me-1"></i>Terhubung
                                                        </span>
                                                    <?php elseif ($cfg['last_status'] === 'failed'): ?>
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill" title="<?= htmlspecialchars($cfg['last_error_message'] ?? '') ?>">
                                                            <i class="bi bi-exclamation-octagon-fill me-1"></i>Gagal
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 rounded-pill">
                                                            <i class="bi bi-clock me-1"></i>Belum Diuji
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                                        <?php if (empty($cfg['is_active'])): ?>
                                                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1 fw-semibold" title="Gunakan Kunci Ini" onclick="aktifkanAiConfig(<?= $cfg['id'] ?>, '<?= htmlspecialchars(addslashes($cfg['label'])) ?>')">
                                                                <i class="bi bi-check2-circle me-1"></i>Aktifkan
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-sm btn-outline-info rounded-circle p-1" style="width: 28px; height: 28px;" title="Uji Koneksi" onclick="ujiKoneksiRow(<?= $cfg['id'] ?>, this)">
                                                            <i class="bi bi-lightning-charge-fill text-warning"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle p-1" style="width: 28px; height: 28px;" title="Edit" onclick='bukaModalEdit(<?= json_encode($cfg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                                            <i class="bi bi-pencil-square text-dark"></i>
                                                        </button>
                                                        <?php if (empty($cfg['is_active'])): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width: 28px; height: 28px;" title="Hapus" onclick="hapusAiConfig(<?= $cfg['id'] ?>, '<?= htmlspecialchars(addslashes($cfg['label'])) ?>')">
                                                                <i class="bi bi-trash3"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>Belum ada konfigurasi API Key tersimpan. Klik <strong>"+ Tambah API Key Baru"</strong> di atas.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Form Aksi Hidden untuk Aktifkan & Hapus Konfigurasi -->
                <form id="formAiRowAction" method="POST" style="display: none;">
                    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                </form>

                <!-- ══════════ MODAL TAMBAH API KEY ══════════ -->
                <div class="modal fade" id="modalTambahAiConfig" tabindex="-1" aria-labelledby="modalTambahAiConfigLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <div class="modal-header bg-primary text-white py-3">
                                <h5 class="modal-title fs-6 fw-bold mb-0" id="modalTambahAiConfigLabel">
                                    <i class="bi bi-plus-circle-fill me-2"></i>Tambah Konfigurasi API Key Chatbot AI
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/admin/chatbot_add_config" method="POST" id="formTambahAiConfig">
                                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                                <div class="modal-body p-4">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="add_ai_label" class="form-label small fw-bold text-dark">
                                                <i class="bi bi-tag-fill text-primary me-1"></i>Label / Nama Konfigurasi <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="add_ai_label" name="label" required placeholder="Contoh: Gemini Cadangan 2 / OpenAI Produksi">
                                            <div class="form-text" style="font-size: 11px;">Identitas unik agar mudah dikenali dalam daftar.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="add_ai_provider" class="form-label small fw-bold text-dark">
                                                <i class="bi bi-diagram-3-fill text-primary me-1"></i>Penyedia Layanan (Provider) <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select fw-semibold" id="add_ai_provider" name="provider" onchange="renderModalProviderUI('add', this.value)">
                                                <option value="gemini" selected>Google Gemini AI</option>
                                                <option value="openai">OpenAI ChatGPT Platform</option>
                                                <option value="custom">Custom / OpenAI-Compatible (Groq, DeepSeek, Ollama)</option>
                                            </select>
                                            <div class="form-text text-muted" id="add_provider_help" style="font-size: 11px;">Google Generative Language API v1beta berkecepatan tinggi.</div>
                                        </div>
                                        <div class="col-12">
                                            <label for="add_ai_api_key" class="form-label small fw-bold text-dark">
                                                <i class="bi bi-key-fill text-warning me-1"></i>API Key Provider <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-shield-key-fill text-muted"></i></span>
                                                <input type="password" class="form-control font-monospace" id="add_ai_api_key" name="api_key" required placeholder="Tempelkan API Key baru Anda di sini...">
                                                <button class="btn btn-outline-secondary" type="button" onclick="toggleInputEye('add_ai_api_key')">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div class="form-text" style="font-size: 11px;">Kunci disimpan aman di server dan tidak pernah diekspos ke publik/browser.</div>
                                        </div>
                                        <!-- Pengaturan model & parameter diset otomatis dengan konfigurasi optimal sekolah -->
                                        <input type="hidden" id="add_ai_model" name="model" value="gemini-3.1-flash-lite-preview">
                                        <input type="hidden" id="add_ai_base_url" name="base_url" value="">
                                        <input type="hidden" id="add_ai_temperature" name="temperature" value="0.20">
                                        <input type="hidden" id="add_ai_max_tokens" name="max_tokens" value="350">
                                    </div>

                                    <!-- Alert Hasil Uji Form Tambah -->
                                    <div id="add_alert_uji" class="alert alert-info mt-3 mb-0 d-none" role="alert">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-info-circle-fill fs-5" id="add_alert_icon"></i>
                                            <span id="add_alert_text" class="small"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light py-2">
                                    <button type="button" class="btn btn-outline-primary rounded-pill px-3" id="btnUjiFormTambah" onclick="ujiKoneksiModal('add')">
                                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Uji Koneksi Form
                                    </button>
                                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="btnSimpanTambahConfig">
                                        <i class="bi bi-check2-circle me-1"></i>Simpan Konfigurasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ══════════ MODAL EDIT API KEY ══════════ -->
                <div class="modal fade" id="modalEditAiConfig" tabindex="-1" aria-labelledby="modalEditAiConfigLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <div class="modal-header bg-warning bg-opacity-10 py-3 border-bottom">
                                <h5 class="modal-title fs-6 fw-bold mb-0 text-dark" id="modalEditAiConfigLabel">
                                    <i class="bi bi-pencil-square text-warning me-2"></i>Edit Konfigurasi API Key Chatbot AI
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/admin/chatbot_update_config" method="POST" id="formEditAiConfig">
                                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                                <input type="hidden" name="id" id="edit_ai_id">
                                <div class="modal-body p-4">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="edit_ai_label" class="form-label small fw-bold text-dark">
                                                <i class="bi bi-tag-fill text-primary me-1"></i>Label / Nama Konfigurasi <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="edit_ai_label" name="label" required>
                                            <div class="form-text" style="font-size: 11px;">Identitas unik konfigurasi ini.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_ai_provider" class="form-label small fw-bold text-dark">
                                                <i class="bi bi-diagram-3-fill text-primary me-1"></i>Penyedia Layanan (Provider) <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select fw-semibold" id="edit_ai_provider" name="provider" onchange="renderModalProviderUI('edit', this.value)">
                                                <option value="gemini">Google Gemini AI</option>
                                                <option value="openai">OpenAI ChatGPT Platform</option>
                                                <option value="custom">Custom / OpenAI-Compatible (Groq, DeepSeek, Ollama)</option>
                                            </select>
                                            <div class="form-text text-muted" id="edit_provider_help" style="font-size: 11px;"></div>
                                        </div>
                                        <div class="col-12">
                                            <label for="edit_ai_api_key" class="form-label small fw-bold text-dark">
                                                <i class="bi bi-key-fill text-warning me-1"></i>Ganti API Key (Opsional)
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-shield-key-fill text-muted"></i></span>
                                                <input type="password" class="form-control font-monospace" id="edit_ai_api_key" name="api_key" placeholder="Biarkan kosong untuk mempertahankan kunci yang sedang tersimpan">
                                                <button class="btn btn-outline-secondary" type="button" onclick="toggleInputEye('edit_ai_api_key')">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div class="form-text mt-2" style="font-size: 11px;">
                                                <i class="bi bi-shield-check text-success me-1"></i>Kunci tersimpan saat ini: <code class="bg-light px-2 py-1 rounded text-dark font-monospace" id="edit_current_masked_key">-</code>
                                            </div>
                                        </div>
                                        <!-- Parameter teknis otomatis dipertahankan -->
                                        <input type="hidden" id="edit_ai_model" name="model" value="gemini-3.1-flash-lite-preview">
                                        <input type="hidden" id="edit_ai_base_url" name="base_url" value="">
                                        <input type="hidden" id="edit_ai_temperature" name="temperature" value="0.20">
                                        <input type="hidden" id="edit_ai_max_tokens" name="max_tokens" value="350">
                                    </div>

                                    <!-- Alert Hasil Uji Form Edit -->
                                    <div id="edit_alert_uji" class="alert alert-info mt-3 mb-0 d-none" role="alert">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-info-circle-fill fs-5" id="edit_alert_icon"></i>
                                            <span id="edit_alert_text" class="small"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light py-2">
                                    <button type="button" class="btn btn-outline-primary rounded-pill px-3" id="btnUjiFormEdit" onclick="ujiKoneksiModal('edit')">
                                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Uji Koneksi Form
                                    </button>
                                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="btnSimpanEditConfig">
                                        <i class="bi bi-check2-circle me-1"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Panduan Langkah Mendapatkan API Key Sesuai Provider -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-patch-question-fill text-primary"></i>
                            <span>Petunjuk Memperoleh API Key Provider</span>
                        </h6>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 small">
                            Panduan Lengkap
                        </span>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            <!-- Guide Google Gemini -->
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-success">1. Google Gemini (Gratis)</span>
                                        </div>
                                        <h6 class="fw-bold text-dark">Google AI Studio</h6>
                                        <p class="text-muted small mb-2">
                                            Buka <a href="https://aistudio.google.com/app/apikey" target="_blank" class="fw-bold text-primary text-decoration-none">Google AI Studio <i class="bi bi-box-arrow-up-right"></i></a>, masuk dengan akun Google, klik <strong>"Create API key"</strong>, lalu salin string kunci berawalan <code>AQ...</code> atau <code>AIza...</code>.
                                        </p>
                                    </div>
                                    <div class="small text-success fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i>Gratis, cepat, kuota resmi Google.
                                    </div>
                                </div>
                            </div>

                            <!-- Guide OpenAI -->
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-primary">2. OpenAI (Resmi)</span>
                                        </div>
                                        <h6 class="fw-bold text-dark">OpenAI Platform</h6>
                                        <p class="text-muted small mb-2">
                                            Buka <a href="https://platform.openai.com/api-keys" target="_blank" class="fw-bold text-primary text-decoration-none">platform.openai.com <i class="bi bi-box-arrow-up-right"></i></a>, login, buat Secret Key baru (berawalan <code>sk-proj-...</code>), pastikan akun memiliki saldo kuota (billing active).
                                        </p>
                                    </div>
                                    <div class="small text-primary fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i>Model standar industri (GPT-4o Mini).
                                    </div>
                                </div>
                            </div>

                            <!-- Guide Groq / DeepSeek -->
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-light border h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-info text-dark">3. Groq / DeepSeek</span>
                                        </div>
                                        <h6 class="fw-bold text-dark">Groq Cloud / DeepSeek</h6>
                                        <p class="text-muted small mb-2">
                                            Buka <a href="https://console.groq.com/keys" target="_blank" class="fw-bold text-primary text-decoration-none">console.groq.com <i class="bi bi-box-arrow-up-right"></i></a> (kunci <code>gsk_...</code>) dengan Base URL <code>https://api.groq.com/openai/v1</code>, atau DeepSeek Platform dengan kunci <code>sk-...</code>.
                                        </p>
                                    </div>
                                    <div class="small text-info text-dark fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i>Kecepatan inferensi tinggi & hemat biaya.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Live Preview Foto Admin
    const inputFoto = document.getElementById('inputFotoSettings');
    const imgPreview = document.getElementById('imgPreviewAdmin');
    const fallbackPreview = document.getElementById('fallbackPreviewAdmin');

    if (inputFoto) {
        inputFoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ukuran Terlalu Besar',
                        text: 'Ukuran foto maksimal adalah 2MB.'
                    });
                    inputFoto.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(evt) {
                    imgPreview.src = evt.target.result;
                    imgPreview.classList.remove('d-none');
                    if (fallbackPreview) fallbackPreview.classList.add('d-none');
                };
                reader.readAsDataURL(file);

                const btnSimpan = document.getElementById('btnSimpanFotoAdmin');
                if (btnSimpan) {
                    btnSimpan.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simpan Foto Baru';
                }
            }
        });
    }

    // Handler Hapus Foto Profil Admin
    const btnHapusFotoAdmin = document.getElementById('btnHapusFotoAdmin');
    const formHapusFotoAdmin = document.getElementById('formHapusFotoAdmin');
    if (btnHapusFotoAdmin && formHapusFotoAdmin) {
        btnHapusFotoAdmin.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Hapus Foto Profil?',
                text: 'Foto profil Anda saat ini akan dihapus dan dikembalikan ke avatar default sistem.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    formHapusFotoAdmin.submit();
                }
            });
        });
    }

    // 2. Toggle Eye for Passwords
    document.querySelectorAll('.btnToggleEye').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.pwd-input');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    // 3. Theme Selector
    const selectLight = document.getElementById('selectThemeLight');
    const selectDark  = document.getElementById('selectThemeDark');
    const checkLight  = document.getElementById('checkLight');
    const checkDark   = document.getElementById('checkDark');
    const btnSimpanTema = document.getElementById('btnSimpanTema');

    function updateThemeUI(theme) {
        if (theme === 'dark') {
            checkDark.className = 'badge bg-success text-white border';
            checkLight.className = 'badge bg-light text-muted border';
            selectDark.classList.add('border-primary', 'border-2');
            selectLight.classList.remove('border-primary', 'border-2');
        } else {
            checkLight.className = 'badge bg-success text-white border';
            checkDark.className = 'badge bg-secondary text-white border';
            selectLight.classList.add('border-primary', 'border-2');
            selectDark.classList.remove('border-primary', 'border-2');
        }
    }

    let activeTheme = localStorage.getItem('app_theme') || 'light';
    updateThemeUI(activeTheme);

    if (selectLight) {
        selectLight.addEventListener('click', function() {
            activeTheme = 'light';
            updateThemeUI('light');
            if (typeof applyTheme === 'function') applyTheme('light');
        });
    }

    if (selectDark) {
        selectDark.addEventListener('click', function() {
            activeTheme = 'dark';
            updateThemeUI('dark');
            if (typeof applyTheme === 'function') applyTheme('dark');
        });
    }

    if (btnSimpanTema) {
        btnSimpanTema.addEventListener('click', function() {
            localStorage.setItem('app_theme', activeTheme);
            if (typeof applyTheme === 'function') applyTheme(activeTheme);
            Swal.fire({
                icon: 'success',
                title: 'Tema Disimpan',
                text: `Mode tampilan berhasil diubah menjadi: ${activeTheme === 'dark' ? 'Mode Gelap' : 'Mode Terang'}.`,
                timer: 1500,
                showConfirmButton: false
            });
        });
    }

    // 4. Tab URL Hash Auto-Activate
    if (window.location.hash) {
        const targetBtn = document.querySelector(`[data-bs-target="${window.location.hash}"]`);
        if (targetBtn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            const tabInstance = bootstrap.Tab.getOrCreateInstance(targetBtn);
            tabInstance.show();
        }
    }

    // 5. Geofencing Admin GPS Fetcher & Distance Calculator
    const btnAmbilLokasiGPS = document.getElementById('btnAmbilLokasiGPS');
    const inputLat = document.getElementById('latitude_lokasi');
    const inputLng = document.getElementById('longitude_lokasi');
    const inputRadius = document.getElementById('radius_meter_lokasi');
    const gpsStatusText = document.getElementById('liveAdminGpsStatus');
    const distanceBox = document.getElementById('liveDistanceComparison');
    const valDistance = document.getElementById('valLiveDistance');
    const formLokasi = document.getElementById('formPengaturanLokasi');

    let currentAdminCoords = null;

    function hitungJarakAdmin(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return Math.round(R * c);
    }

    function perbaruiJarakAdmin() {
        if (!currentAdminCoords || !inputLat || !inputLng || !distanceBox || !valDistance) return;
        const targetLat = parseFloat(inputLat.value);
        const targetLng = parseFloat(inputLng.value);
        if (isNaN(targetLat) || isNaN(targetLng)) return;

        const meter = hitungJarakAdmin(currentAdminCoords.latitude, currentAdminCoords.longitude, targetLat, targetLng);
        distanceBox.classList.remove('d-none');
        if (meter <= 100) {
            valDistance.className = 'badge bg-success';
            valDistance.innerHTML = `<i class="bi bi-check-circle me-1"></i>${meter} meter (Dalam Batas 100m)`;
        } else {
            valDistance.className = 'badge bg-danger';
            valDistance.innerHTML = `<i class="bi bi-x-circle me-1"></i>${meter} meter (Di Luar Batas 100m)`;
        }
    }

    if (btnAmbilLokasiGPS) {
        btnAmbilLokasiGPS.addEventListener('click', function() {
            if (!navigator.geolocation) {
                Swal.fire({
                    icon: 'error',
                    title: 'GPS Tidak Didukung',
                    text: 'Perangkat atau browser Anda tidak mendukung Geolocation API.'
                });
                return;
            }

            gpsStatusText.innerHTML = '<span class="spinner-border spinner-border-sm me-1 text-primary"></span> Mendeteksi koordinat GPS perangkat Anda...';

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    currentAdminCoords = {
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude
                    };
                    inputLat.value = pos.coords.latitude.toFixed(6);
                    inputLng.value = pos.coords.longitude.toFixed(6);

                    gpsStatusText.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i> Koordinat terisi: <strong>${inputLat.value}, ${inputLng.value}</strong> (Akurasi: ±${Math.round(pos.coords.accuracy)}m)`;
                    perbaruiJarakAdmin();

                    Swal.fire({
                        icon: 'success',
                        title: 'Koordinat Diperoleh',
                        text: `Latitude (${inputLat.value}) dan Longitude (${inputLng.value}) berhasil disematkan.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                function(err) {
                    let errMsg = 'Gagal mengambil lokasi GPS. Pastikan izin lokasi telah diberikan di browser.';
                    if (err.code === 1) errMsg = 'Izin akses lokasi ditolak oleh pengguna/browser.';
                    else if (err.code === 2) errMsg = 'Posisi tidak dapat ditentukan.';
                    else if (err.code === 3) errMsg = 'Waktu permintaan lokasi habis (timeout).';

                    gpsStatusText.innerHTML = `<i class="bi bi-exclamation-triangle-fill text-danger me-1"></i> ${errMsg}`;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Akses Lokasi Ditolak',
                        text: errMsg
                    });
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });
    }

    if (inputLat && inputLng) {
        inputLat.addEventListener('input', perbaruiJarakAdmin);
        inputLng.addEventListener('input', perbaruiJarakAdmin);
    }

    if (inputRadius) {
        inputRadius.addEventListener('input', function() {
            if (parseInt(this.value) > 100) {
                this.value = 100;
                Swal.fire({
                    icon: 'warning',
                    title: 'Batas Maksimal 100 Meter',
                    text: 'Sesuai ketentuan, batasan jarak absensi tidak boleh lebih dari 100 meter.',
                    timer: 2500,
                    showConfirmButton: false
                });
            }
        });
    }

    if (formLokasi) {
        formLokasi.addEventListener('submit', function(e) {
            const radVal = parseInt(inputRadius.value);
            if (radVal > 100) {
                e.preventDefault();
                inputRadius.value = 100;
                Swal.fire({
                    icon: 'error',
                    title: 'Radius Tidak Boleh Lebih Dari 100m',
                    text: 'Batasan jarak absensi maksimal adalah 100 meter.'
                });
            }
        });
    }

    // ══════════ MULTI-PROVIDER AI CHATBOT MANAGEMENT LOGIC ══════════
    const providerPresets = {
        'gemini': {
            help: 'Provider Google Gemini menggunakan Google Generative Language API v1beta berkecepatan tinggi.',
            keyPlaceholder: 'Tempelkan Google Gemini API Key Anda (misal: AQ... / AIza...)',
            models: [
                { id: 'gemini-3.1-flash-lite-preview', name: 'Gemini 3.1 Flash Lite Preview' },
                { id: 'gemini-3.5-flash-lite', name: 'Gemini 3.5 Flash Lite' },
                { id: 'gemini-3-flash-preview', name: 'Gemini 3 Flash Preview' },
                { id: 'gemini-3.1-flash-lite', name: 'Gemini 3.1 Flash Lite' },
                { id: 'gemini-1.5-flash', name: 'Gemini 1.5 Flash' }
            ],
            defaultBaseUrl: '',
            showBaseUrl: false
        },
        'openai': {
            help: 'Provider OpenAI resmi via endpoint Chat Completions API (https://api.openai.com/v1).',
            keyPlaceholder: 'Tempelkan OpenAI API Key Anda (misal: sk-proj-...)',
            models: [
                { id: 'gpt-4o-mini', name: 'GPT-4o Mini' },
                { id: 'gpt-4o', name: 'GPT-4o' },
                { id: 'gpt-3.5-turbo', name: 'GPT-3.5 Turbo' }
            ],
            defaultBaseUrl: 'https://api.openai.com/v1',
            showBaseUrl: false
        },
        'custom': {
            help: 'Mendukung sembarang provider berbasis OpenAI API (Groq, DeepSeek, OpenRouter, Mistral, Ollama lokal).',
            keyPlaceholder: 'Tempelkan API Key provider (gsk_... untuk Groq, sk-... untuk DeepSeek)',
            models: [
                { id: 'llama-3.3-70b-versatile', name: 'Groq: LLaMA 3.3 70B' },
                { id: 'llama-3.1-8b-instant', name: 'Groq: LLaMA 3.1 8B' },
                { id: 'deepseek-chat', name: 'DeepSeek-V3' },
                { id: 'deepseek-reasoner', name: 'DeepSeek-R1' },
                { id: 'meta-llama/llama-3.3-70b-instruct', name: 'OpenRouter: LLaMA 3.3' }
            ],
            defaultBaseUrl: 'https://api.groq.com/openai/v1',
            showBaseUrl: true
        }
    };

    window.toggleInputEye = function(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const btn = input.nextElementSibling;
        const icon = btn ? btn.querySelector('i') : null;
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            if (icon) icon.className = 'bi bi-eye';
        }
    };

    window.renderModalProviderUI = function(prefix, providerKey) {
        const modelInput = document.getElementById(`${prefix}_ai_model`);
        const helpEl = document.getElementById(`${prefix}_provider_help`);
        if (modelInput) {
            modelInput.value = (providerKey === 'openai') ? 'gpt-4o-mini' : 'gemini-3.1-flash-lite-preview';
        }
        if (helpEl) {
            helpEl.innerText = (providerKey === 'openai') 
                ? 'Provider resmi OpenAI ChatGPT (GPT-4o Mini).' 
                : 'Google Gemini AI berkecepatan tinggi & kuota resmi gratis.';
        }
    };

    // Inisialisasi awal modal tambah
    renderModalProviderUI('add', 'gemini');

    window.bukaModalEdit = function(cfg) {
        if (!cfg) return;
        document.getElementById('edit_ai_id').value = cfg.id;
        document.getElementById('edit_ai_label').value = cfg.label || '';
        document.getElementById('edit_ai_provider').value = cfg.provider || 'gemini';
        document.getElementById('edit_ai_api_key').value = '';
        document.getElementById('edit_current_masked_key').innerText = cfg.api_key_masked || 'Tersimpan di server';
        document.getElementById('edit_ai_model').value = cfg.model || '';
        document.getElementById('edit_ai_base_url').value = cfg.base_url || '';
        document.getElementById('edit_ai_temperature').value = cfg.temperature || '0.20';
        document.getElementById('edit_ai_max_tokens').value = cfg.max_tokens || 350;

        const alertBox = document.getElementById('edit_alert_uji');
        if (alertBox) alertBox.classList.add('d-none');

        renderModalProviderUI('edit', cfg.provider || 'gemini', true);

        const modalEl = document.getElementById('modalEditAiConfig');
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    };

    window.aktifkanAiConfig = function(id, label) {
        Swal.fire({
            title: 'Aktifkan Konfigurasi AI Ini?',
            html: `Jadikan <strong>"${label}"</strong> sebagai API utama chatbot?<br><small class="text-muted">Chatbot akan langsung beralih menggunakan API Key dan model ini setelah verifikasi koneksi berhasil.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Ya, Aktifkan',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                const form = document.getElementById('formAiRowAction');
                form.action = '<?= BASEURL ?>/admin/chatbot_activate_config/' + id;
                form.submit();
            }
        });
    };

    window.hapusAiConfig = function(id, label) {
        Swal.fire({
            title: 'Hapus Konfigurasi Ini?',
            html: `Apakah Anda yakin ingin menghapus <strong>"${label}"</strong> dari daftar?<br><small class="text-danger">Tindakan ini tidak dapat dibatalkan.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                const form = document.getElementById('formAiRowAction');
                form.action = '<?= BASEURL ?>/admin/chatbot_delete_config/' + id;
                form.submit();
            }
        });
    };

    window.ujiKoneksiRow = function(id, btn) {
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        const formData = new FormData();
        formData.append('csrf_token', '<?= $data['csrf_token'] ?>');
        formData.append('id', id);

        fetch('<?= BASEURL ?>/admin/chatbot_check', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHtml;

            const colStatus = document.getElementById('col-status-' + id);
            const nowTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            if (data.status) {
                if (colStatus) {
                    colStatus.innerHTML = `
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill">
                            <i class="bi bi-check-circle-fill me-1"></i>Terhubung
                        </span>
                        <div class="text-muted mt-1" style="font-size: 10px;">Hari ini ${nowTime}</div>
                    `;
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Uji Koneksi Berhasil!',
                    html: `${data.pesan}<br><br><div class="alert alert-success py-2 px-3 text-start mb-0 small"><i class="bi bi-check-circle-fill me-1"></i>Kunci API terverifikasi valid dan siap melayani percakapan chatbot.</div>`
                });
            } else {
                if (colStatus) {
                    colStatus.innerHTML = `
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill" title="${data.pesan}">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i>Gagal
                        </span>
                        <div class="text-muted mt-1" style="font-size: 10px;">Hari ini ${nowTime}</div>
                    `;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Uji Koneksi Gagal',
                    text: data.pesan
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Jaringan',
                text: 'Gagal menghubungi server untuk menguji koneksi AI.'
            });
        });
    };

    window.ujiKoneksiModal = function(modalType) {
        const btn = document.getElementById(`btnUjiForm${modalType === 'add' ? 'Tambah' : 'Edit'}`);
        const alertBox = document.getElementById(`${modalType}_alert_uji`);
        const alertIcon = document.getElementById(`${modalType}_alert_icon`);
        const alertText = document.getElementById(`${modalType}_alert_text`);

        const providerVal = document.getElementById(`${modalType}_ai_provider`).value;
        const keyVal = document.getElementById(`${modalType}_ai_api_key`).value.trim();
        const modelVal = document.getElementById(`${modalType}_ai_model`).value.trim();
        const baseUrlVal = document.getElementById(`${modalType}_ai_base_url`).value.trim();
        const idVal = modalType === 'edit' ? document.getElementById('edit_ai_id').value : 0;

        if (modalType === 'add' && !keyVal && !baseUrlVal.includes('localhost') && !baseUrlVal.includes('127.0.0.1')) {
            Swal.fire({
                icon: 'warning',
                title: 'API Key Kosong',
                text: 'Silakan isi API Key terlebih dahulu sebelum menguji koneksi.'
            });
            return;
        }

        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menguji...';

        const formData = new FormData();
        formData.append('csrf_token', '<?= $data['csrf_token'] ?>');
        if (modalType === 'edit' && keyVal === '' && idVal > 0) {
            formData.append('id', idVal);
            formData.append('provider', providerVal);
            formData.append('model', modelVal);
            formData.append('base_url', baseUrlVal);
        } else {
            formData.append('provider', providerVal);
            formData.append('api_key', keyVal);
            formData.append('model', modelVal);
            formData.append('base_url', baseUrlVal);
        }

        fetch('<?= BASEURL ?>/admin/chatbot_check', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHtml;

            if (alertBox) {
                alertBox.classList.remove('d-none');
                if (data.status) {
                    alertBox.className = 'alert alert-success mt-3 mb-0';
                    if (alertIcon) alertIcon.className = 'bi bi-check-circle-fill text-success fs-5';
                    if (alertText) alertText.innerHTML = `<strong>Koneksi Berhasil!</strong> ${data.pesan} (Respons: "${data.reply || 'OK'}")`;
                } else {
                    alertBox.className = 'alert alert-danger mt-3 mb-0';
                    if (alertIcon) alertIcon.className = 'bi bi-exclamation-triangle-fill text-danger fs-5';
                    if (alertText) alertText.innerHTML = `<strong>Uji Gagal:</strong> ${data.pesan}`;
                }
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            if (alertBox) {
                alertBox.classList.remove('d-none');
                alertBox.className = 'alert alert-danger mt-3 mb-0';
                if (alertText) alertText.innerText = 'Gagal menghubungi server untuk pengujian koneksi.';
            }
        });
    };
});
</script>
