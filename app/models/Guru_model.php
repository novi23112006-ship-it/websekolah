<?php

class Guru_model {
    private $table = 'guru';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllGuru() {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY nama ASC");
        return $this->db->resultSet();
    }

    public function checkDuplicateNipBarcode($nip, $barcode, $id = null) {
        $query = "SELECT id FROM {$this->table} WHERE (nip = :nip OR barcode = :barcode)";
        if ($id != null) {
            $query .= " AND id != :id";
        }
        $this->db->query($query);
        $this->db->bind('nip', $nip);
        $this->db->bind('barcode', $barcode);
        if ($id != null) {
            $this->db->bind('id', $id);
        }
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function getGuruById($id) {
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
        $guru = $this->getGuruById($id);
        if ($guru && empty($guru['barcode'])) {
            $isKepsek = (isset($guru['mata_pelajaran']) && stripos($guru['mata_pelajaran'], 'Kepala') !== false);
            $prefix = $isKepsek ? 'BC-KEPSEK-' : 'BC-GURU-';
            $candidate = $prefix . str_pad($id, 3, '0', STR_PAD_LEFT);
            if ($this->checkDuplicateNipBarcode('__NO_NIP__', $candidate, $id)) {
                $candidate = $prefix . str_pad($id, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(uniqid(), -3));
            }
            $this->db->query("UPDATE {$this->table} SET barcode = :barcode WHERE id = :id");
            $this->db->bind('barcode', $candidate);
            $this->db->bind('id', $id);
            $this->db->execute();
            return $candidate;
        }
        return false;
    }

    public function tambahDataGuru($data) {
        $password = password_hash('password', PASSWORD_BCRYPT);
        
        // Auto-generate barcode jika kosong
        $barcode = trim($data['barcode'] ?? '');
        if (empty($barcode)) {
            $isKepsek = (isset($data['mata_pelajaran']) && stripos($data['mata_pelajaran'], 'Kepala') !== false);
            $prefix = $isKepsek ? 'BC-KEPSEK-' : 'BC-GURU-';
            $barcode = $prefix . strtoupper(bin2hex(random_bytes(3)));
        }
        
        // 1. Insert into users table
        $queryUser = "INSERT INTO users (username, password, role, status) VALUES (:username, :password, 'guru', 'active')";
        $this->db->query($queryUser);
        $this->db->bind('username', $data['nip']);
        $this->db->bind('password', $password);
        $this->db->execute();
        
        // Fetch last insert id directly from DB query
        $this->db->query("SELECT LAST_INSERT_ID() as id");
        $user_id_row = $this->db->single();
        $user_id = $user_id_row['id'];
        
        // 2. Insert into guru table
        $queryGuru = "INSERT INTO {$this->table} (user_id, nip, nama, mata_pelajaran, barcode) 
                      VALUES (:user_id, :nip, :nama, :mata_pelajaran, :barcode)";
        $this->db->query($queryGuru);
        $this->db->bind('user_id', $user_id);
        $this->db->bind('nip', $data['nip']);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('mata_pelajaran', $data['mata_pelajaran']);
        $this->db->bind('barcode', $barcode);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function ubahDataGuru($data) {
        $barcode = trim($data['barcode'] ?? '');
        if (empty($barcode)) {
            $existing = $this->getGuruById($data['id']);
            if (!empty($existing['barcode'])) {
                $barcode = $existing['barcode'];
            } else {
                $isKepsek = (isset($data['mata_pelajaran']) && stripos($data['mata_pelajaran'], 'Kepala') !== false);
                $prefix = $isKepsek ? 'BC-KEPSEK-' : 'BC-GURU-';
                $barcode = $prefix . str_pad($data['id'], 3, '0', STR_PAD_LEFT);
            }
        }

        // Update guru table
        $queryGuru = "UPDATE {$this->table} SET 
                        nip = :nip,
                        nama = :nama,
                        mata_pelajaran = :mata_pelajaran,
                        barcode = :barcode
                      WHERE id = :id";
        $this->db->query($queryGuru);
        $this->db->bind('nip', $data['nip']);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('mata_pelajaran', $data['mata_pelajaran']);
        $this->db->bind('barcode', $barcode);
        $this->db->bind('id', $data['id']);
        
        $this->db->execute();
        $rowCount = $this->db->rowCount();

        // Also update username in users table if nip changed
        $guru = $this->getGuruById($data['id']);
        if ($guru) {
            $queryUser = "UPDATE users SET username = :username WHERE id = :user_id";
            $this->db->query($queryUser);
            $this->db->bind('username', $data['nip']);
            $this->db->bind('user_id', $guru['user_id']);
            $this->db->execute();
        }

        return true; // As long as no exception
    }

    public function hapusDataGuru($id) {
        $guru = $this->getGuruById($id);
        if ($guru) {
            // Because of ON DELETE CASCADE on users->guru, deleting users will delete guru
            $query = "DELETE FROM users WHERE id = :user_id";
            $this->db->query($query);
            $this->db->bind('user_id', $guru['user_id']);
            $this->db->execute();
            return $this->db->rowCount();
        }
        return 0;
    }
}
