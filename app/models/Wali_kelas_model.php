<?php

class Wali_kelas_model {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Dapatkan data kelas yang diampu oleh Wali Kelas (user_id)
     * Pengecekan server-side: user_id -> guru.id -> kelas.wali_kelas_id
     */
    public function getKelasByUserId($user_id) {
        $this->db->query("SELECT k.*, g.id as guru_id, g.nama as nama_guru, g.nip
                          FROM kelas k
                          JOIN guru g ON k.wali_kelas_id = g.id
                          WHERE g.user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        return $this->db->single();
    }

    /**
     * Dapatkan daftar siswa HANYA di kelas wali kelas tersebut
     */
    public function getSiswaByWali($user_id, $keyword = null) {
        $kelas = $this->getKelasByUserId($user_id);
        if (!$kelas) return [];

        $query = "SELECT s.*, k.nama_kelas
                  FROM siswa s
                  JOIN kelas k ON s.kelas_id = k.id
                  WHERE s.kelas_id = :kelas_id";

        if ($keyword) {
            $query .= " AND (s.nama LIKE :kw OR s.nis LIKE :kw OR s.nisn LIKE :kw)";
        }

        $query .= " ORDER BY s.nama ASC";

        $this->db->query($query);
        $this->db->bind('kelas_id', $kelas['id']);
        if ($keyword) {
            $this->db->bind('kw', '%' . $keyword . '%');
        }

        return $this->db->resultSet();
    }

    /**
     * Dapatkan absensi kelas siswa hari ini/tanggal tertentu
     */
    public function getAbsensiKelas($user_id, $tanggal = null, $status = null) {
        $kelas = $this->getKelasByUserId($user_id);
        if (!$kelas) return ['kelas' => null, 'siswa' => []];

        if (!$tanggal) {
            $tanggal = date('Y-m-d');
        }

        $query = "SELECT s.id as siswa_id, s.nis, s.nama, 
                         a.id as absensi_id, a.tanggal, a.jam_masuk, a.status, a.keterangan
                  FROM siswa s
                  LEFT JOIN absensi_siswa a ON s.id = a.siswa_id AND a.tanggal = :tanggal
                  WHERE s.kelas_id = :kelas_id";

        if ($status) {
            $query .= " AND a.status = :status";
        }

        $query .= " ORDER BY s.nama ASC";

        $this->db->query($query);
        $this->db->bind('kelas_id', $kelas['id']);
        $this->db->bind('tanggal', $tanggal);
        if ($status) {
            $this->db->bind('status', $status);
        }

        return [
            'kelas' => $kelas,
            'tanggal' => $tanggal,
            'siswa' => $this->db->resultSet()
        ];
    }

    /**
     * Dapatkan catatan siswa untuk kelas wali kelas
     */
    public function getCatatanSiswa($user_id, $siswa_id = null, $kategori = null) {
        $kelas = $this->getKelasByUserId($user_id);
        if (!$kelas) return [];

        $query = "SELECT c.*, s.nama as nama_siswa, s.nis, s.nisn, g.nama as nama_wali
                  FROM catatan_siswa c
                  JOIN siswa s ON c.siswa_id = s.id
                  LEFT JOIN guru g ON c.wali_kelas_id = g.id
                  WHERE s.kelas_id = :kelas_id";

        if ($siswa_id) {
            $query .= " AND c.siswa_id = :siswa_id";
        }

        if ($kategori) {
            $query .= " AND c.kategori = :kategori";
        }

        $query .= " ORDER BY c.tanggal DESC, c.id DESC";

        $this->db->query($query);
        $this->db->bind('kelas_id', $kelas['id']);
        if ($siswa_id) {
            $this->db->bind('siswa_id', $siswa_id);
        }
        if ($kategori) {
            $this->db->bind('kategori', $kategori);
        }

        return $this->db->resultSet();
    }

    /**
     * Dapatkan detail catatan siswa tunggal dengan validasi server-side
     */
    public function getCatatanById($user_id, $id) {
        $kelas = $this->getKelasByUserId($user_id);
        if (!$kelas) return false;

        $this->db->query("SELECT c.*, s.nama as nama_siswa, s.nis, s.nisn, k.nama_kelas, g.nama as nama_wali
                          FROM catatan_siswa c
                          JOIN siswa s ON c.siswa_id = s.id
                          JOIN kelas k ON s.kelas_id = k.id
                          LEFT JOIN guru g ON c.wali_kelas_id = g.id
                          WHERE c.id = :id AND s.kelas_id = :kelas_id");
        $this->db->bind('id', $id);
        $this->db->bind('kelas_id', $kelas['id']);
        return $this->db->single();
    }

    /**
     * Tambah catatan siswa dengan verifikasi server-side (wali_kelas -> kelas -> siswa)
     */
    public function addCatatanSiswa($user_id, $data) {
        $kelas = $this->getKelasByUserId($user_id);
        if (!$kelas) return false;

        // Verify that target student belongs to this wali kelas's class
        $this->db->query("SELECT id FROM siswa WHERE id = :siswa_id AND kelas_id = :kelas_id");
        $this->db->bind('siswa_id', $data['siswa_id']);
        $this->db->bind('kelas_id', $kelas['id']);
        $siswaCheck = $this->db->single();

        if (!$siswaCheck) {
            return false; // Unauthorized or invalid student
        }

        $allowedKategori = ['Pembinaan','Pelanggaran','Prestasi','Izin','Akademik','Perilaku','Lainnya'];
        if (!in_array($data['kategori'], $allowedKategori)) {
            return false;
        }

        $this->db->query("INSERT INTO catatan_siswa (siswa_id, wali_kelas_id, kategori, tanggal, catatan)
                          VALUES (:siswa_id, :wali_kelas_id, :kategori, :tanggal, :catatan)");
        $this->db->bind('siswa_id', $data['siswa_id']);
        $this->db->bind('wali_kelas_id', $kelas['guru_id']);
        $this->db->bind('kategori', $data['kategori']);
        $this->db->bind('tanggal', $data['tanggal'] ?: date('Y-m-d'));
        $this->db->bind('catatan', $data['catatan']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Edit catatan siswa dengan verifikasi server-side
     */
    public function updateCatatanSiswa($user_id, $id, $data) {
        $existing = $this->getCatatanById($user_id, $id);
        if (!$existing) {
            return false; // Record not found or unauthorized
        }

        $kelas = $this->getKelasByUserId($user_id);

        // Verify target student belongs to class
        $this->db->query("SELECT id FROM siswa WHERE id = :siswa_id AND kelas_id = :kelas_id");
        $this->db->bind('siswa_id', $data['siswa_id']);
        $this->db->bind('kelas_id', $kelas['id']);
        $siswaCheck = $this->db->single();

        if (!$siswaCheck) {
            return false;
        }

        $allowedKategori = ['Pembinaan','Pelanggaran','Prestasi','Izin','Akademik','Perilaku','Lainnya'];
        if (!in_array($data['kategori'], $allowedKategori)) {
            return false;
        }

        $this->db->query("UPDATE catatan_siswa 
                          SET siswa_id = :siswa_id, kategori = :kategori, tanggal = :tanggal, catatan = :catatan, updated_at = CURRENT_TIMESTAMP()
                          WHERE id = :id");
        $this->db->bind('siswa_id', $data['siswa_id']);
        $this->db->bind('kategori', $data['kategori']);
        $this->db->bind('tanggal', $data['tanggal']);
        $this->db->bind('catatan', $data['catatan']);
        $this->db->bind('id', $id);

        $this->db->execute();
        return $this->db->rowCount() >= 0;
    }

    /**
     * Hapus catatan siswa dengan verifikasi server-side
     */
    public function deleteCatatanSiswa($user_id, $id) {
        $existing = $this->getCatatanById($user_id, $id);
        if (!$existing) {
            return false; // Unauthorized or record does not exist
        }

        $this->db->query("DELETE FROM catatan_siswa WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Dapatkan rekap absensi per siswa di kelas wali kelas dengan filter tanggal, siswa, dan status
     */
    public function getRekapAbsensi($user_id, $tgl_mulai = null, $tgl_akhir = null, $siswa_id = null, $status = null) {
        $kelas = $this->getKelasByUserId($user_id);
        if (!$kelas) return ['kelas' => null, 'rekap' => [], 'ringkasan' => []];

        if (!$tgl_mulai) $tgl_mulai = date('Y-m-01');
        if (!$tgl_akhir) $tgl_akhir = date('Y-m-t');

        $query = "SELECT s.id as siswa_id, s.nis, s.nama,
                         SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as total_hadir,
                         SUM(CASE WHEN a.status = 'terlambat' THEN 1 ELSE 0 END) as total_terlambat,
                         SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) as total_izin,
                         SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) as total_sakit,
                         SUM(CASE WHEN a.status = 'alpa' THEN 1 ELSE 0 END) as total_alpa
                  FROM siswa s
                  LEFT JOIN absensi_siswa a ON s.id = a.siswa_id AND a.tanggal BETWEEN :tgl_mulai AND :tgl_akhir
                  WHERE s.kelas_id = :kelas_id";

        if ($siswa_id) {
            $query .= " AND s.id = :siswa_id";
        }

        $query .= " GROUP BY s.id, s.nis, s.nama ORDER BY s.nama ASC";

        $this->db->query($query);
        $this->db->bind('kelas_id', $kelas['id']);
        $this->db->bind('tgl_mulai', $tgl_mulai);
        $this->db->bind('tgl_akhir', $tgl_akhir);
        if ($siswa_id) {
            $this->db->bind('siswa_id', $siswa_id);
        }

        $rows = $this->db->resultSet();

        $ringkasan = [
            'total_hadir' => 0,
            'total_terlambat' => 0,
            'total_izin' => 0,
            'total_sakit' => 0,
            'total_alpa' => 0
        ];

        $rekapFinal = [];
        foreach ($rows as $r) {
            $h = (int)$r['total_hadir'];
            $t = (int)$r['total_terlambat'];
            $i = (int)$r['total_izin'];
            $s = (int)$r['total_sakit'];
            $a = (int)$r['total_alpa'];
            $totalHari = $h + $t + $i + $s + $a;
            $persentase = $totalHari > 0 ? round((($h + $t) / $totalHari) * 100, 1) : 0;

            // Optional status filter
            if ($status) {
                if ($status == 'hadir' && $h == 0) continue;
                if ($status == 'terlambat' && $t == 0) continue;
                if ($status == 'izin' && $i == 0) continue;
                if ($status == 'sakit' && $s == 0) continue;
                if ($status == 'alpa' && $a == 0) continue;
            }

            $ringkasan['total_hadir'] += $h;
            $ringkasan['total_terlambat'] += $t;
            $ringkasan['total_izin'] += $i;
            $ringkasan['total_sakit'] += $s;
            $ringkasan['total_alpa'] += $a;

            $r['persentase'] = $persentase;
            $rekapFinal[] = $r;
        }

        return [
            'kelas' => $kelas,
            'tgl_mulai' => $tgl_mulai,
            'tgl_akhir' => $tgl_akhir,
            'siswa_id' => $siswa_id,
            'status' => $status,
            'ringkasan' => $ringkasan,
            'rekap' => $rekapFinal
        ];
    }

    /**
     * Dapatkan detail riwayat absensi siswa tunggal dengan otorisasi server-side
     */
    public function getDetailAbsensiSiswaByWali($user_id, $siswa_id, $tgl_mulai = null, $tgl_akhir = null, $status = null) {
        $kelas = $this->getKelasByUserId($user_id);
        if (!$kelas) return false;

        // Verify student belongs to this wali class
        $this->db->query("SELECT s.*, k.nama_kelas 
                          FROM siswa s 
                          JOIN kelas k ON s.kelas_id = k.id 
                          WHERE s.id = :siswa_id AND s.kelas_id = :kelas_id");
        $this->db->bind('siswa_id', $siswa_id);
        $this->db->bind('kelas_id', $kelas['id']);
        $siswa = $this->db->single();

        if (!$siswa) return false; // IDOR check failed or student not found

        $query = "SELECT a.* 
                  FROM absensi_siswa a 
                  WHERE a.siswa_id = :siswa_id";

        if ($tgl_mulai && $tgl_akhir) {
            $query .= " AND a.tanggal BETWEEN :tgl_mulai AND :tgl_akhir";
        }
        if ($status) {
            $query .= " AND a.status = :status";
        }

        $query .= " ORDER BY a.tanggal DESC, a.id DESC";

        $this->db->query($query);
        $this->db->bind('siswa_id', $siswa_id);
        if ($tgl_mulai && $tgl_akhir) {
            $this->db->bind('tgl_mulai', $tgl_mulai);
            $this->db->bind('tgl_akhir', $tgl_akhir);
        }
        if ($status) {
            $this->db->bind('status', $status);
        }

        $riwayat = $this->db->resultSet();

        // Calculate summary for this student
        $stats = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'total' => 0,
            'persentase' => 0
        ];

        foreach ($riwayat as $r) {
            $st = $r['status'];
            if (isset($stats[$st])) {
                $stats[$st]++;
            }
            $stats['total']++;
        }

        if ($stats['total'] > 0) {
            $stats['persentase'] = round((($stats['hadir'] + $stats['terlambat']) / $stats['total']) * 100, 1);
        }

        return [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'riwayat' => $riwayat,
            'stats' => $stats
        ];
    }

    /**
     * Dapatkan profil wali kelas
     */
    public function getProfil($user_id) {
        $this->db->query("SELECT u.username, u.role, g.nama, g.nip, g.mata_pelajaran, g.no_hp, k.nama_kelas
                          FROM users u
                          LEFT JOIN guru g ON u.id = g.user_id
                          LEFT JOIN kelas k ON g.id = k.wali_kelas_id
                          WHERE u.id = :user_id");
        $this->db->bind('user_id', $user_id);
        return $this->db->single();
    }
}
