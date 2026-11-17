<?php
// Guard: jika stats false (guru tidak ditemukan di tabel guru)
if (!$data['stats']): ?>
<div class="alert alert-warning">
    <strong>Perhatian:</strong> Data profil guru Anda belum terdaftar di sistem. Silakan hubungi Admin.
</div>
<?php return; endif; ?>

<?php
$stats = $data['stats'];
$absen = $stats['absen_hari_ini'];
$tanggal = $data['tanggal_hari_ini'] ?? date('l, d F Y');
?>

<!-- Alert Notifikasi Flash -->
<?php Flasher::flash(); ?>

<!-- Baris Greeting Guru & Tombol Aksi -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 p-3 bg-white rounded-4 shadow-sm border">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 d-inline-flex align-items-center justify-content-center shadow-sm flex-shrink-0" 
             style="width: 48px; height: 48px; font-size: 22px;">
            <i class="bi bi-person-workspace"></i>
        </div>
        <div>
            <h5 class="mb-1 text-gray-800 fw-bold">Selamat datang, <?= htmlspecialchars($stats['guru']['nama'] ?? $_SESSION['nama'] ?? 'Bapak/Ibu Guru') ?>! 👋</h5>
            <p class="text-muted mb-0 small"><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($tanggal) ?> &bull; <i class="bi bi-clock me-1"></i><span id="liveClockGuruDash" class="fw-semibold text-dark"><?= $jamSekarang ?? DateHelper::jamSekarang('H:i:s') ?> WIB</span> &bull; <i class="bi bi-book me-1"></i><?= htmlspecialchars($stats['guru']['mata_pelajaran'] ?? '-') ?></p>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-info text-white fw-semibold rounded-pill px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalQrCodeGuruDashboard">
            <i class="bi bi-qr-code me-1"></i>QR Barcode Saya
        </button>
        <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-semibold rounded-pill px-3 py-2" data-bs-toggle="modal" data-bs-target="#modalPengajuanIzinGuru">
            <i class="bi bi-file-earmark-medical me-1"></i>Ajukan Izin / Sakit
        </button>
        <?php if (!$absen): ?>
            <button type="button" class="btn btn-sm btn-dark text-white fw-semibold rounded-pill px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalScanBarcodeGuru">
                <i class="bi bi-qr-code-scan me-1"></i>Scan Absen Masuk
            </button>
        <?php elseif (empty($absen['jam_pulang']) && in_array($absen['status'], ['hadir', 'terlambat'])): ?>
            <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold rounded-pill px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalScanBarcodeGuru">
                <i class="bi bi-box-arrow-left me-1"></i>Scan Absen Pulang
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-sm btn-success text-white fw-semibold rounded-pill px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalScanBarcodeGuru">
                <i class="bi bi-check-circle-fill me-1"></i>Absensi Hari Ini Lengkap
            </button>
        <?php endif; ?>
        <a href="<?= BASEURL ?>/guru/jadwal" class="btn btn-sm btn-primary rounded-pill px-3 py-2">
            <i class="bi bi-calendar3-range me-1"></i>Jadwal Mengajar
        </a>
    </div>
</div>

<!-- ═══ ROW 1: 4 Card Statistik ═══════════════════════════════════════════ -->
<div class="row g-3 mb-4">

    <!-- Card Status Absensi Guru -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0
                    <?php 
                        if (!$absen) {
                            echo 'bg-danger';
                        } elseif ($absen['status'] == 'terlambat') {
                            echo 'bg-warning';
                        } elseif ($absen['status'] == 'sakit') {
                            echo 'bg-primary';
                        } elseif ($absen['status'] == 'izin') {
                            echo 'bg-info';
                        } else {
                            echo 'bg-success';
                        }
                    ?>"
                    style="width:54px;height:54px;">
                    <i class="bi <?php 
                        if (!$absen) echo 'bi-x-lg';
                        elseif ($absen['status'] == 'sakit') echo 'bi-bandaid';
                        elseif ($absen['status'] == 'izin') echo 'bi-envelope-paper';
                        else echo 'bi-check-lg';
                    ?> text-white fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Absensi Guru Hari Ini</div>
                    <?php if ($absen): ?>
                        <div class="fw-bold fs-6">
                            <?php if ($absen['status'] == 'terlambat'): ?>
                                <span class="text-warning">Terlambat</span>
                            <?php elseif ($absen['status'] == 'sakit'): ?>
                                <span class="text-primary">Sakit</span>
                            <?php elseif ($absen['status'] == 'izin'): ?>
                                <span class="text-info">Izin</span>
                            <?php else: ?>
                                <span class="text-success">Hadir ✓</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted small">
                            <?php if (in_array($absen['status'], ['hadir', 'terlambat'])): ?>
                                <div><i class="bi bi-box-arrow-in-right text-success me-1"></i>Masuk: <?= htmlspecialchars(substr($absen['jam_scan'], 0, 5)) ?> WIB</div>
                                <?php if (!empty($absen['jam_pulang'])): ?>
                                    <div><i class="bi bi-box-arrow-left text-primary me-1"></i>Pulang: <?= htmlspecialchars(substr($absen['jam_pulang'], 0, 5)) ?> WIB</div>
                                <?php else: ?>
                                    <div><i class="bi bi-clock-history text-warning me-1"></i>Pulang: <em>Belum Absen</em></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?= htmlspecialchars(mb_strimwidth($absen['keterangan'] ?? ucfirst($absen['status']), 0, 24, '...')) ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="fw-bold fs-6 text-danger">Belum Absen</div>
                        <div class="text-muted small">
                            <a href="javascript:void(0)" class="text-primary text-decoration-none fw-semibold" data-bs-toggle="modal" data-bs-target="#modalScanBarcodeGuru">
                                <i class="bi bi-qr-code-scan me-1"></i>Scan (Maks 100m)
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Total Jadwal Mengajar -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0 text-white"
                    style="width:54px;height:54px;">
                    <i class="bi bi-calendar3-range text-white fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Jadwal Mengajar</div>
                    <div class="fw-bold fs-4"><?= (int)($stats['total_jadwal_mengajar'] ?? 0) ?> Sesi</div>
                    <div class="text-muted small">Senin - Jumat</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Total Kelas Diajar -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info d-flex align-items-center justify-content-center flex-shrink-0 text-white"
                    style="width:54px;height:54px;">
                    <i class="bi bi-mortarboard-fill text-white fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Kelas yang Diampu</div>
                    <div class="fw-bold fs-4"><?= (int)($stats['total_kelas_diajar'] ?? 0) ?> Kelas</div>
                    <div class="text-muted small">Aktif mengajar</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Mapel Guru -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center flex-shrink-0 text-white"
                    style="width:54px;height:54px;">
                    <i class="bi bi-book-half text-white fs-4"></i>
                </div>
                <div class="overflow-hidden">
                    <div class="text-muted small">Mata Pelajaran</div>
                    <div class="fw-bold fs-6 text-truncate" title="<?= htmlspecialchars($stats['guru']['mata_pelajaran'] ?? '-') ?>"><?= htmlspecialchars($stats['guru']['mata_pelajaran'] ?? '-') ?></div>
                    <div class="text-muted small">Semester Ini</div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ═══ SECTION JADWAL MENGAJAR GURU (SENIN - JUMAT) ═══════════════════════ -->
