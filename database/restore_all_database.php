<?php
/**
 * Master Database Restoration Script for db_absensi_smk
 * Recreates all tables, constraints, users, teachers, 27 rombel classes, 135 students,
 * subjects, teaching schedules, and attendance rules.
 */

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'db_absensi_smk';

echo "=== MEMULAI RESTORASI LENGKAP DATABASE: {$dbname} ===\n\n";

try {
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("[FATAL ERROR] Gagal terhubung ke MySQL: " . $e->getMessage() . "\n");
}

// 1. Buat / Gunakan Database
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$pdo->exec("USE `{$dbname}`;");
echo "[1/6] Database `{$dbname}` siap.\n";

// 2. Drop tabel lama jika ada dengan FK checks dinonaktifkan
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$tablesToDrop = [
    'absensi_siswa', 'absensi_guru', 'catatan_siswa', 'jadwal_mengajar',
    'jadwal_absensi', 'siswa', 'kelas', 'guru', 'mata_pelajaran', 'users'
];
foreach ($tablesToDrop as $tbl) {
    $pdo->exec("DROP TABLE IF EXISTS `{$tbl}`;");
}
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
echo "[2/6] Seluruh tabel lama dibersihkan.\n";

// 3. Buat Schema Tabel
echo "[3/6] Membangun schema seluruh tabel...\n";

