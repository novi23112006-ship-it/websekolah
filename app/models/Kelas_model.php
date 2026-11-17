<?php

class Kelas_model {
    private $table = 'kelas';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllKelas() {
        // Gabung dengan tabel guru untuk nama wali kelas dan hitung total siswa aktif
        $this->db->query("SELECT kelas.*, guru.nama as nama_wali,
                          (SELECT COUNT(siswa.id) FROM siswa WHERE siswa.kelas_id = kelas.id AND siswa.status = 'active') as total_siswa
                          FROM {$this->table} 
                          LEFT JOIN guru ON kelas.wali_kelas_id = guru.id 
                          ORDER BY tingkat, nama_kelas ASC");
        return $this->db->resultSet();
    }

    public function getSiswaByKelasId($kelas_id) {
        $this->db->query("SELECT id, nis, nama, jenis_kelamin FROM siswa WHERE kelas_id = :kelas_id AND status = 'active' ORDER BY nama ASC");
        $this->db->bind('kelas_id', $kelas_id);
        return $this->db->resultSet();
    }

    public function countSiswaByKelasId($kelas_id) {
        $this->db->query("SELECT COUNT(id) as total FROM siswa WHERE kelas_id = :kelas_id AND status = 'active'");
        $this->db->bind('kelas_id', $kelas_id);
        $res = $this->db->single();
        return $res ? (int)$res['total'] : 0;
    }

    public function naikKelasMassal($kelas_asal_id, $kelas_tujuan_id) {
        $this->db->query("UPDATE siswa SET kelas_id = :kelas_tujuan_id WHERE kelas_id = :kelas_asal_id AND status = 'active'");
        $this->db->bind('kelas_tujuan_id', $kelas_tujuan_id);
        $this->db->bind('kelas_asal_id', $kelas_asal_id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function luluskanKelasMassal($kelas_asal_id) {
        $this->db->query("UPDATE siswa SET status = 'inactive', kelas_id = NULL WHERE kelas_id = :kelas_asal_id AND status = 'active'");
        $this->db->bind('kelas_asal_id', $kelas_asal_id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function getKelasById($id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function checkNamaKelasExists($nama_kelas, $id = null) {
        $query = "SELECT id FROM {$this->table} WHERE nama_kelas = :nama_kelas";
        if ($id != null) {
            $query .= " AND id != :id";
        }
        $this->db->query($query);
        $this->db->bind('nama_kelas', $nama_kelas);
        if ($id != null) {
            $this->db->bind('id', $id);
        }
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function tambahDataKelas($data) {
        $query = "INSERT INTO {$this->table} (nama_kelas, jurusan, tingkat, wali_kelas_id) 
                  VALUES (:nama_kelas, :jurusan, :tingkat, :wali_kelas_id)";
        $this->db->query($query);
        $this->db->bind('nama_kelas', $data['nama_kelas']);
        $this->db->bind('jurusan', $data['jurusan']);
        $this->db->bind('tingkat', $data['tingkat']);
        $this->db->bind('wali_kelas_id', empty($data['wali_kelas_id']) ? null : $data['wali_kelas_id']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function ubahDataKelas($data) {
        $query = "UPDATE {$this->table} SET 
                    nama_kelas = :nama_kelas,
                    jurusan = :jurusan,
                    tingkat = :tingkat,
                    wali_kelas_id = :wali_kelas_id
                  WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('nama_kelas', $data['nama_kelas']);
        $this->db->bind('jurusan', $data['jurusan']);
        $this->db->bind('tingkat', $data['tingkat']);
        $this->db->bind('wali_kelas_id', empty($data['wali_kelas_id']) ? null : $data['wali_kelas_id']);
        $this->db->bind('id', $data['id']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function hapusDataKelas($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
