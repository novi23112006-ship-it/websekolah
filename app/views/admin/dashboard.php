<?php
$namaHari = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];
$namaBulan = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
$hariIni = $namaHari[date('l')] ?? date('l');
$tglIni = date('j') . ' ' . ($namaBulan[(int)date('n')] ?? date('F')) . ' ' . date('Y');
$adminNama = $_SESSION['nama'] ?? 'Administrator';
?>

<!-- ======================================================== -->
<!-- EXECUTIVE HEADER BANNER (LIGHT MINIMALIST) -->
<!-- ======================================================== -->
<div class="card border mb-4 bg-white" style="border-radius: 14px; border-color: #E5E7EB !important;">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary px-3 py-1 rounded-pill small">
                        <i class="bi bi-shield-check me-1"></i> Dashboard Utama Administrator
                    </span>
                    <span class="badge bg-success px-3 py-1 rounded-pill small">
                        <i class="bi bi-circle-fill me-1" style="font-size: 7px;"></i> Sistem Online
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-1">Pusat Informasi & Kendali Operasional Sekolah</h3>
                <p class="text-muted mb-0">
                    Selamat bertugas, <strong><?= htmlspecialchars($adminNama) ?></strong>. Seluruh ringkasan data, status presensi harian, dan kegiatan KBM terangkum di sini.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="p-3 rounded-3 d-inline-block text-start text-lg-end bg-light border">
                    <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i> <?= $hariIni ?>, <?= $tglIni ?></div>
                    <div class="fs-3 fw-bold text-primary tracking-wide" id="liveDashboardClock"><?= date('H:i:s') ?> <span class="fs-6 fw-normal text-muted">WIB</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- 1. MASTER KPI METRIC CARDS -->
<!-- ======================================================== -->
<div class="row g-3 mb-4">
    <!-- Total Siswa -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-primary border-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted text-uppercase fw-bold small">Total Siswa Aktif</span>
                        <h3 class="fw-bold text-dark my-1"><?= number_format($data['stats']['total_siswa']) ?></h3>
                        <div class="small text-muted">
                            <i class="bi bi-mortarboard text-secondary me-1"></i> <?= $data['stats']['total_siswa_alumni'] ?> Alumni / Lulus
                        </div>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Guru -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-success border-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted text-uppercase fw-bold small">Tenaga Pendidik (Guru)</span>
                        <h3 class="fw-bold text-dark my-1"><?= number_format($data['stats']['total_guru']) ?></h3>
                        <div class="small text-success fw-semibold">
                            <i class="bi bi-check-circle-fill me-1"></i> <?= $data['stats']['guru_masuk_total'] ?> Masuk Hari Ini
                        </div>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="bi bi-person-badge-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Kelas -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-info border-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted text-uppercase fw-bold small">Rombongan Belajar (Kelas)</span>
                        <h3 class="fw-bold text-dark my-1"><?= number_format($data['stats']['total_kelas']) ?></h3>
                        <div class="small text-muted">
                            <i class="bi bi-diagram-3 me-1"></i> Tingkat X, XI, XII
                        </div>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                        <i class="bi bi-door-open-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Mapel -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100 rounded-3 border-start border-warning border-4">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted text-uppercase fw-bold small">Mata Pelajaran Aktif</span>
                        <h3 class="fw-bold text-dark my-1"><?= number_format($data['stats']['total_mapel']) ?></h3>
                        <div class="small text-muted">
                            <i class="bi bi-book-half me-1"></i> Kurikulum Sekolah
                        </div>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="bi bi-journal-bookmark fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- 2. QUICK ACTION HUB (PINTASAN CEPAT) -->
