<?php

class Absensi_model {
    private $table = 'absensi_siswa';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Helper evaluasi jadwal absensi aktif server-side (Siswa / Guru).
     * Memeriksa jam_mulai, jam_masuk (batas hadir), dan jam_akhir.
     * Menggunakan waktu server Asia/Jakarta.
     *
     * @param string $tipe_pengguna 'siswa' | 'guru'
     * @param int|null $kelas_id id kelas siswa (opsional)
     * @param string|null $custom_time waktu pengujian (opsional, default: waktu server)
     * @return array
     */
    public function evaluasiWaktuJadwal($tipe_pengguna, $kelas_id = null, $custom_time = null) {
        date_default_timezone_set('Asia/Jakarta');
        $jam_sekarang = $custom_time ? $custom_time : date('H:i:s');
        $tanggal_hari_ini = date('Y-m-d');

        $mapHari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $hari_ini = $mapHari[date('l')];

        // Ambil jadwal aktif melalui Jadwal_model
        require_once __DIR__ . '/Jadwal_model.php';
        $jadwalModel = new Jadwal_model();
        $jadwal = $jadwalModel->getActiveJadwal($tipe_pengguna, $hari_ini, $tanggal_hari_ini, $kelas_id);

        if (!$jadwal) {
            // Catat log detail teknis secara aman
            error_log("[JADWAL ABSENSI] Tidak ada jadwal aktif untuk tipe: {$tipe_pengguna}, hari: {$hari_ini}, tanggal: {$tanggal_hari_ini}, kelas_id: " . ($kelas_id ?? 'NULL'));
            return [
                'status' => false,
                'pesan'  => 'Jadwal absensi untuk hari ini belum diatur atau dinonaktifkan oleh Admin. Silakan hubungi Administrator.',
                'jadwal' => null,
                'jam'    => $jam_sekarang
            ];
        }

        $jam_mulai = $jadwal['jam_mulai'];
        $jam_masuk = $jadwal['jam_masuk'];
        $jam_akhir = $jadwal['jam_akhir'];

        $t_sekarang = strtotime($jam_sekarang);
        $t_mulai    = strtotime($jam_mulai);
        $t_masuk    = strtotime($jam_masuk);
        $t_akhir    = strtotime($jam_akhir);

        // 1. Sebelum jam mulai: tolak absensi
        if ($t_sekarang < $t_mulai) {
            return [
                'status' => false,
                'pesan'  => 'Waktu absensi belum dibuka. Absensi dibuka pukul ' . substr($jam_mulai, 0, 5) . ' WIB.',
                'jadwal' => $jadwal,
                'jam'    => $jam_sekarang
            ];
        }

        // 2. Setelah jam akhir: tolak absensi
        if ($t_sekarang > $t_akhir) {
            return [
                'status' => false,
                'pesan'  => 'Waktu absensi sudah ditutup. Batas akhir absensi adalah pukul ' . substr($jam_akhir, 0, 5) . ' WIB.',
                'jadwal' => $jadwal,
                'jam'    => $jam_sekarang
            ];
        }

        // 3. Dari jam mulai sampai batas waktu hadir: Hadir
        if ($t_sekarang <= $t_masuk) {
            return [
                'status'           => true,
                'status_kehadiran' => 'hadir',
                'pesan'            => 'Absensi berhasil dicatat (Tepat Waktu).',
                'jadwal'           => $jadwal,
                'jam'              => $jam_sekarang
            ];
        }

        // 4. Setelah batas waktu hadir sampai jam akhir: Terlambat
        return [
            'status'           => true,
            'status_kehadiran' => 'terlambat',
            'pesan'            => 'Absensi berhasil dicatat (Terlambat). Anda melewati batas waktu ' . substr($jam_masuk, 0, 5) . ' WIB.',
            'jadwal'           => $jadwal,
            'jam'              => $jam_sekarang
        ];
    }

