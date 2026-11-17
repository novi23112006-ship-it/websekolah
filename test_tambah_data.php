<?php
require_once 'config/config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die('Koneksi Gagal: ' . $conn->connect_error); }

$conn->query("UPDATE users SET username='siswa' WHERE username='siswa1'");

$conn->query("INSERT INTO kelas (nama_kelas, jurusan, tingkat, wali_kelas_id) VALUES ('XI RPL 1', 'Rekayasa Perangkat Lunak', 'XI', 4)");
$kelas_id = $conn->insert_id;

$siswa_baru = [
    ['nis'=>'2024001', 'nama'=>'Bima Sakti', 'jk'=>'L'],
    ['nis'=>'2024002', 'nama'=>'Siti Nurhaliza', 'jk'=>'P'],
    ['nis'=>'2024003', 'nama'=>'Kevin Sanjaya', 'jk'=>'L']
];

foreach($siswa_baru as $i => $s) {
    $uname = strtolower(explode(' ', $s['nama'])[0]); 
    $pwd = password_hash($uname, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, nama, role, status) VALUES (?, ?, ?, 'siswa', 'active')");
    $stmt->bind_param("sss", $uname, $pwd, $s['nama']);
    $stmt->execute();
    $uid = $conn->insert_id;
    
    $barcode = 'STD-1000' . $i;
    $stmt = $conn->prepare("INSERT INTO siswa (user_id, nis, nama, jenis_kelamin, kelas_id, barcode) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssis", $uid, $s['nis'], $s['nama'], $s['jk'], $kelas_id, $barcode);
    $stmt->execute();
}
echo 'Data Berhasil Ditambahkan!';
?>
