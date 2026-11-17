<?php
$stats = $data['stats'] ?? [];
$info_guru = $stats['info_guru'] ?? $stats['guru'] ?? [];
$hariIni = $data['hari_ini'] ?? 'Senin';
$jadwalSeninJumat = $data['jadwal_senin_jumat'] ?? [];
$jadwalHariIni = $data['jadwal_hari_ini'] ?? [];
$daftarHari = DateHelper::getDaftarHari(true); // ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']

// Hitung total jam mengajar di hari Senin - Sabtu
$totalJadwal = 0;
foreach ($daftarHari as $d) {
    $totalJadwal += count($jadwalSeninJumat[$d] ?? []);
}
?>

<!-- Alert Flash -->
<div class="mb-3">
    <?php Flasher::flash(); ?>
</div>

<!-- Header Jadwal Mengajar Guru -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 p-3 bg-white rounded-4 shadow-sm border">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 d-inline-flex align-items-center justify-content-center shadow-sm" 
             style="width: 52px; height: 52px; font-size: 26px;">
            <i class="bi bi-calendar3-range"></i>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 text-gray-800 fw-bold">Jadwal Mengajar Guru (Senin - Sabtu)</h5>
                <span class="badge bg-primary rounded-pill px-3 py-1"><?= $totalJadwal ?> Sesi KBM</span>
            </div>
            <p class="text-muted mb-0 small mt-1">
                <i class="bi bi-person me-1"></i><?= htmlspecialchars($info_guru['nama'] ?? $_SESSION['nama'] ?? 'Guru') ?> &bull; 
                NIP: <?= htmlspecialchars($info_guru['nip'] ?? '-') ?> &bull; 
                Mapel: <?= htmlspecialchars($info_guru['mata_pelajaran'] ?? '-') ?> &bull; 
                Hari Ini: <strong class="text-primary"><?= htmlspecialchars($data['tanggal_hari_ini'] ?? DateHelper::tanggalIndo()) ?></strong> &bull; 
                Waktu: <strong class="text-success"><span id="liveTimeClock"><?= $data['jam_sekarang'] ?? DateHelper::jamSekarang('H:i') ?></span> WIB</strong>
            </p>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASEURL; ?>/guru" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
        <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Cetak Jadwal
        </button>
    </div>
</div>

