<?php

class Admin_jadwal_mengajar extends Controller {
    public function __construct() {
        AuthHelper::requireRole('admin');
    }

    public function index() {
        $data['judul'] = 'Jadwal Mengajar Guru';
        $data['active_menu'] = 'jadwal_mengajar';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();

        $filters = [
            'hari'     => $_GET['hari'] ?? '',
            'guru_id'  => $_GET['guru_id'] ?? '',
            'kelas_id' => $_GET['kelas_id'] ?? '',
            'status'   => $_GET['status'] ?? '',
            'keyword'  => trim($_GET['keyword'] ?? '')
        ];

        $data['view_mode'] = $_GET['view'] ?? 'kelas'; // 'kelas', 'grid', or 'table'
        $data['filters'] = $filters;
        $data['hari_ini'] = DateHelper::hariIni();
        $data['tanggal_hari_ini'] = DateHelper::tanggalIndo();
        $data['jam_sekarang'] = DateHelper::jamSekarang('H:i');
        $data['jadwal_list'] = $this->model('Jadwal_mengajar_model')->getAllJadwalMengajar($filters);
        $data['stats'] = $this->model('Jadwal_mengajar_model')->getStatistikJadwal();

        $data['guru_list']  = $this->model('Jadwal_mengajar_model')->getAllGuruList();
        $data['kelas_list'] = $this->model('Jadwal_mengajar_model')->getAllKelasList();
        $data['mapel_list'] = $this->model('Jadwal_mengajar_model')->getAllMapelList();

        $this->view('layouts/dashboard_header', $data);
        $this->view('admin/jadwal_mengajar/index', $data);
        $this->view('layouts/dashboard_footer');
    }

    public function tambah() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin_jadwal_mengajar');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan CSRF tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin_jadwal_mengajar');
            exit;
        }

        $hasil = $this->model('Jadwal_mengajar_model')->tambahJadwal($_POST);
        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin_jadwal_mengajar');
        exit;
    }

    public function ubah() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin_jadwal_mengajar');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan CSRF tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin_jadwal_mengajar');
            exit;
        }

        $hasil = $this->model('Jadwal_mengajar_model')->ubahJadwal($_POST);
        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin_jadwal_mengajar');
        exit;
    }

    public function hapus($id = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? $id;
            $csrf = $_POST['csrf_token'] ?? '';
            if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
                Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/admin_jadwal_mengajar');
                exit;
            }
        }

        if (!$id) {
            header('Location: ' . BASEURL . '/admin_jadwal_mengajar');
            exit;
        }

        $hasil = $this->model('Jadwal_mengajar_model')->hapusJadwal($id);
        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin_jadwal_mengajar');
        exit;
    }

    public function get_detail($id) {
        header('Content-Type: application/json');
        $jadwal = $this->model('Jadwal_mengajar_model')->getJadwalById($id);
        echo json_encode($jadwal ?: []);
        exit;
    }
}
