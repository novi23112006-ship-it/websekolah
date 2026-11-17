<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <?php if ($data['kelas']): ?>
            <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Kelas: <span class="fw-semibold text-primary"><?= htmlspecialchars($data['kelas']['nama_kelas']) ?></span> | Pembinaan, pelanggaran, dan prestasi siswa</p>
        <?php else: ?>
            <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Pencatatan perkembangan, pembinaan, pelanggaran, dan prestasi siswa</p>
        <?php endif; ?>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahCatatan">
        <i class="bi bi-plus-circle me-1"></i> Tambah Catatan Siswa
    </button>
</div>

<?php Flasher::flash(); ?>

<!-- Filter Siswa -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="<?= BASEURL ?>/wali_kelas/catatan" method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Pilih Siswa</label>
                <select name="siswa_id" class="form-select form-select-sm">
                    <option value="">-- Semua Siswa di Kelas --</option>
                    <?php foreach ($data['siswa_list'] as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (isset($_GET['siswa_id']) && $_GET['siswa_id'] == $s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nis']) ?> - <?= htmlspecialchars($s['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-filter me-1"></i> Filter</button>
                <a href="<?= BASEURL ?>/wali_kelas/catatan" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Catatan Siswa -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 font-weight-bold text-success"><i class="bi bi-journal-text me-1"></i> Riwayat Catatan Siswa</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="ps-3">No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kategori</th>
                        <th>Tanggal</th>
                        <th>Catatan</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['catatan'])): ?>
                        <?php $no = 1; foreach ($data['catatan'] as $c): ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($c['nis']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($c['nama_siswa']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($c['kategori']) ?></span></td>
                            <td class="small text-muted"><?= date('d/m/Y', strtotime($c['tanggal'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($c['catatan'])) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($c['nama_wali'] ?: 'Wali Kelas') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada catatan untuk siswa di kelas ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Catatan -->
<div class="modal fade" id="modalTambahCatatan" tabindex="-1" aria-labelledby="modalTambahCatatanLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= BASEURL ?>/wali_kelas/add_catatan" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTambahCatatanLabel"><i class="bi bi-plus-circle me-2"></i>Tambah Catatan Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="siswa_id" class="form-label small fw-semibold">Pilih Siswa</label>
                        <select name="siswa_id" id="siswa_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($data['siswa_list'] as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nis']) ?> - <?= htmlspecialchars($s['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="kategori" class="form-label small fw-semibold">Kategori</label>
                        <select name="kategori" id="kategori" class="form-select form-select-sm" required>
                            <option value="Pembinaan">Pembinaan</option>
                            <option value="Pelanggaran">Pelanggaran</option>
                            <option value="Prestasi">Prestasi</option>
                            <option value="Izin">Izin</option>
                            <option value="Akademik">Akademik</option>
                            <option value="Perilaku">Perilaku</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal" class="form-label small fw-semibold">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="catatan" class="form-label small fw-semibold">Isi Catatan</label>
                        <textarea name="catatan" id="catatan" class="form-control form-control-sm" rows="4" placeholder="Tuliskan catatan singkat mengenai siswa ini..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