<?php
$hariIni = $data['hari_ini'] ?? 'Senin';
$jadwalHariIni = $data['jadwal_hari_ini'] ?? [];
$jadwalSeninJumat = $data['jadwal_senin_jumat'] ?? [];
$daftarHariGuru = DateHelper::getDaftarHari(true);
$currentTime = date('H:i:s');
?>
<div class="card shadow-sm border-0 rounded-4 mb-4" id="sectionJadwalGuru">
    <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-calendar3-range fs-5"></i>
            </div>
            <div>
                <h6 class="m-0 fw-bold text-dark">Jadwal Mengajar Guru (Senin - Sabtu)</h6>
                <small class="text-muted">
                    Mata Pelajaran: <strong><?= htmlspecialchars($stats['guru']['mata_pelajaran'] ?? '-') ?></strong> &bull; 
                    Hari Ini: <span class="badge bg-primary-subtle text-primary fw-bold"><?= htmlspecialchars($data['tanggal_hari_ini'] ?? DateHelper::tanggalIndo()) ?></span> &bull; 
                    Waktu: <strong class="text-success"><span id="liveTimeClock"><?= $data['jam_sekarang'] ?? DateHelper::jamSekarang('H:i') ?></span> WIB</strong>
                </small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASEURL; ?>/guru/jadwal" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="bi bi-calendar-week me-1"></i>Kelola & Cetak Jadwal <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="card-body p-3">
        <!-- Tabs Navigasi Hari Mengajar -->
        <ul class="nav nav-pills gap-2 mb-3 border-bottom pb-2 flex-wrap" id="guruJadwalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= (!in_array($hariIni, $daftarHariGuru) || $hariIni === 'Senin') ? 'active' : '' ?>" 
                        id="tab-hari-guru-Senin" data-bs-toggle="pill" data-bs-target="#pane-guru-Senin" type="button" role="tab">
                    <span class="fw-bold">Senin</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalSeninJumat['Senin'] ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= ($hariIni === 'Selasa') ? 'active' : '' ?>" 
                        id="tab-hari-guru-Selasa" data-bs-toggle="pill" data-bs-target="#pane-guru-Selasa" type="button" role="tab">
                    <span class="fw-bold">Selasa</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalSeninJumat['Selasa'] ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= ($hariIni === 'Rabu') ? 'active' : '' ?>" 
                        id="tab-hari-guru-Rabu" data-bs-toggle="pill" data-bs-target="#pane-guru-Rabu" type="button" role="tab">
                    <span class="fw-bold">Rabu</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalSeninJumat['Rabu'] ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= ($hariIni === 'Kamis') ? 'active' : '' ?>" 
                        id="tab-hari-guru-Kamis" data-bs-toggle="pill" data-bs-target="#pane-guru-Kamis" type="button" role="tab">
                    <span class="fw-bold">Kamis</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalSeninJumat['Kamis'] ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= ($hariIni === 'Jumat') ? 'active' : '' ?>" 
                        id="tab-hari-guru-Jumat" data-bs-toggle="pill" data-bs-target="#pane-guru-Jumat" type="button" role="tab">
                    <span class="fw-bold">Jumat</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalSeninJumat['Jumat'] ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= ($hariIni === 'Sabtu') ? 'active' : '' ?>" 
                        id="tab-hari-guru-Sabtu" data-bs-toggle="pill" data-bs-target="#pane-guru-Sabtu" type="button" role="tab">
                    <span class="fw-bold">Sabtu</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalSeninJumat['Sabtu'] ?? []) ?></span>
                </button>
            </li>
            <?php if (!empty($jadwalHariIni)): ?>
            <li class="nav-item ms-auto" role="presentation">
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                    <i class="bi bi-clock me-1"></i>Hari Ini: <?= count($jadwalHariIni) ?> Kelas Terjadwal
                </span>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Content Per Hari -->
        <div class="tab-content" id="guruJadwalTabsContent">
            <?php foreach ($daftarHariGuru as $dh): ?>
                <?php 
                    $isActive = false;
                    if (in_array($hariIni, $daftarHariGuru)) {
                        $isActive = ($dh === $hariIni);
                    } else {
                        $isActive = ($dh === 'Senin');
                    }
                    $listDH = $jadwalSeninJumat[$dh] ?? [];
                ?>
                <div class="tab-pane fade <?= $isActive ? 'show active' : '' ?>" id="pane-guru-<?= $dh ?>" role="tabpanel">
                    <?php if (!empty($listDH)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width: 170px;">Jam Mengajar</th>
                                        <th>Kelas</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Ruangan</th>
                                        <th>Keterangan / Pokok Bahasan</th>
                                        <th class="text-center" style="width: 130px;">Status KBM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($listDH as $jm): ?>
                                        <?php
                                            $kbmStatus = DateHelper::getStatusKBM($dh, $jm['jam_mulai'], $jm['jam_selesai']);
                                            $isNow = ($kbmStatus['status'] === 'sedang_kbm');
                                        ?>
                                        <tr class="<?= $isNow ? 'table-primary bg-opacity-25' : '' ?>">
                                            <td class="ps-3">
                                                <span class="badge <?= $isNow ? 'bg-primary' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> px-2 py-1 fw-bold">
                                                    <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($jm['jam_mulai'])) ?> - <?= date('H:i', strtotime($jm['jam_selesai'])) ?>
                                                </span>
                                                <?php if ($isNow): ?>
                                                    <div class="small text-success fw-bold mt-1"><i class="bi bi-broadcast me-1"></i>Sedang KBM</div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($jm['nama_kelas']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($jm['jurusan'] ?? '-') ?></small>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">
                                                    <?= htmlspecialchars($jm['nama_mapel'] ?? '-') ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?php if (!empty($jm['kode_mapel'])): ?>
                                                        <span class="badge bg-light text-secondary border me-1"><?= htmlspecialchars($jm['kode_mapel']) ?></span>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($jm['kelompok'] ?? 'Kejuruan') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($jm['ruangan'] ?? 'Ruang Kelas') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted small"><?= htmlspecialchars($jm['keterangan'] ?? '-') ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= $kbmStatus['badge'] ?? 'bg-secondary' ?> rounded-pill px-3 py-1">
                                                    <i class="<?= $kbmStatus['icon'] ?> me-1"></i><?= $kbmStatus['label'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 px-3 bg-light rounded-4 border">
                            <i class="bi bi-calendar-minus text-muted fs-2 mb-2 d-inline-block"></i>
                            <h6 class="fw-bold text-dark">Tidak ada jadwal mengajar pada hari <?= $dh ?></h6>
                            <p class="text-muted small mb-0">Anda tidak memiliki jadwal mengajar di kelas pada hari <?= $dh ?>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ═══ ROW 2: Detail Absensi Guru + Scan Siswa Terbaru ════════════════════ -->
<div class="row g-3 mb-4">

    <!-- Card Status Absensi Guru Detail -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-person-badge me-2 text-dark"></i>Status Absensi Saya</h6>
            </div>
            <div class="card-body d-flex flex-column justify-content-center text-center py-4">
                <?php if (!$absen): ?>
                    <i class="bi bi-x-circle text-danger" style="font-size:3.5rem;"></i>
                    <h5 class="mt-3 text-danger fw-bold">Belum Absen</h5>
                    <p class="text-muted small mb-2">Silakan lakukan scan barcode atau ajukan izin/sakit jika berhalangan hadir.</p>
                    <div class="p-2 mb-3 bg-light rounded-3 border small">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Jarak Anda:</span>
                            <span id="cardJarakBadgeGuruDash" class="badge bg-secondary">Mendeteksi GPS...</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalScanBarcodeGuru">
                            <i class="bi bi-qr-code-scan me-1"></i>Scan Sekarang
                        </button>
                        <button type="button" class="btn btn-outline-warning text-dark btn-sm fw-semibold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalPengajuanIzinGuru">
                            <i class="bi bi-file-earmark-medical me-1"></i>Ajukan Izin / Sakit
                        </button>
                    </div>
                <?php elseif ($absen['status'] == 'terlambat'): ?>
                    <i class="bi bi-exclamation-circle text-warning" style="font-size:3.5rem;"></i>
                    <h5 class="mt-3 text-warning fw-bold">Terlambat</h5>
                    <p class="text-muted mb-1 small">Waktu Scan:</p>
                    <span class="badge bg-warning text-dark fs-6"><?= htmlspecialchars($absen['jam_scan']) ?> WIB</span>
                <?php elseif ($absen['status'] == 'sakit'): ?>
                    <i class="bi bi-bandaid-fill text-primary" style="font-size:3.5rem;"></i>
                    <h5 class="mt-3 text-primary fw-bold">Sakit (Izin Sakit)</h5>
                    <p class="text-muted mb-1 small">Keterangan:</p>
                    <div class="p-2 bg-light rounded text-start small border">
                        <?= nl2br(htmlspecialchars($absen['keterangan'] ?? 'Surat keterangan sakit')) ?>
                    </div>
                <?php elseif ($absen['status'] == 'izin'): ?>
                    <i class="bi bi-envelope-paper-fill text-info" style="font-size:3.5rem;"></i>
                    <h5 class="mt-3 text-info fw-bold">Izin (Tercatat)</h5>
                    <p class="text-muted mb-1 small">Keterangan:</p>
                    <div class="p-2 bg-light rounded text-start small border">
                        <?= nl2br(htmlspecialchars($absen['keterangan'] ?? 'Pengajuan izin')) ?>
                    </div>
                <?php else: ?>
                    <i class="bi bi-check-circle text-success" style="font-size:3.5rem;"></i>
                    <h5 class="mt-3 text-success fw-bold">Hadir Tepat Waktu</h5>
                    <p class="text-muted mb-1 small">Waktu Scan:</p>
                    <span class="badge bg-success fs-6"><?= htmlspecialchars($absen['jam_scan']) ?> WIB</span>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white py-2 text-center">
                <?php if (in_array($absen['status'] ?? '', ['sakit', 'izin'])): ?>
                    <small class="text-muted"><i class="bi bi-check-circle me-1 text-success"></i>Tercatat dalam data izin dewan guru</small>
                <?php else: ?>
                    <small class="text-muted">Batas masuk tepat waktu: <strong>06:45 WIB</strong></small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Card Riwayat Absensi Guru -->
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-calendar-check me-2 text-primary"></i>Riwayat Absensi Saya</h6>
                <a href="<?= BASEURL ?>/laporan/guru" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Tanggal</th>
                                <th>Jam Scan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($stats['riwayat'])): ?>
                                <?php foreach ($stats['riwayat'] as $r): ?>
                                <tr>
                                    <td class="ps-3"><?= date('d M Y', strtotime($r['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($r['jam_scan'] ?? '-') ?></td>
                                    <td>
                                        <?php
                                            $badgeMap = [
                                                'hadir'     => 'bg-success',
                                                'terlambat' => 'bg-warning text-dark',
                                                'izin'      => 'bg-info text-dark',
                                                'sakit'     => 'bg-primary',
                                                'alpa'      => 'bg-danger',
                                            ];
                                            $bc = $badgeMap[$r['status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $bc ?>"><?= ucfirst($r['status']) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted py-4">Belum ada riwayat absensi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ============================================================== -->
<!-- MODAL PENGAJUAN SAKIT / IZIN GURU                              -->
<!-- ============================================================== -->
<div class="modal fade" id="modalPengajuanIzinGuru" tabindex="-1" aria-labelledby="modalPengajuanIzinGuruLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-warning bg-gradient text-dark border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalPengajuanIzinGuruLabel">
                    <i class="bi bi-file-earmark-medical me-2"></i>Pengajuan Sakit / Izin Guru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASEURL; ?>/guru/ajukan_izin" method="POST" enctype="multipart/form-data" id="formPengajuanIzinGuru">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small border-0 rounded-3 mb-3">
                        <i class="bi bi-info-circle me-1"></i>Pengajuan ini akan langsung tercatat pada absensi harian guru dan laporan Kepala Sekolah / Admin.
                    </div>

                    <!-- Pilihan Kategori -->
                    <label class="form-label fw-bold text-dark mb-2">Kategori Pengajuan <span class="text-danger">*</span></label>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="status" id="statusSakitGuru" value="sakit" checked>
                            <label class="btn btn-outline-info w-100 py-2 d-flex flex-column align-items-center rounded-3 fw-semibold" for="statusSakitGuru">
                                <i class="bi bi-bandaid fs-3 mb-1"></i>
                                <span>Sakit</span>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="status" id="statusIzinGuru" value="izin">
                            <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center rounded-3 fw-semibold" for="statusIzinGuru">
                                <i class="bi bi-envelope-paper fs-3 mb-1"></i>
                                <span>Izin</span>
                            </label>
                        </div>
                    </div>

                    <!-- Tanggal -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="tglMulaiGuru" class="form-label small fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tglMulaiGuru" name="tanggal_mulai" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label for="tglSelesaiGuru" class="form-label small fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tglSelesaiGuru" name="tanggal_selesai" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-3">
                        <label for="keteranganIzinGuru" class="form-label small fw-semibold">Alasan / Keterangan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="keteranganIzinGuru" name="keterangan" rows="3" placeholder="Contoh: Sakit demam/flu istirahat dokter, atau izin dinas luar / pelatihan MGMP..." required minlength="5"></textarea>
                    </div>

                    <!-- Lampiran Surat (Opsional) -->
                    <div class="mb-2">
                        <label for="lampiranGuru" class="form-label small fw-semibold">
                            <i class="bi bi-paperclip me-1"></i>Lampiran Surat / Dokumen (Opsional)
                        </label>
                        <input class="form-control form-control-sm" type="file" id="lampiranGuru" name="lampiran" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text text-muted small">Surat tugas / surat dokter pendukung (Maks. 2MB, JPG/PNG/PDF).</div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary px-3 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4 rounded-pill fw-semibold text-dark" id="btnSubmitIzinGuru">
                        <i class="bi bi-send me-1"></i>Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tglMulai = document.getElementById('tglMulaiGuru');
    const tglSelesai = document.getElementById('tglSelesaiGuru');
    if (tglMulai && tglSelesai) {
        tglMulai.addEventListener('change', function() {
            if (tglSelesai.value < this.value) {
                tglSelesai.value = this.value;
            }
            tglSelesai.min = this.value;
        });
    }

    // ─── Handle Pengajuan Izin / Sakit Guru via AJAX & SweetAlert2 ─────────
    const formIzinGuru = document.getElementById('formPengajuanIzinGuru');
    const btnSubmitIzinGuru = document.getElementById('btnSubmitIzinGuru');

    if (formIzinGuru) {
        formIzinGuru.addEventListener('submit', function(e) {
            e.preventDefault();

            const ketInput = document.getElementById('keteranganIzinGuru');
            const ketVal = ketInput ? ketInput.value.trim() : '';

            if (ketVal.length < 5) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Keterangan Terlalu Pendek',
                        text: 'Mohon isi alasan atau keterangan pengajuan minimal 5 karakter.',
                        confirmButtonColor: '#ffc107'
                    });
                } else {
                    alert('Keterangan pengajuan minimal 5 karakter.');
                }
                return;
            }

            const formData = new FormData(formIzinGuru);
            const modalIzinEl = document.getElementById('modalPengajuanIzinGuru');
            const bsModalIzin = modalIzinEl ? (bootstrap.Modal.getInstance(modalIzinEl) || new bootstrap.Modal(modalIzinEl)) : null;

            if (btnSubmitIzinGuru) {
                btnSubmitIzinGuru.disabled = true;
                btnSubmitIzinGuru.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Mengirim...';
            }

            fetch(formIzinGuru.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (btnSubmitIzinGuru) {
                    btnSubmitIzinGuru.disabled = false;
                    btnSubmitIzinGuru.innerHTML = '<i class="bi bi-send me-1"></i>Kirim Pengajuan';
                }

                if (data.status === true) {
                    if (bsModalIzin) bsModalIzin.hide();
                    formIzinGuru.reset();

                    const statusVal  = formData.get('status') || 'izin';
                    const statusText = statusVal === 'sakit' ? 'Sakit' : 'Izin';
                    const badgeClass = statusVal === 'sakit' ? 'info text-dark' : 'primary';
                    const tglM       = formData.get('tanggal_mulai');
                    const tglS       = formData.get('tanggal_selesai');

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: `Pengajuan ${statusText} Guru Berhasil!`,
                            html: `
                                <div class="text-center py-2">
                                    <div class="mb-3 text-secondary">${data.pesan || 'Pengajuan izin / sakit dewan guru telah berhasil dicatat.'}</div>
                                    <div class="p-3 bg-light rounded-4 text-start small border mb-3">
                                        <div class="mb-1"><strong>Kategori:</strong> <span class="badge bg-${badgeClass} text-uppercase px-2 py-1">${statusText}</span></div>
                                        <div class="mb-1"><strong>Periode:</strong> ${tglM} s/d ${tglS}</div>
                                        <div><strong>Keterangan:</strong> <em>"${ketVal}"</em></div>
                                    </div>
                                    <small class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Tercatat pada data absensi guru dan dilaporkan ke Kepala Sekolah.</small>
                                </div>
                            `,
                            confirmButtonColor: '#198754',
                            confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Selesai',
                            timer: 4500,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        window.location.reload();
                    }

                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pengajuan Gagal',
                            text: data.pesan || 'Gagal menyimpan keterangan pengajuan izin/sakit guru.',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        alert(data.pesan || 'Pengajuan gagal.');
                    }
                }
            })
            .catch(err => {
                if (btnSubmitIzinGuru) {
                    btnSubmitIzinGuru.disabled = false;
                    btnSubmitIzinGuru.innerHTML = '<i class="bi bi-send me-1"></i>Kirim Pengajuan';
                }
                formIzinGuru.submit();
            });
        });
    }

    // Render QR Code di Dashboard Guru
    const barcodeValGuru = "<?= addslashes($stats['guru']['barcode'] ?? ''); ?>";
    if (barcodeValGuru) {
        const qrEl = document.getElementById("qrcodeDashboardGuru");
        if (qrEl) {
            new QRCode(qrEl, {
                text: barcodeValGuru,
                width: 200,
                height: 200,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        }
    }

    // Live Clock Updater
    const liveClockEl = document.getElementById('liveClockGuruDash');
    if (liveClockEl) {
        setInterval(() => {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            liveClockEl.textContent = `${h}:${m}:${s} WIB`;
        }, 1000);
    }
});
</script>

