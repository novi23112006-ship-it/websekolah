<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Data guru / tenaga pendidik sekolah (Read-Only)</p>
    </div>
</div>

<!-- Filter Form -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="<?= BASEURL ?>/kepala_sekolah/guru" method="GET" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label small fw-semibold">Pencarian</label>
                <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Cari NIP / Nama Guru / Mata Pelajaran..." value="<?= htmlspecialchars($data['keyword'] ?? '') ?>">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search me-1"></i> Cari</button>
                <a href="<?= BASEURL ?>/kepala_sekolah/guru" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Table Data Guru -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-success"><i class="bi bi-person-badge me-1"></i> Data Guru Sekolah</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="ps-3">No</th>
                        <th>NIP</th>
                        <th>Nama Guru</th>
                        <th>Username</th>
                        <th>Mata Pelajaran</th>
                        <th>No HP</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['guru'])): ?>
                        <?php $no = 1; foreach ($data['guru'] as $g): ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($g['nip'] ?: '-') ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($g['nama']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($g['username'] ?: '-') ?></span></td>
                            <td><?= htmlspecialchars($g['mata_pelajaran'] ?: '-') ?></td>
                            <td><?= htmlspecialchars($g['no_hp'] ?: '-') ?></td>
                            <td>
                                <span class="badge <?= $g['status'] == 'active' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= ucfirst($g['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada data guru.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
