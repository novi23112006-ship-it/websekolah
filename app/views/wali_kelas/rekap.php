<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <?php if ($data['kelas']): ?>
            <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Kelas: <span class="fw-semibold text-primary"><?= htmlspecialchars($data['kelas']['nama_kelas']) ?></span> | Periode: <span class="fw-semibold"><?= date('d/m/Y', strtotime($data['tgl_mulai'])) ?> - <?= date('d/m/Y', strtotime($data['tgl_akhir'])) ?></span></p>
        <?php else: ?>
            <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Rekapitulasi absensi siswa kelas Anda</p>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Periode Tanggal -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="<?= BASEURL ?>/wali_kelas/rekap" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Mulai</label>
                <input type="date" name="tgl_mulai" class="form-control form-control-sm" value="<?= htmlspecialchars($data['tgl_mulai']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Akhir</label>
                <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="<?= htmlspecialchars($data['tgl_akhir']) ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-filter me-1"></i> Tampilkan Rekap</button>
                <a href="<?= BASEURL ?>/wali_kelas/rekap" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Rekap -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-file-earmark-bar-graph me-1"></i> Ringkasan Rekap Kehadiran Siswa</h6>
        <button class="btn btn-sm btn-outline-secondary d-print-none" onclick="window.print()"><i class="bi bi-printer me-1"></i> Cetak Rekap</button>
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
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['rekap'])): ?>
                        <?php $no = 1; foreach ($data['rekap'] as $r): ?>
                        <tr>
                            <td class="ps-3 text-center"><?= $no++ ?></td>
                            <td class="text-center"><?= htmlspecialchars($r['nis']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($r['nama']) ?></td>
                            <td class="text-center fw-semibold text-success"><?= (int)$r['total_hadir'] ?></td>
                            <td class="text-center fw-semibold text-warning"><?= (int)$r['total_terlambat'] ?></td>
                            <td class="text-center fw-semibold text-info"><?= (int)$r['total_izin'] ?></td>
                            <td class="text-center fw-semibold text-secondary"><?= (int)$r['total_sakit'] ?></td>
                            <td class="text-center fw-semibold text-danger"><?= (int)$r['total_alpa'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada data rekap absensi untuk periode ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