<!-- Modal QR Barcode Guru di Dashboard -->
<div class="modal fade" id="modalQrCodeGuruDashboard" tabindex="-1" aria-labelledby="labelQrGuruDash" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center rounded-4 border-0 shadow overflow-hidden">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold mb-0" id="labelQrGuruDash"><i class="bi bi-qr-code me-2"></i>QR Code Barcode Saya</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($stats['guru']['nama'] ?? ''); ?></h5>
                <p class="text-muted small mb-3">NIP: <?= htmlspecialchars($stats['guru']['nip'] ?? '-'); ?> • <?= htmlspecialchars($stats['guru']['mata_pelajaran'] ?? 'Guru'); ?></p>
                
                <div class="p-3 bg-light rounded-4 border d-inline-block shadow-sm mb-3">
                    <div id="qrcodeDashboardGuru" class="d-flex justify-content-center"></div>
                </div>

                <div>
                    <span class="badge bg-dark px-3 py-2 fs-6 font-monospace mb-2"><?= htmlspecialchars($stats['guru']['barcode'] ?? '-'); ?></span>
                </div>
                <p class="text-muted small mb-3">Arahkan QR Code ini ke kamera terminal di menu <strong>Absensi Guru</strong> untuk mencatat kehadiran hari ini.</p>

                <div class="d-flex justify-content-center gap-2">
                    <a href="<?= BASEURL; ?>/guru/cetak_barcode" target="_blank" class="btn btn-primary rounded-pill px-3 fw-semibold">
                        <i class="bi bi-printer me-1"></i> Cetak Kartu Absensi
                    </a>
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL SCANNER BARCODE ABSENSI GURU (GEOFENCING MAKS 100M)      -->
<!-- ============================================================== -->
<div class="modal fade" id="modalScanBarcodeGuru" tabindex="-1" aria-labelledby="modalScanBarcodeGuruLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalScanBarcodeGuruLabel">
                    <i class="bi bi-qr-code-scan me-2"></i>Scan Barcode Absensi Guru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="stopScannerGuruDash()"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Area Alert Modal -->
                <div id="modalScanAlertGuruDash" class="mb-3"></div>

                <!-- Status Jarak & Geofencing (Maksimal 100 Meter) -->
                <div class="alert alert-light border d-flex align-items-center justify-content-between p-2 mb-3 rounded-3" id="gpsStatusBoxGuruDash">
                    <div class="d-flex align-items-center gap-2 text-start">
                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        <div>
                            <span class="fw-semibold small d-block" id="gpsStatusTextGuruDash">Mendeteksi Lokasi GPS...</span>
                            <small class="text-muted" style="font-size: 11px;">Batas Jarak: Maksimal <strong>100 Meter</strong> dari Sekolah</small>
                        </div>
                    </div>
                    <span class="badge bg-secondary" id="gpsDistanceBadgeGuruDash">GPS Standby</span>
                </div>

                <!-- Status Badge -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Arahkan kamera ke barcode kartu guru atau verifikasi kode barcode Anda.</span>
                    <span class="badge bg-secondary" id="scannerStatusBadgeGuruDash">Menyiapkan Kamera...</span>
                </div>

                <!-- Viewport Kamera -->
                <div class="position-relative mb-3">
                    <div id="readerGuruDash" style="width: 100%; max-width: 480px; margin: 0 auto; border-radius: 12px; overflow: hidden; background: #000; min-height: 280px;" class="shadow-sm">
                        <div class="d-flex align-items-center justify-content-center text-white" style="min-height: 280px;">
                            <div class="text-center p-3">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <p class="mb-0 text-white-50 small">Meminta izin kamera...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kontrol Scanner -->
                <div class="d-flex gap-2 justify-content-center flex-wrap mb-3">
                    <button type="button" id="btnRestartCameraGuruDash" class="btn btn-sm btn-outline-primary d-none" onclick="startScannerGuruDash()">
                        <i class="bi bi-camera-video me-1"></i>Mulai Ulang Kamera
                    </button>
                    <button type="button" id="btnToggleManualGuruDash" class="btn btn-sm btn-outline-secondary" onclick="toggleManualInputGuruDash()">
                        <i class="bi bi-keyboard me-1"></i>Input / Verifikasi Barcode
                    </button>
                </div>

                <!-- Alternatif Input Manual Barcode -->
                <div id="manualInputContainerGuruDash" class="bg-light p-3 rounded-3 border mb-3">
                    <label for="inputManualBarcodeGuruDash" class="form-label fw-semibold text-dark small mb-1">
                        <i class="bi bi-upc-scan me-1"></i>Barcode Guru
                    </label>
                    <div class="input-group">
                        <input type="text" id="inputManualBarcodeGuruDash" class="form-control" 
                               value="<?= htmlspecialchars($stats['guru']['barcode'] ?? '') ?>" 
                               placeholder="Contoh: <?= htmlspecialchars($stats['guru']['barcode'] ?? 'GRU-001') ?>" autocomplete="off">
                        <button class="btn btn-dark" type="button" id="btnSubmitManualGuruDash" onclick="submitManualBarcodeGuruDash()">
                            <i class="bi bi-send me-1"></i>Kirim Absen
                        </button>
                    </div>
                    <small class="text-muted">Batas jarak maksimal adalah 100 meter dari sekolah. Pastikan GPS aktif.</small>
                </div>

                <!-- Area Rincian Hasil Scan -->
                <div id="modalScanResultGuruDash" class="d-none mt-3"></div>
            </div>
            <div class="modal-footer border-0 bg-light py-2 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal" onclick="stopScannerGuruDash()">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include QRCode JS & html5-qrcode JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
