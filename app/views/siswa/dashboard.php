<?php
$absen = $data['stats']['absen_hari_ini'];
?>

<!-- Alert Notifikasi Dashboard -->
<div id="dashboardAlertArea" class="mb-3">
    <?php Flasher::flash(); ?>
</div>

<!-- Header Greeting & Tombol Scan -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 p-3 bg-white rounded-4 shadow-sm border">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 d-inline-flex align-items-center justify-content-center shadow-sm flex-shrink-0" 
             style="width: 48px; height: 48px; font-size: 22px;">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div>
            <h5 class="mb-1 text-gray-800 fw-bold">Selamat datang, <?= htmlspecialchars($data['stats']['info_siswa']['nama']) ?>! 👋</h5>
            <p class="text-muted mb-0 small">
                <i class="bi bi-mortarboard me-1"></i>Kelas: <?= htmlspecialchars($data['stats']['info_siswa']['nama_kelas'] ?? '-') ?> &bull; 
                NIS: <?= htmlspecialchars($data['stats']['info_siswa']['nis'] ?? '-') ?> &bull; 
                <i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($data['tanggal_hari_ini'] ?? DateHelper::tanggalIndo(date('Y-m-d'), true)) ?> &bull; 
                <i class="bi bi-clock me-1"></i><span id="liveClockSiswaTop" class="fw-semibold text-dark"><?= $data['jam_sekarang'] ?? DateHelper::jamSekarang('H:i:s') ?> WIB</span>
            </p>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-info text-white shadow-sm rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalQrCodeSiswaDashboard">
            <i class="bi bi-qr-code me-1"></i>QR Barcode Saya
        </button>
        <button type="button" class="btn btn-outline-warning text-dark shadow-sm rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPengajuanIzinSiswa">
            <i class="bi bi-file-earmark-medical me-1"></i>Ajukan Izin / Sakit
        </button>
        <?php if(!$absen): ?>
            <button type="button" class="btn btn-primary shadow-sm rounded-pill px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalScannerSiswa">
                <i class="bi bi-qr-code-scan me-2"></i>Mulai Scan
            </button>
        <?php elseif(empty($absen['jam_pulang']) && in_array($absen['status'], ['hadir', 'terlambat'])): ?>
            <button type="button" class="btn btn-warning text-dark shadow-sm rounded-pill px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalScannerSiswa">
                <i class="bi bi-box-arrow-left me-2"></i>Scan Absen Pulang
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-success shadow-sm rounded-pill px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalScannerSiswa">
                <i class="bi bi-check-circle-fill me-2"></i>Absensi Hari Ini Lengkap
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Status Kehadiran Hari Ini -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100 rounded-4 overflow-hidden" id="cardStatusHariIni">
            <div class="card-header bg-white border-0 pt-4 pb-0 text-center">
                <h6 class="text-uppercase fw-bold text-primary mb-0">Status Absensi Hari Ini</h6>
            </div>
            <div class="card-body text-center d-flex flex-column justify-content-center py-4" id="bodyStatusHariIni">
                <?php if(!$absen): ?>
                    <div class="mb-3" id="iconStatusHariIni">
                        <i class="bi bi-x-circle text-danger" style="font-size: 5rem; opacity: 0.8;"></i>
                    </div>
                    <h4 class="text-danger fw-bold" id="textStatusHariIni">Belum Absen</h4>
                    <p class="text-muted px-3" id="descStatusHariIni">Anda belum mencatat kehadiran hari ini. Klik tombol <strong>Mulai Scan</strong> untuk scan barcode atau <strong>Ajukan Izin / Sakit</strong> jika berhalangan hadir.</p>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPengajuanIzinSiswa">
                            <i class="bi bi-file-earmark-medical me-1"></i>Ajukan Izin / Sakit Sekarang
                        </button>
                    </div>
                <?php else: ?>
                    <?php 
                        $status = $absen['status'];
                        $iconMap = [
                            'hadir'     => ['icon' => 'bi-check-circle-fill', 'color' => 'text-success', 'text' => 'Hadir Tepat Waktu'],
                            'terlambat' => ['icon' => 'bi-exclamation-circle-fill', 'color' => 'text-warning', 'text' => 'Terlambat'],
                            'sakit'     => ['icon' => 'bi-bandaid-fill', 'color' => 'text-info', 'text' => 'Sakit (Izin Sakit)'],
                            'izin'      => ['icon' => 'bi-envelope-paper-fill', 'color' => 'text-primary', 'text' => 'Izin (Tercatat)'],
                            'alpa'      => ['icon' => 'bi-x-square-fill', 'color' => 'text-danger', 'text' => 'Alpa']
                        ];
                        $st = $iconMap[$status] ?? $iconMap['alpa'];
                    ?>
                    <div class="mb-3" id="iconStatusHariIni">
                        <i class="bi <?= $st['icon'] ?> <?= $st['color'] ?>" style="font-size: 5rem; drop-shadow: 0 4px 6px rgba(0,0,0,0.1);"></i>
                    </div>
                    <h4 class="<?= $st['color'] ?> fw-bold" id="textStatusHariIni"><?= $st['text'] ?></h4>
                    
                    <?php if(in_array($status, ['hadir', 'terlambat'])): ?>
                        <div class="mt-3 d-flex flex-wrap gap-2 justify-content-center" id="badgeJamMasuk">
                            <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                                <i class="bi bi-box-arrow-in-right me-1 text-success"></i>Masuk: <strong><?= substr($absen['jam_masuk'], 0, 5) ?> WIB</strong>
                            </span>
                            <?php if(!empty($absen['jam_pulang'])): ?>
                                <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                                    <i class="bi bi-box-arrow-left me-1 text-primary"></i>Pulang: <strong><?= substr($absen['jam_pulang'], 0, 5) ?> WIB</strong>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border px-3 py-2 fs-6">
                                    <i class="bi bi-clock-history me-1 text-warning"></i>Pulang: <em>Belum Absen</em>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php elseif(in_array($status, ['sakit', 'izin'])): ?>
                        <div class="mt-2 px-3 text-muted small" id="badgeKeteranganIzin">
                            <div class="p-2 bg-light rounded-3 border text-start">
                                <i class="bi bi-chat-left-quote me-1 text-primary"></i><strong>Keterangan:</strong><br>
                                <?= nl2br(htmlspecialchars($absen['keterangan'] ?? '-')) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Akumulasi Absensi -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100 rounded-4">
            <div class="card-header bg-white border-bottom pt-4 pb-3">
                <h6 class="m-0 font-weight-bold text-dark"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Akumulasi Kehadiran (Semester Ini)</h6>
            </div>
            <div class="card-body d-flex align-items-center">
                <div class="row w-100 text-center g-3">
                    <div class="col-sm-4">
                        <div class="p-4 bg-success bg-opacity-10 rounded-4 border border-success border-opacity-25 h-100 transition-hover">
                            <i class="bi bi-calendar-check text-success fs-3 mb-2 d-block"></i>
                            <div class="display-5 fw-bold text-success mb-1" id="countTotalHadir"><?= $data['stats']['total_hadir'] ?></div>
                            <div class="text-uppercase fw-semibold text-success small">Hadir</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-4 bg-warning bg-opacity-10 rounded-4 border border-warning border-opacity-25 h-100 transition-hover">
                            <i class="bi bi-calendar-minus text-warning fs-3 mb-2 d-block"></i>
                            <div class="display-5 fw-bold text-warning mb-1" id="countTotalTerlambat"><?= $data['stats']['total_terlambat'] ?></div>
                            <div class="text-uppercase fw-semibold text-warning small">Terlambat</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-4 bg-danger bg-opacity-10 rounded-4 border border-danger border-opacity-25 h-100 transition-hover">
                            <i class="bi bi-calendar-x text-danger fs-3 mb-2 d-block"></i>
                            <div class="display-5 fw-bold text-danger mb-1" id="countTotalLainnya"><?= $data['stats']['total_absen_lainnya'] ?></div>
                            <div class="text-uppercase fw-semibold text-danger small">Izin/Skt/Alp</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MATA PELAJARAN HARIAN & JADWAL KELAS SISWA                     -->
