<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 d-print-none">
    <div>
        <?php if ($data['kelas']): ?>
            <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Kelas: <span class="fw-semibold text-primary"><?= htmlspecialchars($data['kelas']['nama_kelas']) ?></span> | Periode: <span class="fw-semibold"><?= date('d/m/Y', strtotime($data['tgl_mulai'])) ?> - <?= date('d/m/Y', strtotime($data['tgl_akhir'])) ?></span></p>
        <?php else: ?>
            <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Rekapitulasi absensi siswa kelas Anda</p>
        <?php endif; ?>
    </div>
    <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Cetak Rekap Absensi
    </button>
</div>

<!-- Tampilan Header Khusus Cetak (Print View) -->
<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold mb-1">REKAP ABSENSI SISWA KELAS <?= htmlspecialchars($data['kelas']['nama_kelas'] ?? '') ?></h3>
    <p class="mb-0">Periode: <?= date('d F Y', strtotime($data['tgl_mulai'])) ?> s/d <?= date('d F Y', strtotime($data['tgl_akhir'])) ?></p>
    <hr class="my-2">
</div>

<?php Flasher::flash(); ?>

<!-- FILTER FORM (d-print-none) -->
<div class="card shadow-sm border-0 mb-4 d-print-none">
    <div class="card-body">
        <form action="<?= BASEURL ?>/wali-kelas/rekap-absensi" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tanggal Mulai</label>
                <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= htmlspecialchars($data['tgl_mulai']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tanggal Akhir</label>
                <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="<?= htmlspecialchars($data['tgl_akhir']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Pilih Siswa</label>
                <select name="siswa_id" class="form-select form-select-sm">
                    <option value="">-- Semua Siswa --</option>
                    <?php foreach ($data['siswa_list'] as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (isset($data['siswa_id']) && $data['siswa_id'] == $s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nis']) ?> - <?= htmlspecialchars($s['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Filter Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="hadir" <?= (isset($data['status']) && $data['status'] == 'hadir') ? 'selected' : '' ?>>Hadir</option>
                    <option value="terlambat" <?= (isset($data['status']) && $data['status'] == 'terlambat') ? 'selected' : '' ?>>Terlambat</option>
                    <option value="izin" <?= (isset($data['status']) && $data['status'] == 'izin') ? 'selected' : '' ?>>Izin</option>
                    <option value="sakit" <?= (isset($data['status']) && $data['status'] == 'sakit') ? 'selected' : '' ?>>Sakit</option>
                    <option value="alpa" <?= (isset($data['status']) && $data['status'] == 'alpa') ? 'selected' : '' ?>>Alpa</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-filter me-1"></i> Terapkan Filter</button>
                <a href="<?= BASEURL ?>/wali-kelas/rekap-absensi" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- ═══ CARD RINGKASAN REKAP KELAS ═════════════════════════════════════════ -->
<?php $ringkasan = $data['ringkasan']; ?>
<div class="row g-3 mb-4">
    <!-- Hadir -->
    <div class="col">
        <div class="card border-0 bg-success bg-opacity-10 border-start border-success border-4 shadow-sm h-100">
            <div class="card-body py-2">
                <div class="text-xs font-weight-bold text-success text-uppercase">Hadir</div>
                <div class="h4 mb-0 font-weight-bold text-success"><?= (int)($ringkasan['total_hadir'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <!-- Terlambat -->
    <div class="col">
        <div class="card border-0 bg-warning bg-opacity-10 border-start border-warning border-4 shadow-sm h-100">
            <div class="card-body py-2">
                <div class="text-xs font-weight-bold text-warning text-uppercase">Terlambat</div>
                <div class="h4 mb-0 font-weight-bold text-dark"><?= (int)($ringkasan['total_terlambat'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <!-- Izin -->
    <div class="col">
        <div class="card border-0 bg-info bg-opacity-10 border-start border-info border-4 shadow-sm h-100">
            <div class="card-body py-2">
                <div class="text-xs font-weight-bold text-info text-uppercase">Izin</div>
                <div class="h4 mb-0 font-weight-bold text-info"><?= (int)($ringkasan['total_izin'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <!-- Sakit -->
    <div class="col">
        <div class="card border-0 bg-secondary bg-opacity-10 border-start border-secondary border-4 shadow-sm h-100">
            <div class="card-body py-2">
                <div class="text-xs font-weight-bold text-secondary text-uppercase">Sakit</div>
                <div class="h4 mb-0 font-weight-bold text-secondary"><?= (int)($ringkasan['total_sakit'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <!-- Alpa -->
    <div class="col">
        <div class="card border-0 bg-danger bg-opacity-10 border-start border-danger border-4 shadow-sm h-100">
            <div class="card-body py-2">
                <div class="text-xs font-weight-bold text-danger text-uppercase">Alpa</div>
                <div class="h4 mb-0 font-weight-bold text-danger"><?= (int)($ringkasan['total_alpa'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL REKAP ABSENSI -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-file-earmark-bar-graph me-1"></i> Rekapitulasi Kehadiran Siswa</h6>
        <span class="badge bg-light text-dark border d-print-none">Klik nama siswa untuk melihat riwayat lengkap</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%" class="ps-3">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th class="text-success" width="10%">Hadir</th>
                        <th class="text-warning" width="10%">Terlambat</th>
                        <th class="text-info" width="10%">Izin</th>
                        <th class="text-secondary" width="10%">Sakit</th>
                        <th class="text-danger" width="10%">Alpa</th>
                        <th width="15%">Persentase Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['rekap'])): ?>
                        <?php $no = 1; foreach ($data['rekap'] as $r): ?>
                        <tr>
                            <td class="ps-3 text-center"><?= $no++ ?></td>
                            <td class="text-center"><?= htmlspecialchars($r['nis']) ?></td>
                            <td>
                                <a href="<?= BASEURL ?>/wali-kelas/rekap-absensi/siswa/<?= $r['siswa_id'] ?>" class="fw-bold text-primary text-decoration-none" title="Lihat Riwayat Lengkap">
                                    <?= htmlspecialchars($r['nama']) ?> <i class="bi bi-box-arrow-up-right small ms-1 d-print-none"></i>
                                </a>
                            </td>
                            <td class="text-center fw-semibold text-success"><?= (int)$r['total_hadir'] ?></td>
                            <td class="text-center fw-semibold text-warning"><?= (int)$r['total_terlambat'] ?></td>
                            <td class="text-center fw-semibold text-info"><?= (int)$r['total_izin'] ?></td>
                            <td class="text-center fw-semibold text-secondary"><?= (int)$r['total_sakit'] ?></td>
                            <td class="text-center fw-semibold text-danger"><?= (int)$r['total_alpa'] ?></td>
                            <td class="text-center">
                                <?php
                                    $p = $r['persentase'];
                                    $pClass = $p >= 85 ? 'bg-success' : ($p >= 75 ? 'bg-warning text-dark' : 'bg-danger');
                                ?>
                                <span class="badge <?= $pClass ?> px-2 py-1"><?= $p ?>%</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Tidak ada data rekap absensi untuk kriteria filter ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
