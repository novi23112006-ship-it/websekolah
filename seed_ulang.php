<?php
require_once 'config/config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) { die('Koneksi Gagal: ' . $conn->connect_error); }

$conn->begin_transaction();
try {
    // 1. Hapus data lama yang bergantung
    $conn->query("DELETE FROM absensi_siswa");
    $conn->query("DELETE FROM catatan_siswa");
    $conn->query("DELETE FROM siswa");
    $conn->query("DELETE FROM kelas");
    $conn->query("DELETE FROM users WHERE role='siswa'");

    $first_names_m = ['Budi','Andi','Rizal','Kevin','Bima','Fajar','Rizky','Agus','Dicky','Hendra','Eko','Rahmat','Dian','Teguh','Bambang','Wawan','Deni','Arif','Bayu','Gilang','Aditya','Baskoro','Cahyo','Darmanto','Erwan','Farid','Galih','Hafiz','Iqbal','Joko'];
    $first_names_f = ['Siti','Bunga','Dina','Rina','Ayu','Putri','Sari','Dewi','Lestari','Nisa','Rani','Intan','Mega','Tari','Dita','Maya','Siska','Nurul','Indah','Fitri','Amalia','Bella','Citra','Desy','Eka','Fatimah','Gita','Hani','Ira','Juwita'];
    $last_names = ['Sanjaya','Sakti','Wijaya','Saputro','Pratama','Santoso','Hidayat','Kurniawan','Nugroho','Setiawan','Wibowo','Firmansyah','Syahputra','Mahendra','Lestari','Susanti','Rahmawati','Sari','Kusuma','Permatasari','Putra','Siregar','Nasution','Lubis','Harahap','Sinaga','Simanjuntak','Ritonga','Situmorang','Panjaitan'];

    function getRandomName($gender) {
        global $first_names_m, $first_names_f, $last_names;
        $f = $gender == 'L' ? $first_names_m[array_rand($first_names_m)] : $first_names_f[array_rand($first_names_f)];
        $l = $last_names[array_rand($last_names)];
        return $f . ' ' . $l;
    }

    $wali_kelas_id = 4; // menggunakan guru ID 4 yang valid (Rina Marlina, S.Pd)
    $nis_counter = 260001;
    $total_siswa = 0;
    
    // Flag untuk membuat user khusus 'siswa' yang bisa login mudah
    $is_first_student = true;

    // Jurusan TKJ (X, XI, XII masing-masing 3 kelas @ 25 siswa)
    $tingkat = ['X', 'XI', 'XII'];
    foreach($tingkat as $tkt) {
        for($i=1; $i<=3; $i++) {
            $nama_kelas = "$tkt TKJ $i";
            $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, jurusan, tingkat, wali_kelas_id) VALUES (?, 'Teknik Komputer dan Jaringan', ?, ?)");
            $stmt->bind_param("ssi", $nama_kelas, $tkt, $wali_kelas_id);
            $stmt->execute();
            $kelas_id = $conn->insert_id;
            
            for($s=0; $s<25; $s++) {
                $jk = (rand(0,1) == 0) ? 'L' : 'P';
                $nama = getRandomName($jk);
                
                if($is_first_student) {
                    $uname = 'siswa';
                    $pwd = password_hash('siswa', PASSWORD_DEFAULT);
                    $nama = 'Siswa Dummy (Tester)';
                    $is_first_student = false;
                } else {
                    $uname = strtolower(explode(' ', $nama)[0]) . rand(100,999);
                    $pwd = password_hash($uname, PASSWORD_DEFAULT);
                }
                
                $nis = (string)$nis_counter++;
                
                // insert user
                $stmt_u = $conn->prepare("INSERT INTO users (username, password, nama, role, status) VALUES (?, ?, ?, 'siswa', 'active')");
                $stmt_u->bind_param("sss", $uname, $pwd, $nama);
                $stmt_u->execute();
                $uid = $conn->insert_id;
                
                // insert siswa
                $barcode = 'STD-' . $nis;
                $stmt_s = $conn->prepare("INSERT INTO siswa (user_id, nis, nama, jenis_kelamin, kelas_id, barcode) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_s->bind_param("isssis", $uid, $nis, $nama, $jk, $kelas_id, $barcode);
                $stmt_s->execute();
                
                $total_siswa++;
            }
        }
    }
    
    // Jurusan RPL (X, XI, XII masing-masing 3 kelas @ 20 siswa)
    foreach($tingkat as $tkt) {
        for($i=1; $i<=3; $i++) {
            $nama_kelas = "$tkt RPL $i";
            $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, jurusan, tingkat, wali_kelas_id) VALUES (?, 'Rekayasa Perangkat Lunak', ?, ?)");
            $stmt->bind_param("ssi", $nama_kelas, $tkt, $wali_kelas_id);
            $stmt->execute();
            $kelas_id = $conn->insert_id;
            
            for($s=0; $s<20; $s++) {
                $jk = (rand(0,1) == 0) ? 'L' : 'P';
                $nama = getRandomName($jk);
                $uname = strtolower(explode(' ', $nama)[0]) . rand(100,999);
                $pwd = password_hash($uname, PASSWORD_DEFAULT);
                $nis = (string)$nis_counter++;
                
                // insert user
                $stmt_u = $conn->prepare("INSERT INTO users (username, password, nama, role, status) VALUES (?, ?, ?, 'siswa', 'active')");
                $stmt_u->bind_param("sss", $uname, $pwd, $nama);
                $stmt_u->execute();
                $uid = $conn->insert_id;
                
                // insert siswa
                $barcode = 'STD-' . $nis;
                $stmt_s = $conn->prepare("INSERT INTO siswa (user_id, nis, nama, jenis_kelamin, kelas_id, barcode) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_s->bind_param("isssis", $uid, $nis, $nama, $jk, $kelas_id, $barcode);
                $stmt_s->execute();
                
                $total_siswa++;
            }
        }
    }
    
    $conn->commit();
    echo "BERHASIL! Total Kelas: 18 (9 TKJ, 9 RPL). Total Siswa Baru: $total_siswa\n";
    echo "Login siswa tester: username='siswa', password='siswa'";
} catch (Exception $e) {
    $conn->rollback();
    echo "GAGAL: " . $e->getMessage();
}
?>