// Konfigurasi & Variabel Absensi Guru Dashboard
const GURU_DASH_SCAN_URL   = '<?= BASEURL; ?>/guru/scan';
const GURU_DASH_CSRF_TOKEN = '<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>';
const GURU_DASH_SEKOLAH_LAT = <?= (float)($data['lokasi_sekolah']['latitude'] ?? -6.208763); ?>;
const GURU_DASH_SEKOLAH_LNG = <?= (float)($data['lokasi_sekolah']['longitude'] ?? 106.845599); ?>;
const GURU_DASH_MAX_RADIUS  = 100; // Strict 100 meter!
const GURU_DASH_GEOFENCE_ACTIVE = <?= ($data['lokasi_sekolah']['geofence_aktif'] ?? false) ? 'true' : 'false'; ?>;

let html5QrcodeGuruDash = null;
let isScanningGuruDash = false;
let isStartingGuruDash = false;
let scanCooldownGuruDash = false;
let lastScannedGuruDash = '';
let userCoordsGuruDash = null;

function hitungJarakGuruDash(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return Math.round(R * c);
}

function updateGpsLocationGuruDash(callback) {
    const badge = document.getElementById('gpsDistanceBadgeGuruDash');
    const text  = document.getElementById('gpsStatusTextGuruDash');
    const cardBadge = document.getElementById('cardJarakBadgeGuruDash');

    if (!navigator.geolocation) {
        if (text) text.textContent = 'Geolocation tidak didukung browser ini.';
        if (badge) { badge.className = 'badge bg-danger'; badge.textContent = 'No GPS'; }
        if (cardBadge) { cardBadge.className = 'badge bg-danger'; cardBadge.textContent = 'No GPS'; }
        if (callback) callback(null);
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            userCoordsGuruDash = {
                latitude: pos.coords.latitude,
                longitude: pos.coords.longitude,
                accuracy: pos.coords.accuracy
            };
            const jarak = hitungJarakGuruDash(userCoordsGuruDash.latitude, userCoordsGuruDash.longitude, GURU_DASH_SEKOLAH_LAT, GURU_DASH_SEKOLAH_LNG);
            userCoordsGuruDash.jarak = jarak;

            if (cardBadge) {
                if (!GURU_DASH_GEOFENCE_ACTIVE) {
                    cardBadge.className = 'badge bg-info text-dark';
                    cardBadge.innerHTML = `<i class="bi bi-geo-alt me-1"></i>${jarak}m (Bebas Radius)`;
                } else if (jarak <= GURU_DASH_MAX_RADIUS) {
                    cardBadge.className = 'badge bg-success';
                    cardBadge.innerHTML = `<i class="bi bi-check-circle me-1"></i>${jarak}m (Valid)`;
                } else {
                    cardBadge.className = 'badge bg-danger';
                    cardBadge.innerHTML = `<i class="bi bi-x-circle me-1"></i>${jarak}m (Di Luar Batas)`;
                }
            }

            if (badge && text) {
                if (!GURU_DASH_GEOFENCE_ACTIVE) {
                    badge.className = 'badge bg-info text-dark';
                    badge.innerHTML = `<i class="bi bi-geo-alt me-1"></i>${jarak}m (Bebas Radius)`;
                    text.innerHTML = `Batasan Jarak Nonaktif &bull; Terdeteksi <strong>${jarak} meter</strong> dari sekolah`;
                } else if (jarak <= GURU_DASH_MAX_RADIUS) {
                    badge.className = 'badge bg-success';
                    badge.innerHTML = `<i class="bi bi-check-circle me-1"></i>${jarak}m (Dalam Radius)`;
                    text.innerHTML = `Lokasi Valid &bull; Terdeteksi <strong>${jarak} meter</strong> dari sekolah`;
                } else {
                    badge.className = 'badge bg-danger';
                    badge.innerHTML = `<i class="bi bi-x-circle me-1"></i>${jarak}m (Di Luar Radius)`;
                    text.innerHTML = `<span class="text-danger fw-bold">Di Luar Batas!</span> Jarak Anda <strong>${jarak} meter</strong> (Maks 100m)`;
                    if (isScanningGuruDash) {
                        stopScannerGuruDash();
                    }
                    tampilkanDitolakJarakGuruDash(jarak);
                }
            }

            if (callback) callback(userCoordsGuruDash);
        },
        (err) => {
            if (badge && text) {
                badge.className = 'badge bg-warning text-dark';
                badge.textContent = 'GPS Nonaktif';
                text.innerHTML = '<span class="text-warning fw-semibold">Mohon izinkan akses lokasi (GPS) pada browser</span>';
            }
            if (cardBadge) {
                cardBadge.className = 'badge bg-warning text-dark';
                cardBadge.textContent = 'GPS Nonaktif';
            }
            if (callback) callback(null);
        },
        { enableHighAccuracy: true, timeout: 8000, maximumAge: 3000 }
    );
}

