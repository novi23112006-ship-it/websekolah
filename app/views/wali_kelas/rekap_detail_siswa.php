<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 d-print-none">
    <div>
        <p class="text-muted mb-0"><i class="bi bi-person-lines-fill me-1"></i> Riwayat Absensi Siswa: <span class="fw-semibold text-primary"><?= htmlspecialchars($data['siswa']['nama']) ?></span> | Kelas: <?= htmlspecialchars($data['kelas']['nama_kelas']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Riwayat
        </button>
        <a href="<?= BASEURL ?>/wali-kelas/rekap-absensi" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap
        </a>
    </div>
</div>

<!-- Header Print View -->
<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold mb-1">LAPORAN RIWAYAT ABSENSI SISWA</h3>
    <p class="mb-0">Nama: <?= htmlspecialchars($data['siswa']['nama']) ?> | NIS: <?= htmlspecialchars($data['siswa']['nis']) ?> | Kelas: <?= htmlspecialchars($data['kelas']['nama_kelas']) ?></p>
    <hr class="my-2">
</div>

<?php Flasher::flash(); ?>

<?php $s = $data['siswa']; $stats = $data['stats']; ?>

<!-- PROFIL & STATISTIK RINGKAS SISWA -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small font-weight-bold mb-3"><i class="bi bi-person-badge me-1"></i> Data Siswa</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr><td width="35%" class="text-muted">Nama</td><td class="fw-bold"><?= htmlspecialchars($s['nama']) ?></td></tr>
                    <tr><td class="text-muted">NIS</td><td><?= htmlspecialchars($s['nis']) ?></td></tr>
                    <tr><td class="text-muted">NISN</td><td><?= htmlspecialchars($s['nisn'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">Kelas</td><td><span class="badge bg-primary"><?= htmlspecialchars($data['kelas']['nama_kelas']) ?></span></td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="text-muted text-uppercase small font-weight-bold mb-3"><i class="bi bi-graph-up me-1"></i> Ringkasan Kehadiran</h6>
                <div class="row text-center g-2">
                    <div class="col">
                        <div class="p-2 border rounded bg-success bg-opacity-10">
                            <small class="text-success fw-bold d-block">Hadir</small>
                            <span class="fs-4 fw-bold text-success"><?= $stats['hadir'] ?></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-warning bg-opacity-10">
                            <small class="text-warning fw-bold d-block">Terlambat</small>
                            <span class="fs-4 fw-bold text-dark"><?= $stats['terlambat'] ?></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-info bg-opacity-10">
                            <small class="text-info fw-bold d-block">Izin</small>
                            <span class="fs-4 fw-bold text-info"><?= $stats['izin'] ?></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-secondary bg-opacity-10">
                            <small class="text-secondary fw-bold d-block">Sakit</small>
                            <span class="fs-4 fw-bold text-secondary"><?= $stats['sakit'] ?></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-danger bg-opacity-10">
                            <small class="text-danger fw-bold d-block">Alpa</small>
                            <span class="fs-4 fw-bold text-danger"><?= $stats['alpa'] ?></span>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 border rounded bg-dark bg-opacity-10">
                            <small class="text-dark fw-bold d-block">Kehadiran</small>
                            <span class="fs-4 fw-bold text-dark"><?= $stats['persentase'] ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FILTER PERIODE (d-print-none) -->
<div class="card shadow-sm border-0 mb-4 d-print-none">
    <div class="card-body py-3">
        <form action="<?= BASEURL ?>/wali-kelas/rekap-absensi/siswa/<?= $s['id'] ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Mulai</label>
                <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= htmlspecialchars($data['tgl_mulai'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Akhir</label>
                <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="<?= htmlspecialchars($data['tgl_akhir'] ?? '') ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                <a href="<?= BASEURL ?>/wali-kelas/rekap-absensi/siswa/<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- TABEL DETAIL RIWAYAT ABSENSI -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-clock-history me-1"></i> Log Riwayat Absensi</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="ps-3">No</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['riwayat'])): ?>
                        <?php $no = 1; foreach ($data['riwayat'] as $r): ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td class="fw-semibold"><?= date('d F Y', strtotime($r['tanggal'])) ?></td>
                            <td><?= $r['jam_masuk'] ? htmlspecialchars($r['jam_masuk']) . ' WIB' : '-' ?></td>
                            <td>
                                <?php
                                    $badgeMap = [
                                        'hadir'     => 'bg-success',
                                        'terlambat' => 'bg-warning text-dark',
                                        'izin'      => 'bg-info text-dark',
                                        'sakit'     => 'bg-secondary',
                                        'alpa'      => 'bg-danger',
                                    ];
                                    $bc = $badgeMap[$r['status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $bc ?>"><?= ucfirst($r['status']) ?></span>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($r['keterangan'] ?: '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat absensi untuk siswa ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
