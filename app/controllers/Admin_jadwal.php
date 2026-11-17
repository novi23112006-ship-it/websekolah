<?php

class Admin_jadwal extends Controller {

    public function __construct() {
        AuthHelper::requireRole('admin');
    }

    /**
     * Tampilkan halaman utama Manajemen Jadwal Absensi
     */
    public function index() {
        $data['judul'] = 'Manajemen Jadwal Absensi';
        $data['active_menu'] = 'jadwal';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();

        $filters = [
            'tipe_pengguna' => isset($_GET['tipe']) ? trim($_GET['tipe']) : '',
            'hari'          => isset($_GET['hari']) ? trim($_GET['hari']) : '',
            'is_active'     => isset($_GET['status']) ? trim($_GET['status']) : '',
            'keyword'       => isset($_GET['keyword']) ? trim($_GET['keyword']) : ''
        ];

        $data['filters'] = $filters;
        $data['jadwal']  = $this->model('Jadwal_model')->getAllJadwal($filters);
        $data['kelas_list'] = $this->model('Jadwal_model')->getAllKelas();

        // Hitung ringkasan jumlah jadwal
        $data['total_jadwal'] = count($data['jadwal']);
        $data['total_siswa']  = 0;
        $data['total_guru']   = 0;
        $data['total_aktif']  = 0;
        foreach ($data['jadwal'] as $j) {
            if ($j['tipe_pengguna'] === 'siswa') $data['total_siswa']++;
            if ($j['tipe_pengguna'] === 'guru') $data['total_guru']++;
            if ($j['is_active'] == 1) $data['total_aktif']++;
        }

        $this->view('layouts/dashboard_header', $data);
        $this->view('admin/jadwal/index', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Tambah jadwal baru (POST)
     */
    public function tambah() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin_jadwal');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan CSRF tidak valid. Silakan coba kembali.', 'danger');
            header('Location: ' . BASEURL . '/admin_jadwal');
            exit;
        }

        $result = $this->model('Jadwal_model')->tambahJadwal($_POST);
        if ($result['status']) {
            Flasher::setFlash('Berhasil', $result['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $result['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin_jadwal');
        exit;
    }

    /**
     * Ubah jadwal yang ada (POST)
     */
    public function ubah() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin_jadwal');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan CSRF tidak valid. Silakan coba kembali.', 'danger');
            header('Location: ' . BASEURL . '/admin_jadwal');
            exit;
        }

        $result = $this->model('Jadwal_model')->ubahJadwal($_POST);
        if ($result['status']) {
            Flasher::setFlash('Berhasil', $result['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $result['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin_jadwal');
        exit;
    }

    /**
     * Toggle status aktif/nonaktif jadwal
     */
    public function toggle($id) {
        $id = (int)$id;
        $result = $this->model('Jadwal_model')->toggleStatus($id);
        if ($result['status']) {
            Flasher::setFlash('Berhasil', $result['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $result['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin_jadwal');
        exit;
    }

    /**
     * Hapus jadwal (dengan proteksi keamanan)
     */
    public function hapus($id) {
        $id = (int)$id;
        $result = $this->model('Jadwal_model')->hapusJadwal($id);
        if ($result['status']) {
            Flasher::setFlash('Berhasil', $result['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $result['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin_jadwal');
        exit;
    }

    /**
     * Detail jadwal untuk AJAX edit modal (JSON)
     */
    public function get_detail($id) {
        header('Content-Type: application/json');
        $id = (int)$id;
        $jadwal = $this->model('Jadwal_model')->getJadwalById($id);
        if (!$jadwal) {
            http_response_code(404);
            echo json_encode(['status' => false, 'pesan' => 'Jadwal tidak ditemukan']);
            exit;
        }
        echo json_encode(['status' => true, 'data' => $jadwal]);
        exit;
    }
}
