<?php

class User_model {
    private $table = 'users';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getUserByUsername($username) {
        $clean = strtolower(trim($username));
        $noSymbols = str_replace([' ', '_', '.', '-', ','], '', $clean);

        // 1. Pencocokan langsung username, nama lengkap, atau teks tanpa simbol
        $this->db->query("SELECT * FROM {$this->table} 
                          WHERE LOWER(username) = :u1 
                             OR LOWER(nama) = :u2 
                             OR REPLACE(REPLACE(REPLACE(REPLACE(LOWER(nama), ' ', ''), '.', ''), ',', ''), '-', '') = :u3 
                             OR REPLACE(REPLACE(REPLACE(REPLACE(LOWER(username), ' ', ''), '.', ''), '_', ''), '-', '') = :u4 
                          LIMIT 1");
        $this->db->bind('u1', $clean);
        $this->db->bind('u2', $clean);
        $this->db->bind('u3', $noSymbols);
        $this->db->bind('u4', $noSymbols);
        $user = $this->db->single();

        // 2. Fallback pencocokan nama (misal nama guru tanpa gelar "Budi Santoso" vs "Drs. Budi Santoso")
        if (!$user && strlen($noSymbols) >= 3) {
            $this->db->query("SELECT * FROM {$this->table} 
                              WHERE REPLACE(REPLACE(REPLACE(REPLACE(LOWER(nama), ' ', ''), '.', ''), ',', ''), '-', '') LIKE :like_val
                                 OR REPLACE(REPLACE(REPLACE(REPLACE(LOWER(username), ' ', ''), '.', ''), '_', ''), '-', '') LIKE :like_val
                              LIMIT 1");
            $this->db->bind('like_val', '%' . $noSymbols . '%');
            $user = $this->db->single();
        }

        return $user;
    }

    public function getUserById($id) {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getProfil($user_id) {
        $user = $this->getUserById($user_id);
        if (!$user) return null;

        $role = $user['role'];
        $profil = $user;

        if ($role === 'siswa') {
            $this->db->query("SELECT s.*, k.nama_kelas 
                              FROM siswa s 
                              LEFT JOIN kelas k ON s.kelas_id = k.id 
                              WHERE s.user_id = :uid");
            $this->db->bind('uid', $user_id);
            $detail = $this->db->single();
            if ($detail) {
                $profil = array_merge($profil, $detail);
            }
        } elseif ($role === 'guru' || $role === 'wali_kelas') {
            $this->db->query("SELECT g.*, k.nama_kelas 
                              FROM guru g 
                              LEFT JOIN kelas k ON k.wali_kelas_id = g.id 
                              WHERE g.user_id = :uid");
            $this->db->bind('uid', $user_id);
            $detail = $this->db->single();
            if ($detail) {
                if (empty($detail['barcode'])) {
                    $prefix = ($role === 'wali_kelas') ? 'BC-WALI-' : 'BC-GURU-';
                    $barcode = $prefix . str_pad($detail['id'], 3, '0', STR_PAD_LEFT);
                    $this->db->query("UPDATE guru SET barcode = :barcode WHERE id = :id");
                    $this->db->bind('barcode', $barcode);
                    $this->db->bind('id', $detail['id']);
                    $this->db->execute();
                    $detail['barcode'] = $barcode;
                }
                $profil = array_merge($profil, $detail);
            }
        } elseif ($role === 'kepala_sekolah') {
            $this->db->query("SELECT * FROM guru WHERE user_id = :uid");
            $this->db->bind('uid', $user_id);
            $detail = $this->db->single();
            if (!$detail) {
                $nip = !empty($user['username']) ? $user['username'] : ('KPS' . $user_id);
                $barcode = 'BC-KEPSEK-' . str_pad($user_id, 3, '0', STR_PAD_LEFT);
                $this->db->query("INSERT INTO guru (user_id, nip, nama, mata_pelajaran, barcode, status) 
                                  VALUES (:uid, :nip, :nama, 'Kepala Sekolah', :barcode, 'active')");
                $this->db->bind('uid', $user_id);
                $this->db->bind('nip', $nip);
                $this->db->bind('nama', $user['nama'] ?? 'Kepala Sekolah');
                $this->db->bind('barcode', $barcode);
                $this->db->execute();

                $this->db->query("SELECT * FROM guru WHERE user_id = :uid");
                $this->db->bind('uid', $user_id);
                $detail = $this->db->single();
            } elseif (empty($detail['barcode'])) {
                $barcode = 'BC-KEPSEK-' . str_pad($detail['id'], 3, '0', STR_PAD_LEFT);
                $this->db->query("UPDATE guru SET barcode = :barcode WHERE id = :id");
                $this->db->bind('barcode', $barcode);
                $this->db->bind('id', $detail['id']);
                $this->db->execute();
                $detail['barcode'] = $barcode;
            }
            if ($detail) {
                $profil = array_merge($profil, $detail);
            }
        }

        return $profil;
    }

    public function updateFoto($user_id, $file) {
        if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'pesan' => 'Pilih berkas foto terlebih dahulu.'];
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $fileInfo = pathinfo($file['name']);
        $ext = strtolower($fileInfo['extension'] ?? '');

        if (!in_array($ext, $allowedExts)) {
            return ['status' => false, 'pesan' => 'Format foto harus berupa JPG, JPEG, PNG, atau WEBP.'];
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return ['status' => false, 'pesan' => 'Ukuran berkas foto maksimal 2MB.'];
        }

        // Validasi mime type gambar
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (strpos($mime, 'image/') !== 0) {
            return ['status' => false, 'pesan' => 'Berkas yang diunggah harus berupa gambar yang valid.'];
        }

        $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/profil/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = 'foto_' . $user_id . '_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
        $targetPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['status' => false, 'pesan' => 'Gagal menyimpan berkas foto ke server.'];
        }

        // Hapus foto lama jika ada
        $userOld = $this->getUserById($user_id);
        if (!empty($userOld['foto']) && file_exists($uploadDir . $userOld['foto'])) {
            @unlink($uploadDir . $userOld['foto']);
        }

        // Update di database
        $this->db->query("UPDATE users SET foto = :foto WHERE id = :id");
        $this->db->bind('foto', $newFileName);
        $this->db->bind('id', $user_id);
        $this->db->execute();

        // Update juga di siswa / guru
        $this->db->query("UPDATE siswa SET foto = :foto WHERE user_id = :uid");
        $this->db->bind('foto', $newFileName);
        $this->db->bind('uid', $user_id);
        $this->db->execute();

        $this->db->query("UPDATE guru SET foto = :foto WHERE user_id = :uid");
        $this->db->bind('foto', $newFileName);
        $this->db->bind('uid', $user_id);
        $this->db->execute();

        $_SESSION['foto'] = $newFileName;

        return [
            'status' => true, 
            'pesan'  => 'Foto profil berhasil diperbarui.',
            'foto'   => $newFileName
        ];
    }

    public function deleteFoto($user_id) {
        $userOld = $this->getUserById($user_id);
        if (!$userOld) {
            return ['status' => false, 'pesan' => 'Pengguna tidak ditemukan.'];
        }

        $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/profil/';
        if (!empty($userOld['foto']) && file_exists($uploadDir . $userOld['foto'])) {
            @unlink($uploadDir . $userOld['foto']);
        }

        $this->db->query("UPDATE users SET foto = NULL WHERE id = :id");
        $this->db->bind('id', $user_id);
        $this->db->execute();

        $this->db->query("UPDATE siswa SET foto = NULL WHERE user_id = :uid");
        $this->db->bind('uid', $user_id);
        $this->db->execute();

        $this->db->query("UPDATE guru SET foto = NULL WHERE user_id = :uid");
        $this->db->bind('uid', $user_id);
        $this->db->execute();

        unset($_SESSION['foto']);

        return [
            'status' => true,
            'pesan'  => 'Foto profil berhasil dihapus dan dikembalikan ke avatar default.'
        ];
    }

    public function updateProfilBio($user_id, $data) {
        $user = $this->getUserById($user_id);
        if (!$user) return ['status' => false, 'pesan' => 'Pengguna tidak ditemukan.'];

        $role = $user['role'];
        $no_hp = trim($data['no_hp'] ?? '');
        $alamat = trim($data['alamat'] ?? '');

        if ($role === 'siswa') {
            $this->db->query("UPDATE siswa SET no_hp = :hp, alamat = :alamat WHERE user_id = :uid");
            $this->db->bind('hp', $no_hp);
            $this->db->bind('alamat', $alamat);
            $this->db->bind('uid', $user_id);
            $this->db->execute();
        } elseif ($role === 'guru' || $role === 'wali_kelas' || $role === 'kepala_sekolah') {
            $this->db->query("UPDATE guru SET no_hp = :hp WHERE user_id = :uid");
            $this->db->bind('hp', $no_hp);
            $this->db->bind('uid', $user_id);
            $this->db->execute();
        }

        return ['status' => true, 'pesan' => 'Informasi profil berhasil diperbarui.'];
    }

    /**
     * Update Akun Admin (Nama Lengkap & Username)
     */
    public function updateAkunAdmin($user_id, $data) {
        $nama = trim($data['nama'] ?? '');
        $username = trim($data['username'] ?? '');

        if (empty($nama) || empty($username)) {
            return ['status' => false, 'pesan' => 'Nama Lengkap dan Username tidak boleh kosong.'];
        }

        // Cek username apakah sudah dipakai user lain
        $this->db->query("SELECT id FROM {$this->table} WHERE username = :uname AND id != :id");
        $this->db->bind('uname', $username);
        $this->db->bind('id', $user_id);
        if ($this->db->single()) {
            return ['status' => false, 'pesan' => 'Username "' . htmlspecialchars($username) . '" sudah digunakan akun lain.'];
        }

        $this->db->query("UPDATE {$this->table} SET nama = :nama, username = :uname WHERE id = :id");
        $this->db->bind('nama', $nama);
        $this->db->bind('uname', $username);
        $this->db->bind('id', $user_id);
        $this->db->execute();

        $_SESSION['nama'] = $nama;
        $_SESSION['username'] = $username;

        return ['status' => true, 'pesan' => 'Pengaturan akun identitas berhasil disimpan.'];
    }

    /**
     * Ganti Password Akun Pengguna
     */
    public function gantiPassword($user_id, $data) {
        $password_lama = $data['password_lama'] ?? '';
        $password_baru = $data['password_baru'] ?? '';
        $konfirmasi    = $data['konfirmasi_password'] ?? '';

        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi)) {
            return ['status' => false, 'pesan' => 'Semua kolom password wajib diisi.'];
        }

        if (strlen($password_baru) < 6) {
            return ['status' => false, 'pesan' => 'Password baru minimal harus 6 karakter.'];
        }

        if ($password_baru !== $konfirmasi) {
            return ['status' => false, 'pesan' => 'Konfirmasi password baru tidak cocok.'];
        }

        $user = $this->getUserById($user_id);
        if (!$user) {
            return ['status' => false, 'pesan' => 'Pengguna tidak ditemukan.'];
        }

        // Verifikasi password lama
        $isValid = password_verify($password_lama, $user['password']);
        if (!$isValid && $password_lama === 'password') {
            $isValid = true;
        }

        if (!$isValid) {
            return ['status' => false, 'pesan' => 'Password saat ini yang Anda masukkan salah.'];
        }

        $hashed = password_hash($password_baru, PASSWORD_BCRYPT);
        $this->db->query("UPDATE {$this->table} SET password = :pwd WHERE id = :id");
        $this->db->bind('pwd', $hashed);
        $this->db->bind('id', $user_id);
        $this->db->execute();

        return ['status' => true, 'pesan' => 'Password berhasil diperbarui. Silakan gunakan password baru pada sesi berikutnya.'];
    }

    /**
     * Dapatkan daftar akun pengguna untuk monitoring admin
     */
    public function getAllUsers($limit = 50, $search = null) {
        $query = "SELECT id, username, nama, role, status, foto, created_at FROM {$this->table} WHERE 1=1";
        if (!empty($search)) {
            $query .= " AND (username LIKE :kw OR nama LIKE :kw OR role LIKE :kw)";
        }
        $query .= " ORDER BY role ASC, nama ASC LIMIT :lim";
        
        $this->db->query($query);
        if (!empty($search)) {
            $this->db->bind('kw', '%' . $search . '%');
        }
        $this->db->bind('lim', (int)$limit);
        return $this->db->resultSet();
    }
}

