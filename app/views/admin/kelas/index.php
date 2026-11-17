<div class="row">
    <div class="col-12">
        <?php Flasher::flash(); ?>
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-door-open me-2"></i>Daftar Kelas & Rombel
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalKenaikanKelas" onclick="setupModalKenaikanUmum()">
                        <i class="bi bi-arrow-up-right-circle me-1"></i> Kenaikan Kelas Massal
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#formModal" onclick="setupModalTambah()">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Kelas
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>Nama Kelas</th>
                                <th>Jurusan</th>
                                <th class="text-center" width="10%">Tingkat</th>
                                <th>Wali Kelas</th>
                                <th class="text-center" width="14%">Jumlah Siswa</th>
                                <th width="22%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($data['kelas'] as $kelas) : ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($kelas['nama_kelas']); ?></span>
                                </td>
                                <td><?= htmlspecialchars($kelas['jurusan'] ?? '-'); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-secondary px-2 py-1"><?= htmlspecialchars($kelas['tingkat']); ?></span>
                                </td>
                                <td><?= htmlspecialchars($kelas['nama_wali'] ?? 'Belum ditentukan'); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">
                                        <i class="bi bi-people-fill me-1"></i> <?= (int)$kelas['total_siswa']; ?> Siswa
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-success mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#modalKenaikanKelas"
                                        onclick="setupModalKenaikanSpesifik(<?= $kelas['id']; ?>, '<?= htmlspecialchars(addslashes($kelas['nama_kelas'])); ?>', <?= (int)$kelas['total_siswa']; ?>)"
                                        title="Kenaikan / Pindah Kelas Otomatis">
                                        <i class="bi bi-arrow-up-right-circle me-1"></i>Naik Kelas
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#formModal"
                                        onclick="setupModalUbah(<?= htmlspecialchars(json_encode($kelas)); ?>)">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <a href="<?= BASEURL; ?>/admin_kelas/hapus/<?= $kelas['id']; ?>" class="btn btn-sm btn-outline-danger mb-1" onclick="return confirm('Yakin ingin menghapus kelas ini? Data siswa di kelas ini akan terlepas.');">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($data['kelas'])): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Belum ada data kelas.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Tambah / Ubah Kelas -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="judulModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="judulModal"><i class="bi bi-door-open me-2"></i>Tambah Data Kelas</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="<?= BASEURL; ?>/admin_kelas/tambah" method="post" id="formKelas">
            <input type="hidden" name="id" id="id">
            
            <div class="mb-3">
                <label for="nama_kelas" class="form-label fw-semibold">Nama Kelas</label>
                <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" required placeholder="Cth: X TKJ 1 / XI RPL 2">
            </div>
            <div class="mb-3">
                <label for="jurusan" class="form-label fw-semibold">Jurusan</label>
                <input type="text" class="form-control" id="jurusan" name="jurusan" placeholder="Cth: Teknik Komputer dan Jaringan / Rekayasa Perangkat Lunak">
            </div>
            <div class="mb-3">
                <label for="tingkat" class="form-label fw-semibold">Tingkat</label>
                <select class="form-select" id="tingkat" name="tingkat">
                    <option value="X">X (Sepuluh)</option>
                    <option value="XI">XI (Sebelas)</option>
                    <option value="XII">XII (Dua Belas)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="wali_kelas_id" class="form-label fw-semibold">Wali Kelas</label>
                <select class="form-select" id="wali_kelas_id" name="wali_kelas_id">
                    <option value="">-- Pilih Wali Kelas (Opsional) --</option>
                    <?php foreach($data['guru'] as $guru): ?>
                        <option value="<?= $guru['id'] ?>"><?= htmlspecialchars($guru['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Data</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Kenaikan Kelas Massal (Otomatis) -->
<div class="modal fade" id="modalKenaikanKelas" tabindex="-1" aria-labelledby="judulKenaikanModal" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="judulKenaikanModal">
            <i class="bi bi-arrow-up-right-circle me-2"></i>Otomatisasi Kenaikan / Pindah Kelas Massal
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <!-- Info Banner -->
        <div class="alert alert-info d-flex align-items-start mb-3">
            <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
            <div>
                <strong>Proses Kenaikan Kelas Massal:</strong>
                <p class="mb-0 small text-muted">
                    Fitur ini memindahkan seluruh siswa aktif dalam satu rombel sekaligus (contoh: dari <strong>Kelas XI ke Kelas XII</strong> atau <strong>Kelulusan Kelas XII</strong>) secara otomatis. Anda tidak perlu mengubah atau memindahkan data siswa satu per satu secara manual.
                </p>
            </div>
        </div>

        <form action="<?= BASEURL; ?>/admin_kelas/naik_kelas_massal" method="post" id="formKenaikanKelas" onsubmit="return validasiSubmitKenaikan();">
            
            <!-- Tipe Aksi -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Tipe Proses</label>
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="card p-2 border cursor-pointer h-100" id="cardTipeNaik" onclick="pilihTipeAksi('naik')" style="cursor: pointer;">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe_aksi" id="tipeNaik" value="naik" checked onchange="handleTipeChange()">
                                <label class="form-check-label fw-bold text-dark" for="tipeNaik">
                                    <i class="bi bi-arrow-up-circle text-primary me-1"></i> Kenaikan / Pindah Kelas
                                </label>
                                <div class="small text-muted">Pindahkan seluruh siswa ke jenjang/kelas berikutnya.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-2 border cursor-pointer h-100" id="cardTipeLulus" onclick="pilihTipeAksi('lulus')" style="cursor: pointer;">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe_aksi" id="tipeLulus" value="lulus" onchange="handleTipeChange()">
                                <label class="form-check-label fw-bold text-dark" for="tipeLulus">
                                    <i class="bi bi-mortarboard text-success me-1"></i> Kelulusan Kelas XII (Alumni)
                                </label>
                                <div class="small text-muted">Set status siswa menjadi lulus/nonaktif.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <!-- Kelas Asal -->
                <div class="col-md-6">
                    <label for="kelas_asal_id" class="form-label fw-semibold">Kelas Asal (Rombel Sumber)</label>
                    <select class="form-select form-select-lg fs-6" id="kelas_asal_id" name="kelas_asal_id" required onchange="onKelasAsalChanged()">
                        <option value="">-- Pilih Kelas Asal --</option>
                        <?php foreach($data['kelas'] as $k): ?>
                            <option value="<?= $k['id'] ?>" data-nama="<?= htmlspecialchars($k['nama_kelas']) ?>" data-tingkat="<?= $k['tingkat'] ?>" data-total="<?= (int)$k['total_siswa'] ?>">
                                <?= htmlspecialchars($k['nama_kelas']) ?> (<?= (int)$k['total_siswa'] ?> Siswa)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text small">Pilih kelas yang siswanya akan dipindahkan/dinaikkan.</div>
                </div>

                <!-- Kelas Tujuan -->
                <div class="col-md-6" id="containerKelasTujuan">
                    <label for="kelas_tujuan_id" class="form-label fw-semibold">Kelas Tujuan (Rombel Baru)</label>
                    <select class="form-select form-select-lg fs-6" id="kelas_tujuan_id" name="kelas_tujuan_id">
                        <option value="">-- Pilih Kelas Tujuan --</option>
                        <?php foreach($data['kelas'] as $k): ?>
                            <option value="<?= $k['id'] ?>" data-nama="<?= htmlspecialchars($k['nama_kelas']) ?>">
                                <?= htmlspecialchars($k['nama_kelas']) ?> (Tingkat <?= $k['tingkat'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text small">Pilih kelas baru tujuan siswa tersebut.</div>
                </div>
            </div>

            <!-- Preview Siswa Box -->
            <div class="card bg-light border-0 mb-3" id="previewBox" style="display: none;">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary me-2" id="badgeJumlahSiswa">0 Siswa</span>
                            <span class="fw-semibold text-dark" id="textInfoAsal">Kelas Asal</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-decoration-none" id="btnToggleDaftar" onclick="toggleDaftarSiswa()">
                            <i class="bi bi-chevron-down me-1"></i> Lihat Daftar Nama Siswa
                        </button>
                    </div>

                    <div id="containerDaftarSiswa" class="mt-3 pt-3 border-top" style="display: none; max-height: 180px; overflow-y: auto;">
                        <div class="d-flex flex-wrap gap-1" id="listNamaSiswa">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Konfirmasi Checkbox -->
            <div class="form-check p-3 bg-warning bg-opacity-10 rounded border border-warning mb-2">
                <input class="form-check-input" type="checkbox" id="checkKonfirmasi" required>
                <label class="form-check-label small fw-semibold text-dark" for="checkKonfirmasi">
                    Saya menyatakan yakin dan menyetujui pembaruan massal ini. Seluruh siswa aktif pada kelas asal yang dipilih akan otomatis diperbarui.
                </label>
            </div>

      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success px-4" id="btnSubmitKenaikan">
            <i class="bi bi-check2-circle me-1"></i> Eksekusi Kenaikan Kelas
        </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function setupModalTambah() {
    document.getElementById('judulModal').innerHTML = '<i class="bi bi-door-open me-2"></i>Tambah Data Kelas';
    document.getElementById('formKelas').action = '<?= BASEURL; ?>/admin_kelas/tambah';
    document.getElementById('btnSubmit').innerHTML = 'Simpan Data';
    
    document.getElementById('id').value = '';
    document.getElementById('nama_kelas').value = '';
    document.getElementById('jurusan').value = '';
    document.getElementById('tingkat').value = 'X';
    document.getElementById('wali_kelas_id').value = '';
}

function setupModalUbah(data) {
    document.getElementById('judulModal').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Ubah Data Kelas';
    document.getElementById('formKelas').action = '<?= BASEURL; ?>/admin_kelas/ubah';
    document.getElementById('btnSubmit').innerHTML = 'Ubah Data';
    
    document.getElementById('id').value = data.id;
    document.getElementById('nama_kelas').value = data.nama_kelas;
    document.getElementById('jurusan').value = data.jurusan;
    document.getElementById('tingkat').value = data.tingkat;
    document.getElementById('wali_kelas_id').value = data.wali_kelas_id ? data.wali_kelas_id : '';
}

// Handler Kenaikan Kelas Massal
function setupModalKenaikanUmum() {
    pilihTipeAksi('naik');
    document.getElementById('kelas_asal_id').value = '';
    document.getElementById('kelas_tujuan_id').value = '';
    document.getElementById('previewBox').style.display = 'none';
    document.getElementById('checkKonfirmasi').checked = false;
}

function setupModalKenaikanSpesifik(kelasId, namaKelas, totalSiswa) {
    setupModalKenaikanUmum();
    const selectAsal = document.getElementById('kelas_asal_id');
    selectAsal.value = kelasId;
    onKelasAsalChanged();
}

function pilihTipeAksi(tipe) {
    if (tipe === 'lulus') {
        document.getElementById('tipeLulus').checked = true;
    } else {
        document.getElementById('tipeNaik').checked = true;
    }
    handleTipeChange();
}

function handleTipeChange() {
    const isLulus = document.getElementById('tipeLulus').checked;
    const containerTujuan = document.getElementById('containerKelasTujuan');
    const selectTujuan = document.getElementById('kelas_tujuan_id');
    const btnSubmit = document.getElementById('btnSubmitKenaikan');
    
    if (isLulus) {
        containerTujuan.style.display = 'none';
        selectTujuan.removeAttribute('required');
        btnSubmit.innerHTML = '<i class="bi bi-mortarboard me-1"></i> Eksekusi Kelulusan Kelas';
        btnSubmit.className = 'btn btn-primary px-4';
    } else {
        containerTujuan.style.display = 'block';
        selectTujuan.setAttribute('required', 'required');
        btnSubmit.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Eksekusi Kenaikan Kelas';
        btnSubmit.className = 'btn btn-success px-4';
    }
}

function onKelasAsalChanged() {
    const selectAsal = document.getElementById('kelas_asal_id');
    const kelasId = selectAsal.value;
    const previewBox = document.getElementById('previewBox');
    const badgeJumlahSiswa = document.getElementById('badgeJumlahSiswa');
    const textInfoAsal = document.getElementById('textInfoAsal');
    const listNamaSiswa = document.getElementById('listNamaSiswa');
    const containerDaftar = document.getElementById('containerDaftarSiswa');
    
    if (!kelasId) {
        previewBox.style.display = 'none';
        return;
    }

    const selectedOption = selectAsal.options[selectAsal.selectedIndex];
    const namaKelas = selectedOption.getAttribute('data-nama');
    const totalSiswa = selectedOption.getAttribute('data-total');

    previewBox.style.display = 'block';
    badgeJumlahSiswa.textContent = totalSiswa + ' Siswa Aktif';
    textInfoAsal.textContent = 'Ditemukan di kelas ' + namaKelas;
    listNamaSiswa.innerHTML = '<span class="text-muted small">Memuat daftar siswa...</span>';

    // Fetch siswa secara dinamis
    fetch('<?= BASEURL; ?>/admin_kelas/get_siswa_kelas/' + kelasId)
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success' && res.data && res.data.length > 0) {
                badgeJumlahSiswa.textContent = res.count + ' Siswa Aktif';
                let html = '';
                res.data.forEach((s, idx) => {
                    html += `<span class="badge bg-white text-dark border px-2 py-1 mb-1">
                                <span class="text-primary fw-bold">${idx + 1}.</span> ${s.nama} (${s.nis})
                             </span> `;
                });
                listNamaSiswa.innerHTML = html;
            } else {
                badgeJumlahSiswa.textContent = '0 Siswa';
                listNamaSiswa.innerHTML = '<span class="text-muted small fst-italic">Tidak ada siswa aktif di kelas ini.</span>';
            }
        })
        .catch(err => {
            listNamaSiswa.innerHTML = '<span class="text-danger small">Gagal memuat rincian siswa.</span>';
        });
}

function toggleDaftarSiswa() {
    const container = document.getElementById('containerDaftarSiswa');
    const btn = document.getElementById('btnToggleDaftar');
    if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
        btn.innerHTML = '<i class="bi bi-chevron-up me-1"></i> Sembunyikan Rincian';
    } else {
        container.style.display = 'none';
        btn.innerHTML = '<i class="bi bi-chevron-down me-1"></i> Lihat Daftar Nama Siswa';
    }
}

