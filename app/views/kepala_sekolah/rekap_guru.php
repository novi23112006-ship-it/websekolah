<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 d-print-none">
    <div>
        <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Rekapitulasi Kehadiran Guru / Tenaga Pendidik</p>
    </div>
    <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Cetak Rekap Guru
    </button>
</div>

<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold mb-1">REKAPITULASI ABSENSI GURU SEKOLAH</h3>
    <p class="mb-0">Periode: <?= date('d F Y', strtotime($data['filters']['tgl_mulai'])) ?> s/d <?= date('d F Y', strtotime($data['filters']['tgl_akhir'])) ?></p>
    <hr class="my-2">
</div>

<!-- Filter Form -->
<div class="card shadow-sm border-0 mb-4 d-print-none">
    <div class="card-body">
        <form action="<?= BASEURL ?>/kepala_sekolah/rekap_guru" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Mulai</label>
                <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= htmlspecialchars($data['filters']['tgl_mulai']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Akhir</label>
                <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="<?= htmlspecialchars($data['filters']['tgl_akhir']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Filter Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="hadir" <?= $data['filters']['status'] == 'hadir' ? 'selected' : '' ?>>Hadir</option>
                    <option value="terlambat" <?= $data['filters']['status'] == 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
                    <option value="izin" <?= $data['filters']['status'] == 'izin' ? 'selected' : '' ?>>Izin</option>
                    <option value="sakit" <?= $data['filters']['status'] == 'sakit' ? 'selected' : '' ?>>Sakit</option>
                    <option value="alpa" <?= $data['filters']['status'] == 'alpa' ? 'selected' : '' ?>>Alpa</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-filter me-1"></i> Terapkan Filter</button>
                <a href="<?= BASEURL ?>/kepala_sekolah/rekap_guru" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Rekap Guru -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-success"><i class="bi bi-journal-check me-1"></i> Data Rekap Kehadiran Guru</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th width="5%" class="ps-3">No</th>
                        <th>NIP</th>
                        <th>Nama Guru</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-success" width="10%">Hadir</th>
                        <th class="text-warning" width="10%">Terlambat</th>
                        <th class="text-info" width="10%">Izin</th>
                        <th class="text-secondary" width="10%">Sakit</th>
                        <th class="text-danger" width="10%">Alpa</th>
                        <th width="12%">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['rekap'])): ?>
                        <?php $no = 1; foreach ($data['rekap'] as $r): ?>
                        <tr>
                            <td class="ps-3 text-center"><?= $no++ ?></td>
                            <td class="text-center"><?= htmlspecialchars($r['nip'] ?: '-') ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($r['nama']) ?></td>
                            <td><?= htmlspecialchars($r['mata_pelajaran'] ?: '-') ?></td>
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
                            <td colspan="10" class="text-center text-muted py-4">Tidak ada data rekap guru yang sesuai filter.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