// Table: users
$pdo->exec("CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `role` enum('admin','guru','kepala_sekolah','wali_kelas','siswa') NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Table: guru
$pdo->exec("CREATE TABLE `guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `mata_pelajaran` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_guru_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Table: kelas
$pdo->exec("CREATE TABLE `kelas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(50) NOT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `tingkat` varchar(10) DEFAULT NULL,
  `wali_kelas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `wali_kelas_id` (`wali_kelas_id`),
  CONSTRAINT `fk_kelas_wali` FOREIGN KEY (`wali_kelas_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Table: siswa
$pdo->exec("CREATE TABLE `siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `rfid_uid` varchar(50) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nis` (`nis`),
  UNIQUE KEY `nisn` (`nisn`),
  UNIQUE KEY `rfid_uid` (`rfid_uid`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `user_id` (`user_id`),
  KEY `kelas_id` (`kelas_id`),
  CONSTRAINT `fk_siswa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_siswa_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Table: absensi_siswa
$pdo->exec("CREATE TABLE `absensi_siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `status` enum('hadir','terlambat','sakit','izin','alpa') NOT NULL DEFAULT 'hadir',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_absensi_siswa_tanggal` (`siswa_id`, `tanggal`),
  KEY `siswa_id` (`siswa_id`),
  KEY `tanggal` (`tanggal`),
  CONSTRAINT `fk_absensi_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Table: absensi_guru
$pdo->exec("CREATE TABLE `absensi_guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_scan` time DEFAULT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `status` enum('hadir','terlambat','sakit','izin','alpa') NOT NULL DEFAULT 'hadir',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_absensi_guru_tanggal` (`guru_id`, `tanggal`),
  KEY `guru_id` (`guru_id`),
  KEY `tanggal` (`tanggal`),
  CONSTRAINT `fk_absensi_guru` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Table: catatan_siswa
$pdo->exec("CREATE TABLE `catatan_siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `wali_kelas_id` int(11) NOT NULL,
  `kategori` enum('Pembinaan','Pelanggaran','Prestasi','Izin','Akademik','Perilaku','Lainnya') NOT NULL,
  `tanggal` date NOT NULL,
  `catatan` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `siswa_id` (`siswa_id`),
  KEY `wali_kelas_id` (`wali_kelas_id`),
  CONSTRAINT `fk_catatan_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_catatan_wali` FOREIGN KEY (`wali_kelas_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Table: mata_pelajaran
$pdo->exec("CREATE TABLE `mata_pelajaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_mapel` varchar(20) NOT NULL UNIQUE,
  `nama_mapel` varchar(100) NOT NULL,
  `kelompok` varchar(50) NOT NULL DEFAULT 'Muatan Nasional',
  `tingkat` varchar(20) NOT NULL DEFAULT 'Semua Tingkat',
  `jam_per_minggu` int(11) NOT NULL DEFAULT 2,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Table: jadwal_mengajar
$pdo->exec("CREATE TABLE `jadwal_mengajar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guru_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `mapel_id` int(11) DEFAULT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruangan` varchar(50) DEFAULT 'Ruang Kelas',
  `keterangan` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_guru` (`guru_id`),
  KEY `idx_kelas` (`kelas_id`),
  KEY `idx_mapel` (`mapel_id`),
  CONSTRAINT `fk_jm_guru` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jm_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jm_mapel` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Table: jadwal_absensi
$pdo->exec("CREATE TABLE `jadwal_absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jadwal` varchar(100) NOT NULL,
  `tipe_pengguna` enum('siswa','guru') NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `hari` varchar(50) NOT NULL DEFAULT 'Senin - Jumat',
  `jam_mulai` time NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_akhir` time NOT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_akhir` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tipe_hari_active` (`tipe_pengguna`, `hari`, `is_active`),
  KEY `idx_kelas` (`kelas_id`),
  CONSTRAINT `fk_jadwal_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

echo "   - 10 tabel berhasil dibuat.\n";

// 4. Seeding Data Guru & Pengguna Utama
echo "[4/6] Seeding Guru, Admin, Kepala Sekolah, dan Mata Pelajaran...\n";

$passHash = password_hash('password', PASSWORD_DEFAULT);

// Users Guru & Pimpinan
$primaryUsers = [
    ['username' => 'admin',         'nama' => 'Administrator',        'role' => 'admin'],
    ['username' => 'kepsek',        'nama' => 'Drs. Budi Santoso',    'role' => 'kepala_sekolah'],
    ['username' => 'siti_aminah',   'nama' => 'Siti Aminah, S.Pd',    'role' => 'guru'],
    ['username' => 'andi_hermawan', 'nama' => 'Andi Hermawan, M.Kom', 'role' => 'guru'],
    ['username' => 'rina_marlina',  'nama' => 'Rina Marlina, S.Pd',   'role' => 'wali_kelas'],
    ['username' => 'benjamin',      'nama' => 'Benjamin, S.Pd',       'role' => 'guru'],
    ['username' => 'ahmad_fauzi',   'nama' => 'Ahmad Fauzi, S.Ag',     'role' => 'guru'],
    ['username' => 'dewi_sartika',  'nama' => 'Dewi Sartika, S.Pd',    'role' => 'guru'],
    ['username' => 'hendra_wijaya', 'nama' => 'Hendra Wijaya, S.Pd',   'role' => 'guru'],
    ['username' => 'nurul_hidayah', 'nama' => 'Nurul Hidayah, S.Pd',   'role' => 'guru'],
    ['username' => 'bayu_pratama',  'nama' => 'Bayu Pratama, S.Pd',    'role' => 'guru'],
    ['username' => 'dian_kusuma',   'nama' => 'Dian Kusuma, S.Kom',    'role' => 'guru'],
    ['username' => 'irfan_maulana', 'nama' => 'Irfan Maulana, S.Si',   'role' => 'guru'],
    ['username' => 'ratna_juwita',  'nama' => 'Ratna Juwita, S.Si, M.Pd','role' => 'guru'],
    ['username' => 'eko_prasetyo',  'nama' => 'Eko Prasetyo, S.T, M.Kom','role' => 'guru'],
    ['username' => 'fajar_nugraha', 'nama' => 'Fajar Nugraha, S.Kom',  'role' => 'guru'],
    ['username' => 'maya_indah',    'nama' => 'Maya Indah, S.Ds',      'role' => 'guru'],
    ['username' => 'tri_wahyuni',   'nama' => 'Tri Wahyuni, S.E, M.M', 'role' => 'guru'],
    ['username' => 'asep_saepudin', 'nama' => 'Asep Saepudin, S.Pd',   'role' => 'guru'],
    ['username' => 'm_ridwan',      'nama' => 'M. Ridwan, S.Pd.I',     'role' => 'guru'],
    ['username' => 'aditya_putra',  'nama' => 'Aditya Putra, S.Kom',   'role' => 'guru'],
    ['username' => 'fitri_handayani','nama' => 'Fitri Handayani, S.Kom','role' => 'guru'],
    ['username' => 'bambang_sujarwo','nama' => 'Bambang Sujarwo, S.T', 'role' => 'guru'],
    ['username' => 'rizky_ramadhan','nama' => 'Rizky Ramadhan, M.Kom', 'role' => 'guru'],
    ['username' => 'wulan_sari',    'nama' => 'Wulan Sari, S.Kom',     'role' => 'guru'],
    ['username' => 'sri_wahyuni',   'nama' => 'Sri Wahyuni, S.Pd',     'role' => 'guru'],
    ['username' => 'joko_susilo',   'nama' => 'Joko Susilo, S.Pd',     'role' => 'guru'],
    ['username' => 'linda_permata', 'nama' => 'Linda Permata, S.Pd',   'role' => 'guru'],
    ['username' => 'h_lukman_hakim','nama' => 'H. Lukman Hakim, M.Pd.I','role' => 'guru'],
    ['username' => 'agus_kurniawan','nama' => 'Agus Kurniawan, S.Pd, Kons','role' => 'guru'],
    ['username' => 'yuni_astuti',   'nama' => 'Yuni Astuti, S.Pd, Kons','role' => 'guru'],
    // Alias / kompatibilitas backward
    ['username' => 'guru1',         'nama' => 'Siti Aminah (Alias)',  'role' => 'guru'],
    ['username' => 'guru2',         'nama' => 'Andi Hermawan (Alias)','role' => 'guru'],
    ['username' => 'walikelas1',    'nama' => 'Rina Marlina (Alias)', 'role' => 'wali_kelas']
];

$stmtUser = $pdo->prepare("INSERT INTO users (username, password, nama, role, status) VALUES (?, ?, ?, ?, 'active')");
$userIdMap = [];
foreach ($primaryUsers as $u) {
    $stmtUser->execute([$u['username'], $passHash, $u['nama'], $u['role']]);
    $userIdMap[$u['username']] = $pdo->lastInsertId();
}

// Data Profil Guru (Hanya 1 Kepala Sekolah: Drs. Budi Santoso)
$guruData = [
    [
        'user_id' => $userIdMap['kepsek'],
        'nip'     => '198001012005011001',
        'nama'    => 'Drs. Budi Santoso',
        'mapel'   => 'Kepala Sekolah',
        'barcode' => 'BC-KEPSEK-001',
        'no_hp'   => '081234567890'
    ],
    [
        'user_id' => $userIdMap['siti_aminah'],
        'nip'     => '198502022010012002',
        'nama'    => 'Siti Aminah, S.Pd',
        'mapel'   => 'Matematika',
        'barcode' => 'BC-GURU-001',
        'no_hp'   => '081234567891'
    ],
    [
        'user_id' => $userIdMap['andi_hermawan'],
        'nip'     => '198803032012011003',
        'nama'    => 'Andi Hermawan, M.Kom',
        'mapel'   => 'Pemrograman Dasar',
        'barcode' => 'BC-GURU-002',
        'no_hp'   => '081234567892'
    ],
    [
        'user_id' => $userIdMap['rina_marlina'],
        'nip'     => '199004042015012004',
        'nama'    => 'Rina Marlina, S.Pd',
        'mapel'   => 'Bahasa Inggris',
        'barcode' => 'BC-WALI-001',
        'no_hp'   => '081234567893'
    ],
    [
        'user_id' => $userIdMap['benjamin'],
        'nip'     => '199205052018011005',
        'nama'    => 'Benjamin, S.Pd',
        'mapel'   => 'PPKN',
        'barcode' => 'BC-GURU-003',
        'no_hp'   => '081234567894'
    ],
    [
        'user_id' => $userIdMap['ahmad_fauzi'],
        'nip'     => '198306152008011004',
        'nama'    => 'Ahmad Fauzi, S.Ag',
        'mapel'   => 'Pendidikan Agama Islam',
        'barcode' => 'BC-GURU-004',
        'no_hp'   => '081234567806'
    ],
    [
        'user_id' => $userIdMap['dewi_sartika'],
        'nip'     => '198607202010012005',
        'nama'    => 'Dewi Sartika, S.Pd',
        'mapel'   => 'Bahasa Indonesia',
        'barcode' => 'BC-GURU-005',
        'no_hp'   => '081234567807'
    ],
    [
        'user_id' => $userIdMap['hendra_wijaya'],
        'nip'     => '198708142011011006',
        'nama'    => 'Hendra Wijaya, S.Pd',
        'mapel'   => 'Sejarah Indonesia',
        'barcode' => 'BC-GURU-006',
        'no_hp'   => '081234567808'
    ],
    [
        'user_id' => $userIdMap['nurul_hidayah'],
        'nip'     => '198909252014012007',
        'nama'    => 'Nurul Hidayah, S.Pd',
        'mapel'   => 'Seni Budaya',
        'barcode' => 'BC-GURU-007',
        'no_hp'   => '081234567809'
    ],
    [
        'user_id' => $userIdMap['bayu_pratama'],
        'nip'     => '199110102016011008',
        'nama'    => 'Bayu Pratama, S.Pd',
        'mapel'   => 'Pendidikan Jasmani & Olahraga',
        'barcode' => 'BC-GURU-008',
        'no_hp'   => '081234567810'
    ],
    [
        'user_id' => $userIdMap['dian_kusuma'],
        'nip'     => '199311122019012009',
        'nama'    => 'Dian Kusuma, S.Kom',
        'mapel'   => 'Simulasi & Komunikasi Digital',
        'barcode' => 'BC-GURU-009',
        'no_hp'   => '081234567811'
    ],
    [
        'user_id' => $userIdMap['irfan_maulana'],
        'nip'     => '198412032009011010',
        'nama'    => 'Irfan Maulana, S.Si',
        'mapel'   => 'Fisika Terapan',
        'barcode' => 'BC-GURU-010',
        'no_hp'   => '081234567812'
    ],
    [
        'user_id' => $userIdMap['ratna_juwita'],
        'nip'     => '198601182010012011',
        'nama'    => 'Ratna Juwita, S.Si, M.Pd',
        'mapel'   => 'Kimia Terapan',
        'barcode' => 'BC-GURU-011',
        'no_hp'   => '081234567813'
    ],
    [
        'user_id' => $userIdMap['eko_prasetyo'],
        'nip'     => '198702222011011012',
        'nama'    => 'Eko Prasetyo, S.T, M.Kom',
        'mapel'   => 'Sistem Komputer',
        'barcode' => 'BC-GURU-012',
        'no_hp'   => '081234567814'
    ],
    [
        'user_id' => $userIdMap['fajar_nugraha'],
        'nip'     => '198803152012011013',
        'nama'    => 'Fajar Nugraha, S.Kom',
        'mapel'   => 'Komputer & Jaringan Dasar',
        'barcode' => 'BC-GURU-013',
        'no_hp'   => '081234567815'
    ],
    [
        'user_id' => $userIdMap['maya_indah'],
        'nip'     => '199004282015012014',
        'nama'    => 'Maya Indah, S.Ds',
        'mapel'   => 'Dasar Desain Grafis',
        'barcode' => 'BC-GURU-014',
        'no_hp'   => '081234567816'
    ],
    [
        'user_id' => $userIdMap['tri_wahyuni'],
        'nip'     => '198205162007012015',
        'nama'    => 'Tri Wahyuni, S.E, M.M',
        'mapel'   => 'Produk Kreatif & Kewirausahaan',
        'barcode' => 'BC-GURU-015',
        'no_hp'   => '081234567817'
    ],
    [
        'user_id' => $userIdMap['asep_saepudin'],
        'nip'     => '198406212009011016',
        'nama'    => 'Asep Saepudin, S.Pd',
        'mapel'   => 'Bahasa & Sastra Sunda',
        'barcode' => 'BC-GURU-016',
        'no_hp'   => '081234567818'
    ],
    [
        'user_id' => $userIdMap['m_ridwan'],
        'nip'     => '198507092010011017',
        'nama'    => 'M. Ridwan, S.Pd.I',
        'mapel'   => 'Ke-NU-an / Aswaja',
        'barcode' => 'BC-GURU-017',
        'no_hp'   => '081234567819'
    ],
    [
        'user_id' => $userIdMap['aditya_putra'],
        'nip'     => '198908302014011018',
        'nama'    => 'Aditya Putra, S.Kom',
        'mapel'   => 'Administrasi Infrastruktur Jaringan',
        'barcode' => 'BC-GURU-018',
        'no_hp'   => '081234567820'
    ],
    [
        'user_id' => $userIdMap['fitri_handayani'],
        'nip'     => '199109192016012019',
        'nama'    => 'Fitri Handayani, S.Kom',
        'mapel'   => 'Administrasi Sistem Jaringan',
        'barcode' => 'BC-GURU-019',
        'no_hp'   => '081234567821'
    ],
    [
        'user_id' => $userIdMap['bambang_sujarwo'],
        'nip'     => '198310052008011020',
        'nama'    => 'Bambang Sujarwo, S.T',
        'mapel'   => 'Teknologi Layanan Jaringan',
        'barcode' => 'BC-GURU-020',
        'no_hp'   => '081234567822'
    ],
    [
        'user_id' => $userIdMap['rizky_ramadhan'],
        'nip'     => '198811142013011021',
        'nama'    => 'Rizky Ramadhan, M.Kom',
        'mapel'   => 'Pemrograman Web & Perangkat Bergerak',
        'barcode' => 'BC-GURU-021',
        'no_hp'   => '081234567823'
    ],
    [
        'user_id' => $userIdMap['wulan_sari'],
        'nip'     => '199212252018012022',
        'nama'    => 'Wulan Sari, S.Kom',
        'mapel'   => 'Basis Data',
        'barcode' => 'BC-GURU-022',
        'no_hp'   => '081234567824'
    ],
    [
        'user_id' => $userIdMap['sri_wahyuni'],
        'nip'     => '198703112011012023',
        'nama'    => 'Sri Wahyuni, S.Pd',
        'mapel'   => 'Matematika',
        'barcode' => 'BC-GURU-023',
        'no_hp'   => '081234567825'
    ],
    [
        'user_id' => $userIdMap['joko_susilo'],
        'nip'     => '198904082014011024',
        'nama'    => 'Joko Susilo, S.Pd',
        'mapel'   => 'Bahasa Indonesia',
        'barcode' => 'BC-GURU-024',
        'no_hp'   => '081234567826'
    ],
    [
        'user_id' => $userIdMap['linda_permata'],
        'nip'     => '199105172016012025',
        'nama'    => 'Linda Permata, S.Pd',
        'mapel'   => 'Bahasa Inggris',
        'barcode' => 'BC-GURU-025',
        'no_hp'   => '081234567827'
    ],
    [
        'user_id' => $userIdMap['h_lukman_hakim'],
        'nip'     => '198106202006011026',
        'nama'    => 'H. Lukman Hakim, M.Pd.I',
        'mapel'   => 'Pendidikan Agama Islam',
        'barcode' => 'BC-GURU-026',
        'no_hp'   => '081234567828'
    ],
    [
        'user_id' => $userIdMap['agus_kurniawan'],
        'nip'     => '198607142010011027',
        'nama'    => 'Agus Kurniawan, S.Pd, Kons',
        'mapel'   => 'Bimbingan & Konseling (BK)',
        'barcode' => 'BC-GURU-027',
        'no_hp'   => '081234567829'
    ],
    [
        'user_id' => $userIdMap['yuni_astuti'],
        'nip'     => '199008222015012028',
        'nama'    => 'Yuni Astuti, S.Pd, Kons',
        'mapel'   => 'Bimbingan & Konseling (BK)',
        'barcode' => 'BC-GURU-028',
        'no_hp'   => '081234567830'
    ]
];

$stmtGuru = $pdo->prepare("INSERT INTO guru (user_id, nip, nama, mata_pelajaran, barcode, no_hp, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
$guruIdMap = [];
foreach ($guruData as $g) {
    $stmtGuru->execute([$g['user_id'], $g['nip'], $g['nama'], $g['mapel'], $g['barcode'], $g['no_hp']]);
    $guruIdMap[$g['nama']] = $pdo->lastInsertId();
}

$rinaId = $guruIdMap['Rina Marlina, S.Pd'];
$sitiId = $guruIdMap['Siti Aminah, S.Pd'];
$andiId = $guruIdMap['Andi Hermawan, M.Kom'];
$benId  = $guruIdMap['Benjamin, S.Pd'];

// Data Mata Pelajaran
$mapelList = [
    [1,  'PAI',     'Pendidikan Agama Islam',               'Muatan Nasional',           'Semua Tingkat', 3],
    [2,  'PPKN',    'Pendidikan Pancasila & Kewarganegaraan','Muatan Nasional',          'Semua Tingkat', 2],
    [3,  'BIN',     'Bahasa Indonesia',                     'Muatan Nasional',           'Semua Tingkat', 4],
    [4,  'MAT',     'Matematika',                           'Muatan Nasional',           'Semua Tingkat', 4],
    [5,  'SEJ',     'Sejarah Indonesia',                    'Muatan Nasional',           'Semua Tingkat', 2],
    [6,  'BIG',     'Bahasa Inggris',                       'Muatan Nasional',           'Semua Tingkat', 3],
    [7,  'SEN',     'Seni Budaya',                          'Muatan Kewilayahan',        'Semua Tingkat', 2],
    [8,  'PJOK',    'Pendidikan Jasmani & Olahraga',        'Muatan Kewilayahan',        'Semua Tingkat', 2],
    [9,  'SIM',     'Simulasi & Komunikasi Digital',        'Muatan Peminatan Kejuruan', 'X',             3],
    [10, 'FIS',     'Fisika Terapan',                       'Muatan Peminatan Kejuruan', 'X',             2],
    [11, 'KIM',     'Kimia Terapan',                        'Muatan Peminatan Kejuruan', 'X',             2],
    [12, 'SISKOM',  'Sistem Komputer',                      'Muatan Peminatan Kejuruan', 'X',             2],
    [13, 'KJD',     'Komputer & Jaringan Dasar',            'Muatan Peminatan Kejuruan', 'X',             4],
    [14, 'PROGDAS', 'Pemrograman Dasar',                    'Muatan Peminatan Kejuruan', 'X',             4],
    [15, 'DDG',     'Dasar Desain Grafis',                  'Muatan Peminatan Kejuruan', 'X',             3],
    [16, 'PKK',     'Produk Kreatif & Kewirausahaan',       'Muatan Peminatan Kejuruan', 'XI',            5],
    [17, 'ML-SUN',  'Bahasa & Sastra Sunda',                'Muatan Lokal',              'Semua Tingkat', 2],
    [18, 'ML-ASW',  'Ke-NU-an / Aswaja',                    'Muatan Lokal',              'Semua Tingkat', 2]
];

$stmtMapel = $pdo->prepare("INSERT INTO mata_pelajaran (id, kode_mapel, nama_mapel, kelompok, tingkat, jam_per_minggu, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
foreach ($mapelList as $m) {
    $stmtMapel->execute($m);
}
echo "   - 18 Mata Pelajaran berhasil diinsert.\n";

// 5. Seeding 27 Rombel Kelas & 135 Siswa
echo "[5/6] Seeding 27 Rombel Kelas (TKJ, TKR, TSM) dan 135 Siswa Lengkap...\n";

$jurusanMap = [
    'TKJ' => 'Teknik Komputer dan Jaringan',
    'TKR' => 'Teknik Kendaraan Ringan',
    'TSM' => 'Teknik Sepeda Motor'
];
$angkatan = ['X', 'XI', 'XII'];

// Rombel 1 Siswa (45 Siswa)
$siswaRombel1 = [
    'X TKJ 1' => [
        ['nama' => 'Ahmad Fauzi',      'username' => 'ahmad_fauzi_s',      'jk' => 'L'],
        ['nama' => 'Siti Rahmawati',   'username' => 'siti_rahmawati',   'jk' => 'P'],
        ['nama' => 'Budi Setiawan',    'username' => 'budi_setiawan',    'jk' => 'L'],
        ['nama' => 'Rani Permatasari', 'username' => 'rani_permatasari', 'jk' => 'P'],
        ['nama' => 'Andi Pratama',     'username' => 'andi_pratama',     'jk' => 'L']
    ],
    'X TKR 1' => [
        ['nama' => 'Rizky Wijaya',     'username' => 'rizky_wijaya',     'jk' => 'L'],
        ['nama' => 'Maya Nugroho',     'username' => 'maya_nugroho',     'jk' => 'P'],
        ['nama' => 'Fajar Hidayat',    'username' => 'fajar_hidayat',    'jk' => 'L'],
        ['nama' => 'Dina Lestari',     'username' => 'dina_lestari',     'jk' => 'P'],
        ['nama' => 'Kevin Pratama',    'username' => 'kevin_pratama',    'jk' => 'L']
    ],
    'X TSM 1' => [
        ['nama' => 'Bima Saputro',     'username' => 'bima_saputro',     'jk' => 'L'],
        ['nama' => 'Ayu Kusuma',       'username' => 'ayu_kusuma',       'jk' => 'P'],
        ['nama' => 'Agus Kurniawan',   'username' => 'agus_kurniawan_s',   'jk' => 'L'],
        ['nama' => 'Putri Susanti',    'username' => 'putri_susanti',    'jk' => 'P'],
        ['nama' => 'Dicky Setiawan',   'username' => 'dicky_setiawan',   'jk' => 'L']
    ],
    'XI TKJ 1' => [
        ['nama' => 'Hendra Wibowo',    'username' => 'hendra_wibowo',    'jk' => 'L'],
        ['nama' => 'Sari Handayani',   'username' => 'sari_handayani',   'jk' => 'P'],
        ['nama' => 'Eko Firmansyah',   'username' => 'eko_firmansyah',   'jk' => 'L'],
        ['nama' => 'Dewi Anggraini',   'username' => 'dewi_anggraini',   'jk' => 'P'],
        ['nama' => 'Rahmat Syahputra', 'username' => 'rahmat_syahputra', 'jk' => 'L']
    ],
    'XI TKR 1' => [
        ['nama' => 'Teguh Mahendra',   'username' => 'teguh_mahendra',   'jk' => 'L'],
        ['nama' => 'Rina Wulandari',   'username' => 'rina_wulandari',   'jk' => 'P'],
        ['nama' => 'Bambang Kusuma',   'username' => 'bambang_kusuma',   'jk' => 'L'],
        ['nama' => 'Intan Permata',    'username' => 'intan_permata',    'jk' => 'P'],
        ['nama' => 'Deni Siregar',     'username' => 'deni_siregar',     'jk' => 'L']
    ],
    'XI TSM 1' => [
        ['nama' => 'Arif Nasution',    'username' => 'arif_nasution',    'jk' => 'L'],
        ['nama' => 'Mega Utami',       'username' => 'mega_utami',       'jk' => 'P'],
        ['nama' => 'Bayu Lubis',       'username' => 'bayu_lubis',       'jk' => 'L'],
        ['nama' => 'Tari Puspitasari', 'username' => 'tari_puspitasari', 'jk' => 'P'],
        ['nama' => 'Gilang Harahap',   'username' => 'gilang_harahap',   'jk' => 'L']
    ],
    'XII TKJ 1' => [
        ['nama' => 'Aditya Sinaga',     'username' => 'aditya_sinaga',     'jk' => 'L'],
        ['nama' => 'Dita Safitri',      'username' => 'dita_safitri',      'jk' => 'P'],
        ['nama' => 'Iqbal Simanjuntak', 'username' => 'iqbal_simanjuntak', 'jk' => 'L'],
        ['nama' => 'Siska Amelia',      'username' => 'siska_amelia',      'jk' => 'P'],
        ['nama' => 'Galih Ritonga',     'username' => 'galih_ritonga',     'jk' => 'L']
    ],
    'XII TKR 1' => [
        ['nama' => 'Hafiz Situmorang', 'username' => 'hafiz_situmorang', 'jk' => 'L'],
        ['nama' => 'Nurul Fatimah',    'username' => 'nurul_fatimah',    'jk' => 'P'],
        ['nama' => 'Farid Panjaitan',  'username' => 'farid_panjaitan',  'jk' => 'L'],
        ['nama' => 'Indah Wahyuni',    'username' => 'indah_wahyuni',    'jk' => 'P'],
        ['nama' => 'Erwan Susanto',    'username' => 'erwan_susanto',    'jk' => 'L']
    ],
    'XII TSM 1' => [
        ['nama' => 'Cahyo Wibisono',   'username' => 'cahyo_wibisono',   'jk' => 'L'],
        ['nama' => 'Fitri Handayani',  'username' => 'fitri_handayani_s',  'jk' => 'P'],
        ['nama' => 'Baskoro Putro',    'username' => 'baskoro_putro',    'jk' => 'L'],
        ['nama' => 'Bella Citra',      'username' => 'bella_citra',      'jk' => 'P'],
        ['nama' => 'Dimas Sanjaya',    'username' => 'dimas_sanjaya',    'jk' => 'L']
    ]
];

// Rombel 2 & 3 Siswa (90 Siswa)
$siswaRombel23 = [
    'X TKJ 2' => [
        ['nama' => 'Aldy Prasetyo',      'username' => 'aldy_prasetyo',      'jk' => 'L'],
        ['nama' => 'Anisa Rahma',        'username' => 'anisa_rahma',        'jk' => 'P'],
        ['nama' => 'Bagas Saputra',      'username' => 'bagas_saputra',      'jk' => 'L'],
        ['nama' => 'Cantika Putri',      'username' => 'cantika_putri',      'jk' => 'P'],
        ['nama' => 'Danu Wijaya',        'username' => 'danu_wijaya',        'jk' => 'L']
    ],
    'X TKJ 3' => [
        ['nama' => 'Doni Kurniawan',     'username' => 'doni_kurniawan',     'jk' => 'L'],
        ['nama' => 'Elvira Maharani',    'username' => 'elvira_maharani',    'jk' => 'P'],
        ['nama' => 'Farhan Ramadhan',    'username' => 'farhan_ramadhan',    'jk' => 'L'],
        ['nama' => 'Gita Savitri',       'username' => 'gita_savitri',       'jk' => 'P'],
        ['nama' => 'Hafizh Pratama',     'username' => 'hafizh_pratama',     'jk' => 'L']
    ],
    'X TKR 2' => [
        ['nama' => 'Ilham Maulana',      'username' => 'ilham_maulana',      'jk' => 'L'],
        ['nama' => 'Jessica Larasati',   'username' => 'jessica_larasati',   'jk' => 'P'],
        ['nama' => 'Kemal Syahputra',    'username' => 'kemal_syahputra',    'jk' => 'L'],
        ['nama' => 'Larasati Dewi',      'username' => 'larasati_dewi',      'jk' => 'P'],
        ['nama' => 'Mirza Alamsyah',     'username' => 'mirza_alamsyah',     'jk' => 'L']
    ],
    'X TKR 3' => [
        ['nama' => 'Naufal Azhar',       'username' => 'naufal_azhar',       'jk' => 'L'],
        ['nama' => 'Olivia Chandra',     'username' => 'olivia_chandra',     'jk' => 'P'],
        ['nama' => 'Panji Kusuma',       'username' => 'panji_kusuma',       'jk' => 'L'],
        ['nama' => 'Qonita Zahra',       'username' => 'qonita_zahra',       'jk' => 'P'],
        ['nama' => 'Raditya Nugraha',    'username' => 'raditya_nugraha',    'jk' => 'L']
    ],
    'X TSM 2' => [
        ['nama' => 'Rendy Saputra',      'username' => 'rendy_saputra',      'jk' => 'L'],
        ['nama' => 'Safira Amanda',      'username' => 'safira_amanda',      'jk' => 'P'],
        ['nama' => 'Taufik Hidayatullah','username' => 'taufik_hidayatullah','jk' => 'L'],
        ['nama' => 'Ulfa Mardhiyah',     'username' => 'ulfa_mardhiyah',     'jk' => 'P'],
        ['nama' => 'Vicky Firmansyah',   'username' => 'vicky_firmansyah',   'jk' => 'L']
    ],
    'X TSM 3' => [
        ['nama' => 'Wahyu Wibisono',     'username' => 'wahyu_wibisono',     'jk' => 'L'],
        ['nama' => 'Yasmin Aulia',       'username' => 'yasmin_aulia',       'jk' => 'P'],
        ['nama' => 'Zaki Mubarak',       'username' => 'zaki_mubarak',       'jk' => 'L'],
        ['nama' => 'Zahra Amelia',       'username' => 'zahra_amelia',       'jk' => 'P'],
        ['nama' => 'Agung Prasetya',     'username' => 'agung_prasetya',     'jk' => 'L']
    ],
    'XI TKJ 2' => [
        ['nama' => 'Bayu Pamungkas',     'username' => 'bayu_pamungkas',     'jk' => 'L'],
        ['nama' => 'Citra Anggraini',    'username' => 'citra_anggraini',    'jk' => 'P'],
        ['nama' => 'Dika Pratama',       'username' => 'dika_pratama',       'jk' => 'L'],
        ['nama' => 'Erika Nurhaliza',    'username' => 'erika_nurhaliza',    'jk' => 'P'],
        ['nama' => 'Faisal Basri',       'username' => 'faisal_basri',       'jk' => 'L']
    ],
    'XI TKJ 3' => [
        ['nama' => 'Gading Prakoso',     'username' => 'gading_prakoso',     'jk' => 'L'],
        ['nama' => 'Hana Pertiwi',       'username' => 'hana_pertiwi',       'jk' => 'P'],
        ['nama' => 'Indra Gunawan',      'username' => 'indra_gunawan',      'jk' => 'L'],
        ['nama' => 'Julia Rahayu',       'username' => 'julia_rahayu',       'jk' => 'P'],
        ['nama' => 'Kresna Bayu',        'username' => 'kresna_bayu',        'jk' => 'L']
    ],
    'XI TKR 2' => [
        ['nama' => 'Lukman Hakim',       'username' => 'lukman_hakim',       'jk' => 'L'],
        ['nama' => 'Maulida Rahmah',     'username' => 'maulida_rahmah',     'jk' => 'P'],
        ['nama' => 'Niko Saputra',       'username' => 'niko_saputra',       'jk' => 'L'],
        ['nama' => 'Nadya Faradilla',    'username' => 'nadya_faradilla',    'jk' => 'P'],
        ['nama' => 'Oki Setiawan',       'username' => 'oki_setiawan',       'jk' => 'L']
    ],
    'XI TKR 3' => [
        ['nama' => 'Prima Wicaksono',    'username' => 'prima_wicaksono',    'jk' => 'L'],
        ['nama' => 'Qori Anggraini',     'username' => 'qori_anggraini',     'jk' => 'P'],
        ['nama' => 'Rio Fernando',       'username' => 'rio_fernando',       'jk' => 'L'],
        ['nama' => 'Shinta Bella',       'username' => 'shinta_bella',       'jk' => 'P'],
        ['nama' => 'Tommy Gunawan',      'username' => 'tommy_gunawan',      'jk' => 'L']
    ],
    'XI TSM 2' => [
        ['nama' => 'Umar Alfarizi',      'username' => 'umar_alfarizi',      'jk' => 'L'],
        ['nama' => 'Vina Panduwinata',   'username' => 'vina_panduwinata',   'jk' => 'P'],
        ['nama' => 'Wildan Mubarok',     'username' => 'wildan_mubarok',     'jk' => 'L'],
        ['nama' => 'Wulan Dari',         'username' => 'wulan_dari',         'jk' => 'P'],
        ['nama' => 'Yoga Pratama',       'username' => 'yoga_pratama',       'jk' => 'L']
    ],
    'XI TSM 3' => [
        ['nama' => 'Yudha Wardhana',     'username' => 'yudha_wardhana',     'jk' => 'L'],
        ['nama' => 'Zulfikar Rahman',    'username' => 'zulfikar_rahman',    'jk' => 'L'],
        ['nama' => 'Tiara Andini',       'username' => 'tiara_andini',       'jk' => 'P'],
        ['nama' => 'Salma Salsabila',    'username' => 'salma_salsabila',    'jk' => 'P'],
        ['nama' => 'Rian Hidayat',       'username' => 'rian_hidayat',       'jk' => 'L']
    ],
    'XII TKJ 2' => [
        ['nama' => 'Alif Bahtiar',       'username' => 'alif_bahtiar',       'jk' => 'L'],
        ['nama' => 'Berliana Putri',     'username' => 'berliana_putri',     'jk' => 'P'],
        ['nama' => 'Candra Kirana',      'username' => 'candra_kirana',      'jk' => 'L'],
        ['nama' => 'Dara Ayu',           'username' => 'dara_ayu',           'jk' => 'P'],
        ['nama' => 'Evan Dimas',         'username' => 'evan_dimas',         'jk' => 'L']
    ],
    'XII TKJ 3' => [
        ['nama' => 'Firman Utina',       'username' => 'firman_utina',       'jk' => 'L'],
        ['nama' => 'Gina Sonia',         'username' => 'gina_sonia',         'jk' => 'P'],
        ['nama' => 'Hanif Sjahbandi',    'username' => 'hanif_sjahbandi',    'jk' => 'L'],
        ['nama' => 'Irma Suryani',       'username' => 'irma_suryani',       'jk' => 'P'],
        ['nama' => 'Johan Yoga',         'username' => 'johan_yoga',         'jk' => 'L']
    ],
    'XII TKR 2' => [
        ['nama' => 'Kurniawan Dwi',      'username' => 'kurniawan_dwi',      'jk' => 'L'],
        ['nama' => 'Lusi Rahmawati',     'username' => 'lusi_rahmawati',     'jk' => 'P'],
        ['nama' => 'Mario Suryo',        'username' => 'mario_suryo',        'jk' => 'L'],
        ['nama' => 'Nadia Vega',         'username' => 'nadia_vega',         'jk' => 'P'],
        ['nama' => 'Oktovianus Maniani', 'username' => 'oktovianus_maniani', 'jk' => 'L']
    ],
    'XII TKR 3' => [
        ['nama' => 'Ponaryo Astaman',    'username' => 'ponaryo_astaman',    'jk' => 'L'],
        ['nama' => 'Raisa Andriana',     'username' => 'raisa_andriana',     'jk' => 'P'],
        ['nama' => 'Syamsir Alam',       'username' => 'syamsir_alam',       'jk' => 'L'],
        ['nama' => 'Tasya Kamila',       'username' => 'tasya_kamila',       'jk' => 'P'],
        ['nama' => 'Titus Bonai',        'username' => 'titus_bonai',        'jk' => 'L']
    ],
    'XII TSM 2' => [
        ['nama' => 'Usman Harun',        'username' => 'usman_harun',        'jk' => 'L'],
        ['nama' => 'Vanya Shinta',       'username' => 'vanya_shinta',       'jk' => 'P'],
        ['nama' => 'Wahyu Subo',         'username' => 'wahyu_subo',         'jk' => 'L'],
        ['nama' => 'Widya Utami',        'username' => 'widya_utami',        'jk' => 'P'],
        ['nama' => 'Yanto Basna',        'username' => 'yanto_basna',        'jk' => 'L']
    ],
    'XII TSM 3' => [
        ['nama' => 'Zaenal Arif',        'username' => 'zaenal_arif',        'jk' => 'L'],
        ['nama' => 'Zulham Zamrun',      'username' => 'zulham_zamrun',      'jk' => 'L'],
        ['nama' => 'Aulia Sarah',        'username' => 'aulia_sarah',        'jk' => 'P'],
        ['nama' => 'Bianca Jodie',       'username' => 'bianca_jodie',       'jk' => 'P'],
        ['nama' => 'Clara Bernadeth',    'username' => 'clara_bernadeth',    'jk' => 'P']
    ]
];

$stmtKelas = $pdo->prepare("INSERT INTO kelas (nama_kelas, jurusan, tingkat, wali_kelas_id) VALUES (?, ?, ?, ?)");
$stmtSiswa = $pdo->prepare("INSERT INTO siswa (user_id, nis, nisn, nama, jenis_kelamin, kelas_id, rfid_uid, barcode, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");

$kelasMap = [];

// 1. Buat 27 Kelas
foreach ($angkatan as $tingkat) {
    foreach ($jurusanMap as $kodeJur => $namaJur) {
        for ($rombel = 1; $rombel <= 3; $rombel++) {
            $namaKelas = "{$tingkat} {$kodeJur} {$rombel}";
            
            // Penugasan Wali Kelas:
            // Rina Marlina = Wali Kelas khusus XI TKJ 1
            if ($namaKelas === 'XI TKJ 1') {
                $waliId = $rinaId;
            } elseif ($rombel === 1) {
                $waliId = ($kodeJur === 'TKJ') ? $sitiId : (($kodeJur === 'TKR') ? $andiId : $benId);
            } elseif ($rombel === 2) {
                $waliId = ($kodeJur === 'TKJ') ? $benId : (($kodeJur === 'TKR') ? $sitiId : $andiId);
            } else {
                $waliId = ($kodeJur === 'TKJ') ? $andiId : (($kodeJur === 'TKR') ? $benId : $sitiId);
            }

            $stmtKelas->execute([$namaKelas, $namaJur, $tingkat, $waliId]);
            $kelasMap[$namaKelas] = $pdo->lastInsertId();
        }
    }
}

// 2. Insert Siswa sesuai urutan kanonikal:
// Tahap A: 9 Kelas Rombel 1 (NIS 260001 - 260045)
$nisCounter = 260001;
foreach ($siswaRombel1 as $namaKelas => $muridList) {
    $kelasId = $kelasMap[$namaKelas];
    foreach ($muridList as $m) {
        $stmtUser->execute([$m['username'], $passHash, $m['nama'], 'siswa']);
        $sUserId = $pdo->lastInsertId();

        $nis = (string)$nisCounter++;
        $nisn = '00' . $nis;
        $rfid = 'RFID-' . $nis;
        $barcode = 'STD-' . $nis;

        $stmtSiswa->execute([$sUserId, $nis, $nisn, $m['nama'], $m['jk'], $kelasId, $rfid, $barcode]);
    }
}

// Tahap B: 18 Kelas Rombel 2 & 3 (NIS 260046 - 260135, Anisa Rahma = 260047 / STD-260047)
foreach ($siswaRombel23 as $namaKelas => $muridList) {
    $kelasId = $kelasMap[$namaKelas];
    foreach ($muridList as $m) {
        $stmtUser->execute([$m['username'], $passHash, $m['nama'], 'siswa']);
        $sUserId = $pdo->lastInsertId();

        $nis = (string)$nisCounter++;
        $nisn = '00' . $nis;
        $rfid = 'RFID-' . $nis;
        $barcode = 'STD-' . $nis;

        $stmtSiswa->execute([$sUserId, $nis, $nisn, $m['nama'], $m['jk'], $kelasId, $rfid, $barcode]);
    }
}
echo "   - 27 Kelas & 135 Siswa berhasil dibuat.\n";

// 6. Seeding Jadwal Mengajar & Jadwal Absensi
echo "[6/6] Seeding Jadwal Mengajar & Ketentuan Jadwal Absensi...\n";

// Jadwal Mengajar Terlengkap
$jadwalMengajarList = [
    // Siti Aminah (Matematika)
    [$sitiId, $kelasMap['X TKJ 1'], 4, 'Senin',  '09:15:00', '10:45:00', 'Ruang Teori 1', 'Matematika Terapan & Aljabar'],
    [$sitiId, $kelasMap['X TKJ 1'], 4, 'Rabu',   '09:15:00', '10:45:00', 'Ruang Teori 1', 'Matematika Terapan'],
    [$sitiId, $kelasMap['X TKJ 1'], 4, 'Kamis',  '07:30:00', '09:00:00', 'Ruang Teori 1', 'Kalkulus Dasar & Logika'],
    [$sitiId, $kelasMap['XI TSM 1'], 4, 'Kamis', '09:15:00', '10:45:00', 'Ruang Teori 3', 'Matematika Terapan Otomotif'],
    [$sitiId, $kelasMap['X TKJ 1'], 4, 'Jumat',  '10:15:00', '11:30:00', 'Ruang Teori 1', 'Matematika Terapan & Evaluasi'],

    // Andi Hermawan (Pemrograman & Jaringan)
    [$andiId, $kelasMap['X TKJ 1'], 14, 'Senin', '11:00:00', '12:30:00', 'Lab Komputer 1', 'Algoritma & Pemrograman Dasar'],
    [$andiId, $kelasMap['X TKJ 1'], 13, 'Selasa', '11:00:00', '12:30:00', 'Lab Komputer 1', 'Pengenalan Hardware & OS'],
    [$andiId, $kelasMap['X TKJ 1'], 14, 'Rabu',  '11:00:00', '12:30:00', 'Lab Komputer 1', 'Praktikum Coding PHP & Python'],
    [$andiId, $kelasMap['X TKJ 1'], 13, 'Kamis', '09:15:00', '10:45:00', 'Lab Komputer 1', 'Instalasi Jaringan LAN'],
    [$andiId, $kelasMap['X TKJ 1'], 14, 'Jumat', '08:30:00', '10:00:00', 'Lab Komputer 1', 'Web Design & HTML/CSS'],
    [$andiId, $kelasMap['X TKJ 2'], 14, 'Jumat', '10:15:00', '11:30:00', 'Lab Komputer 2', 'Praktikum Web Dasar'],

    // Rina Marlina (Bahasa Inggris)
    [$rinaId, $kelasMap['X TKJ 1'], 6, 'Senin',  '13:00:00', '14:30:00', 'Ruang Bahasa', 'Technical English for IT'],
    [$rinaId, $kelasMap['X TKJ 1'], 6, 'Selasa', '09:15:00', '10:45:00', 'Ruang Bahasa', 'English Grammar & Conversation'],
    [$rinaId, $kelasMap['X TKJ 1'], 6, 'Rabu',   '07:30:00', '09:00:00', 'Ruang Bahasa', 'Reading Comprehension'],
    [$rinaId, $kelasMap['X TKJ 1'], 6, 'Kamis',  '11:00:00', '12:30:00', 'Ruang Bahasa', 'Listening & Presentation'],
    [$rinaId, $kelasMap['X TKJ 1'], 6, 'Jumat',  '07:00:00', '08:30:00', 'Ruang Bahasa', 'English Vocabulary for Vocational'],
    [$rinaId, $kelasMap['XI TKJ 1'], 6, 'Selasa', '13:00:00', '14:30:00', 'Ruang Bahasa', 'Advanced Vocational English'],

    // Benjamin (PPKN)
    [$benId, $kelasMap['X TKJ 2'], 2, 'Senin',  '07:30:00', '09:00:00', 'Ruang Kelas 10-2', 'Pendidikan Pancasila & Karakter'],
    [$benId, $kelasMap['X TKJ 1'], 2, 'Selasa', '13:00:00', '14:30:00', 'Ruang Teori 1', 'Wawasan Kebangsaan & Nilai Pancasila'],
    [$benId, $kelasMap['X TKJ 1'], 2, 'Rabu',   '13:00:00', '14:30:00', 'Ruang Teori 1', 'Hak dan Kewajiban Warga Negara'],
    [$benId, $kelasMap['X TKJ 1'], 2, 'Kamis',  '13:00:00', '14:30:00', 'Ruang Teori 1', 'Norma Hukum dan Keadilan'],
    [$benId, $kelasMap['X TKJ 1'], 2, 'Jumat',  '13:00:00', '14:30:00', 'Ruang Teori 1', 'Bhinneka Tunggal Ika & Diskusi']
];

$stmtJM = $pdo->prepare("INSERT INTO jadwal_mengajar (guru_id, kelas_id, mapel_id, hari, jam_mulai, jam_selesai, ruangan, keterangan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
foreach ($jadwalMengajarList as $jm) {
    $stmtJM->execute($jm);
}
echo "   - Jadwal Mengajar berhasil diinsert.\n";

// Jadwal Absensi (Sesuai instruksi user: 'Jadwal Siswa', 'Jadwal Guru', Hari: 'Semua Hari' agar presensi aktif setiap hari termasuk Sabtu)
$stmtJA = $pdo->prepare("INSERT INTO jadwal_absensi (nama_jadwal, tipe_pengguna, kelas_id, hari, jam_mulai, jam_masuk, jam_akhir, is_active, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)");

$stmtJA->execute([
    'Jadwal Siswa',
    'siswa',
    NULL,
    'Semua Hari',
    '06:00:00',
    '07:00:00',
    '16:00:00',
    'Jadwal presensi harian seluruh siswa SMK'
]);

$stmtJA->execute([
    'Jadwal Guru',
    'guru',
    NULL,
    'Semua Hari',
    '06:00:00',
    '06:45:00',
    '16:00:00',
    'Jadwal presensi harian dewan guru SMK'
]);
echo "   - Jadwal Absensi default ('Jadwal Siswa' & 'Jadwal Guru', Semua Hari) berhasil diinsert.\n";

// ── 12. Tabel & Seed Konfigurasi Multi API Key Chatbot AI ──
$pdo->exec("CREATE TABLE IF NOT EXISTS `ai_configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(100) NOT NULL,
  `provider` varchar(50) NOT NULL DEFAULT 'gemini',
  `api_key` text NOT NULL,
  `model` varchar(100) NOT NULL DEFAULT 'gemini-3.1-flash-lite-preview',
  `base_url` varchar(255) DEFAULT '',
  `temperature` decimal(3,2) NOT NULL DEFAULT 0.20,
  `max_tokens` int(11) NOT NULL DEFAULT 350,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `is_fallback` tinyint(1) NOT NULL DEFAULT 0,
  `last_status` varchar(50) DEFAULT 'untested',
  `last_tested_at` datetime DEFAULT NULL,
  `last_error_message` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$countAi = $pdo->query("SELECT COUNT(*) FROM ai_configurations")->fetchColumn();
if ($countAi == 0) {
    $stmtAi = $pdo->prepare("INSERT INTO ai_configurations (label, provider, api_key, model, base_url, temperature, max_tokens, is_active, last_status, last_tested_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 'success', NOW())");
    $stmtAi->execute([
        'Google Gemini Utama (Sekolah)',
        'gemini',
        'YOUR_GEMINI_API_KEY',
        'gemini-3.1-flash-lite-preview',
        '',
        0.20,
        350
    ]);
    echo "   - Default konfigurasi AI Chatbot (Google Gemini Utama) berhasil diinsert.\n";
}

echo "\n======================================================\n";
echo "RESTORASI DATABASE db_absensi_smk SELESAI DENGAN SUKSES!\n";
echo "======================================================\n";