<!-- ============================================================== -->
<?php
$hariIni = $data['hari_ini'] ?? DateHelper::hariIni();
$jadwalHariIni = $data['jadwal_hari_ini'] ?? [];
$jadwalMingguan = $data['jadwal_mingguan'] ?? [];
$daftarHari = DateHelper::getDaftarHari(true);
$namaKelasSiswa = $data['stats']['info_siswa']['nama_kelas'] ?? '-';
$currentTime = DateHelper::jamSekarang('H:i:s');
$tanggalHariIni = $data['tanggal_hari_ini'] ?? DateHelper::tanggalIndo(date('Y-m-d'), true);
?>
<div class="card shadow-sm border-0 rounded-4 mb-4" id="sectionJadwalSiswa">
    <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-journal-bookmark-fill fs-5"></i>
            </div>
            <div>
                <h6 class="m-0 fw-bold text-dark">Mata Pelajaran Harian & Jadwal Kelas</h6>
                <small class="text-muted">
                    Kelas: <strong><?= htmlspecialchars($namaKelasSiswa) ?></strong> &bull; 
                    <i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($tanggalHariIni) ?> &bull; 
                    <i class="bi bi-clock me-1"></i><span id="liveClockSiswaDash" class="fw-semibold text-dark"><?= DateHelper::jamSekarang('H:i:s') ?> WIB</span>
                </small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASEURL; ?>/siswa/jadwal" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="bi bi-calendar3 me-1"></i>Lihat Jadwal Lengkap <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="card-body p-3">
        <!-- Tab Navigasi Harian vs Mingguan -->
        <ul class="nav nav-tabs card-header-tabs mb-3 border-bottom pb-2" id="siswaJadwalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold" id="tab-harian-siswa" data-bs-toggle="tab" data-bs-target="#pane-harian-siswa" type="button" role="tab">
                    <i class="bi bi-calendar-day me-1 text-primary"></i>Mata Pelajaran Hari Ini (<?= htmlspecialchars($hariIni) ?>)
                    <span class="badge bg-primary rounded-pill ms-1"><?= count($jadwalHariIni) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold" id="tab-mingguan-siswa" data-bs-toggle="tab" data-bs-target="#pane-mingguan-siswa" type="button" role="tab">
                    <i class="bi bi-calendar-week me-1 text-success"></i>Jadwal Mingguan (Senin - Sabtu)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="siswaJadwalTabsContent">
            <!-- PANE 1: JADWAL HARI INI -->
            <div class="tab-pane fade show active" id="pane-harian-siswa" role="tabpanel">
                <?php if (!empty($jadwalHariIni)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 170px;">Jam KBM</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Guru Pengajar</th>
                                    <th>Ruangan</th>
                                    <th>Keterangan / Materi</th>
                                    <th class="text-center" style="width: 130px;">Status KBM</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jadwalHariIni as $j): ?>
                                    <?php
                                        $kbmStatus = DateHelper::getStatusKBM($hariIni, $j['jam_mulai'], $j['jam_selesai']);
                                        $isNow = ($kbmStatus['status'] === 'sedang_kbm');
                                    ?>
                                    <tr class="<?= $isNow ? 'table-primary bg-opacity-25' : '' ?>">
                                        <td class="ps-3">
                                            <span class="badge <?= $isNow ? 'bg-primary' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> px-2 py-1 fw-bold">
                                                <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($j['jam_mulai'])) ?> - <?= date('H:i', strtotime($j['jam_selesai'])) ?>
                                            </span>
                                            <?php if ($isNow): ?>
                                                <div class="small text-success fw-bold mt-1"><i class="bi bi-broadcast me-1"></i>Sedang KBM</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <?= htmlspecialchars($j['nama_mapel'] ?? 'Mata Pelajaran') ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?php if (!empty($j['kode_mapel'])): ?>
                                                    <span class="badge bg-light text-secondary border me-1"><?= htmlspecialchars($j['kode_mapel']) ?></span>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($j['kelompok'] ?? 'Umum') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark small"><?= htmlspecialchars($j['nama_guru']) ?></div>
                                                    <div class="text-muted" style="font-size: 11px;">NIP: <?= htmlspecialchars($j['nip'] ?? '-') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($j['ruangan'] ?? 'Ruang Kelas') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted small"><?= htmlspecialchars($j['keterangan'] ?? '-') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $kbmStatus['badge_class'] ?> rounded-pill px-3 py-1">
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
                        <i class="bi bi-cup-hot text-primary fs-1 mb-2 d-inline-block"></i>
                        <h6 class="fw-bold text-dark">Tidak ada jadwal mata pelajaran hari ini (<?= htmlspecialchars($hariIni) ?>)</h6>
                        <p class="text-muted small mb-3">Hari ini tidak ada kegiatan belajar mengajar terjadwal untuk kelas <?= htmlspecialchars($namaKelasSiswa) ?>. Anda dapat melihat jadwal lengkap mingguan.</p>
                        <a href="<?= BASEURL; ?>/siswa/jadwal" class="btn btn-sm btn-primary rounded-pill px-4">
                            <i class="bi bi-calendar-week me-1"></i>Buka Jadwal Lengkap Kelas
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- PANE 2: JADWAL LENGKAP MINGGUAN DENGAN PILLS HARI -->
            <div class="tab-pane fade" id="pane-mingguan-siswa" role="tabpanel">
                <div class="row g-2 mb-3">
                    <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h): ?>
                        <?php 
                            $cnt = count($jadwalMingguan[$h] ?? []);
                            $isHIni = ($h === $hariIni);
                        ?>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="p-2 text-center rounded-3 border <?= $isHIni ? 'border-primary bg-primary bg-opacity-10' : 'bg-light' ?>">
                                <div class="fw-bold small <?= $isHIni ? 'text-primary' : 'text-dark' ?>"><?= $h ?></div>
                                <span class="badge <?= $cnt > 0 ? ($isHIni ? 'bg-primary' : 'bg-secondary') : 'bg-light text-muted border' ?> rounded-pill" style="font-size: 10px;">
                                    <?= $cnt ?> Mapel
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="accordion" id="accordionJadwalMingguan">
                    <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $index => $h): ?>
                        <?php 
                            $listM = $jadwalMingguan[$h] ?? [];
                            $isExpanded = ($h === $hariIni) || ($hariIni === 'Minggu' && $h === 'Senin');
                        ?>
                        <div class="accordion-item border rounded-3 mb-2 overflow-hidden shadow-none">
                            <h2 class="accordion-header" id="heading<?= $h ?>">
                                <button class="accordion-button <?= $isExpanded ? '' : 'collapsed' ?> py-2 px-3 fw-semibold small bg-white text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $h ?>">
                                    <div class="d-flex justify-content-between align-items-center w-100 me-2">
                                        <span>
                                            <i class="bi bi-calendar-event me-2 text-primary"></i>Hari <?= $h ?>
                                            <?php if ($h === $hariIni): ?>
                                                <span class="badge bg-success ms-2">Hari Ini</span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="badge bg-secondary rounded-pill"><?= count($listM) ?> Pelajaran</span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse<?= $h ?>" class="accordion-collapse collapse <?= $isExpanded ? 'show' : '' ?>" data-bs-parent="#accordionJadwalMingguan">
                                <div class="accordion-body p-0">
                                    <?php if (!empty($listM)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 13px;">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-3" style="width: 140px;">Waktu</th>
                                                        <th>Mata Pelajaran</th>
                                                        <th>Guru</th>
                                                        <th>Ruangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($listM as $item): ?>
                                                    <tr>
                                                        <td class="ps-3 fw-semibold text-primary">
                                                            <?= date('H:i', strtotime($item['jam_mulai'])) ?> - <?= date('H:i', strtotime($item['jam_selesai'])) ?>
                                                        </td>
                                                        <td class="fw-bold"><?= htmlspecialchars($item['nama_mapel']) ?></td>
                                                        <td><?= htmlspecialchars($item['nama_guru']) ?></td>
                                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($item['ruangan']) ?></span></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-3 text-muted text-center small">Tidak ada jadwal KBM pada hari <?= $h ?>.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- RIWAYAT PENGAJUAN SAKIT / IZIN SISWA                           -->
<!-- ============================================================== -->
<?php if (!empty($data['stats']['riwayat_izin'])): ?>
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-dark">
            <i class="bi bi-journal-medical me-2 text-warning"></i>Riwayat Pengajuan Sakit / Izin
        </h6>
        <span class="badge bg-light text-dark border"><?= count($data['stats']['riwayat_izin']) ?> Pengajuan Terakhir</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <th>Kategori</th>
                        <th>Alasan / Keterangan</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['stats']['riwayat_izin'] as $iz): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= date('d F Y', strtotime($iz['tanggal'])) ?></td>
                        <td>
                            <?php if ($iz['status'] === 'sakit'): ?>
                                <span class="badge bg-info text-dark"><i class="bi bi-bandaid me-1"></i>Sakit</span>
                            <?php else: ?>
                                <span class="badge bg-primary"><i class="bi bi-envelope me-1"></i>Izin</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($iz['keterangan'] ?? '-') ?></td>
                        <td class="text-center">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                <i class="bi bi-check-circle me-1"></i>Tercatat
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ============================================================== -->
<!-- MODAL QR BARCODE SISWA DI DASHBOARD (TAMPILAN RESMI IDENTIK)   -->
<!-- ============================================================== -->
<div class="modal fade" id="modalQrCodeSiswaDashboard" tabindex="-1" aria-labelledby="labelQrSiswaDash" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center rounded-4 border-0 shadow overflow-hidden">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fs-6 fw-bold mb-0" id="labelQrSiswaDash"><i class="bi bi-qr-code me-2"></i>QR Code Barcode Absensi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($data['stats']['info_siswa']['nama'] ?? ''); ?></h5>
                <p class="text-muted small mb-3">NIS: <?= htmlspecialchars($data['stats']['info_siswa']['nis'] ?? '-'); ?> &bull; Kelas <?= htmlspecialchars($data['stats']['info_siswa']['nama_kelas'] ?? '-'); ?></p>
                
                <div class="p-3 bg-light rounded-4 border d-inline-block shadow-sm mb-3">
                    <div id="qrcodeDashboardSiswa" class="d-flex justify-content-center"></div>
                </div>

                <div>
                    <div class="d-inline-block bg-dark text-white px-3 py-1 rounded font-monospace fw-bold mb-2 fs-6"><?= htmlspecialchars($data['stats']['info_siswa']['barcode'] ?? '-'); ?></div>
                </div>
                <p class="text-muted small mb-3">QR Barcode ini dapat dipindai pada kamera terminal absensi sekolah.</p>

                <div class="d-flex justify-content-center gap-2">
                    <a href="<?= BASEURL; ?>/siswa/cetak_barcode" target="_blank" class="btn btn-primary rounded-pill px-3 fw-semibold">
                        <i class="bi bi-printer me-1"></i> Cetak Kartu
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
<!-- MODAL SCANNER BARCODE / QR CODE ABSENSI SISWA                  -->
<!-- ============================================================== -->
<div class="modal fade" id="modalScannerSiswa" tabindex="-1" aria-labelledby="modalScannerSiswaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalScannerSiswaLabel">
                    <i class="bi bi-qr-code-scan me-2"></i>Scan Barcode / QR Absensi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="stopScannerSiswa()"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Area Alert Modal -->
                <div id="modalScanAlert" class="mb-3"></div>

                <!-- Status Jarak & Geofencing (Maksimal 100 Meter) -->
                <div class="alert alert-light border d-flex align-items-center justify-content-between p-2 mb-3 rounded-3" id="gpsStatusBoxSiswa">
                    <div class="d-flex align-items-center gap-2 text-start">
                        <i class="bi bi-geo-alt-fill text-danger fs-5"></i>
                        <div>
                            <span class="fw-semibold small d-block" id="gpsStatusTextSiswa">Mendeteksi Lokasi GPS...</span>
                            <small class="text-muted" style="font-size: 11px;">Batas Jarak: Maksimal <strong>100 Meter</strong> dari Sekolah</small>
                        </div>
                    </div>
                    <span class="badge bg-secondary" id="gpsDistanceBadgeSiswa">GPS Standby</span>
                </div>

                <!-- Status Badge -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Arahkan kamera ke Barcode / QR Code kartu siswa Anda.</span>
                    <span class="badge bg-secondary" id="scannerStatusBadge">Menyiapkan Kamera...</span>
                </div>

                <!-- Viewport Kamera -->
                <div class="position-relative mb-3">
                    <div id="readerSiswa" style="width: 100%; max-width: 480px; margin: 0 auto; border-radius: 12px; overflow: hidden; background: #000; min-height: 280px;" class="shadow-sm">
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
                    <button type="button" id="btnRestartCamera" class="btn btn-sm btn-outline-primary d-none" onclick="startScannerSiswa()">
                        <i class="bi bi-camera-video me-1"></i>Mulai Ulang Kamera
                    </button>
                    <button type="button" id="btnToggleManual" class="btn btn-sm btn-outline-secondary" onclick="toggleManualInput()">
                        <i class="bi bi-keyboard me-1"></i>Input Manual Barcode
                    </button>
                </div>

                <!-- Alternatif Input Manual Barcode -->
                <div id="manualInputContainer" class="d-none bg-light p-3 rounded-3 border mb-3">
                    <label for="inputManualBarcode" class="form-label fw-semibold text-dark small mb-1">
                        <i class="bi bi-pencil-square me-1"></i>Input Barcode Manual
                    </label>
                    <div class="input-group">
                        <input type="text" id="inputManualBarcode" class="form-control" placeholder="Contoh: STD-260001" autocomplete="off">
                        <button class="btn btn-primary" type="button" id="btnSubmitManual" onclick="submitManualBarcode()">
                            <i class="bi bi-send me-1"></i>Kirim Absen
                        </button>
                    </div>
                    <small class="text-muted">Gunakan opsi ini jika kamera perangkat tidak dapat diakses atau barcode tidak terbaca.</small>
                </div>

                <!-- Area Rincian Hasil Scan -->
                <div id="modalScanResult" class="d-none mt-3"></div>
            </div>
            <div class="modal-footer border-0 bg-light py-2 px-4 rounded-bottom-4">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal" onclick="stopScannerSiswa()">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL PENGAJUAN SAKIT / IZIN SISWA                             -->
