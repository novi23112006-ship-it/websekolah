<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Data seluruh siswa terdaftar di sekolah (Read-Only)</p>
    </div>
</div>

<!-- Filter Form -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="<?= BASEURL ?>/kepala_sekolah/siswa" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Pencarian</label>
                <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Cari NIS / Nama Siswa..." value="<?= htmlspecialchars($data['keyword'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Kelas</label>
                <select name="kelas_id" class="form-select form-select-sm">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($data['kelas_list'] as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $data['kelas_id'] == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search me-1"></i> Cari</button>
                <a href="<?= BASEURL ?>/kepala_sekolah/siswa" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Table Data Siswa -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-primary"><i class="bi bi-people me-1"></i> Data Siswa Sekolah</h6>
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
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th>L/P</th>
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
                            <td class="fw-bold"><?= htmlspecialchars($s['nama']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s['nama_kelas'] ?: '-') ?></span></td>
                            <td class="small text-muted"><?= htmlspecialchars($s['jurusan'] ?: '-') ?></td>
                            <td class="text-center"><?= htmlspecialchars($s['jenis_kelamin']) ?></td>
                            <td>
                                <span class="badge <?= $s['status'] == 'active' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= ucfirst($s['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada data siswa.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
