<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            
            <h1 class="display-6 fw-bold mb-4">Terminal Absensi Siswa</h1>
            
            <!-- Area Input RFID -->
            <div class="card shadow mb-4 border-primary">
                <div class="card-body p-5">
                    <h5 class="text-muted mb-4">Tempelkan Kartu RFID Anda</h5>
                    
                    <form action="<?= BASEURL; ?>/absensi/siswa" method="POST" id="formAbsensi">
                        <div class="input-group input-group-lg mb-3">
                            <span class="input-group-text" id="rfid-addon"><i class="bi bi-upc-scan"></i> RFID</span>
                            <input type="text" name="rfid_uid" id="rfid_uid" class="form-control" placeholder="Scan kartu di sini..." autofocus autocomplete="off" required>
                            <button class="btn btn-primary" type="submit">Absen</button>
                        </div>
                    </form>
                    
                    <small class="text-muted">Gunakan mesin scanner RFID. Fokus harus selalu berada di input field ini.</small>
                </div>
            </div>

            <!-- Area Hasil Scan -->
            <?php if(isset($data['error'])): ?>
                <div class="alert alert-danger h4 p-4">
                    <?= $data['error'] ?>
                </div>
            <?php endif; ?>

            <?php if(isset($data['hasil'])): ?>
                
                <?php if($data['hasil']['status'] == false): ?>
                    <div class="alert alert-danger p-4">
                        <h4 class="alert-heading fw-bold mb-0"><?= $data['hasil']['pesan'] ?></h4>
                        <?php if(isset($data['hasil']['siswa'])): ?>
                            <hr>
                            <p class="mb-0 fs-5"><?= $data['hasil']['siswa']['nama'] ?> (<?= $data['hasil']['siswa']['nis'] ?>)</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert <?= ($data['hasil']['status_kehadiran'] == 'terlambat') ? 'alert-warning' : 'alert-success' ?> p-4 shadow-sm text-start">
                        <div class="text-center mb-3">
                            <h2 class="alert-heading fw-bold"><?= $data['hasil']['pesan'] ?></h2>
                        </div>
                        
                        <table class="table table-borderless table-sm fs-5 mb-0">
                            <tr>
                                <td width="35%">Nama</td>
                                <td>: <strong><?= $data['hasil']['siswa']['nama'] ?></strong></td>
                            </tr>
                            <tr>
                                <td>NIS</td>
                                <td>: <?= $data['hasil']['siswa']['nis'] ?></td>
                            </tr>
                            <tr>
                                <td>Kelas</td>
                                <td>: <?= $data['hasil']['siswa']['nama_kelas'] ?></td>
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
                    // Mengembalikan fokus ke input field setelah 2 detik pesan tampil
                    setTimeout(function() {
                        document.getElementById('rfid_uid').value = '';
                        document.getElementById('rfid_uid').focus();
                    }, 2000);
                </script>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="<?= BASEURL ?>" class="text-decoration-none text-muted">Kembali ke Beranda</a>
            </div>

        </div>
    </div>
</div>

<script>
    // Memastikan input rfid_uid selalu mendapat fokus apabila pengguna klik diluar
    document.addEventListener('click', function(e) {
        if(e.target.id !== 'rfid_uid') {
            document.getElementById('rfid_uid').focus();
        }
    });
</script>