<!-- ============================================================== -->
<div class="modal fade" id="modalPengajuanIzinSiswa" tabindex="-1" aria-labelledby="modalPengajuanIzinSiswaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-warning bg-gradient text-dark border-0 py-3">
                <h5 class="modal-title fw-bold" id="modalPengajuanIzinSiswaLabel">
                    <i class="bi bi-file-earmark-medical me-2"></i>Pengajuan Sakit / Izin
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASEURL; ?>/siswa/ajukan_izin" method="POST" enctype="multipart/form-data" id="formPengajuanIzinSiswa">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>">
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small border-0 rounded-3 mb-3">
                        <i class="bi bi-info-circle me-1"></i>Pengajuan ini akan langsung tercatat pada absensi harian Anda dan dilaporkan ke Wali Kelas.
                    </div>

                    <!-- Pilihan Kategori -->
                    <label class="form-label fw-bold text-dark mb-2">Kategori Pengajuan <span class="text-danger">*</span></label>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="status" id="statusSakitSiswa" value="sakit" checked>
                            <label class="btn btn-outline-info w-100 py-2 d-flex flex-column align-items-center rounded-3 fw-semibold" for="statusSakitSiswa">
                                <i class="bi bi-bandaid fs-3 mb-1"></i>
                                <span>Sakit</span>
                            </label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="status" id="statusIzinSiswa" value="izin">
                            <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center rounded-3 fw-semibold" for="statusIzinSiswa">
                                <i class="bi bi-envelope-paper fs-3 mb-1"></i>
                                <span>Izin</span>
                            </label>
                        </div>
                    </div>

                    <!-- Tanggal -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="tglMulaiSiswa" class="form-label small fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tglMulaiSiswa" name="tanggal_mulai" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label for="tglSelesaiSiswa" class="form-label small fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tglSelesaiSiswa" name="tanggal_selesai" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-3">
                        <label for="keteranganIzinSiswa" class="form-label small fw-semibold">Alasan / Keterangan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="keteranganIzinSiswa" name="keterangan" rows="3" placeholder="Contoh: Mengalami demam tinggi dan istirahat dokter, atau izin acara keluarga..." required minlength="5"></textarea>
                    </div>

                    <!-- Lampiran Surat (Opsional) -->
                    <div class="mb-2">
                        <label for="lampiranSiswa" class="form-label small fw-semibold">
                            <i class="bi bi-paperclip me-1"></i>Lampiran Bukti / Surat (Opsional)
                        </label>
                        <input class="form-control form-control-sm" type="file" id="lampiranSiswa" name="lampiran" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text text-muted small">Surat keterangan dokter atau surat izin orang tua (Maks. 2MB, JPG/PNG/PDF).</div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light py-2 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary px-3 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning px-4 rounded-pill fw-semibold text-dark" id="btnSubmitIzinSiswa">
                        <i class="bi bi-send me-1"></i>Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.transition-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
