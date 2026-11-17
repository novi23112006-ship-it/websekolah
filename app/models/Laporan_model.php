<?php

class Laporan_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // =============================================
    // LAPORAN ABSENSI SISWA
    // =============================================

    /**
     * Bangun WHERE clause secara dinamis.
     * role_context: ['role' => '...', 'kelas_id' => ..., 'siswa_id' => ...]
     */
    private function buildSiswaWhere($filters, $role_context) {
        $conditions = [];
        $params = [];

        // --- Role-based restriction ---
        $role = $role_context['role'];

        if ($role === 'wali_kelas') {
            // Wali kelas hanya melihat kelasnya
            $conditions[] = 's.kelas_id = :wk_kelas_id';
            $params['wk_kelas_id'] = $role_context['kelas_id'];
        } elseif ($role === 'siswa') {
            // Siswa hanya melihat data dirinya
            $conditions[] = 's.id = :my_siswa_id';
            $params['my_siswa_id'] = $role_context['siswa_id'];
        }

        // --- Filter dari user input ---
        if (!empty($filters['tanggal_mulai'])) {
            $conditions[] = 'a.tanggal >= :tanggal_mulai';
            $params['tanggal_mulai'] = $filters['tanggal_mulai'];
        }
        if (!empty($filters['tanggal_akhir'])) {
            $conditions[] = 'a.tanggal <= :tanggal_akhir';
            $params['tanggal_akhir'] = $filters['tanggal_akhir'];
        }
        if (!empty($filters['kelas_id']) && $role !== 'wali_kelas' && $role !== 'siswa') {
            $conditions[] = 's.kelas_id = :kelas_id';
            $params['kelas_id'] = $filters['kelas_id'];
        }
        if (!empty($filters['siswa_id']) && $role !== 'siswa') {
            $conditions[] = 's.id = :siswa_id';
            $params['siswa_id'] = $filters['siswa_id'];
        }
        if (!empty($filters['jurusan'])) {
            $conditions[] = 'k.jurusan = :jurusan';
            $params['jurusan'] = $filters['jurusan'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = 'a.status = :status';
            $params['status'] = $filters['status'];
        }

        $where = '';
        if (!empty($conditions)) {
            $where = 'WHERE ' . implode(' AND ', $conditions);
        }

        return [$where, $params];
    }

    public function getLaporanSiswa($filters, $page, $per_page, $role_context) {
        [$where, $params] = $this->buildSiswaWhere($filters, $role_context);
        $offset = ($page - 1) * $per_page;

        $query = "SELECT a.id, a.tanggal, a.jam_masuk, a.jam_pulang, a.status, a.keterangan, a.siswa_id,
                         s.nis, s.nama, k.nama_kelas
                  FROM absensi_siswa a
                  JOIN siswa s ON a.siswa_id = s.id
                  LEFT JOIN kelas k ON s.kelas_id = k.id
                  {$where}
                  ORDER BY a.tanggal DESC, a.id DESC
                  LIMIT :limit OFFSET :offset";

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind('limit', (int)$per_page, PDO::PARAM_INT);
        $this->db->bind('offset', (int)$offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function countLaporanSiswa($filters, $role_context) {
        [$where, $params] = $this->buildSiswaWhere($filters, $role_context);

        $query = "SELECT COUNT(a.id) as total
                  FROM absensi_siswa a
                  JOIN siswa s ON a.siswa_id = s.id
                  LEFT JOIN kelas k ON s.kelas_id = k.id
                  {$where}";

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->single()['total'];
    }

    public function getAllLaporanSiswaForExport($filters, $role_context) {
        [$where, $params] = $this->buildSiswaWhere($filters, $role_context);

        $query = "SELECT a.id, a.tanggal, a.jam_masuk, a.jam_pulang, a.status, a.keterangan,
                         s.nis, s.nama, k.nama_kelas
                  FROM absensi_siswa a
                  JOIN siswa s ON a.siswa_id = s.id
                  LEFT JOIN kelas k ON s.kelas_id = k.id
                  {$where}
                  ORDER BY a.tanggal DESC, a.id DESC";

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet();
    }

    // =============================================
    // LAPORAN ABSENSI GURU
    // =============================================

    private function buildGuruWhere($filters, $role_context) {
        $conditions = [];
        $params = [];

        $role = $role_context['role'];

        if ($role === 'guru') {
            // Guru hanya melihat data dirinya
            $conditions[] = 'g.id = :my_guru_id';
            $params['my_guru_id'] = $role_context['guru_id'];
        }

        if (!empty($filters['tanggal_mulai'])) {
            $conditions[] = 'a.tanggal >= :tanggal_mulai';
            $params['tanggal_mulai'] = $filters['tanggal_mulai'];
        }
        if (!empty($filters['tanggal_akhir'])) {
            $conditions[] = 'a.tanggal <= :tanggal_akhir';
            $params['tanggal_akhir'] = $filters['tanggal_akhir'];
        }
        if (!empty($filters['guru_id']) && $role !== 'guru') {
            $conditions[] = 'g.id = :guru_id';
            $params['guru_id'] = $filters['guru_id'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = 'a.status = :status';
            $params['status'] = $filters['status'];
        }

        $where = '';
        if (!empty($conditions)) {
            $where = 'WHERE ' . implode(' AND ', $conditions);
        }

        return [$where, $params];
    }

    public function getLaporanGuru($filters, $page, $per_page, $role_context) {
        [$where, $params] = $this->buildGuruWhere($filters, $role_context);
        $offset = ($page - 1) * $per_page;

        $query = "SELECT a.id, a.tanggal, a.jam_scan, a.jam_pulang, a.status, a.keterangan,
                         g.nip, g.nama, g.mata_pelajaran
                  FROM absensi_guru a
                  JOIN guru g ON a.guru_id = g.id
                  {$where}
                  ORDER BY a.tanggal DESC, a.id DESC
                  LIMIT :limit OFFSET :offset";

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind('limit', (int)$per_page, PDO::PARAM_INT);
        $this->db->bind('offset', (int)$offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    public function countLaporanGuru($filters, $role_context) {
        [$where, $params] = $this->buildGuruWhere($filters, $role_context);

        $query = "SELECT COUNT(a.id) as total
                  FROM absensi_guru a
                  JOIN guru g ON a.guru_id = g.id
                  {$where}";

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->single()['total'];
    }

    public function getAllLaporanGuruForExport($filters, $role_context) {
        [$where, $params] = $this->buildGuruWhere($filters, $role_context);

        $query = "SELECT a.id, a.tanggal, a.jam_scan, a.jam_pulang, a.status, a.keterangan,
                         g.nip, g.nama, g.mata_pelajaran
                  FROM absensi_guru a
                  JOIN guru g ON a.guru_id = g.id
                  {$where}
                  ORDER BY a.tanggal DESC, a.id DESC";

        $this->db->query($query);
        foreach ($params as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet();
    }

    // =============================================
    // HELPERS untuk dropdown filter
    // =============================================
    public function getAllKelasForFilter() {
        $this->db->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat, nama_kelas ASC");
        return $this->db->resultSet();
    }

    public function getAllJurusanForFilter() {
        $this->db->query("SELECT DISTINCT jurusan FROM kelas WHERE jurusan IS NOT NULL AND jurusan != '' ORDER BY jurusan ASC");
        return $this->db->resultSet();
    }

    public function getAllSiswaForFilter($kelas_id = '') {
        if (!empty($kelas_id)) {
            $this->db->query("SELECT id, nama, nis FROM siswa WHERE kelas_id = :kelas_id ORDER BY nama ASC");
            $this->db->bind('kelas_id', $kelas_id);
        } else {
            $this->db->query("SELECT id, nama, nis FROM siswa ORDER BY nama ASC");
        }
        return $this->db->resultSet();
    }

    public function getAllGuruForFilter() {
        $this->db->query("SELECT id, nama, nip FROM guru ORDER BY nama ASC");
        return $this->db->resultSet();
    }

    public function getKelasIdByUserId($user_id) {
        $this->db->query("SELECT kelas.id FROM kelas 
                          JOIN guru ON kelas.wali_kelas_id = guru.id 
                          WHERE guru.user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $row = $this->db->single();
        return $row ? $row['id'] : null;
    }

    public function getGuruIdByUserId($user_id) {
        $this->db->query("SELECT id FROM guru WHERE user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $row = $this->db->single();
        return $row ? $row['id'] : null;
    }

    public function getSiswaIdByUserId($user_id) {
        $this->db->query("SELECT id FROM siswa WHERE user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $row = $this->db->single();
        return $row ? $row['id'] : null;
    }

    public function getStatistikAbsensiSiswa($siswa_id) {
        $this->db->query("SELECT status, COUNT(id) as total FROM absensi_siswa WHERE siswa_id = :siswa_id GROUP BY status");
        $this->db->bind('siswa_id', $siswa_id);
        $stats = $this->db->resultSet();
        
        $result = [
            'hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0
        ];
        foreach ($stats as $s) {
            if (isset($result[$s['status']])) {
                $result[$s['status']] = $s['total'];
            }
        }
        return $result;
    }
}