function setBadgeGuruDash(text, colorClass) {
    const b = document.getElementById('scannerStatusBadgeGuruDash');
    if (b) {
        b.className = `badge ${colorClass}`;
        b.textContent = text;
    }
}

function showAlertGuruDash(message, type) {
    const area = document.getElementById('modalScanAlertGuruDash');
    if (area) {
        area.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center gap-2 mb-0" role="alert">
                <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill'} fs-5 flex-shrink-0"></i>
                <div>${message}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
    }
}

function clearAlertGuruDash() {
    const area = document.getElementById('modalScanAlertGuruDash');
    if (area) area.innerHTML = '';
}

function tampilkanDitolakJarakGuruDash(jarak) {
    setBadgeGuruDash('Di Luar Radius', 'bg-danger');
    const jarakStr = (jarak !== null && jarak !== undefined) ? `${jarak} meter` : 'Tidak terdeteksi';
    showAlertGuruDash(`Absensi ditolak: Anda berada di luar area sekolah! Jarak Anda saat ini ${jarakStr} dari sekolah (Batas maksimal adalah ${GURU_DASH_MAX_RADIUS} meter).`, 'danger');

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: `Di Luar Batas Jarak Absensi (Maks ${GURU_DASH_MAX_RADIUS}m)`,
            html: `
                <div class="text-center py-2">
                    <i class="bi bi-geo-alt-fill text-danger fs-1 mb-2 d-block"></i>
                    <p class="text-secondary mb-2">Absensi ditolak karena perangkat Anda berada di luar area sekolah.</p>
                    <div class="alert alert-danger py-2 px-3 small rounded-3 mb-2">
                        Jarak terdeteksi: <strong>${jarakStr}</strong><br>
                        Batas maksimal yang diizinkan: <strong>${GURU_DASH_MAX_RADIUS} meter</strong>
                    </div>
                    <small class="text-muted">Pastikan Anda sudah berada di lingkungan sekolah (maksimal ${GURU_DASH_MAX_RADIUS} meter) dan GPS aktif.</small>
                </div>
            `,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Tutup'
        });
    }
}

