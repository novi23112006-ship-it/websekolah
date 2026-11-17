-- 1. Tambah kolom 'nama' di tabel users
ALTER TABLE `users` ADD COLUMN `nama` VARCHAR(100) NULL AFTER `password`;

-- Update nama berdasarkan data siswa dan guru (optional, generic fallback for admin/kepsek)
UPDATE `users` SET `nama` = 'Administrator' WHERE `role` = 'admin';
UPDATE `users` SET `nama` = 'Kepala Sekolah' WHERE `role` = 'kepala_sekolah';
UPDATE `users` u JOIN `guru` g ON u.id = g.user_id SET u.nama = g.nama;
UPDATE `users` u JOIN `siswa` s ON u.id = s.user_id SET u.nama = s.nama;

-- 2. Tambah kolom 'no_hp' di tabel siswa dan guru
ALTER TABLE `siswa` ADD COLUMN `no_hp` VARCHAR(20) NULL AFTER `alamat`;
ALTER TABLE `guru` ADD COLUMN `no_hp` VARCHAR(20) NULL AFTER `barcode`;

-- 3. Tambahkan unique constraint untuk absensi ganda
ALTER TABLE `absensi_siswa` ADD CONSTRAINT `uq_absensi_siswa_tanggal` UNIQUE (`siswa_id`, `tanggal`);
ALTER TABLE `absensi_guru` ADD CONSTRAINT `uq_absensi_guru_tanggal` UNIQUE (`guru_id`, `tanggal`);

-- 4. Buat tabel catatan_siswa
CREATE TABLE IF NOT EXISTS `catatan_siswa` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Insert Dummy Data (if not exists)
-- Password 'password' has been hashed using PHP password_hash default BCRYPT
SET @default_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

INSERT IGNORE INTO `users` (`username`, `password`, `nama`, `role`, `status`) VALUES
('guru01', @default_password, 'Guru Dummy 01', 'guru', 'active'),
('kepala01', @default_password, 'Kepala Sekolah Dummy 01', 'kepala_sekolah', 'active'),
('wali01', @default_password, 'Wali Kelas Dummy 01', 'wali_kelas', 'active'),
('siswa01', @default_password, 'Siswa Dummy 01', 'siswa', 'active');

-- Add profile for guru01
INSERT IGNORE INTO `guru` (`user_id`, `nip`, `nama`, `mata_pelajaran`, `barcode`, `status`)
SELECT id, '198001012005011001', 'Guru Dummy 01', 'Matematika', 'BC-GURU-01', 'active' FROM `users` WHERE `username` = 'guru01'
ON DUPLICATE KEY UPDATE nip = nip;

-- Add profile for wali01
INSERT IGNORE INTO `guru` (`user_id`, `nip`, `nama`, `mata_pelajaran`, `barcode`, `status`)
SELECT id, '198001012005011002', 'Wali Kelas Dummy 01', 'Fisika', 'BC-WALI-01', 'active' FROM `users` WHERE `username` = 'wali01'
ON DUPLICATE KEY UPDATE nip = nip;

-- Check if we need to add a dummy class for wali01 to be a wali kelas
INSERT IGNORE INTO `kelas` (`nama_kelas`, `jurusan`, `tingkat`, `wali_kelas_id`)
SELECT 'X TKJ 2', 'Teknik jaringan komputer', 'X', id FROM `guru` WHERE `nama` = 'Wali Kelas Dummy 01' LIMIT 1
ON DUPLICATE KEY UPDATE nama_kelas = nama_kelas;

-- Add profile for siswa01
INSERT IGNORE INTO `siswa` (`user_id`, `nis`, `nisn`, `nama`, `jenis_kelamin`, `kelas_id`, `rfid_uid`, `status`)
SELECT u.id, '10001', '0010000001', 'Siswa Dummy 01', 'L', k.id, 'RFID-0001', 'active' 
FROM `users` u 
JOIN `kelas` k ON k.nama_kelas = 'X TKJ 2'
WHERE u.username = 'siswa01'
ON DUPLICATE KEY UPDATE nis = nis;