    /**
     * Catat absensi siswa via RFID UID (Mendukung Absen Masuk & Absen Pulang).
     */
    public function catatAbsensiSiswa($rfid_uid, $custom_time = null, $mode = 'auto') {
        date_default_timezone_set('Asia/Jakarta');
        $mode = strtolower(trim($mode ?? 'auto'));
        if (!in_array($mode, ['auto', 'masuk', 'pulang'])) {
            $mode = 'auto';
        }

        // 1. Cari data siswa berdasarkan rfid_uid
        $this->db->query("SELECT siswa.*, kelas.nama_kelas FROM siswa LEFT JOIN kelas ON siswa.kelas_id = kelas.id WHERE siswa.rfid_uid = :rfid_uid AND siswa.status = 'active'");
        $this->db->bind('rfid_uid', $rfid_uid);
        $siswa = $this->db->single();

        if (!$siswa) {
            return [
                'status' => false,
                'pesan'  => 'RFID tidak terdaftar atau akun non-aktif.'
            ];
        }

        $jam_sekarang = $custom_time ? $custom_time : date('H:i:s');

        // 2. Cek status absensi hari ini di database
        $this->db->query("SELECT id, status, jam_masuk, jam_pulang, keterangan FROM {$this->table} WHERE siswa_id = :siswa_id AND tanggal = CURDATE()");
        $this->db->bind('siswa_id', $siswa['id']);
        $existing = $this->db->single();

        if ($existing) {
            if (in_array($existing['status'], ['sakit', 'izin', 'alpa'])) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Siswa sudah tercatat berstatus ' . ucfirst($existing['status']) . ' hari ini.',
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            if (!empty($existing['jam_pulang'])) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'sudah_lengkap'    => true,
                    'tipe_absen'       => 'selesai',
                    'pesan'            => 'Absensi siswa hari ini sudah lengkap. (Masuk: ' . substr($existing['jam_masuk'], 0, 5) . ' WIB, Pulang: ' . substr($existing['jam_pulang'], 0, 5) . ' WIB)',
                    'jam'              => $jam_sekarang,
                    'jam_masuk'        => $existing['jam_masuk'],
                    'jam_pulang'       => $existing['jam_pulang'],
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            if ($mode === 'masuk') {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Siswa sudah melakukan Absen Masuk hari ini pada pukul ' . substr($existing['jam_masuk'], 0, 5) . ' WIB.',
                    'jam'              => $existing['jam_masuk'],
                    'jam_masuk'        => $existing['jam_masuk'],
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            // Mode auto atau pulang -> Lakukan Absen Pulang
            $tMasuk = strtotime($existing['jam_masuk']);
            $tNow   = strtotime($jam_sekarang);
            if ($mode === 'auto' && ($tNow - $tMasuk >= 0 && $tNow - $tMasuk < 30)) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Siswa baru saja melakukan Absen Masuk (' . substr($existing['jam_masuk'], 0, 5) . ' WIB). Tunggu sejenak sebelum Absen Pulang.',
                    'jam'              => $existing['jam_masuk'],
                    'jam_masuk'        => $existing['jam_masuk'],
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            $keteranganPulang = !empty($existing['keterangan']) 
                ? $existing['keterangan'] . ' | Pulang: ' . substr($jam_sekarang, 0, 5) . ' WIB'
                : 'Absen Pulang via RFID (' . substr($jam_sekarang, 0, 5) . ' WIB)';

            try {
                $this->db->query("UPDATE {$this->table} SET jam_pulang = :jam_pulang, keterangan = :keterangan WHERE id = :id");
                $this->db->bind('jam_pulang', $jam_sekarang);
                $this->db->bind('keterangan', $keteranganPulang);
                $this->db->bind('id', $existing['id']);
                $this->db->execute();
            } catch (\PDOException $e) {
                $this->kirimNotifikasiKegagalanSafe('Pencatatan Absen Pulang Siswa (RFID) Gagal', 'Gagal update jam_pulang ke database.');
                error_log("[Absensi_model Error] catatAbsensiSiswa Pulang: " . $e->getMessage());
                return [
                    'status' => false,
                    'pesan'  => 'Terjadi kesalahan sistem saat menyimpan Absen Pulang.'
                ];
            }

            $this->kirimNotifikasiTelegramSiswaSafe($siswa, $jam_sekarang, $existing['status'], 'pulang', $existing['jam_masuk']);

            return [
                'status'           => true,
                'tipe_absen'       => 'pulang',
                'pesan'            => 'Absen Pulang berhasil dicatat pukul ' . substr($jam_sekarang, 0, 5) . ' WIB. Selamat beristirahat!',
                'jam'              => $jam_sekarang,
                'jam_masuk'        => $existing['jam_masuk'],
                'jam_pulang'       => $jam_sekarang,
                'status_kehadiran' => $existing['status'],
                'siswa'            => $siswa
            ];
        }

        // Belum ada data absensi hari ini
        if ($mode === 'pulang') {
            return [
                'status' => false,
                'pesan'  => 'Belum ada catatan Absen Masuk hari ini. Silakan lakukan Absen Masuk terlebih dahulu.',
                'siswa'  => $siswa
            ];
        }

        // Evaluasi jadwal absensi aktif siswa untuk Absen Masuk
        $eval = $this->evaluasiWaktuJadwal('siswa', $siswa['kelas_id'], $custom_time);
        if (!$eval['status']) {
            return [
                'status' => false,
                'pesan'  => $eval['pesan'],
                'siswa'  => $siswa,
                'jadwal' => $eval['jadwal'] ?? null
            ];
        }

        $jam_sekarang     = $eval['jam'];
        $status_kehadiran = $eval['status_kehadiran'];

        // Simpan Absen Masuk
        try {
            $queryInsert = "INSERT INTO {$this->table} (siswa_id, tanggal, jam_masuk, status, keterangan) 
                            VALUES (:siswa_id, CURDATE(), :jam_masuk, :status, :keterangan)";
            $this->db->query($queryInsert);
            $this->db->bind('siswa_id', $siswa['id']);
            $this->db->bind('jam_masuk', $jam_sekarang);
            $this->db->bind('status', $status_kehadiran);
            $this->db->bind('keterangan', 'Absen Masuk via RFID');
            $this->db->execute();
        } catch (\PDOException $e) {
            $this->kirimNotifikasiKegagalanSafe('Pencatatan Absensi Siswa (RFID) Gagal', 'Gagal menyimpan data absensi siswa ke database.');
            error_log("[Absensi_model Error] catatAbsensiSiswa: " . $e->getMessage());
            return [
                'status' => false,
                'pesan'  => 'Terjadi kesalahan sistem saat menyimpan data absensi.'
            ];
        }

        // Kirim notifikasi Telegram setelah data absensi berhasil tersimpan
        $this->kirimNotifikasiTelegramSiswaSafe($siswa, $jam_sekarang, $status_kehadiran, 'masuk');

        return [
            'status'           => true,
            'tipe_absen'       => 'masuk',
            'pesan'            => $eval['pesan'],
            'jam'              => $jam_sekarang,
            'jam_masuk'        => $jam_sekarang,
            'jam_pulang'       => null,
            'status_kehadiran' => $status_kehadiran,
            'siswa'            => $siswa,
            'jadwal'           => $eval['jadwal']
        ];
    }

    /**
     * Catat absensi siswa menggunakan barcode (QR Code kamera scanner terminal).
     * Mendukung Absen Masuk dan Absen Pulang secara otomatis atau sesuai mode.
     */
    public function catatAbsensiSiswaBarcode($barcode, $custom_time = null, $mode = 'auto') {
        date_default_timezone_set('Asia/Jakarta');
        $mode = strtolower(trim($mode ?? 'auto'));
        if (!in_array($mode, ['auto', 'masuk', 'pulang'])) {
            $mode = 'auto';
        }

        // 1. Cari data siswa berdasarkan siswa.barcode (bukan rfid_uid)
        $this->db->query("SELECT siswa.*, kelas.nama_kelas 
                          FROM siswa 
                          LEFT JOIN kelas ON siswa.kelas_id = kelas.id 
                          WHERE siswa.barcode = :barcode AND siswa.status = 'active'");
        $this->db->bind('barcode', $barcode);
        $siswa = $this->db->single();

        if (!$siswa) {
            return [
                'status' => false,
                'pesan'  => 'Barcode siswa tidak ditemukan atau akun non-aktif.'
            ];
        }

        $jam_sekarang = $custom_time ? $custom_time : date('H:i:s');

        // 2. Cek apakah sudah absen hari ini di database
        $this->db->query("SELECT id, status, jam_masuk, jam_pulang, keterangan 
                          FROM {$this->table} 
                          WHERE siswa_id = :siswa_id AND tanggal = CURDATE()");
        $this->db->bind('siswa_id', $siswa['id']);
        $existing = $this->db->single();

        if ($existing) {
            if (in_array($existing['status'], ['sakit', 'izin', 'alpa'])) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Siswa sudah tercatat berstatus ' . ucfirst($existing['status']) . ' hari ini.',
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            if (!empty($existing['jam_pulang'])) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'sudah_lengkap'    => true,
                    'tipe_absen'       => 'selesai',
                    'pesan'            => 'Absensi siswa hari ini sudah lengkap. (Masuk: ' . substr($existing['jam_masuk'], 0, 5) . ' WIB, Pulang: ' . substr($existing['jam_pulang'], 0, 5) . ' WIB)',
                    'jam'              => $jam_sekarang,
                    'jam_masuk'        => $existing['jam_masuk'],
                    'jam_pulang'       => $existing['jam_pulang'],
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            if ($mode === 'masuk') {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Siswa sudah melakukan Absen Masuk hari ini pada pukul ' . substr($existing['jam_masuk'], 0, 5) . ' WIB.',
                    'jam'              => $existing['jam_masuk'],
                    'jam_masuk'        => $existing['jam_masuk'],
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            // Mode auto atau pulang -> Lakukan Absen Pulang
            $tMasuk = strtotime($existing['jam_masuk']);
            $tNow   = strtotime($jam_sekarang);
            if ($mode === 'auto' && ($tNow - $tMasuk >= 0 && $tNow - $tMasuk < 30)) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Siswa baru saja melakukan Absen Masuk (' . substr($existing['jam_masuk'], 0, 5) . ' WIB). Tunggu sejenak sebelum Absen Pulang.',
                    'jam'              => $existing['jam_masuk'],
                    'jam_masuk'        => $existing['jam_masuk'],
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            $keteranganPulang = !empty($existing['keterangan']) 
                ? $existing['keterangan'] . ' | Pulang: ' . substr($jam_sekarang, 0, 5) . ' WIB'
                : 'Absen Pulang via Kamera (' . substr($jam_sekarang, 0, 5) . ' WIB)';

            try {
                $this->db->query("UPDATE {$this->table} SET jam_pulang = :jam_pulang, keterangan = :keterangan WHERE id = :id");
                $this->db->bind('jam_pulang', $jam_sekarang);
                $this->db->bind('keterangan', $keteranganPulang);
                $this->db->bind('id', $existing['id']);
                $this->db->execute();
            } catch (\PDOException $e) {
                $this->kirimNotifikasiKegagalanSafe('Pencatatan Absen Pulang Siswa (Barcode) Gagal', 'Gagal update jam_pulang barcode siswa ke database.');
                error_log("[Absensi_model Error] catatAbsensiSiswaBarcode Pulang: " . $e->getMessage());
                return [
                    'status' => false,
                    'pesan'  => 'Terjadi kesalahan sistem saat menyimpan Absen Pulang.'
                ];
            }

            // Kirim notifikasi Telegram untuk Absen Pulang
            $this->kirimNotifikasiTelegramSiswaSafe($siswa, $jam_sekarang, $existing['status'], 'pulang', $existing['jam_masuk']);

            return [
                'status'           => true,
                'tipe_absen'       => 'pulang',
                'pesan'            => 'Absen Pulang berhasil dicatat pukul ' . substr($jam_sekarang, 0, 5) . ' WIB. Selamat beristirahat!',
                'jam'              => $jam_sekarang,
                'jam_masuk'        => $existing['jam_masuk'],
                'jam_pulang'       => $jam_sekarang,
                'status_kehadiran' => $existing['status'],
                'siswa'            => $siswa
            ];
        }

        // Belum ada absensi hari ini
        if ($mode === 'pulang') {
            return [
                'status' => false,
                'pesan'  => 'Belum ada catatan Absen Masuk hari ini. Silakan lakukan Absen Masuk terlebih dahulu.',
                'siswa'  => $siswa
            ];
        }

        // Evaluasi jadwal absensi aktif siswa untuk Absen Masuk
        $eval = $this->evaluasiWaktuJadwal('siswa', $siswa['kelas_id'], $custom_time);
        if (!$eval['status']) {
            return [
                'status' => false,
                'pesan'  => $eval['pesan'],
                'siswa'  => $siswa,
                'jadwal' => $eval['jadwal'] ?? null
            ];
        }

        $jam_sekarang     = $eval['jam'];
        $status_kehadiran = $eval['status_kehadiran'];

        // Simpan absensi masuk
        try {
            $queryInsert = "INSERT INTO {$this->table} (siswa_id, tanggal, jam_masuk, status, keterangan) 
                            VALUES (:siswa_id, CURDATE(), :jam_masuk, :status, :keterangan)";
            $this->db->query($queryInsert);
            $this->db->bind('siswa_id', $siswa['id']);
            $this->db->bind('jam_masuk', $jam_sekarang);
            $this->db->bind('status', $status_kehadiran);
            $this->db->bind('keterangan', 'Absen Masuk via Barcode/QR Kamera');
            $this->db->execute();
        } catch (\PDOException $e) {
            $this->kirimNotifikasiKegagalanSafe('Pencatatan Absensi Siswa (Barcode) Gagal', 'Gagal menyimpan data absensi barcode siswa ke database.');
            error_log("[Absensi_model Error] catatAbsensiSiswaBarcode: " . $e->getMessage());
            return [
                'status' => false,
                'pesan'  => 'Terjadi kesalahan sistem saat menyimpan data absensi.'
            ];
        }

        // Kirim notifikasi Telegram setelah data absensi berhasil tersimpan
        $this->kirimNotifikasiTelegramSiswaSafe($siswa, $jam_sekarang, $status_kehadiran, 'masuk');

        return [
            'status'           => true,
            'tipe_absen'       => 'masuk',
            'pesan'            => $eval['pesan'],
            'jam'              => $jam_sekarang,
            'jam_masuk'        => $jam_sekarang,
            'jam_pulang'       => null,
            'status_kehadiran' => $status_kehadiran,
            'siswa'            => $siswa,
            'jadwal'           => $eval['jadwal']
        ];
    }

    /**
     * Catat absensi siswa mandiri oleh siswa yang sedang login.
     * Mengambil identitas siswa dari $user_id (session) dan memvalidasi barcode milik akun tersebut.
     * Mendukung Absen Masuk dan Absen Pulang.
     */
    public function catatAbsensiSiswaMandiri($user_id, $barcode, $custom_time = null, $mode = 'auto') {
        date_default_timezone_set('Asia/Jakarta');
        $mode = strtolower(trim($mode ?? 'auto'));
        if (!in_array($mode, ['auto', 'masuk', 'pulang'])) {
            $mode = 'auto';
        }

        // 1. Ambil identitas siswa dari user_id session (bukan dari input browser)
        $this->db->query("SELECT siswa.*, kelas.nama_kelas 
                          FROM siswa 
                          LEFT JOIN kelas ON siswa.kelas_id = kelas.id 
                          WHERE siswa.user_id = :user_id AND siswa.status = 'active'");
        $this->db->bind('user_id', $user_id);
        $siswa = $this->db->single();

        if (!$siswa) {
            return [
                'status' => false,
                'pesan'  => 'Data siswa tidak ditemukan atau akun Anda berstatus non-aktif.'
            ];
        }

        // 2. Validasi Barcode/QR: pastikan barcode siswa terdaftar dan cocok dengan akun yang login
        if (empty($siswa['barcode'])) {
            return [
                'status' => false,
                'pesan'  => 'Barcode kartu Anda belum terdaftar di sistem. Silakan hubungi admin sekolah.'
            ];
        }

        if ($barcode !== $siswa['barcode']) {
            return [
                'status' => false,
                'pesan'  => 'Barcode/QR tidak valid.'
            ];
        }

        $jam_sekarang = $custom_time ? $custom_time : date('H:i:s');

        // 3. Cek apakah sudah absen hari ini
        $this->db->query("SELECT id, status, jam_masuk, jam_pulang, keterangan FROM {$this->table} WHERE siswa_id = :siswa_id AND tanggal = CURDATE()");
        $this->db->bind('siswa_id', $siswa['id']);
        $existing = $this->db->single();

        if ($existing) {
            if (in_array($existing['status'], ['sakit', 'izin', 'alpa'])) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Absensi hari ini tercatat ' . ucfirst($existing['status']) . '.',
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            if (!empty($existing['jam_pulang'])) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'sudah_lengkap'    => true,
                    'tipe_absen'       => 'selesai',
                    'pesan'            => 'Absensi Anda hari ini sudah lengkap. (Masuk: ' . substr($existing['jam_masuk'], 0, 5) . ' WIB, Pulang: ' . substr($existing['jam_pulang'], 0, 5) . ' WIB)',
                    'jam'              => $jam_sekarang,
                    'jam_masuk'        => $existing['jam_masuk'],
                    'jam_pulang'       => $existing['jam_pulang'],
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            if ($mode === 'masuk') {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Anda sudah melakukan Absen Masuk hari ini pada pukul ' . substr($existing['jam_masuk'], 0, 5) . ' WIB.',
                    'jam'              => $existing['jam_masuk'],
                    'jam_masuk'        => $existing['jam_masuk'],
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            // Mode auto atau pulang -> Lakukan Absen Pulang
            $tMasuk = strtotime($existing['jam_masuk']);
            $tNow   = strtotime($jam_sekarang);
            if ($mode === 'auto' && ($tNow - $tMasuk >= 0 && $tNow - $tMasuk < 30)) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Anda baru saja melakukan Absen Masuk (' . substr($existing['jam_masuk'], 0, 5) . ' WIB). Tunggu sejenak sebelum Absen Pulang.',
                    'jam'              => $existing['jam_masuk'],
                    'jam_masuk'        => $existing['jam_masuk'],
                    'status_kehadiran' => $existing['status'],
                    'siswa'            => $siswa
                ];
            }

            $keteranganPulang = !empty($existing['keterangan']) 
                ? $existing['keterangan'] . ' | Pulang: ' . substr($jam_sekarang, 0, 5) . ' WIB'
                : 'Absen Pulang Mandiri Dashboard (' . substr($jam_sekarang, 0, 5) . ' WIB)';

            try {
                $this->db->query("UPDATE {$this->table} SET jam_pulang = :jam_pulang, keterangan = :keterangan WHERE id = :id");
                $this->db->bind('jam_pulang', $jam_sekarang);
                $this->db->bind('keterangan', $keteranganPulang);
                $this->db->bind('id', $existing['id']);
                $this->db->execute();
            } catch (\PDOException $e) {
                $this->kirimNotifikasiKegagalanSafe('Pencatatan Absen Pulang Mandiri Siswa Gagal', 'Gagal update jam_pulang mandiri siswa ke database.');
                error_log("[Absensi_model Error] catatAbsensiSiswaMandiri Pulang: " . $e->getMessage());
                return [
                    'status' => false,
                    'pesan'  => 'Terjadi kesalahan sistem saat menyimpan Absen Pulang.'
                ];
            }

            $this->kirimNotifikasiTelegramSiswaSafe($siswa, $jam_sekarang, $existing['status'], 'pulang', $existing['jam_masuk']);

            return [
                'status'           => true,
                'tipe_absen'       => 'pulang',
                'pesan'            => 'Absen Pulang berhasil dicatat pukul ' . substr($jam_sekarang, 0, 5) . ' WIB. Selamat beristirahat!',
                'jam'              => $jam_sekarang,
                'jam_masuk'        => $existing['jam_masuk'],
                'jam_pulang'       => $jam_sekarang,
                'status_kehadiran' => $existing['status'],
                'siswa'            => $siswa
            ];
        }

        // Belum ada data absensi hari ini
        if ($mode === 'pulang') {
            return [
                'status' => false,
                'pesan'  => 'Belum ada catatan Absen Masuk hari ini. Silakan lakukan Absen Masuk terlebih dahulu.',
                'siswa'  => $siswa
            ];
        }

        // Evaluasi jadwal absensi aktif siswa untuk Absen Masuk
        $eval = $this->evaluasiWaktuJadwal('siswa', $siswa['kelas_id'], $custom_time);
        if (!$eval['status']) {
            return [
                'status' => false,
                'pesan'  => $eval['pesan'],
                'siswa'  => $siswa,
                'jadwal' => $eval['jadwal'] ?? null
            ];
        }

        $jam_sekarang     = $eval['jam'];
        $status_kehadiran = $eval['status_kehadiran'];

        // Simpan Absen Masuk ke database
        try {
            $queryInsert = "INSERT INTO {$this->table} (siswa_id, tanggal, jam_masuk, status, keterangan) 
                            VALUES (:siswa_id, CURDATE(), :jam_masuk, :status, :keterangan)";
            $this->db->query($queryInsert);
            $this->db->bind('siswa_id', $siswa['id']);
            $this->db->bind('jam_masuk', $jam_sekarang);
            $this->db->bind('status', $status_kehadiran);
            $this->db->bind('keterangan', 'Absen Masuk Mandiri via Scan Dashboard');
            $this->db->execute();
        } catch (\PDOException $e) {
            $this->kirimNotifikasiKegagalanSafe('Pencatatan Absensi Mandiri Siswa Gagal', 'Gagal menyimpan absensi mandiri siswa ke database.');
            error_log("[Absensi_model Error] catatAbsensiSiswaMandiri: " . $e->getMessage());
            return [
                'status' => false,
                'pesan'  => 'Terjadi kesalahan sistem saat menyimpan data absensi.'
            ];
        }

        // Kirim notifikasi Telegram setelah data absensi berhasil tersimpan
        $this->kirimNotifikasiTelegramSiswaSafe($siswa, $jam_sekarang, $status_kehadiran, 'masuk');

        return [
            'status'           => true,
            'tipe_absen'       => 'masuk',
            'pesan'            => $eval['pesan'],
            'jam'              => $jam_sekarang,
            'jam_masuk'        => $jam_sekarang,
            'jam_pulang'       => null,
            'status_kehadiran' => $status_kehadiran,
            'siswa'            => $siswa,
            'jadwal'           => $eval['jadwal']
        ];
    }

    /**
     * Catat absensi guru via Barcode (Terminal)
     * Mendukung Absen Masuk dan Absen Pulang.
     */
    public function catatAbsensiGuru($barcode, $custom_time = null, $mode = 'auto') {
        date_default_timezone_set('Asia/Jakarta');
        $mode = strtolower(trim($mode ?? 'auto'));
        if (!in_array($mode, ['auto', 'masuk', 'pulang'])) {
            $mode = 'auto';
        }

        // 1. Cari data guru berdasarkan barcode
        $this->db->query("SELECT * FROM guru WHERE barcode = :barcode AND status = 'active'");
        $this->db->bind('barcode', $barcode);
        $guru = $this->db->single();

        if (!$guru) {
            return [
                'status' => false,
                'pesan'  => 'Barcode tidak terdaftar atau akun non-aktif.'
            ];
        }

        $jam_sekarang = $custom_time ? $custom_time : date('H:i:s');

        // 2. Cek apakah sudah absen hari ini
        $this->db->query("SELECT id, status, jam_scan, jam_pulang, keterangan FROM absensi_guru WHERE guru_id = :guru_id AND tanggal = CURDATE()");
        $this->db->bind('guru_id', $guru['id']);
        $existing = $this->db->single();

        if ($existing) {
            if (in_array($existing['status'], ['sakit', 'izin', 'alpa'])) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Bapak/Ibu Guru sudah tercatat berstatus ' . ucfirst($existing['status']) . ' hari ini.',
                    'status_kehadiran' => $existing['status'],
                    'guru'             => $guru
                ];
            }

            if (!empty($existing['jam_pulang'])) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'sudah_lengkap'    => true,
                    'tipe_absen'       => 'selesai',
                    'pesan'            => 'Absensi dewan guru hari ini sudah lengkap. (Masuk: ' . substr($existing['jam_scan'], 0, 5) . ' WIB, Pulang: ' . substr($existing['jam_pulang'], 0, 5) . ' WIB)',
                    'jam'              => $jam_sekarang,
                    'jam_scan'         => $existing['jam_scan'],
                    'jam_masuk'        => $existing['jam_scan'],
                    'jam_pulang'       => $existing['jam_pulang'],
                    'status_kehadiran' => $existing['status'],
                    'guru'             => $guru
                ];
            }

            if ($mode === 'masuk') {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Bapak/Ibu Guru sudah melakukan Absen Masuk hari ini pada pukul ' . substr($existing['jam_scan'], 0, 5) . ' WIB.',
                    'jam'              => $existing['jam_scan'],
                    'jam_scan'         => $existing['jam_scan'],
                    'jam_masuk'        => $existing['jam_scan'],
                    'status_kehadiran' => $existing['status'],
                    'guru'             => $guru
                ];
            }

            // Mode auto atau pulang -> Lakukan Absen Pulang
            $tMasuk = strtotime($existing['jam_scan']);
            $tNow   = strtotime($jam_sekarang);
            if ($mode === 'auto' && ($tNow - $tMasuk >= 0 && $tNow - $tMasuk < 30)) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Bapak/Ibu Guru baru saja melakukan Absen Masuk (' . substr($existing['jam_scan'], 0, 5) . ' WIB). Tunggu sejenak sebelum Absen Pulang.',
                    'jam'              => $existing['jam_scan'],
                    'jam_scan'         => $existing['jam_scan'],
                    'jam_masuk'        => $existing['jam_scan'],
                    'status_kehadiran' => $existing['status'],
                    'guru'             => $guru
                ];
            }

            $keteranganPulang = !empty($existing['keterangan']) 
                ? $existing['keterangan'] . ' | Pulang: ' . substr($jam_sekarang, 0, 5) . ' WIB'
                : 'Absen Pulang via Barcode (' . substr($jam_sekarang, 0, 5) . ' WIB)';

            try {
                $this->db->query("UPDATE absensi_guru SET jam_pulang = :jam_pulang, keterangan = :keterangan WHERE id = :id");
                $this->db->bind('jam_pulang', $jam_sekarang);
                $this->db->bind('keterangan', $keteranganPulang);
                $this->db->bind('id', $existing['id']);
                $this->db->execute();
            } catch (\PDOException $e) {
                $this->kirimNotifikasiKegagalanSafe('Pencatatan Absen Pulang Guru Gagal', 'Gagal update jam_pulang guru ke database.');
                error_log("[Absensi_model Error] catatAbsensiGuru Pulang: " . $e->getMessage());
                return [
                    'status' => false,
                    'pesan'  => 'Terjadi kesalahan sistem saat menyimpan data Absen Pulang Guru.'
                ];
            }

            $this->kirimNotifikasiTelegramGuruSafe($guru, $jam_sekarang, $existing['status'], 'pulang', $existing['jam_scan']);

            return [
                'status'           => true,
                'tipe_absen'       => 'pulang',
                'pesan'            => 'Absen Pulang Guru berhasil dicatat pukul ' . substr($jam_sekarang, 0, 5) . ' WIB. Terima kasih atas dedikasi mengajar hari ini!',
                'jam'              => $jam_sekarang,
                'jam_scan'         => $existing['jam_scan'],
                'jam_masuk'        => $existing['jam_scan'],
                'jam_pulang'       => $jam_sekarang,
                'status_kehadiran' => $existing['status'],
                'guru'             => $guru
            ];
        }

        // Belum ada data absensi hari ini
        if ($mode === 'pulang') {
            return [
                'status' => false,
                'pesan'  => 'Belum ada catatan Absen Masuk hari ini. Silakan lakukan Absen Masuk terlebih dahulu.',
                'guru'   => $guru
            ];
        }

        // Evaluasi jadwal absensi aktif guru untuk Absen Masuk
        $eval = $this->evaluasiWaktuJadwal('guru', null, $custom_time);
        if (!$eval['status']) {
            return [
                'status' => false,
                'pesan'  => $eval['pesan'],
                'guru'   => $guru,
                'jadwal' => $eval['jadwal'] ?? null
            ];
        }

        $jam_sekarang     = $eval['jam'];
        $status_kehadiran = $eval['status_kehadiran'];

        // Simpan Absen Masuk
        try {
            $queryInsert = "INSERT INTO absensi_guru (guru_id, tanggal, jam_scan, status, keterangan) 
                            VALUES (:guru_id, CURDATE(), :jam_scan, :status, :keterangan)";
            $this->db->query($queryInsert);
            $this->db->bind('guru_id', $guru['id']);
            $this->db->bind('jam_scan', $jam_sekarang);
            $this->db->bind('status', $status_kehadiran);
            $this->db->bind('keterangan', 'Absen Masuk via Barcode/QR');
            $this->db->execute();
        } catch (\PDOException $e) {
            $this->kirimNotifikasiKegagalanSafe('Pencatatan Absensi Guru Gagal', 'Gagal menyimpan data absensi guru ke database.');
            error_log("[Absensi_model Error] catatAbsensiGuru: " . $e->getMessage());
            return [
                'status' => false,
                'pesan'  => 'Terjadi kesalahan sistem saat menyimpan data absensi.'
            ];
        }

        // Kirim notifikasi Telegram setelah data absensi guru berhasil tersimpan
        $this->kirimNotifikasiTelegramGuruSafe($guru, $jam_sekarang, $status_kehadiran, 'masuk');

        return [
            'status'           => true,
            'tipe_absen'       => 'masuk',
            'pesan'            => $eval['pesan'],
            'jam'              => $jam_sekarang,
            'jam_scan'         => $jam_sekarang,
            'jam_masuk'        => $jam_sekarang,
            'jam_pulang'       => null,
            'status_kehadiran' => $status_kehadiran,
            'guru'             => $guru,
            'jadwal'           => $eval['jadwal']
        ];
    }

    /**
     * Catat absensi guru via kamera barcode (AJAX).
     * Menggunakan guru.barcode — TIDAK mencari siswa.barcode.
     * Mendukung Absen Masuk dan Absen Pulang.
     */
    public function catatAbsensiGuruBarcode($barcode, $custom_time = null, $mode = 'auto') {
        date_default_timezone_set('Asia/Jakarta');
        $mode = strtolower(trim($mode ?? 'auto'));
        if (!in_array($mode, ['auto', 'masuk', 'pulang'])) {
            $mode = 'auto';
        }

        // 1. Cari guru berdasarkan guru.barcode (bukan tabel siswa)
        $this->db->query("SELECT * FROM guru WHERE barcode = :barcode AND status = 'active'");
        $this->db->bind('barcode', $barcode);
        $guru = $this->db->single();

        if (!$guru) {
            return [
                'status' => false,
                'pesan'  => 'Barcode guru tidak ditemukan atau akun non-aktif.'
            ];
        }

        $jam_sekarang = $custom_time ? $custom_time : date('H:i:s');

        // 2. Cek absensi hari ini (database-level)
        $this->db->query("SELECT id, status, jam_scan, jam_pulang, keterangan FROM absensi_guru WHERE guru_id = :guru_id AND tanggal = CURDATE()");
        $this->db->bind('guru_id', $guru['id']);
        $existing = $this->db->single();

        if ($existing) {
            if (in_array($existing['status'], ['sakit', 'izin', 'alpa'])) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Bapak/Ibu Guru sudah tercatat berstatus ' . ucfirst($existing['status']) . ' hari ini.',
                    'status_kehadiran' => $existing['status'],
                    'guru'             => $guru
                ];
            }

            if (!empty($existing['jam_pulang'])) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'sudah_lengkap'    => true,
                    'tipe_absen'       => 'selesai',
                    'pesan'            => 'Absensi dewan guru hari ini sudah lengkap. (Masuk: ' . substr($existing['jam_scan'], 0, 5) . ' WIB, Pulang: ' . substr($existing['jam_pulang'], 0, 5) . ' WIB)',
                    'jam'              => $jam_sekarang,
                    'jam_scan'         => $existing['jam_scan'],
                    'jam_masuk'        => $existing['jam_scan'],
                    'jam_pulang'       => $existing['jam_pulang'],
                    'status_kehadiran' => $existing['status'],
                    'guru'             => $guru
                ];
            }

            if ($mode === 'masuk') {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Bapak/Ibu Guru sudah melakukan Absen Masuk hari ini pada pukul ' . substr($existing['jam_scan'], 0, 5) . ' WIB.',
                    'jam'              => $existing['jam_scan'],
                    'jam_scan'         => $existing['jam_scan'],
                    'jam_masuk'        => $existing['jam_scan'],
                    'status_kehadiran' => $existing['status'],
                    'guru'             => $guru
                ];
            }

            // Mode auto atau pulang -> Lakukan Absen Pulang
            $tMasuk = strtotime($existing['jam_scan']);
            $tNow   = strtotime($jam_sekarang);
            if ($mode === 'auto' && ($tNow - $tMasuk >= 0 && $tNow - $tMasuk < 30)) {
                return [
                    'status'           => false,
                    'sudah_absen'      => true,
                    'pesan'            => 'Bapak/Ibu Guru baru saja melakukan Absen Masuk (' . substr($existing['jam_scan'], 0, 5) . ' WIB). Tunggu sejenak sebelum Absen Pulang.',
                    'jam'              => $existing['jam_scan'],
                    'jam_scan'         => $existing['jam_scan'],
                    'jam_masuk'        => $existing['jam_scan'],
                    'status_kehadiran' => $existing['status'],
                    'guru'             => $guru
                ];
            }

            $keteranganPulang = !empty($existing['keterangan']) 
                ? $existing['keterangan'] . ' | Pulang: ' . substr($jam_sekarang, 0, 5) . ' WIB'
                : 'Absen Pulang via Barcode Kamera (' . substr($jam_sekarang, 0, 5) . ' WIB)';

            try {
                $this->db->query("UPDATE absensi_guru SET jam_pulang = :jam_pulang, keterangan = :keterangan WHERE id = :id");
                $this->db->bind('jam_pulang', $jam_sekarang);
                $this->db->bind('keterangan', $keteranganPulang);
                $this->db->bind('id', $existing['id']);
                $this->db->execute();
            } catch (\PDOException $e) {
                $this->kirimNotifikasiKegagalanSafe('Pencatatan Absen Pulang Guru (Barcode) Gagal', 'Gagal update jam_pulang barcode guru ke database.');
                error_log("[Absensi_model Error] catatAbsensiGuruBarcode Pulang: " . $e->getMessage());
                return [
                    'status' => false,
                    'pesan'  => 'Terjadi kesalahan sistem saat menyimpan data Absen Pulang Guru.'
                ];
            }

            // Kirim notifikasi Telegram setelah data absensi pulang guru berhasil tersimpan
            $this->kirimNotifikasiTelegramGuruSafe($guru, $jam_sekarang, $existing['status'], 'pulang', $existing['jam_scan']);

            return [
                'status'           => true,
                'tipe_absen'       => 'pulang',
                'pesan'            => 'Absen Pulang Guru berhasil dicatat pukul ' . substr($jam_sekarang, 0, 5) . ' WIB. Terima kasih atas dedikasi mengajar hari ini!',
                'jam'              => $jam_sekarang,
                'jam_scan'         => $existing['jam_scan'],
                'jam_masuk'        => $existing['jam_scan'],
                'jam_pulang'       => $jam_sekarang,
                'status_kehadiran' => $existing['status'],
                'guru'             => $guru
            ];
        }

        // Belum ada data absensi hari ini
        if ($mode === 'pulang') {
            return [
                'status' => false,
                'pesan'  => 'Belum ada catatan Absen Masuk hari ini. Silakan lakukan Absen Masuk terlebih dahulu.',
                'guru'   => $guru
            ];
        }

        // Evaluasi jadwal absensi aktif guru untuk Absen Masuk
        $eval = $this->evaluasiWaktuJadwal('guru', null, $custom_time);
        if (!$eval['status']) {
            return [
                'status' => false,
                'pesan'  => $eval['pesan'],
                'guru'   => $guru,
                'jadwal' => $eval['jadwal'] ?? null
            ];
        }

        $jam_sekarang     = $eval['jam'];
        $status_kehadiran = $eval['status_kehadiran'];

        // Simpan Absen Masuk
        try {
            $queryInsert = "INSERT INTO absensi_guru (guru_id, tanggal, jam_scan, status, keterangan)
                            VALUES (:guru_id, CURDATE(), :jam_scan, :status, :keterangan)";
            $this->db->query($queryInsert);
            $this->db->bind('guru_id', $guru['id']);
            $this->db->bind('jam_scan', $jam_sekarang);
            $this->db->bind('status', $status_kehadiran);
            $this->db->bind('keterangan', 'Absen Masuk via Barcode/QR Kamera');
            $this->db->execute();
        } catch (\PDOException $e) {
            $this->kirimNotifikasiKegagalanSafe('Pencatatan Absensi Guru (Barcode) Gagal', 'Gagal menyimpan absensi barcode guru ke database.');
            error_log("[Absensi_model Error] catatAbsensiGuruBarcode: " . $e->getMessage());
            return [
                'status' => false,
                'pesan'  => 'Terjadi kesalahan sistem saat menyimpan data absensi.'
            ];
        }

        // Kirim notifikasi Telegram setelah data absensi guru berhasil tersimpan
        $this->kirimNotifikasiTelegramGuruSafe($guru, $jam_sekarang, $status_kehadiran, 'masuk');

        return [
            'status'           => true,
            'tipe_absen'       => 'masuk',
            'pesan'            => $eval['pesan'],
            'jam'              => $jam_sekarang,
            'jam_scan'         => $jam_sekarang,
            'jam_masuk'        => $jam_sekarang,
            'jam_pulang'       => null,
            'status_kehadiran' => $status_kehadiran,
            'guru'             => $guru,
            'jadwal'           => $eval['jadwal']
        ];
    }

    /**
     * Pengajuan Sakit / Izin oleh Siswa dari Dashboard
     *
     * @param int $user_id
     * @param array $data
     * @param array|null $file
     * @return array
     */
    public function ajukanIzinSiswa($user_id, $data, $file = null) {
        date_default_timezone_set('Asia/Jakarta');

        // 1. Ambil data siswa berdasarkan user_id
        $this->db->query("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id WHERE s.user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $siswa = $this->db->single();

        if (!$siswa) {
            return ['status' => false, 'pesan' => 'Data profil siswa tidak ditemukan.'];
        }

        // 2. Validasi status
        $status = strtolower(trim($data['status'] ?? ''));
        if (!in_array($status, ['sakit', 'izin'])) {
            return ['status' => false, 'pesan' => 'Kategori pengajuan harus berupa Sakit atau Izin.'];
        }

        // 3. Validasi tanggal
        $tgl_mulai = trim($data['tanggal_mulai'] ?? '');
        $tgl_selesai = trim($data['tanggal_selesai'] ?? '');
        if (empty($tgl_selesai)) {
            $tgl_selesai = $tgl_mulai;
        }

        if (empty($tgl_mulai) || !strtotime($tgl_mulai)) {
            return ['status' => false, 'pesan' => 'Tanggal mulai tidak valid.'];
        }
        if (!strtotime($tgl_selesai)) {
            return ['status' => false, 'pesan' => 'Tanggal selesai tidak valid.'];
        }

        $time_mulai = strtotime($tgl_mulai);
        $time_selesai = strtotime($tgl_selesai);

        if ($time_mulai > $time_selesai) {
            return ['status' => false, 'pesan' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'];
        }

        $diff_days = ($time_selesai - $time_mulai) / (60 * 60 * 24);
        if ($diff_days > 14) {
            return ['status' => false, 'pesan' => 'Rentang pengajuan maksimal adalah 14 hari.'];
        }

        // 4. Validasi keterangan
        $keterangan = trim($data['keterangan'] ?? '');
        if (empty($keterangan) || strlen($keterangan) < 5) {
            return ['status' => false, 'pesan' => 'Keterangan atau alasan pengajuan minimal 5 karakter.'];
        }

        // 5. Handle file upload (opsional)
        $lampiran_info = '';
        if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
            $fileInfo = pathinfo($file['name']);
            $ext = strtolower($fileInfo['extension'] ?? '');

            if (!in_array($ext, $allowedExts)) {
                return ['status' => false, 'pesan' => 'Format berkas lampiran harus berupa JPG, PNG, atau PDF.'];
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                return ['status' => false, 'pesan' => 'Ukuran berkas lampiran maksimal 2MB.'];
            }

            $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/surat/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newFileName = 'surat_siswa_' . $siswa['id'] . '_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
            $targetPath = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $lampiran_info = ' (Lampiran: ' . $newFileName . ')';
            }
        }

        $full_keterangan = $keterangan . $lampiran_info;
        $jam_sekarang = date('H:i:s');

        // 6. Cek apakah ada tanggal yang sudah memiliki absensi
        $current = $time_mulai;
        $dates_to_insert = [];
        $conflicted_dates = [];

        while ($current <= $time_selesai) {
            $tgl = date('Y-m-d', $current);
            $this->db->query("SELECT id, status FROM absensi_siswa WHERE siswa_id = :siswa_id AND tanggal = :tanggal");
            $this->db->bind('siswa_id', $siswa['id']);
            $this->db->bind('tanggal', $tgl);
            $existing = $this->db->single();

            if ($existing) {
                $conflicted_dates[] = date('d/m/Y', $current) . ' (' . ucfirst($existing['status']) . ')';
            } else {
                $dates_to_insert[] = $tgl;
            }
            $current = strtotime('+1 day', $current);
        }

        if (!empty($conflicted_dates)) {
            return [
                'status' => false,
                'pesan'  => 'Gagal mengajukan: Sudah terdapat absensi pada tanggal: ' . implode(', ', $conflicted_dates) . '.'
            ];
        }

        if (empty($dates_to_insert)) {
            return ['status' => false, 'pesan' => 'Tidak ada tanggal yang dapat diproses.'];
        }

        // 7. Simpan absensi siswa untuk setiap tanggal
        try {
            foreach ($dates_to_insert as $tgl) {
                $this->db->query("INSERT INTO absensi_siswa (siswa_id, tanggal, jam_masuk, status, keterangan) 
                                  VALUES (:siswa_id, :tanggal, :jam_masuk, :status, :keterangan)");
                $this->db->bind('siswa_id', $siswa['id']);
                $this->db->bind('tanggal', $tgl);
                $this->db->bind('jam_masuk', $jam_sekarang);
                $this->db->bind('status', $status);
                $this->db->bind('keterangan', $full_keterangan);
                $this->db->execute();
            }
        } catch (\PDOException $e) {
            $this->kirimNotifikasiKegagalanSafe('Pengajuan Izin Siswa Gagal Disimpan', 'Gagal menyimpan pengajuan izin/sakit siswa ke database.');
            error_log("[Absensi_model Error] ajukanIzinSiswa: " . $e->getMessage());
            return [
                'status' => false,
                'pesan'  => 'Terjadi kesalahan sistem saat menyimpan pengajuan izin/sakit.'
            ];
        }

        // Kirim notifikasi pengajuan izin siswa setelah data berhasil disimpan
        $this->kirimNotifikasiIzinSiswaSafe($siswa, $tgl_mulai, $tgl_selesai, $status, $keterangan);

        $totalHari = count($dates_to_insert);
        $teksHari = $totalHari > 1 ? "selama {$totalHari} hari" : "untuk tanggal " . date('d/m/Y', $time_mulai);

        return [
            'status'       => true,
            'pesan'        => 'Pengajuan ' . ucfirst($status) . " berhasil dicatat {$teksHari}.",
            'total_hari'   => $totalHari,
            'status_absen' => $status
        ];
    }

    /**
     * Pengajuan Sakit / Izin oleh Guru dari Dashboard
     *
     * @param int $user_id
     * @param array $data
     * @param array|null $file
     * @return array
     */
    public function ajukanIzinGuru($user_id, $data, $file = null) {
        date_default_timezone_set('Asia/Jakarta');

        // 1. Ambil data guru berdasarkan user_id
        $this->db->query("SELECT * FROM guru WHERE user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $guru = $this->db->single();

        if (!$guru) {
            return ['status' => false, 'pesan' => 'Data profil guru tidak ditemukan.'];
        }

        // 2. Validasi status
        $status = strtolower(trim($data['status'] ?? ''));
        if (!in_array($status, ['sakit', 'izin'])) {
            return ['status' => false, 'pesan' => 'Kategori pengajuan harus berupa Sakit atau Izin.'];
        }

        // 3. Validasi tanggal
        $tgl_mulai = trim($data['tanggal_mulai'] ?? '');
        $tgl_selesai = trim($data['tanggal_selesai'] ?? '');
        if (empty($tgl_selesai)) {
            $tgl_selesai = $tgl_mulai;
        }

        if (empty($tgl_mulai) || !strtotime($tgl_mulai)) {
            return ['status' => false, 'pesan' => 'Tanggal mulai tidak valid.'];
        }
        if (!strtotime($tgl_selesai)) {
            return ['status' => false, 'pesan' => 'Tanggal selesai tidak valid.'];
        }

        $time_mulai = strtotime($tgl_mulai);
        $time_selesai = strtotime($tgl_selesai);

        if ($time_mulai > $time_selesai) {
            return ['status' => false, 'pesan' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'];
        }

        $diff_days = ($time_selesai - $time_mulai) / (60 * 60 * 24);
        if ($diff_days > 14) {
            return ['status' => false, 'pesan' => 'Rentang pengajuan maksimal adalah 14 hari.'];
        }

        // 4. Validasi keterangan
        $keterangan = trim($data['keterangan'] ?? '');
        if (empty($keterangan) || strlen($keterangan) < 5) {
            return ['status' => false, 'pesan' => 'Keterangan atau alasan pengajuan minimal 5 karakter.'];
        }

        // 5. Handle file upload (opsional)
        $lampiran_info = '';
        if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
            $fileInfo = pathinfo($file['name']);
            $ext = strtolower($fileInfo['extension'] ?? '');

            if (!in_array($ext, $allowedExts)) {
                return ['status' => false, 'pesan' => 'Format berkas lampiran harus berupa JPG, PNG, atau PDF.'];
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                return ['status' => false, 'pesan' => 'Ukuran berkas lampiran maksimal 2MB.'];
            }

            $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/surat/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newFileName = 'surat_guru_' . $guru['id'] . '_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
            $targetPath = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $lampiran_info = ' (Lampiran: ' . $newFileName . ')';
            }
        }

        $full_keterangan = $keterangan . $lampiran_info;
        $jam_sekarang = date('H:i:s');

        // 6. Cek absensi existing
        $current = $time_mulai;
        $dates_to_insert = [];
        $conflicted_dates = [];

        while ($current <= $time_selesai) {
            $tgl = date('Y-m-d', $current);
            $this->db->query("SELECT id, status FROM absensi_guru WHERE guru_id = :guru_id AND tanggal = :tanggal");
            $this->db->bind('guru_id', $guru['id']);
            $this->db->bind('tanggal', $tgl);
            $existing = $this->db->single();

            if ($existing) {
                $conflicted_dates[] = date('d/m/Y', $current) . ' (' . ucfirst($existing['status']) . ')';
            } else {
                $dates_to_insert[] = $tgl;
            }
            $current = strtotime('+1 day', $current);
        }

        if (!empty($conflicted_dates)) {
            return [
                'status' => false,
                'pesan'  => 'Gagal mengajukan: Sudah terdapat absensi guru pada tanggal: ' . implode(', ', $conflicted_dates) . '.'
            ];
        }

        if (empty($dates_to_insert)) {
            return ['status' => false, 'pesan' => 'Tidak ada tanggal yang dapat diproses.'];
        }

        // 7. Simpan absensi guru
        try {
            foreach ($dates_to_insert as $tgl) {
                $this->db->query("INSERT INTO absensi_guru (guru_id, tanggal, jam_scan, status, keterangan) 
                                  VALUES (:guru_id, :tanggal, :jam_scan, :status, :keterangan)");
                $this->db->bind('guru_id', $guru['id']);
                $this->db->bind('tanggal', $tgl);
                $this->db->bind('jam_scan', $jam_sekarang);
                $this->db->bind('status', $status);
                $this->db->bind('keterangan', $full_keterangan);
                $this->db->execute();
            }
        } catch (\PDOException $e) {
            $this->kirimNotifikasiKegagalanSafe('Pengajuan Izin Guru Gagal Disimpan', 'Gagal menyimpan pengajuan izin/sakit guru ke database.');
            error_log("[Absensi_model Error] ajukanIzinGuru: " . $e->getMessage());
            return [
                'status' => false,
                'pesan'  => 'Terjadi kesalahan sistem saat menyimpan pengajuan izin/sakit.'
            ];
        }

        // Kirim notifikasi pengajuan izin guru setelah data berhasil disimpan
        $this->kirimNotifikasiIzinGuruSafe($guru, $tgl_mulai, $tgl_selesai, $status, $keterangan);

        $totalHari = count($dates_to_insert);
        $teksHari = $totalHari > 1 ? "selama {$totalHari} hari" : "untuk tanggal " . date('d/m/Y', $time_mulai);

        return [
            'status'       => true,
            'pesan'        => 'Pengajuan ' . ucfirst($status) . " berhasil dicatat {$teksHari}.",
            'total_hari'   => $totalHari,
            'status_absen' => $status
        ];
    }

    /**
     * Helper pengiriman notifikasi Telegram siswa secara aman tanpa mengganggu alur absensi
     */
    private function kirimNotifikasiTelegramSiswaSafe($siswa, $jam, $status, $tipe = 'masuk', $jamMasuk = null) {
        try {
            if (class_exists('TelegramHelper') && TelegramHelper::isEnabled()) {
                TelegramHelper::notifikasiAbsensiSiswa([
                    'nama'       => $siswa['nama'] ?? 'Siswa',
                    'nis'        => $siswa['nis'] ?? '-',
                    'nama_kelas' => $siswa['nama_kelas'] ?? '-',
                    'tanggal'    => date('Y-m-d'),
                    'jam'        => $jam,
                    'status'     => $status,
                    'tipe'       => $tipe,
                    'jam_masuk'  => $jamMasuk,
                    'jam_pulang' => ($tipe === 'pulang') ? $jam : null
                ]);
            }
        } catch (\Throwable $e) {
            error_log("[Telegram Dispatch Error - Siswa] " . $e->getMessage());
        }
    }

    /**
     * Helper pengiriman notifikasi Telegram guru secara aman tanpa mengganggu alur absensi
     */
    private function kirimNotifikasiTelegramGuruSafe($guru, $jam, $status, $tipe = 'masuk', $jamMasuk = null) {
        try {
            if (class_exists('TelegramHelper') && TelegramHelper::isEnabled()) {
                TelegramHelper::notifikasiAbsensiGuru([
                    'nama'       => $guru['nama'] ?? 'Guru',
                    'nip'        => $guru['nip'] ?? '-',
                    'tanggal'    => date('Y-m-d'),
                    'jam'        => $jam,
                    'status'     => $status,
                    'tipe'       => $tipe,
                    'jam_masuk'  => $jamMasuk,
                    'jam_pulang' => ($tipe === 'pulang') ? $jam : null
                ]);
            }
        } catch (\Throwable $e) {
            error_log("[Telegram Dispatch Error - Guru] " . $e->getMessage());
        }
    }

    /**
     * Helper pengiriman notifikasi Telegram pengajuan izin siswa secara aman
     */
    private function kirimNotifikasiIzinSiswaSafe($siswa, $tgl_mulai, $tgl_selesai, $status, $keterangan) {
        try {
            if (class_exists('TelegramHelper') && TelegramHelper::isEnabled()) {
                TelegramHelper::notifikasiPengajuanIzinSiswa([
                    'nama'        => $siswa['nama'] ?? 'Siswa',
                    'nama_kelas'  => $siswa['nama_kelas'] ?? '-',
                    'tgl_mulai'   => $tgl_mulai,
                    'tgl_selesai' => $tgl_selesai,
                    'status'      => $status,
                    'keterangan'  => $keterangan
                ]);
            }
        } catch (\Throwable $e) {
            error_log("[Telegram Dispatch Error - Izin Siswa] " . $e->getMessage());
        }
    }

    /**
     * Helper pengiriman notifikasi Telegram pengajuan izin guru secara aman
     */
    private function kirimNotifikasiIzinGuruSafe($guru, $tgl_mulai, $tgl_selesai, $status, $keterangan) {
        try {
            if (class_exists('TelegramHelper') && TelegramHelper::isEnabled()) {
                TelegramHelper::notifikasiPengajuanIzinGuru([
                    'nama'        => $guru['nama'] ?? 'Guru',
                    'tgl_mulai'   => $tgl_mulai,
                    'tgl_selesai' => $tgl_selesai,
                    'status'      => $status,
                    'keterangan'  => $keterangan
                ]);
            }
        } catch (\Throwable $e) {
            error_log("[Telegram Dispatch Error - Izin Guru] " . $e->getMessage());
        }
    }

    /**
     * Helper pengiriman notifikasi kegagalan sistem penting secara aman ke Admin
     */
    private function kirimNotifikasiKegagalanSafe($judul, $ringkasan) {
        try {
            if (class_exists('TelegramHelper') && TelegramHelper::isEnabled()) {
                TelegramHelper::notifikasiKegagalanSistem($judul, $ringkasan);
            }
        } catch (\Throwable $e) {
            error_log("[Telegram Dispatch Error - Kegagalan Sistem] " . $e->getMessage());
        }
    }
}
