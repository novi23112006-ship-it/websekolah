<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <?php if ($data['kelas']): ?>
            <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Kelas: <span class="fw-semibold text-primary"><?= htmlspecialchars($data['kelas']['nama_kelas']) ?></span> | Tanggal: <span class="fw-semibold"><?= date('d F Y', strtotime($data['tanggal'])) ?></span></p>
        <?php else: ?>
            <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Kelola dan pantau presensi harian siswa kelas Anda</p>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Tanggal & Status -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="<?= BASEURL ?>/wali_kelas/absensi" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Absensi</label>
                <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= htmlspecialchars($data['tanggal']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Status Kehadiran</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="hadir" <?= $data['status'] == 'hadir' ? 'selected' : '' ?>>Hadir</option>
                    <option value="terlambat" <?= $data['status'] == 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
                    <option value="izin" <?= $data['status'] == 'izin' ? 'selected' : '' ?>>Izin</option>
                    <option value="sakit" <?= $data['status'] == 'sakit' ? 'selected' : '' ?>>Sakit</option>
                    <option value="alpa" <?= $data['status'] == 'alpa' ? 'selected' : '' ?>>Alpa</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-filter me-1"></i> Filter</button>
                <a href="<?= BASEURL ?>/wali_kelas/absensi" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Absensi Kelas -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-clipboard-check me-1"></i> Data Absensi Kelas</h6>
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
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['siswa'])): ?>
                        <?php $no = 1; foreach ($data['siswa'] as $s): ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($s['nis']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($s['nama']) ?></td>
                            <td><?= $s['jam_masuk'] ? htmlspecialchars($s['jam_masuk']) . ' WIB' : '-' ?></td>
                            <td>
                                <?php if (!$s['status']): ?>
                                    <span class="badge bg-secondary">Belum Absen</span>
                                <?php elseif ($s['status'] == 'hadir'): ?>
                                    <span class="badge bg-success">Hadir</span>
                                <?php elseif ($s['status'] == 'terlambat'): ?>
                                    <span class="badge bg-warning text-dark">Terlambat</span>
                                <?php elseif ($s['status'] == 'sakit'): ?>
                                    <span class="badge bg-secondary">Sakit</span>
                                <?php elseif ($s['status'] == 'izin'): ?>
                                    <span class="badge bg-info text-dark">Izin</span>
                                <?php elseif ($s['status'] == 'alpa'): ?>
                                    <span class="badge bg-danger">Alpa</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($s['keterangan'] ?: '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada data absensi untuk filter ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
