<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = new Database();

echo "Membuat tabel jadwal_absensi...\n";

$sql = "CREATE TABLE IF NOT EXISTS `jadwal_absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jadwal` varchar(100) NOT NULL,
  `tipe_pengguna` enum('siswa','guru') NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `hari` varchar(50) NOT NULL DEFAULT 'Senin - Jumat',
  `jam_mulai` time NOT NULL,
  `jam_masuk` time NOT NULL COMMENT 'Batas waktu hadir tepat waktu',
  `jam_akhir` time NOT NULL COMMENT 'Batas akhir absensi dibuka',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$db->query($sql);
$db->execute();

echo "Tabel jadwal_absensi berhasil dibuat / sudah ada.\n";

// Cek apakah sudah ada data awal
$db->query("SELECT COUNT(id) as total FROM `jadwal_absensi`");
$count = $db->single()['total'];

if ($count == 0) {
    echo "Menambahkan jadwal default awal...\n";
    
    // Jadwal Siswa Reguler: 06:00 - 07:00 (hadir) - 16:00 (akhir)
    $db->query("INSERT INTO `jadwal_absensi` 
        (`nama_jadwal`, `tipe_pengguna`, `kelas_id`, `hari`, `jam_mulai`, `jam_masuk`, `jam_akhir`, `is_active`, `keterangan`)
        VALUES 
        ('Jadwal Siswa', 'siswa', NULL, 'Senin - Jumat', '06:00:00', '07:00:00', '16:00:00', 1, 'Jadwal presensi harian siswa SMK'),
        ('Jadwal Guru', 'guru', NULL, 'Senin - Jumat', '06:00:00', '06:45:00', '16:00:00', 1, 'Jadwal presensi harian guru SMK')");
    $db->execute();
    echo "Jadwal default berhasil ditambahkan.\n";
} else {
    echo "Data jadwal sudah terisi ($count jadwal terdaftar).\n";
}

$db->query("SELECT * FROM `jadwal_absensi`");
print_r($db->resultSet());
