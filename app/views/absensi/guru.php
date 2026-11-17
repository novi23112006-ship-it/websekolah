<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            
            <h1 class="display-6 fw-bold mb-4">Terminal Absensi Guru</h1>
            
            <div class="row">
                <!-- Area Kamera QR/Barcode Scanner -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow h-100 border-primary">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-camera-video"></i> Scan Kamera
                        </div>
                        <div class="card-body p-3">
                            <div id="reader" width="100%"></div>
                            <small class="text-muted d-block mt-2">Arahkan QR Code atau Barcode Anda ke kamera.</small>
                        </div>
                    </div>
                </div>
                
                <!-- Area Input Manual -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow h-100 border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <i class="bi bi-keyboard"></i> Input Manual / Scanner USB
                        </div>
                        <div class="card-body p-4 d-flex flex-column justify-content-center">
                            
                            <form action="<?= BASEURL; ?>/absensi/guru" method="POST" id="formAbsensi">
                                <div class="mb-3">
                                    <input type="text" name="barcode" id="barcode" class="form-control form-control-lg text-center" placeholder="Ketik kode barcode..." autofocus required>
                                </div>
                                <button class="btn btn-primary btn-lg w-100" type="submit">Proses Absensi</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Area Hasil Scan -->
            <?php if(isset($data['error'])): ?>
                <div class="alert alert-danger h4 p-4 mt-3">
                    <?= $data['error'] ?>
                </div>
            <?php endif; ?>

            <?php if(isset($data['hasil'])): ?>
                <?php if($data['hasil']['status'] == false): ?>
                    <div class="alert alert-danger p-4 mt-3">
                        <h4 class="alert-heading fw-bold mb-0"><?= $data['hasil']['pesan'] ?></h4>
                        <?php if(isset($data['hasil']['guru'])): ?>
                            <hr>
                            <p class="mb-0 fs-5"><?= $data['hasil']['guru']['nama'] ?> (NIP: <?= $data['hasil']['guru']['nip'] ?: '-' ?>)</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert <?= ($data['hasil']['status_kehadiran'] == 'terlambat') ? 'alert-warning' : 'alert-success' ?> p-4 shadow-sm text-start mt-3">
                        <div class="text-center mb-3">
                            <h2 class="alert-heading fw-bold"><?= $data['hasil']['pesan'] ?></h2>
                        </div>
                        
                        <table class="table table-borderless table-sm fs-5 mb-0">
                            <tr>
                                <td width="35%">Nama Guru</td>
                                <td>: <strong><?= $data['hasil']['guru']['nama'] ?></strong></td>
                            </tr>
                            <tr>
                                <td>NIP</td>
                                <td>: <?= $data['hasil']['guru']['nip'] ?: '-' ?></td>
                            </tr>
                            <tr>
                                <td>Mata Pelajaran</td>
                                <td>: <?= $data['hasil']['guru']['mata_pelajaran'] ?: '-' ?></td>
                            </tr>
                            <tr>
                                <td>Jam Scan</td>
                                <td>: <strong><?= $data['hasil']['jam'] ?></strong></td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>: 
                                    <?php if($data['hasil']['status_kehadiran'] == 'terlambat'): ?>
                                        <span class="badge bg-danger fs-6">Terlambat</span>
                                    <?php else: ?>
                                        <span class="badge bg-success fs-6">Hadir (Tepat Waktu)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php endif; ?>
                
                <script>
                    setTimeout(function() {
                        document.getElementById('barcode').value = '';
                        document.getElementById('barcode').focus();
                    }, 3000);
                </script>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="<?= BASEURL ?>" class="text-decoration-none text-muted">Kembali ke Beranda</a>
            </div>

        </div>
    </div>
</div>

<!-- Library HTML5-QRCode via CDN -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function onScanSuccess(decodedText, decodedResult) {
        // Hentikan scan sementara agar tidak looping
        html5QrcodeScanner.clear();
        
        // Isi input dan submit form otomatis
        document.getElementById('barcode').value = decodedText;
        document.getElementById('formAbsensi').submit();
    }

    function onScanFailure(error) {
        // Abaikan error saat kamera sedang mendeteksi hal lain
    }

    // Inisialisasi Scanner
    let html5QrcodeScanner = new Html5QrcodeScanner(
      "reader",
      { fps: 10, qrbox: {width: 250, height: 250} },
      /* verbose= */ false);
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
</script>
