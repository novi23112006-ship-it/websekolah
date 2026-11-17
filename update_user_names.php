<?php
require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->begin_transaction();

try {
    echo "Memperbarui username & password guru dan siswa berdasarkan nama masing-masing...\n";

    $hashPassword = password_hash('password', PASSWORD_DEFAULT);

    // 1. Update Guru & Pegawai
    $guruUpdates = [
        ['user_id' => 2,  'username' => 'budi_santoso',  'nama' => 'Drs. Budi Santoso'],
        ['user_id' => 3,  'username' => 'siti_aminah',   'nama' => 'Siti Aminah, S.Pd'],
        ['user_id' => 4,  'username' => 'andi_hermawan', 'nama' => 'Andi Hermawan, M.Kom'],
        ['user_id' => 5,  'username' => 'rina_marlina',  'nama' => 'Rina Marlina, S.Pd'],
        ['user_id' => 13, 'username' => 'wali_dummy',    'nama' => 'Wali Kelas Dummy 01'],
    ];

    foreach ($guruUpdates as $gu) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, nama = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $gu['username'], $gu['nama'], $hashPassword, $gu['user_id']);
        $stmt->execute();

        // Pastikan nama di tabel guru juga konsisten
        $stmt_g = $conn->prepare("UPDATE guru SET nama = ? WHERE user_id = ?");
        $stmt_g->bind_param("si", $gu['nama'], $gu['user_id']);
        $stmt_g->execute();
    }
    echo "Data Guru berhasil diperbarui dengan username berbasis nama.\n";

    // 2. Data 45 Siswa dengan nama dan username unik
    $siswaList = [
        // X RPL
        ['nis' => '260001', 'nama' => 'Ahmad Fauzi',         'username' => 'ahmad_fauzi'],
        ['nis' => '260002', 'nama' => 'Siti Rahmawati',      'username' => 'siti_rahmawati'],
        ['nis' => '260003', 'nama' => 'Budi Setiawan',       'username' => 'budi_setiawan'],
        ['nis' => '260004', 'nama' => 'Rani Permatasari',    'username' => 'rani_permatasari'],
        ['nis' => '260005', 'nama' => 'Andi Pratama',        'username' => 'andi_pratama'],

        // X TKJ
        ['nis' => '260006', 'nama' => 'Rizky Wijaya',        'username' => 'rizky_wijaya'],
        ['nis' => '260007', 'nama' => 'Maya Nugroho',        'username' => 'maya_nugroho'],
        ['nis' => '260008', 'nama' => 'Fajar Hidayat',       'username' => 'fajar_hidayat'],
        ['nis' => '260009', 'nama' => 'Dina Lestari',        'username' => 'dina_lestari'],
        ['nis' => '260010', 'nama' => 'Kevin Pratama',       'username' => 'kevin_pratama'],

        // X DKV
        ['nis' => '260011', 'nama' => 'Bima Saputro',        'username' => 'bima_saputro'],
        ['nis' => '260012', 'nama' => 'Ayu Kusuma',          'username' => 'ayu_kusuma'],
        ['nis' => '260013', 'nama' => 'Agus Kurniawan',      'username' => 'agus_kurniawan'],
        ['nis' => '260014', 'nama' => 'Putri Susanti',       'username' => 'putri_susanti'],
        ['nis' => '260015', 'nama' => 'Dicky Setiawan',      'username' => 'dicky_setiawan'],

        // XI RPL
        ['nis' => '260016', 'nama' => 'Hendra Wibowo',       'username' => 'hendra_wibowo'],
        ['nis' => '260017', 'nama' => 'Sari Handayani',      'username' => 'sari_handayani'],
        ['nis' => '260018', 'nama' => 'Eko Firmansyah',      'username' => 'eko_firmansyah'],
        ['nis' => '260019', 'nama' => 'Dewi Anggraini',      'username' => 'dewi_anggraini'],
        ['nis' => '260020', 'nama' => 'Rahmat Syahputra',    'username' => 'rahmat_syahputra'],

        // XI TKJ
        ['nis' => '260021', 'nama' => 'Teguh Mahendra',      'username' => 'teguh_mahendra'],
        ['nis' => '260022', 'nama' => 'Rina Wulandari',      'username' => 'rina_wulandari'],
        ['nis' => '260023', 'nama' => 'Bambang Kusuma',      'username' => 'bambang_kusuma'],
        ['nis' => '260024', 'nama' => 'Intan Permata',       'username' => 'intan_permata'],
        ['nis' => '260025', 'nama' => 'Deni Siregar',        'username' => 'deni_siregar'],

        // XI DKV
        ['nis' => '260026', 'nama' => 'Arif Nasution',       'username' => 'arif_nasution'],
        ['nis' => '260027', 'nama' => 'Mega Utami',          'username' => 'mega_utami'],
        ['nis' => '260028', 'nama' => 'Bayu Lubis',          'username' => 'bayu_lubis'],
        ['nis' => '260029', 'nama' => 'Tari Puspitasari',    'username' => 'tari_puspitasari'],
        ['nis' => '260030', 'nama' => 'Gilang Harahap',      'username' => 'gilang_harahap'],

        // XII RPL
        ['nis' => '260031', 'nama' => 'Aditya Sinaga',       'username' => 'aditya_sinaga'],
        ['nis' => '260032', 'nama' => 'Dita Safitri',        'username' => 'dita_safitri'],
        ['nis' => '260033', 'nama' => 'Iqbal Simanjuntak',   'username' => 'iqbal_simanjuntak'],
        ['nis' => '260034', 'nama' => 'Siska Amelia',        'username' => 'siska_amelia'],
        ['nis' => '260035', 'nama' => 'Galih Ritonga',       'username' => 'galih_ritonga'],

        // XII TKJ
        ['nis' => '260036', 'nama' => 'Hafiz Situmorang',    'username' => 'hafiz_situmorang'],
        ['nis' => '260037', 'nama' => 'Nurul Fatimah',       'username' => 'nurul_fatimah'],
        ['nis' => '260038', 'nama' => 'Farid Panjaitan',     'username' => 'farid_panjaitan'],
        ['nis' => '260039', 'nama' => 'Indah Wahyuni',       'username' => 'indah_wahyuni'],
        ['nis' => '260040', 'nama' => 'Erwan Susanto',       'username' => 'erwan_susanto'],

        // XII DKV
        ['nis' => '260041', 'nama' => 'Cahyo Wibisono',      'username' => 'cahyo_wibisono'],
        ['nis' => '260042', 'nama' => 'Fitri Handayani',     'username' => 'fitri_handayani'],
        ['nis' => '260043', 'nama' => 'Baskoro Putro',       'username' => 'baskoro_putro'],
        ['nis' => '260044', 'nama' => 'Bella Citra',         'username' => 'bella_citra'],
        ['nis' => '260045', 'nama' => 'Dimas Sanjaya',       'username' => 'dimas_sanjaya'],
    ];

    foreach ($siswaList as $s) {
        // Ambil user_id siswa berdasarkan NIS
        $res = $conn->query("SELECT id, user_id FROM siswa WHERE nis = '{$s['nis']}'");
        if ($row = $res->fetch_assoc()) {
            $siswaId = $row['id'];
            $userId  = $row['user_id'];

            // Update user
            $stmt_u = $conn->prepare("UPDATE users SET username = ?, nama = ?, password = ? WHERE id = ?");
            $stmt_u->bind_param("sssi", $s['username'], $s['nama'], $hashPassword, $userId);
            $stmt_u->execute();

            // Update siswa
            $stmt_s = $conn->prepare("UPDATE siswa SET nama = ? WHERE id = ?");
            $stmt_s->bind_param("si", $s['nama'], $siswaId);
            $stmt_s->execute();
        }
    }
    echo "45 Siswa berhasil diperbarui dengan nama & username masing-masing.\n";

    $conn->commit();
    echo "UPDATE SELESAI!\n";

} catch (Exception $e) {
    $conn->rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
}

$conn->close();