window.startScannerGuruDash = async function() {
    if (isScanningGuruDash || isStartingGuruDash) return;
    isStartingGuruDash = true;

    clearAlertGuruDash();

    // Validasi ketat geofencing client-side: jika jarak > 100m dan geofence aktif, tolak langsung!
    if (GURU_DASH_GEOFENCE_ACTIVE && userCoordsGuruDash && userCoordsGuruDash.jarak > GURU_DASH_MAX_RADIUS) {
        tampilkanDitolakJarakGuruDash(userCoordsGuruDash.jarak);
        isStartingGuruDash = false;
        return;
    }

    const readerDiv = document.getElementById('readerGuruDash');
    const restartBtn = document.getElementById('btnRestartCameraGuruDash');

    if (restartBtn) restartBtn.classList.add('d-none');
    setBadgeGuruDash('Menyiapkan Kamera...', 'bg-warning text-dark');

    // Bersihkan instance lama jika ada
    if (html5QrcodeGuruDash) {
        try {
            if (isScanningGuruDash) await html5QrcodeGuruDash.stop();
            html5QrcodeGuruDash.clear();
        } catch(e) {}
        html5QrcodeGuruDash = null;
        isScanningGuruDash = false;
    }

    if (readerDiv) {
        readerDiv.innerHTML = '';
    }

    try {
        html5QrcodeGuruDash = new Html5Qrcode("readerGuruDash");
    } catch (e) {
        isStartingGuruDash = false;
        showAlertGuruDash('Gagal memuat modul kamera. Silakan gunakan Input Manual.', 'danger');
        setBadgeGuruDash('Kamera Error', 'bg-danger');
        return;
    }

    const config = { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };

    const devices = await Html5Qrcode.getCameras().catch(() => []);
    let cameraSelection = { facingMode: "environment" };
    if (devices && devices.length > 0) {
        const backCam = devices.find(d => /back|rear|environment/i.test(d.label));
        cameraSelection = backCam ? backCam.id : devices[0].id;
    }

    try {
        await html5QrcodeGuruDash.start(
            cameraSelection,
            config,
            onScanSuccessGuruDash,
            () => {}
        );
        isScanningGuruDash = true;
        isStartingGuruDash = false;
        setBadgeGuruDash('Kamera Aktif (Scanning...)', 'bg-success');
    } catch (err) {
        // Fallback kamera depan
        try {
            if (readerDiv) readerDiv.innerHTML = '';
            await html5QrcodeGuruDash.start(
                { facingMode: "user" },
                config,
                onScanSuccessGuruDash,
                () => {}
            );
            isScanningGuruDash = true;
            isStartingGuruDash = false;
            setBadgeGuruDash('Kamera Depan Aktif', 'bg-success');
        } catch (errFallback) {
            isScanningGuruDash = false;
            isStartingGuruDash = false;
            setBadgeGuruDash('Kamera Gagal', 'bg-danger');
            if (restartBtn) restartBtn.classList.remove('d-none');
            showAlertGuruDash('Kamera tidak dapat diakses atau izin ditolak. Silakan gunakan verifikasi barcode manual.', 'warning');
        }
    }
};