</style>

<!-- Library html5-qrcode Existing -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
(function() {
    const SCAN_URL   = '<?= BASEURL; ?>/siswa/scan';
    const CSRF_TOKEN = '<?= htmlspecialchars($data['csrf_token'] ?? ''); ?>';
    const SEKOLAH_LAT = <?= (float)($data['lokasi_sekolah']['latitude'] ?? -6.208763); ?>;
    const SEKOLAH_LNG = <?= (float)($data['lokasi_sekolah']['longitude'] ?? 106.845599); ?>;
    const MAX_RADIUS_METER = <?= (int)($data['lokasi_sekolah']['radius_meter'] ?? 100); ?>;
    const GEOFENCE_ACTIVE = <?= ($data['lokasi_sekolah']['geofence_aktif'] ?? true) ? 'true' : 'false'; ?>;

    let html5QrcodeScanner = null;
    let isScanning = false;
    let isStartingSiswa = false;
    let scanCooldown = false;
    let lastScannedText = '';
    let userCoords = null;

    // Element references
    const modalEl          = document.getElementById('modalScannerSiswa');
    const statusBadge      = document.getElementById('scannerStatusBadge');
    const alertArea        = document.getElementById('modalScanAlert');
    const manualContainer  = document.getElementById('manualInputContainer');
    const manualInput      = document.getElementById('inputManualBarcode');
    const restartBtn       = document.getElementById('btnRestartCamera');
    const resultArea       = document.getElementById('modalScanResult');

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
        const badge = document.getElementById('gpsDistanceBadgeSiswa');
        const text  = document.getElementById('gpsStatusTextSiswa');

        if (!navigator.geolocation) {
            if (text) text.textContent = 'Geolocation tidak didukung pada browser ini.';
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
                            window.stopScannerSiswa();
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
                    text.innerHTML = '<span class="text-warning fw-semibold">Mohon izinkan akses lokasi (GPS) browser</span>';
                }
                if (callback) callback(null);
            },
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 3000 }
        );
    }

    function tampilkanDitolakJarakSiswa(jarak) {
        setBadge('Di Luar Radius', 'bg-danger');
        const jarakStr = jarak ? `${jarak} meter` : 'Tidak terdeteksi';
        showAlert(`Absensi ditolak: Anda berada di luar area sekolah! Jarak Anda saat ini ${jarakStr} dari sekolah (Batas maksimal adalah ${MAX_RADIUS_METER} meter).`, 'danger');

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: `Di Luar Batas Jarak Absensi (Maks ${MAX_RADIUS_METER}m)`,
                html: `
                    <div class="text-center py-2">
                        <i class="bi bi-geo-alt-fill text-danger fs-1 mb-2 d-block"></i>
                        <p class="text-secondary mb-2">Absensi tidak dapat diproses karena Anda berada di luar area sekolah.</p>
                        <div class="alert alert-danger py-2 px-3 small rounded-3 mb-2">
                            Jarak terdeteksi: <strong>${jarakStr}</strong><br>
                            Batas maksimal yang diizinkan: <strong>${MAX_RADIUS_METER} meter</strong>
                        </div>
                        <small class="text-muted">Pastikan Anda sudah berada di area lingkungan sekolah (maksimal ${MAX_RADIUS_METER} meter) dan mengaktifkan GPS perangkat.</small>
                    </div>
                `,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Tutup'
            });
        }
    }

    function setBadge(text, colorClass) {
        if (!statusBadge) return;
        statusBadge.className = 'badge ' + colorClass;
        statusBadge.textContent = text;
    }

    function showAlert(message, type) {
        if (!alertArea) return;
        alertArea.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center mb-0" role="alert">
                <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : (type === 'warning' ? 'bi-exclamation-triangle-fill' : (type === 'info' ? 'bi-info-circle-fill' : 'bi-x-circle-fill'))} fs-5 me-2"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
    }

    function clearAlert() {
        if (alertArea) alertArea.innerHTML = '';
    }

    window.toggleManualInput = function() {
        if (!manualContainer) return;
        manualContainer.classList.toggle('d-none');
        if (!manualContainer.classList.contains('d-none') && manualInput) {
            manualInput.focus();
        }
    };

    window.submitManualBarcode = function() {
        if (!manualInput) return;
        const code = manualInput.value.trim();
        if (!code) {
            showAlert('Mohon ketikkan kode Barcode/QR terlebih dahulu.', 'warning');
            return;
        }
        manualInput.disabled = true;
        const submitBtn = document.getElementById('btnSubmitManual');
        if (submitBtn) submitBtn.disabled = true;

        prosesAbsensiKeServer(code, () => {
            manualInput.disabled = false;
            if (submitBtn) submitBtn.disabled = false;
        });
    };

    if (manualInput) {
        manualInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                window.submitManualBarcode();
            }
        });
    }

    // ─── Kamera Scanner ──────────────────────────────────────────────────────────

    window.startScannerSiswa = async function() {
        if (isScanning || isStartingSiswa) return;
        isStartingSiswa = true;

        clearAlert();
        if (resultArea) resultArea.classList.add('d-none');
        if (restartBtn) restartBtn.classList.add('d-none');

        if (GEOFENCE_ACTIVE && userCoords && userCoords.jarak > MAX_RADIUS_METER) {
            tampilkanDitolakJarakSiswa(userCoords.jarak);
            isStartingSiswa = false;
            return;
        }

        setBadge('Menyiapkan Kamera...', 'bg-secondary');

        // Bersihkan instance lama jika ada
        if (html5QrcodeScanner) {
            try {
                if (isScanning) await html5QrcodeScanner.stop();
                html5QrcodeScanner.clear();
            } catch(e) {}
            html5QrcodeScanner = null;
            isScanning = false;
        }

        const readerDiv = document.getElementById('readerSiswa');
        if (readerDiv) {
            readerDiv.innerHTML = '';
        }

        try {
            html5QrcodeScanner = new Html5Qrcode("readerSiswa");
        } catch (e) {
            isStartingSiswa = false;
            showAlert('Gagal memuat modul kamera. Silakan gunakan Input Manual.', 'danger');
            setBadge('Kamera Error', 'bg-danger');
            if (manualContainer) manualContainer.classList.remove('d-none');
            return;
        }

        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        const devices = await Html5Qrcode.getCameras().catch(() => []);
        let cameraSelection = { facingMode: "environment" };
        if (devices && devices.length > 0) {
            const backCam = devices.find(d => /back|rear|environment/i.test(d.label));
            cameraSelection = backCam ? backCam.id : devices[0].id;
        }

        try {
            await html5QrcodeScanner.start(
                cameraSelection,
                config,
                onScanSuccess,
                onScanFailure
            );
            isScanning = true;
            isStartingSiswa = false;
            setBadge('Kamera Aktif (Scanning...)', 'bg-success');
        } catch (err) {
            // Fallback kamera depan
            try {
                if (readerDiv) readerDiv.innerHTML = '';
                await html5QrcodeScanner.start(
                    { facingMode: "user" },
                    config,
                    onScanSuccess,
                    onScanFailure
                );
                isScanning = true;
                isStartingSiswa = false;
                setBadge('Kamera Depan Aktif', 'bg-success');
            } catch (errFallback) {
                isScanning = false;
                isStartingSiswa = false;
                setBadge('Kamera Gagal', 'bg-danger');
                if (restartBtn) restartBtn.classList.remove('d-none');
                if (manualContainer) manualContainer.classList.remove('d-none');

                let errorMsg = 'Kamera tidak dapat diakses atau izin kamera ditolak. Silakan berikan izin kamera pada browser Anda atau gunakan opsi Input Manual di bawah.';
                if (errFallback && errFallback.name === 'NotAllowedError') {
                    errorMsg = 'Izin akses kamera ditolak oleh browser. Mohon izinkan akses kamera di pengaturan browser Anda.';
                } else if (errFallback && errFallback.name === 'NotFoundError') {
                    errorMsg = 'Kamera tidak ditemukan pada perangkat Anda. Silakan gunakan Input Manual.';
                }
                showAlert(errorMsg, 'warning');

                if (readerDiv) {
                    readerDiv.innerHTML = `
                        <div class="d-flex align-items-center justify-content-center text-white" style="min-height: 280px;">
                            <div class="text-center p-4">
                                <i class="bi bi-camera-video-off text-danger" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0 text-white-50 small">Kamera tidak aktif.</p>
                            </div>
                        </div>`;
                }
            }
        }
    };

    window.stopScannerSiswa = async function() {
        isStartingSiswa = false;
        if (html5QrcodeScanner) {
            try {
                if (isScanning) await html5QrcodeScanner.stop();
                html5QrcodeScanner.clear();
            } catch(e) {}
            html5QrcodeScanner = null;
        }
        isScanning = false;
        setBadge('Kamera Berhenti', 'bg-secondary');
    };

    window.toggleManualInput = function() {
        if (manualContainer) {
            manualContainer.classList.toggle('d-none');
            const inp = document.getElementById('inputManualBarcode');
            if (inp && !manualContainer.classList.contains('d-none')) {
                inp.focus();
            }
        }
    };

    window.submitManualBarcode = function() {
        const inp = document.getElementById('inputManualBarcode');
        const code = inp ? inp.value.trim() : '';
        if (!code) {
            showAlert('Masukkan barcode terlebih dahulu.', 'warning');
            return;
        }

        if (GEOFENCE_ACTIVE && userCoords && userCoords.jarak > MAX_RADIUS_METER) {
            tampilkanDitolakJarakSiswa(userCoords.jarak);
            return;
        }

        prosesAbsensiKeServer(code);
    };

    function onScanSuccess(decodedText) {
        if (scanCooldown) return;
        if (decodedText === lastScannedText) return;

        if (GEOFENCE_ACTIVE && userCoords && userCoords.jarak > MAX_RADIUS_METER) {
            tampilkanDitolakJarakSiswa(userCoords.jarak);
            return;
        }

        scanCooldown = true;
        lastScannedText = decodedText;
        setTimeout(() => {
            scanCooldown = false;
            lastScannedText = '';
        }, 3000);

        setBadge('Memvalidasi...', 'bg-warning text-dark');
        showAlert('<div class="spinner-border spinner-border-sm me-2" role="status"></div>Memvalidasi barcode dengan server...', 'info');

        // Hentikan pemindaian sementara agar tidak berulang
        if (html5QrcodeScanner && isScanning) {
            html5QrcodeScanner.pause();
        }

        prosesAbsensiKeServer(decodedText, () => {
            if (html5QrcodeScanner && isScanning) {
                html5QrcodeScanner.resume();
            }
        });
    }

    function onScanFailure(error) {
        // Ignored during passive scanning
    }

    // ─── Pengiriman AJAX ke Backend ──────────────────────────────────────────────

    function prosesAbsensiKeServer(barcode, onDoneCallback) {
        const executeSend = (coords) => {
            if (GEOFENCE_ACTIVE && coords && coords.jarak > MAX_RADIUS_METER) {
                if (typeof onDoneCallback === 'function') onDoneCallback();
                tampilkanDitolakJarakSiswa(coords.jarak);
                return;
            }

            setBadge('Memvalidasi...', 'bg-warning text-dark');
            showAlert('<div class="spinner-border spinner-border-sm me-2" role="status"></div>Memvalidasi lokasi & barcode ke server...', 'info');

            const formData = new FormData();
            formData.append('barcode', barcode);
            formData.append('csrf_token', CSRF_TOKEN);
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
                if (typeof onDoneCallback === 'function') onDoneCallback();
                tanganiHasilAbsensi(data);
            })
            .catch(err => {
                if (typeof onDoneCallback === 'function') onDoneCallback();
                setBadge('Koneksi Gagal', 'bg-danger');
                showAlert('Koneksi ke server gagal. Periksa jaringan Anda dan coba kembali.', 'danger');
                if (restartBtn) restartBtn.classList.remove('d-none');
            });
        };

        if (userCoords) {
            executeSend(userCoords);
        } else {
            updateGpsLocation((coords) => {
                executeSend(coords);
            });
        }
    }

    function tanganiHasilAbsensi(data) {
        if (data.status === true) {
            window.stopScannerSiswa();
            setBadge('Berhasil', 'bg-success');
            showAlert(data.pesan || 'Absensi berhasil dicatat.', 'success');

            const isPulang    = data.tipe_absen === 'pulang';
            const isTerlambat = data.status_kehadiran === 'terlambat';
            const badgeColor  = isPulang ? 'primary' : (isTerlambat ? 'warning text-dark' : 'success');
            const statusLabel = isPulang ? 'Absen Pulang Berhasil' : (isTerlambat ? 'Terlambat' : 'Hadir Tepat Waktu');
            const namaSiswa   = data.siswa?.nama || '<?= htmlspecialchars($data['stats']['info_siswa']['nama'] ?? 'Siswa') ?>';
            const kelasSiswa  = data.siswa?.nama_kelas || '<?= htmlspecialchars($data['stats']['info_siswa']['nama_kelas'] ?? '-') ?>';
            const jamScan     = data.jam || '<?= date('H:i:s') ?>';
            const jamMasuk    = data.jam_masuk ? data.jam_masuk.substring(0, 5) : jamScan.substring(0, 5);
            const jamPulang   = data.jam_pulang ? data.jam_pulang.substring(0, 5) : (isPulang ? jamScan.substring(0, 5) : '-');

            // Tutup modal scanner agar SweetAlert tampil bersih
            if (modalEl) {
                const bsModal = bootstrap.Modal.getInstance(modalEl);
                if (bsModal) bsModal.hide();
            }

            // Tampilkan kartu hasil di modal jika dibuka kembali
            if (resultArea) {
                resultArea.classList.remove('d-none');
                resultArea.innerHTML = `
                    <div class="card border-0 bg-success bg-opacity-10 rounded-3 p-3 text-center">
                        <i class="bi bi-check-circle-fill text-success fs-1 mb-2"></i>
                        <h5 class="fw-bold text-success mb-1">${isPulang ? 'Absen Pulang Berhasil!' : 'Absensi Berhasil!'}</h5>
                        <p class="mb-2 text-dark small">${namaSiswa} (${kelasSiswa})</p>
                        <div>
                            <span class="badge bg-${badgeColor} px-3 py-2 fs-6 mb-1">
                                ${statusLabel}
                            </span>
                        </div>
                        <div class="text-muted small mt-2">
                            <span><i class="bi bi-box-arrow-in-right text-success me-1"></i>Masuk: <b>${jamMasuk} WIB</b></span>
                            ${isPulang ? `<span class="ms-2"><i class="bi bi-box-arrow-left text-primary me-1"></i>Pulang: <b>${jamPulang} WIB</b></span>` : ''}
                        </div>
                    </div>`;
            }

            // Tampilkan Notifikasi Sukses SweetAlert2
            if (typeof Swal !== 'undefined') {
                const swalTitle = isPulang 
                    ? 'Absen Pulang Berhasil!' 
                    : (isTerlambat ? 'Absen Masuk Berhasil (Terlambat)!' : 'Absen Masuk Berhasil (Tepat Waktu)!');

                Swal.fire({
                    icon: isPulang ? 'success' : (isTerlambat ? 'warning' : 'success'),
                    title: swalTitle,
                    html: `
                        <div class="text-center py-2">
                            <div class="mb-3 text-secondary">${data.pesan || 'Kehadiran Anda telah berhasil dicatat ke sistem absensi.'}</div>
                            <div class="p-3 bg-light rounded-4 text-start small border mb-3">
                                <div class="mb-1"><strong>Nama:</strong> ${namaSiswa}</div>
                                <div class="mb-1"><strong>Kelas:</strong> ${kelasSiswa}</div>
                                <div class="mb-1"><strong>Jam Masuk:</strong> <span class="badge bg-success bg-opacity-75">${jamMasuk} WIB</span></div>
                                ${isPulang ? `<div class="mb-1"><strong>Jam Pulang:</strong> <span class="badge bg-primary">${jamPulang} WIB</span></div>` : ''}
                                <div><strong>Status:</strong> <span class="badge bg-${badgeColor} fs-6">${statusLabel}</span></div>
                            </div>
                            <small class="text-muted">
                                ${isPulang ? '🏠 Terima kasih telah belajar dengan giat hari ini. Selamat beristirahat dan hati-hati di perjalanan!' : (isTerlambat ? '⚠️ Anda tercatat terlambat hari ini. Mohon usahakan hadir lebih awal esok hari.' : '🎉 Terima kasih telah hadir tepat waktu. Selamat belajar!')}
                            </small>
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
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }

        } else if (data.di_luar_radius) {
            // DI LUAR BATAS RADIUS (LEBIH DARI 100 METER)
            setBadge('Di Luar Radius', 'bg-danger');
            showAlert(data.pesan, 'danger');
            if (restartBtn) restartBtn.classList.remove('d-none');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Di Luar Batas Jarak Absensi (Maks 100m)',
                    html: `
                        <div class="text-center py-2">
                            <i class="bi bi-geo-alt-fill text-danger fs-1 mb-2 d-block"></i>
                            <p class="text-secondary mb-2">${data.pesan}</p>
                            <div class="alert alert-danger py-2 px-3 small rounded-3 mb-2">
                                Jarak terdeteksi: <strong>${data.jarak ? data.jarak + ' meter' : 'Tidak terdeteksi'}</strong><br>
                                Batas maksimal yang diizinkan: <strong>100 meter</strong>
                            </div>
                            <small class="text-muted">Pastikan Anda sudah berada di area lingkungan sekolah dan mengaktifkan GPS perangkat.</small>
                        </div>
                    `,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Tutup'
                });
            }

        } else if (data.sudah_absen === true) {
            // SUDAH ABSEN HARI INI
            window.stopScannerSiswa();
            setBadge('Sudah Absen', 'bg-info text-dark');
            showAlert(data.pesan || 'Absensi hari ini sudah tercatat.', 'info');

            if (modalEl) {
                const bsModal = bootstrap.Modal.getInstance(modalEl);
                if (bsModal) bsModal.hide();
            }

            const isLengkap = data.sudah_lengkap === true;
            const jamMasukStr = data.jam_masuk ? data.jam_masuk.substring(0, 5) : '-';
            const jamPulangStr = data.jam_pulang ? data.jam_pulang.substring(0, 5) : '-';

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
                            ${data.jam ? `<div class="p-2 bg-light rounded-3 small border">Waktu Scan: <strong>${data.jam} WIB</strong></div>` : ''}
                        </div>
                    `,
                    confirmButtonColor: '#0d6efd',
                    confirmButtonText: 'Tutup'
                });
            }

        } else {
            // GAGAL / KODE TIDAK VALID
            setBadge('Gagal', 'bg-danger');
            showAlert(data.pesan || 'Barcode/QR tidak valid.', 'danger');
            if (restartBtn) restartBtn.classList.remove('d-none');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Absensi Gagal',
                    text: data.pesan || 'Barcode/QR tidak valid atau berada di luar jam operasional jadwal.',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Coba Lagi'
                });
            }
        }
    }

    function updateDashboardCard(statusKehadiran, jamMasuk) {
        const iconDiv  = document.getElementById('iconStatusHariIni');
        const textH4   = document.getElementById('textStatusHariIni');
        const descP    = document.getElementById('descStatusHariIni');
        const bodyCard = document.getElementById('bodyStatusHariIni');

        const isTerlambat = statusKehadiran === 'terlambat';
        const colorClass  = isTerlambat ? 'text-warning' : 'text-success';
        const iconClass   = isTerlambat ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill';
        const textLabel   = isTerlambat ? 'Terlambat' : 'Hadir Tepat Waktu';

        if (iconDiv) {
            iconDiv.innerHTML = `<i class="bi ${iconClass} ${colorClass}" style="font-size: 5rem; drop-shadow: 0 4px 6px rgba(0,0,0,0.1);"></i>`;
        }
        if (textH4) {
            textH4.className = `${colorClass} fw-bold`;
            textH4.textContent = textLabel;
        }
        if (descP) {
            descP.style.display = 'none';
        }
        if (bodyCard && !document.getElementById('badgeJamMasuk')) {
            const badgeDiv = document.createElement('div');
            badgeDiv.id = 'badgeJamMasuk';
            badgeDiv.className = 'mt-3';
            badgeDiv.innerHTML = `
                <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                    <i class="bi bi-clock me-2 text-muted"></i>Jam Masuk: <strong>${jamMasuk} WIB</strong>
                </span>`;
            bodyCard.appendChild(badgeDiv);
        }
    }

    // ─── Modal Event Listeners ───────────────────────────────────────────────────

    if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function() {
            updateGpsLocation();
            window.startScannerSiswa();
        });

        modalEl.addEventListener('hidden.bs.modal', function() {
            window.stopScannerSiswa();
            clearAlert();
            if (resultArea) resultArea.classList.add('d-none');
            if (manualContainer) manualContainer.classList.add('d-none');
        });
    }

    // Auto-detect GPS saat pertama kali halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        updateGpsLocation();
    });

    // Auto-sync tanggal selesai jika tanggal mulai dimajukan
    const tglMulai = document.getElementById('tglMulaiSiswa');
    const tglSelesai = document.getElementById('tglSelesaiSiswa');
    if (tglMulai && tglSelesai) {
        tglMulai.addEventListener('change', function() {
            if (tglSelesai.value < this.value) {
                tglSelesai.value = this.value;
            }
            tglSelesai.min = this.value;
        });
    }

    // ─── Handle Pengajuan Izin / Sakit via AJAX & SweetAlert2 ───────────────────
    const formIzinSiswa = document.getElementById('formPengajuanIzinSiswa');
    const btnSubmitIzinSiswa = document.getElementById('btnSubmitIzinSiswa');

    if (formIzinSiswa) {
        formIzinSiswa.addEventListener('submit', function(e) {
            e.preventDefault();

            const ketInput = document.getElementById('keteranganIzinSiswa');
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

            const formData = new FormData(formIzinSiswa);
            const modalIzinEl = document.getElementById('modalPengajuanIzinSiswa');
            const bsModalIzin = modalIzinEl ? (bootstrap.Modal.getInstance(modalIzinEl) || new bootstrap.Modal(modalIzinEl)) : null;

            if (btnSubmitIzinSiswa) {
                btnSubmitIzinSiswa.disabled = true;
                btnSubmitIzinSiswa.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Mengirim...';
            }

            fetch(formIzinSiswa.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (btnSubmitIzinSiswa) {
                    btnSubmitIzinSiswa.disabled = false;
                    btnSubmitIzinSiswa.innerHTML = '<i class="bi bi-send me-1"></i>Kirim Pengajuan';
                }

                if (data.status === true) {
                    if (bsModalIzin) bsModalIzin.hide();
                    formIzinSiswa.reset();

                    const statusVal  = formData.get('status') || 'izin';
                    const statusText = statusVal === 'sakit' ? 'Sakit' : 'Izin';
                    const badgeClass = statusVal === 'sakit' ? 'info text-dark' : 'primary';
                    const tglM       = formData.get('tanggal_mulai');
                    const tglS       = formData.get('tanggal_selesai');

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: `Pengajuan ${statusText} Berhasil!`,
                            html: `
                                <div class="text-center py-2">
                                    <div class="mb-3 text-secondary">${data.pesan || 'Pengajuan berhasil dicatat ke sistem absensi.'}</div>
                                    <div class="p-3 bg-light rounded-4 text-start small border mb-3">
                                        <div class="mb-1"><strong>Kategori:</strong> <span class="badge bg-${badgeClass} text-uppercase px-2 py-1">${statusText}</span></div>
                                        <div class="mb-1"><strong>Periode:</strong> ${tglM} s/d ${tglS}</div>
                                        <div><strong>Keterangan:</strong> <em>"${ketVal}"</em></div>
                                    </div>
                                    <small class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Tercatat pada kehadiran harian dan dilaporkan ke Wali Kelas.</small>
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
                            text: data.pesan || 'Gagal menyimpan keterangan pengajuan izin/sakit.',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        alert(data.pesan || 'Pengajuan gagal.');
                    }
                }
            })
            .catch(err => {
                if (btnSubmitIzinSiswa) {
                    btnSubmitIzinSiswa.disabled = false;
                    btnSubmitIzinSiswa.innerHTML = '<i class="bi bi-send me-1"></i>Kirim Pengajuan';
                }
                // Fallback ke normal form POST submit jika AJAX bermasalah
                formIzinSiswa.submit();
            });
        });
    }

    // Live Clock Updater
    const topClock = document.getElementById('liveClockSiswaTop');
    const dashClock = document.getElementById('liveClockSiswaDash');
    if (topClock || dashClock) {
        setInterval(() => {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const timeStr = `${h}:${m}:${s} WIB`;
            if (topClock) topClock.textContent = timeStr;
            if (dashClock) dashClock.textContent = timeStr;
        }, 1000);
    }

})();
</script>

<!-- QRCode JS Generator untuk QR Barcode Siswa -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function() {
    const modalQrSiswa = document.getElementById('modalQrCodeSiswaDashboard');
    if (modalQrSiswa) {
        let qrSiswaObj = null;
        modalQrSiswa.addEventListener('show.bs.modal', function () {
            const qrContainer = document.getElementById('qrcodeDashboardSiswa');
            const barcodeVal = '<?= htmlspecialchars($data['stats']['info_siswa']['barcode'] ?? ''); ?>';
            if (qrContainer && !qrSiswaObj && barcodeVal) {
                qrContainer.innerHTML = '';
                qrSiswaObj = new QRCode(qrContainer, {
                    text: barcodeVal,
                    width: 170,
                    height: 170,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });
            }
        });
    }
})();
</script>
