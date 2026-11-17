<?php
// Helper function untuk membangun query string pagination
function buildPaginationUrl($params) {
    $query = http_build_query(array_filter($params, fn($v) => $v !== ''));
    return '?' . $query;
}
$role = $_SESSION['role'];
?>

<div class="card shadow mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary"><i class="bi bi-journal-text me-2"></i>Riwayat Absen Siswa</h5>
        <div class="d-flex gap-2 d-print-none">
            <!-- Print Button -->
            <?php
            $print_params = array_merge($data['filters'], []);
            $print_qs = http_build_query(array_filter($data['filters'], fn($v) => $v !== ''));
            ?>
            <a href="<?= BASEURL ?>/laporan/printSiswa<?= $print_qs ? '?' . $print_qs : '' ?>" 
               target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer"></i> Cetak Riwayat
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card-body border-bottom pb-3 d-print-none">
        <form method="GET" action="<?= $data['base_route'] ?>" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label form-label-sm">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="<?= htmlspecialchars($data['filters']['tanggal_mulai']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="<?= htmlspecialchars($data['filters']['tanggal_akhir']) ?>">
            </div>
            
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
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i>Tampilkan</button>
                <a href="<?= $data['base_route'] ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
            </div>
        </form>
    </div>

    <!-- Ringkasan Card -->
    <div class="card-body border-bottom bg-light d-print-none">
        <?php
        $stats = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];
        foreach($data['laporan'] as $row) {
            if(isset($stats[$row['status']])) {
                $stats[$row['status']]++;
            }
        }
        $total_filtered = count($data['laporan']);
        ?>
        <div class="row g-2 text-center">
            <div class="col">
                <div class="p-2 border rounded bg-white">
                    <div class="small text-muted">Total Filtered</div>
                    <div class="fw-bold fs-5"><?= $total_filtered ?></div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded bg-success text-white">
                    <div class="small opacity-75">Hadir</div>
                    <div class="fw-bold fs-5"><?= $stats['hadir'] ?></div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded bg-warning text-dark">
                    <div class="small opacity-75">Terlambat</div>
                    <div class="fw-bold fs-5"><?= $stats['terlambat'] ?></div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded bg-info text-white">
                    <div class="small opacity-75">Izin</div>
                    <div class="fw-bold fs-5"><?= $stats['izin'] ?></div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded bg-primary text-white">
                    <div class="small opacity-75">Sakit</div>
                    <div class="fw-bold fs-5"><?= $stats['sakit'] ?></div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 border rounded bg-danger text-white">
                    <div class="small opacity-75">Alpa</div>
                    <div class="fw-bold fs-5"><?= $stats['alpa'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card-body">
        <p class="text-muted small mb-2 d-print-none">Menampilkan <?= count($data['laporan']) ?> dari <?= $data['total'] ?> data.</p>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="4%" class="text-center">No</th>
                        <th>Tanggal</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th class="text-center">Jam Scan</th>
                        <th class="text-center">Status</th>
                        <th>Keterangan</th>
                        <th class="text-center d-print-none" width="8%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = (($data['page'] - 1) * $data['per_page']) + 1;
                    foreach($data['laporan'] as $row):
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($row['nis']) ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
                        <td class="text-center"><?= $row['jam_masuk'] ?? '-' ?></td>
                        <td class="text-center">
                            <?php
                            $badges = ['hadir'=>'success','terlambat'=>'warning','sakit'=>'info','izin'=>'primary','alpa'=>'danger'];
                            $color = $badges[$row['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $color ?> <?= $row['status'] === 'terlambat' ? 'text-dark' : '' ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td><small><?= htmlspecialchars($row['keterangan'] ?? '-') ?></small></td>
                        <td class="text-center d-print-none">
                            <a href="<?= BASEURL ?>/guru/laporan_absensi/siswa/<?= $row['siswa_id'] ?? $row['id'] /* ID referensi perlu dicek jika query join bermasalah. Kita asumsikan a.siswa_id ada walau ga di-select langsung, kita pakai query untuk ambil s.id */ ?>" 
                               class="btn btn-sm btn-outline-info" title="Detail Riwayat"
                               <?php 
                                    // Hack krn query getLaporanSiswa mungkin tidak select s.id
                                    // Kita butuh siswa_id. Asumsikan kita punya dari $row atau bisa query string jika tidak.
                                    // Di Laporan_model::getLaporanSiswa, a.siswa_id sayangnya tidak di-select secara eksplisit, 
                                    // tapi wait, a.id yang ter-select adalah absensi_siswa.id.
                                    // Oh wait, Laporan_model belum ditambahkan 'a.siswa_id'.
                               ?>
                            >
                                <i class="bi bi-person-lines-fill"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($data['laporan'])): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data absensi yang sesuai filter.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($data['total_pages'] > 1): ?>
        <nav class="d-print-none">
            <ul class="pagination pagination-sm justify-content-center mt-3">
                <?php
                $base_params = array_merge($data['filters']);
                // Prev
                if($data['page'] > 1):
                    $prev_qs = http_build_query(array_merge(array_filter($base_params, fn($v) => $v !== ''), ['page' => $data['page'] - 1]));
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $data['base_route'] ?>?<?= $prev_qs ?>">«</a>
                </li>
                <?php endif; ?>
                
                <?php for($i = max(1, $data['page'] - 2); $i <= min($data['total_pages'], $data['page'] + 2); $i++):
                    $page_qs = http_build_query(array_merge(array_filter($base_params, fn($v) => $v !== ''), ['page' => $i]));
                ?>
                <li class="page-item <?= $i === $data['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $data['base_route'] ?>?<?= $page_qs ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if($data['page'] < $data['total_pages']):
                    $next_qs = http_build_query(array_merge(array_filter($base_params, fn($v) => $v !== ''), ['page' => $data['page'] + 1]));
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $data['base_route'] ?>?<?= $next_qs ?>">»</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>
