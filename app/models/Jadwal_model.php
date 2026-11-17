<?php

class Jadwal_model {
    private $table = 'jadwal_absensi';
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Dapatkan semua jadwal dengan filter opsional (tipe_pengguna, hari, status, keyword)
     */
    public function getAllJadwal($filters = []) {
        $query = "SELECT j.*, k.nama_kelas 
                  FROM {$this->table} j 
                  LEFT JOIN kelas k ON j.kelas_id = k.id 
                  WHERE 1=1";
        $params = [];

        if (!empty($filters['tipe_pengguna']) && in_array($filters['tipe_pengguna'], ['siswa', 'guru'])) {
            $query .= " AND j.tipe_pengguna = :tipe";
            $params['tipe'] = $filters['tipe_pengguna'];
        }

        if (!empty($filters['hari']) && $filters['hari'] !== 'all') {
            $query .= " AND j.hari = :hari";
            $params['hari'] = $filters['hari'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== 'all') {
            $query .= " AND j.is_active = :is_active";
            $params['is_active'] = (int)$filters['is_active'];
        }

        if (!empty($filters['keyword'])) {
            $query .= " AND (j.nama_jadwal LIKE :keyword OR j.keterangan LIKE :keyword)";
            $params['keyword'] = '%' . trim($filters['keyword']) . '%';
        }

        $query .= " ORDER BY j.tipe_pengguna ASC, j.hari ASC, j.jam_mulai ASC, j.id DESC";

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }

        return $this->db->resultSet();
    }

