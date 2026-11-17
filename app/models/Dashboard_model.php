<?php

class Dashboard_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // ==========================================
    // ADMIN & KEPALA SEKOLAH
    // ==========================================
    public function getAdminStats() {
        $stats = [];
        
        // Master Data Counts
        $this->db->query("SELECT COUNT(id) as total FROM siswa WHERE status = 'active'");
        $stats['total_siswa'] = (int)($this->db->single()['total'] ?? 0);

        $this->db->query("SELECT COUNT(id) as total FROM siswa WHERE status = 'inactive'");
        $stats['total_siswa_alumni'] = (int)($this->db->single()['total'] ?? 0);
        
        $this->db->query("SELECT COUNT(id) as total FROM guru");
        $stats['total_guru'] = (int)($this->db->single()['total'] ?? 0);

        $this->db->query("SELECT COUNT(id) as total FROM kelas");
        $stats['total_kelas'] = (int)($this->db->single()['total'] ?? 0);

        $this->db->query("SELECT COUNT(id) as total FROM mata_pelajaran WHERE status = 'active'");
        $stats['total_mapel'] = (int)($this->db->single()['total'] ?? 0);

        // Rekap Siswa Hari Ini (CURDATE())
        $this->db->query("SELECT 
                            SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                            SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                            SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                            SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                            SUM(CASE WHEN status = 'alpa' THEN 1 ELSE 0 END) as alpa,
                            COUNT(id) as total_presensi
                          FROM absensi_siswa 
                          WHERE tanggal = CURDATE()");
        $resSiswa = $this->db->single();
        $stats['siswa_hadir_hari_ini'] = (int)($resSiswa['hadir'] ?? 0);
        $stats['siswa_terlambat_hari_ini'] = (int)($resSiswa['terlambat'] ?? 0);
        $stats['siswa_izin_hari_ini'] = (int)($resSiswa['izin'] ?? 0);
        $stats['siswa_sakit_hari_ini'] = (int)($resSiswa['sakit'] ?? 0);
        $stats['siswa_alpa_hari_ini'] = (int)($resSiswa['alpa'] ?? 0);
        $stats['siswa_total_presensi'] = (int)($resSiswa['total_presensi'] ?? 0);
        $totalMasukSiswa = $stats['siswa_hadir_hari_ini'] + $stats['siswa_terlambat_hari_ini'];
        $stats['siswa_belum_absen'] = max(0, $stats['total_siswa'] - $stats['siswa_total_presensi']);
        $stats['siswa_persentase'] = $stats['total_siswa'] > 0 ? round(($totalMasukSiswa / $stats['total_siswa']) * 100, 1) : 0;

        // Rekap Guru Hari Ini (CURDATE())
        $this->db->query("SELECT 
                            SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                            SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                            SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                            SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                            SUM(CASE WHEN status = 'alpa' THEN 1 ELSE 0 END) as alpa,
                            COUNT(id) as total_presensi
                          FROM absensi_guru 
                          WHERE tanggal = CURDATE()");
        $resGuru = $this->db->single();
        $stats['guru_hadir_hari_ini'] = (int)($resGuru['hadir'] ?? 0);
        $stats['guru_terlambat_hari_ini'] = (int)($resGuru['terlambat'] ?? 0);
        $stats['guru_izin_hari_ini'] = (int)($resGuru['izin'] ?? 0);
        $stats['guru_sakit_hari_ini'] = (int)($resGuru['sakit'] ?? 0);
        $stats['guru_alpa_hari_ini'] = (int)($resGuru['alpa'] ?? 0);
        $stats['guru_total_presensi'] = (int)($resGuru['total_presensi'] ?? 0);
        $totalMasukGuru = $stats['guru_hadir_hari_ini'] + $stats['guru_terlambat_hari_ini'];
        $stats['guru_masuk_total'] = $totalMasukGuru;
        $stats['guru_belum_absen'] = max(0, $stats['total_guru'] - $stats['guru_total_presensi']);
        $stats['guru_persentase'] = $stats['total_guru'] > 0 ? round(($totalMasukGuru / $stats['total_guru']) * 100, 1) : 0;

        return $stats;
    }

    public function getAdminLatestAbsensiSiswa($limit = 10) {
        $limit = max(1, (int)$limit);
        $this->db->query("SELECT absensi_siswa.*, siswa.nama, siswa.nis, kelas.nama_kelas 
                          FROM absensi_siswa 
                          JOIN siswa ON absensi_siswa.siswa_id = siswa.id 
                          LEFT JOIN kelas ON siswa.kelas_id = kelas.id
                          ORDER BY absensi_siswa.id DESC LIMIT {$limit}");
        return $this->db->resultSet();
    }

    public function getAdminLatestAbsensiGuru($limit = 10) {
        $limit = max(1, (int)$limit);
        $this->db->query("SELECT absensi_guru.*, guru.nama, guru.nip 
                          FROM absensi_guru 
                          JOIN guru ON absensi_guru.guru_id = guru.id 
                          ORDER BY absensi_guru.id DESC LIMIT {$limit}");
        return $this->db->resultSet();
    }

    public function getStatistikPerKelas() {
        $this->db->query("SELECT 
                            kelas.id, 
                            kelas.nama_kelas, 
                            kelas.tingkat,
                            kelas.jurusan,
                            guru.nama as nama_wali,
                            (SELECT COUNT(s.id) FROM siswa s WHERE s.kelas_id = kelas.id AND s.status = 'active') as total_siswa,
                            (SELECT COUNT(a.id) FROM absensi_siswa a JOIN siswa s ON a.siswa_id = s.id WHERE s.kelas_id = kelas.id AND a.tanggal = CURDATE() AND a.status = 'hadir') as hadir,
                            (SELECT COUNT(a.id) FROM absensi_siswa a JOIN siswa s ON a.siswa_id = s.id WHERE s.kelas_id = kelas.id AND a.tanggal = CURDATE() AND a.status = 'terlambat') as terlambat,
                            (SELECT COUNT(a.id) FROM absensi_siswa a JOIN siswa s ON a.siswa_id = s.id WHERE s.kelas_id = kelas.id AND a.tanggal = CURDATE() AND a.status = 'izin') as izin,
                            (SELECT COUNT(a.id) FROM absensi_siswa a JOIN siswa s ON a.siswa_id = s.id WHERE s.kelas_id = kelas.id AND a.tanggal = CURDATE() AND a.status = 'sakit') as sakit,
                            (SELECT COUNT(a.id) FROM absensi_siswa a JOIN siswa s ON a.siswa_id = s.id WHERE s.kelas_id = kelas.id AND a.tanggal = CURDATE() AND a.status = 'alpa') as alpa
                          FROM kelas
                          LEFT JOIN guru ON kelas.wali_kelas_id = guru.id
                          ORDER BY kelas.tingkat ASC, kelas.nama_kelas ASC");
        $results = $this->db->resultSet();
        foreach ($results as &$r) {
            $total = (int)$r['total_siswa'];
            $hadirTerlambat = (int)$r['hadir'] + (int)$r['terlambat'];
            $r['persentase'] = $total > 0 ? round(($hadirTerlambat / $total) * 100, 1) : 0;
            $r['belum_absen'] = max(0, $total - ($hadirTerlambat + (int)$r['izin'] + (int)$r['sakit'] + (int)$r['alpa']));
        }
        return $results;
    }

    public function getJadwalKBMHariIni($hari = null) {
        if ($hari === null) {
            $days = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];
            $currentDayEnglish = date('l');
            $hari = $days[$currentDayEnglish] ?? 'Senin';
        }

        $this->db->query("SELECT jm.*, g.nama as nama_guru, k.nama_kelas, mp.nama_mapel, mp.kode_mapel
                          FROM jadwal_mengajar jm
                          JOIN guru g ON jm.guru_id = g.id
                          JOIN kelas k ON jm.kelas_id = k.id
                          JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                          WHERE jm.hari = :hari AND jm.status = 'active'
                          ORDER BY jm.jam_mulai ASC, k.nama_kelas ASC");
        $this->db->bind('hari', $hari);
        return [
            'hari' => $hari,
            'list' => $this->db->resultSet()
        ];
    }

    // ==========================================
    // GURU
    // ==========================================
    public function getGuruStats($user_id) {
        $stats = [];

        // Ambil data guru dari user_id
        $this->db->query("SELECT id, nama, nip, mata_pelajaran, barcode FROM guru WHERE user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $guru = $this->db->single();
        if (!$guru) return false;

        $guru_id = $guru['id'];
        $stats['info_guru'] = $guru;
        $stats['guru'] = $guru;

        // ── Status absensi guru hari ini ──
        $this->db->query("SELECT status, jam_scan, jam_pulang, keterangan FROM absensi_guru WHERE guru_id = :guru_id AND tanggal = CURDATE()");
        $this->db->bind('guru_id', $guru_id);
        $stats['absen_hari_ini'] = $this->db->single();

        // ── Riwayat absensi guru 5 terakhir ──
        $this->db->query("SELECT * FROM absensi_guru WHERE guru_id = :guru_id ORDER BY tanggal DESC LIMIT 5");
        $this->db->bind('guru_id', $guru_id);
        $stats['riwayat'] = $this->db->resultSet();

        // ── Riwayat pengajuan izin/sakit guru 5 terakhir ──
        $this->db->query("SELECT * FROM absensi_guru WHERE guru_id = :guru_id AND status IN ('sakit', 'izin') ORDER BY tanggal DESC LIMIT 5");
        $this->db->bind('guru_id', $guru_id);
        $stats['riwayat_izin'] = $this->db->resultSet();

        // ── Jadwal Mengajar Guru (Senin s/d Sabtu & Hari Ini) ──
        $hariIni = DateHelper::hariIni();
        $stats['hari_ini'] = $hariIni;
        $stats['tanggal_hari_ini'] = DateHelper::tanggalIndo();

        $this->db->query("SELECT jm.*, k.nama_kelas, k.jurusan, 
                                 (SELECT COUNT(*) FROM siswa WHERE siswa.kelas_id = k.id) as total_siswa_kelas,
                                 mp.kode_mapel, mp.nama_mapel, mp.kelompok
                          FROM jadwal_mengajar jm
                          JOIN kelas k ON jm.kelas_id = k.id
                          LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                          WHERE jm.guru_id = :guru_id AND jm.status = 'active'
                          ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC");
        $this->db->bind('guru_id', $guru_id);
        $allJadwalGuru = $this->db->resultSet();

        $stats['jadwal_hari_ini'] = [];
        $stats['jadwal_senin_jumat'] = [
            'Senin'  => [],
            'Selasa' => [],
            'Rabu'   => [],
            'Kamis'  => [],
            'Jumat'  => [],
            'Sabtu'  => []
        ];
        $stats['jadwal_mingguan'] = &$stats['jadwal_senin_jumat'];

        foreach ($allJadwalGuru as $jg) {
            if ($jg['hari'] === $hariIni) {
                $stats['jadwal_hari_ini'][] = $jg;
            }
            if (isset($stats['jadwal_senin_jumat'][$jg['hari']])) {
                $stats['jadwal_senin_jumat'][$jg['hari']][] = $jg;
            }
        }
        $stats['total_jadwal_mengajar'] = count($allJadwalGuru);

        // Kelas yang diampu guru
        $kelasIds = array_filter(array_unique(array_column($allJadwalGuru, 'kelas_id')));
        $stats['total_kelas_diajar'] = count($kelasIds);

        return $stats;
    }

    // ==========================================
    // WALI KELAS
    // ==========================================
    public function getWaliKelasStats($user_id) {
        $stats = [];
        
        // Cari kelas yang diampu
        $this->db->query("SELECT kelas.id, kelas.nama_kelas FROM kelas 
                          JOIN guru ON kelas.wali_kelas_id = guru.id 
                          WHERE guru.user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $kelas = $this->db->single();
        
        if (!$kelas) {
            return false; // Bukan wali kelas atau belum di-assign
        }
        
        $kelas_id = $kelas['id'];
        $stats['info_kelas'] = $kelas;

        // Total siswa kelas ini
        $this->db->query("SELECT COUNT(id) as total FROM siswa WHERE kelas_id = :kelas_id");
        $this->db->bind('kelas_id', $kelas_id);
        $stats['total_siswa'] = $this->db->single()['total'];

        // Statistik Hari Ini (Hadir, Terlambat, Izin, Sakit, Alpa)
        $statuses = ['hadir', 'terlambat', 'izin', 'sakit', 'alpa'];
        foreach ($statuses as $status) {
            $this->db->query("SELECT COUNT(absensi_siswa.id) as total FROM absensi_siswa 
                              JOIN siswa ON absensi_siswa.siswa_id = siswa.id 
                              WHERE siswa.kelas_id = :kelas_id AND absensi_siswa.tanggal = CURDATE() AND absensi_siswa.status = :status");
            $this->db->bind('kelas_id', $kelas_id);
            $this->db->bind('status', $status);
            $stats['status_' . $status] = $this->db->single()['total'];
        }

        // Daftar siswa kelas tersebut dan status hari ini
        $this->db->query("SELECT siswa.nis, siswa.nama, absensi_siswa.status as status_absen, absensi_siswa.jam_masuk
                          FROM siswa 
                          LEFT JOIN absensi_siswa ON siswa.id = absensi_siswa.siswa_id AND absensi_siswa.tanggal = CURDATE()
                          WHERE siswa.kelas_id = :kelas_id
                          ORDER BY siswa.nama ASC");
        $this->db->bind('kelas_id', $kelas_id);
        $stats['daftar_siswa'] = $this->db->resultSet();

        // Absensi terbaru (siswa kelas ini)
        $this->db->query("SELECT absensi_siswa.jam_masuk, absensi_siswa.status, absensi_siswa.tanggal,
                                 siswa.nama, siswa.nis
                          FROM absensi_siswa
                          JOIN siswa ON absensi_siswa.siswa_id = siswa.id
                          WHERE siswa.kelas_id = :kelas_id
                          ORDER BY absensi_siswa.tanggal DESC, absensi_siswa.id DESC LIMIT 5");
        $this->db->bind('kelas_id', $kelas_id);
        $stats['absensi_terbaru'] = $this->db->resultSet();

        // Catatan terbaru (siswa kelas ini)
        $this->db->query("SELECT catatan_siswa.id, catatan_siswa.kategori, catatan_siswa.tanggal, catatan_siswa.catatan,
                                 siswa.nama as nama_siswa, siswa.nis
                          FROM catatan_siswa
                          JOIN siswa ON catatan_siswa.siswa_id = siswa.id
                          WHERE siswa.kelas_id = :kelas_id
                          ORDER BY catatan_siswa.tanggal DESC, catatan_siswa.id DESC LIMIT 5");
        $this->db->bind('kelas_id', $kelas_id);
        $stats['catatan_terbaru'] = $this->db->resultSet();

        // Catatan bulan ini
        $this->db->query("SELECT COUNT(catatan_siswa.id) as total 
                          FROM catatan_siswa 
                          JOIN siswa ON catatan_siswa.siswa_id = siswa.id 
                          WHERE siswa.kelas_id = :kelas_id 
                            AND MONTH(catatan_siswa.tanggal) = MONTH(CURDATE()) 
                            AND YEAR(catatan_siswa.tanggal) = YEAR(CURDATE())");
        $this->db->bind('kelas_id', $kelas_id);
        $stats['catatan_bulan_ini'] = (int)$this->db->single()['total'];

        // Siswa yang membutuhkan pembinaan (kategori Pembinaan/Pelanggaran)
        $this->db->query("SELECT COUNT(DISTINCT siswa.id) as total 
                          FROM siswa 
                          JOIN catatan_siswa ON siswa.id = catatan_siswa.siswa_id 
                          WHERE siswa.kelas_id = :kelas_id 
                            AND catatan_siswa.kategori IN ('Pembinaan', 'Pelanggaran')");
        $this->db->bind('kelas_id', $kelas_id);
        $stats['siswa_butuh_pembinaan'] = (int)$this->db->single()['total'];

        return $stats;
    }

    // ==========================================
    // SISWA
    // ==========================================
    public function getSiswaStats($user_id) {
        $stats = [];
        
        // Ambil siswa info
        $this->db->query("SELECT siswa.*, kelas.nama_kelas, kelas.jurusan 
                          FROM siswa 
                          LEFT JOIN kelas ON siswa.kelas_id = kelas.id 
                          WHERE siswa.user_id = :user_id");
        $this->db->bind('user_id', $user_id);
        $siswa = $this->db->single();
        
        if (!$siswa) return false;
        
        $siswa_id = $siswa['id'];
        $stats['info_siswa'] = $siswa;

        // Absen Hari Ini
        $this->db->query("SELECT status, jam_masuk, jam_pulang, keterangan FROM absensi_siswa WHERE siswa_id = :siswa_id AND tanggal = CURDATE()");
        $this->db->bind('siswa_id', $siswa_id);
        $stats['absen_hari_ini'] = $this->db->single();

        // Riwayat Pengajuan Izin / Sakit Siswa
        $this->db->query("SELECT * FROM absensi_siswa WHERE siswa_id = :siswa_id AND status IN ('sakit', 'izin') ORDER BY tanggal DESC LIMIT 5");
        $this->db->bind('siswa_id', $siswa_id);
        $stats['riwayat_izin'] = $this->db->resultSet();

        // Total akumulasi (Hadir, Terlambat, Izin/Sakit/Alpa)
        $this->db->query("SELECT COUNT(id) as total FROM absensi_siswa WHERE siswa_id = :siswa_id AND status = 'hadir'");
        $this->db->bind('siswa_id', $siswa_id);
        $stats['total_hadir'] = $this->db->single()['total'];

        $this->db->query("SELECT COUNT(id) as total FROM absensi_siswa WHERE siswa_id = :siswa_id AND status = 'terlambat'");
        $this->db->bind('siswa_id', $siswa_id);
        $stats['total_terlambat'] = $this->db->single()['total'];

        $this->db->query("SELECT COUNT(id) as total FROM absensi_siswa WHERE siswa_id = :siswa_id AND status IN ('izin', 'sakit', 'alpa')");
        $this->db->bind('siswa_id', $siswa_id);
        $stats['total_absen_lainnya'] = $this->db->single()['total'];

        // ── Mata Pelajaran Harian & Jadwal Mingguan Siswa ──
        $hariIni = DateHelper::hariIni();
        $stats['hari_ini'] = $hariIni;
        $stats['tanggal_hari_ini'] = DateHelper::tanggalIndo();
        $stats['jadwal_hari_ini'] = [];
        $stats['jadwal_mingguan'] = [
            'Senin'   => [],
            'Selasa'  => [],
            'Rabu'    => [],
            'Kamis'   => [],
            'Jumat'   => [],
            'Sabtu'   => []
        ];

        if (!empty($siswa['kelas_id'])) {
            $this->db->query("SELECT jm.*, 
                                     g.nama as nama_guru, g.nip as nip_guru, g.foto as foto_guru, g.mata_pelajaran as spesialisasi_guru,
                                     k.nama_kelas, k.jurusan,
                                     mp.kode_mapel, mp.nama_mapel, mp.kelompok
                              FROM jadwal_mengajar jm
                              JOIN guru g ON jm.guru_id = g.id
                              JOIN kelas k ON jm.kelas_id = k.id
                              LEFT JOIN mata_pelajaran mp ON jm.mapel_id = mp.id
                              WHERE jm.kelas_id = :kelas_id AND jm.status = 'active'
                              ORDER BY FIELD(jm.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jm.jam_mulai ASC");
            $this->db->bind('kelas_id', $siswa['kelas_id']);
            $allJadwalKelas = $this->db->resultSet();

            foreach ($allJadwalKelas as $jk) {
                if ($jk['hari'] === $hariIni) {
                    $stats['jadwal_hari_ini'][] = $jk;
                }
                if (isset($stats['jadwal_mingguan'][$jk['hari']])) {
                    $stats['jadwal_mingguan'][$jk['hari']][] = $jk;
                }
            }
        }

        return $stats;
    }
}
