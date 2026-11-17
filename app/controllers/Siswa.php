<?php

class Siswa extends Controller {
    public function __construct() {
        AuthHelper::requireRole('siswa');
    }

    public function index() {
        $data['judul'] = 'Dashboard Siswa';
        $data['active_menu'] = 'dashboard';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();
        
        $stats = $this->model('Dashboard_model')->getSiswaStats($_SESSION['user_id']);
        $data['stats'] = $stats;
        $data['hari_ini'] = $stats['hari_ini'] ?? DateHelper::hariIni();
        $data['tanggal_hari_ini'] = DateHelper::tanggalIndo();
        $data['jam_sekarang'] = DateHelper::jamSekarang('H:i');
        $data['jadwal_hari_ini'] = $stats['jadwal_hari_ini'] ?? [];
        $data['jadwal_mingguan'] = $stats['jadwal_mingguan'] ?? [];
        $data['lokasi_sekolah'] = LokasiHelper::getPengaturanLokasi();

        $this->view('layouts/dashboard_header', $data);
        $this->view('siswa/dashboard', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Halaman khusus Jadwal Mata Pelajaran Siswa
     */
    public function jadwal() {
        $data['judul'] = 'Jadwal Mata Pelajaran';
        $data['active_menu'] = 'jadwal_siswa';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();

        $stats = $this->model('Dashboard_model')->getSiswaStats($_SESSION['user_id']);
        $data['stats'] = $stats;
        $data['hari_ini'] = $stats['hari_ini'] ?? DateHelper::hariIni();
        $data['tanggal_hari_ini'] = DateHelper::tanggalIndo();
        $data['jam_sekarang'] = DateHelper::jamSekarang('H:i');
        $data['jadwal_hari_ini'] = $stats['jadwal_hari_ini'] ?? [];
        $data['jadwal_mingguan'] = $stats['jadwal_mingguan'] ?? [];

        $this->view('layouts/dashboard_header', $data);
        $this->view('siswa/jadwal', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Endpoint POST untuk proses scan Barcode/QR oleh siswa yang sedang login.
     * Mengembalikan JSON untuk AJAX.
     */
    public function scan() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => false, 
                'pesan'  => 'Method tidak diizinkan.'
            ]);
            exit;
        }

        // 1. Verifikasi CSRF Token
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            http_response_code(403);
            echo json_encode([
                'status' => false, 
                'pesan'  => 'Token keamanan tidak valid. Silakan muat ulang halaman.'
            ]);
            exit;
        }

        // 2. Validasi Lokasi Geofencing (Maksimal 100 meter dari sekolah)
        $userLat = $_POST['latitude'] ?? null;
        $userLng = $_POST['longitude'] ?? null;
        $cekLokasi = LokasiHelper::validasiJarak($userLat, $userLng, 'siswa');
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

        // 3. Validasi input barcode
        $barcode = trim($_POST['barcode'] ?? '');
        if (empty($barcode)) {
            echo json_encode([
                'status' => false, 
                'pesan'  => 'Barcode/QR tidak boleh kosong.'
            ]);
            exit;
        }

        // 4. Sanitasi format barcode (alfanumerik dan strip)
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $barcode)) {
            echo json_encode([
                'status' => false, 
                'pesan'  => 'Barcode/QR tidak valid.'
            ]);
            exit;
        }

        // Mode absensi: auto (default), masuk, atau pulang
        $mode = strtolower(trim($_POST['mode'] ?? 'auto'));
        if (!in_array($mode, ['auto', 'masuk', 'pulang'])) {
            $mode = 'auto';
        }

        // 5. Catat absensi mandiri siswa berdasarkan identitas sesi terotentikasi
        $hasil = $this->model('Absensi_model')->catatAbsensiSiswaMandiri($_SESSION['user_id'], $barcode, null, $mode);
        if (is_array($hasil)) {
            $hasil['jarak'] = $cekLokasi['jarak'];
        }

        echo json_encode($hasil);
        exit;
    }

    /**
     * Endpoint untuk memproses pengajuan izin atau sakit oleh siswa.
     */
    public function ajukan_izin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/siswa');
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
            header('Location: ' . BASEURL . '/siswa');
            exit;
        }

        // 2. Proses pengajuan via model
        $file = $_FILES['lampiran'] ?? null;
        $hasil = $this->model('Absensi_model')->ajukanIzinSiswa($_SESSION['user_id'], $_POST, $file);

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
        header('Location: ' . BASEURL . '/siswa');
        exit;
    }

    /**
     * Halaman Profil Siswa
     */
    public function profil() {
        $data['judul'] = 'Profil Saya';
        $data['active_menu'] = 'profil';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();
        $data['profil'] = $this->model('User_model')->getProfil($_SESSION['user_id']);

        $this->view('layouts/dashboard_header', $data);
        $this->view('siswa/profil', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Upload dan ganti foto profil siswa
     */
    public function update_foto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/siswa/profil');
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
            header('Location: ' . BASEURL . '/siswa/profil');
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

        $redirect = !empty($_POST['redirect_back']) ? $_POST['redirect_back'] : (BASEURL . '/siswa/profil');
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Hapus foto profil siswa
     */
    public function hapus_foto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/siswa/profil');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/siswa/profil');
            exit;
        }

        $hasil = $this->model('User_model')->deleteFoto($_SESSION['user_id']);

        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        $redirect = !empty($_POST['redirect_back']) ? $_POST['redirect_back'] : (BASEURL . '/siswa/profil');
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Update kontak & biodata siswa
     */
    public function update_bio() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/siswa/profil');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/siswa/profil');
            exit;
        }

        $hasil = $this->model('User_model')->updateProfilBio($_SESSION['user_id'], $_POST);
        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }
        header('Location: ' . BASEURL . '/siswa/profil');
        exit;
    }

    /**
     * Cetak Kartu Absensi Barcode Siswa Sendiri
     */
    public function cetak_barcode() {
        $siswa = $this->model('User_model')->getProfil($_SESSION['user_id']);
        if (!$siswa || empty($siswa['barcode'])) {
            Flasher::setFlash('Gagal', 'Data profil atau barcode Anda belum tersedia.', 'danger');
            header('Location: ' . BASEURL . '/siswa');
            exit;
        }

        $data['judul'] = 'Cetak Barcode Siswa';
        $data['siswa'] = $siswa;
        $this->view('admin/siswa/print_barcode', $data);
    }

    private function isAjax() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}

