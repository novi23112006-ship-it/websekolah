<div class="row">
    <div class="col-12">
        <?php Flasher::flash(); ?>

        <!-- ═══ KPI SUMMARY ROW ═════════════════════════════════════════════════ -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 border-start border-primary border-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Jadwal</div>
                            <div class="h3 mb-0 fw-bold text-dark"><?= $data['total_jadwal']; ?></div>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                            <i class="bi bi-calendar-week fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 border-start border-info border-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs fw-bold text-info text-uppercase mb-1">Jadwal Siswa</div>
                            <div class="h3 mb-0 fw-bold text-dark"><?= $data['total_siswa']; ?></div>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 border-start border-success border-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">Jadwal Guru</div>
                            <div class="h3 mb-0 fw-bold text-dark"><?= $data['total_guru']; ?></div>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                            <i class="bi bi-person-badge-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-3 h-100 p-3 border-start border-warning border-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">Jadwal Aktif</div>
                            <div class="h3 mb-0 fw-bold text-dark"><?= $data['total_aktif']; ?></div>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                            <i class="bi bi-check-circle-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ MAIN CARD: TABEL & FILTER JADWAL ═════════════════════════════════ -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="m-0 fw-bold text-dark">
                            <i class="bi bi-clock-history text-primary me-2"></i>Daftar Jadwal Absensi
                        </h5>
                        <p class="text-muted small mb-0">Atur jam mulai scan, batas hadir tepat waktu, dan jam tutup absensi siswa & dewan guru.</p>
                    </div>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#formJadwalModal" onclick="setupModalTambah()">
                        <i class="bi bi-plus-circle me-1"></i> Buat Jadwal Baru
                    </button>
                </div>
            </div>

            <div class="card-body pt-0">
                <!-- Filter Pills: Tipe Pengguna -->
                <?php 
                    $currTipe = $data['filters']['tipe_pengguna'];
                    $currHari = $data['filters']['hari'];
                    $currStatus = $data['filters']['is_active'];
                    $currKeyword = $data['filters']['keyword'];
                ?>
                <div class="d-flex flex-wrap gap-2 mb-3 pb-2 border-bottom">
                    <a href="<?= BASEURL; ?>/admin_jadwal?tipe=&hari=<?= urlencode($currHari) ?>&status=<?= urlencode($currStatus) ?>&keyword=<?= urlencode($currKeyword) ?>" 
                       class="btn btn-sm rounded-pill px-3 <?= empty($currTipe) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                        Semua Kategori
                    </a>
                    <a href="<?= BASEURL; ?>/admin_jadwal?tipe=siswa&hari=<?= urlencode($currHari) ?>&status=<?= urlencode($currStatus) ?>&keyword=<?= urlencode($currKeyword) ?>" 
                       class="btn btn-sm rounded-pill px-3 <?= ($currTipe === 'siswa') ? 'btn-info text-white' : 'btn-outline-info' ?>">
                        <i class="bi bi-mortarboard me-1"></i> Jadwal Siswa
                    </a>
                    <a href="<?= BASEURL; ?>/admin_jadwal?tipe=guru&hari=<?= urlencode($currHari) ?>&status=<?= urlencode($currStatus) ?>&keyword=<?= urlencode($currKeyword) ?>" 
                       class="btn btn-sm rounded-pill px-3 <?= ($currTipe === 'guru') ? 'btn-success text-white' : 'btn-outline-success' ?>">
                        <i class="bi bi-person-workspace me-1"></i> Jadwal Guru
                    </a>
                </div>

                <!-- Filter Bar Form -->
                <form action="<?= BASEURL; ?>/admin_jadwal" method="GET" class="row g-2 mb-4 align-items-end">
                    <input type="hidden" name="tipe" value="<?= htmlspecialchars($currTipe); ?>">
                    
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Hari Berlaku</label>
                        <select name="hari" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Hari --</option>
                            <?php foreach(['Senin - Jumat', 'Senin - Sabtu', 'Semua Hari', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $h): ?>
                                <option value="<?= $h ?>" <?= ($currHari === $h) ? 'selected' : '' ?>><?= $h ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Status</label>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="1" <?= ($currStatus === '1') ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= ($currStatus === '0') ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Pencarian</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" name="keyword" class="form-control" placeholder="Cari nama jadwal..." value="<?= htmlspecialchars($currKeyword); ?>">
                        </div>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-dark w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                        <a href="<?= BASEURL; ?>/admin_jadwal" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="jadwalTable">
                        <thead class="table-light">
                            <tr>
                                <th width="4%" class="text-center">No</th>
                                <th width="22%">Nama Jadwal & Pengguna</th>
                                <th width="12%">Hari</th>
                                <th width="12%">Jam Mulai</th>
                                <th width="13%">Batas Hadir</th>
                                <th width="12%">Jam Tutup</th>
                                <th width="10%" class="text-center">Status</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($data['jadwal'] as $j): 
                                $isSiswa = ($j['tipe_pengguna'] === 'siswa');
                                $badgeTipe = $isSiswa ? 'bg-info bg-opacity-10 text-info border border-info border-opacity-25' : 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                                $iconTipe  = $isSiswa ? 'bi-mortarboard' : 'bi-person-badge';
                            ?>
                            <tr>
                                <td class="text-center fw-semibold text-muted"><?= $no++; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($j['nama_jadwal']); ?></div>
                                    <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                        <span class="badge <?= $badgeTipe ?> rounded-pill px-2 py-1 text-xs">
                                            <i class="bi <?= $iconTipe ?> me-1"></i><?= ucfirst($j['tipe_pengguna']); ?>
                                        </span>
                                        <?php if (!empty($j['nama_kelas'])): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 text-xs">
                                                <i class="bi bi-house-door me-1"></i>Kelas <?= htmlspecialchars($j['nama_kelas']); ?>
                                            </span>
                                        <?php elseif($isSiswa): ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1 text-xs">
                                                Semua Kelas
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($j['tanggal_mulai']) || !empty($j['tanggal_akhir'])): ?>
                                            <span class="badge bg-light text-muted border rounded-pill px-2 py-1 text-xs" title="Periode Berlaku">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                <?= !empty($j['tanggal_mulai']) ? date('d/m/y', strtotime($j['tanggal_mulai'])) : 'Awal' ?> s/d <?= !empty($j['tanggal_akhir']) ? date('d/m/y', strtotime($j['tanggal_akhir'])) : 'Seterusnya' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(!empty($j['keterangan'])): ?>
                                        <div class="text-xs text-muted mt-1 fst-italic"><?= htmlspecialchars($j['keterangan']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                        <i class="bi bi-calendar3 me-1 text-primary"></i><?= htmlspecialchars($j['hari']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-primary">
                                        <i class="bi bi-play-circle me-1"></i><?= substr($j['jam_mulai'], 0, 5); ?> WIB
                                    </div>
                                    <div class="text-xs text-muted">Awal scan dibuka</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-success">
                                        <i class="bi bi-check2-circle me-1"></i><?= substr($j['jam_masuk'], 0, 5); ?> WIB
                                    </div>
                                    <div class="text-xs text-muted">> <?= substr($j['jam_masuk'], 0, 5); ?> Terlambat</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-danger">
                                        <i class="bi bi-stop-circle me-1"></i><?= substr($j['jam_akhir'], 0, 5); ?> WIB
                                    </div>
                                    <div class="text-xs text-muted">Batas akhir scan</div>
                                </td>
                                <td class="text-center">
                                    <?php if ($j['is_active'] == 1): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1">
                                            <i class="bi bi-slash-circle me-1"></i> Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Tombol Edit -->
                                        <button type="button" class="btn btn-outline-primary" 
                                                title="Edit Jadwal"
                                                onclick="setupModalUbah(<?= htmlspecialchars(json_encode($j)); ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <!-- Tombol Toggle Status -->
                                        <a href="<?= BASEURL; ?>/admin_jadwal/toggle/<?= $j['id']; ?>" 
                                           class="btn <?= ($j['is_active'] == 1) ? 'btn-outline-warning' : 'btn-outline-success'; ?>"
                                           title="<?= ($j['is_active'] == 1) ? 'Nonaktifkan Jadwal' : 'Aktifkan Jadwal'; ?>"
                                           onclick="return confirm('Apakah Anda yakin ingin <?= ($j['is_active'] == 1) ? 'menonaktifkan' : 'mengaktifkan'; ?> jadwal <?= htmlspecialchars($j['nama_jadwal']); ?>?');">
                                            <i class="bi <?= ($j['is_active'] == 1) ? 'bi-toggle-on' : 'bi-toggle-off'; ?>"></i>
                                        </a>
                                        <!-- Tombol Hapus -->
                                        <a href="<?= BASEURL; ?>/admin_jadwal/hapus/<?= $j['id']; ?>" 
                                           class="btn btn-outline-danger" 
                                           title="Hapus Jadwal"
                                           onclick="return confirm('PERINGATAN: Yakin ingin menghapus jadwal <?= htmlspecialchars($j['nama_jadwal']); ?>? Disarankan untuk menonaktifkannya saja jika jadwal sudah pernah digunakan agar riwayat absensi tetap konsisten.');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if (empty($data['jadwal'])): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    Tidak ada jadwal absensi yang sesuai filter.
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#formJadwalModal" onclick="setupModalTambah()">
                                            <i class="bi bi-plus-circle me-1"></i> Buat Jadwal Baru
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ MODAL TAMBAH / UBAH JADWAL ═══════════════════════════════════════════ -->
<div class="modal fade" id="formJadwalModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="modalTitle">
                    <i class="bi bi-calendar-plus me-2"></i>Tambah Jadwal Absensi Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASEURL; ?>/admin_jadwal/tambah" method="POST" id="formJadwal" onsubmit="return validateFormJadwal(event)">
                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token']; ?>">
                <input type="hidden" name="id" id="jadwal_id">

                <div class="modal-body p-4">
                    <div id="modalAlertError" class="alert alert-danger d-none py-2 px-3 small"></div>

                    <!-- Row 1: Nama & Tipe -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold text-dark">Nama Jadwal <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jadwal" id="nama_jadwal" class="form-control" placeholder="Contoh: Jadwal Siswa atau Jadwal Guru" required maxlength="100">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-dark">Jenis Pengguna <span class="text-danger">*</span></label>
                            <select name="tipe_pengguna" id="tipe_pengguna" class="form-select" required onchange="handleTipeChange()">
                                <option value="siswa">Siswa</option>
                                <option value="guru">Guru (Dewan Guru)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Cakupan Kelas & Hari -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6" id="wrapperKelas">
                            <label class="form-label small fw-semibold text-dark">Cakupan Kelas (Khusus Siswa)</label>
                            <select name="kelas_id" id="kelas_id" class="form-select">
                                <option value="">-- Berlaku untuk Semua Kelas --</option>
                                <?php foreach ($data['kelas_list'] as $k): ?>
                                    <option value="<?= $k['id']; ?>">Kelas <?= htmlspecialchars($k['nama_kelas']); ?> (<?= htmlspecialchars($k['jurusan']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-xs">Pilih kelas spesifik jika jadwal ini khusus kelas tertentu.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Hari Berlaku <span class="text-danger">*</span></label>
                            <select name="hari" id="hari" class="form-select" required>
                                <option value="Senin - Jumat">Senin - Jumat</option>
                                <option value="Senin - Sabtu">Senin - Sabtu</option>
                                <option value="Semua Hari">Semua Hari</option>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 3: Konfigurasi Waktu 24 Jam (Card Accent) -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="fw-bold small text-dark mb-2">
                            <i class="bi bi-clock me-1 text-primary"></i>Ketentuan Waktu Absensi (Format 24 Jam)
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-primary">
                                    <i class="bi bi-play-circle me-1"></i>Jam Mulai Scan <span class="text-danger">*</span>
                                </label>
                                <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" required value="06:00">
                                <div class="form-text text-xs">Absensi sebelum jam ini ditolak ("Belum dibuka").</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-success">
                                    <i class="bi bi-check-circle me-1"></i>Batas Hadir Tepat Waktu <span class="text-danger">*</span>
                                </label>
                                <input type="time" name="jam_masuk" id="jam_masuk" class="form-control" required value="07:00">
                                <div class="form-text text-xs">Lewat dari jam ini berstatus "Terlambat".</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-danger">
                                    <i class="bi bi-stop-circle me-1"></i>Jam Tutup / Akhir Absensi <span class="text-danger">*</span>
                                </label>
                                <input type="time" name="jam_akhir" id="jam_akhir" class="form-control" required value="16:00">
                                <div class="form-text text-xs">Lewat dari jam ini ditolak ("Sudah ditutup").</div>
                            </div>
                        </div>
                    </div>

                    <!-- Row 4: Periode Berlaku Opsional & Status -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Tanggal Mulai Berlaku (Opsional)</label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control">
                            <div class="form-text text-xs">Kosongkan jika berlaku permanen.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Tanggal Akhir Berlaku (Opsional)</label>
                            <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control">
                            <div class="form-text text-xs">Kosongkan jika berlaku permanen.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Status Jadwal <span class="text-danger">*</span></label>
                            <select name="is_active" id="is_active" class="form-select" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 5: Keterangan -->
                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-dark">Keterangan / Catatan (Opsional)</label>
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="2" placeholder="Catatan opsional untuk jadwal ini..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSubmitJadwal">
                        <i class="bi bi-save me-1"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleTipeChange() {
    const tipe = document.getElementById('tipe_pengguna').value;
    const wrapperKelas = document.getElementById('wrapperKelas');
    const kelasSelect  = document.getElementById('kelas_id');

    if (tipe === 'guru') {
        kelasSelect.value = '';
        wrapperKelas.style.opacity = '0.5';
        kelasSelect.disabled = true;
    } else {
        wrapperKelas.style.opacity = '1';
        kelasSelect.disabled = false;
    }
}

function setupModalTambah() {
    const form = document.getElementById('formJadwal');
    form.action = '<?= BASEURL; ?>/admin_jadwal/tambah';
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-calendar-plus me-2"></i>Tambah Jadwal Absensi Baru';
    document.getElementById('btnSubmitJadwal').innerHTML = '<i class="bi bi-save me-1"></i> Simpan Jadwal';
    document.getElementById('modalAlertError').classList.add('d-none');

    document.getElementById('jadwal_id').value = '';
    document.getElementById('nama_jadwal').value = '';
    document.getElementById('tipe_pengguna').value = 'siswa';
    document.getElementById('kelas_id').value = '';
    document.getElementById('hari').value = 'Senin - Jumat';
    document.getElementById('jam_mulai').value = '06:00';
    document.getElementById('jam_masuk').value = '07:00';
    document.getElementById('jam_akhir').value = '16:00';
    document.getElementById('tanggal_mulai').value = '';
    document.getElementById('tanggal_akhir').value = '';
    document.getElementById('is_active').value = '1';
    document.getElementById('keterangan').value = '';

    handleTipeChange();
}

function setupModalUbah(jadwal) {
    const form = document.getElementById('formJadwal');
    form.action = '<?= BASEURL; ?>/admin_jadwal/ubah';
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Ubah Jadwal Absensi';
    document.getElementById('btnSubmitJadwal').innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simpan Perubahan';
    document.getElementById('modalAlertError').classList.add('d-none');

    document.getElementById('jadwal_id').value = jadwal.id;
    document.getElementById('nama_jadwal').value = jadwal.nama_jadwal;
    document.getElementById('tipe_pengguna').value = jadwal.tipe_pengguna;
    document.getElementById('kelas_id').value = jadwal.kelas_id || '';
    document.getElementById('hari').value = jadwal.hari;
    document.getElementById('jam_mulai').value = jadwal.jam_mulai ? jadwal.jam_mulai.substring(0, 5) : '06:00';
    document.getElementById('jam_masuk').value = jadwal.jam_masuk ? jadwal.jam_masuk.substring(0, 5) : '07:00';
    document.getElementById('jam_akhir').value = jadwal.jam_akhir ? jadwal.jam_akhir.substring(0, 5) : '16:00';
    document.getElementById('tanggal_mulai').value = jadwal.tanggal_mulai || '';
    document.getElementById('tanggal_akhir').value = jadwal.tanggal_akhir || '';
    document.getElementById('is_active').value = jadwal.is_active;
    document.getElementById('keterangan').value = jadwal.keterangan || '';

    handleTipeChange();
}

function validateFormJadwal(e) {
    const jamMulai = document.getElementById('jam_mulai').value;
    const jamMasuk = document.getElementById('jam_masuk').value;
    const jamAkhir = document.getElementById('jam_akhir').value;
    const tglMulai = document.getElementById('tanggal_mulai').value;
    const tglAkhir = document.getElementById('tanggal_akhir').value;
    const alertBox = document.getElementById('modalAlertError');

    alertBox.classList.add('d-none');
    alertBox.innerHTML = '';

    if (!jamMulai || !jamMasuk || !jamAkhir) {
        alertBox.innerHTML = 'Jam mulai, batas waktu hadir, dan jam akhir wajib diisi!';
        alertBox.classList.remove('d-none');
        e.preventDefault();
        return false;
    }

    if (jamMulai >= jamAkhir) {
        alertBox.innerHTML = '<strong>Validasi Gagal:</strong> Jam mulai (' + jamMulai + ') harus lebih awal daripada jam akhir (' + jamAkhir + ')!';
        alertBox.classList.remove('d-none');
        e.preventDefault();
        return false;
    }

    if (jamMasuk < jamMulai || jamMasuk > jamAkhir) {
        alertBox.innerHTML = '<strong>Validasi Gagal:</strong> Batas waktu hadir (' + jamMasuk + ') harus berada dalam rentang jam mulai (' + jamMulai + ') dan jam akhir (' + jamAkhir + ')!';
        alertBox.classList.remove('d-none');
        e.preventDefault();
        return false;
    }

    if (tglMulai && tglAkhir && tglMulai > tglAkhir) {
        alertBox.innerHTML = '<strong>Validasi Gagal:</strong> Tanggal mulai periode harus lebih awal atau sama dengan tanggal akhir periode!';
        alertBox.classList.remove('d-none');
        e.preventDefault();
        return false;
    }

    return true;
}
</script>
