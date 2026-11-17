<?php
/**
 * Script Restrukturisasi Rombel Kelas 1, 2, 3
 * Menjadikan seluruh jurusan (TKJ, TKR, TSM) memiliki 3 rombel paralel (1, 2, 3) di setiap angkatan (X, XI, XII).
 * Total 27 Kelas @ 5 Siswa = 135 Siswa.
 */

require_once 'config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error . "\n");
}

$conn->begin_transaction();

try {
    echo "=== MEMULAI RESTRUKTURISASI KELAS MENJADI ROMBEL 1, 2, 3 ===\n\n";

    // 1. Ambil daftar guru yang ada untuk rotasi wali kelas
    $guru_res = $conn->query("SELECT id FROM guru ORDER BY id ASC");
    $guru_ids = [];
    while ($row = $guru_res->fetch_assoc()) {
        $guru_ids[] = $row['id'];
    }
    if (empty($guru_ids)) {
        $guru_ids = [1];
    }

    // 2. Definisi 3 Jurusan dan 3 Angkatan
    $jurusan_map = [
        'TKJ' => 'Teknik Komputer dan Jaringan',
        'TKR' => 'Teknik Kendaraan Ringan',
        'TSM' => 'Teknik Sepeda Motor'
    ];
    $angkatan_list = ['X', 'XI', 'XII'];

    // 3. Update 9 Kelas Existing yang tadinya tanpa angka rombel menjadi Rombel 1
    // Contoh: 'X TKJ' -> 'X TKJ 1'
    $rename_map = [
        'X TKJ'   => 'X TKJ 1',
        'X TKR'   => 'X TKR 1',
        'X TSM'   => 'X TSM 1',
        'XI TKJ'  => 'XI TKJ 1',
        'XI TKR'  => 'XI TKR 1',
        'XI TSM'  => 'XI TSM 1',
        'XII TKJ' => 'XII TKJ 1',
        'XII TKR' => 'XII TKR 1',
        'XII TSM' => 'XII TSM 1'
    ];

    echo "1. Memperbarui 9 kelas existing menjadi Rombel 1...\n";
    foreach ($rename_map as $old_name => $new_name) {
        $stmt = $conn->prepare("UPDATE kelas SET nama_kelas = ? WHERE nama_kelas = ?");
        $stmt->bind_param("ss", $new_name, $old_name);
        $stmt->execute();
        echo "   - '$old_name' berhasil diubah menjadi '$new_name'\n";
    }

    // 4. Data 90 Siswa Baru untuk 18 Kelas Baru (Rombel 2 dan 3)
    $siswa_baru = [
        // === ANGKATAN X ===
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

        // === ANGKATAN XI ===
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

        // === ANGKATAN XII ===
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

    // 5. Buat 18 Kelas Baru dan Siswanya
    echo "\n2. Membuat 18 rombel baru (Rombel 2 & 3) beserta 90 siswa baru...\n";

    $hashDefault = password_hash('password', PASSWORD_DEFAULT);
    $nis_counter = 260046;
    $wali_idx = 0;
    $kelas_baru_count = 0;
    $siswa_baru_count = 0;

    foreach ($angkatan_list as $tingkat) {
        foreach ($jurusan_map as $kode_jur => $nama_jur) {
            for ($rombel = 2; $rombel <= 3; $rombel++) {
                $nama_kelas = "{$tingkat} {$kode_jur} {$rombel}";
                $wali_kelas_id = $guru_ids[$wali_idx % count($guru_ids)];
                $wali_idx++;

                // Periksa apakah kelas sudah ada
                $chk = $conn->prepare("SELECT id FROM kelas WHERE nama_kelas = ?");
                $chk->bind_param("s", $nama_kelas);
                $chk->execute();
                $res_chk = $chk->get_result();

                if ($row = $res_chk->fetch_assoc()) {
                    $kelas_id = $row['id'];
                    echo "   - Kelas '$nama_kelas' sudah ada (ID: $kelas_id)\n";
                } else {
                    $stmt_k = $conn->prepare("INSERT INTO kelas (nama_kelas, jurusan, tingkat, wali_kelas_id) VALUES (?, ?, ?, ?)");
                    $stmt_k->bind_param("sssi", $nama_kelas, $nama_jur, $tingkat, $wali_kelas_id);
                    $stmt_k->execute();
                    $kelas_id = $conn->insert_id;
                    $kelas_baru_count++;
                    echo "   + Kelas '$nama_kelas' dibuat (ID: $kelas_id, Wali ID: $wali_kelas_id)\n";
                }

                // Tambah 5 siswa untuk rombel ini jika belum ada
                $muridList = $siswa_baru[$nama_kelas] ?? [];
                foreach ($muridList as $m) {
                    // Cek apakah user sudah ada
                    $chk_u = $conn->prepare("SELECT id FROM users WHERE username = ?");
                    $chk_u->bind_param("s", $m['username']);
                    $chk_u->execute();
                    $res_u = $chk_u->get_result();

                    if ($u_row = $res_u->fetch_assoc()) {
                        $user_id = $u_row['id'];
                    } else {
                        $stmt_u = $conn->prepare("INSERT INTO users (username, password, nama, role, status) VALUES (?, ?, ?, 'siswa', 'active')");
                        $stmt_u->bind_param("sss", $m['username'], $hashDefault, $m['nama']);
                        $stmt_u->execute();
                        $user_id = $conn->insert_id;
                    }

                    // Cek apakah siswa sudah ada di tabel siswa
                    $chk_s = $conn->prepare("SELECT id FROM siswa WHERE user_id = ?");
                    $chk_s->bind_param("i", $user_id);
                    $chk_s->execute();
                    $res_s = $chk_s->get_result();

                    if (!$res_s->fetch_assoc()) {
                        $nis = (string)$nis_counter++;
                        $barcode = "STD-" . $nis;
                        $stmt_s = $conn->prepare("INSERT INTO siswa (user_id, nis, nama, jenis_kelamin, kelas_id, barcode, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                        $stmt_s->bind_param("isssis", $user_id, $nis, $m['nama'], $m['jk'], $kelas_id, $barcode);
                        $stmt_s->execute();
                        $siswa_baru_count++;
                    }
                }
            }
        }
    }

    $conn->commit();

    echo "\n=== RESTRUKTURISASI SELESAI DENGAN SUKSES! ===\n";
    echo "- Total Kelas Baru Dibuat: {$kelas_baru_count}\n";
    echo "- Total Siswa Baru Dibuat: {$siswa_baru_count}\n";

} catch (Exception $e) {
    $conn->rollback();
    echo "\n[ERROR]: " . $e->getMessage() . "\n";
}

$conn->close();
