<?php

class Guru extends Controller {
    public function __construct() {
        AuthHelper::requireRole('guru');
    }

    public function index() {
        $data['judul'] = 'Dashboard Guru';
        $data['active_menu'] = 'dashboard';
        $stats = $this->model('Dashboard_model')->getGuruStats($_SESSION['user_id']);
        $data['stats'] = $stats;
        $data['hari_ini'] = $stats['hari_ini'] ?? 'Senin';
        $data['jadwal_hari_ini'] = $stats['jadwal_hari_ini'] ?? [];
        $data['jadwal_senin_jumat'] = $stats['jadwal_senin_jumat'] ?? [];
        $data['jadwal_mingguan'] = $stats['jadwal_mingguan'] ?? [];
        $data['tanggal_hari_ini'] = DateHelper::tanggalIndo();
        $data['jam_sekarang'] = DateHelper::jamSekarang('H:i');
        $data['csrf_token'] = AuthHelper::generateCsrfToken();
        $data['lokasi_sekolah'] = LokasiHelper::getPengaturanLokasi();

        $this->view('layouts/dashboard_header', $data);
        $this->view('guru/dashboard', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Halaman Jadwal Mengajar Guru (Senin s/d Sabtu)
     */
    public function jadwal() {
        $data['judul'] = 'Jadwal Mengajar Guru';
        $data['active_menu'] = 'jadwal_mengajar_guru';
        $stats = $this->model('Dashboard_model')->getGuruStats($_SESSION['user_id']);
        $data['stats'] = $stats;
        $data['hari_ini'] = $stats['hari_ini'] ?? DateHelper::hariIni();
        $data['jadwal_hari_ini'] = $stats['jadwal_hari_ini'] ?? [];
        $data['jadwal_senin_jumat'] = $stats['jadwal_senin_jumat'] ?? [];
        $data['jadwal_mingguan'] = $stats['jadwal_mingguan'] ?? [];
        $data['tanggal_hari_ini'] = DateHelper::tanggalIndo();
        $data['jam_sekarang'] = DateHelper::jamSekarang('H:i');
        $data['csrf_token'] = AuthHelper::generateCsrfToken();

        $this->view('layouts/dashboard_header', $data);
        $this->view('guru/jadwal', $data);
        $this->view('layouts/dashboard_footer');
    }

    public function laporan_absensi($action = '', $id = '') {
        $kelas_id = $this->model('Laporan_model')->getKelasIdByUserId($_SESSION['user_id']);
        if (empty($kelas_id) && !in_array($_SESSION['role'], ['admin', 'kepala_sekolah', 'wali_kelas'])) {
            Flasher::setFlash('Akses Dibatasi', 'Informasi absensi siswa hanya dapat diakses oleh Wali Kelas.', 'warning');
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        if ($action === 'siswa' && !empty($id)) {
            $this->detail_siswa($id);
            return;
        }

        // Filter dari GET
        $filters = [
            'tanggal_mulai' => $_GET['tanggal_mulai'] ?? '',
            'tanggal_akhir' => $_GET['tanggal_akhir'] ?? '',
            'kelas_id'      => $_GET['kelas_id'] ?? '',
            'siswa_id'      => $_GET['siswa_id'] ?? '',
            'status'        => $_GET['status'] ?? '',
        ];

        // Wali Kelas dibatasi pada kelas yang diampu
        $role_context = ['role' => $_SESSION['role']];
        if (!empty($kelas_id)) {
            $role_context['kelas_id'] = $kelas_id;
            $filters['kelas_id'] = $kelas_id;
        }

        // Handle export Excel
        if (isset($_GET['export']) && $_GET['export'] === 'excel') {
            // Karena private di Laporan.php, kita tidak bisa memanggilnya langsung.
            // Tapi instruksi bilang "Jika memungkinkan". Kita bisa redirect ke Laporan/siswa?
            // Atau cukup redirect ke Laporan::siswa untuk kemudahan:
            // header("Location: " . BASEURL . "/laporan/siswa?" . http_build_query($_GET));
            // exit;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 15;

        $model = $this->model('Laporan_model');
        $total = $model->countLaporanSiswa($filters, $role_context);
        $data['laporan'] = $model->getLaporanSiswa($filters, $page, $per_page, $role_context);
        $data['kelas'] = $model->getAllKelasForFilter();
        
        if ($_SESSION['role'] === 'wali_kelas') {
            $data['siswa_list'] = $model->getAllSiswaForFilter($role_context['kelas_id']);
        } else {
            $data['siswa_list'] = $model->getAllSiswaForFilter($filters['kelas_id']);
        }

        // Pagination info
        $data['total'] = $total;
        $data['page'] = $page;
        $data['per_page'] = $per_page;
        $data['total_pages'] = ceil($total / $per_page);
        $data['filters'] = $filters;
        $data['role_context'] = $role_context;
        
        $data['judul'] = 'Riwayat Absen Siswa';
        $data['active_menu'] = 'laporan_absensi_siswa_guru';
        
        // Base route untuk form dan pagination
        $data['base_route'] = BASEURL . '/guru/laporan_absensi';

        $this->view('layouts/dashboard_header', $data);
        $this->view('guru/laporan_absensi', $data);
        $this->view('layouts/dashboard_footer');
    }

    private function detail_siswa($id) {
        $model = $this->model('Laporan_model');
        $siswa = $this->model('Siswa_model')->getSiswaById($id);
        
        if (!$siswa) {
            Flasher::setFlash('Gagal', 'Siswa tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/guru/laporan_absensi');
            exit;
        }

        // Proteksi jika Wali Kelas: pastikan siswa ini ada di kelasnya
        if ($_SESSION['role'] === 'wali_kelas') {
            $kelas_id = $model->getKelasIdByUserId($_SESSION['user_id']);
            if ($siswa['kelas_id'] != $kelas_id) {
                http_response_code(403);
                $data['judul'] = 'Akses Ditolak';
                $this->view('layouts/dashboard_header', $data);
                $this->view('errors/403');
                $this->view('layouts/dashboard_footer');
                exit;
            }
        }

        $filters = ['siswa_id' => $id];
        $role_context = ['role' => 'admin']; // Bypass filter karena sudah divalidasi di atas
        
        $data['siswa'] = $siswa;
        
        if (file_exists('../app/models/Kelas_model.php')) {
            $data['kelas'] = $this->model('Kelas_model')->getKelasById($siswa['kelas_id']);
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 15;
        
        $total = $model->countLaporanSiswa($filters, $role_context);
        $data['laporan'] = $model->getLaporanSiswa($filters, $page, $per_page, $role_context);
        
        $data['total'] = $total;
        $data['page'] = $page;
        $data['per_page'] = $per_page;
        $data['total_pages'] = ceil($total / $per_page);
        $data['filters'] = $filters;

        // Hitung statistik
        $data['statistik'] = $model->getStatistikAbsensiSiswa($id);

        $data['judul'] = 'Riwayat Absensi: ' . $siswa['nama'];
        $data['active_menu'] = 'laporan_absensi_siswa_guru';

        $this->view('layouts/dashboard_header', $data);
        $this->view('guru/detail_siswa', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Endpoint untuk memproses pengajuan izin atau sakit oleh guru.
     */
    public function ajukan_izin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        // 1. Verifikasi CSRF Token
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            if ($this->isAjax()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['status' => false, 'pesan' => 'Token keamanan tidak valid. Silakan muat ulang halaman.']);
                exit;
            }
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid. Silakan coba kembali.', 'danger');
            header('Location: ' . BASEURL . '/guru');
            exit;
        }

        // 2. Proses pengajuan via model
        $file = $_FILES['lampiran'] ?? null;
        $hasil = $this->model('Absensi_model')->ajukanIzinGuru($_SESSION['user_id'], $_POST, $file);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode($hasil);
            exit;
        }

        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }
        header('Location: ' . BASEURL . '/guru');
        exit;
    }

    /**
     * Halaman Profil Guru
     */
    public function profil() {
        $data['judul'] = 'Profil Saya';
        $data['active_menu'] = 'profil';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();
        $data['profil'] = $this->model('User_model')->getProfil($_SESSION['user_id']);

        $this->view('layouts/dashboard_header', $data);
        $this->view('guru/profil', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Cetak Kartu Absensi Barcode Guru
     */
    public function cetak_barcode() {
        $guru = $this->model('User_model')->getProfil($_SESSION['user_id']);
        if (!$guru || empty($guru['barcode'])) {
            Flasher::setFlash('Gagal', 'Data profil atau barcode Anda belum tersedia.', 'danger');
            header('Location: ' . BASEURL . '/guru/profil');
            exit;
        }

        $data['judul'] = 'Cetak Kartu Absensi Guru';
        $data['guru'] = $guru;
        $this->view('admin/guru/print_barcode', $data);
    }

    /**
     * Upload dan ganti foto profil guru
     */
    public function update_foto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/guru/profil');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            if ($this->isAjax()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['status' => false, 'pesan' => 'Token keamanan tidak valid.']);
                exit;
            }
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/guru/profil');
            exit;
        }

        $file = $_FILES['foto'] ?? null;
        $hasil = $this->model('User_model')->updateFoto($_SESSION['user_id'], $file);

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode($hasil);
            exit;
        }

        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        $redirect = !empty($_POST['redirect_back']) ? $_POST['redirect_back'] : (BASEURL . '/guru/profil');
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Hapus foto profil guru
     */
    public function hapus_foto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/guru/profil');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/guru/profil');
            exit;
        }

        $hasil = $this->model('User_model')->deleteFoto($_SESSION['user_id']);

        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        $redirect = !empty($_POST['redirect_back']) ? $_POST['redirect_back'] : (BASEURL . '/guru/profil');
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Update kontak guru
     */
    public function update_bio() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/guru/profil');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/guru/profil');
            exit;
        }

        $hasil = $this->model('User_model')->updateProfilBio($_SESSION['user_id'], $_POST);
        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }
        header('Location: ' . BASEURL . '/guru/profil');
        exit;
    }

    /**
     * Endpoint POST scan absensi guru langsung dari Dashboard Guru (Geofencing maksimal 100 meter)
     */
    public function scan() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'pesan' => 'Method tidak diizinkan.']);
            exit;
        }

        // 1. Verifikasi CSRF Token
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            http_response_code(403);
            echo json_encode(['status' => false, 'pesan' => 'Token keamanan tidak valid. Silakan muat ulang halaman.']);
            exit;
        }

        // 2. Validasi Geofencing Jarak (Maksimal 100 Meter dari Sekolah)
        $userLat = $_POST['latitude'] ?? null;
        $userLng = $_POST['longitude'] ?? null;
        $cekLokasi = LokasiHelper::validasiJarak($userLat, $userLng, 'guru');
        if (!$cekLokasi['valid']) {
            echo json_encode([
                'status'         => false,
                'di_luar_radius' => $cekLokasi['di_luar_radius'] ?? true,
                'jarak'          => $cekLokasi['jarak'] ?? null,
                'max_radius'     => $cekLokasi['max_radius'] ?? 100,
                'pesan'          => $cekLokasi['pesan']
            ]);
            exit;
        }

        // 3. Ambil barcode guru (bisa dari input POST barcode atau profil guru login)
        $barcode = trim($_POST['barcode'] ?? '');
        if (empty($barcode)) {
            $guru = $this->model('User_model')->getProfil($_SESSION['user_id']);
            $barcode = $guru['barcode'] ?? '';
        }

        if (empty($barcode)) {
            echo json_encode(['status' => false, 'pesan' => 'Barcode guru tidak ditemukan.']);
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $barcode)) {
            echo json_encode(['status' => false, 'pesan' => 'Format barcode tidak valid.']);
            exit;
        }

        // Mode absensi: auto (default), masuk, atau pulang
        $mode = strtolower(trim($_POST['mode'] ?? 'auto'));
        if (!in_array($mode, ['auto', 'masuk', 'pulang'])) {
            $mode = 'auto';
        }

        // 4. Catat absensi guru
        $hasil = $this->model('Absensi_model')->catatAbsensiGuruBarcode($barcode, null, $mode);
        if (is_array($hasil)) {
            $hasil['jarak'] = $cekLokasi['jarak'];
        }

        echo json_encode($hasil);
        exit;
    }

    private function isAjax() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}
