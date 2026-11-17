<?php
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->begin_transaction();

try {
    echo "Memulai restrukturisasi jurusan menjadi TKJ, TKR, dan TSM...\n";

    // 1. Bersihkan absensi dan catatan siswa terdahulu
    $conn->query("DELETE FROM absensi_siswa");
    $conn->query("DELETE FROM catatan_siswa");

    // 2. Bersihkan siswa & kelas lama
    $conn->query("DELETE FROM siswa");
    $conn->query("DELETE FROM kelas");

    // 3. Bersihkan user siswa terdahulu
    $conn->query("DELETE FROM users WHERE role = 'siswa'");

    echo "Pembersihan data lama selesai.\n";

    // Ambil guru yang ada untuk penugasan wali kelas
    $guru_res = $conn->query("SELECT id FROM guru ORDER BY id ASC");
    $guru_ids = [];
    while ($row = $guru_res->fetch_assoc()) {
        $guru_ids[] = $row['id'];
    }
    if (empty($guru_ids)) {
        $guru_ids = [1];
    }

    // 3 Jurusan Resmi Sesuai Permintaan:
    // 1. TKJ (Teknik Komputer dan Jaringan)
    // 2. TKR (Teknik Kendaraan Ringan)
    // 3. TSM (Teknik Sepeda Motor)
    $jurusan_list = [
        ['kode' => 'TKJ', 'nama' => 'Teknik Komputer dan Jaringan'],
        ['kode' => 'TKR', 'nama' => 'Teknik Kendaraan Ringan'],
        ['kode' => 'TSM', 'nama' => 'Teknik Sepeda Motor']
    ];

    // 3 Tingkat / Angkatan
    $angkatan_list = ['X', 'XI', 'XII'];

    // 45 Siswa Unik (5 siswa per kelas x 9 kelas)
    // Pembagian 5 murid per kelas:
    $siswa_data = [
        // 1. X TKJ
        'X TKJ' => [
            ['nama' => 'Ahmad Fauzi',      'username' => 'ahmad_fauzi',      'jk' => 'L'],
            ['nama' => 'Siti Rahmawati',   'username' => 'siti_rahmawati',   'jk' => 'P'],
            ['nama' => 'Budi Setiawan',    'username' => 'budi_setiawan',    'jk' => 'L'],
            ['nama' => 'Rani Permatasari', 'username' => 'rani_permatasari', 'jk' => 'P'],
            ['nama' => 'Andi Pratama',     'username' => 'andi_pratama',     'jk' => 'L']
        ],
        // 2. X TKR
        'X TKR' => [
            ['nama' => 'Rizky Wijaya',     'username' => 'rizky_wijaya',     'jk' => 'L'],
            ['nama' => 'Maya Nugroho',     'username' => 'maya_nugroho',     'jk' => 'P'],
            ['nama' => 'Fajar Hidayat',    'username' => 'fajar_hidayat',    'jk' => 'L'],
            ['nama' => 'Dina Lestari',     'username' => 'dina_lestari',     'jk' => 'P'],
            ['nama' => 'Kevin Pratama',    'username' => 'kevin_pratama',    'jk' => 'L']
        ],
        // 3. X TSM
        'X TSM' => [
            ['nama' => 'Bima Saputro',     'username' => 'bima_saputro',     'jk' => 'L'],
            ['nama' => 'Ayu Kusuma',       'username' => 'ayu_kusuma',       'jk' => 'P'],
            ['nama' => 'Agus Kurniawan',   'username' => 'agus_kurniawan',   'jk' => 'L'],
            ['nama' => 'Putri Susanti',    'username' => 'putri_susanti',    'jk' => 'P'],
            ['nama' => 'Dicky Setiawan',   'username' => 'dicky_setiawan',   'jk' => 'L']
        ],
        // 4. XI TKJ
        'XI TKJ' => [
            ['nama' => 'Hendra Wibowo',    'username' => 'hendra_wibowo',    'jk' => 'L'],
            ['nama' => 'Sari Handayani',   'username' => 'sari_handayani',   'jk' => 'P'],
            ['nama' => 'Eko Firmansyah',   'username' => 'eko_firmansyah',   'jk' => 'L'],
            ['nama' => 'Dewi Anggraini',   'username' => 'dewi_anggraini',   'jk' => 'P'],
            ['nama' => 'Rahmat Syahputra', 'username' => 'rahmat_syahputra', 'jk' => 'L']
        ],
        // 5. XI TKR
        'XI TKR' => [
            ['nama' => 'Teguh Mahendra',   'username' => 'teguh_mahendra',   'jk' => 'L'],
            ['nama' => 'Rina Wulandari',   'username' => 'rina_wulandari',   'jk' => 'P'],
            ['nama' => 'Bambang Kusuma',   'username' => 'bambang_kusuma',   'jk' => 'L'],
            ['nama' => 'Intan Permata',    'username' => 'intan_permata',    'jk' => 'P'],
            ['nama' => 'Deni Siregar',     'username' => 'deni_siregar',     'jk' => 'L']
        ],
        // 6. XI TSM
        'XI TSM' => [
            ['nama' => 'Arif Nasution',    'username' => 'arif_nasution',    'jk' => 'L'],
            ['nama' => 'Mega Utami',       'username' => 'mega_utami',       'jk' => 'P'],
            ['nama' => 'Bayu Lubis',       'username' => 'bayu_lubis',       'jk' => 'L'],
            ['nama' => 'Tari Puspitasari', 'username' => 'tari_puspitasari', 'jk' => 'P'],
            ['nama' => 'Gilang Harahap',   'username' => 'gilang_harahap',   'jk' => 'L']
        ],
        // 7. XII TKJ
        'XII TKJ' => [
            ['nama' => 'Aditya Sinaga',     'username' => 'aditya_sinaga',     'jk' => 'L'],
            ['nama' => 'Dita Safitri',      'username' => 'dita_safitri',      'jk' => 'P'],
            ['nama' => 'Iqbal Simanjuntak', 'username' => 'iqbal_simanjuntak', 'jk' => 'L'],
            ['nama' => 'Siska Amelia',      'username' => 'siska_amelia',      'jk' => 'P'],
            ['nama' => 'Galih Ritonga',     'username' => 'galih_ritonga',     'jk' => 'L']
        ],
        // 8. XII TKR
        'XII TKR' => [
            ['nama' => 'Hafiz Situmorang', 'username' => 'hafiz_situmorang', 'jk' => 'L'],
            ['nama' => 'Nurul Fatimah',    'username' => 'nurul_fatimah',    'jk' => 'P'],
            ['nama' => 'Farid Panjaitan',  'username' => 'farid_panjaitan',  'jk' => 'L'],
            ['nama' => 'Indah Wahyuni',    'username' => 'indah_wahyuni',    'jk' => 'P'],
            ['nama' => 'Erwan Susanto',    'username' => 'erwan_susanto',    'jk' => 'L']
        ],
        // 9. XII TSM
        'XII TSM' => [
            ['nama' => 'Cahyo Wibisono',   'username' => 'cahyo_wibisono',   'jk' => 'L'],
            ['nama' => 'Fitri Handayani',  'username' => 'fitri_handayani',  'jk' => 'P'],
            ['nama' => 'Baskoro Putro',    'username' => 'baskoro_putro',    'jk' => 'L'],
            ['nama' => 'Bella Citra',      'username' => 'bella_citra',      'jk' => 'P'],
            ['nama' => 'Dimas Sanjaya',    'username' => 'dimas_sanjaya',    'jk' => 'L']
        ]
    ];

    $hashDefault = password_hash('password', PASSWORD_DEFAULT);
    $nis_counter = 260001;
    $wali_idx = 0;
    $total_kelas_created = 0;
    $total_siswa_created = 0;

    foreach ($angkatan_list as $tingkat) {
        foreach ($jurusan_list as $jur) {
            $nama_kelas = "{$tingkat} {$jur['kode']}";
            $wali_kelas_id = $guru_ids[$wali_idx % count($guru_ids)];
            $wali_idx++;

            // 1. Insert Kelas
            $stmt_k = $conn->prepare("INSERT INTO kelas (nama_kelas, jurusan, tingkat, wali_kelas_id) VALUES (?, ?, ?, ?)");
            $stmt_k->bind_param("sssi", $nama_kelas, $jur['nama'], $tingkat, $wali_kelas_id);
            $stmt_k->execute();
            $kelas_id = $conn->insert_id;
            $total_kelas_created++;

            // 2. Insert 5 Siswa di kelas ini
            $muridList = $siswa_data[$nama_kelas] ?? [];
            foreach ($muridList as $m) {
                $nis = (string)$nis_counter++;
                $barcode = "STD-" . $nis;

                // Insert User (dengan nama lengkap dan username berbasis nama)
                $stmt_u = $conn->prepare("INSERT INTO users (username, password, nama, role, status) VALUES (?, ?, ?, 'siswa', 'active')");
                $stmt_u->bind_param("sss", $m['username'], $hashDefault, $m['nama']);
                $stmt_u->execute();
                $user_id = $conn->insert_id;

                // Insert Siswa
                $stmt_s = $conn->prepare("INSERT INTO siswa (user_id, nis, nama, jenis_kelamin, kelas_id, barcode, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                $stmt_s->bind_param("isssis", $user_id, $nis, $m['nama'], $m['jk'], $kelas_id, $barcode);
                $stmt_s->execute();

                $total_siswa_created++;
            }
        }
    }

    $conn->commit();

    echo "BERHASIL DI-RESTRUKTURISASI!\n";
    echo "- Total Jurusan: 3 (TKJ, TKR, TSM)\n";
    echo "- Total Kelas: {$total_kelas_created} (1 kelas per jurusan per angkatan)\n";
    echo "- Total Siswa: {$total_siswa_created} (5 murid per kelas)\n";
    echo "- Semua akun siswa dapat login menggunakan Nama Lengkap atau Username masing-masing!\n";
    echo "- Password default: password\n";

} catch (Exception $e) {
    $conn->rollback();
    echo "GAGAL: " . $e->getMessage() . "\n";
}

$conn->close();
