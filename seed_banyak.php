<?php
require_once 'config/config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die('Koneksi Gagal: ' . $conn->connect_error); }

$first_names_m = ['Budi', 'Andi', 'Rizal', 'Kevin', 'Bima', 'Fajar', 'Rizky', 'Agus', 'Dicky', 'Hendra', 'Eko', 'Rahmat', 'Dian', 'Teguh', 'Bambang', 'Wawan', 'Deni', 'Arif', 'Bayu', 'Gilang'];
$first_names_f = ['Siti', 'Bunga', 'Dina', 'Rina', 'Ayu', 'Putri', 'Sari', 'Dewi', 'Lestari', 'Nisa', 'Rani', 'Intan', 'Mega', 'Tari', 'Dita', 'Maya', 'Siska', 'Nurul', 'Indah', 'Fitri'];
$last_names = ['Sanjaya', 'Sakti', 'Wijaya', 'Saputro', 'Pratama', 'Santoso', 'Hidayat', 'Kurniawan', 'Nugroho', 'Setiawan', 'Wibowo', 'Firmansyah', 'Syahputra', 'Mahendra', 'Lestari', 'Susanti', 'Rahmawati', 'Sari', 'Kusuma', 'Permatasari'];

function getRandomName(&$nis_counter, $gender) {
    global $first_names_m, $first_names_f, $last_names;
    $f = $gender == 'L' ? $first_names_m[array_rand($first_names_m)] : $first_names_f[array_rand($first_names_f)];
    $l = $last_names[array_rand($last_names)];
    return $f . ' ' . $l;
}

$classes = [
    ['nama' => 'XI TKJ 1', 'jur' => 'Teknik Komputer dan Jaringan', 'tkt' => 'XI'],
    ['nama' => 'XI TKJ 2', 'jur' => 'Teknik Komputer dan Jaringan', 'tkt' => 'XI'],
    ['nama' => 'XI TKJ 3', 'jur' => 'Teknik Komputer dan Jaringan', 'tkt' => 'XI'],
    ['nama' => 'XI TKJ 4', 'jur' => 'Teknik Komputer dan Jaringan', 'tkt' => 'XI'],
    ['nama' => 'XI TKJ 5', 'jur' => 'Teknik Komputer dan Jaringan', 'tkt' => 'XI'],
    ['nama' => 'XII RPL 1', 'jur' => 'Rekayasa Perangkat Lunak', 'tkt' => 'XII'],
    ['nama' => 'XII RPL 2', 'jur' => 'Rekayasa Perangkat Lunak', 'tkt' => 'XII'],
    ['nama' => 'XII RPL 3', 'jur' => 'Rekayasa Perangkat Lunak', 'tkt' => 'XII'],
    ['nama' => 'XII RPL 4', 'jur' => 'Rekayasa Perangkat Lunak', 'tkt' => 'XII'],
    ['nama' => 'XII RPL 5', 'jur' => 'Rekayasa Perangkat Lunak', 'tkt' => 'XII']
];

$wali_kelas_id = 4; // menggunakan guru ID yang valid
$nis_counter = 2025001;
$total_added = 0;

$conn->begin_transaction();

try {
    foreach($classes as $c) {
        $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, jurusan, tingkat, wali_kelas_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $c['nama'], $c['jur'], $c['tkt'], $wali_kelas_id);
        $stmt->execute();
        $kelas_id = $conn->insert_id;
        
        // Buat 25 siswa
        for($i=0; $i<25; $i++) {
            $jk = (rand(0,1) == 0) ? 'L' : 'P';
            $nama = getRandomName($nis_counter, $jk);
            $uname = strtolower(explode(' ', $nama)[0]) . rand(100, 999);
            $pwd = password_hash($uname, PASSWORD_DEFAULT);
            $nis = (string)$nis_counter++;
            
            // user
            $stmt = $conn->prepare("INSERT INTO users (username, password, nama, role, status) VALUES (?, ?, ?, 'siswa', 'active')");
            $stmt->bind_param("sss", $uname, $pwd, $nama);
            $stmt->execute();
            $uid = $conn->insert_id;
            
            // siswa
            $barcode = 'STD-' . $nis;
            $stmt = $conn->prepare("INSERT INTO siswa (user_id, nis, nama, jenis_kelamin, kelas_id, barcode) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssis", $uid, $nis, $nama, $jk, $kelas_id, $barcode);
            $stmt->execute();
            
            $total_added++;
        }
    }
    $conn->commit();
    echo "Berhasil menambahkan 10 Kelas dan $total_added Siswa!";
} catch (Exception $e) {
    $conn->rollback();
    echo "Gagal: " . $e->getMessage();
}
?>
