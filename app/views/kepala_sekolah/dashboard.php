<?php
$st = $data['stats'];
$latestGuru = $data['latest_guru'] ?? [];

// Format Tanggal Bahasa Indonesia
$hariIndo = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];
$bulanIndo = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$tanggalIndo = ($hariIndo[date('l')] ?? date('l')) . ', ' . date('d') . ' ' . ($bulanIndo[date('m')] ?? date('F')) . ' ' . date('Y');

// Hitung KPI Khusus Guru
$totalGuru  = (int)($st['guru']['total'] ?? 0);
$presensiGuru = (int)($st['guru']['hadir'] ?? 0) + (int)($st['guru']['terlambat'] ?? 0);
$pctGuru = ($totalGuru > 0) ? round(($presensiGuru / $totalGuru) * 100) : 0;
$guruTidakHadir = (int)($st['guru']['izin'] ?? 0) + (int)($st['guru']['sakit'] ?? 0) + (int)($st['guru']['alpa'] ?? 0);
?>

<style>
.dashboard-hero {
    background: #FFFFFF;
    border: 1px solid #E5E7EB;
    border-radius: 1rem;
    color: #1F2937;
    padding: 1.5rem 1.75rem;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
}
.stat-card-modern {
    border: 1px solid #E5E7EB;
    border-radius: 0.85rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    background: #FFFFFF;
}
.stat-card-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
}
.stat-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
}
.chart-container-box {
    position: relative;
    height: 280px;
    width: 100%;
}
.progress-thin {
    height: 8px;
    border-radius: 6px;
    background-color: #e2e8f0;
}
</style>

<!-- ═══ EXECUTIVE ACTION BAR / HERO BANNER (LIGHT MINIMALIST) ═══════════════════════════════════ -->
<div class="dashboard-hero mb-4">
    <div class="row align-items-center g-3">
        <div class="col-lg-7">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bi bi-shield-check fs-2 text-primary"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Executive Monitoring Absensi Guru</h5>
                    <p class="mb-0 text-muted small">
                        Laporan komprehensif kehadiran seluruh dewan guru & tenaga pendidik SMK per hari ini.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-5 text-lg-end">
            <div class="d-inline-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fs-6 fw-semibold">
                    <i class="bi bi-calendar3 text-primary me-1"></i> <?= $tanggalIndo ?>
                </span>
                <a href="<?= BASEURL ?>/kepala_sekolah/rekap_guru" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-journal-check me-1"></i> Rekap Absensi Guru
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ═══ ROW 1: EXECUTIVE KPI SUMMARY GURU ═════════════════════════════════════ -->
<div class="row g-3 mb-4">
    <!-- Total Guru -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card-modern shadow-sm h-100 p-3 border-start border-primary border-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Total Guru Terdaftar</span>
                <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="h3 fw-bold text-dark mb-1"><?= $totalGuru ?> <span class="fs-6 fw-normal text-muted">Guru</span></div>
            <div class="small text-muted">
                <span class="text-primary fw-semibold"><?= (int)($st['guru']['hadir'] ?? 0) ?> Hadir Tepat</span> &bull; 
                <span class="text-warning fw-semibold"><?= (int)($st['guru']['terlambat'] ?? 0) ?> Terlambat</span>
            </div>
        </div>
    </div>

    <!-- Kehadiran Guru Hari Ini -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card-modern shadow-sm h-100 p-3 border-start border-success border-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Persentase Kehadiran</span>
                <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
                    <i class="bi bi-person-check-fill"></i>
                </div>
            </div>
            <div class="h3 fw-bold text-success mb-1"><?= $pctGuru ?>%</div>
            <div class="progress progress-thin mb-2">
                <div class="progress-bar bg-success" style="width: <?= $pctGuru ?>%"></div>
            </div>
            <div class="small text-muted"><?= $presensiGuru ?> dari <?= $totalGuru ?> guru tercatat hadir/terlambat</div>
        </div>
    </div>

    <!-- Guru Terlambat -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card-modern shadow-sm h-100 p-3 border-start border-warning border-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Terlambat Hari Ini</span>
                <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div class="h3 fw-bold text-warning mb-1"><?= (int)($st['guru']['terlambat'] ?? 0) ?> <span class="fs-6 fw-normal text-muted">Guru</span></div>
            <div class="small text-muted">Presensi masuk melewati pukul 07:00 WIB</div>
        </div>
    </div>

    <!-- Tidak Hadir (Izin / Sakit / Alpa) -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card-modern shadow-sm h-100 p-3 border-start border-danger border-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase">Tidak Hadir Hari Ini</span>
                <div class="stat-icon-wrapper bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-exclamation-diamond-fill"></i>
                </div>
            </div>
            <div class="h3 fw-bold text-dark mb-1"><?= $guruTidakHadir ?> <span class="fs-6 fw-normal text-muted">Guru</span></div>
            <div class="small text-muted">
                Izin: <?= (int)($st['guru']['izin'] ?? 0) ?> &bull; 
                Sakit: <?= (int)($st['guru']['sakit'] ?? 0) ?> &bull; 
                Alpa: <?= (int)($st['guru']['alpa'] ?? 0) ?>
            </div>
        </div>
    </div>
