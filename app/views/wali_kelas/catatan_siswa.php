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

<!-- Filter Siswa & Kategori -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="<?= BASEURL ?>/wali-kelas/catatan-siswa" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold">Pilih Siswa</label>
                <select name="siswa_id" class="form-select form-select-sm">
                    <option value="">-- Semua Siswa di Kelas --</option>
                    <?php foreach ($data['siswa_list'] as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (isset($data['siswa_id']) && $data['siswa_id'] == $s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nis']) ?> - <?= htmlspecialchars($s['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Pilih Kategori</label>
                <select name="kategori" class="form-select form-select-sm">
                    <option value="">-- Semua Kategori --</option>
                    <?php 
                    $kategoris = ['Pembinaan','Pelanggaran','Prestasi','Izin','Akademik','Perilaku','Lainnya'];
                    foreach($kategoris as $kat):
                    ?>
                        <option value="<?= $kat ?>" <?= (isset($data['kategori']) && $data['kategori'] == $kat) ? 'selected' : '' ?>><?= $kat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                <a href="<?= BASEURL ?>/wali-kelas/catatan-siswa" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>Catatan Singkat</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['catatan'])): ?>
                        <?php $no = 1; foreach ($data['catatan'] as $c): ?>
                        <tr>
                            <td class="ps-3"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($c['nis']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($c['nama_siswa']) ?></td>
                            <td>
                                <?php
                                    $kategoriBadge = [
                                        'Pembinaan'   => 'bg-warning text-dark',
                                        'Pelanggaran' => 'bg-danger',
                                        'Prestasi'    => 'bg-success',
                                        'Izin'        => 'bg-info text-dark',
                                        'Akademik'    => 'bg-primary',
                                        'Perilaku'    => 'bg-dark',
                                        'Lainnya'     => 'bg-secondary'
                                    ];
                                    $badgeStyle = $kategoriBadge[$c['kategori']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $badgeStyle ?>"><?= htmlspecialchars($c['kategori']) ?></span>
                            </td>
                            <td class="small text-muted"><?= date('d/m/Y', strtotime($c['tanggal'])) ?></td>
                            <td class="small text-truncate" style="max-width: 220px;" title="<?= htmlspecialchars($c['catatan']) ?>">
                                <?= htmlspecialchars($c['catatan']) ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASEURL ?>/wali-kelas/catatan-siswa/<?= $c['id'] ?>" class="btn btn-outline-info" title="Detail">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditCatatan<?= $c['id'] ?>" title="Edit">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalHapusCatatan<?= $c['id'] ?>" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Edit Catatan -->
                        <div class="modal fade" id="modalEditCatatan<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="<?= BASEURL ?>/wali-kelas/catatan-siswa/edit/<?= $c['id'] ?>" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Catatan Siswa</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Pilih Siswa</label>
                                                <select name="siswa_id" class="form-select form-select-sm" required>
                                                    <?php foreach ($data['siswa_list'] as $s): ?>
                                                        <option value="<?= $s['id'] ?>" <?= $s['id'] == $c['siswa_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($s['nis']) ?> - <?= htmlspecialchars($s['nama']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Kategori</label>
                                                <select name="kategori" class="form-select form-select-sm" required>
                                                    <?php foreach($kategoris as $kat): ?>
                                                        <option value="<?= $kat ?>" <?= $kat == $c['kategori'] ? 'selected' : '' ?>><?= $kat ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Tanggal</label>
                                                <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= htmlspecialchars($c['tanggal']) ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Isi Catatan</label>
                                                <textarea name="catatan" class="form-control form-control-sm" rows="4" required><?= htmlspecialchars($c['catatan']) ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-sm btn-warning">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Hapus Catatan -->
                        <div class="modal fade" id="modalHapusCatatan<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="<?= BASEURL ?>/wali-kelas/catatan-siswa/delete/<?= $c['id'] ?>" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="mb-0">Apakah Anda yakin ingin menghapus catatan untuk siswa <strong><?= htmlspecialchars($c['nama_siswa']) ?></strong> (Kategori: <?= htmlspecialchars($c['kategori']) ?>)?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

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
            <form action="<?= BASEURL ?>/wali-kelas/catatan-siswa/add" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTambahCatatanLabel"><i class="bi bi-plus-circle me-2"></i>Tambah Catatan Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="siswa_id" class="form-label small fw-semibold">Pilih Siswa <span class="text-danger">*</span></label>
                        <select name="siswa_id" id="siswa_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($data['siswa_list'] as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nis']) ?> - <?= htmlspecialchars($s['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="kategori" class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" id="kategori" class="form-select form-select-sm" required>
                            <?php foreach($kategoris as $kat): ?>
                                <option value="<?= $kat ?>"><?= $kat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal" class="form-label small fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="catatan" class="form-label small fw-semibold">Isi Catatan <span class="text-danger">*</span></label>
                        <textarea name="catatan" id="catatan" class="form-control form-control-sm" rows="4" placeholder="Tuliskan catatan detail mengenai siswa ini..." required></textarea>
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