window.stopScannerGuruDash = async function() {
    isStartingGuruDash = false;
    if (html5QrcodeGuruDash) {
        try {
            if (isScanningGuruDash) await html5QrcodeGuruDash.stop();
            html5QrcodeGuruDash.clear();
        } catch(e) {}
        html5QrcodeGuruDash = null;
    }
    isScanningGuruDash = false;
    setBadgeGuruDash('Kamera Berhenti', 'bg-secondary');
};

function toggleManualInputGuruDash() {
    const el = document.getElementById('manualInputContainerGuruDash');
    if (el) {
        el.classList.toggle('d-none');
        if (!el.classList.contains('d-none')) {
            const input = document.getElementById('inputManualBarcodeGuruDash');
            if (input) input.focus();
        }
    }
}

function submitManualBarcodeGuruDash() {
    const input = document.getElementById('inputManualBarcodeGuruDash');
    const val = input ? input.value.trim() : '';
    if (!val) {
        showAlertGuruDash('Masukkan atau periksa kode barcode guru terlebih dahulu.', 'warning');
        return;
    }

    // Validasi ketat geofencing client-side
    if (GURU_DASH_GEOFENCE_ACTIVE && userCoordsGuruDash && userCoordsGuruDash.jarak > GURU_DASH_MAX_RADIUS) {
        tampilkanDitolakJarakGuruDash(userCoordsGuruDash.jarak);
        return;
    }

    prosesAbsensiKeServerGuruDash(val);
}

function onScanSuccessGuruDash(decodedText) {
    if (scanCooldownGuruDash) return;
    if (decodedText === lastScannedGuruDash) return;

    // Validasi ketat geofencing client-side
    if (GURU_DASH_GEOFENCE_ACTIVE && userCoordsGuruDash && userCoordsGuruDash.jarak > GURU_DASH_MAX_RADIUS) {
        tampilkanDitolakJarakGuruDash(userCoordsGuruDash.jarak);
        return;
    }

    scanCooldownGuruDash = true;
    lastScannedGuruDash = decodedText;
    setTimeout(() => {
        scanCooldownGuruDash = false;
        lastScannedGuruDash = '';
    }, 3000);

    if (html5QrcodeGuruDash && isScanningGuruDash) {
        html5QrcodeGuruDash.pause();
    }

    prosesAbsensiKeServerGuruDash(decodedText, () => {
        if (html5QrcodeGuruDash && isScanningGuruDash) {
            html5QrcodeGuruDash.resume();
        }
    });
}