<!-- Ringkasan Jadwal Mengajar Hari Ini -->
<div class="card shadow-sm border-0 rounded-4 mb-4 bg-primary bg-opacity-10 border border-primary border-opacity-25">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary px-3 py-2 rounded-pill fs-6">
                    <i class="bi bi-calendar-check me-1"></i>Hari Ini (<?= htmlspecialchars($hariIni) ?>)
                </span>
                <span class="text-dark fw-bold"><?= count($jadwalHariIni) ?> Kelas Terjadwal</span>
            </div>
            <small class="text-muted"><?= htmlspecialchars($data['tanggal_hari_ini'] ?? date('d F Y')) ?></small>
        </div>

        <?php if (!empty($jadwalHariIni)): ?>
            <div class="row g-3">
                <?php foreach ($jadwalHariIni as $jh): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 fw-bold">
                                    <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($jh['jam_mulai'])) ?> - <?= date('H:i', strtotime($jh['jam_selesai'])) ?>
                                </span>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($jh['ruangan'] ?? 'Ruang Kelas') ?>
                                </span>
                            </div>
                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($jh['nama_mapel'] ?? '-') ?></h6>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-secondary-subtle text-secondary"><?= htmlspecialchars($jh['nama_kelas'] ?? '-') ?></span>
                                <small class="text-muted"><?= htmlspecialchars($jh['jurusan'] ?? '') ?></small>
                            </div>
                            <?php if (!empty($jh['keterangan'])): ?>
                                <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($jh['keterangan']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-3 text-muted">
                <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                <div class="fw-semibold">Tidak ada jadwal mengajar pada hari ini (<?= htmlspecialchars($hariIni) ?>).</div>
                <small>Silakan cek jadwal mengajar hari Senin s/d Jumat pada tab di bawah.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Navigasi Hari Senin s/d Jumat -->
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body p-3">
        <ul class="nav nav-pills nav-fill gap-2 flex-column flex-sm-row" id="pillsJadwalGuruTab" role="tablist">
            <?php foreach ($daftarHari as $h): ?>
                <?php 
                    $isActive = false;
                    if (in_array($hariIni, $daftarHari)) {
                        $isActive = ($h === $hariIni);
                    } else {
                        $isActive = ($h === 'Senin');
                    }
                    $cnt = count($jadwalSeninJumat[$h] ?? []);
                ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill py-2 <?= $isActive ? 'active' : '' ?>" 
                            id="tab-guru-<?= $h ?>" data-bs-toggle="pill" data-bs-target="#content-guru-<?= $h ?>" type="button" role="tab">
                        <span class="fw-bold"><?= $h ?></span>
                        <span class="badge <?= $isActive ? 'bg-white text-primary' : 'bg-secondary' ?> ms-1"><?= $cnt ?> Kelas</span>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Konten Jadwal Per Hari (Senin - Jumat) -->
<div class="tab-content" id="pillsJadwalGuruTabContent">
    <?php foreach ($daftarHari as $h): ?>
        <?php 
            $isActive = false;
            if (in_array($hariIni, $daftarHari)) {
                $isActive = ($h === $hariIni);
            } else {
                $isActive = ($h === 'Senin');
            }
            $listJadwalHari = $jadwalSeninJumat[$h] ?? [];
        ?>
        <div class="tab-pane fade <?= $isActive ? 'show active' : '' ?>" id="content-guru-<?= $h ?>" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-calendar-event me-2 text-primary"></i>Jadwal Mengajar Hari <?= $h ?>
                        </h6>
                        <small class="text-muted">Total <?= count($listJadwalHari) ?> sesi kelas mengajar</small>
                    </div>
                    <?php if ($h === $hariIni): ?>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                            <i class="bi bi-dot"></i> Hari Ini
                        </span>
                    <?php endif; ?>
                </div>

                <div class="card-body p-0">
                    <?php if (!empty($listJadwalHari)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width: 170px;">Jam Mengajar</th>
                                        <th>Kelas</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Ruangan</th>
                                        <th>Jumlah Siswa</th>
                                        <th>Keterangan / Pokok Bahasan</th>
                                        <th class="text-center" style="width: 120px;">Status KBM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($listJadwalHari as $idx => $m): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 fw-bold">
                                                <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($m['jam_mulai'])) ?> - <?= date('H:i', strtotime($m['jam_selesai'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($m['nama_kelas']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($m['jurusan'] ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">
                                                <?= htmlspecialchars($m['nama_mapel'] ?? '-') ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?php if (!empty($m['kode_mapel'])): ?>
                                                    <span class="badge bg-light text-secondary border me-1"><?= htmlspecialchars($m['kode_mapel']) ?></span>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($m['kelompok'] ?? 'Kejuruan') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($m['ruangan'] ?? 'Ruang Kelas') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info-emphasis">
                                                <i class="bi bi-people me-1"></i><?= (int)($m['total_siswa_kelas'] ?? 0) ?> Siswa
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted small"><?= htmlspecialchars($m['keterangan'] ?? '-') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php $kbm = DateHelper::getStatusKBM($h, $m['jam_mulai'], $m['jam_selesai']); ?>
                                            <span class="badge <?= $kbm['badge'] ?> rounded-pill px-3 py-1 fw-semibold">
                                                <i class="bi <?= $kbm['icon'] ?> me-1"></i><?= $kbm['label'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-minus text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                            <h6 class="mt-3 text-muted fw-bold">Tidak ada jadwal mengajar pada hari <?= $h ?></h6>
                            <p class="text-muted small mb-0">Anda tidak memiliki jam mengajar kelas pada hari ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.querySelectorAll('#liveTimeClock').forEach(el => {
            el.textContent = hours + ':' + minutes + ':' + seconds;
        });
    }
    setInterval(updateClock, 1000);
    updateClock();
});
</script>