<!-- ======================================================== -->
<div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-body py-3 px-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span class="fw-bold text-dark small text-uppercase">
                <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Pintasan Aksi Cepat:
            </span>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= BASEURL ?>/absensi_siswa" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" title="Buka Scanner Absensi Siswa di tab baru">
                    <i class="bi bi-qr-code-scan me-1"></i> Scan Absen Siswa <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 10px;"></i>
                </a>
                <a href="<?= BASEURL ?>/guru_absensi" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" title="Buka Scanner Absensi Guru di tab baru">
                    <i class="bi bi-person-badge me-1"></i> Scan Absen Guru <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 10px;"></i>
                </a>
                <a href="<?= BASEURL ?>/admin_kelas" class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm">
                    <i class="bi bi-arrow-up-right-circle me-1"></i> Kenaikan Kelas Massal
                </a>
                <a href="<?= BASEURL ?>/laporan" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> Rekap Absensi Guru dan Siswa
                </a>
                <a href="<?= BASEURL ?>/admin_kelas" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-door-open me-1"></i> Data Kelas
                </a>
                <a href="<?= BASEURL ?>/admin_mapel" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-book me-1"></i> Mata Pelajaran
                </a>
                <a href="<?= BASEURL ?>/laporan/printSiswa?tanggal_mulai=<?= date('Y-m-d') ?>&tanggal_akhir=<?= date('Y-m-d') ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                    <i class="bi bi-printer me-1"></i> Cetak Rekap Hari Ini
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- 3. DETAIL REKAP PRESENSI HARI INI (SISWA & GURU) -->
<!-- ======================================================== -->
<div class="row g-3 mb-4">
    <!-- Presensi Siswa Hari Ini -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="bi bi-people-fill me-2"></i>Presensi Siswa Hari Ini (<?= $hariIni ?>)
                </h6>
                <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold">
                    <?= $data['stats']['siswa_persentase'] ?>% Kehadiran
                </span>
            </div>
            <div class="card-body">
                <!-- Stacked Progress Bar -->
                <?php
                $totSiswa = max(1, $data['stats']['total_siswa']);
                $pctHadir = round(($data['stats']['siswa_hadir_hari_ini'] / $totSiswa) * 100, 1);
                $pctTerlambat = round(($data['stats']['siswa_terlambat_hari_ini'] / $totSiswa) * 100, 1);
                $pctIzin = round(($data['stats']['siswa_izin_hari_ini'] / $totSiswa) * 100, 1);
                $pctSakit = round(($data['stats']['siswa_sakit_hari_ini'] / $totSiswa) * 100, 1);
                $pctAlpa = round(($data['stats']['siswa_alpa_hari_ini'] / $totSiswa) * 100, 1);
                ?>
                <div class="progress mb-3" style="height: 12px; border-radius: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pctHadir ?>%" title="Hadir: <?= $data['stats']['siswa_hadir_hari_ini'] ?>"></div>
                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pctTerlambat ?>%" title="Terlambat: <?= $data['stats']['siswa_terlambat_hari_ini'] ?>"></div>
                    <div class="progress-bar bg-info" role="progressbar" style="width: <?= $pctIzin ?>%" title="Izin: <?= $data['stats']['siswa_izin_hari_ini'] ?>"></div>
                    <div class="progress-bar bg-secondary" role="progressbar" style="width: <?= $pctSakit ?>%" title="Sakit: <?= $data['stats']['siswa_sakit_hari_ini'] ?>"></div>
                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $pctAlpa ?>%" title="Alpa: <?= $data['stats']['siswa_alpa_hari_ini'] ?>"></div>
                </div>

                <!-- Breakdown Metric Badges -->
                <div class="row g-2 text-center">
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-success fw-bold d-block fs-5"><?= $data['stats']['siswa_hadir_hari_ini'] ?></span>
                            <span class="small text-muted">Hadir</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-warning fw-bold d-block fs-5"><?= $data['stats']['siswa_terlambat_hari_ini'] ?></span>
                            <span class="small text-muted">Terlambat</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-info fw-bold d-block fs-5"><?= $data['stats']['siswa_izin_hari_ini'] ?></span>
                            <span class="small text-muted">Izin</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-secondary fw-bold d-block fs-5"><?= $data['stats']['siswa_sakit_hari_ini'] ?></span>
                            <span class="small text-muted">Sakit</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-danger fw-bold d-block fs-5"><?= $data['stats']['siswa_alpa_hari_ini'] ?></span>
                            <span class="small text-muted">Alpa</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-muted fw-bold d-block fs-5"><?= $data['stats']['siswa_belum_absen'] ?></span>
                            <span class="small text-muted">Belum</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top flex-wrap gap-2">
                    <small class="text-muted">Total Presensi: <strong><?= $data['stats']['siswa_total_presensi'] ?></strong> dari <strong><?= $data['stats']['total_siswa'] ?></strong> siswa</small>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="<?= BASEURL ?>/absensi_siswa" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary rounded-pill px-3 py-1 shadow-sm" title="Buka Scanner di tab baru">
                            <i class="bi bi-qr-code-scan me-1"></i> Buka Scan Siswa <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 10px;"></i>
                        </a>
                        <a href="<?= BASEURL ?>/laporan/siswa" class="btn btn-sm btn-link text-decoration-none p-0">
                            Riwayat <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Presensi Guru Hari Ini -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-success">
                    <i class="bi bi-person-badge-fill me-2"></i>Presensi Guru Hari Ini (<?= $hariIni ?>)
                </h6>
                <span class="badge bg-success rounded-pill px-3 py-2 fw-semibold">
                    <?= $data['stats']['guru_persentase'] ?>% Kehadiran
                </span>
            </div>
            <div class="card-body">
                <!-- Stacked Progress Bar -->
                <?php
                $totGuru = max(1, $data['stats']['total_guru']);
                $pctGHadir = round(($data['stats']['guru_hadir_hari_ini'] / $totGuru) * 100, 1);
                $pctGTerlambat = round(($data['stats']['guru_terlambat_hari_ini'] / $totGuru) * 100, 1);
                $pctGIzin = round(($data['stats']['guru_izin_hari_ini'] / $totGuru) * 100, 1);
                $pctGSakit = round(($data['stats']['guru_sakit_hari_ini'] / $totGuru) * 100, 1);
                $pctGAlpa = round(($data['stats']['guru_alpa_hari_ini'] / $totGuru) * 100, 1);
                ?>
                <div class="progress mb-3" style="height: 12px; border-radius: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pctGHadir ?>%" title="Hadir: <?= $data['stats']['guru_hadir_hari_ini'] ?>"></div>
                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pctGTerlambat ?>%" title="Terlambat: <?= $data['stats']['guru_terlambat_hari_ini'] ?>"></div>
                    <div class="progress-bar bg-info" role="progressbar" style="width: <?= $pctGIzin ?>%" title="Izin: <?= $data['stats']['guru_izin_hari_ini'] ?>"></div>
                    <div class="progress-bar bg-secondary" role="progressbar" style="width: <?= $pctGSakit ?>%" title="Sakit: <?= $data['stats']['guru_sakit_hari_ini'] ?>"></div>
                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $pctGAlpa ?>%" title="Alpa: <?= $data['stats']['guru_alpa_hari_ini'] ?>"></div>
                </div>

                <!-- Breakdown Metric Badges -->
                <div class="row g-2 text-center">
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-success fw-bold d-block fs-5"><?= $data['stats']['guru_hadir_hari_ini'] ?></span>
                            <span class="small text-muted">Hadir</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-warning fw-bold d-block fs-5"><?= $data['stats']['guru_terlambat_hari_ini'] ?></span>
                            <span class="small text-muted">Terlambat</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-info fw-bold d-block fs-5"><?= $data['stats']['guru_izin_hari_ini'] ?></span>
                            <span class="small text-muted">Izin</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-secondary fw-bold d-block fs-5"><?= $data['stats']['guru_sakit_hari_ini'] ?></span>
                            <span class="small text-muted">Sakit</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-danger fw-bold d-block fs-5"><?= $data['stats']['guru_alpa_hari_ini'] ?></span>
                            <span class="small text-muted">Alpa</span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-muted fw-bold d-block fs-5"><?= $data['stats']['guru_belum_absen'] ?></span>
                            <span class="small text-muted">Belum</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top flex-wrap gap-2">
                    <small class="text-muted">Total Guru Masuk: <strong><?= $data['stats']['guru_masuk_total'] ?></strong> dari <strong><?= $data['stats']['total_guru'] ?></strong> orang</small>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="<?= BASEURL ?>/guru_absensi" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success rounded-pill px-3 py-1 shadow-sm" title="Buka Scanner di tab baru">
                            <i class="bi bi-person-badge me-1"></i> Buka Scan Guru <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 10px;"></i>
                        </a>
                        <a href="<?= BASEURL ?>/laporan/guru" class="btn btn-sm btn-link text-decoration-none p-0">
                            Riwayat <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- 4. REKAPITULASI KEHADIRAN SISWA PER KELAS HARI INI -->
