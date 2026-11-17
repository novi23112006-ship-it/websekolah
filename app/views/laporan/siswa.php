<?php
// Helper function untuk membangun query string pagination
function buildPaginationUrl($params) {
    $query = http_build_query(array_filter($params, fn($v) => $v !== ''));
    return '?' . $query;
}
$role = $data['role_context']['role'] ?? $_SESSION['role'];
?>

<?php if($role === 'admin'): ?>
<!-- Navigasi Modul Terpusat: Rekap Absensi Guru dan Siswa -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1 text-primary"><i class="bi bi-file-earmark-bar-graph me-2"></i>Rekap Absensi Guru dan Siswa</h5>
            <p class="text-muted small mb-0">Pusat rekapitulasi data kehadiran dan riwayat absensi terintegrasi untuk siswa dan guru</p>
        </div>
        <div class="btn-group p-1 bg-light rounded-pill border shadow-sm" role="group">
            <a href="<?= BASEURL; ?>/laporan/siswa" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                <i class="bi bi-people-fill me-1"></i> Rekap Absen Siswa
            </a>
            <a href="<?= BASEURL; ?>/laporan/guru" class="btn btn-sm btn-light text-muted rounded-pill px-3 fw-semibold">
                <i class="bi bi-person-badge-fill me-1"></i> Rekap Absen Guru
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary"><i class="bi bi-clock-history me-2"></i>Riwayat Absen Siswa</h5>
        <?php if ($role !== 'siswa'): ?>
        <div class="d-flex gap-2">
            <!-- Print Button -->
            <?php
            $print_params = array_merge($data['filters'], []);
            $print_qs = http_build_query(array_filter($data['filters'], fn($v) => $v !== ''));
            ?>
            <a href="<?= BASEURL ?>/laporan/printSiswa<?= $print_qs ? '?' . $print_qs : '' ?>" 
               target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer"></i> Print
            </a>
            <!-- Export Excel -->
            <?php
            $export_params = array_merge($data['filters'], ['export' => 'excel']);
            $export_qs = http_build_query(array_filter($export_params, fn($v) => $v !== ''));
            ?>
            <a href="<?= BASEURL ?>/laporan/siswa?<?= $export_qs ?>" class="btn btn-sm btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Filter Form -->
    <div class="card-body border-bottom pb-3">
        <form method="GET" action="<?= BASEURL ?>/laporan/siswa" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label form-label-sm">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="<?= htmlspecialchars($data['filters']['tanggal_mulai']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="<?= htmlspecialchars($data['filters']['tanggal_akhir']) ?>">
            </div>
            <?php if(in_array($role, ['admin', 'kepala_sekolah'])): ?>
            <div class="col-md-2">
                <label class="form-label form-label-sm">Kelas</label>
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">Semua Kelas</option>
                    <?php foreach($data['kelas'] as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $data['filters']['kelas_id'] == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <?php if(in_array($role, ['admin', 'kepala_sekolah', 'wali_kelas'])): ?>
            <div class="col-md-2">
                <label class="form-label form-label-sm">Siswa</label>
                <select name="siswa_id" class="form-select form-select-sm">
                    <option value="">Semua Siswa</option>
                    <?php foreach($data['siswa_list'] as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $data['filters']['siswa_id'] == $s['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nama']) ?> (<?= $s['nis'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-2">
                <label class="form-label form-label-sm">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <?php foreach(['hadir', 'terlambat', 'sakit', 'izin', 'alpa'] as $st): ?>
                        <option value="<?= $st ?>" <?= $data['filters']['status'] == $st ? 'selected' : '' ?>>
                            <?= ucfirst($st) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="<?= BASEURL ?>/laporan/siswa" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card-body">
        <p class="text-muted small mb-2">Menampilkan <?= count($data['laporan']) ?> dari <?= $data['total'] ?> data.</p>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th width="4%">No</th>
                        <th>Tanggal</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = (($data['page'] - 1) * $data['per_page']) + 1;
                    foreach($data['laporan'] as $row):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($row['nis']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
                        <td><?= $row['jam_masuk'] ?? '-' ?></td>
                        <td><?= !empty($row['jam_pulang']) ? $row['jam_pulang'] : '-' ?></td>
                        <td>
                            <?php
                            $badges = ['hadir'=>'success','terlambat'=>'warning','sakit'=>'info','izin'=>'primary','alpa'=>'danger'];
                            $color = $badges[$row['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $color ?> <?= $row['status'] === 'terlambat' ? 'text-dark' : '' ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($data['laporan'])): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data yang sesuai filter.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($data['total_pages'] > 1): ?>
        <nav>
            <ul class="pagination pagination-sm justify-content-center mt-3">
                <?php
                $base_params = array_merge($data['filters']);
                // Prev
                if($data['page'] > 1):
                    $prev_qs = http_build_query(array_merge(array_filter($base_params, fn($v) => $v !== ''), ['page' => $data['page'] - 1]));
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= BASEURL ?>/laporan/siswa?<?= $prev_qs ?>">«</a>
                </li>
                <?php endif; ?>
                
                <?php for($i = max(1, $data['page'] - 2); $i <= min($data['total_pages'], $data['page'] + 2); $i++):
                    $page_qs = http_build_query(array_merge(array_filter($base_params, fn($v) => $v !== ''), ['page' => $i]));
                ?>
                <li class="page-item <?= $i === $data['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= BASEURL ?>/laporan/siswa?<?= $page_qs ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if($data['page'] < $data['total_pages']):
                    $next_qs = http_build_query(array_merge(array_filter($base_params, fn($v) => $v !== ''), ['page' => $data['page'] + 1]));
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= BASEURL ?>/laporan/siswa?<?= $next_qs ?>">»</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>
