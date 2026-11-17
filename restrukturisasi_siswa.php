<?php
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->begin_transaction();

try {
    echo "Memulai restrukturisasi data siswa & kelas...\n";

    // 1. Bersihkan data absensi siswa & catatan siswa
    $conn->query("DELETE FROM absensi_siswa");
    $conn->query("DELETE FROM catatan_siswa");

    // 2. Hapus data siswa
    $conn->query("DELETE FROM siswa");

    // 3. Hapus kelas lama
    $conn->query("DELETE FROM kelas");

    // 4. Hapus user dengan role 'siswa'
    $conn->query("DELETE FROM users WHERE role = 'siswa'");

    echo "Data lama berhasil dibersihkan.\n";

    // Ambil daftar guru yang ada untuk wali kelas
    $guru_res = $conn->query("SELECT id FROM guru ORDER BY id ASC");
    $guru_ids = [];
    while ($row = $guru_res->fetch_assoc()) {
        $guru_ids[] = $row['id'];
    }
    if (empty($guru_ids)) {
        $guru_ids = [1];
    }

    // 3 Jurusan
    $jurusan_list = [
        [
            'kode' => 'RPL',
            'nama' => 'Rekayasa Perangkat Lunak'
        ],
        [
            'kode' => 'TKJ',
            'nama' => 'Teknik Komputer dan Jaringan'
        ],
        [
            'kode' => 'DKV',
            'nama' => 'Desain Komunikasi Visual'
        ]
    ];

    // 3 Angkatan
    $angkatan_list = ['X', 'XI', 'XII'];

    // Generator Nama Siswa Indonesia
    $names_male = [
        'Budi Santoso', 'Andi Pratama', 'Rizky Wijaya', 'Fajar Hidayat', 'Kevin Nugroho',
        'Bima Saputro', 'Agus Kurniawan', 'Dicky Setiawan', 'Hendra Wibowo', 'Eko Firmansyah',
        'Rahmat Syahputra', 'Teguh Mahendra', 'Bambang Kusuma', 'Deni Siregar', 'Arif Nasution',
        'Bayu Lubis', 'Gilang Harahap', 'Aditya Sinaga', 'Iqbal Simanjuntak', 'Galih Ritonga',
        'Hafiz Situmorang', 'Farid Panjaitan', 'Erwan Susanto', 'Cahyo Wibisono', 'Baskoro Putro'
    ];

    $names_female = [
        'Siti Rahmawati', 'Rani Permatasari', 'Maya Nugroho', 'Dina Lestari', 'Ayu Kusuma',
        'Putri Susanti', 'Sari Handayani', 'Dewi Anggraini', 'Rina Wulandari', 'Intan Permata',
        'Mega Utami', 'Tari Puspitasari', 'Dita Safitri', 'Siska Amelia', 'Nurul Fatimah',
        'Indah Wahyuni', 'Fitri Handayani', 'Bella Citra', 'Citra Kirana', 'Desy Natalia',
        'Eka Ramadhani', 'Gita Gutawa', 'Hani Prawira', 'Ira Koesno', 'Juwita Bahar'
    ];

    $nis_counter = 260001;
    $wali_idx = 0;
    $is_first_student = true;
    $total_siswa_created = 0;
    $total_kelas_created = 0;

    $male_ptr = 0;
    $female_ptr = 0;

    foreach ($angkatan_list as $tingkat) {
        foreach ($jurusan_list as $jur) {
            $nama_kelas = "{$tingkat} {$jur['kode']}";
            $wali_kelas_id = $guru_ids[$wali_idx % count($guru_ids)];
            $wali_idx++;

            // Insert Kelas
            $stmt_k = $conn->prepare("INSERT INTO kelas (nama_kelas, jurusan, tingkat, wali_kelas_id) VALUES (?, ?, ?, ?)");
            $stmt_k->bind_param("sssi", $nama_kelas, $jur['nama'], $tingkat, $wali_kelas_id);
            $stmt_k->execute();
            $kelas_id = $conn->insert_id;
            $total_kelas_created++;

            // Insert 5 Siswa per kelas
            for ($s = 1; $s <= 5; $s++) {
                // Bergantian laki-laki dan perempuan
                $is_male = ($s % 2 !== 0);
                $jk = $is_male ? 'L' : 'P';

                if ($is_first_student) {
                    $nama = "Siswa Dummy (Tester)";
                    $uname = "siswa";
                    $pwd = password_hash('siswa', PASSWORD_DEFAULT);
                    $jk = 'L';
                    $is_first_student = false;
                } else {
                    if ($is_male) {
                        $nama = $names_male[$male_ptr % count($names_male)];
                        $male_ptr++;
                    } else {
                        $nama = $names_female[$female_ptr % count($names_female)];
                        $female_ptr++;
                    }
                    $name_part = strtolower(explode(' ', $nama)[0]);
                    $uname = $name_part . substr((string)$nis_counter, -3);
                    $pwd = password_hash('siswa123', PASSWORD_DEFAULT);
                }

                $nis = (string)$nis_counter++;
                $barcode = "STD-" . $nis;

                // 1. Insert ke tabel users
                $stmt_u = $conn->prepare("INSERT INTO users (username, password, nama, role, status) VALUES (?, ?, ?, 'siswa', 'active')");
                $stmt_u->bind_param("sss", $uname, $pwd, $nama);
                $stmt_u->execute();
                $user_id = $conn->insert_id;

                // 2. Insert ke tabel siswa
                $stmt_s = $conn->prepare("INSERT INTO siswa (user_id, nis, nama, jenis_kelamin, kelas_id, barcode, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                $stmt_s->bind_param("isssis", $user_id, $nis, $nama, $jk, $kelas_id, $barcode);
                $stmt_s->execute();
                $siswa_id = $conn->insert_id;

                $total_siswa_created++;
            }
        }
    }

    $conn->commit();

    echo "BERHASIL!\n";
    echo "Total Kelas Dibuat: $total_kelas_created (3 Jurusan x 3 Angkatan)\n";
    echo "Total Siswa Dibuat: $total_siswa_created (9 Kelas x 5 Siswa)\n";

} catch (Exception $e) {
    $conn->rollback();
    echo "GAGAL: " . $e->getMessage() . "\n";
}

$conn->close();