<!-- ======================================================== -->
<div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="m-0 fw-bold text-dark">
                <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>Rekapitulasi Kehadiran Siswa per Kelas Hari Ini
            </h6>
            <small class="text-muted">Pantau tingkat kehadiran setiap rombongan belajar secara real-time</small>
        </div>
        <a href="<?= BASEURL ?>/admin_kelas" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            <i class="bi bi-gear me-1"></i> Kelola Kelas
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="4%" class="text-center">No</th>
                        <th>Kelas & Jurusan</th>
                        <th>Wali Kelas</th>
                        <th class="text-center" width="10%">Total Siswa</th>
                        <th class="text-center" width="8%">Hadir</th>
                        <th class="text-center" width="8%">Terlambat</th>
                        <th class="text-center" width="8%">Izin/Sakit</th>
                        <th class="text-center" width="8%">Belum</th>
                        <th width="18%">Tingkat Kehadiran</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($data['rekap_kelas'] as $rk) : ?>
                    <?php
                    $persen = (float)$rk['persentase'];
                    $badgeBg = ($persen >= 90) ? 'bg-success' : (($persen >= 75) ? 'bg-primary' : (($persen >= 50) ? 'bg-warning' : 'bg-danger'));
                    ?>
                    <tr>
                        <td class="text-center text-muted"><?= $no++ ?></td>
                        <td>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($rk['nama_kelas']) ?></span>
                            <div class="small text-muted"><?= htmlspecialchars($rk['jurusan'] ?? '-') ?> &bull; Tingkat <?= htmlspecialchars($rk['tingkat']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($rk['nama_wali'] ?? 'Belum ditentukan') ?></td>
                        <td class="text-center fw-bold"><?= (int)$rk['total_siswa'] ?></td>
                        <td class="text-center text-success fw-bold"><?= (int)$rk['hadir'] ?></td>
                        <td class="text-center text-warning fw-bold"><?= (int)$rk['terlambat'] ?></td>
                        <td class="text-center text-info fw-bold"><?= (int)$rk['izin'] + (int)$rk['sakit'] ?></td>
                        <td class="text-center text-muted fw-bold"><?= (int)$rk['belum_absen'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar <?= $badgeBg ?>" role="progressbar" style="width: <?= $persen ?>%"></div>
                                </div>
                                <span class="small fw-bold text-dark" style="min-width: 45px;"><?= $persen ?>%</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="<?= BASEURL ?>/laporan/siswa?kelas_id=<?= $rk['id'] ?>&tanggal_mulai=<?= date('Y-m-d') ?>&tanggal_akhir=<?= date('Y-m-d') ?>" 
                               class="btn btn-sm btn-outline-primary py-0 px-2 small" title="Lihat Detail Riwayat Hari Ini">
                                Riwayat
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($data['rekap_kelas'])) : ?>
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">Belum ada data kelas terdaftar.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- 5. JADWAL KBM HARI INI -->
<!-- ======================================================== -->
<div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="m-0 fw-bold text-dark">
                <i class="bi bi-clock-history text-warning me-2"></i>Jadwal Kegiatan Belajar Mengajar (KBM) Hari Ini (<?= $data['jadwal_kbm']['hari'] ?>)
            </h6>
            <small class="text-muted">Sesi pembelajaran aktif sesuai hari berjalan</small>
        </div>
        <span class="badge bg-light text-dark border px-3 py-2">
            Total <?= count($data['jadwal_kbm']['list']) ?> Sesi KBM Terjadwal
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="15%">Waktu Sesi</th>
                        <th width="15%">Kelas & Ruang</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru Pengajar</th>
                        <th width="15%" class="text-center">Status Sesi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $currentTime = date('H:i:s');
                    $no = 1;
                    foreach ($data['jadwal_kbm']['list'] as $j) :
                        $mulai = $j['jam_mulai'];
                        $selesai = $j['jam_selesai'];
                        $isOngoing = ($currentTime >= $mulai && $currentTime <= $selesai);
                        $isFinished = ($currentTime > $selesai);
                    ?>
                    <tr class="<?= $isOngoing ? 'table-warning bg-opacity-25' : '' ?>">
                        <td class="text-center text-muted"><?= $no++ ?></td>
                        <td>
                            <span class="fw-bold text-dark"><i class="bi bi-clock me-1 text-primary"></i> <?= substr($mulai, 0, 5) ?> - <?= substr($selesai, 0, 5) ?></span>
                        </td>
                        <td>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($j['nama_kelas']) ?></span>
                            <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($j['ruangan'] ?? 'Ruang Kelas') ?></div>
                        </td>
                        <td>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($j['nama_mapel']) ?></span>
                            <span class="badge bg-light text-secondary border ms-1"><?= htmlspecialchars($j['kode_mapel']) ?></span>
                        </td>
                        <td>
                            <i class="bi bi-person-circle text-secondary me-1"></i> <?= htmlspecialchars($j['nama_guru']) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($isOngoing) : ?>
                                <span class="badge bg-danger rounded-pill px-3 py-1">
                                    <i class="bi bi-broadcast me-1"></i> Berlangsung
                                </span>
                            <?php elseif ($isFinished) : ?>
                                <span class="badge bg-secondary rounded-pill px-3 py-1">Selesai</span>
                            <?php else : ?>
                                <span class="badge bg-light text-dark border rounded-pill px-3 py-1">Akan Datang</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($data['jadwal_kbm']['list'])) : ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-calendar-x fs-3 d-block mb-1 text-secondary"></i>
                            Tidak ada jadwal sesi KBM terjadwal untuk hari <strong><?= $data['jadwal_kbm']['hari'] ?></strong>.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- 6. LOG AKTIVITAS TERKINI (SISWA & GURU) -->
