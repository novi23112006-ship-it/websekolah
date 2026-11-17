<div class="row">
    <div class="col-12">
        <?php Flasher::flash(); ?>
        
        <!-- Alert Info Kenaikan Kelas Massal -->
        <div class="alert alert-info border-0 rounded-4 p-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2 shadow-sm">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-arrow-up-right-circle-fill fs-4 text-primary"></i>
                <span class="small">
                    <strong>Otomatisasi Kenaikan Kelas:</strong> Untuk menaikkan kelas siswa (misalnya dari kelas 10 ke 11, atau 11 ke 12), gunakan sistem <strong>Kenaikan Kelas Massal</strong> langsung untuk 1 kelas sekaligus secara otomatis tanpa perlu mengubah data siswa satu per satu manual.
                </span>
            </div>
            <a href="<?= BASEURL; ?>/admin_kelas" class="btn btn-sm btn-success rounded-pill px-3 text-nowrap fw-semibold shadow-sm">
                <i class="bi bi-lightning-charge me-1"></i> Kenaikan Kelas Massal
            </a>
        </div>
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="m-0 font-weight-bold text-primary"><i class="bi bi-mortarboard me-2"></i>Daftar Siswa</h5>
                <div class="d-flex gap-2">
                    <a href="<?= BASEURL; ?>/admin_kelas" class="btn btn-sm btn-outline-success rounded-pill px-3">
                        <i class="bi bi-arrow-up-right-circle me-1"></i> Kenaikan Kelas Massal
                    </a>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#formModal" onclick="setupModalTambah()">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Siswa
                    </button>
                </div>
            </div>
            <div class="card-body">
                
                <div class="row mb-3">
                    <div class="col-md-4 mb-2">
                        <!-- Search by name/NIS -->
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari Nama atau NIS...">
                    </div>
                    <div class="col-md-4 mb-2">
                        <!-- Filter by Kelas -->
                        <form action="<?= BASEURL; ?>/admin_siswa" method="GET" id="filterForm">
                            <select class="form-select" name="kelas_id" onchange="document.getElementById('filterForm').submit()">
                                <option value="">-- Semua Kelas --</option>
                                <?php foreach($data['kelas'] as $kls): ?>
                                    <option value="<?= $kls['id'] ?>" <?= ($data['filter_kelas'] == $kls['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kls['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="siswaTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th>NIS</th>
                                <th>Nama Lengkap</th>
                                <th>L/P</th>
                                <th>Kelas</th>
                                <th>Barcode</th>
                                <th>Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($data['siswa'] as $siswa) : ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($siswa['nis']); ?></td>
                                <td><?= htmlspecialchars($siswa['nama']); ?></td>
                                <td><?= $siswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                <td><?= htmlspecialchars($siswa['nama_kelas'] ?? '-'); ?></td>
                                <td>
                                    <?php if(!empty($siswa['barcode'])): ?>
                                        <code class="bg-light px-2 py-1 rounded text-primary fw-bold font-monospace"><?= htmlspecialchars($siswa['barcode']); ?></code>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Belum ada barcode</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($siswa['status'] == 'active'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(empty($siswa['barcode'])): ?>
                                        <form action="<?= BASEURL; ?>/admin_siswa/generate_barcode/<?= $siswa['id']; ?>" method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= AuthHelper::generateCsrfToken(); ?>">
                                            <input type="hidden" name="filter_kelas" value="<?= htmlspecialchars($data['filter_kelas']) ?>">
                                            <button type="submit" class="btn btn-sm btn-info mb-1" title="Generate Barcode">
                                                <i class="bi bi-magic me-1"></i>Generate
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-success mb-1" onclick="showBarcode('<?= htmlspecialchars($siswa['barcode']) ?>', '<?= htmlspecialchars(addslashes($siswa['nama'])) ?>', '<?= htmlspecialchars($siswa['nis']) ?>', '<?= htmlspecialchars(addslashes($siswa['nama_kelas'] ?? '-')) ?>', '<?= $siswa['id'] ?>')" title="Lihat QR Code">
                                            <i class="bi bi-qr-code me-1"></i>Lihat
                                        </button>
                                        <a href="<?= BASEURL; ?>/admin_siswa/cetak_barcode/<?= $siswa['id']; ?>" target="_blank" class="btn btn-sm btn-secondary mb-1" title="Cetak Kartu Barcode">
                                            <i class="bi bi-printer me-1"></i>Cetak
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-sm btn-warning mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#formModal"
                                        onclick="setupModalUbah(<?= htmlspecialchars(json_encode($siswa)); ?>)">Edit</button>
                                    <a href="<?= BASEURL; ?>/admin_siswa/hapus/<?= $siswa['id']; ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Yakin ingin menghapus siswa ini? Akun login juga akan terhapus.');">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($data['siswa'])): ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data siswa.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="judulModal" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="judulModal">Tambah Data Siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="<?= BASEURL; ?>/admin_siswa/tambah" method="post" id="formSiswa">
            <input type="hidden" name="id" id="id">
            <input type="hidden" name="filter_kelas" value="<?= htmlspecialchars($data['filter_kelas']) ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nis" class="form-label">NIS (Nomor Induk Siswa)</label>
                    <input type="text" class="form-control" id="nis" name="nis" required>
                    <small class="text-muted">Digunakan sebagai Username login.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="nisn" class="form-label">NISN (Opsional)</label>
                    <input type="text" class="form-control" id="nisn" name="nisn">
                </div>
            </div>

            <div class="mb-3">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                    <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select class="form-select" id="kelas_id" name="kelas_id">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach($data['kelas'] as $kls): ?>
                            <option value="<?= $kls['id'] ?>"><?= htmlspecialchars($kls['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="rfid_uid" class="form-label">RFID UID (Unik)</label>
                <input type="text" class="form-control" id="rfid_uid" name="rfid_uid" required>
            </div>
            
            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control" id="alamat" name="alamat" rows="2"></textarea>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status Akun</label>
                <select class="form-select" id="status" name="status">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Data</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tampil QR Code Siswa -->
<div class="modal fade" id="barcodeModal" tabindex="-1" aria-labelledby="barcodeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center rounded-4 shadow border-0 overflow-hidden">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title fs-6 fw-bold mb-0" id="barcodeModalLabel"><i class="bi bi-qr-code me-2"></i>QR Code Barcode Absensi</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
         <h5 id="barcodeSiswaNama" class="mb-1 fw-bold text-dark"></h5>
         <p id="barcodeSiswaSub" class="text-muted small mb-3"></p>
         
         <div class="p-3 bg-light rounded-4 border d-inline-block shadow-sm mb-3">
             <div id="qrcode" class="d-flex justify-content-center"></div>
         </div>

         <div>
             <div class="d-inline-block bg-dark text-white px-3 py-1 rounded font-monospace fw-bold mb-2 fs-6" id="barcodeText"></div>
         </div>
         <p class="text-muted small mb-3">QR Barcode ini dapat dipindai pada kamera terminal absensi sekolah.</p>

         <div class="d-flex justify-content-center gap-2">
            <a id="btnCetakModal" href="#" target="_blank" class="btn btn-primary rounded-pill px-3 fw-semibold">
               <i class="bi bi-printer me-1"></i> Cetak Kartu
            </a>
            <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
               Tutup
            </button>
         </div>
      </div>
    </div>
  </div>
</div>

<!-- Include QRCode JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
function setupModalTambah() {
    document.getElementById('judulModal').innerHTML = 'Tambah Data Siswa';
    document.getElementById('formSiswa').action = '<?= BASEURL; ?>/admin_siswa/tambah';
    document.getElementById('btnSubmit').innerHTML = 'Simpan Data';
    
    document.getElementById('id').value = '';
    document.getElementById('nis').value = '';
    document.getElementById('nisn').value = '';
    document.getElementById('nama').value = '';
    document.getElementById('jenis_kelamin').value = 'L';
    document.getElementById('kelas_id').value = '<?= htmlspecialchars($data['filter_kelas']) ?>';
    document.getElementById('rfid_uid').value = '';
    document.getElementById('alamat').value = '';
    document.getElementById('status').value = 'active';
}

function setupModalUbah(data) {
    document.getElementById('judulModal').innerHTML = 'Ubah Data Siswa';
    document.getElementById('formSiswa').action = '<?= BASEURL; ?>/admin_siswa/ubah';
    document.getElementById('btnSubmit').innerHTML = 'Ubah Data';
    
    document.getElementById('id').value = data.id;
    document.getElementById('nis').value = data.nis;
    document.getElementById('nisn').value = data.nisn ? data.nisn : '';
    document.getElementById('nama').value = data.nama;
    document.getElementById('jenis_kelamin').value = data.jenis_kelamin;
    document.getElementById('kelas_id').value = data.kelas_id ? data.kelas_id : '';
    document.getElementById('rfid_uid').value = data.rfid_uid;
    document.getElementById('alamat').value = data.alamat ? data.alamat : '';
    document.getElementById('status').value = data.status;
}

// Simple Client-side Search
document.getElementById('searchInput').addEventListener('keyup', function() {
    var value = this.value.toLowerCase();
    var rows = document.querySelectorAll('#siswaTable tbody tr');
    
    rows.forEach(function(row) {
        var text = row.innerText.toLowerCase();
        if(text.indexOf(value) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

let qrcodeObj = null;
function showBarcode(barcode, nama, nis, kelas, siswaId) {
    document.getElementById('barcodeSiswaNama').innerText = nama;
    document.getElementById('barcodeSiswaSub').innerText = 'NIS: ' + (nis || '-') + (kelas && kelas !== '-' ? ' • ' + kelas : '');
    document.getElementById('barcodeText').innerText = barcode;
    document.getElementById('btnCetakModal').href = '<?= BASEURL; ?>/admin_siswa/cetak_barcode/' + siswaId;
    
    let qrContainer = document.getElementById('qrcode');
    qrContainer.innerHTML = ''; // clear previous
    
    qrcodeObj = new QRCode(qrContainer, {
        text: barcode,
        width: 170,
        height: 170,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
    
    var barcodeModal = new bootstrap.Modal(document.getElementById('barcodeModal'));
    barcodeModal.show();
}
</script>
