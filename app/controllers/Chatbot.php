<?php

class Chatbot extends Controller {

    public function index() {
        // Bisa diakses oleh pengguna yang sudah login maupun tamu/pengunjung
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');

        if (empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong.']);
            exit;
        }

        if (strlen($message) > 500) {
            echo json_encode(['success' => false, 'message' => 'Pesan terlalu panjang. Mohon dipersingkat.']);
            exit;
        }

        $role = $_SESSION['role'] ?? 'pengguna';
        $username = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Tamu';
        $user_id = $_SESSION['user_id'] ?? null;

        // Filter cepat untuk permintaan coding atau script
        $codingKeywords = ['coding', 'script', 'skrip', 'python', 'javascript', 'html', 'css', 'bikin web', 'buat aplikasi', 'bikin kode', 'buatkan kode', 'buat coding', 'tulis kode'];
        $lowerMsg = strtolower($message);
        $isCoding = false;
        foreach ($codingKeywords as $kw) {
            if (strpos($lowerMsg, $kw) !== false) {
                $isCoding = true;
                break;
            }
        }

        if ($isCoding && (strpos($lowerMsg, 'buat') !== false || strpos($lowerMsg, 'bikin') !== false || strpos($lowerMsg, 'tulis') !== false || strpos($lowerMsg, 'code') !== false)) {
            header('Content-Type: application/json');
            $refuseMsg = ($role === 'kepala_sekolah')
                ? 'Mohon maaf Bapak/Ibu Kepala Sekolah, saya adalah asisten khusus Dashboard Kepala Sekolah. Saya hanya dapat menjawab pertanyaan seputar monitoring presensi dewan guru, data guru, jadwal KBM, dan fitur yang ada di Dashboard Kepala Sekolah.'
                : 'Maaf ya kak, saya adalah asisten khusus Sistem Absensi & Informasi SMK ini. Saya hanya dapat menjawab pertanyaan seputar data sistem sekolah, kelas, guru, siswa, dan absensi di sistem ini.';
            echo json_encode([
                'success' => true,
                'message' => $refuseMsg
            ]);
            exit;
        }

        $isApiConfigured = ChatbotHelper::isConfigured() && ChatbotHelper::isActive();

        $jawaban = null;
        $aiError = false;

        if ($isApiConfigured) {
            $system_context = $this->buildSystemPrompt($role, $username, $user_id, $message);
            $jawaban = $this->panggilAiAPI($system_context, $message);
            if ($jawaban === null) {
                $aiError = true;
            }
        }

        // Fallback jika AI tidak merespons
        if (empty($jawaban)) {
            $ruleAnswer = $this->getRuleBasedAnswer($message, $role, $username, $user_id);
            if ($ruleAnswer) {
                $jawaban = $ruleAnswer;
            } elseif ($aiError) {
                $jawaban = ($role === 'kepala_sekolah')
                    ? 'Mohon maaf Bapak/Ibu Kepala Sekolah, layanan asisten AI sedang mengalami kendala. Silakan coba beberapa saat lagi.'
                    : 'Maaf ya kak, layanan AI sedang mengalami kendala. Silakan coba beberapa saat lagi ya!';
            } else {
                $jawaban = ($role === 'kepala_sekolah')
                    ? 'Mohon maaf Bapak/Ibu Kepala Sekolah, saya adalah asisten khusus Dashboard Kepala Sekolah. Saya hanya dapat menjawab pertanyaan seputar monitoring presensi dewan guru, data guru, jadwal KBM, dan fitur yang ada di Dashboard Kepala Sekolah.'
                    : 'Maaf ya kak, saya adalah asisten khusus Sistem Absensi & Informasi SMK ini. Saya hanya dapat menjawab pertanyaan seputar data sistem sekolah, kelas, guru, siswa, dan absensi di sistem ini.';
            }
        }

        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => $jawaban
        ]);
        exit;
    }

    /**
     * Membaca seluruh informasi database sistem secara real-time
     */
    /**
     * Membaca informasi database sistem KHUSUS untuk Kepala Sekolah
     * Hanya mencakup apa yang ada di Dashboard Kepala Sekolah (Data dewan guru, monitoring presensi guru, KBM).
     * Sama sekali TIDAK memuat data siswa maupun rekap absensi siswa.
     */
    private function getKepalaSekolahDatabaseContext($db, $username, $userQuery) {
        // 1. Total Dewan Guru
        $db->query("SELECT COUNT(id) as total FROM guru WHERE status = 'active'");
        $totalGuru = (int)$db->single()['total'];

        // 2. Statistik Presensi Dewan Guru Hari Ini
        $statuses = ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'];
        $guruStats = [];
        $totalSudahAbsen = 0;
        foreach ($statuses as $st) {
            $db->query("SELECT COUNT(id) as total FROM absensi_guru WHERE tanggal = CURDATE() AND status = :st");
            $db->bind('st', $st);
            $cnt = (int)$db->single()['total'];
            $guruStats[$st] = $cnt;
            $totalSudahAbsen += $cnt;
        }
        $guruBelumAbsenCount = max(0, $totalGuru - $totalSudahAbsen);
        $presensiGuru = $guruStats['hadir'] + $guruStats['terlambat'];
        $pctKehadiran = ($totalGuru > 0) ? round(($presensiGuru / $totalGuru) * 100) : 0;
        $guruTidakHadir = $guruStats['izin'] + $guruStats['sakit'] + $guruStats['alpa'];

        // 3. Daftar Dewan Guru yang BELUM ABSEN hari ini
        $db->query("SELECT g.nama, g.nip, g.mata_pelajaran 
                    FROM guru g 
                    WHERE g.status = 'active' 
                    AND g.id NOT IN (SELECT guru_id FROM absensi_guru WHERE tanggal = CURDATE())
                    ORDER BY g.nama ASC");
        $guruBelumAbsen = $db->resultSet();
        $listBelumAbsen = [];
        if (!empty($guruBelumAbsen)) {
            foreach ($guruBelumAbsen as $gba) {
                $listBelumAbsen[] = "- {$gba['nama']} (NIP: " . ($gba['nip'] ?: '-') . ", Mapel: {$gba['mata_pelajaran']})";
            }
        } else {
            $listBelumAbsen[] = "- Seluruh dewan guru telah melakukan absensi hari ini.";
        }

        // 4. Daftar Dewan Guru yang SUDAH ABSEN hari ini
        $db->query("SELECT g.nama, a.status, a.jam_masuk, a.jam_pulang, a.keterangan 
                    FROM absensi_guru a 
                    JOIN guru g ON a.guru_id = g.id 
                    WHERE a.tanggal = CURDATE()
                    ORDER BY a.jam_masuk DESC");
        $guruSudahAbsen = $db->resultSet();
        $listSudahAbsen = [];
        if (!empty($guruSudahAbsen)) {
            foreach ($guruSudahAbsen as $gsa) {
                $jam = $gsa['jam_masuk'] ? date('H:i', strtotime($gsa['jam_masuk'])) . ' WIB' : '-';
                $ket = $gsa['keterangan'] ? " (Ket: {$gsa['keterangan']})" : '';
                $listSudahAbsen[] = "- {$gsa['nama']} | Status: " . strtoupper($gsa['status']) . " | Jam Masuk: {$jam}{$ket}";
            }
        } else {
            $listSudahAbsen[] = "- Belum ada dewan guru yang melakukan absensi hari ini.";
        }

        // 5. Daftar Lengkap Dewan Guru & Staff
        $db->query("SELECT g.nama, g.nip, g.mata_pelajaran, COALESCE(u.role, 'guru') as role 
                    FROM guru g 
                    LEFT JOIN users u ON g.user_id = u.id
                    WHERE g.status = 'active'
                    ORDER BY g.nama ASC");
        $allGurus = $db->resultSet();
        $guruList = [];
        $noKS = 1;
        foreach ($allGurus as $g) {
            $nip = !empty($g['nip']) ? " (NIP: {$g['nip']})" : '';
            $mapel = !empty($g['mata_pelajaran']) ? " - Mapel/Tugas: {$g['mata_pelajaran']}" : '';
            $guruList[] = "{$noKS}. {$g['nama']}{$nip}{$mapel}";
            $noKS++;
        }

        // 6. Data Jadwal Mengajar (KBM Seluruh Guru)
        $db->query("SELECT jm.hari, jm.jam_mulai, jm.jam_selesai, jm.ruangan, 
                           g.nama as nama_guru, k.nama_kelas, mp.kode_mapel, mp.nama_mapel
                    FROM jadwal_mengajar jm
                    JOIN guru g ON jm.guru_id = g.id
                    JOIN kelas k ON jm.kelas_id = k.id
                    LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                    WHERE jm.status = 'active'
                    ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC");
        $allJadwals = $db->resultSet();
        $jadwalList = array_map(function($j) {
            $jam = date('H:i', strtotime($j['jam_mulai'])) . ' - ' . date('H:i', strtotime($j['jam_selesai']));
            return "- {$j['hari']} ($jam WIB) | Guru: {$j['nama_guru']} | Mapel: {$j['nama_mapel']} ({$j['kode_mapel']}) | Kelas: {$j['nama_kelas']} | Ruangan: {$j['ruangan']}";
        }, $allJadwals);

        // 7. Daftar Mata Pelajaran
        $db->query("SELECT kode_mapel, nama_mapel, kelompok, tingkat, jam_per_minggu 
                    FROM mata_pelajaran 
                    WHERE status = 'active'
                    ORDER BY FIELD(kelompok, 'Muatan Nasional', 'Muatan Kewilayahan', 'Muatan Peminatan Kejuruan', 'Muatan Lokal'), tingkat, nama_mapel ASC");
        $allMapels = $db->resultSet();
        $mapelList = array_map(function($m) {
            return "- [{$m['kode_mapel']}] {$m['nama_mapel']} ({$m['kelompok']}, Tingkat {$m['tingkat']}, {$m['jam_per_minggu']} JP/minggu)";
        }, $allMapels);

        $context = "INFORMASI RESMI DATABASE SISTEM KHUSUS DASHBOARD MONITORING KEPALA SEKOLAH:\n";
        $context .= "(PENTING: Seluruh data di bawah ini adalah SATU-SATUNYA cakupan informasi yang boleh Anda gunakan. Akun Kepala Sekolah tidak memiliki data siswa maupun absensi siswa).\n\n";

        $context .= "1. EXECUTIVE MONITORING PRESENSI DEWAN GURU HARI INI:\n";
        $context .= "- Total Dewan Guru Terdaftar: {$totalGuru} orang\n";
        $context .= "- Persentase Kehadiran Guru: {$pctKehadiran}%\n";
        $context .= "- Guru Hadir Tepat Waktu: {$guruStats['hadir']} orang\n";
        $context .= "- Guru Terlambat: {$guruStats['terlambat']} orang\n";
        $context .= "- Guru Izin: {$guruStats['izin']} orang\n";
        $context .= "- Guru Sakit: {$guruStats['sakit']} orang\n";
        $context .= "- Guru Alpa (Tanpa Keterangan): {$guruStats['alpa']} orang\n";
        $context .= "- Total Guru Tidak Hadir (Izin/Sakit/Alpa): {$guruTidakHadir} orang\n";
        $context .= "- Guru Belum Melakukan Absensi Hari Ini: {$guruBelumAbsenCount} orang\n";
        $context .= "- Jam Masuk Guru: Maksimal pukul " . JAM_MASUK_GURU . " WIB (lewat jam ini tercatat terlambat)\n";
        $context .= "- Metode Absensi Guru: Barcode / QR Code via scanner atau manual barcode.\n\n";

        $context .= "2. DAFTAR DEWAN GURU YANG BELUM ABSEN HARI INI ({$guruBelumAbsenCount} orang):\n";
        $context .= implode("\n", $listBelumAbsen) . "\n\n";

        $context .= "3. DAFTAR DEWAN GURU YANG SUDAH ABSEN HARI INI ({$totalSudahAbsen} orang):\n";
        $context .= implode("\n", $listSudahAbsen) . "\n\n";

        $context .= "4. DAFTAR LENGKAP DEWAN GURU & TUGAS MENGAJAR:\n";
        $context .= implode("\n", $guruList) . "\n\n";

        $context .= "5. DAFTAR JADWAL MENGAJAR GURU (KBM SEKOLAH):\n";
        $context .= implode("\n", $jadwalList) . "\n\n";

        $context .= "6. DAFTAR MATA PELAJARAN (KURIKULUM SMK):\n";
        $context .= implode("\n", $mapelList) . "\n\n";

        $context .= "7. FITUR & MENU PADA DASHBOARD KEPALA SEKOLAH:\n";
        $context .= "- Dashboard Utama: Halaman eksekutif monitoring kehadiran guru hari ini dengan kartu KPI, persentase kehadiran, 6 status detail, grafik donat distribusi status guru, dan feed log kehadiran dewan guru secara real-time.\n";
        $context .= "- Menu Data Guru: Menampilkan direktori seluruh dewan guru beserta NIP, status, dan rincian tugas mengajar.\n";
        $context .= "- Menu Rekap Absensi Guru: Laporan dan rekapitulasi kehadiran dewan guru per periode tanggal dan status kehadiran.\n";
        $context .= "- QR Barcode Saya: Tombol di hero dashboard dan kartu di halaman profil untuk menampilkan barcode/QR code presensi pribadi Kepala Sekolah.\n";
        $context .= "- Profil & Pengaturan Tema: Mengubah kata sandi/profil dan beralih antara Mode Terang / Gelap (Light / Dark Mode).\n\n";

        $context .= "8. BATASAN AKSES & PRIVASI SISTEM KEPALA SEKOLAH (SANGAT KETAT):\n";
        $context .= "- Dashboard Kepala Sekolah DIBATASI KHUSUS untuk monitoring dewan guru saja.\n";
        $context .= "- Data siswa, jumlah siswa, kelas rombel siswa, dan rekapitulasi absensi siswa TIDAK DITAMPILKAN di Dashboard Kepala Sekolah.\n";
        $context .= "- Jika ada pertanyaan seputar siswa atau absensi siswa, jawab secara santun bahwa hal tersebut berada di luar cakupan Dashboard Kepala Sekolah, dan merupakan wewenang Administrator serta Wali Kelas.\n";

        return $context;
    }

    /**
     * Membaca seluruh informasi database & fitur sistem secara real-time
     * Dengan isolasi data dan privasi jadwal berbasis peran (Role-Based Schedule Isolation).
     */
    private function getDatabaseContext($userQuery, $role = 'pengguna', $user_id = null, $username = 'Tamu') {
        try {
            $db = new Database();

            // Khusus Kepala Sekolah: Isolasi ketat HANYA data dan fitur yang ada di Dashboard Kepala Sekolah (Data guru, absensi guru, KBM)
            if ($role === 'kepala_sekolah') {
                return $this->getKepalaSekolahDatabaseContext($db, $username, $userQuery);
            }

            // 1. Data Guru (Daftar singkat dewan guru)
            $db->query("SELECT g.nama, g.nip, g.mata_pelajaran, COALESCE(u.role, 'guru') as role 
                        FROM guru g 
                        LEFT JOIN users u ON g.user_id = u.id");
            $gurus = $db->resultSet();

            // 2. Data Kelas & Wali Kelas
            $db->query("SELECT k.nama_kelas, k.jurusan, k.tingkat, COALESCE(g.nama, '-') AS wali_kelas, COUNT(s.id) AS total_siswa
                        FROM kelas k
                        LEFT JOIN guru g ON k.wali_kelas_id = g.id
                        LEFT JOIN siswa s ON k.id = s.kelas_id
                        GROUP BY k.id
                        ORDER BY k.id ASC");
            $kelases = $db->resultSet();

            // 3. Totals
            $db->query("SELECT 
                        (SELECT COUNT(*) FROM siswa) AS total_siswa,
                        (SELECT COUNT(*) FROM guru) AS total_guru,
                        (SELECT COUNT(*) FROM kelas) AS total_kelas,
                        (SELECT COUNT(*) FROM mata_pelajaran WHERE status = 'active') AS total_mapel,
                        (SELECT COUNT(*) FROM jadwal_mengajar WHERE status = 'active') AS total_jadwal");
            $totals = $db->single();

            // 4. Data Master Mata Pelajaran (Kurikulum SMK)
            $db->query("SELECT kode_mapel, nama_mapel, kelompok, tingkat, jam_per_minggu 
                        FROM mata_pelajaran 
                        WHERE status = 'active'
                        ORDER BY FIELD(kelompok, 'Muatan Nasional', 'Muatan Kewilayahan', 'Muatan Peminatan Kejuruan', 'Muatan Lokal'), tingkat, nama_mapel ASC");
            $allMapels = $db->resultSet();

            // 5. Data Jadwal Mengajar (ISOLASI KETAT SESUAI ROLE & IDENTITAS LOGIN)
            $jadwalTitle = "4. DAFTAR JADWAL MENGAJAR:";
            $jadwalList = [];

            if (in_array($role, ['guru', 'wali_kelas'])) {
                // Cari profil guru dan id guru pengguna yang sedang login
                $guruProfile = null;
                if ($user_id) {
                    $db->query("SELECT id, nama, nip, mata_pelajaran FROM guru WHERE user_id = :uid LIMIT 1");
                    $db->bind('uid', $user_id);
                    $guruProfile = $db->single();
                }

                $guruId = $guruProfile['id'] ?? null;
                $guruNama = $guruProfile['nama'] ?? $username;

                if ($guruId) {
                    $db->query("SELECT jm.hari, jm.jam_mulai, jm.jam_selesai, jm.ruangan, 
                                       k.nama_kelas, mp.kode_mapel, mp.nama_mapel
                                FROM jadwal_mengajar jm
                                JOIN kelas k ON jm.kelas_id = k.id
                                LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                                WHERE jm.status = 'active' AND jm.guru_id = :guru_id
                                ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC");
                    $db->bind('guru_id', $guruId);
                    $myJadwals = $db->resultSet();

                    $jadwalTitle = "4. JADWAL MENGAJAR PRIBADI MILIK BAPAK/IBU {$guruNama}:\n(PERINGATAN PRIVASI: Ini adalah SATU-SATUNYA jadwal mengajar yang boleh Anda berikan kepada guru ini. Jadwal guru lain TIDAK DISEDIAKAN dan DILARANG dibagikan demi privasi dewan guru!)";
                    if (!empty($myJadwals)) {
                        $jadwalList = array_map(function($j) {
                            $jam = date('H:i', strtotime($j['jam_mulai'])) . ' - ' . date('H:i', strtotime($j['jam_selesai']));
                            return "- {$j['hari']} ($jam WIB) | Mapel: {$j['nama_mapel']} ({$j['kode_mapel']}) | Kelas: {$j['nama_kelas']} | Ruangan: {$j['ruangan']}";
                        }, $myJadwals);
                    } else {
                        $jadwalList[] = "- Anda tidak memiliki jadwal mengajar aktif di sistem saat ini.";
                    }
                } else {
                    $jadwalTitle = "4. JADWAL MENGAJAR PRIBADI GURU INI:";
                    $jadwalList[] = "- Data guru belum terhubung dengan jadwal mengajar.";
                }
            } elseif ($role === 'siswa') {
                // Cari kelas siswa
                $siswaProfile = null;
                if ($user_id) {
                    $db->query("SELECT s.id, s.nama, s.kelas_id, k.nama_kelas 
                                FROM siswa s 
                                LEFT JOIN kelas k ON s.kelas_id = k.id 
                                WHERE s.user_id = :uid LIMIT 1");
                    $db->bind('uid', $user_id);
                    $siswaProfile = $db->single();
                }

                $kelasId = $siswaProfile['kelas_id'] ?? null;
                $namaKelas = $siswaProfile['nama_kelas'] ?? 'Kelas Anda';

                if ($kelasId) {
                    $db->query("SELECT jm.hari, jm.jam_mulai, jm.jam_selesai, jm.ruangan, 
                                       g.nama as nama_guru, k.nama_kelas, mp.kode_mapel, mp.nama_mapel
                                FROM jadwal_mengajar jm
                                JOIN guru g ON jm.guru_id = g.id
                                JOIN kelas k ON jm.kelas_id = k.id
                                LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                                WHERE jm.status = 'active' AND jm.kelas_id = :kid
                                ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC");
                    $db->bind('kid', $kelasId);
                    $classJadwals = $db->resultSet();

                    $jadwalTitle = "4. JADWAL PELAJARAN UNTUK KELAS ANDA ({$namaKelas}):\n(PENTING: Siswa hanya dapat melihat jadwal pelajaran untuk kelasnya sendiri!)";
                    if (!empty($classJadwals)) {
                        $jadwalList = array_map(function($j) {
                            $jam = date('H:i', strtotime($j['jam_mulai'])) . ' - ' . date('H:i', strtotime($j['jam_selesai']));
                            return "- {$j['hari']} ($jam WIB) | Mapel: {$j['nama_mapel']} ({$j['kode_mapel']}) | Guru Pengajar: {$j['nama_guru']} | Ruangan: {$j['ruangan']}";
                        }, $classJadwals);
                    } else {
                        $jadwalList[] = "- Belum ada jadwal pelajaran terdaftar untuk kelas {$namaKelas}.";
                    }
                } else {
                    $jadwalTitle = "4. JADWAL PELAJARAN SISWA:";
                    $jadwalList[] = "- Akun Anda belum terdaftar dalam kelas.";
                }
            } elseif ($role === 'admin') {
                // Admin dapat melihat seluruh jadwal sekolah
                $db->query("SELECT jm.hari, jm.jam_mulai, jm.jam_selesai, jm.ruangan, 
                                   g.nama as nama_guru, k.nama_kelas, mp.kode_mapel, mp.nama_mapel
                            FROM jadwal_mengajar jm
                            JOIN guru g ON jm.guru_id = g.id
                            JOIN kelas k ON jm.kelas_id = k.id
                            LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                            WHERE jm.status = 'active'
                            ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC");
                $allJadwals = $db->resultSet();

                $jadwalTitle = "4. DAFTAR JADWAL MENGAJAR SELURUH GURU & KELAS (KBM SEKOLAH):";
                $jadwalList = array_map(function($j) {
                    $jam = date('H:i', strtotime($j['jam_mulai'])) . ' - ' . date('H:i', strtotime($j['jam_selesai']));
                    return "- {$j['hari']} ($jam WIB) | Guru: {$j['nama_guru']} | Mapel: {$j['nama_mapel']} ({$j['kode_mapel']}) | Kelas: {$j['nama_kelas']} | Ruangan: {$j['ruangan']}";
                }, $allJadwals);
            } else {
                $jadwalTitle = "4. INFORMASI JADWAL MENGAJAR:";
                $jadwalList[] = "- Silakan login untuk melihat jadwal mengajar atau jadwal pelajaran sesuai peran akun Anda.";
            }

            // 6. Absensi hari ini (DIBATASI SESUAI ROLE & PRIVASI)
            $rekapSiswa = "Informasi kehadiran siswa hanya dapat diakses oleh Wali Kelas dan Administrator.";
            if ($role === 'admin') {
                $db->query("SELECT status, COUNT(*) AS count FROM absensi_siswa WHERE tanggal = CURDATE() GROUP BY status");
                $todaySiswa = $db->resultSet();
                $rekapSiswa = !empty($todaySiswa) ? implode(', ', array_map(fn($s) => "{$s['status']}: {$s['count']}", $todaySiswa)) : 'Belum ada absensi siswa hari ini';
            } elseif ($role === 'wali_kelas' && $user_id) {
                $db->query("SELECT absensi_siswa.status, COUNT(absensi_siswa.id) AS count 
                            FROM absensi_siswa 
                            JOIN siswa ON absensi_siswa.siswa_id = siswa.id 
                            JOIN kelas ON siswa.kelas_id = kelas.id 
                            JOIN guru ON kelas.wali_kelas_id = guru.id 
                            WHERE guru.user_id = :uid AND absensi_siswa.tanggal = CURDATE() 
                            GROUP BY absensi_siswa.status");
                $db->bind('uid', $user_id);
                $waliTodaySiswa = $db->resultSet();
                $rekapSiswa = !empty($waliTodaySiswa) ? "Kehadiran kelas perwalian Anda hari ini: " . implode(', ', array_map(fn($s) => "{$s['status']}: {$s['count']}", $waliTodaySiswa)) : 'Belum ada siswa kelas Anda yang absen hari ini';
            }

            $db->query("SELECT status, COUNT(*) AS count FROM absensi_guru WHERE tanggal = CURDATE() GROUP BY status");
            $todayGuru = $db->resultSet();
            $rekapGuru  = !empty($todayGuru) ? implode(', ', array_map(fn($g) => "{$g['status']}: {$g['count']}", $todayGuru)) : 'Belum ada absensi guru hari ini';

            // 7. Pencarian siswa spesifik (HANYA UNTUK ADMIN ATAU WALI KELAS UNTUK KELASNYA)
            $studentMatches = [];
            if (in_array($role, ['admin', 'wali_kelas'])) {
                $cleanQuery = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $userQuery);
                $ignore = ['siapa', 'berapa', 'kelas', 'siswa', 'guru', 'absen', 'absensi', 'apakah', 'tentang', 'ada', 'yang', 'hari', 'ini', 'sekolah', 'info', 'tolong'];
                $words = array_filter(explode(' ', $cleanQuery), function($w) use ($ignore) {
                    $w = strtolower(trim($w));
                    return strlen($w) >= 3 && !in_array($w, $ignore);
                });

                if (!empty($words)) {
                    $clauses = [];
                    $bindings = [];
                    $idx = 0;
                    foreach ($words as $word) {
                        $clauses[] = "(s.nama LIKE :w{$idx}a OR s.nis LIKE :w{$idx}b OR k.nama_kelas LIKE :w{$idx}c)";
                        $bindings[":w{$idx}a"] = "%$word%";
                        $bindings[":w{$idx}b"] = "%$word%";
                        $bindings[":w{$idx}c"] = "%$word%";
                        $idx++;
                    }

                    $extraWhere = "";
                    if ($role === 'wali_kelas' && $user_id) {
                        $extraWhere = " AND k.wali_kelas_id = (SELECT id FROM guru WHERE user_id = :wali_uid LIMIT 1) ";
                        $bindings[':wali_uid'] = $user_id;
                    }

                    $sql = "SELECT s.nis, s.nama, k.nama_kelas, s.status 
                            FROM siswa s 
                            LEFT JOIN kelas k ON s.kelas_id = k.id 
                            WHERE (" . implode(' OR ', $clauses) . ") {$extraWhere} LIMIT 15";
                    $db->query($sql);
                    foreach ($bindings as $k => $v) {
                        $db->bind($k, $v);
                    }
                    $studentMatches = $db->resultSet();
                }
            }

            $guruList = [];
            $noG = 1;
            foreach ($gurus as $g) {
                $nip = !empty($g['nip']) ? " (NIP: {$g['nip']})" : '';
                $mapel = !empty($g['mata_pelajaran']) ? " - Mapel/Tugas: {$g['mata_pelajaran']}" : '';
                $guruList[] = "{$noG}. {$g['nama']}{$nip}{$mapel}";
                $noG++;
            }

            $kelasList = [];
            $noK = 1;
            foreach ($kelases as $k) {
                $kelasList[] = "{$noK}. {$k['nama_kelas']} - Jurusan {$k['jurusan']}, Tingkat {$k['tingkat']}, Wali Kelas: {$k['wali_kelas']} ({$k['total_siswa']} Siswa)";
                $noK++;
            }

            $mapelList = [];
            $noM = 1;
            foreach ($allMapels as $m) {
                $mapelList[] = "{$noM}. [{$m['kode_mapel']}] {$m['nama_mapel']} ({$m['kelompok']}, Tingkat {$m['tingkat']}, {$m['jam_per_minggu']} JP/minggu)";
                $noM++;
            }

            $context = "INFORMASI RESMI DATABASE SISTEM & FITUR SEKOLAH:\n";
            $context .= "1. STATISTIK SISTEM:\n";
            $context .= "- Total Siswa Terdaftar: {$totals['total_siswa']} siswa\n";
            $context .= "- Total Guru & Staff: {$totals['total_guru']} orang\n";
            $context .= "- Total Kelas: {$totals['total_kelas']} kelas\n";
            $context .= "- Total Mata Pelajaran: " . ($totals['total_mapel'] ?? count($allMapels)) . " mapel\n";
            $context .= "- Total Jadwal Mengajar: " . ($totals['total_jadwal'] ?? 0) . " sesi mengajar aktif\n";
            $context .= "- Aturan Jam Masuk Siswa: Maksimal pukul " . JAM_MASUK_NORMAL . " WIB (lewat jam ini tercatat 'terlambat')\n";
            $context .= "- Aturan Jam Masuk Guru: Maksimal pukul " . JAM_MASUK_GURU . " WIB (lewat jam ini tercatat 'terlambat')\n";
            $context .= "- Metode Absensi: Barcode/QR Code via kamera web/HP atau input barcode manual, serta RFID untuk siswa.\n";
            $context .= "- Status Kehadiran Sistem: hadir, terlambat, izin, sakit, alpa.\n\n";

            $context .= "2. FITUR SISTEM TERBARU:\n";
            $context .= "- Pengaturan Akun: Tersedia di menu dropdown profil kanan atas (di samping Mode Tampilan) (/admin/pengaturan) untuk Admin. Bisa mengubah identitas (Nama Lengkap, Username login), mengganti Foto Profil (JPG/PNG/WEBP max 2MB), dan mengganti Password (kata sandi) dengan verifikasi password lama, validasi minimal 6 karakter, dan tombol lihat/sembunyikan password. Halaman ini juga memiliki monitoring daftar akun semua pengguna sistem. Role lain (Guru, Siswa, Wali Kelas, Kepala Sekolah) dapat mengedit profil melalui menu 'Profil' masing-masing.\n";
            $context .= "- Mode Terang & Gelap (Light / Dark Mode): Pengguna dapat beralih antara tampilan Terang dan Gelap kapan saja menggunakan tombol toggle di pojok kanan atas navbar dashboard (ikon matahari / bulan) atau melalui tab Pengaturan Akun. Tema tersimpan otomatis di peramban (localStorage) secara permanen.\n";
            $context .= "- Manajemen Mata Pelajaran: Tersedia di menu sidebar 'Mata Pelajaran' (/admin_mapel) untuk Admin. Memungkinkan kelola master data mapel kurikulum SMK (Muatan Nasional, Muatan Kewilayahan, Muatan Peminatan Kejuruan C3 seperti TKJ & TBSM, dan Muatan Lokal), lengkap dengan kode mapel, tingkat kelas, alokasi jam/minggu (JP), dan status.\n";
            $context .= "- Jadwal Mengajar Guru: Tersedia di menu sidebar 'Jadwal Mengajar' (/admin_jadwal_mengajar) untuk Admin. Mengatur Kegiatan Belajar Mengajar (KBM) guru per hari (Senin s/d Sabtu), jam mulai - selesai, kelas, dan ruangan. Dilengkapi sistem cerdas deteksi dan pencegahan jadwal bentrok guru maupun bentrok kelas (collision prevention), serta menyediakan tampilan Tabel dan Grid Mingguan.\n";
            $context .= "- Pengajuan Izin & Sakit: Siswa dan Guru dapat langsung mengajukan surat atau keterangan izin/sakit mandiri dari dashboard masing-masing. Setelah absen atau izin terkirim, muncul notifikasi berhasil (SweetAlert).\n";
            $context .= "- Penamaan Kelas: Format kelas terdaftar lengkap per jurusan seperti X TKJ 1, X TKJ 2, XI TKJ 1, XII TBSM 1, dst.\n\n";

            $context .= "3. DAFTAR MATA PELAJARAN (KURIKULUM SMK):\n" . implode("\n", $mapelList) . "\n\n";
            $context .= "{$jadwalTitle}\n" . implode("\n", $jadwalList) . "\n\n";
            $context .= "5. DAFTAR GURU & STAFF:\n" . implode("\n", $guruList) . "\n\n";
            $context .= "6. DAFTAR KELAS & WALI KELAS:\n" . implode("\n", $kelasList) . "\n\n";
            $context .= "7. REKAP KEHADIRAN HARI INI:\n- Siswa hari ini: $rekapSiswa\n- Guru hari ini: $rekapGuru\n";

            if (!empty($studentMatches)) {
                $context .= "\n8. DATA PENCARIAN SISWA RELEVAN:\n";
                foreach ($studentMatches as $sm) {
                    $context .= "- NIS: {$sm['nis']}, Nama: {$sm['nama']}, Kelas: {$sm['nama_kelas']}, Status: {$sm['status']}\n";
                }
            }

            return $context;
        } catch (Throwable $e) {
            return "Catatan: Data database saat ini sedang offline atau dalam pemeliharaan.";
        }
    }

    /**
     * Membangun system prompt dengan seluruh informasi database sistem
     */
    private function buildSystemPrompt($role, $username, $user_id, $userQuery) {
        $role_label = ucfirst(str_replace('_', ' ', $role));
        $dbContext = $this->getDatabaseContext($userQuery, $role, $user_id, $username);

        $roleSpecificRules = "";
        if ($role === 'guru') {
            $roleSpecificRules = "
ATURAN KHUSUS UNTUK GURU ({$username}):
1. Pengguna yang sedang berbicara dengan Anda adalah BAPAK/IBU GURU bernama {$username}.
2. Sapalah dengan ramah, santun, dan sebut namanya dengan penuh hormat (contoh: 'Halo Bapak/Ibu {$username}!').
3. JADWAL MENGAJAR PRIBADI:
   - Jika guru ini menanyakan jadwal mengajar (contoh: 'jadwal hari selasa', 'jadwal saya', 'kapan saya mengajar', 'jadwal mengajar hari rabu', dll), BACA & BERIKAN HANYA jadwal mengajar pribadi milik {$username} yang tertera pada bagian nomor 4 di atas!
   - JANGAN PERNAH menyertakan, membocorkan, atau menyebutkan jadwal mengajar guru lain (seperti jadwal Siti Aminah, Rina Marlina, benjamin, dll).
   - Jika pada hari yang ditanyakan {$username} hanya memiliki 1 sesi jadwal mengajar (atau beberapa), sebutkan HANYA sesi milik {$username} tersebut secara jelas dan akurat (jam, mapel, kelas, dan ruangan).
   - Jika pada hari yang ditanyakan {$username} tidak memiliki jadwal mengajar, jawab dengan jelas dan santun bahwa Bapak/Ibu {$username} tidak memiliki jadwal mengajar pada hari tersebut.
4. PRIVASI GURU LAIN:
   - Jika guru ini secara spesifik menanyakan jadwal mengajar guru lain, tolak dengan santun: 'Maaf Bapak/Ibu, demi menjaga privasi dewan guru, asisten hanya dapat memberikan informasi jadwal mengajar pribadi Anda sendiri.'
5. PRIVASI KEHADIRAN SISWA:
   - Data kehadiran dan absensi siswa sekolah adalah hak akses Wali Kelas dan Admin. Guru biasa tidak memiliki akses ke rekapitulasi kehadiran seluruh siswa.";
        } elseif ($role === 'wali_kelas') {
            $roleSpecificRules = "
ATURAN KHUSUS UNTUK WALI KELAS ({$username}):
1. Pengguna adalah WALI KELAS bernama {$username}.
2. JADWAL MENGAJAR: HANYA berikan jadwal mengajar pribadi milik {$username}. Jangan berikan jadwal guru lain.
3. DATA SISWA & ABSENSI: Wali kelas hanya memiliki wewenang untuk siswa di kelas perwaliannya.";
        } elseif ($role === 'siswa') {
            $roleSpecificRules = "
ATURAN KHUSUS UNTUK SISWA ({$username}):
1. Pengguna adalah SISWA bernama {$username}.
2. JADWAL PELAJARAN: Berikan HANYA jadwal mata pelajaran untuk kelas siswa ini. Jangan berikan jadwal kelas lain.
3. Berikan jawaban yang ramah, memotivasi, dan penuh semangat belajar.";
        } elseif ($role === 'admin') {
            $roleSpecificRules = "
ATURAN KHUSUS ADMINISTRATOR:
1. Pengguna adalah Administrator dengan hak akses penuh untuk mengelola dan memantau jadwal semua guru, siswa, kelas, mata pelajaran, dan absensi di seluruh sekolah.";
        } elseif ($role === 'kepala_sekolah') {
            $roleSpecificRules = "
ATURAN KHUSUS KEPALA SEKOLAH ({$username}):
1. Pengguna yang sedang bertanya adalah BAPAK/IBU KEPALA SEKOLAH bernama {$username}. Sapalah dengan sangat hormat, sopan, dan hangat (contoh: 'Selamat siang/pagi, Bapak/Ibu {$username}').
2. BATASAN CAKUPAN JAWABAN KETAT (HANYA INFORMASI PADA DASHBOARD KEPALA SEKOLAH):
   - Anda HANYA diperbolehkan menjawab pertanyaan dan memberikan informasi yang terdapat pada Dashboard dan Hak Akses Kepala Sekolah:
     * Monitoring presensi dewan guru hari ini: persentase kehadiran, guru hadir, guru terlambat, guru izin, guru sakit, guru alpa, dan daftar nama dewan guru yang belum absen hari ini.
     * Rekapitulasi absensi dewan guru dan laporan kehadiran guru.
     * Direktori dewan guru (NIP, mata pelajaran yang diampu, peran tugas mengajar).
     * Jadwal Kegiatan Belajar Mengajar (KBM) dewan guru.
     * Fitur Dashboard Kepala Sekolah, Profil Kepala Sekolah, tombol QR code presensi kepala sekolah, dan mode tampilan (terang/gelap).
   - DILARANG KERAS MEMBERIKAN INFORMASI DATA SISWA, JUMLAH SISWA, MAUPUN REKAPITULASI ABSENSI SISWA KARENA TIDAK ADA DI DASHBOARD KEPALA SEKOLAH!
3. JAWABAN PERTANYAAN ABSENSI (Contoh: 'siapa yang belum absen hari ini', 'bagaimana presensi hari ini', 'siapa yang terlambat', dll):
   - Anda HANYA MENJAWAB TENTANG DEWAN GURU!
   - Sebutkan nama-nama dewan guru yang belum absen hari ini berdasarkan data 'DAFTAR DEWAN GURU YANG BELUM ABSEN HARI INI' di atas.
   - JANGAN PERNAH menyebutkan atau menyertakan kata 'siswa' atau data absensi siswa sama sekali!
4. JIKA KEPALA SEKOLAH MENANYAKAN TENTANG SISWA ATAU ABSENSI SISWA:
   - Tolak secara santun dan jelaskan batas wewenang:
     'Mohon maaf Bapak/Ibu Kepala Sekolah, sesuai kebijakan hak akses sistem dan cakupan Dashboard Kepala Sekolah, akun Kepala Sekolah difokuskan khusus untuk monitoring dewan guru dan tidak memiliki akses ke data maupun rekapitulasi absensi siswa. Informasi absensi siswa dapat diakses oleh Administrator dan Wali Kelas masing-masing.'";
        }

        $waktuSekarang = DateHelper::jamSekarang('H:i:s') . ' WIB';
        $tanggalSekarang = DateHelper::tanggalIndo(date('Y-m-d'), true);
        $hariIni = DateHelper::hariIni();

        if ($role === 'kepala_sekolah') {
            $mainRules = "ATURAN PALING UTAMA & KETAT KHUSUS KEPALA SEKOLAH:
1. BACA & GUNAKAN SELURUH DATA DATABASE PADA DASHBOARD KEPALA SEKOLAH DI ATAS untuk menjawab pertanyaan seputar:
   - Data & monitoring presensi dewan guru hari ini: persentase kehadiran, status kehadiran guru, daftar nama dewan guru yang belum absen atau hadir, dan rekapitulasi kehadiran dewan guru.
   - Direktori dewan guru: NIP, tugas mengajar, dan mata pelajaran kurikulum SMK.
   - Jadwal Kegiatan Belajar Mengajar (KBM) guru di sekolah.
   - Fitur Dashboard Kepala Sekolah, QR Code presensi kepala sekolah, dan mode tampilan (terang/gelap).
   Jawablah secara faktual, penuh hormat, dan akurat berdasarkan data di atas.
2. DILARANG KERAS MENYEBUTKAN ATAU MEMBAHAS DATA SISWA & REKAP ABSENSI SISWA!
   Data siswa dan absensi siswa tidak ada di Dashboard Kepala Sekolah. Jika Kepala Sekolah bertanya mengenai siswa atau absensi siswa, jawab dengan santun:
   'Mohon maaf Bapak/Ibu Kepala Sekolah, sesuai kebijakan hak akses dan cakupan Dashboard Kepala Sekolah, akun Kepala Sekolah difokuskan khusus untuk monitoring dewan guru dan tidak memiliki akses ke data maupun rekapitulasi absensi siswa. Informasi absensi siswa dapat diakses oleh Administrator dan Wali Kelas masing-masing.'
3. DILARANG KERAS MENJAWAB PERTANYAAN DI LUAR SISTEM SEKOLAH!
   Jika ditanya di luar topik sekolah/monitoring guru, tolak secara santun:
   'Mohon maaf Bapak/Ibu Kepala Sekolah, saya adalah asisten khusus Dashboard Kepala Sekolah. Saya hanya dapat menjawab pertanyaan seputar monitoring presensi dewan guru, data guru, jadwal KBM, dan fitur pada Dashboard Kepala Sekolah.'
4. GAYA FORMAT & PENYAJIAN JAWABAN (WAJIB DIIKUTI DENGAN SANGAT KETAT):
   - JIKA MENAMPILKAN DAFTAR ATAU KUMPULAN DATA (seperti daftar nama dewan guru, guru yang belum absen, jadwal KBM, mata pelajaran, dll):
     WAJIB MENYAJIKAN DATA SECARA RAPI, BERSIH, TERSTRUKTUR, DAN MENGGUNAKAN NOMOR URUT (1, 2, 3, dst.) DI MANA SETIAP NOMOR TERCANTUM PADA BARIS BARU TERSENDIRI!
     Contoh format:
     Berikut adalah daftar 30 Dewan Guru SMK:
     1. Drs. Budi Santoso - Kepala Sekolah
     2. Siti Aminah, S.Pd - Matematika
     3. Andi Hermawan, M.Kom - Pemrograman Dasar
     ...
     30. Yuni Astuti, S.Pd, Kons - Bimbingan Konseling (BK)
     DILARANG KERAS menyajikan daftar nama atau data dalam bentuk satu paragraf panjang yang hanya dipisahkan dengan tanda koma!
   - Untuk pertanyaan berupa penjelasan singkat cara pemakaian sistem atau jawaban non-daftar:
     Jawablah dengan sangat hormat, santun, hangat, dan mengalir (1-3 kalimat yang padat dan jelas).";
        } else {
            $mainRules = "ATURAN PALING UTAMA & KETAT:
1. BACA & GUNAKAN SELURUH DATA DATABASE & FITUR DI ATAS untuk menjawab pertanyaan seputar sekolah:
   - Data & Statistik: jumlah siswa, guru, kelas, mata pelajaran, jadwal mengajar, wali kelas, jam masuk, rekap absensi.
   - Mata Pelajaran: daftar mapel, kelompok muatan (Nasional, Kewilayahan, Kejuruan, Mulok), kode mapel, alokasi jam/minggu.
   - Jadwal Mengajar: jadwal mengajar per hari, jam KBM, kelas, ruangan.
   - Pengaturan Akun: cara ubah nama, username, ganti foto profil, ganti kata sandi (password), dan monitoring akun.
   - Mode Terang / Gelap: cara ganti tema lewat tombol toggle di pojok kanan atas navbar atau di pengaturan akun.
   - Pengajuan Izin & Sakit: cara siswa & guru mengajukan izin atau sakit di dashboard masing-masing.
   Jawablah secara faktual, ramah, dan akurat berdasarkan data di atas.
2. DILARANG KERAS MENJAWAB PERTANYAAN DI LUAR SISTEM INI!
   Jika pengguna menanyakan topik di luar sistem sekolah/absensi ini (contoh: coding/skrip pemrograman di luar aplikasi, resep masakan, politik, teknologi luar, sejarah umum, selebriti, rumus matematika/fisika umum, cuaca luar, hiburan, game, atau topik umum lainnya):
   KAMU WAJIB MENOLAK SECARA TEGAS DAN SANTUN:
   \"Maaf ya kak, saya adalah asisten khusus Sistem Absensi & Informasi SMK ini. Saya hanya dapat menjawab pertanyaan seputar data sistem sekolah, kelas, guru, siswa, jadwal mengajar, mata pelajaran, dan absensi di sistem ini.\"
3. GAYA FORMAT & PENYAJIAN JAWABAN (WAJIB DIIKUTI DENGAN SANGAT KETAT):
   - FORMAT DAFTAR BERURUTAN (NUMBERED LIST):
     Jika pengguna meminta daftar, menyebutkan nama-nama, atau menanyakan kumpulan data (contoh: 'daftar guru', 'siapa saja guru', 'sebutkan nama guru', 'daftar mata pelajaran', 'daftar kelas', 'jadwal mengajar', 'siswa', dll):
     KAMU WAJIB MENYAJIKANNYA SECARA RAPI, BERSIH, TERSTRUKTUR, DAN MENGGUNAKAN NOMOR URUT (1, 2, 3, dst.) DI MANA SETIAP NOMOR DITULIS PADA BARIS BARU TERSENDIRI!
     Contoh format yang WAJIB digunakan:
     Berikut adalah daftar 30 Dewan Guru SMK:
     1. Drs. Budi Santoso - Kepala Sekolah
     2. Siti Aminah, S.Pd - Matematika
     3. Andi Hermawan, M.Kom - Pemrograman Dasar
     ...
     30. Yuni Astuti, S.Pd, Kons - Bimbingan Konseling (BK)

     DILARANG KERAS menyajikan daftar nama atau data dalam bentuk satu paragraf panjang yang digabung dengan tanda koma!
   - Untuk pertanyaan berupa penjelasan atau konfirmasi singkat: Jawablah dengan ramah, santun, dan padat (1-3 kalimat).";
        }

        return "Kamu adalah asisten resmi Sistem Informasi & Absensi SMK yang ramah, sopan, dan hangat layaknya staf sekolah manusia.

Nama pengguna saat ini: {$username} ({$role_label}).
Waktu & Tanggal Sistem Saat Ini: {$tanggalSekarang}, Pukul {$waktuSekarang} (Hari: {$hariIni}).

{$dbContext}

{$roleSpecificRules}

{$mainRules}";
    }

    /**
     * Panggil AI API menggunakan Multi-Provider Adapter (Gemini / OpenAI / Custom)
     */
    private function panggilAiAPI($system_prompt, $message) {
        $adapter = AiProviderFactory::getAdapter();

        // Riwayat konteks percakapan singkat sesi saat ini (maks 4 putaran terakhir)
        $history = $_SESSION['chatbot_history'] ?? [];
        if (!is_array($history)) {
            $history = [];
        }

        $dbCfg = ChatbotHelper::getActiveDbConfig();
        $options = [
            'apiKey'      => $dbCfg['api_key'] ?? (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : ''),
            'model'       => $dbCfg['model'] ?? (defined('GEMINI_MODEL') ? GEMINI_MODEL : ''),
            'temperature' => isset($dbCfg['temperature']) ? (float)$dbCfg['temperature'] : 0.2,
            'maxTokens'   => isset($dbCfg['max_tokens']) ? (int)$dbCfg['max_tokens'] : 1200,
            'baseUrl'     => $dbCfg['base_url'] ?? ''
        ];

        $res = $adapter->chat($system_prompt, $message, $history, $options);
        if (!empty($res['success']) && !empty($res['message'])) {
            $botReply = $res['message'];
            // Simpan riwayat interaksi untuk multi-turn chat memory
            $history[] = ['role' => 'user', 'text' => $message];
            $history[] = ['role' => 'assistant', 'text' => $botReply];
            if (count($history) > 8) {
                $history = array_slice($history, -8);
            }
            $_SESSION['chatbot_history'] = $history;
            return $botReply;
        }

        // Jika adapter gagal namun provider adalah gemini, coba panggil fallback lokal
        if (ChatbotHelper::getProvider() === 'gemini') {
            return $this->panggilGeminiAPI($system_prompt, $message);
        }

        return null;
    }

    /**
     * Panggil Gemini API dengan curl / stream context dan fallback model berkecepatan tinggi
     */
    private function panggilGeminiAPI($system_prompt, $message) {
        $urls = [
            GEMINI_API_URL . '?key=' . GEMINI_API_KEY,
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key=' . GEMINI_API_KEY,
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key=' . GEMINI_API_KEY,
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent?key=' . GEMINI_API_KEY
        ];

        $payload = json_encode([
            'systemInstruction' => [
                'parts' => [['text' => $system_prompt]]
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $message]]
                ]
            ],
            'generationConfig' => [
                'temperature'     => 0.2,
                'maxOutputTokens' => 1200,
                'topP'            => 0.85,
            ]
        ]);

        foreach ($urls as $url) {
            if (!$url) continue;

            $response = false;
            $httpCode = 0;

            // 1. Coba cURL jika extension aktif (timeout agresif 3.5 detik untuk kecepatan respons)
            if (function_exists('curl_init')) {
                try {
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 3.5);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                } catch (\Throwable $e) {
                    $response = false;
                }
            }

            // 2. Fallback via file_get_contents (PHP stream context) jika cURL tidak aktif/gagal
            if ($response === false) {
                try {
                    $streamCtx = stream_context_create([
                        'http' => [
                            'method'  => 'POST',
                            'header'  => "Content-Type: application/json\r\n",
                            'content' => $payload,
                            'timeout' => 3.5,
                            'ignore_errors' => true
                        ],
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false
                        ]
                    ]);
                    $response = @file_get_contents($url, false, $streamCtx);
                    if (function_exists('http_get_last_response_headers')) {
                        $responseHeaders = http_get_last_response_headers();
                    } else {
                        $varName = 'http_response_header';
                        $responseHeaders = $$varName ?? [];
                    }
                    if (is_array($responseHeaders)) {
                        foreach ($responseHeaders as $hdr) {
                            if (preg_match('#HTTP/\S+\s+(\d+)#', $hdr, $hm)) {
                                $httpCode = (int)$hm[1];
                                break;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $response = false;
                }
            }

            if ($httpCode === 200 && $response) {
                $json = json_decode($response, true);
                $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    $cleaned = trim($text);
                    $cleaned = str_replace('**', '', $cleaned);
                    return $cleaned;
                }
            }
        }

        return null;
    }

    /**
     * Rule-based FAQ Fallback ramah jika AI offline
     */
    private function getRuleBasedAnswer($q, $role, $username = 'Tamu', $user_id = null) {
        $q = strtolower($q);

        if (strpos($q, 'halo') !== false || strpos($q, 'hai') !== false || strpos($q, 'selamat') !== false) {
            return "Halo {$username}! Senang bisa menyapa Anda. Ada yang bisa saya bantu seputar absensi, jadwal mengajar, mata pelajaran, atau informasi sekolah hari ini?";
        }

        // Pengecekan cerdas jadwal mengajar guru / siswa
        if (strpos($q, 'jadwal') !== false) {
            if (in_array($role, ['guru', 'wali_kelas']) && $user_id) {
                try {
                    $db = new Database();
                    $db->query("SELECT id, nama FROM guru WHERE user_id = :uid LIMIT 1");
                    $db->bind('uid', $user_id);
                    $g = $db->single();
                    if ($g) {
                        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
                        $targetDay = null;
                        foreach ($days as $d) {
                            if (strpos($q, $d) !== false) {
                                $targetDay = ucfirst($d);
                                break;
                            }
                        }

                        if ($targetDay) {
                            $db->query("SELECT jm.jam_mulai, jm.jam_selesai, jm.ruangan, k.nama_kelas, mp.nama_mapel, mp.kode_mapel 
                                        FROM jadwal_mengajar jm 
                                        JOIN kelas k ON jm.kelas_id = k.id 
                                        LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id 
                                        WHERE jm.guru_id = :gid AND jm.hari = :hari AND jm.status = 'active' 
                                        ORDER BY jm.jam_mulai ASC");
                            $db->bind('gid', $g['id']);
                            $db->bind('hari', $targetDay);
                            $jadwals = $db->resultSet();

                            if (!empty($jadwals)) {
                                $lines = [];
                                foreach ($jadwals as $i => $j) {
                                    $jam = date('H:i', strtotime($j['jam_mulai'])) . ' - ' . date('H:i', strtotime($j['jam_selesai']));
                                    $lines[] = ($i + 1) . ". {$jam} WIB: {$j['nama_mapel']} ({$j['kode_mapel']}) di kelas {$j['nama_kelas']} ({$j['ruangan']})";
                                }
                                return "Halo Bapak/Ibu {$g['nama']}! Untuk jadwal mengajar pribadi Anda pada hari {$targetDay}, tercatat ada " . count($jadwals) . " sesi:\n" . implode("\n", $lines);
                            } else {
                                return "Halo Bapak/Ibu {$g['nama']}! Anda tidak memiliki jadwal mengajar pada hari {$targetDay}.";
                            }
                        } else {
                            $db->query("SELECT jm.hari, jm.jam_mulai, jm.jam_selesai, jm.ruangan, k.nama_kelas, mp.nama_mapel, mp.kode_mapel 
                                        FROM jadwal_mengajar jm 
                                        JOIN kelas k ON jm.kelas_id = k.id 
                                        LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id 
                                        WHERE jm.guru_id = :gid AND jm.status = 'active' 
                                        ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC");
                            $db->bind('gid', $g['id']);
                            $allTeacherJadwals = $db->resultSet();

                            if (!empty($allTeacherJadwals)) {
                                $lines = [];
                                foreach ($allTeacherJadwals as $i => $j) {
                                    $jam = date('H:i', strtotime($j['jam_mulai'])) . ' - ' . date('H:i', strtotime($j['jam_selesai']));
                                    $lines[] = "- {$j['hari']} ($jam WIB): {$j['nama_mapel']} | Kelas {$j['nama_kelas']} ({$j['ruangan']})";
                                }
                                return "Halo Bapak/Ibu {$g['nama']}! Berikut ringkasan jadwal mengajar pribadi Anda:\n" . implode("\n", $lines);
                            } else {
                                return "Halo Bapak/Ibu {$g['nama']}! Anda belum memiliki jadwal mengajar aktif di sistem.";
                            }
                        }
                    }
                } catch (Throwable $e) {}
            } elseif ($role === 'siswa' && $user_id) {
                try {
                    $db = new Database();
                    $db->query("SELECT s.id, s.nama, s.kelas_id, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id WHERE s.user_id = :uid LIMIT 1");
                    $db->bind('uid', $user_id);
                    $s = $db->single();
                    if ($s && $s['kelas_id']) {
                        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
                        $targetDay = null;
                        foreach ($days as $d) {
                            if (strpos($q, $d) !== false) {
                                $targetDay = ucfirst($d);
                                break;
                            }
                        }

                        if ($targetDay) {
                            $db->query("SELECT jm.jam_mulai, jm.jam_selesai, jm.ruangan, g.nama as nama_guru, mp.nama_mapel, mp.kode_mapel 
                                        FROM jadwal_mengajar jm 
                                        JOIN guru g ON jm.guru_id = g.id 
                                        LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id 
                                        WHERE jm.kelas_id = :kid AND jm.hari = :hari AND jm.status = 'active' 
                                        ORDER BY jm.jam_mulai ASC");
                            $db->bind('kid', $s['kelas_id']);
                            $db->bind('hari', $targetDay);
                            $jadwals = $db->resultSet();

                            if (!empty($jadwals)) {
                                $lines = [];
                                foreach ($jadwals as $i => $j) {
                                    $jam = date('H:i', strtotime($j['jam_mulai'])) . ' - ' . date('H:i', strtotime($j['jam_selesai']));
                                    $lines[] = ($i + 1) . ". {$jam} WIB: {$j['nama_mapel']} ({$j['kode_mapel']}) - Guru: {$j['nama_guru']} ({$j['ruangan']})";
                                }
                                return "Halo {$s['nama']}! Jadwal pelajaran kelas {$s['nama_kelas']} pada hari {$targetDay}:\n" . implode("\n", $lines);
                            } else {
                                return "Halo {$s['nama']}! Tidak ada jadwal pelajaran untuk kelas {$s['nama_kelas']} pada hari {$targetDay}.";
                            }
                        }
                    }
                } catch (Throwable $e) {}
            }
        }

        if (strpos($q, 'mata pelajaran') !== false || strpos($q, 'mapel') !== false || strpos($q, 'kurikulum') !== false) {
            return "Sistem telah dilengkapi data Mata Pelajaran lengkap untuk kurikulum SMK (Muatan Nasional, Kewilayahan, Kejuruan, dan Mulok). Admin dapat mengelolanya di menu 'Mata Pelajaran' pada sidebar.";
        }

        if (strpos($q, 'jadwal mengajar') !== false || strpos($q, 'jadwal kbm') !== false || strpos($q, 'jadwal guru') !== false || strpos($q, 'jadwal pelajaran') !== false) {
            return "Jadwal mengajar guru dan KBM kelas dapat dilihat dan dikelola melalui menu 'Jadwal Mengajar' di sidebar. Fitur ini dilengkapi pencegahan bentrok otomatis untuk guru dan kelas, serta mode tampilan Grid Mingguan.";
        }

        if (strpos($q, 'pengaturan akun') !== false || strpos($q, 'ganti password') !== false || strpos($q, 'ubah profil') !== false || strpos($q, 'ganti kata sandi') !== false) {
            return "Untuk mengubah identitas akun, mengganti kata sandi (password), atau mengganti foto profil, Admin dapat membuka menu 'Pengaturan Akun' di sidebar. Role pengguna lain dapat membuka menu 'Profil' masing-masing.";
        }

        if (strpos($q, 'terang') !== false || strpos($q, 'gelap') !== false || strpos($q, 'dark mode') !== false || strpos($q, 'light mode') !== false || strpos($q, 'tema') !== false) {
            return "Anda dapat beralih antara Mode Terang dan Mode Gelap secara instan melalui tombol toggle bergambar matahari/bulan di pojok kanan atas navbar dashboard, atau melalui tab Pengaturan Akun. Tema akan tersimpan otomatis di browser Anda.";
        }

        if (strpos($q, 'izin') !== false || strpos($q, 'sakit') !== false || strpos($q, 'pengajuan') !== false) {
            return "Siswa dan guru dapat mengajukan keterangan Izin atau Sakit langsung melalui dashboard masing-masing. Setelah formulir dikirim, notifikasi konfirmasi berhasil akan langsung tampil di layar.";
        }

        if (strpos($q, 'absensi siswa') !== false || strpos($q, 'scan siswa') !== false) {
            return "Untuk absensi siswa, silakan buka menu Absensi Siswa lalu arahkan kamera ke barcode kartu pelajar atau gunakan input manual jika barcode tidak terbaca ya kak.";
        }

        if (strpos($q, 'absensi guru') !== false || strpos($q, 'scan guru') !== false) {
            return "Untuk absensi guru, silakan buka menu Absensi Guru di sidebar, kemudian lakukan scan barcode guru melalui scanner yang tersedia ya kak.";
        }

        if (strpos($q, 'laporan') !== false || strpos($q, 'rekap') !== false || strpos($q, 'riwayat') !== false) {
            return "Laporan dan rekap kehadiran dapat dicek pada menu Riwayat Absen di sidebar. Anda bisa memfilter berdasarkan tanggal, kelas, atau status kehadiran.";
        }

        return null;
    }
}