    /**
     * Dapatkan satu jadwal berdasarkan ID
     */
    public function getJadwalById($id) {
        $this->db->query("SELECT j.*, k.nama_kelas 
                          FROM {$this->table} j 
                          LEFT JOIN kelas k ON j.kelas_id = k.id 
                          WHERE j.id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    /**
     * Dapatkan jadwal aktif untuk backend absensi (Siswa atau Guru)
     * Prioritas:
     * 1. Jadwal spesifik kelas (untuk siswa jika ada) > jadwal umum (kelas_id NULL)
     * 2. Jadwal spesifik hari ('Senin', 'Selasa', dst.) > 'Semua Hari'
     * 3. Jadwal dengan periode tanggal tertentu > periode tanpa batas
     * 4. ID terbaru
     */
    public function getActiveJadwal($tipe_pengguna, $hari = null, $tanggal = null, $kelas_id = null) {
        if (!$hari) {
            $mapHari = [
                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
            ];
            $hari = $mapHari[date('l')];
        }

        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }

        $query = "SELECT j.*, k.nama_kelas 
                  FROM {$this->table} j 
                  LEFT JOIN kelas k ON j.kelas_id = k.id 
                  WHERE j.tipe_pengguna = :tipe 
                    AND j.is_active = 1 
                    AND (
                        j.hari = :hari 
                        OR j.hari = 'Semua Hari'
                        OR (j.hari = 'Senin - Jumat' AND :hari IN ('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'))
                        OR (j.hari = 'Senin - Sabtu' AND :hari IN ('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'))
                    ) 
                    AND (j.tanggal_mulai IS NULL OR j.tanggal_mulai <= :tanggal) 
                    AND (j.tanggal_akhir IS NULL OR j.tanggal_akhir >= :tanggal)";

        if ($tipe_pengguna === 'siswa' && !empty($kelas_id)) {
            $query .= " AND (j.kelas_id = :kelas_id OR j.kelas_id IS NULL)";
        } else {
            $query .= " AND j.kelas_id IS NULL";
        }

        // Urutan prioritas spesifik -> umum
        $query .= " ORDER BY " . 
                  ($tipe_pengguna === 'siswa' && !empty($kelas_id) ? "(j.kelas_id IS NOT NULL) DESC, " : "") . 
                  "(j.hari != 'Semua Hari' AND j.hari != 'Senin - Jumat' AND j.hari != 'Senin - Sabtu') DESC, 
                   (j.hari = 'Senin - Jumat' OR j.hari = 'Senin - Sabtu') DESC, 
                   (j.tanggal_mulai IS NOT NULL) DESC, 
                   j.id DESC 
                   LIMIT 1";

        $this->db->query($query);
        $this->db->bind('tipe', $tipe_pengguna);
        $this->db->bind('hari', $hari);
        $this->db->bind('tanggal', $tanggal);
        if ($tipe_pengguna === 'siswa' && !empty($kelas_id)) {
            $this->db->bind('kelas_id', $kelas_id);
        }

        return $this->db->single();
    }

    /**
     * Validasi data input jadwal server-side
     */
    public function validateJadwal($data) {
        $nama_jadwal   = trim($data['nama_jadwal'] ?? '');
        $tipe_pengguna = trim($data['tipe_pengguna'] ?? '');
        $hari          = trim($data['hari'] ?? '');
        $jam_mulai     = trim($data['jam_mulai'] ?? '');
        $jam_masuk     = trim($data['jam_masuk'] ?? '');
        $jam_akhir     = trim($data['jam_akhir'] ?? '');
        $tanggal_mulai = !empty($data['tanggal_mulai']) ? trim($data['tanggal_mulai']) : null;
        $tanggal_akhir = !empty($data['tanggal_akhir']) ? trim($data['tanggal_akhir']) : null;

        // 1. Cek field wajib tidak boleh kosong
        if (empty($nama_jadwal)) {
            return ['valid' => false, 'error' => 'Nama jadwal tidak boleh kosong.'];
        }
        if (strlen($nama_jadwal) > 100) {
            return ['valid' => false, 'error' => 'Nama jadwal maksimal 100 karakter.'];
        }
        if (!in_array($tipe_pengguna, ['siswa', 'guru'])) {
            return ['valid' => false, 'error' => 'Jenis pengguna harus dipilih antara Siswa atau Guru.'];
        }
        $allowedHari = ['Senin - Jumat', 'Senin - Sabtu', 'Semua Hari', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        if (!in_array($hari, $allowedHari)) {
            return ['valid' => false, 'error' => 'Hari berlaku tidak valid.'];
        }

        // 2. Cek format waktu (H:i atau H:i:s)
        if (empty($jam_mulai) || empty($jam_masuk) || empty($jam_akhir)) {
            return ['valid' => false, 'error' => 'Jam mulai, batas waktu hadir, dan jam akhir wajib diisi.'];
        }

        $t_mulai = strtotime($jam_mulai);
        $t_masuk = strtotime($jam_masuk);
        $t_akhir = strtotime($jam_akhir);

        if ($t_mulai === false || $t_masuk === false || $t_akhir === false) {
            return ['valid' => false, 'error' => 'Format jam tidak valid. Gunakan format waktu 24 jam (HH:MM).'];
        }

        // 3. Logika waktu
        if ($t_mulai >= $t_akhir) {
            return ['valid' => false, 'error' => 'Jam mulai harus lebih awal daripada jam akhir absensi.'];
        }
        if ($t_masuk < $t_mulai || $t_masuk > $t_akhir) {
            return ['valid' => false, 'error' => 'Batas waktu hadir harus berada dalam rentang jam mulai dan jam akhir absensi.'];
        }

        // 4. Logika tanggal periode jika diisi
        if ($tanggal_mulai && $tanggal_akhir) {
            if (strtotime($tanggal_mulai) > strtotime($tanggal_akhir)) {
                return ['valid' => false, 'error' => 'Tanggal mulai periode harus lebih awal atau sama dengan tanggal akhir periode.'];
            }
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * Cek apakah terdapat jadwal aktif lain yang bertabrakan (overlapping scope, day, period, and time)
     */
    public function checkCollision($data, $exclude_id = null) {
        $tipe_pengguna = $data['tipe_pengguna'];
        $kelas_id      = (!empty($data['kelas_id']) && $tipe_pengguna === 'siswa') ? (int)$data['kelas_id'] : null;
        $hari          = $data['hari'];
        $jam_mulai     = strlen($data['jam_mulai']) == 5 ? $data['jam_mulai'] . ':00' : $data['jam_mulai'];
        $jam_akhir     = strlen($data['jam_akhir']) == 5 ? $data['jam_akhir'] . ':00' : $data['jam_akhir'];
        $tgl_mulai     = !empty($data['tanggal_mulai']) ? $data['tanggal_mulai'] : null;
        $tgl_akhir     = !empty($data['tanggal_akhir']) ? $data['tanggal_akhir'] : null;

        $query = "SELECT * FROM {$this->table} 
                  WHERE is_active = 1 
                    AND tipe_pengguna = :tipe";

        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        if ($kelas_id !== null) {
            $query .= " AND kelas_id = :kelas_id";
        } else {
            $query .= " AND kelas_id IS NULL";
        }

        $this->db->query($query);
        $this->db->bind('tipe', $tipe_pengguna);
        if ($exclude_id) {
            $this->db->bind('exclude_id', $exclude_id);
        }
        if ($kelas_id !== null) {
            $this->db->bind('kelas_id', $kelas_id);
        }

        $candidates = $this->db->resultSet();

        foreach ($candidates as $cand) {
            // 1. Cek kesamaan / irisan hari berlaku
            $daysA = $this->getListOfDays($hari);
            $daysB = $this->getListOfDays($cand['hari']);
            $dayOverlap = !empty(array_intersect($daysA, $daysB));
            if (!$dayOverlap) {
                continue;
            }

            // 2. Cek tumpang tindih periode tanggal
            $c_tgl_mulai = $cand['tanggal_mulai'];
            $c_tgl_akhir = $cand['tanggal_akhir'];

            $dateOverlap = true;
            if ($tgl_mulai && $c_tgl_akhir && $tgl_mulai > $c_tgl_akhir) {
                $dateOverlap = false;
            }
            if ($tgl_akhir && $c_tgl_mulai && $tgl_akhir < $c_tgl_mulai) {
                $dateOverlap = false;
            }
            if (!$dateOverlap) {
                continue;
            }

            // 3. Cek tumpang tindih jam (Time overlap: NOT (A_end <= B_start OR A_start >= B_end))
            $cand_start = $cand['jam_mulai'];
            $cand_end   = $cand['jam_akhir'];

            if (!($jam_akhir <= $cand_start || $jam_mulai >= $cand_end)) {
                return [
                    'has_collision' => true,
                    'conflicting_jadwal' => $cand
                ];
            }
        }

        return ['has_collision' => false, 'conflicting_jadwal' => null];
    }

    /**
     * Tambah jadwal baru
     */
    public function tambahJadwal($data) {
        $validasi = $this->validateJadwal($data);
        if (!$validasi['valid']) {
            return ['status' => false, 'pesan' => $validasi['error']];
        }

        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        // Cek tabrakan jika status aktif
        if ($is_active === 1) {
            $collision = $this->checkCollision($data);
            if ($collision['has_collision']) {
                $cf = $collision['conflicting_jadwal'];
                $msg = "Jadwal bertabrakan dengan jadwal aktif: '" . htmlspecialchars($cf['nama_jadwal']) . 
                       "' (Hari: {$cf['hari']}, Jam: " . substr($cf['jam_mulai'], 0, 5) . " - " . substr($cf['jam_akhir'], 0, 5) . "). " .
                       "Nonaktifkan jadwal tersebut terlebih dahulu atau sesuaikan waktunya.";
                return ['status' => false, 'pesan' => $msg];
            }
        }

        $jam_mulai = strlen($data['jam_mulai']) == 5 ? $data['jam_mulai'] . ':00' : $data['jam_mulai'];
        $jam_masuk = strlen($data['jam_masuk']) == 5 ? $data['jam_masuk'] . ':00' : $data['jam_masuk'];
        $jam_akhir = strlen($data['jam_akhir']) == 5 ? $data['jam_akhir'] . ':00' : $data['jam_akhir'];

        $kelas_id = ($data['tipe_pengguna'] === 'siswa' && !empty($data['kelas_id'])) ? (int)$data['kelas_id'] : null;
        $tanggal_mulai = !empty($data['tanggal_mulai']) ? $data['tanggal_mulai'] : null;
        $tanggal_akhir = !empty($data['tanggal_akhir']) ? $data['tanggal_akhir'] : null;
        $keterangan = !empty($data['keterangan']) ? trim($data['keterangan']) : null;

        $query = "INSERT INTO {$this->table} 
                  (`nama_jadwal`, `tipe_pengguna`, `kelas_id`, `hari`, `jam_mulai`, `jam_masuk`, `jam_akhir`, 
                   `tanggal_mulai`, `tanggal_akhir`, `is_active`, `keterangan`) 
                  VALUES 
                  (:nama_jadwal, :tipe_pengguna, :kelas_id, :hari, :jam_mulai, :jam_masuk, :jam_akhir, 
                   :tanggal_mulai, :tanggal_akhir, :is_active, :keterangan)";

        $this->db->query($query);
        $this->db->bind('nama_jadwal', trim($data['nama_jadwal']));
        $this->db->bind('tipe_pengguna', $data['tipe_pengguna']);
        $this->db->bind('kelas_id', $kelas_id);
        $this->db->bind('hari', $data['hari']);
        $this->db->bind('jam_mulai', $jam_mulai);
        $this->db->bind('jam_masuk', $jam_masuk);
        $this->db->bind('jam_akhir', $jam_akhir);
        $this->db->bind('tanggal_mulai', $tanggal_mulai);
        $this->db->bind('tanggal_akhir', $tanggal_akhir);
        $this->db->bind('is_active', $is_active);
        $this->db->bind('keterangan', $keterangan);

        $this->db->execute();
        $id = $this->db->lastInsertId();

        return ['status' => true, 'pesan' => 'Jadwal absensi berhasil ditambahkan.', 'id' => $id];
    }

    /**
     * Ubah jadwal yang sudah ada
     */
    public function ubahJadwal($data) {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            return ['status' => false, 'pesan' => 'ID jadwal tidak valid.'];
        }

        $existing = $this->getJadwalById($id);
        if (!$existing) {
            return ['status' => false, 'pesan' => 'Data jadwal tidak ditemukan.'];
        }

        $validasi = $this->validateJadwal($data);
        if (!$validasi['valid']) {
            return ['status' => false, 'pesan' => $validasi['error']];
        }

        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        // Cek tabrakan jika status aktif
        if ($is_active === 1) {
            $collision = $this->checkCollision($data, $id);
            if ($collision['has_collision']) {
                $cf = $collision['conflicting_jadwal'];
                $msg = "Perubahan jadwal bertabrakan dengan jadwal aktif: '" . htmlspecialchars($cf['nama_jadwal']) . 
                       "' (Hari: {$cf['hari']}, Jam: " . substr($cf['jam_mulai'], 0, 5) . " - " . substr($cf['jam_akhir'], 0, 5) . "). " .
                       "Nonaktifkan jadwal tersebut terlebih dahulu atau sesuaikan waktunya.";
                return ['status' => false, 'pesan' => $msg];
            }
        }

        $jam_mulai = strlen($data['jam_mulai']) == 5 ? $data['jam_mulai'] . ':00' : $data['jam_mulai'];
        $jam_masuk = strlen($data['jam_masuk']) == 5 ? $data['jam_masuk'] . ':00' : $data['jam_masuk'];
        $jam_akhir = strlen($data['jam_akhir']) == 5 ? $data['jam_akhir'] . ':00' : $data['jam_akhir'];

        $kelas_id = ($data['tipe_pengguna'] === 'siswa' && !empty($data['kelas_id'])) ? (int)$data['kelas_id'] : null;
        $tanggal_mulai = !empty($data['tanggal_mulai']) ? $data['tanggal_mulai'] : null;
        $tanggal_akhir = !empty($data['tanggal_akhir']) ? $data['tanggal_akhir'] : null;
        $keterangan = !empty($data['keterangan']) ? trim($data['keterangan']) : null;

        $query = "UPDATE {$this->table} 
                  SET `nama_jadwal` = :nama_jadwal,
                      `tipe_pengguna` = :tipe_pengguna,
                      `kelas_id` = :kelas_id,
                      `hari` = :hari,
                      `jam_mulai` = :jam_mulai,
                      `jam_masuk` = :jam_masuk,
                      `jam_akhir` = :jam_akhir,
                      `tanggal_mulai` = :tanggal_mulai,
                      `tanggal_akhir` = :tanggal_akhir,
                      `is_active` = :is_active,
                      `keterangan` = :keterangan
                  WHERE `id` = :id";

        $this->db->query($query);
        $this->db->bind('nama_jadwal', trim($data['nama_jadwal']));
        $this->db->bind('tipe_pengguna', $data['tipe_pengguna']);
        $this->db->bind('kelas_id', $kelas_id);
        $this->db->bind('hari', $data['hari']);
        $this->db->bind('jam_mulai', $jam_mulai);
        $this->db->bind('jam_masuk', $jam_masuk);
        $this->db->bind('jam_akhir', $jam_akhir);
        $this->db->bind('tanggal_mulai', $tanggal_mulai);
        $this->db->bind('tanggal_akhir', $tanggal_akhir);
        $this->db->bind('is_active', $is_active);
        $this->db->bind('keterangan', $keterangan);
        $this->db->bind('id', $id);

        $this->db->execute();
        return ['status' => true, 'pesan' => 'Jadwal absensi berhasil diperbarui.'];
    }

    /**
     * Aktifkan atau Nonaktifkan jadwal
     */
    public function toggleStatus($id) {
        $jadwal = $this->getJadwalById($id);
        if (!$jadwal) {
            return ['status' => false, 'pesan' => 'Data jadwal tidak ditemukan.'];
        }

        $newStatus = ($jadwal['is_active'] == 1) ? 0 : 1;

        // Jika akan diaktifkan, cek apakah ada jadwal lain yang bertabrakan
        if ($newStatus === 1) {
            $collision = $this->checkCollision($jadwal, $id);
            if ($collision['has_collision']) {
                $cf = $collision['conflicting_jadwal'];
                $msg = "Gagal mengaktifkan jadwal: bertabrakan dengan jadwal aktif '" . htmlspecialchars($cf['nama_jadwal']) . 
                       "' (Hari: {$cf['hari']}, Jam: " . substr($cf['jam_mulai'], 0, 5) . " - " . substr($cf['jam_akhir'], 0, 5) . ").";
                return ['status' => false, 'pesan' => $msg];
            }
        }

        $this->db->query("UPDATE {$this->table} SET is_active = :status WHERE id = :id");
        $this->db->bind('status', $newStatus);
        $this->db->bind('id', $id);
        $this->db->execute();

        $statusLabel = ($newStatus === 1) ? 'diaktifkan' : 'dinonaktifkan';
        return ['status' => true, 'pesan' => "Jadwal '{$jadwal['nama_jadwal']}' berhasil {$statusLabel}.", 'is_active' => $newStatus];
    }

    /**
     * Hapus jadwal (dengan validasi keamanan)
     */
    public function hapusJadwal($id) {
        $jadwal = $this->getJadwalById($id);
        if (!$jadwal) {
            return ['status' => false, 'pesan' => 'Data jadwal tidak ditemukan.'];
        }

        // Hapus record jadwal
        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();

        return ['status' => true, 'pesan' => "Jadwal '{$jadwal['nama_jadwal']}' berhasil dihapus."];
    }

    /**
     * Dapatkan daftar seluruh kelas untuk dropdown form
     */
    public function getAllKelas() {
        $this->db->query("SELECT id, nama_kelas, jurusan FROM kelas ORDER BY nama_kelas ASC");
        return $this->db->resultSet();
    }

    /**
     * Konversi hari jadwal menjadi daftar hari satuan untuk cek irisan
     */
    private function getListOfDays($h) {
        if ($h === 'Semua Hari') return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        if ($h === 'Senin - Jumat') return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        if ($h === 'Senin - Sabtu') return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return [$h];
    }
}
