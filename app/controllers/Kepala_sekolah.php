<?php

class Kepala_sekolah extends Controller {
    public function __construct() {
        AuthHelper::requireRole('kepala_sekolah');
    }

    /**
     * Dashboard Kepala Sekolah
     */
    public function index() {
        $data['judul'] = 'Dashboard Kepala Sekolah';
        $data['active_menu'] = 'dashboard';
        
        $data['stats'] = $this->model('Kepala_sekolah_model')->getDashboardStats();
        $data['latest_guru'] = $this->model('Dashboard_model')->getAdminLatestAbsensiGuru(10);
        $data['profil'] = $this->model('User_model')->getProfil($_SESSION['user_id']);

        $this->view('layouts/dashboard_header', $data);
        $this->view('kepala_sekolah/dashboard', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Rekap Absensi Siswa (Dibatasi - Khusus Guru Saja)
     */
    public function rekap_siswa() {
        Flasher::setFlash('Akses Dibatasi', 'Akun Kepala Sekolah dibatasi hanya dapat melihat rekap data guru.', 'warning');
        header('Location: ' . BASEURL . '/kepala_sekolah/rekap_guru');
        exit;
    }

    /**
     * Rekap Absensi Guru
     */
    public function rekap_guru() {
        $data['judul'] = 'Rekap Absensi Guru';
        $data['active_menu'] = 'rekap_guru';

        $filters = [
            'tgl_mulai' => isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01'),
            'tgl_akhir' => isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-t'),
            'status'    => isset($_GET['status']) ? $_GET['status'] : ''
        ];

        $data['rekap'] = $this->model('Kepala_sekolah_model')->getRekapGuru($filters);
        $data['filters'] = $filters;

        $this->view('layouts/dashboard_header', $data);
        $this->view('kepala_sekolah/rekap_guru', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Data Siswa (Dibatasi - Khusus Guru Saja)
     */
    public function siswa() {
        Flasher::setFlash('Akses Dibatasi', 'Akun Kepala Sekolah dibatasi hanya dapat melihat data guru.', 'warning');
        header('Location: ' . BASEURL . '/kepala_sekolah/guru');
        exit;
    }

    /**
     * Data Guru (Read-only)
     */
    public function guru() {
        $data['judul'] = 'Data Guru';
        $data['active_menu'] = 'data_guru';

        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : null;
        $data['guru'] = $this->model('Kepala_sekolah_model')->getGuruList($keyword);
        $data['keyword'] = $keyword;

        $this->view('layouts/dashboard_header', $data);
        $this->view('kepala_sekolah/guru', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Riwayat Absen (Dialihkan ke Rekap Absensi Guru)
     */
    public function laporan() {
        header('Location: ' . BASEURL . '/kepala_sekolah/rekap_guru');
        exit;
    }

    /**
     * Profil Kepala Sekolah
     */
    public function profil() {
        $data['judul'] = 'Profil Kepala Sekolah';
        $data['active_menu'] = 'profil';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();
        $data['profil'] = $this->model('User_model')->getProfil($_SESSION['user_id']);

        $this->view('layouts/dashboard_header', $data);
        $this->view('kepala_sekolah/profil', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Cetak Kartu Absensi Barcode Kepala Sekolah
     */
    public function cetak_barcode() {
        $guru = $this->model('User_model')->getProfil($_SESSION['user_id']);
        if (!$guru || empty($guru['barcode'])) {
            Flasher::setFlash('Gagal', 'Data profil atau barcode Anda belum tersedia.', 'danger');
            header('Location: ' . BASEURL . '/kepala_sekolah/profil');
            exit;
        }

        $data['judul'] = 'Cetak Kartu Absensi Kepala Sekolah';
        $data['guru'] = $guru;
        $this->view('admin/guru/print_barcode', $data);
    }

    /**
     * Upload dan ganti foto profil kepala sekolah
     */
    public function update_foto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/kepala_sekolah/profil');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/kepala_sekolah/profil');
            exit;
        }

        $file = $_FILES['foto'] ?? null;
        $hasil = $this->model('User_model')->updateFoto($_SESSION['user_id'], $file);

        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/kepala_sekolah/profil');
        exit;
    }

    public function hapus_foto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/kepala_sekolah/profil');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/kepala_sekolah/profil');
            exit;
        }

        $hasil = $this->model('User_model')->deleteFoto($_SESSION['user_id']);

        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/kepala_sekolah/profil');
        exit;
    }
}
