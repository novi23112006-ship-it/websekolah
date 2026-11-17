<div class="row">
    <!-- Profil Siswa & Statistik -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-person-badge me-2"></i>Profil Siswa</h6>
            </div>
            <div class="card-body text-center">
                <i class="bi bi-person-circle text-secondary mb-3" style="font-size: 5rem;"></i>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($data['siswa']['nama']) ?></h5>
                <p class="text-muted mb-3">NIS: <?= htmlspecialchars($data['siswa']['nis']) ?></p>
                <div class="border-top pt-3 text-start">
                    <p class="mb-1"><strong>Kelas:</strong> <?= htmlspecialchars($data['kelas']['nama_kelas'] ?? '-') ?></p>
                    <p class="mb-1"><strong>Jenis Kelamin:</strong> <?= $data['siswa']['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></p>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-bar-chart me-2"></i>Statistik Absensi</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Hadir
                        <span class="badge bg-success rounded-pill"><?= $data['statistik']['hadir'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Terlambat
                        <span class="badge bg-warning text-dark rounded-pill"><?= $data['statistik']['terlambat'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Izin
                        <span class="badge bg-info text-dark rounded-pill"><?= $data['statistik']['izin'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Sakit
                        <span class="badge bg-primary rounded-pill"><?= $data['statistik']['sakit'] ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Alpa
                        <span class="badge bg-danger rounded-pill"><?= $data['statistik']['alpa'] ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Riwayat Absensi -->
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-clock-history me-2"></i>Riwayat Kehadiran</h6>
                <a href="<?= BASEURL ?>/guru/laporan_absensi" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>Tanggal</th>
                                <th class="text-center">Jam Scan</th>
                                <th class="text-center">Status</th>
                                <th>Keterangan</th>
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
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($data['laporan'])): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Siswa belum memiliki riwayat absensi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if($data['total_pages'] > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination pagination-sm justify-content-center">
                        <?php if($data['page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $data['page'] - 1 ?>">«</a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for($i = max(1, $data['page'] - 2); $i <= min($data['total_pages'], $data['page'] + 2); $i++): ?>
                        <li class="page-item <?= $i === $data['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>

                        <?php if($data['page'] < $data['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $data['page'] + 1 ?>">»</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
