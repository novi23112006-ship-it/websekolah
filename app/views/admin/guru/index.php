<div class="row">
    <div class="col-12">
        <?php Flasher::flash(); ?>
        
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-primary"><i class="bi bi-people-fill me-2"></i>Daftar Guru & Kepala Sekolah</h5>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#formModal" onclick="setupModalTambah()">
                    <i class="bi bi-person-plus-fill me-1"></i> + Tambah Guru
                </button>
            </div>
            <div class="card-body">
                <!-- Search bar -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari Nama, NIP, atau Mapel...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="guruTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Mata Pelajaran / Jabatan</th>
                                <th>Barcode ID</th>
                                <th width="24%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($data['guru'] as $guru) : ?>
                            <tr>
                                <td class="text-center text-muted"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($guru['nip'] ?: '-'); ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($guru['nama']); ?></td>
                                <td>
                                    <?php if (stripos($guru['mata_pelajaran'] ?? '', 'Kepala') !== false): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-award-fill me-1"></i>Kepala Sekolah</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-primary border"><?= htmlspecialchars($guru['mata_pelajaran'] ?: 'Guru'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($guru['barcode'])): ?>
                                        <code class="bg-light px-2 py-1 rounded text-primary fw-bold font-monospace"><?= htmlspecialchars($guru['barcode']); ?></code>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Belum ada barcode</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (empty($guru['barcode'])): ?>
                                        <form action="<?= BASEURL; ?>/admin_guru/generate_barcode/<?= $guru['id']; ?>" method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= AuthHelper::generateCsrfToken(); ?>">
                                            <button type="submit" class="btn btn-sm btn-info text-white mb-1" title="Generate Barcode Otomatis">
                                                <i class="bi bi-magic me-1"></i>Generate
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-success mb-1" 
                                            onclick="showBarcode('<?= htmlspecialchars($guru['barcode']) ?>', '<?= htmlspecialchars(addslashes($guru['nama'])) ?>', '<?= htmlspecialchars($guru['nip'] ?: '-') ?>', '<?= htmlspecialchars(addslashes($guru['mata_pelajaran'] ?: '-')) ?>', '<?= $guru['id'] ?>')" 
                                            title="Lihat QR Code">
                                            <i class="bi bi-qr-code me-1"></i>Lihat
                                        </button>
                                        <a href="<?= BASEURL; ?>/admin_guru/cetak_barcode/<?= $guru['id']; ?>" target="_blank" class="btn btn-sm btn-secondary mb-1" title="Cetak Kartu Barcode">
                                            <i class="bi bi-printer me-1"></i>Cetak
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-sm btn-warning mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#formModal"
                                        onclick="setupModalUbah(<?= htmlspecialchars(json_encode($guru)); ?>)" title="Edit Data">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <a href="<?= BASEURL; ?>/admin_guru/hapus/<?= $guru['id']; ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Yakin ingin menghapus guru ini? Ini juga akan menghapus akun login mereka.');" title="Hapus Data">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($data['guru'])): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data guru terdaftar.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Tambah / Edit Guru -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="judulModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold text-primary" id="judulModal">Tambah Data Guru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="<?= BASEURL; ?>/admin_guru/tambah" method="post" id="formGuru">
            <input type="hidden" name="id" id="id">
            
            <div class="mb-3">
                <label for="nip" class="form-label fw-semibold">NIP / Username</label>
                <input type="text" class="form-control" id="nip" name="nip" required placeholder="Contoh: 198803032012011003">
                <small class="text-muted">NIP akan digunakan sebagai username login.</small>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label fw-semibold">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama" name="nama" required placeholder="Nama lengkap beserta gelar">
            </div>
            <div class="mb-3">
                <label for="mata_pelajaran" class="form-label fw-semibold">Mata Pelajaran / Jabatan</label>
                <input type="text" class="form-control" id="mata_pelajaran" name="mata_pelajaran" placeholder="Contoh: Pemrograman Dasar atau Kepala Sekolah">
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="barcode" class="form-label fw-semibold mb-0">Barcode ID</label>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 11px;" onclick="generateRandomBarcode()">
                        <i class="bi bi-magic me-1"></i>Isi Acak Otomatis
                    </button>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                    <input type="text" class="form-control font-monospace" id="barcode" name="barcode" placeholder="Contoh: BC-GURU-003 (atau kosongkan)">
                </div>
                <small class="text-muted">Bisa diketik manual, klik "Isi Acak Otomatis", atau <strong>kosongkan</strong> agar sistem membuatkannya secara otomatis.</small>
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" id="btnSubmit">Simpan Data</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tampil QR Code -->
<div class="modal fade" id="barcodeModal" tabindex="-1" aria-labelledby="barcodeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center rounded-4 shadow border-0 overflow-hidden">
      <div class="modal-header bg-primary text-white py-3">
        <h5 class="modal-title fs-6 fw-bold mb-0" id="barcodeModalLabel"><i class="bi bi-qr-code me-2"></i>QR Code Barcode Absensi</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
         <h5 id="barcodeGuruNama" class="mb-1 fw-bold text-dark"></h5>
         <p id="barcodeGuruSub" class="text-muted small mb-3"></p>
         
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
    document.getElementById('judulModal').innerHTML = '<i class="bi bi-person-plus-fill me-1"></i> Tambah Data Guru';
    document.getElementById('formGuru').action = '<?= BASEURL; ?>/admin_guru/tambah';
    document.getElementById('btnSubmit').innerHTML = 'Simpan Data';
    
    document.getElementById('id').value = '';
    document.getElementById('nip').value = '';
    document.getElementById('nama').value = '';
    document.getElementById('mata_pelajaran').value = '';
    document.getElementById('barcode').value = '';
}

function setupModalUbah(data) {
    document.getElementById('judulModal').innerHTML = '<i class="bi bi-pencil-square me-1"></i> Ubah Data Guru';
    document.getElementById('formGuru').action = '<?= BASEURL; ?>/admin_guru/ubah';
    document.getElementById('btnSubmit').innerHTML = 'Simpan Perubahan';
    
    document.getElementById('id').value = data.id;
    document.getElementById('nip').value = data.nip || '';
    document.getElementById('nama').value = data.nama || '';
    document.getElementById('mata_pelajaran').value = data.mata_pelajaran || '';
    document.getElementById('barcode').value = data.barcode || '';
}

function generateRandomBarcode() {
    const mapel = document.getElementById('mata_pelajaran').value.toLowerCase();
    const prefix = mapel.includes('kepala') ? 'BC-KEPSEK-' : 'BC-GURU-';
    const rand = Math.floor(1000 + Math.random() * 9000);
    document.getElementById('barcode').value = prefix + rand;
}

let qrcodeObj = null;
function showBarcode(barcode, nama, nip, mapel, guruId) {
    document.getElementById('barcodeGuruNama').innerText = nama;
    document.getElementById('barcodeGuruSub').innerText = 'NIP: ' + nip + ' • ' + mapel;
    document.getElementById('barcodeText').innerText = barcode;
    document.getElementById('btnCetakModal').href = '<?= BASEURL; ?>/admin_guru/cetak_barcode/' + guruId;
    
    let qrContainer = document.getElementById('qrcode');
    qrContainer.innerHTML = '';
    
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

// Simple Client-side Search
document.getElementById('searchInput').addEventListener('keyup', function() {
    var value = this.value.toLowerCase();
    var rows = document.querySelectorAll('#guruTable tbody tr');
    
    rows.forEach(function(row) {
        var text = row.innerText.toLowerCase();
        if(text.indexOf(value) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