</div>

<!-- ═══ SECTION 2: STATISTIK STATUS GURU HARI INI ═════════════════════════════ -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold text-dark mb-0">
        <i class="bi bi-person-badge-fill text-success me-2"></i>Rincian Status Kehadiran Guru Hari Ini
    </h6>
    <a href="<?= BASEURL ?>/kepala_sekolah/rekap_guru" class="btn btn-sm btn-outline-success rounded-pill px-3">
        <i class="bi bi-eye me-1"></i> Rekap Absensi Guru Lengkap
    </a>
</div>

<div class="row g-3 mb-4">
    <!-- Total Guru -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card-modern shadow-sm h-100 p-3 border-start border-success border-3">
            <div class="text-muted small fw-bold text-uppercase mb-1">Total Guru</div>
            <div class="h4 mb-0 fw-bold text-dark"><?= (int)$st['guru']['total'] ?></div>
            <div class="text-xs text-muted mt-1">Tenaga Pendidik</div>
        </div>
    </div>
    <!-- Hadir -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card-modern shadow-sm h-100 p-3 border-start border-success border-3">
            <div class="text-success small fw-bold text-uppercase mb-1">Hadir Tepat</div>
            <div class="h4 mb-0 fw-bold text-success"><?= (int)$st['guru']['hadir'] ?></div>
            <div class="text-xs text-muted mt-1">Tepat waktu</div>
        </div>
    </div>
    <!-- Terlambat -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card-modern shadow-sm h-100 p-3 border-start border-warning border-3">
            <div class="text-warning small fw-bold text-uppercase mb-1">Terlambat</div>
            <div class="h4 mb-0 fw-bold text-warning"><?= (int)$st['guru']['terlambat'] ?></div>
            <div class="text-xs text-muted mt-1">> 07:00 WIB</div>
        </div>
    </div>
    <!-- Izin -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card-modern shadow-sm h-100 p-3 border-start border-info border-3">
            <div class="text-info small fw-bold text-uppercase mb-1">Izin</div>
            <div class="h4 mb-0 fw-bold text-info"><?= (int)$st['guru']['izin'] ?></div>
            <div class="text-xs text-muted mt-1">Dispensasi / Izin</div>
        </div>
    </div>
    <!-- Sakit -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card-modern shadow-sm h-100 p-3 border-start border-secondary border-3">
            <div class="text-secondary small fw-bold text-uppercase mb-1">Sakit</div>
            <div class="h4 mb-0 fw-bold text-secondary"><?= (int)$st['guru']['sakit'] ?></div>
            <div class="text-xs text-muted mt-1">Surat Dokter</div>
        </div>
    </div>
    <!-- Belum Absen -->
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card stat-card-modern shadow-sm h-100 p-3 border-start border-dark border-3 bg-light">
            <div class="text-muted small fw-bold text-uppercase mb-1">Belum Absen</div>
            <div class="h4 mb-0 fw-bold text-muted"><?= (int)$st['guru']['belum_absen'] ?></div>
            <div class="text-xs text-muted mt-1">Menunggu presensi</div>
        </div>
    </div>
</div>

