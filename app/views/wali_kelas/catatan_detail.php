<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i> Informasi lengkap catatan siswa di kelas</p>
    </div>
    <a href="<?= BASEURL ?>/wali-kelas/catatan-siswa" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Catatan
    </a>
</div>

<?php Flasher::flash(); ?>

<?php $c = $data['catatan']; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-journal-text me-1"></i> Catatan #<?= $c['id'] ?>
                </h6>
                <div>
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
                    <span class="badge <?= $badgeStyle ?> fs-6"><?= htmlspecialchars($c['kategori']) ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="text-muted small fw-semibold text-uppercase d-block mb-1">Nama Siswa</label>
                    <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($c['nama_siswa']) ?></h5>
                    <span class="text-muted small">NIS: <?= htmlspecialchars($c['nis']) ?> | Kelas: <?= htmlspecialchars($c['nama_kelas']) ?></span>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small fw-semibold text-uppercase d-block mb-1">Tanggal Catatan</label>
                        <div class="fw-semibold text-dark"><i class="bi bi-calendar3 me-1"></i> <?= date('d F Y', strtotime($c['tanggal'])) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small fw-semibold text-uppercase d-block mb-1">Wali Kelas / Pencatat</label>
                        <div class="fw-semibold text-dark"><i class="bi bi-person-badge me-1"></i> <?= htmlspecialchars($c['nama_wali'] ?: 'Wali Kelas') ?></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small fw-semibold text-uppercase d-block mb-2">Isi Catatan</label>
                    <div class="p-3 bg-light rounded border">
                        <?= nl2br(htmlspecialchars($c['catatan'])) ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center text-muted small border-top pt-3">
                    <div>Dibuat: <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></div>
                    <div>Diperbarui: <?= date('d/m/Y H:i', strtotime($c['updated_at'])) ?></div>
                </div>
            </div>
            <div class="card-footer bg-white py-3 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditDetail">
                    <i class="bi bi-pencil me-1"></i> Edit Catatan
                </button>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapusDetail">
                    <i class="bi bi-trash me-1"></i> Hapus Catatan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Detail -->
<div class="modal fade" id="modalEditDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= BASEURL ?>/wali-kelas/catatan-siswa/edit/<?= $c['id'] ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <input type="hidden" name="siswa_id" value="<?= $c['siswa_id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Catatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Siswa</label>
                        <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($c['nama_siswa']) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kategori</label>
                        <select name="kategori" class="form-select form-select-sm" required>
                            <?php 
                            $kategoris = ['Pembinaan','Pelanggaran','Prestasi','Izin','Akademik','Perilaku','Lainnya'];
                            foreach($kategoris as $kat):
                            ?>
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

<!-- Modal Hapus Detail -->
<div class="modal fade" id="modalHapusDetail" tabindex="-1" aria-hidden="true">
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
                    <p class="mb-0">Apakah Anda yakin ingin menghapus catatan ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
