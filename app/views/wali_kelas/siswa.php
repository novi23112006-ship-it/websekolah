<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <?php if ($data['kelas']): ?>
            <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Kelas: <span class="fw-semibold text-primary"><?= htmlspecialchars($data['kelas']['nama_kelas']) ?></span> (<?= count($data['siswa']) ?> siswa terdaftar)</p>
        <?php else: ?>
            <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Daftar siswa yang berada di bawah bimbingan Anda</p>
        <?php endif; ?>
    </div>
</div>

<?php Flasher::flash(); ?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-people me-1"></i> Daftar Siswa</h6>
        <form action="<?= BASEURL ?>/wali_kelas/siswa" method="GET" class="d-flex gap-2">
            <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Cari NIS / Nama..." value="<?= htmlspecialchars($data['keyword'] ?? '') ?>">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
            <?php if (!empty($data['keyword'])): ?>
                <a href="<?= BASEURL ?>/wali_kelas/siswa" class="btn btn-sm btn-outline-secondary">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="ps-3">No</th>
                        <th>NIS</th>
                        <th>NISN</th>
                        <th>Nama Siswa</th>
                        <th>Jenis Kelamin</th>
                        <th>No HP</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['siswa'])): ?>
                        <?php $no = 1; foreach ($data['siswa'] as $s): ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($s['nis']) ?></td>
                            <td><?= htmlspecialchars($s['nisn'] ?: '-') ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($s['nama']) ?></td>
                            <td><?= $s['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                            <td><?= htmlspecialchars($s['no_hp'] ?: '-') ?></td>
                            <td>
                                <span class="badge <?= $s['status'] == 'active' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= ucfirst($s['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                Tidak ada data siswa yang ditemukan untuk kelas ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