function prosesAbsensiKeServerGuruDash(barcode, onDone) {
    const executeSend = (coords) => {
        // Validasi ketat geofencing client-side
        if (GURU_DASH_GEOFENCE_ACTIVE && coords && coords.jarak > GURU_DASH_MAX_RADIUS) {
            if (typeof onDone === 'function') onDone();
            tampilkanDitolakJarakGuruDash(coords.jarak);
            return;
        }

        setBadgeGuruDash('Memvalidasi...', 'bg-warning text-dark');
        showAlertGuruDash('<div class="spinner-border spinner-border-sm me-2" role="status"></div>Memvalidasi lokasi & barcode ke server...', 'info');

        const formData = new FormData();
        formData.append('barcode', barcode);
        formData.append('csrf_token', GURU_DASH_CSRF_TOKEN);
        if (coords) {
            formData.append('latitude', coords.latitude);
            formData.append('longitude', coords.longitude);
        }

        fetch(GURU_DASH_SCAN_URL, {
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
            if (typeof onDone === 'function') onDone();
            tanganiHasilAbsensiGuruDash(data);
        })
        .catch(err => {
            if (typeof onDone === 'function') onDone();
            setBadgeGuruDash('Koneksi Gagal', 'bg-danger');
            showAlertGuruDash('Koneksi ke server gagal. Periksa jaringan Anda dan coba kembali.', 'danger');
        });
    };

    if (userCoordsGuruDash) {
        executeSend(userCoordsGuruDash);
    } else {
        updateGpsLocationGuruDash((coords) => {
            if (!coords && GURU_DASH_GEOFENCE_ACTIVE) {
                if (typeof onDone === 'function') onDone();
                showAlertGuruDash('Akses lokasi (GPS) diperlukan untuk memverifikasi batasan maksimal 100 meter dari sekolah.', 'danger');
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

function tanganiHasilAbsensiGuruDash(data) {
    const modalEl = document.getElementById('modalScanBarcodeGuru');

    if (data.status === true) {
        window.stopScannerGuruDash();
        setBadgeGuruDash('Berhasil', 'bg-success');
        showAlertGuruDash(data.pesan || 'Absensi berhasil dicatat.', 'success');

        const isPulang    = data.tipe_absen === 'pulang';
        const isTerlambat = data.status_kehadiran === 'terlambat';
        const badgeColor  = isPulang ? 'primary' : (isTerlambat ? 'warning text-dark' : 'success');
        const statusLabel = isPulang ? 'Absen Pulang Berhasil' : (isTerlambat ? 'Terlambat' : 'Hadir Tepat Waktu');
        const jamScan     = data.jam || '<?= date('H:i:s') ?>';
        const jamMasuk    = (data.jam_scan || data.jam_masuk || jamScan).substring(0, 5);
        const jamPulang   = data.jam_pulang ? data.jam_pulang.substring(0, 5) : (isPulang ? jamScan.substring(0, 5) : '-');

        if (modalEl) {
            const bsModal = bootstrap.Modal.getInstance(modalEl);
            if (bsModal) bsModal.hide();
        }

        if (typeof Swal !== 'undefined') {
            const swalTitle = isPulang 
                ? 'Absen Pulang Guru Berhasil!' 
                : (isTerlambat ? 'Absen Masuk Berhasil (Terlambat)!' : 'Absen Masuk Berhasil (Tepat Waktu)!');

            Swal.fire({
                icon: isPulang ? 'success' : (isTerlambat ? 'warning' : 'success'),
                title: swalTitle,
                html: `
                    <div class="text-center py-2">
                        <div class="mb-3 text-secondary">${data.pesan || 'Kehadiran Bapak/Ibu Guru telah berhasil dicatat ke sistem absensi.'}</div>
                        <div class="p-3 bg-light rounded-4 text-start small border mb-3">
                            <div class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($stats['guru']['nama'] ?? 'Guru') ?></div>
                            <div class="mb-1"><strong>NIP:</strong> <?= htmlspecialchars($stats['guru']['nip'] ?? '-') ?></div>
                            <div class="mb-1"><strong>Jam Masuk:</strong> <span class="badge bg-success bg-opacity-75">${jamMasuk} WIB</span></div>
                            ${isPulang ? `<div class="mb-1"><strong>Jam Pulang:</strong> <span class="badge bg-primary">${jamPulang} WIB</span></div>` : ''}
                            <div class="mb-1"><strong>Jarak:</strong> <span class="badge bg-success">${data.jarak || 0} meter (Valid)</span></div>
                            <div><strong>Status:</strong> <span class="badge bg-${badgeColor} fs-6">${statusLabel}</span></div>
                        </div>
                        <small class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>${isPulang ? 'Terima kasih atas dedikasi mengajar hari ini! Hati-hati di jalan.' : 'Kehadiran otomatis dilaporkan ke Kepala Sekolah.'}</small>
                    </div>
                `,
                confirmButtonColor: isPulang ? '#0d6efd' : (isTerlambat ? '#ffc107' : '#198754'),
                confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Selesai',
                timer: 4500,
                timerProgressBar: true
            }).then(() => {
                window.location.reload();
            });
        } else {
            setTimeout(() => { window.location.reload(); }, 2000);
        }

    } else if (data.di_luar_radius) {
        // LEBIH DARI 100 METER
        tampilkanDitolakJarakGuruDash(data.jarak);

    } else if (data.sudah_absen === true) {
        window.stopScannerGuruDash();
        setBadgeGuruDash('Sudah Absen', 'bg-info text-dark');
        showAlertGuruDash(data.pesan || 'Absensi hari ini sudah tercatat.', 'info');

        const isLengkap    = data.sudah_lengkap === true;
        const jamMasukStr  = (data.jam_scan || data.jam_masuk || '-').substring(0, 5);
        const jamPulangStr = (data.jam_pulang || '-').substring(0, 5);

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: isLengkap ? 'Absensi Hari Ini Sudah Lengkap' : 'Sudah Absen Hari Ini',
                html: isLengkap ? `
                    <div class="text-center py-2">
                        <p class="mb-3 text-muted">${data.pesan}</p>
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
                ` : `
                    <div class="text-center py-2">
                        <p class="mb-2 text-muted">${data.pesan}</p>
                    </div>
                `,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Tutup'
            });
        }

    } else {
        setBadgeGuruDash('Gagal', 'bg-danger');
        showAlertGuruDash(data.pesan || 'Format barcode tidak valid atau di luar jam jadwal.', 'danger');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Absensi Gagal',
                text: data.pesan || 'Gagal memproses absensi guru.',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Coba Lagi'
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modalScanGuruEl = document.getElementById('modalScanBarcodeGuru');
    if (modalScanGuruEl) {
        modalScanGuruEl.addEventListener('shown.bs.modal', function() {
            updateGpsLocationGuruDash();
            window.startScannerGuruDash();
        });

        modalScanGuruEl.addEventListener('hidden.bs.modal', function() {
            window.stopScannerGuruDash();
            clearAlertGuruDash();
        });
    }

    // Auto-detect GPS saat pertama kali halaman dimuat untuk kartu
    updateGpsLocationGuruDash();

    const manualInputEl = document.getElementById('inputManualBarcodeGuruDash');
    if (manualInputEl) {
        manualInputEl.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') submitManualBarcodeGuruDash();
        });
    }
});
</script>

