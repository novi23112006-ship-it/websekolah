<!-- Flash Alert -->
<div class="mb-3">
    <?php Flasher::flash(); ?>
</div>

<!-- Header Page & Action Buttons -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-calendar2-range text-primary me-2"></i>Jadwal Mengajar Guru</h4>
        <p class="text-muted mb-0 small">
            Atur jadwal Kegiatan Belajar Mengajar (KBM), pembagian jam guru, kelas, dan ruangan &bull; 
            <i class="bi bi-calendar3 text-primary ms-1"></i> <strong class="text-dark"><?= htmlspecialchars($data['tanggal_hari_ini'] ?? DateHelper::tanggalIndo(date('Y-m-d'), true)) ?></strong> &bull; 
            <i class="bi bi-clock text-primary ms-1"></i> <span id="liveClockAdminJadwal" class="fw-bold text-dark"><?= $data['jam_sekarang'] ?? DateHelper::jamSekarang('H:i:s') ?> WIB</span>
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <!-- Toggle View Mode -->
        <div class="btn-group btn-group-sm rounded-pill p-1 bg-light border shadow-sm">
            <a href="<?= BASEURL; ?>/admin_jadwal_mengajar?<?= http_build_query(array_merge($data['filters'], ['view' => 'kelas'])) ?>" 
               class="btn btn-sm rounded-pill <?= ($data['view_mode'] === 'kelas') ? 'btn-primary' : 'btn-light text-muted' ?>">
                <i class="bi bi-mortarboard me-1"></i> Per Kelas
            </a>
            <a href="<?= BASEURL; ?>/admin_jadwal_mengajar?<?= http_build_query(array_merge($data['filters'], ['view' => 'grid'])) ?>" 
               class="btn btn-sm rounded-pill <?= ($data['view_mode'] === 'grid') ? 'btn-primary' : 'btn-light text-muted' ?>">
                <i class="bi bi-grid-3x3-gap me-1"></i> Grid Hari
            </a>
            <a href="<?= BASEURL; ?>/admin_jadwal_mengajar?<?= http_build_query(array_merge($data['filters'], ['view' => 'table'])) ?>" 
               class="btn btn-sm rounded-pill <?= ($data['view_mode'] === 'table') ? 'btn-primary' : 'btn-light text-muted' ?>">
                <i class="bi bi-table me-1"></i> Tabel
            </a>
        </div>
        <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Jadwal
        </button>
    </div>
</div>

