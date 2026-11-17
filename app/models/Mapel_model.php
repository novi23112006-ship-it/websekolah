<?php

class Mapel_model {
    private $table = 'mata_pelajaran';
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllMapel($filters = []) {
        $query = "SELECT * FROM {$this->table} WHERE 1=1";

        if (!empty($filters['kelompok'])) {
            $query .= " AND kelompok = :kelompok";
        }
        if (!empty($filters['tingkat'])) {
            $query .= " AND (tingkat = :tingkat OR tingkat = 'Semua Tingkat')";
        }
        if (!empty($filters['status'])) {
            $query .= " AND status = :status";
        }
        if (!empty($filters['keyword'])) {
            $query .= " AND (kode_mapel LIKE :kw OR nama_mapel LIKE :kw)";
        }

        $query .= " ORDER BY kelompok ASC, kode_mapel ASC";

        $this->db->query($query);
        if (!empty($filters['kelompok'])) {
            $this->db->bind('kelompok', $filters['kelompok']);
        }
        if (!empty($filters['tingkat'])) {
            $this->db->bind('tingkat', $filters['tingkat']);
        }
        if (!empty($filters['status'])) {
            $this->db->bind('status', $filters['status']);
        }
        if (!empty($filters['keyword'])) {
            $this->db->bind('kw', '%' . $filters['keyword'] . '%');
        }

        return $this->db->resultSet();
    }

    public function getMapelById($id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function checkKodeExists($kode, $exclude_id = null) {
        $query = "SELECT id FROM {$this->table} WHERE kode_mapel = :kode";
        if ($exclude_id !== null) {
            $query .= " AND id != :id";
        }
        $this->db->query($query);
        $this->db->bind('kode', strtoupper(trim($kode)));
        if ($exclude_id !== null) {
            $this->db->bind('id', $exclude_id);
        }
        return $this->db->single() ? true : false;
    }

    public function tambahMapel($data) {
        $kode = strtoupper(trim($data['kode_mapel'] ?? ''));
        $nama = trim($data['nama_mapel'] ?? '');
        $kelompok = $data['kelompok'] ?? 'Muatan Nasional';
        $tingkat = $data['tingkat'] ?? 'Semua Tingkat';
        $jam = max(1, (int)($data['jam_per_minggu'] ?? 2));
        $status = $data['status'] ?? 'active';

        if (empty($kode) || empty($nama)) {
            return ['status' => false, 'pesan' => 'Kode Mapel dan Nama Mata Pelajaran wajib diisi.'];
        }

        if ($this->checkKodeExists($kode)) {
            return ['status' => false, 'pesan' => "Kode Mapel '{$kode}' sudah digunakan."];
        }

        $query = "INSERT INTO {$this->table} (kode_mapel, nama_mapel, kelompok, tingkat, jam_per_minggu, status) 
                  VALUES (:kode, :nama, :kelompok, :tingkat, :jam, :status)";
        $this->db->query($query);
        $this->db->bind('kode', $kode);
        $this->db->bind('nama', $nama);
        $this->db->bind('kelompok', $kelompok);
        $this->db->bind('tingkat', $tingkat);
        $this->db->bind('jam', $jam);
        $this->db->bind('status', $status);
        $this->db->execute();

        return ['status' => true, 'pesan' => "Mata pelajaran '{$nama}' ({$kode}) berhasil ditambahkan."];
    }

    public function ubahMapel($data) {
        $id = $data['id'] ?? null;
        if (!$id) return ['status' => false, 'pesan' => 'ID Mapel tidak valid.'];

        $kode = strtoupper(trim($data['kode_mapel'] ?? ''));
        $nama = trim($data['nama_mapel'] ?? '');
        $kelompok = $data['kelompok'] ?? 'Muatan Nasional';
        $tingkat = $data['tingkat'] ?? 'Semua Tingkat';
        $jam = max(1, (int)($data['jam_per_minggu'] ?? 2));
        $status = $data['status'] ?? 'active';

        if (empty($kode) || empty($nama)) {
            return ['status' => false, 'pesan' => 'Kode Mapel dan Nama Mata Pelajaran wajib diisi.'];
        }

        if ($this->checkKodeExists($kode, $id)) {
            return ['status' => false, 'pesan' => "Kode Mapel '{$kode}' sudah digunakan mapel lain."];
        }

        $query = "UPDATE {$this->table} SET 
                  kode_mapel = :kode, 
                  nama_mapel = :nama, 
                  kelompok = :kelompok, 
                  tingkat = :tingkat, 
                  jam_per_minggu = :jam, 
                  status = :status 
                  WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('kode', $kode);
        $this->db->bind('nama', $nama);
        $this->db->bind('kelompok', $kelompok);
        $this->db->bind('tingkat', $tingkat);
        $this->db->bind('jam', $jam);
        $this->db->bind('status', $status);
        $this->db->bind('id', $id);
        $this->db->execute();

        return ['status' => true, 'pesan' => "Data mata pelajaran '{$nama}' berhasil diperbarui."];
    }

    public function hapusMapel($id) {
        // Cek apakah digunakan di jadwal_mengajar
        $this->db->query("SELECT COUNT(*) as cnt FROM jadwal_mengajar WHERE mapel_id = :id");
        $this->db->bind('id', $id);
        $count = $this->db->single()['cnt'];

        if ($count > 0) {
            return ['status' => false, 'pesan' => "Mata pelajaran tidak dapat dihapus karena masih digunakan dalam {$count} jadwal mengajar aktif."];
        }

        $mapel = $this->getMapelById($id);
        if (!$mapel) return ['status' => false, 'pesan' => 'Data mata pelajaran tidak ditemukan.'];

        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();

        return ['status' => true, 'pesan' => "Mata pelajaran '{$mapel['nama_mapel']}' berhasil dihapus."];
    }

    public function getStatistikMapel() {
        $stats = [
            'total' => 0,
            'nasional' => 0,
            'kewilayahan' => 0,
            'kejuruan' => 0,
            'muatan_lokal' => 0,
            'aktif' => 0
        ];

        $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $stats['total'] = (int)$this->db->single()['total'];

        $this->db->query("SELECT COUNT(*) as cnt FROM {$this->table} WHERE kelompok = 'Muatan Nasional'");
        $stats['nasional'] = (int)$this->db->single()['cnt'];

        $this->db->query("SELECT COUNT(*) as cnt FROM {$this->table} WHERE kelompok = 'Muatan Kewilayahan'");
        $stats['kewilayahan'] = (int)$this->db->single()['cnt'];

        $this->db->query("SELECT COUNT(*) as cnt FROM {$this->table} WHERE kelompok = 'Muatan Peminatan Kejuruan'");
        $stats['kejuruan'] = (int)$this->db->single()['cnt'];

        $this->db->query("SELECT COUNT(*) as cnt FROM {$this->table} WHERE kelompok = 'Muatan Lokal'");
        $stats['muatan_lokal'] = (int)$this->db->single()['cnt'];

        $this->db->query("SELECT COUNT(*) as cnt FROM {$this->table} WHERE status = 'active'");
        $stats['aktif'] = (int)$this->db->single()['cnt'];

        return $stats;
    }
}
