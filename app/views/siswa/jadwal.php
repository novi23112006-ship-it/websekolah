<?php
$info_siswa = $data['stats']['info_siswa'] ?? [];
$nama_kelas = $info_siswa['nama_kelas'] ?? 'Kelas';
$hariIni = $data['hari_ini'] ?? 'Senin';
$jadwalMingguan = $data['jadwal_mingguan'] ?? [];
$jadwalHariIni = $data['jadwal_hari_ini'] ?? [];

$daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
?>

<!-- Alert Area -->
<div class="mb-3">
    <?php Flasher::flash(); ?>
</div>

<!-- Header Jadwal Pelajaran Siswa -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 p-3 bg-white rounded-4 shadow-sm border">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 d-inline-flex align-items-center justify-content-center shadow-sm" 
             style="width: 52px; height: 52px; font-size: 26px;">
            <i class="bi bi-calendar3-week"></i>
        </div>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 text-gray-800 fw-bold">Jadwal Mata Pelajaran Kelas</h5>
                <span class="badge bg-primary rounded-pill px-3 py-1"><?= htmlspecialchars($nama_kelas) ?></span>
            </div>
            <p class="text-muted mb-0 small">
                <i class="bi bi-person me-1"></i><?= htmlspecialchars($info_siswa['nama'] ?? 'Siswa') ?> &bull; 
                NIS: <?= htmlspecialchars($info_siswa['nis'] ?? '-') ?> &bull; 
                <i class="bi bi-calendar3 me-1"></i><strong class="text-primary"><?= htmlspecialchars($data['tanggal_hari_ini'] ?? DateHelper::tanggalIndo(date('Y-m-d'), true)) ?></strong> &bull; 
                <i class="bi bi-clock me-1"></i><span id="liveClockSiswaJadwal" class="fw-semibold text-dark"><?= $data['jam_sekarang'] ?? DateHelper::jamSekarang('H:i:s') ?> WIB</span>
            </p>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASEURL; ?>/siswa" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Dashboard
        </a>
        <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Cetak Jadwal
        </button>
    </div>
</div>

<!-- Navigasi Hari (Pills) -->
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body p-3">
        <ul class="nav nav-pills nav-fill gap-2 flex-column flex-sm-row" id="pillsJadwalTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= (!in_array($hariIni, $daftarHari) || $hariIni === 'Senin') ? 'active' : '' ?>" 
                        id="tab-hari-Senin" data-bs-toggle="pill" data-bs-target="#content-Senin" type="button" role="tab">
                    <span class="fw-bold">Senin</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalMingguan['Senin'] ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= ($hariIni === 'Selasa') ? 'active' : '' ?>" 
                        id="tab-hari-Selasa" data-bs-toggle="pill" data-bs-target="#content-Selasa" type="button" role="tab">
                    <span class="fw-bold">Selasa</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalMingguan['Selasa'] ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= ($hariIni === 'Rabu') ? 'active' : '' ?>" 
                        id="tab-hari-Rabu" data-bs-toggle="pill" data-bs-target="#content-Rabu" type="button" role="tab">
                    <span class="fw-bold">Rabu</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalMingguan['Rabu'] ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= ($hariIni === 'Kamis') ? 'active' : '' ?>" 
                        id="tab-hari-Kamis" data-bs-toggle="pill" data-bs-target="#content-Kamis" type="button" role="tab">
                    <span class="fw-bold">Kamis</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalMingguan['Kamis'] ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= ($hariIni === 'Jumat') ? 'active' : '' ?>" 
                        id="tab-hari-Jumat" data-bs-toggle="pill" data-bs-target="#content-Jumat" type="button" role="tab">
                    <span class="fw-bold">Jumat</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalMingguan['Jumat'] ?? []) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill py-2 <?= ($hariIni === 'Sabtu') ? 'active' : '' ?>" 
                        id="tab-hari-Sabtu" data-bs-toggle="pill" data-bs-target="#content-Sabtu" type="button" role="tab">
                    <span class="fw-bold">Sabtu</span>
                    <span class="badge bg-secondary ms-1"><?= count($jadwalMingguan['Sabtu'] ?? []) ?></span>
                </button>
            </li>
        </ul>
    </div>
</div>

<!-- Tab Content Per Hari -->
<div class="tab-content" id="pillsJadwalTabContent">
    <?php foreach ($daftarHari as $hari): ?>
        <?php 
            $isActive = false;
            if (in_array($hariIni, $daftarHari)) {
                $isActive = ($hari === $hariIni);
            } else {
                $isActive = ($hari === 'Senin');
            }
            $listMapel = $jadwalMingguan[$hari] ?? [];
        ?>
        <div class="tab-pane fade <?= $isActive ? 'show active' : '' ?>" id="content-<?= $hari ?>" role="tabpanel">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-clock-history me-2 text-primary"></i>Jadwal Hari <?= $hari ?>
                        </h6>
                        <small class="text-muted">Total <?= count($listMapel) ?> mata pelajaran terjadwal</small>
                    </div>
                    <?php if ($hari === $hariIni): ?>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                            <i class="bi bi-dot"></i> Hari Ini
                        </span>
                    <?php endif; ?>
                </div>

                <div class="card-body p-0">
                    <?php if (!empty($listMapel)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width: 170px;">Waktu (WIB)</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Guru Pengajar</th>
                                        <th>Ruangan</th>
                                        <th>Keterangan / Materi</th>
                                        <th class="text-center" style="width: 130px;">Status KBM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($listMapel as $idx => $m): ?>
                                    <?php
                                        $kbmStatus = DateHelper::getStatusKBM($hari, $m['jam_mulai'], $m['jam_selesai']);
                                        $isNow = ($kbmStatus['status'] === 'sedang_kbm');
                                    ?>
                                    <tr class="<?= $isNow ? 'table-primary bg-opacity-25' : '' ?>">
                                        <td class="ps-4">
                                            <span class="badge <?= $isNow ? 'bg-primary' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> px-2 py-1 fw-bold">
                                                <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($m['jam_mulai'])) ?> - <?= date('H:i', strtotime($m['jam_selesai'])) ?>
                                            </span>
                                            <?php if ($isNow): ?>
                                                <div class="small text-success fw-bold mt-1"><i class="bi bi-broadcast me-1"></i>Sedang KBM</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <?= htmlspecialchars($m['nama_mapel'] ?? 'Mata Pelajaran') ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?php if (!empty($m['kode_mapel'])): ?>
                                                    <span class="badge bg-light text-secondary border me-1"><?= htmlspecialchars($m['kode_mapel']) ?></span>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($m['kelompok'] ?? 'Umum') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
                                                    <i class="bi bi-person-fill"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark small"><?= htmlspecialchars($m['nama_guru']) ?></div>
                                                    <div class="text-muted" style="font-size: 11px;">NIP: <?= htmlspecialchars($m['nip'] ?? '-') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($m['ruangan'] ?? 'Ruang Kelas') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted small"><?= htmlspecialchars($m['keterangan'] ?? '-') ?></span>
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
                        <div class="text-center py-5">
                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                            <h6 class="mt-3 text-muted fw-bold">Tidak ada jadwal KBM pada hari <?= $hari ?></h6>
                            <p class="text-muted small mb-0">Hari ini bebas jadwal pelajaran atau kegiatan ekstrakurikuler / mandiri.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clockEl = document.getElementById('liveClockSiswaJadwal');
    if (clockEl) {
        setInterval(() => {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = `${h}:${m}:${s} WIB`;
        }, 1000);
    }
});
</script>