<!-- Summary Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Total Sesi Mengajar</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1"><?= $data['stats']['total_jadwal'] ?></h3>
                </div>
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-calendar2-week-fill fs-5"></i>
                </div>
            </div>
            <div class="mt-2 text-muted" style="font-size: 12px;">
                <span class="text-success fw-bold"><i class="bi bi-check2-circle"></i> <?= $data['stats']['aktif'] ?> Sesi Aktif</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Guru Terjadwal</span>
                    <h3 class="fw-bold text-success mb-0 mt-1"><?= $data['stats']['total_guru'] ?></h3>
                </div>
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-person-video3 fs-5"></i>
                </div>
            </div>
            <div class="mt-2 text-muted" style="font-size: 12px;">Tenaga Pengajar Aktif</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Kelas Memiliki Jadwal</span>
                    <h3 class="fw-bold text-info mb-0 mt-1"><?= $data['stats']['total_kelas'] ?></h3>
                </div>
                <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-mortarboard-fill fs-5"></i>
                </div>
            </div>
            <div class="mt-2 text-muted" style="font-size: 12px;">Rombel Terjadwal</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Deteksi Bentrok</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1"><i class="bi bi-shield-check text-success"></i> Aman</h3>
                </div>
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-lightning-charge-fill fs-5"></i>
                </div>
            </div>
            <div class="mt-2 text-muted" style="font-size: 12px;">Validasi Bentrok Otomatis Aktif</div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASEURL; ?>/admin_jadwal_mengajar" class="row g-2 align-items-end">
            <input type="hidden" name="view" value="<?= htmlspecialchars($data['view_mode']) ?>">
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Hari</label>
                <select name="hari" class="form-select form-select-sm">
                    <option value="">Semua Hari</option>
                    <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h): ?>
                        <option value="<?= $h ?>" <?= ($data['filters']['hari'] == $h) ? 'selected' : '' ?>><?= $h ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Guru Pengajar</label>
                <select name="guru_id" class="form-select form-select-sm">
                    <option value="">Semua Guru</option>
                    <?php foreach ($data['guru_list'] as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= ($data['filters']['guru_id'] == $g['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Kelas</label>
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($data['kelas_list'] as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= ($data['filters']['kelas_id'] == $k['id']) ? 'selected' : '' ?>>
                            Kelas <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Pencarian</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="keyword" class="form-control" placeholder="Guru, mapel, ruangan..." value="<?= htmlspecialchars($data['filters']['keyword']) ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 flex-grow-1">
                    <i class="bi bi-filter"></i> Filter
                </button>
                <a href="<?= BASEURL; ?>/admin_jadwal_mengajar?view=<?= $data['view_mode'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<?php if ($data['view_mode'] === 'kelas'): ?>
    <!-- ══════════ TAMPILAN JADWAL HARIAN PER KELAS ══════════ -->
    <?php
    // Kelompokkan jadwal per kelas
    $jadwalPerKelas = [];
    foreach ($data['jadwal_list'] as $j) {
        $kId = $j['kelas_id'];
        if (!isset($jadwalPerKelas[$kId])) {
            $jadwalPerKelas[$kId] = [
                'id' => $kId,
                'nama_kelas' => $j['nama_kelas'],
                'total_sesi' => 0,
                'days' => [
                    'Senin' => [],
                    'Selasa' => [],
                    'Rabu' => [],
                    'Kamis' => [],
                    'Jumat' => [],
                    'Sabtu' => []
                ]
            ];
        }
        $jadwalPerKelas[$kId]['total_sesi']++;
        $jadwalPerKelas[$kId]['days'][$j['hari']][] = $j;
    }

    $hariPillBadge = [
        'Senin' => ['badge' => 'bg-primary text-white', 'border' => 'border-primary', 'bg_subtle' => 'bg-primary-subtle text-primary', 'accent_color' => '#2563EB'],
        'Selasa' => ['badge' => 'bg-info text-white', 'border' => 'border-info', 'bg_subtle' => 'bg-info-subtle text-info', 'accent_color' => '#0891B2'],
        'Rabu' => ['badge' => 'bg-warning text-dark', 'border' => 'border-warning', 'bg_subtle' => 'bg-warning-subtle text-warning-emphasis', 'accent_color' => '#D97706'],
        'Kamis' => ['badge' => 'bg-success text-white', 'border' => 'border-success', 'bg_subtle' => 'bg-success-subtle text-success', 'accent_color' => '#16A34A'],
        'Jumat' => ['badge' => 'bg-danger text-white', 'border' => 'border-danger', 'bg_subtle' => 'bg-danger-subtle text-danger', 'accent_color' => '#DC2626'],
        'Sabtu' => ['badge' => 'bg-secondary text-white', 'border' => 'border-secondary', 'bg_subtle' => 'bg-secondary-subtle text-secondary', 'accent_color' => '#475569']
    ];

    $daftarHari = !empty($data['filters']['hari']) ? [$data['filters']['hari']] : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    ?>

    <?php if (empty($jadwalPerKelas)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center mb-4 bg-white">
            <div class="py-4">
                <i class="bi bi-calendar2-x display-4 text-muted opacity-50 mb-3 d-block"></i>
                <h5 class="fw-bold text-dark">Tidak Ada Jadwal Mengajar Ditemukan</h5>
                <p class="text-muted small mb-3">Tidak ada jadwal KBM yang sesuai dengan filter pencarian Anda.</p>
                <a href="<?= BASEURL; ?>/admin_jadwal_mengajar?view=kelas" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Quick Filter Pills Per Kelas -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-circle">
                            <i class="bi bi-mortarboard fs-6"></i>
                        </span>
                        <div>
                            <span class="fw-bold text-dark d-block" style="font-size: 13.5px;">Pilih Rombel / Kelas:</span>
                            <span class="text-muted small" style="font-size: 11px;">Klik tombol kelas di bawah untuk memfilter tampilan jadwal kelas terkait secara instan</span>
                        </div>
                    </div>
                    <span class="badge bg-light text-muted border px-3 py-1">
                        <?= count($jadwalPerKelas) ?> Kelas Terjadwal (<?= count($data['jadwal_list']) ?> Total Sesi)
                    </span>
                </div>
                <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 btn-filter-kelas-pill active shadow-sm fw-semibold" data-target-kelas="all">
                        <i class="bi bi-collection me-1"></i> Semua Kelas (<?= count($jadwalPerKelas) ?>)
                    </button>
                    <?php foreach ($jadwalPerKelas as $kId => $kInfo): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 btn-filter-kelas-pill fw-semibold" data-target-kelas="<?= $kId ?>">
                            <i class="bi bi-mortarboard me-1 text-primary"></i> Kelas <?= htmlspecialchars($kInfo['nama_kelas']) ?> 
                            <span class="badge bg-primary rounded-pill ms-1"><?= $kInfo['total_sesi'] ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Daftar Jadwal Per Kelas -->
        <?php foreach ($jadwalPerKelas as $kId => $kInfo): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4 class-schedule-block" data-kelas-id="<?= $kId ?>" id="kelas-section-<?= $kId ?>">
                <!-- Class Header -->
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-mortarboard-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold text-dark mb-0">Kelas <?= htmlspecialchars($kInfo['nama_kelas']) ?></h5>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                    <?= $kInfo['total_sesi'] ?> Sesi KBM
                                </span>
                            </div>
                            <span class="text-muted small" style="font-size: 12px;">
                                Jadwal Pelajaran Harian & Mingguan Kelas <?= htmlspecialchars($kInfo['nama_kelas']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold btnTambahJadwalKelas" data-kelas-id="<?= $kId ?>" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Jadwal Kelas Ini
                        </button>
                    </div>
                </div>

                <!-- Day Filter Tabs for This Class -->
                <div class="px-4 py-2.5 bg-white border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small fw-semibold" style="font-size: 12px;">
                            <i class="bi bi-calendar3 me-1 text-primary"></i>Pilih Hari:
                        </span>
                        <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border class-day-tabs">
                            <button type="button" class="btn btn-sm rounded-pill px-3 fw-semibold btn-day-tab active" data-class-id="<?= $kId ?>" data-day="all">
                                Semua Hari (<?= $kInfo['total_sesi'] ?>)
                            </button>
                            <?php foreach ($daftarHari as $day): 
                                $count = count($kInfo['days'][$day] ?? []);
                                $isToday = ($day === ($data['hari_ini'] ?? DateHelper::hariIni()));
                            ?>
                                <button type="button" class="btn btn-sm rounded-pill px-2.5 fw-semibold btn-day-tab text-muted" data-class-id="<?= $kId ?>" data-day="<?= $day ?>">
                                    <?= $day ?> (<?= $count ?>)<?= $isToday ? ' &bull; Hari Ini' : '' ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <span class="text-muted small d-none d-lg-inline" style="font-size: 11.5px;">
                        <i class="bi bi-info-circle me-1"></i>Klik tab hari untuk melihat sesi per hari secara leluasa
                    </span>
                </div>

                <!-- Class Schedule Grid (Senin s/d Sabtu) -->
                <div class="card-body p-3 p-md-4 bg-light bg-opacity-25">
                    <div class="row g-3 g-xl-4 schedule-days-container" id="grid-days-kelas-<?= $kId ?>">
                        <?php foreach ($daftarHari as $day): 
                            $daySessions = $kInfo['days'][$day] ?? [];
                            $dayStyle = $hariPillBadge[$day] ?? ['badge' => 'bg-secondary text-white', 'border' => 'border-secondary', 'bg_subtle' => 'bg-secondary-subtle text-secondary', 'accent_color' => '#2563EB'];
                            $isToday = ($day === ($data['hari_ini'] ?? DateHelper::hariIni()));
                        ?>
                            <div class="col-12 col-md-6 col-xl-4 schedule-day-column" data-day="<?= $day ?>" data-class-id="<?= $kId ?>">
                                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden bg-white">
                                    <!-- Day Header -->
                                    <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge <?= $dayStyle['badge'] ?> rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                                <i class="bi bi-calendar2-day me-1"></i>Hari <?= $day ?>
                                            </span>
                                            <?php if ($isToday): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 10px;">
                                                    <i class="bi bi-check-circle-fill me-1"></i>Hari Ini
                                                </span>
                                            <?php endif; ?>
                                            <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                                <?= count($daySessions) ?> Sesi
                                            </span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 fw-semibold btnQuickAddJadwal" 
                                                data-kelas-id="<?= $kId ?>" data-hari="<?= $day ?>" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal" style="font-size: 11px;" title="Tambah Jadwal Hari <?= $day ?>">
                                            <i class="bi bi-plus me-0.5"></i> Tambah
                                        </button>
                                    </div>

                                    <!-- Day Sessions List - Natural flow tanpa trapped scrollbar -->
                                    <div class="card-body p-3 bg-light bg-opacity-35">
                                        <?php if (empty($daySessions)): ?>
                                            <div class="py-4 px-3 text-center text-muted small my-1 rounded-3 border border-dashed bg-white">
                                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-2" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-calendar-x text-muted fs-5"></i>
                                                </div>
                                                <span class="text-secondary d-block fw-semibold mb-1" style="font-size: 12.5px;">Belum Ada Jadwal</span>
                                                <p class="text-muted small mb-2" style="font-size: 11px;">Tidak ada sesi KBM untuk hari <?= $day ?></p>
                                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 btnQuickAddJadwal" 
                                                        data-kelas-id="<?= $kId ?>" data-hari="<?= $day ?>" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal" style="font-size: 11px;">
                                                    <i class="bi bi-plus-circle me-1"></i> Tambah Jadwal Hari <?= $day ?>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div class="d-flex flex-column gap-2.5">
                                                <?php foreach ($daySessions as $js): ?>
                                                    <?php
                                                        $kbmStatus = DateHelper::getStatusKBM($day, $js['jam_mulai'], $js['jam_selesai']);
                                                        $isNow = ($kbmStatus['status'] === 'sedang_kbm');
                                                    ?>
                                                    <div class="schedule-session-card p-3 rounded-3 position-relative <?= $isNow ? 'border-success border-2 bg-success-subtle bg-opacity-10' : '' ?>" style="border-left: 4px solid <?= $isNow ? '#10B981' : ($dayStyle['accent_color'] ?? '#2563EB') ?> !important;">
                                                        <!-- Jam & Ruangan -->
                                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                                            <div class="d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-light border text-dark fw-bold" style="font-size: 11px;">
                                                                <i class="bi bi-clock text-primary"></i> <?= substr($js['jam_mulai'], 0, 5) ?> - <?= substr($js['jam_selesai'], 0, 5) ?> WIB
                                                            </div>
                                                            <div class="d-flex align-items-center gap-1">
                                                                <?php if ($isNow): ?>
                                                                    <span class="badge bg-success text-white rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 9.5px;">
                                                                        <i class="bi bi-broadcast me-1"></i>KBM Aktif
                                                                    </span>
                                                                <?php endif; ?>
                                                                <span class="badge bg-white text-secondary border rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                                                    <i class="bi bi-door-open text-primary me-1"></i><?= htmlspecialchars($js['ruangan'] ?? 'Ruang Kelas') ?>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <!-- Mata Pelajaran -->
                                                        <div class="mb-2">
                                                            <div class="d-flex align-items-start justify-content-between gap-1">
                                                                <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px; line-height: 1.35;">
                                                                    <?= htmlspecialchars($js['nama_mapel'] ?? 'Mata Pelajaran') ?>
                                                                </h6>
                                                                <?php if (!empty($js['kode_mapel'])): ?>
                                                                    <span class="badge bg-light text-secondary border px-1.5 py-0.5 rounded" style="font-size: 9.5px; font-family: monospace; flex-shrink: 0;">
                                                                        <?= htmlspecialchars($js['kode_mapel']) ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php if (!empty($js['keterangan'])): ?>
                                                                <div class="text-muted mt-1" style="font-size: 11px;">
                                                                    <i class="bi bi-info-circle me-1 text-primary"></i><?= htmlspecialchars($js['keterangan']) ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Guru Pengajar & Tombol Aksi -->
                                                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-light-subtle">
                                                            <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                                                                <?php if (!empty($js['foto_guru'])): ?>
                                                                    <img src="<?= BASEURL; ?>/uploads/profil/<?= htmlspecialchars($js['foto_guru']) ?>" class="rounded-circle border" style="width: 26px; height: 26px; object-fit: cover; flex-shrink: 0;">
                                                                <?php else: ?>
                                                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 11.5px; flex-shrink: 0;">
                                                                        <i class="bi bi-person-fill"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <span class="text-dark small fw-semibold text-truncate" style="font-size: 12px;" title="<?= htmlspecialchars($js['nama_guru']) ?>">
                                                                    <?= htmlspecialchars($js['nama_guru']) ?>
                                                                </span>
                                                            </div>
                                                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                                <button type="button" class="btn btn-sm btn-light border text-primary rounded-circle d-inline-flex align-items-center justify-content-center btnEditJadwal" style="width: 28px; height: 28px;" data-id="<?= $js['id'] ?>" title="Edit Jadwal">
                                                                    <i class="bi bi-pencil-square" style="font-size: 12px;"></i>
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-light border text-danger rounded-circle d-inline-flex align-items-center justify-content-center btnHapusJadwal" style="width: 28px; height: 28px;" data-id="<?= $js['id'] ?>" data-info="<?= htmlspecialchars($js['nama_guru'] . ' (' . $js['nama_kelas'] . ' - ' . $js['hari'] . ')') ?>" title="Hapus Jadwal">
                                                                    <i class="bi bi-trash3" style="font-size: 12px;"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

<?php elseif ($data['view_mode'] === 'grid'): ?>
    <!-- ══════════ TAMPILAN GRID MINGGUAN (SENIN - SABTU) ══════════ -->
    <div class="row g-3 g-xl-4 mb-4">
        <?php 
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        foreach ($days as $day): 
            $daySchedules = array_filter($data['jadwal_list'], function($j) use ($day) {
                return $j['hari'] === $day;
            });
            $dayStyle = $hariPillBadge[$day] ?? ['badge' => 'bg-secondary text-white', 'border' => 'border-secondary', 'accent_color' => '#2563EB'];
            $isToday = ($day === ($data['hari_ini'] ?? DateHelper::hariIni()));
        ?>
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden bg-white">
                    <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge <?= $dayStyle['badge'] ?> rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                                <i class="bi bi-calendar2-day me-1"></i>Hari <?= $day ?>
                            </span>
                            <?php if ($isToday): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 10px;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Hari Ini
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                            <?= count($daySchedules) ?> Sesi
                        </span>
                    </div>
                    <div class="card-body p-3 bg-light bg-opacity-35">
                        <?php if (empty($daySchedules)): ?>
                            <div class="text-center py-4 text-muted small my-1 rounded-3 border border-dashed bg-white">
                                <i class="bi bi-calendar-x fs-3 d-block text-secondary opacity-50 mb-1"></i>
                                Tidak ada jadwal di hari <?= $day ?>.
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-2.5">
                                <?php foreach ($daySchedules as $js): ?>
                                    <div class="schedule-session-card p-3 rounded-3 position-relative" style="border-left: 4px solid <?= $dayStyle['accent_color'] ?? '#2563EB' ?> !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                            <div class="d-inline-flex align-items-center gap-1.5 px-2 py-0.5 rounded-pill bg-light border text-dark fw-bold" style="font-size: 11px;">
                                                <i class="bi bi-clock text-primary"></i> <?= substr($js['jam_mulai'], 0, 5) ?> - <?= substr($js['jam_selesai'], 0, 5) ?> WIB
                                            </div>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                                <?= htmlspecialchars($js['nama_kelas']) ?>
                                            </span>
                                        </div>
                                        <h6 class="mb-1 text-dark fw-bold" style="font-size: 13.5px; line-height: 1.35;">
                                            <?= htmlspecialchars($js['nama_mapel'] ?? 'Mata Pelajaran') ?>
                                        </h6>
                                        <div class="d-flex align-items-center justify-content-between pt-2 mt-2 border-top border-light-subtle">
                                            <div class="d-flex align-items-center gap-1.5 overflow-hidden me-2">
                                                <i class="bi bi-person text-secondary"></i>
                                                <span class="text-dark small fw-semibold text-truncate" style="font-size: 12px;">
                                                    <?= htmlspecialchars($js['nama_guru']) ?>
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                <button type="button" class="btn btn-sm btn-light border text-primary rounded-circle d-inline-flex align-items-center justify-content-center btnEditJadwal" style="width: 28px; height: 28px;" data-id="<?= $js['id'] ?>" title="Edit">
                                                    <i class="bi bi-pencil-square" style="font-size: 12px;"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light border text-danger rounded-circle d-inline-flex align-items-center justify-content-center btnHapusJadwal" style="width: 28px; height: 28px;" data-id="<?= $js['id'] ?>" data-info="<?= htmlspecialchars($js['nama_guru'] . ' (' . $js['nama_kelas'] . ')') ?>" title="Hapus">
                                                    <i class="bi bi-trash3" style="font-size: 12px;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- ══════════ TAMPILAN TABEL STANDAR ══════════ -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table me-2 text-primary"></i>Daftar Jadwal Mengajar (<?= count($data['jadwal_list']) ?> Jadwal)</h6>
            <span class="badge bg-light text-muted border px-2 py-1">Tahun Ajaran 2026/2027</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="4%" class="text-center">No</th>
                        <th width="9%">Hari</th>
                        <th width="13%">Waktu</th>
                        <th width="11%">Kelas</th>
                        <th width="20%">Mata Pelajaran</th>
                        <th width="16%">Guru Pengajar</th>
                        <th width="10%">Ruangan</th>
                        <th width="10%" class="text-center">Status KBM</th>
                        <th width="7%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['jadwal_list'])): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar2-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                Belum ada jadwal mengajar yang sesuai filter pencarian.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($data['jadwal_list'] as $j): ?>
                            <?php
                                $kbmStatus = DateHelper::getStatusKBM($j['hari'], $j['jam_mulai'], $j['jam_selesai']);
                                $isNow = ($kbmStatus['status'] === 'sedang_kbm');
                            ?>
                            <tr class="<?= $isNow ? 'table-primary bg-opacity-25' : '' ?>">
                                <td class="text-center text-muted small"><?= $no++ ?></td>
                                <td>
                                    <?php
                                        $hariBadge = [
                                            'Senin' => 'bg-primary-subtle text-primary border-primary-subtle',
                                            'Selasa' => 'bg-info-subtle text-info border-info-subtle',
                                            'Rabu' => 'bg-warning-subtle text-warning border-warning-subtle',
                                            'Kamis' => 'bg-success-subtle text-success border-success-subtle',
                                            'Jumat' => 'bg-danger-subtle text-danger border-danger-subtle',
                                            'Sabtu' => 'bg-secondary-subtle text-secondary border-secondary-subtle'
                                        ];
                                        $hClass = $hariBadge[$j['hari']] ?? 'bg-light text-dark';
                                    ?>
                                    <span class="badge <?= $hClass ?> border px-2 py-1 fw-bold"><?= $j['hari'] ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?></span>
                                    <span class="text-muted d-block small" style="font-size: 10px;">
                                        (<?= round((strtotime($j['jam_selesai']) - strtotime($j['jam_mulai'])) / 60) ?> Menit)
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 fs-6">
                                        <?= htmlspecialchars($j['nama_kelas']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($j['nama_mapel'] ?? '-') ?></div>
                                    <?php if (!empty($j['kode_mapel'])): ?>
                                        <code class="text-muted small" style="font-size: 11px;"><?= htmlspecialchars($j['kode_mapel']) ?></code>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($j['foto_guru'])): ?>
                                            <img src="<?= BASEURL; ?>/uploads/profil/<?= htmlspecialchars($j['foto_guru']) ?>" class="rounded-circle border" style="width: 28px; height: 28px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-inline-flex align-items-center justify-content-center text-muted" style="width: 28px; height: 28px; font-size: 12px;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <span class="fw-semibold text-dark d-block" style="font-size: 13px;"><?= htmlspecialchars($j['nama_guru']) ?></span>
                                            <span class="text-muted" style="font-size: 10px;">NIP: <?= htmlspecialchars($j['nip'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <i class="bi bi-door-open me-1"></i><?= htmlspecialchars($j['ruangan'] ?? 'Ruang Kelas') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $kbmStatus['badge_class'] ?> rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                        <i class="<?= $kbmStatus['icon'] ?> me-1"></i><?= $kbmStatus['label'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary btnEditJadwal" data-id="<?= $j['id'] ?>" title="Edit Jadwal">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btnHapusJadwal" data-id="<?= $j['id'] ?>" data-info="<?= htmlspecialchars($j['nama_guru'] . ' (' . $j['nama_kelas'] . ' - ' . $j['hari'] . ')') ?>" title="Hapus Jadwal">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Modal Tambah Jadwal Mengajar -->
<div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Tambah Jadwal Mengajar Guru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASEURL; ?>/admin_jadwal_mengajar/tambah" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Guru Pengajar <span class="text-danger">*</span></label>
                            <select name="guru_id" class="form-select" required>
                                <option value="">-- Pilih Guru --</option>
                                <?php foreach ($data['guru_list'] as $g): ?>
                                    <option value="<?= $g['id'] ?>">
                                        <?= htmlspecialchars($g['nama']) ?> (<?= htmlspecialchars($g['mata_pelajaran'] ?? 'Semua Mapel') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mapel_id" class="form-select" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <?php foreach ($data['mapel_list'] as $mp): ?>
                                    <option value="<?= $mp['id'] ?>">
                                        [<?= htmlspecialchars($mp['kode_mapel']) ?>] <?= htmlspecialchars($mp['nama_mapel']) ?> (<?= htmlspecialchars($mp['kelompok']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Kelas / Rombel <span class="text-danger">*</span></label>
                            <select name="kelas_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($data['kelas_list'] as $k): ?>
                                    <option value="<?= $k['id'] ?>">Kelas <?= htmlspecialchars($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Hari Mengajar <span class="text-danger">*</span></label>
                            <select name="hari" class="form-select" required>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Ruangan / Lab</label>
                            <input type="text" name="ruangan" class="form-control" placeholder="Contoh: Lab Komputer 1 / Kelas" value="Ruang Kelas">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" class="form-control" value="07:30" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_selesai" class="form-control" value="09:00" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Keterangan / Catatan Materi</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Praktik Jaringan Dasar / Teori Umum">
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ubah Jadwal Mengajar -->
<div class="modal fade" id="modalUbahJadwal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-warning text-dark border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-1"></i> Ubah Jadwal Mengajar</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASEURL; ?>/admin_jadwal_mengajar/ubah" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                <input type="hidden" name="id" id="edit_jadwal_id">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Guru Pengajar <span class="text-danger">*</span></label>
                            <select name="guru_id" id="edit_guru_id" class="form-select" required>
                                <option value="">-- Pilih Guru --</option>
                                <?php foreach ($data['guru_list'] as $g): ?>
                                    <option value="<?= $g['id'] ?>">
                                        <?= htmlspecialchars($g['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mapel_id" id="edit_mapel_id" class="form-select" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <?php foreach ($data['mapel_list'] as $mp): ?>
                                    <option value="<?= $mp['id'] ?>">
                                        [<?= htmlspecialchars($mp['kode_mapel']) ?>] <?= htmlspecialchars($mp['nama_mapel']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Kelas / Rombel <span class="text-danger">*</span></label>
                            <select name="kelas_id" id="edit_kelas_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($data['kelas_list'] as $k): ?>
                                    <option value="<?= $k['id'] ?>">Kelas <?= htmlspecialchars($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Hari Mengajar <span class="text-danger">*</span></label>
                            <select name="hari" id="edit_hari" class="form-select" required>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">Ruangan / Lab</label>
                            <input type="text" name="ruangan" id="edit_ruangan" class="form-control" placeholder="Contoh: Lab Komputer 1 / Kelas">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" id="edit_jam_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_selesai" id="edit_jam_selesai" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Keterangan / Catatan Materi</label>
                        <input type="text" name="keterangan" id="edit_keterangan" class="form-control">
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Perbarui Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Hapus Hidden -->
<form id="formHapusJadwal" action="<?= BASEURL; ?>/admin_jadwal_mengajar/hapus" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
    <input type="hidden" name="id" id="hapus_jadwal_id">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter Pills Per Kelas (Instan / Client-Side)
    const filterPills = document.querySelectorAll('.btn-filter-kelas-pill');
    const classBlocks = document.querySelectorAll('.class-schedule-block');

    filterPills.forEach(pill => {
        pill.addEventListener('click', function() {
            filterPills.forEach(p => {
                p.classList.remove('btn-primary', 'active', 'shadow-sm');
                p.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-primary', 'active', 'shadow-sm');

            const targetKelas = this.getAttribute('data-target-kelas');
            classBlocks.forEach(block => {
                if (targetKelas === 'all' || block.getAttribute('data-kelas-id') === targetKelas) {
                    block.style.display = '';
                } else {
                    block.style.display = 'none';
                }
            });
        });
    });

    // Filter Hari Per Kelas (Instan / Client-Side)
    document.querySelectorAll('.btn-day-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const classId = this.getAttribute('data-class-id');
            const targetDay = this.getAttribute('data-day');
            const container = document.getElementById('grid-days-kelas-' + classId);
            if (!container) return;

            // Reset active state di grup tab kelas ini saja
            const parent = this.closest('.class-day-tabs') || this.parentElement;
            parent.querySelectorAll('.btn-day-tab').forEach(t => {
                t.classList.remove('active', 'btn-primary');
                t.classList.add('text-muted');
            });
            this.classList.add('active', 'btn-primary');
            this.classList.remove('text-muted');

            // Filter kolom hari di dalam kelas ini
            const cols = container.querySelectorAll('.schedule-day-column');
            cols.forEach(col => {
                const colDay = col.getAttribute('data-day');
                if (targetDay === 'all') {
                    col.style.display = '';
                    col.className = 'col-12 col-md-6 col-xl-4 schedule-day-column';
                } else if (colDay === targetDay) {
                    col.style.display = '';
                    col.className = 'col-12 col-lg-8 mx-auto schedule-day-column';
                } else {
                    col.style.display = 'none';
                }
            });
        });
    });

    // Quick Add: Set Kelas ID di modal tambah
    document.querySelectorAll('.btnTambahJadwalKelas').forEach(btn => {
        btn.addEventListener('click', function() {
            const kId = this.getAttribute('data-kelas-id');
            const selectKelas = document.querySelector('#modalTambahJadwal select[name="kelas_id"]');
            if (selectKelas && kId) {
                selectKelas.value = kId;
            }
        });
    });

    // Quick Add: Set Kelas ID dan Hari di modal tambah
    document.querySelectorAll('.btnQuickAddJadwal').forEach(btn => {
        btn.addEventListener('click', function() {
            const kId = this.getAttribute('data-kelas-id');
            const hari = this.getAttribute('data-hari');
            const selectKelas = document.querySelector('#modalTambahJadwal select[name="kelas_id"]');
            const selectHari = document.querySelector('#modalTambahJadwal select[name="hari"]');
            if (selectKelas && kId) {
                selectKelas.value = kId;
            }
            if (selectHari && hari) {
                selectHari.value = hari;
            }
        });
    });

    // Edit Jadwal Button Handler
    document.querySelectorAll('.btnEditJadwal').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            try {
                const res = await fetch('<?= BASEURL; ?>/admin_jadwal_mengajar/get_detail/' + id);
                const data = await res.json();
                if (data && data.id) {
                    document.getElementById('edit_jadwal_id').value = data.id;
                    document.getElementById('edit_guru_id').value = data.guru_id;
                    document.getElementById('edit_mapel_id').value = data.mapel_id || '';
                    document.getElementById('edit_kelas_id').value = data.kelas_id;
                    document.getElementById('edit_hari').value = data.hari;
                    document.getElementById('edit_jam_mulai').value = data.jam_mulai ? data.jam_mulai.substring(0, 5) : '';
                    document.getElementById('edit_jam_selesai').value = data.jam_selesai ? data.jam_selesai.substring(0, 5) : '';
                    document.getElementById('edit_ruangan').value = data.ruangan || '';
                    document.getElementById('edit_keterangan').value = data.keterangan || '';

                    const modal = new bootstrap.Modal(document.getElementById('modalUbahJadwal'));
                    modal.show();
                }
            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Data',
                    text: 'Tidak dapat mengambil detail jadwal mengajar.'
                });
            }
        });
    });

    // Hapus Jadwal Handler
    document.querySelectorAll('.btnHapusJadwal').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const info = this.getAttribute('data-info');

            Swal.fire({
                title: 'Hapus Jadwal Mengajar?',
                text: `Apakah Anda yakin ingin menghapus jadwal ${info}? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('hapus_jadwal_id').value = id;
                    document.getElementById('formHapusJadwal').submit();
                }
            });
        });
    });

    // Live Clock Updater
    const adminClockEl = document.getElementById('liveClockAdminJadwal');
    if (adminClockEl) {
        setInterval(() => {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            adminClockEl.textContent = `${h}:${m}:${s} WIB`;
        }, 1000);
    }
});
</script>