<!-- ======================================================== -->
<div class="row g-3">
    <!-- Log Absensi Siswa Terbaru -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="bi bi-clock-history me-2"></i>Log Absensi Siswa Terkini
                </h6>
                <a href="<?= BASEURL ?>/laporan/siswa" class="small text-decoration-none">Lihat Semua <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Waktu Scan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['absensi_siswa'] as $as) : ?>
                            <tr>
                                <td>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($as['nama']) ?></span>
                                    <div class="small text-muted" style="font-size: 11px;">NIS: <?= htmlspecialchars($as['nis'] ?? '-') ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($as['nama_kelas'] ?? '-') ?></span></td>
                                <td>
                                    <div class="small text-muted"><i class="bi bi-box-arrow-in-right text-success me-1"></i><?= !empty($as['jam_masuk']) ? substr($as['jam_masuk'], 0, 5) : '-' ?></div>
                                    <?php if (!empty($as['jam_pulang'])): ?>
                                        <div class="small text-primary"><i class="bi bi-box-arrow-left me-1"></i><?= substr($as['jam_pulang'], 0, 5) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($as['status'] == 'terlambat') : ?>
                                        <span class="badge bg-warning text-dark">Terlambat</span>
                                    <?php elseif ($as['status'] == 'hadir') : ?>
                                        <span class="badge bg-success">Hadir</span>
                                    <?php elseif ($as['status'] == 'izin') : ?>
                                        <span class="badge bg-info text-dark">Izin</span>
                                    <?php elseif ($as['status'] == 'sakit') : ?>
                                        <span class="badge bg-secondary">Sakit</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">Alpa</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($data['absensi_siswa'])) : ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada catatan presensi siswa hari ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Log Absensi Guru Terbaru -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-success">
                    <i class="bi bi-clock-history me-2"></i>Log Absensi Guru Terkini
                </h6>
                <a href="<?= BASEURL ?>/laporan/guru" class="small text-decoration-none">Lihat Semua <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Guru</th>
                                <th>NIP</th>
                                <th>Waktu Scan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['absensi_guru'] as $ag) : ?>
                            <tr>
                                <td>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($ag['nama']) ?></span>
                                </td>
                                <td class="small text-muted"><?= htmlspecialchars($ag['nip'] ?? '-') ?></td>
                                <td>
                                    <div class="small text-muted"><i class="bi bi-box-arrow-in-right text-success me-1"></i><?= !empty($ag['jam_scan']) ? substr($ag['jam_scan'], 0, 5) : '-' ?></div>
                                    <?php if (!empty($ag['jam_pulang'])): ?>
                                        <div class="small text-primary"><i class="bi bi-box-arrow-left me-1"></i><?= substr($ag['jam_pulang'], 0, 5) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ag['status'] == 'terlambat') : ?>
                                        <span class="badge bg-warning text-dark">Terlambat</span>
                                    <?php elseif ($ag['status'] == 'hadir') : ?>
                                        <span class="badge bg-success">Hadir</span>
                                    <?php elseif ($ag['status'] == 'izin') : ?>
                                        <span class="badge bg-info text-dark">Izin</span>
                                    <?php elseif ($ag['status'] == 'sakit') : ?>
                                        <span class="badge bg-secondary">Sakit</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">Alpa</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($data['absensi_guru'])) : ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada catatan presensi guru hari ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Jam Digital Real-time di Dashboard
function updateDashboardClock() {
    const clockEl = document.getElementById('liveDashboardClock');
    if (!clockEl) return;
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    clockEl.innerHTML = `${hours}:${minutes}:${seconds} <span class="fs-6 fw-normal">WIB</span>`;
}
setInterval(updateDashboardClock, 1000);
</script>