<!-- ═══ SECTION 3: VISUALISASI CHART & TABEL PRESENSI TERBARU GURU ════════════ -->
<div class="row g-4 mb-4">
    <!-- Chart Donut Guru -->
    <div class="col-lg-5">
        <div class="card stat-card-modern shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark">
                    <i class="bi bi-pie-chart-fill text-success me-2"></i>Komposisi Status Absensi Guru
                </h6>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 small">
                    Total: <?= $totalGuru ?> Guru
                </span>
            </div>
            <div class="card-body pt-0 pb-3">
                <div class="chart-container-box">
                    <canvas id="chartGuru"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Feed Pemindaian Guru Terbaru Hari Ini -->
    <div class="col-lg-7">
        <div class="card stat-card-modern shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-dark">
                    <i class="bi bi-clock-history text-primary me-2"></i>Aktivitas Presensi Guru Hari Ini
                </h6>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1 small">
                    <?= count($latestGuru) ?> Catatan Terbaru
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3">Nama Guru</th>
                                <th>NIP</th>
                                <th class="text-center">Waktu Scan</th>
                                <th class="text-center pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($latestGuru)): ?>
                                <?php foreach($latestGuru as $g): 
                                    $stG = $g['status'] ?? 'hadir';
                                    $bgSt = 'bg-success';
                                    $lblSt = 'Hadir Tepat';
                                    if ($stG === 'terlambat') {
                                        $bgSt = 'bg-warning text-dark';
                                        $lblSt = 'Terlambat';
                                    } elseif ($stG === 'izin') {
                                        $bgSt = 'bg-info text-dark';
                                        $lblSt = 'Izin';
                                    } elseif ($stG === 'sakit') {
                                        $bgSt = 'bg-secondary';
                                        $lblSt = 'Sakit';
                                    } elseif ($stG === 'alpa') {
                                        $bgSt = 'bg-danger';
                                        $lblSt = 'Alpa';
                                    }
                                ?>
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark"><?= htmlspecialchars($g['nama']) ?></td>
                                    <td class="small text-muted"><?= htmlspecialchars($g['nip'] ?: '-') ?></td>
                                    <td class="text-center small font-monospace">
                                        <i class="bi bi-clock me-1 text-muted"></i><?= htmlspecialchars($g['jam_masuk'] ?? $g['jam_scan'] ?? '-') ?> WIB
                                    </td>
                                    <td class="text-center pe-3">
                                        <span class="badge <?= $bgSt ?> rounded-pill px-2 py-1 small"><?= $lblSt ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">
                                        <i class="bi bi-info-circle me-1"></i>Belum ada data presensi guru hari ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-2 px-3 text-end">
                <a href="<?= BASEURL ?>/kepala_sekolah/rekap_guru" class="btn btn-sm btn-link text-decoration-none">
                    Lihat Rekap Lengkap Seluruh Guru <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ═══ LOAD CHART.JS CDN ════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const commonChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    boxHeight: 12,
                    padding: 14,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { size: 12, family: "'Segoe UI', Roboto, sans-serif" }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                padding: 10,
                cornerRadius: 8,
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.parsed || 0;
                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                        let percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                        return ` ${label}: ${value} orang (${percentage}%)`;
                    }
                }
            }
        }
    };

    // Chart Guru (Khusus Dewan Guru)
    const ctxGuru = document.getElementById('chartGuru');
    if (ctxGuru) {
        new Chart(ctxGuru.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa', 'Belum Absen'],
                datasets: [{
                    data: [
                        <?= (int)($st['guru']['hadir'] ?? 0) ?>,
                        <?= (int)($st['guru']['terlambat'] ?? 0) ?>,
                        <?= (int)($st['guru']['izin'] ?? 0) ?>,
                        <?= (int)($st['guru']['sakit'] ?? 0) ?>,
                        <?= (int)($st['guru']['alpa'] ?? 0) ?>,
                        <?= (int)($st['guru']['belum_absen'] ?? 0) ?>
                    ],
                    backgroundColor: [
                        '#10b981', // Hadir
                        '#f59e0b', // Terlambat
                        '#06b6d4', // Izin
                        '#8b5cf6', // Sakit
                        '#ef4444', // Alpa
                        '#cbd5e1'  // Belum Absen
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: commonChartOptions
        });
    }
});
</script>