function validasiSubmitKenaikan() {
    const selectAsal = document.getElementById('kelas_asal_id');
    const isLulus = document.getElementById('tipeLulus').checked;
    const asalId = selectAsal.value;

    if (!asalId) {
        alert('Silakan pilih kelas asal terlebih dahulu.');
        return false;
    }

    const namaAsal = selectAsal.options[selectAsal.selectedIndex].getAttribute('data-nama');

    if (!isLulus) {
        const selectTujuan = document.getElementById('kelas_tujuan_id');
        const tujuanId = selectTujuan.value;
        if (!tujuanId) {
            alert('Silakan pilih kelas tujuan.');
            return false;
        }
        if (asalId === tujuanId) {
            alert('Kelas Asal dan Kelas Tujuan tidak boleh sama!');
            return false;
        }
        const namaTujuan = selectTujuan.options[selectTujuan.selectedIndex].getAttribute('data-nama');
        return confirm(`Apakah Anda yakin ingin memindahkan/menaikkan seluruh siswa dari ${namaAsal} ke ${namaTujuan} secara otomatis?`);
    } else {
        return confirm(`PERHATIAN: Seluruh siswa aktif di kelas ${namaAsal} akan dinyatakan LULUS (status nonaktif/alumni). Lanjutkan?`);
    }
}
</script>
