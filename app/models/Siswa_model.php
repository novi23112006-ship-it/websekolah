<?php

class Siswa_model {
    private $table = 'siswa';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllSiswa($kelas_id = '') {
        $query = "SELECT siswa.*, kelas.nama_kelas 
                  FROM {$this->table} 
                  LEFT JOIN kelas ON siswa.kelas_id = kelas.id ";
        
        if (!empty($kelas_id)) {
            $query .= " WHERE siswa.kelas_id = :kelas_id ";
        }
        
        $query .= " ORDER BY siswa.nama ASC";
        
        $this->db->query($query);
        
        if (!empty($kelas_id)) {
            $this->db->bind('kelas_id', $kelas_id);
        }
        
        return $this->db->resultSet();
    }

    public function getSiswaById($id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function findByBarcode($barcode) {
        $this->db->query("SELECT * FROM {$this->table} WHERE barcode = :barcode");
        $this->db->bind('barcode', $barcode);
        return $this->db->single();
    }

    public function generateBarcode($id) {
        $siswa = $this->getSiswaById($id);
        if ($siswa && empty($siswa['barcode'])) {
            $barcode = 'STD-' . str_pad($id, 6, '0', STR_PAD_LEFT);
            $this->db->query("UPDATE {$this->table} SET barcode = :barcode WHERE id = :id");
            $this->db->bind('barcode', $barcode);
            $this->db->bind('id', $id);
            $this->db->execute();
            return true;
        }
        return false;
    }

    public function checkDuplicateNisRfid($nis, $rfid_uid, $id = null) {
        $query = "SELECT id FROM {$this->table} WHERE (nis = :nis OR rfid_uid = :rfid_uid)";
        if ($id != null) {
            $query .= " AND id != :id";
        }
        $this->db->query($query);
        $this->db->bind('nis', $nis);
        $this->db->bind('rfid_uid', $rfid_uid);
        if ($id != null) {
            $this->db->bind('id', $id);
        }
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function checkDuplicateNisn($nisn, $id = null) {
        if(empty($nisn)) return false;
        
        $query = "SELECT id FROM {$this->table} WHERE nisn = :nisn";
        if ($id != null) {
            $query .= " AND id != :id";
        }
        $this->db->query($query);
        $this->db->bind('nisn', $nisn);
        if ($id != null) {
            $this->db->bind('id', $id);
        }
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function tambahDataSiswa($data) {
        $password = password_hash('password', PASSWORD_BCRYPT);
        
        // 1. Insert into users table
        $queryUser = "INSERT INTO users (username, password, role, status) VALUES (:username, :password, 'siswa', 'active')";
        $this->db->query($queryUser);
        $this->db->bind('username', $data['nis']);
        $this->db->bind('password', $password);
        $this->db->execute();
        
        $this->db->query("SELECT LAST_INSERT_ID() as id");
        $user_id = $this->db->single()['id'];
        
        // 2. Insert into siswa table
        $querySiswa = "INSERT INTO {$this->table} (user_id, nis, nisn, nama, jenis_kelamin, kelas_id, rfid_uid, alamat, status) 
                       VALUES (:user_id, :nis, :nisn, :nama, :jenis_kelamin, :kelas_id, :rfid_uid, :alamat, :status)";
        $this->db->query($querySiswa);
        $this->db->bind('user_id', $user_id);
        $this->db->bind('nis', $data['nis']);
        $this->db->bind('nisn', empty($data['nisn']) ? null : $data['nisn']);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
        $this->db->bind('kelas_id', empty($data['kelas_id']) ? null : $data['kelas_id']);
        $this->db->bind('rfid_uid', $data['rfid_uid']);
        $this->db->bind('alamat', $data['alamat']);
        $this->db->bind('status', $data['status']);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function ubahDataSiswa($data) {
        // Update siswa table
        $querySiswa = "UPDATE {$this->table} SET 
                        nis = :nis,
                        nisn = :nisn,
                        nama = :nama,
                        jenis_kelamin = :jenis_kelamin,
                        kelas_id = :kelas_id,
                        rfid_uid = :rfid_uid,
                        alamat = :alamat,
                        status = :status
                      WHERE id = :id";
        $this->db->query($querySiswa);
        $this->db->bind('nis', $data['nis']);
        $this->db->bind('nisn', empty($data['nisn']) ? null : $data['nisn']);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('jenis_kelamin', $data['jenis_kelamin']);
        $this->db->bind('kelas_id', empty($data['kelas_id']) ? null : $data['kelas_id']);
        $this->db->bind('rfid_uid', $data['rfid_uid']);
        $this->db->bind('alamat', $data['alamat']);
        $this->db->bind('status', $data['status']);
        $this->db->bind('id', $data['id']);
        
        $this->db->execute();

        // Also update username in users table if nis changed
        $siswa = $this->getSiswaById($data['id']);
        if ($siswa) {
            $queryUser = "UPDATE users SET username = :username, status = :status WHERE id = :user_id";
            $this->db->query($queryUser);
            $this->db->bind('username', $data['nis']);
            $this->db->bind('status', $data['status']);
            $this->db->bind('user_id', $siswa['user_id']);
            $this->db->execute();
        }

        return true;
    }

    public function hapusDataSiswa($id) {
        $siswa = $this->getSiswaById($id);
        if ($siswa) {
            $query = "DELETE FROM users WHERE id = :user_id";
            $this->db->query($query);
            $this->db->bind('user_id', $siswa['user_id']);
            $this->db->execute();
            return $this->db->rowCount();
        }
        return 0;
    }
}
