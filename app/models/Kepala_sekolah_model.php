<?php

class Kepala_sekolah_model {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Dapatkan statistik hari ini untuk Siswa dan Guru
     */
    public function getDashboardStats() {
        $stats = [
            'siswa' => [
                'total' => 0, 'hadir' => 0, 'terlambat' => 0,
                'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'belum_absen' => 0
            ],
            'guru' => [
                'total' => 0, 'hadir' => 0, 'terlambat' => 0,
                'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'belum_absen' => 0
            ]
        ];

        // --- Siswa Stats Today ---
        $this->db->query("SELECT COUNT(id) as total FROM siswa WHERE status = 'active'");
        $stats['siswa']['total'] = (int)$this->db->single()['total'];

        $statuses = ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'];
        $totalSudahAbsenSiswa = 0;
        foreach ($statuses as $st) {
            $this->db->query("SELECT COUNT(id) as total FROM absensi_siswa WHERE tanggal = CURDATE() AND status = :st");
            $this->db->bind('st', $st);
            $cnt = (int)$this->db->single()['total'];
            $stats['siswa'][$st] = $cnt;
            $totalSudahAbsenSiswa += $cnt;
        }
        $stats['siswa']['belum_absen'] = max(0, $stats['siswa']['total'] - $totalSudahAbsenSiswa);

        // --- Guru Stats Today ---
        $this->db->query("SELECT COUNT(id) as total FROM guru WHERE status = 'active'");
        $stats['guru']['total'] = (int)$this->db->single()['total'];

        $totalSudahAbsenGuru = 0;
        foreach ($statuses as $st) {
            $this->db->query("SELECT COUNT(id) as total FROM absensi_guru WHERE tanggal = CURDATE() AND status = :st");
            $this->db->bind('st', $st);
            $cnt = (int)$this->db->single()['total'];
            $stats['guru'][$st] = $cnt;
            $totalSudahAbsenGuru += $cnt;
        }
        $stats['guru']['belum_absen'] = max(0, $stats['guru']['total'] - $totalSudahAbsenGuru);

        return $stats;
    }

