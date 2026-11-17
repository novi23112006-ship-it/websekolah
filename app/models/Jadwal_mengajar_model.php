<?php

class Jadwal_mengajar_model {
    private $table = 'jadwal_mengajar';
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllJadwalMengajar($filters = []) {
        $query = "SELECT jm.*, 
                         g.nama as nama_guru, g.nip, g.foto as foto_guru,
                         k.nama_kelas,
                         mp.kode_mapel, mp.nama_mapel, mp.kelompok
                  FROM {$this->table} jm
                  INNER JOIN guru g ON jm.guru_id = g.id
                  INNER JOIN kelas k ON jm.kelas_id = k.id
                  LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                  WHERE 1=1";

        if (!empty($filters['hari'])) {
            $query .= " AND jm.hari = :hari";
        }
        if (!empty($filters['guru_id'])) {
            $query .= " AND jm.guru_id = :guru_id";
        }
        if (!empty($filters['kelas_id'])) {
            $query .= " AND jm.kelas_id = :kelas_id";
        }
        if (!empty($filters['status'])) {
            $query .= " AND jm.status = :status";
        }
        if (!empty($filters['keyword'])) {
            $query .= " AND (g.nama LIKE :kw OR k.nama_kelas LIKE :kw OR mp.nama_mapel LIKE :kw OR jm.ruangan LIKE :kw)";
        }

        $query .= " ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC";

        $this->db->query($query);
        if (!empty($filters['hari'])) {
            $this->db->bind('hari', $filters['hari']);
        }
        if (!empty($filters['guru_id'])) {
            $this->db->bind('guru_id', $filters['guru_id']);
        }
        if (!empty($filters['kelas_id'])) {
            $this->db->bind('kelas_id', $filters['kelas_id']);
        }
        if (!empty($filters['status'])) {
            $this->db->bind('status', $filters['status']);
        }
        if (!empty($filters['keyword'])) {
            $this->db->bind('kw', '%' . $filters['keyword'] . '%');
        }

        return $this->db->resultSet();
    }

