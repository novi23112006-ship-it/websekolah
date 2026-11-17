<!-- Flash Alert -->
<div class="mb-3">
    <?php Flasher::flash(); ?>
</div>

<!-- Header Page & Action Buttons -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-book-half text-primary me-2"></i>Manajemen Mata Pelajaran</h4>
        <p class="text-muted mb-0 small">Kelola kurikulum, daftar mata pelajaran SMK, kelompok muatan, dan alokasi jam mengajar</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahMapel">
            <i class="bi bi-plus-circle me-1"></i> Tambah Mata Pelajaran
        </button>
    </div>
</div>

<!-- Summary Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Total Mata Pelajaran</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1"><?= $data['stats']['total'] ?></h3>
                </div>
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-journal-bookmark-fill fs-5"></i>
                </div>
            </div>
            <div class="mt-2 text-muted" style="font-size: 12px;">
                <span class="text-success fw-bold"><i class="bi bi-check2-circle"></i> <?= $data['stats']['aktif'] ?> Aktif</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Muatan Nasional (A)</span>
                    <h3 class="fw-bold text-info mb-0 mt-1"><?= $data['stats']['nasional'] ?></h3>
                </div>
                <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-flag-fill fs-5"></i>
                </div>
            </div>
            <div class="mt-2 text-muted" style="font-size: 12px;">Mata Pelajaran Wajib Umum</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Peminatan Kejuruan (C)</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1"><?= $data['stats']['kejuruan'] ?></h3>
                </div>
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-cpu-fill fs-5"></i>
                </div>
            </div>
            <div class="mt-2 text-muted" style="font-size: 12px;">TKJ, TKR, TSM & Vokasi</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Muatan Lokal & Lainnya</span>
                    <h3 class="fw-bold text-success mb-0 mt-1"><?= $data['stats']['muatan_lokal'] + $data['stats']['kewilayahan'] ?></h3>
                </div>
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-geo-alt-fill fs-5"></i>
                </div>
            </div>
            <div class="mt-2 text-muted" style="font-size: 12px;">Kewilayahan & Khas Sekolah</div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASEURL; ?>/admin_mapel" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Kelompok Muatan</label>
                <select name="kelompok" class="form-select form-select-sm">
                    <option value="">Semua Kelompok</option>
                    <option value="Muatan Nasional" <?= ($data['filters']['kelompok'] == 'Muatan Nasional') ? 'selected' : '' ?>>Muatan Nasional</option>
                    <option value="Muatan Kewilayahan" <?= ($data['filters']['kelompok'] == 'Muatan Kewilayahan') ? 'selected' : '' ?>>Muatan Kewilayahan</option>
                    <option value="Muatan Peminatan Kejuruan" <?= ($data['filters']['kelompok'] == 'Muatan Peminatan Kejuruan') ? 'selected' : '' ?>>Peminatan Kejuruan</option>
                    <option value="Muatan Lokal" <?= ($data['filters']['kelompok'] == 'Muatan Lokal') ? 'selected' : '' ?>>Muatan Lokal</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Tingkat Kelas</label>
                <select name="tingkat" class="form-select form-select-sm">
                    <option value="">Semua Tingkat</option>
                    <option value="X" <?= ($data['filters']['tingkat'] == 'X') ? 'selected' : '' ?>>Kelas X</option>
                    <option value="XI" <?= ($data['filters']['tingkat'] == 'XI') ? 'selected' : '' ?>>Kelas XI</option>
                    <option value="XII" <?= ($data['filters']['tingkat'] == 'XII') ? 'selected' : '' ?>>Kelas XII</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1 fw-semibold">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="active" <?= ($data['filters']['status'] == 'active') ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= ($data['filters']['status'] == 'inactive') ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1 fw-semibold">Pencarian Mapel</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="keyword" class="form-control" placeholder="Kode atau nama mapel..." value="<?= htmlspecialchars($data['filters']['keyword']) ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 flex-grow-1">
                    <i class="bi bi-filter"></i> Filter
                </button>
                <a href="<?= BASEURL; ?>/admin_mapel" class="btn btn-sm btn-outline-secondary rounded-pill px-2" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-columns-reverse me-2 text-primary"></i>Daftar Mata Pelajaran (<?= count($data['mapel_list']) ?> Data)</h6>
        <span class="badge bg-light text-muted border px-2 py-1">Kurikulum SMK Merdeka / 2013</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th width="5%" class="text-center">No</th>
                    <th width="12%">Kode</th>
                    <th width="33%">Nama Mata Pelajaran</th>
                    <th width="20%">Kelompok Muatan</th>
                    <th width="10%">Tingkat</th>
                    <th width="10%" class="text-center">Alokasi Jam</th>
                    <th width="10%" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['mapel_list'])): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada data mata pelajaran yang sesuai filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($data['mapel_list'] as $m): ?>
                        <tr>
                            <td class="text-center text-muted small"><?= $no++ ?></td>
                            <td>
                                <code class="fw-bold bg-light px-2 py-1 rounded text-primary fs-6"><?= htmlspecialchars($m['kode_mapel']) ?></code>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($m['nama_mapel']) ?></div>
                                <?php if ($m['status'] === 'inactive'): ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 10px;">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $badgeKelompok = [
                                        'Muatan Nasional' => 'bg-info-subtle text-info border-info-subtle',
                                        'Muatan Kewilayahan' => 'bg-primary-subtle text-primary border-primary-subtle',
                                        'Muatan Peminatan Kejuruan' => 'bg-warning-subtle text-warning border-warning-subtle',
                                        'Muatan Lokal' => 'bg-success-subtle text-success border-success-subtle'
                                    ];
                                    $kClass = $badgeKelompok[$m['kelompok']] ?? 'bg-light text-dark';
                                ?>
                                <span class="badge <?= $kClass ?> border px-2 py-1" style="font-size: 11px;">
                                    <?= htmlspecialchars($m['kelompok']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($m['tingkat']) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-dark"><?= (int)$m['jam_per_minggu'] ?></span> <span class="text-muted small">JP/mgg</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btnEditMapel" data-id="<?= $m['id'] ?>" title="Edit Mapel">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btnHapusMapel" data-id="<?= $m['id'] ?>" data-nama="<?= htmlspecialchars($m['nama_mapel']) ?>" title="Hapus Mapel">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Mata Pelajaran -->
<div class="modal fade" id="modalTambahMapel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Tambah Mata Pelajaran Baru</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASEURL; ?>/admin_mapel/tambah" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Kode Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="kode_mapel" class="form-control" placeholder="Contoh: MTK, BIN, AIJ" required maxlength="20" style="text-transform: uppercase;">
                        <div class="form-text text-muted" style="font-size: 11px;">Singkatan unik kode mata pelajaran (maks 20 karakter).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mapel" class="form-control" placeholder="Contoh: Administrasi Infrastruktur Jaringan" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-muted">Kelompok Muatan</label>
                            <select name="kelompok" class="form-select" required>
                                <option value="Muatan Nasional">Muatan Nasional (A)</option>
                                <option value="Muatan Kewilayahan">Muatan Kewilayahan (B)</option>
                                <option value="Muatan Peminatan Kejuruan">Muatan Peminatan Kejuruan (C)</option>
                                <option value="Muatan Lokal">Muatan Lokal</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted">Tingkat Kelas</label>
                            <select name="tingkat" class="form-select" required>
                                <option value="Semua Tingkat">Semua Tingkat</option>
                                <option value="X">Kelas X</option>
                                <option value="XI">Kelas XI</option>
                                <option value="XII">Kelas XII</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Jam per Minggu (JP)</label>
                            <input type="number" name="jam_per_minggu" class="form-control" value="2" min="1" max="20" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Mapel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ubah Mata Pelajaran -->
<div class="modal fade" id="modalUbahMapel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-warning text-dark border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-1"></i> Ubah Mata Pelajaran</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASEURL; ?>/admin_mapel/ubah" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Kode Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="kode_mapel" id="edit_kode_mapel" class="form-control" required maxlength="20" style="text-transform: uppercase;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mapel" id="edit_nama_mapel" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-muted">Kelompok Muatan</label>
                            <select name="kelompok" id="edit_kelompok" class="form-select" required>
                                <option value="Muatan Nasional">Muatan Nasional (A)</option>
                                <option value="Muatan Kewilayahan">Muatan Kewilayahan (B)</option>
                                <option value="Muatan Peminatan Kejuruan">Muatan Peminatan Kejuruan (C)</option>
                                <option value="Muatan Lokal">Muatan Lokal</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-muted">Tingkat Kelas</label>
                            <select name="tingkat" id="edit_tingkat" class="form-select" required>
                                <option value="Semua Tingkat">Semua Tingkat</option>
                                <option value="X">Kelas X</option>
                                <option value="XI">Kelas XI</option>
                                <option value="XII">Kelas XII</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Jam per Minggu (JP)</label>
                            <input type="number" name="jam_per_minggu" id="edit_jam_per_minggu" class="form-control" min="1" max="20" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Perbarui Mapel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Hapus Hidden -->
<form id="formHapusMapel" action="<?= BASEURL; ?>/admin_mapel/hapus" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
    <input type="hidden" name="id" id="hapus_id">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Mapel Button Handler
    document.querySelectorAll('.btnEditMapel').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            try {
                const res = await fetch('<?= BASEURL; ?>/admin_mapel/get_detail/' + id);
                const data = await res.json();
                if (data && data.id) {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_kode_mapel').value = data.kode_mapel;
                    document.getElementById('edit_nama_mapel').value = data.nama_mapel;
                    document.getElementById('edit_kelompok').value = data.kelompok;
                    document.getElementById('edit_tingkat').value = data.tingkat;
                    document.getElementById('edit_jam_per_minggu').value = data.jam_per_minggu;
                    document.getElementById('edit_status').value = data.status;

                    const modal = new bootstrap.Modal(document.getElementById('modalUbahMapel'));
                    modal.show();
                }
            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Memuat Data',
                    text: 'Tidak dapat mengambil data mata pelajaran.'
                });
            }
        });
    });

    // Hapus Mapel Handler
    document.querySelectorAll('.btnHapusMapel').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nama = this.getAttribute('data-nama');

            Swal.fire({
                title: 'Hapus Mata Pelajaran?',
                text: `Apakah Anda yakin ingin menghapus '${nama}'? Data yang terhubung dengan jadwal mengajar tidak dapat dihapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('hapus_id').value = id;
                    document.getElementById('formHapusMapel').submit();
                }
            });
        });
    });
});
</script>
