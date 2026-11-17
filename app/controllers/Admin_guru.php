<?php

class Admin_guru extends Controller {
    public function __construct() {
        AuthHelper::requireRole('admin');
    }

    public function index() {
        $data['judul'] = 'Data Guru';
        $data['active_menu'] = 'guru';
        $data['guru'] = $this->model('Guru_model')->getAllGuru();

        $this->view('layouts/dashboard_header', $data);
        $this->view('admin/guru/index', $data);
        $this->view('layouts/dashboard_footer');
    }

    public function tambah() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nip = trim($_POST['nip'] ?? '');
            $barcode = trim($_POST['barcode'] ?? '');

            // Jika barcode kosong, otomatis digenerate
            if (empty($barcode)) {
                $isKepsek = (isset($_POST['mata_pelajaran']) && stripos($_POST['mata_pelajaran'], 'Kepala') !== false);
                $prefix = $isKepsek ? 'BC-KEPSEK-' : 'BC-GURU-';
                $barcode = $prefix . strtoupper(bin2hex(random_bytes(3)));
                $_POST['barcode'] = $barcode;
            }

            if ($this->model('Guru_model')->checkDuplicateNipBarcode($nip, $barcode)) {
                Flasher::setFlash('Gagal', 'NIP atau Barcode sudah terdaftar.', 'danger');
                header('Location: ' . BASEURL . '/admin_guru');
                exit;
            }

            if ($this->model('Guru_model')->tambahDataGuru($_POST) > 0) {
                Flasher::setFlash('Berhasil', 'ditambahkan dengan Barcode ' . $barcode . ' (Akun login dibuat).', 'success');
                header('Location: ' . BASEURL . '/admin_guru');
                exit;
            } else {
                Flasher::setFlash('Gagal', 'ditambahkan.', 'danger');
                header('Location: ' . BASEURL . '/admin_guru');
                exit;
            }
        }
    }

    public function ubah() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? '';
            $nip = trim($_POST['nip'] ?? '');
            $barcode = trim($_POST['barcode'] ?? '');

            if ($this->model('Guru_model')->checkDuplicateNipBarcode($nip, $barcode, $id)) {
                Flasher::setFlash('Gagal', 'NIP atau Barcode sudah terdaftar oleh guru lain.', 'danger');
                header('Location: ' . BASEURL . '/admin_guru');
                exit;
            }

            $this->model('Guru_model')->ubahDataGuru($_POST);
            Flasher::setFlash('Berhasil', 'diubah.', 'success');
            header('Location: ' . BASEURL . '/admin_guru');
            exit;
        }
    }

    public function generate_barcode($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
            
            $barcode = $this->model('Guru_model')->generateBarcode($id);
            if ($barcode) {
                Flasher::setFlash('Berhasil', 'Barcode baru berhasil dibuat: ' . $barcode, 'success');
            } else {
                Flasher::setFlash('Gagal', 'Guru sudah memiliki barcode atau tidak ditemukan.', 'warning');
            }
            header('Location: ' . BASEURL . '/admin_guru');
            exit;
        }
    }

    public function cetak_barcode($id) {
        $guru = $this->model('Guru_model')->getGuruById($id);
        if (!$guru || empty($guru['barcode'])) {
            Flasher::setFlash('Gagal', 'Data guru atau barcode tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin_guru');
            exit;
        }
        
        $data['judul'] = 'Cetak Kartu Absensi Guru & Kepala Sekolah';
        $data['guru'] = $guru;
        $this->view('admin/guru/print_barcode', $data);
    }

    public function hapus($id) {
        if ($this->model('Guru_model')->hapusDataGuru($id) > 0) {
            Flasher::setFlash('Berhasil', 'dihapus (Akun otomatis terhapus).', 'success');
            header('Location: ' . BASEURL . '/admin_guru');
            exit;
        } else {
            Flasher::setFlash('Gagal', 'dihapus.', 'danger');
            header('Location: ' . BASEURL . '/admin_guru');
            exit;
        }
    }
}