    /**
     * Dapatkan statistik per kelas untuk chart / visualisasi
     */
    public function getStatistikPerKelas() {
        $this->db->query("SELECT k.id, k.nama_kelas, k.jurusan,
                          (SELECT COUNT(id) FROM siswa WHERE kelas_id = k.id AND status = 'active') as total_siswa,
                          (SELECT COUNT(a.id) FROM absensi_siswa a JOIN siswa s ON a.siswa_id = s.id WHERE s.kelas_id = k.id AND a.tanggal = CURDATE() AND a.status = 'hadir') as total_hadir,
                          (SELECT COUNT(a.id) FROM absensi_siswa a JOIN siswa s ON a.siswa_id = s.id WHERE s.kelas_id = k.id AND a.tanggal = CURDATE() AND a.status = 'terlambat') as total_terlambat
                          FROM kelas k ORDER BY k.nama_kelas ASC");
        return $this->db->resultSet();
    }

    /**
     * Dapatkan data rekap siswa per kelas
     */
    public function getRekapSiswa($filters) {
        $tgl_mulai = !empty($filters['tgl_mulai']) ? $filters['tgl_mulai'] : date('Y-m-01');
        $tgl_akhir = !empty($filters['tgl_akhir']) ? $filters['tgl_akhir'] : date('Y-m-t');

        $conditions = [];
        $params = [
            'tgl_mulai' => $tgl_mulai,
            'tgl_akhir' => $tgl_akhir
        ];

        if (!empty($filters['kelas_id'])) {
            $conditions[] = 's.kelas_id = :kelas_id';
            $params['kelas_id'] = $filters['kelas_id'];
        }

        if (!empty($filters['jurusan'])) {
            $conditions[] = 'k.jurusan = :jurusan';
            $params['jurusan'] = $filters['jurusan'];
        }

        $where = !empty($conditions) ? 'AND ' . implode(' AND ', $conditions) : '';

        $query = "SELECT s.id as siswa_id, s.nis, s.nama, k.nama_kelas, k.jurusan,
                         SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as total_hadir,
                         SUM(CASE WHEN a.status = 'terlambat' THEN 1 ELSE 0 END) as total_terlambat,
                         SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) as total_izin,
                         SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) as total_sakit,
                         SUM(CASE WHEN a.status = 'alpa' THEN 1 ELSE 0 END) as total_alpa
                  FROM siswa s
                  JOIN kelas k ON s.kelas_id = k.id
                  LEFT JOIN absensi_siswa a ON s.id = a.siswa_id AND a.tanggal BETWEEN :tgl_mulai AND :tgl_akhir
                  WHERE 1=1 {$where}
                  GROUP BY s.id, s.nis, s.nama, k.nama_kelas, k.jurusan
                  ORDER BY k.nama_kelas ASC, s.nama ASC";

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }

        $rows = $this->db->resultSet();
        $status = !empty($filters['status']) ? $filters['status'] : null;

        $rekapFinal = [];
        foreach ($rows as $r) {
            $h = (int)$r['total_hadir'];
            $t = (int)$r['total_terlambat'];
            $i = (int)$r['total_izin'];
            $s = (int)$r['total_sakit'];
            $a = (int)$r['total_alpa'];
            $totalHari = $h + $t + $i + $s + $a;
            $r['persentase'] = $totalHari > 0 ? round((($h + $t) / $totalHari) * 100, 1) : 0;

            if ($status) {
                if ($status == 'hadir' && $h == 0) continue;
                if ($status == 'terlambat' && $t == 0) continue;
                if ($status == 'izin' && $i == 0) continue;
                if ($status == 'sakit' && $s == 0) continue;
                if ($status == 'alpa' && $a == 0) continue;
            }

            $rekapFinal[] = $r;
        }

        return $rekapFinal;
    }

    /**
     * Dapatkan data rekap guru
     */
    public function getRekapGuru($filters) {
        $tgl_mulai = !empty($filters['tgl_mulai']) ? $filters['tgl_mulai'] : date('Y-m-01');
        $tgl_akhir = !empty($filters['tgl_akhir']) ? $filters['tgl_akhir'] : date('Y-m-t');

        $query = "SELECT g.id as guru_id, g.nip, g.nama, g.mata_pelajaran,
                         SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as total_hadir,
                         SUM(CASE WHEN a.status = 'terlambat' THEN 1 ELSE 0 END) as total_terlambat,
                         SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) as total_izin,
                         SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) as total_sakit,
                         SUM(CASE WHEN a.status = 'alpa' THEN 1 ELSE 0 END) as total_alpa
                  FROM guru g
                  LEFT JOIN absensi_guru a ON g.id = a.guru_id AND a.tanggal BETWEEN :tgl_mulai AND :tgl_akhir
                  GROUP BY g.id, g.nip, g.nama, g.mata_pelajaran
                  ORDER BY g.nama ASC";

        $this->db->query($query);
        $this->db->bind('tgl_mulai', $tgl_mulai);
        $this->db->bind('tgl_akhir', $tgl_akhir);

        $rows = $this->db->resultSet();
        $status = !empty($filters['status']) ? $filters['status'] : null;

        $rekapFinal = [];
        foreach ($rows as $r) {
            $h = (int)$r['total_hadir'];
            $t = (int)$r['total_terlambat'];
            $i = (int)$r['total_izin'];
            $s = (int)$r['total_sakit'];
            $a = (int)$r['total_alpa'];
            $totalHari = $h + $t + $i + $s + $a;
            $r['persentase'] = $totalHari > 0 ? round((($h + $t) / $totalHari) * 100, 1) : 0;

            if ($status) {
                if ($status == 'hadir' && $h == 0) continue;
                if ($status == 'terlambat' && $t == 0) continue;
                if ($status == 'izin' && $i == 0) continue;
                if ($status == 'sakit' && $s == 0) continue;
                if ($status == 'alpa' && $a == 0) continue;
            }

            $rekapFinal[] = $r;
        }

        return $rekapFinal;
    }

    /**
     * Dapatkan daftar siswa (Read-only)
     */
    public function getSiswaList($keyword = null, $kelas_id = null) {
        $query = "SELECT s.*, k.nama_kelas, k.jurusan
                  FROM siswa s
                  LEFT JOIN kelas k ON s.kelas_id = k.id
                  WHERE 1=1";

        if ($kelas_id) {
            $query .= " AND s.kelas_id = :kelas_id";
        }

        if ($keyword) {
            $query .= " AND (s.nama LIKE :kw OR s.nis LIKE :kw OR s.nisn LIKE :kw)";
        }

        $query .= " ORDER BY k.nama_kelas ASC, s.nama ASC";

        $this->db->query($query);
        if ($kelas_id) {
            $this->db->bind('kelas_id', $kelas_id);
        }
        if ($keyword) {
            $this->db->bind('kw', '%' . $keyword . '%');
        }

        return $this->db->resultSet();
    }

    /**
     * Dapatkan daftar guru (Read-only)
     */
    public function getGuruList($keyword = null) {
        $query = "SELECT g.*, u.username
                  FROM guru g
                  LEFT JOIN users u ON g.user_id = u.id
                  WHERE 1=1";

        if ($keyword) {
            $query .= " AND (g.nama LIKE :kw OR g.nip LIKE :kw OR g.mata_pelajaran LIKE :kw)";
        }

        $query .= " ORDER BY g.nama ASC";

        $this->db->query($query);
        if ($keyword) {
            $this->db->bind('kw', '%' . $keyword . '%');
        }

        return $this->db->resultSet();
    }

    /**
     * Dapatkan profil Kepala Sekolah
     */
    public function getProfil($user_id) {
        $this->db->query("SELECT u.username, u.nama, u.role
                          FROM users u
                          WHERE u.id = :user_id");
        $this->db->bind('user_id', $user_id);
        return $this->db->single();
    }
}
