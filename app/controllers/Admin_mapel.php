<?php

class Admin_mapel extends Controller {
    public function __construct() {
        AuthHelper::requireRole('admin');
    }

    public function index() {
        $data['judul'] = 'Manajemen Mata Pelajaran';
        $data['active_menu'] = 'mapel';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();

        $filters = [
            'kelompok' => $_GET['kelompok'] ?? '',
            'tingkat'  => $_GET['tingkat'] ?? '',
            'status'   => $_GET['status'] ?? '',
            'keyword'  => trim($_GET['keyword'] ?? '')
        ];

        $data['filters'] = $filters;
        $data['mapel_list'] = $this->model('Mapel_model')->getAllMapel($filters);
        $data['stats'] = $this->model('Mapel_model')->getStatistikMapel();

        $this->view('layouts/dashboard_header', $data);
        $this->view('admin/mapel/index', $data);
        $this->view('layouts/dashboard_footer');
    }

    public function tambah() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin_mapel');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin_mapel');
            exit;
        }

        $hasil = $this->model('Mapel_model')->tambahMapel($_POST);
        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin_mapel');
        exit;
    }

    public function ubah() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin_mapel');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin_mapel');
            exit;
        }

        $hasil = $this->model('Mapel_model')->ubahMapel($_POST);
        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin_mapel');
        exit;
    }

    public function hapus($id = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? $id;
            $csrf = $_POST['csrf_token'] ?? '';
            if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
                Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/admin_mapel');
                exit;
            }
        }

        if (!$id) {
            header('Location: ' . BASEURL . '/admin_mapel');
            exit;
        }

        $hasil = $this->model('Mapel_model')->hapusMapel($id);
        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin_mapel');
        exit;
    }

    public function get_detail($id) {
        header('Content-Type: application/json');
        $mapel = $this->model('Mapel_model')->getMapelById($id);
        echo json_encode($mapel ?: []);
        exit;
    }
}