    public function getJadwalById($id) {
        $query = "SELECT jm.*, 
                         g.nama as nama_guru, g.nip,
                         k.nama_kelas,
                         mp.kode_mapel, mp.nama_mapel
                  FROM {$this->table} jm
                  INNER JOIN guru g ON jm.guru_id = g.id
                  INNER JOIN kelas k ON jm.kelas_id = k.id
                  LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                  WHERE jm.id = :id";
        $this->db->query($query);
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    /**
     * Cek apakah ada bentrok jadwal mengajar
     */
    public function checkBentrok($guru_id, $kelas_id, $hari, $jam_mulai, $jam_selesai, $exclude_id = null) {
        // 1. Cek bentrok guru (guru yang sama tidak bisa mengajar 2 kelas di waktu yang sama)
        $queryGuru = "SELECT jm.*, k.nama_kelas 
                      FROM {$this->table} jm 
                      JOIN kelas k ON jm.kelas_id = k.id
                      WHERE jm.guru_id = :guru_id 
                        AND jm.hari = :hari 
                        AND jm.status = 'active'
                        AND (:jam_mulai < jm.jam_selesai AND :jam_selesai > jm.jam_mulai)";
        if ($exclude_id) {
            $queryGuru .= " AND jm.id != :eid";
        }
        $this->db->query($queryGuru);
        $this->db->bind('guru_id', $guru_id);
        $this->db->bind('hari', $hari);
        $this->db->bind('jam_mulai', $jam_mulai);
        $this->db->bind('jam_selesai', $jam_selesai);
        if ($exclude_id) {
            $this->db->bind('eid', $exclude_id);
        }
        $bentrokGuru = $this->db->single();
        if ($bentrokGuru) {
            return [
                'bentrok' => true,
                'pesan' => "Jadwal bentrok! Guru yang dipilih sudah memiliki jadwal mengajar di kelas {$bentrokGuru['nama_kelas']} pada hari {$hari} jam {$bentrokGuru['jam_mulai']} - {$bentrokGuru['jam_selesai']}."
            ];
        }

        // 2. Cek bentrok kelas (kelas yang sama tidak bisa menerima 2 jadwal pelajaran berbeda di waktu yang sama)
        $queryKelas = "SELECT jm.*, g.nama as nama_guru 
                       FROM {$this->table} jm 
                       JOIN guru g ON jm.guru_id = g.id
                       WHERE jm.kelas_id = :kelas_id 
                         AND jm.hari = :hari 
                         AND jm.status = 'active'
                         AND (:jam_mulai < jm.jam_selesai AND :jam_selesai > jm.jam_mulai)";
        if ($exclude_id) {
            $queryKelas .= " AND jm.id != :eid";
        }
        $this->db->query($queryKelas);
        $this->db->bind('kelas_id', $kelas_id);
        $this->db->bind('hari', $hari);
        $this->db->bind('jam_mulai', $jam_mulai);
        $this->db->bind('jam_selesai', $jam_selesai);
        if ($exclude_id) {
            $this->db->bind('eid', $exclude_id);
        }
        $bentrokKelas = $this->db->single();
        if ($bentrokKelas) {
            return [
                'bentrok' => true,
                'pesan' => "Jadwal bentrok! Kelas yang dipilih sudah dijadwalkan pelajaran bersama {$bentrokKelas['nama_guru']} pada hari {$hari} jam {$bentrokKelas['jam_mulai']} - {$bentrokKelas['jam_selesai']}."
            ];
        }

        return ['bentrok' => false];
    }

    public function tambahJadwal($data) {
        $guru_id = $data['guru_id'] ?? null;
        $mapel_id = !empty($data['mapel_id']) ? $data['mapel_id'] : null;
        $kelas_id = $data['kelas_id'] ?? null;
        $hari = $data['hari'] ?? '';
        $jam_mulai = $data['jam_mulai'] ?? '';
        $jam_selesai = $data['jam_selesai'] ?? '';
        $ruangan = trim($data['ruangan'] ?? 'Ruang Kelas');
        $keterangan = trim($data['keterangan'] ?? '');
        $status = $data['status'] ?? 'active';

        if (empty($guru_id) || empty($kelas_id) || empty($hari) || empty($jam_mulai) || empty($jam_selesai)) {
            return ['status' => false, 'pesan' => 'Guru, Kelas, Hari, Jam Mulai, dan Jam Selesai wajib diisi.'];
        }

        if (strtotime($jam_selesai) <= strtotime($jam_mulai)) {
            return ['status' => false, 'pesan' => 'Jam Selesai harus lebih besar dari Jam Mulai.'];
        }

        // Cek Bentrok
        $cek = $this->checkBentrok($guru_id, $kelas_id, $hari, $jam_mulai, $jam_selesai);
        if ($cek['bentrok']) {
            return ['status' => false, 'pesan' => $cek['pesan']];
        }

        $query = "INSERT INTO {$this->table} (guru_id, mapel_id, kelas_id, hari, jam_mulai, jam_selesai, ruangan, keterangan, status) 
                  VALUES (:guru_id, :mapel_id, :kelas_id, :hari, :jam_mulai, :jam_selesai, :ruangan, :keterangan, :status)";
        $this->db->query($query);
        $this->db->bind('guru_id', $guru_id);
        $this->db->bind('mapel_id', $mapel_id);
        $this->db->bind('kelas_id', $kelas_id);
        $this->db->bind('hari', $hari);
        $this->db->bind('jam_mulai', $jam_mulai);
        $this->db->bind('jam_selesai', $jam_selesai);
        $this->db->bind('ruangan', $ruangan);
        $this->db->bind('keterangan', $keterangan);
        $this->db->bind('status', $status);
        $this->db->execute();

        return ['status' => true, 'pesan' => 'Jadwal mengajar berhasil ditambahkan.'];
    }

    public function ubahJadwal($data) {
        $id = $data['id'] ?? null;
        if (!$id) return ['status' => false, 'pesan' => 'ID Jadwal tidak valid.'];

        $guru_id = $data['guru_id'] ?? null;
        $mapel_id = !empty($data['mapel_id']) ? $data['mapel_id'] : null;
        $kelas_id = $data['kelas_id'] ?? null;
        $hari = $data['hari'] ?? '';
        $jam_mulai = $data['jam_mulai'] ?? '';
        $jam_selesai = $data['jam_selesai'] ?? '';
        $ruangan = trim($data['ruangan'] ?? 'Ruang Kelas');
        $keterangan = trim($data['keterangan'] ?? '');
        $status = $data['status'] ?? 'active';

        if (empty($guru_id) || empty($kelas_id) || empty($hari) || empty($jam_mulai) || empty($jam_selesai)) {
            return ['status' => false, 'pesan' => 'Guru, Kelas, Hari, Jam Mulai, dan Jam Selesai wajib diisi.'];
        }

        if (strtotime($jam_selesai) <= strtotime($jam_mulai)) {
            return ['status' => false, 'pesan' => 'Jam Selesai harus lebih besar dari Jam Mulai.'];
        }

        // Cek Bentrok
        $cek = $this->checkBentrok($guru_id, $kelas_id, $hari, $jam_mulai, $jam_selesai, $id);
        if ($cek['bentrok']) {
            return ['status' => false, 'pesan' => $cek['pesan']];
        }

        $query = "UPDATE {$this->table} SET 
                  guru_id = :guru_id, 
                  mapel_id = :mapel_id, 
                  kelas_id = :kelas_id, 
                  hari = :hari, 
                  jam_mulai = :jam_mulai, 
                  jam_selesai = :jam_selesai, 
                  ruangan = :ruangan, 
                  keterangan = :keterangan, 
                  status = :status 
                  WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('guru_id', $guru_id);
        $this->db->bind('mapel_id', $mapel_id);
        $this->db->bind('kelas_id', $kelas_id);
        $this->db->bind('hari', $hari);
        $this->db->bind('jam_mulai', $jam_mulai);
        $this->db->bind('jam_selesai', $jam_selesai);
        $this->db->bind('ruangan', $ruangan);
        $this->db->bind('keterangan', $keterangan);
        $this->db->bind('status', $status);
        $this->db->bind('id', $id);
        $this->db->execute();

        return ['status' => true, 'pesan' => 'Jadwal mengajar berhasil diperbarui.'];
    }

    public function hapusJadwal($id) {
        $j = $this->getJadwalById($id);
        if (!$j) return ['status' => false, 'pesan' => 'Data jadwal tidak ditemukan.'];

        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();

        return ['status' => true, 'pesan' => "Jadwal mengajar {$j['nama_guru']} ({$j['nama_kelas']} - {$j['hari']}) berhasil dihapus."];
    }

    public function getStatistikJadwal() {
        $stats = [
            'total_jadwal' => 0,
            'total_guru' => 0,
            'total_kelas' => 0,
            'aktif' => 0
        ];

        $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $stats['total_jadwal'] = (int)$this->db->single()['total'];

        $this->db->query("SELECT COUNT(DISTINCT guru_id) as cnt FROM {$this->table}");
        $stats['total_guru'] = (int)$this->db->single()['cnt'];

        $this->db->query("SELECT COUNT(DISTINCT kelas_id) as cnt FROM {$this->table}");
        $stats['total_kelas'] = (int)$this->db->single()['cnt'];

        $this->db->query("SELECT COUNT(*) as cnt FROM {$this->table} WHERE status = 'active'");
        $stats['aktif'] = (int)$this->db->single()['cnt'];

        return $stats;
    }

    public function getAllGuruList() {
        $this->db->query("SELECT id, nip, nama, mata_pelajaran FROM guru WHERE status = 'active' ORDER BY nama ASC");
        return $this->db->resultSet();
    }

    public function getAllKelasList() {
        $this->db->query("SELECT id, nama_kelas, jurusan FROM kelas ORDER BY nama_kelas ASC");
        return $this->db->resultSet();
    }

    public function getAllMapelList() {
        $this->db->query("SELECT id, kode_mapel, nama_mapel, kelompok FROM mata_pelajaran WHERE status = 'active' ORDER BY nama_mapel ASC");
        return $this->db->resultSet();
    }

    /**
     * Dapatkan jadwal aktif untuk satu kelas tertentu (opsional filter hari).
     */
    public function getJadwalByKelas($kelas_id, $hari = null) {
        $query = "SELECT jm.*, 
                         g.nama as nama_guru, g.nip, g.foto as foto_guru, g.mata_pelajaran as spesialisasi_guru,
                         k.nama_kelas, k.jurusan,
                         mp.kode_mapel, mp.nama_mapel, mp.kelompok
                  FROM {$this->table} jm
                  INNER JOIN guru g ON jm.guru_id = g.id
                  INNER JOIN kelas k ON jm.kelas_id = k.id
                  LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                  WHERE jm.kelas_id = :kelas_id AND jm.status = 'active'";
        if (!empty($hari)) {
            $query .= " AND jm.hari = :hari";
        }
        $query .= " ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC";

        $this->db->query($query);
        $this->db->bind('kelas_id', $kelas_id);
        if (!empty($hari)) {
            $this->db->bind('hari', $hari);
        }
        return $this->db->resultSet();
    }

    /**
     * Dapatkan jadwal kelas yang dikelompokkan per hari (Senin s/d Sabtu).
     */
    public function getJadwalMingguanKelas($kelas_id) {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $grouped = [];
        foreach ($days as $day) {
            $grouped[$day] = [];
        }

        $all = $this->getJadwalByKelas($kelas_id);
        foreach ($all as $j) {
            if (isset($grouped[$j['hari']])) {
                $grouped[$j['hari']][] = $j;
            }
        }
        return $grouped;
    }

    /**
     * Dapatkan jadwal mengajar guru tertentu (opsional filter hari).
     */
    public function getJadwalByGuru($guru_id, $hari = null) {
        $query = "SELECT jm.*, 
                         g.nama as nama_guru, g.nip,
                         k.nama_kelas, k.jurusan,
                         (SELECT COUNT(*) FROM siswa WHERE siswa.kelas_id = k.id) as total_siswa_kelas,
                         mp.kode_mapel, mp.nama_mapel, mp.kelompok
                  FROM {$this->table} jm
                  INNER JOIN guru g ON jm.guru_id = g.id
                  INNER JOIN kelas k ON jm.kelas_id = k.id
                  LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                  WHERE jm.guru_id = :guru_id AND jm.status = 'active'";
        if (!empty($hari)) {
            $query .= " AND jm.hari = :hari";
        }
        $query .= " ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC";

        $this->db->query($query);
        $this->db->bind('guru_id', $guru_id);
        if (!empty($hari)) {
            $this->db->bind('hari', $hari);
        }
        return $this->db->resultSet();
    }

    /**
     * Dapatkan jadwal mengajar guru untuk hari Senin hingga Jumat (dikelompokkan per hari).
     */
    public function getJadwalGuruSeninJumat($guru_id) {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $grouped = [];
        foreach ($days as $day) {
            $grouped[$day] = [];
        }

        $all = $this->getJadwalByGuru($guru_id);
        foreach ($all as $j) {
            if (in_array($j['hari'], $days)) {
                $grouped[$j['hari']][] = $j;
            }
        }
        return $grouped;
    }

    /**
     * Helper nama hari bahasa Indonesia
     */
    public static function getHariIndonesia($date = null) {
        $day = date('l', $date ? strtotime($date) : time());
        $map = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        return $map[$day] ?? 'Senin';
    }
}
