<?php if(!$data['stats']): ?>
    <div class="alert alert-warning shadow-sm">
        <h4 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Belum Ter-assign</h4>
        <p class="mb-0">Anda belum diatur sebagai Wali Kelas di data manapun. Silakan hubungi Administrator untuk penugasan kelas.</p>
    </div>
<?php else: ?>
    <?php $stats = $data['stats']; ?>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 p-3 bg-light rounded-3 border">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-mortarboard-fill fs-5"></i>
            </div>
            <div>
                <div class="fw-bold text-dark">Wali Kelas: <span class="text-primary">Kelas <?= htmlspecialchars($stats['info_kelas']['nama_kelas']) ?></span></div>
                <div class="text-muted small">Monitoring kehadiran & pembinaan siswa kelas per <?= date('d F Y') ?></div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fs-6 px-3 py-2 rounded-pill">
                <i class="bi bi-house-door me-1"></i> Kelas <?= htmlspecialchars($stats['info_kelas']['nama_kelas']) ?>
            </span>
            <a href="<?= BASEURL ?>/wali_kelas/absensi" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="bi bi-qr-code me-1"></i> Absensi Kelas
            </a>
            <a href="<?= BASEURL ?>/wali_kelas/catatan" class="btn btn-sm btn-outline-success rounded-pill px-3">
                <i class="bi bi-journal-text me-1"></i> Catatan Siswa
            </a>
            <a href="<?= BASEURL ?>/wali_kelas/rekap_absensi" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Rekap
            </a>
            <a href="<?= BASEURL ?>/laporan/siswa" class="btn btn-sm btn-primary rounded-pill px-3">
                <i class="bi bi-clock-history me-1"></i> Riwayat Absen
            </a>
        </div>
    </div>

    <!-- ═══ ROW 1: CARD UTAMA (TAHAP 9) ════════════════════════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- Total Siswa -->
        <div class="col-md-4">
            <div class="card border-0 border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Siswa</div>
                        <div class="h3 mb-0 font-weight-bold text-dark"><?= (int)$stats['total_siswa'] ?></div>
                        <div class="text-muted small mt-1">Siswa terdaftar di kelas</div>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                        <i class="bi bi-people-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan Bulan Ini -->
        <div class="col-md-4">
            <div class="card border-0 border-start border-info border-4 shadow-sm h-100">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Catatan Bulan Ini</div>
                        <div class="h3 mb-0 font-weight-bold text-dark"><?= (int)($stats['catatan_bulan_ini'] ?? 0) ?></div>
                        <div class="text-muted small mt-1">Bulan <?= date('F Y') ?></div>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                        <i class="bi bi-journal-check fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Siswa Membutuhkan Pembinaan -->
        <div class="col-md-4">
            <div class="card border-0 border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Siswa Butuh Pembinaan</div>
                        <div class="h3 mb-0 font-weight-bold text-dark"><?= (int)($stats['siswa_butuh_pembinaan'] ?? 0) ?></div>
                        <div class="text-muted small mt-1">Pembinaan & Pelanggaran</div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                        <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ ROW 2: STATISTIK KEHADIRAN HARI INI ═════════════════════════════════ -->
    <?php
        $sudahAbsen = (int)($stats['status_hadir'] ?? 0) + (int)($stats['status_terlambat'] ?? 0) + (int)($stats['status_izin'] ?? 0) + (int)($stats['status_sakit'] ?? 0) + (int)($stats['status_alpa'] ?? 0);
        $belumAbsen = max(0, (int)($stats['total_siswa'] ?? 0) - $sudahAbsen);
    ?>
    <div class="row g-3 mb-4">
        <!-- Hadir -->
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 bg-success bg-opacity-10 border-start border-success border-4 shadow-sm h-100">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-success text-uppercase">Hadir Hari Ini</div>
                    <div class="h4 mb-0 font-weight-bold text-success"><?= (int)($stats['status_hadir'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <!-- Terlambat -->
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 bg-warning bg-opacity-10 border-start border-warning border-4 shadow-sm h-100">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-warning text-uppercase">Terlambat</div>
                    <div class="h4 mb-0 font-weight-bold text-dark"><?= (int)($stats['status_terlambat'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <!-- Izin -->
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 bg-info bg-opacity-10 border-start border-info border-4 shadow-sm h-100">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-info text-uppercase">Izin</div>
                    <div class="h4 mb-0 font-weight-bold text-info"><?= (int)($stats['status_izin'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <!-- Sakit -->
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 bg-secondary bg-opacity-10 border-start border-secondary border-4 shadow-sm h-100">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase">Sakit</div>
                    <div class="h4 mb-0 font-weight-bold text-secondary"><?= (int)($stats['status_sakit'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <!-- Alpa -->
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 bg-danger bg-opacity-10 border-start border-danger border-4 shadow-sm h-100">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-danger text-uppercase">Alpa</div>
                    <div class="h4 mb-0 font-weight-bold text-danger"><?= (int)($stats['status_alpa'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <!-- Belum Absen -->
        <div class="col-6 col-md-4 col-lg">
            <div class="card border-0 bg-light border-start border-secondary border-4 shadow-sm h-100">
                <div class="card-body py-2">
                    <div class="text-xs font-weight-bold text-muted text-uppercase">Belum Absen</div>
                    <div class="h4 mb-0 font-weight-bold text-dark"><?= $belumAbsen ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ ROW 3: DATA TERBARU (ABSENSI & CATATAN TERBARU) ═══════════════════ -->
    <div class="row g-4 mb-4">
        <!-- Absensi Terbaru -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history me-1"></i> Absensi Terbaru
                    </h6>
                    <a href="<?= BASEURL ?>/wali_kelas/absensi" class="btn btn-sm btn-outline-primary">Lihat Absensi Kelas</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nama Siswa</th>
                                    <th>Jam</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($stats['absensi_terbaru'])): ?>
                                    <?php foreach($stats['absensi_terbaru'] as $absen): ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold"><?= htmlspecialchars($absen['nama']) ?></td>
                                        <td><i class="bi bi-clock me-1 text-muted"></i><?= htmlspecialchars($absen['jam_masuk'] ?: '-') ?></td>
                                        <td>
                                            <?php
                                                $badgeMap = [
                                                    'hadir'     => 'bg-success',
                                                    'terlambat' => 'bg-warning text-dark',
                                                    'izin'      => 'bg-info text-dark',
                                                    'sakit'     => 'bg-secondary',
                                                    'alpa'      => 'bg-danger',
                                                ];
                                                $bc = $badgeMap[$absen['status']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?= $bc ?>"><?= ucfirst($absen['status']) ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Belum ada absensi terbaru untuk siswa di kelas ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-2 d-flex justify-content-between align-items-center">
                    <a href="<?= BASEURL ?>/wali_kelas/rekap_absensi" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i>Rekap Lengkap
                    </a>
                    <a href="<?= BASEURL ?>/laporan/siswa" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-text me-1"></i>Laporan Siswa
                    </a>
                </div>
            </div>
        </div>

        <!-- Catatan Terbaru -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="bi bi-journal-text me-1"></i> Catatan Terbaru
                    </h6>
                    <a href="<?= BASEURL ?>/wali-kelas/catatan-siswa" class="btn btn-sm btn-outline-success">Kelola Catatan Siswa</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nama Siswa</th>
                                    <th>Kategori</th>
                                    <th>Tanggal</th>
                                    <th>Catatan Singkat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($stats['catatan_terbaru'])): ?>
                                    <?php foreach($stats['catatan_terbaru'] as $catatan): ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold"><?= htmlspecialchars($catatan['nama_siswa']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($catatan['kategori']) ?></span></td>
                                        <td class="small text-muted"><?= date('d/m/Y', strtotime($catatan['tanggal'])) ?></td>
                                        <td class="small text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($catatan['catatan']) ?>">
                                            <a href="<?= BASEURL ?>/wali-kelas/catatan-siswa/<?= $catatan['id'] ?>" class="text-decoration-none text-dark">
                                                <?= htmlspecialchars($catatan['catatan']) ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada catatan siswa terbaru di kelas ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ ROW 4: DAFTAR SISWA KELAS & ABSENSI HARI INI ═════════════════════ -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-dark">
                        <i class="bi bi-people-fill me-1 text-primary"></i> Status Absensi Siswa Kelas Hari Ini
                    </h6>
                    <a href="<?= BASEURL ?>/wali_kelas/siswa" class="btn btn-sm btn-outline-secondary">Data Siswa Lengkap</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="ps-3">No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Jam Masuk</th>
                                    <th>Status Hari Ini</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no=1; foreach($stats['daftar_siswa'] as $siswa): ?>
                                <tr>
                                    <td class="ps-3"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($siswa['nis']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($siswa['nama']) ?></td>
                                    <td><?= $siswa['jam_masuk'] ? htmlspecialchars($siswa['jam_masuk']) . ' WIB' : '-' ?></td>
                                    <td>
                                        <?php if(!$siswa['status_absen']): ?>
                                            <span class="badge bg-secondary">Belum Absen</span>
                                        <?php elseif($siswa['status_absen'] == 'hadir'): ?>
                                            <span class="badge bg-success">Hadir</span>
                                        <?php elseif($siswa['status_absen'] == 'terlambat'): ?>
                                            <span class="badge bg-warning text-dark">Terlambat</span>
                                        <?php elseif($siswa['status_absen'] == 'sakit'): ?>
                                            <span class="badge bg-secondary">Sakit</span>
                                        <?php elseif($siswa['status_absen'] == 'izin'): ?>
                                            <span class="badge bg-info text-dark">Izin</span>
                                        <?php elseif($siswa['status_absen'] == 'alpa'): ?>
                                            <span class="badge bg-danger">Alpa</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($stats['daftar_siswa'])): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada siswa yang terdaftar di kelas ini.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
