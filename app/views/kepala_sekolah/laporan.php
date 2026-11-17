<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 d-print-none">
    <div>
        <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Riwayat Absen Siswa Seluruh Kelas</p>
    </div>
    <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Cetak Laporan
    </button>
</div>

<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold mb-1">RIWAYAT ABSEN SISWA</h3>
    <p class="mb-0">Sekolah SMK | Tanggal Cetak: <?= date('d F Y') ?></p>
    <hr class="my-2">
</div>

<!-- Filter Form -->
<div class="card shadow-sm border-0 mb-4 d-print-none">
    <div class="card-body">
        <form action="<?= BASEURL ?>/kepala_sekolah/laporan" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="<?= htmlspecialchars($data['filters']['tanggal_mulai']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="<?= htmlspecialchars($data['filters']['tanggal_akhir']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Kelas</label>
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($data['kelas_list'] as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $data['filters']['kelas_id'] == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Jurusan</label>
                <select name="jurusan" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    <?php foreach ($data['jurusan_list'] as $j): ?>
                        <option value="<?= htmlspecialchars($j['jurusan']) ?>" <?= $data['filters']['jurusan'] == $j['jurusan'] ? 'selected' : '' ?>><?= htmlspecialchars($j['jurusan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    <option value="hadir" <?= $data['filters']['status'] == 'hadir' ? 'selected' : '' ?>>Hadir</option>
                    <option value="terlambat" <?= $data['filters']['status'] == 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
                    <option value="izin" <?= $data['filters']['status'] == 'izin' ? 'selected' : '' ?>>Izin</option>
                    <option value="sakit" <?= $data['filters']['status'] == 'sakit' ? 'selected' : '' ?>>Sakit</option>
                    <option value="alpa" <?= $data['filters']['status'] == 'alpa' ? 'selected' : '' ?>>Alpa</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-filter me-1"></i> Filter Laporan</button>
                <a href="<?= BASEURL ?>/kepala_sekolah/laporan" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Laporan -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-file-earmark-text me-1"></i> Data Log Absensi Siswa</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="ps-3">No</th>
                        <th>Tanggal</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jam Masuk</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['laporan'])): ?>
                        <?php 
                        $no = ($data['pagination']['page'] - 1) * $data['pagination']['per_page'] + 1; 
                        foreach ($data['laporan'] as $row): 
                        ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td class="fw-semibold"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($row['nis']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['nama']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['nama_kelas'] ?: '-') ?></span></td>
                            <td><?= $row['jam_masuk'] ? htmlspecialchars($row['jam_masuk']) . ' WIB' : '-' ?></td>
                            <td>
                                <?php
                                    $badgeMap = [
                                        'hadir'     => 'bg-success',
                                        'terlambat' => 'bg-warning text-dark',
                                        'izin'      => 'bg-info text-dark',
                                        'sakit'     => 'bg-secondary',
                                        'alpa'      => 'bg-danger',
                                    ];
                                    $bc = $badgeMap[$row['status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $bc ?>"><?= ucfirst($row['status']) ?></span>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($row['keterangan'] ?: '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada data laporan yang sesuai filter.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Pagination (d-print-none) -->
    <?php if ($data['pagination']['total_pages'] > 1): ?>
    <div class="card-footer bg-white py-3 d-print-none">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <?php
                    $p = $data['pagination'];
                    $queryParams = $_GET;
                    
                    if ($p['page'] > 1):
                        $queryParams['page'] = $p['page'] - 1;
                ?>
                    <li class="page-page-item"><a class="page-link" href="?<?= http_build_query($queryParams) ?>">Sebelumnya</a></li>
                <?php endif; ?>

                <?php for($i = 1; $i <= $p['total_pages']; $i++): 
                    $queryParams['page'] = $i;
                ?>
                    <li class="page-item <?= $i == $p['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query($queryParams) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($p['page'] < $p['total_pages']): 
                    $queryParams['page'] = $p['page'] + 1;
                ?>
                    <li class="page-item"><a class="page-link" href="?<?= http_build_query($queryParams) ?>">Selanjutnya</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
