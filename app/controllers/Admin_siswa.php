<?php

class Admin_siswa extends Controller {
    public function __construct() {
        AuthHelper::requireRole('admin');
    }

    public function index() {
        $data['judul'] = 'Data Siswa';
        $data['active_menu'] = 'siswa';
        
        // Get parameter filter kelas dari GET request jika ada
        $kelas_id = $_GET['kelas_id'] ?? '';
        $data['filter_kelas'] = $kelas_id;
        
        $data['siswa'] = $this->model('Siswa_model')->getAllSiswa($kelas_id);
        
        if (file_exists('../app/models/Kelas_model.php')) {
            $data['kelas'] = $this->model('Kelas_model')->getAllKelas();
        } else {
            $data['kelas'] = [];
        }

        $this->view('layouts/dashboard_header', $data);
        $this->view('admin/siswa/index', $data);
        $this->view('layouts/dashboard_footer');
    }

    public function tambah() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nis = $_POST['nis'] ?? '';
            $nisn = $_POST['nisn'] ?? '';
            $rfid_uid = $_POST['rfid_uid'] ?? '';

            if ($this->model('Siswa_model')->checkDuplicateNisRfid($nis, $rfid_uid)) {
                Flasher::setFlash('Gagal', 'NIS atau RFID UID sudah terdaftar.', 'danger');
                header('Location: ' . BASEURL . '/admin_siswa');
                exit;
            }

            if (!empty($nisn) && $this->model('Siswa_model')->checkDuplicateNisn($nisn)) {
                Flasher::setFlash('Gagal', 'NISN sudah terdaftar.', 'danger');
                header('Location: ' . BASEURL . '/admin_siswa');
                exit;
            }

            if ($this->model('Siswa_model')->tambahDataSiswa($_POST) > 0) {
                Flasher::setFlash('Berhasil', 'ditambahkan dan Akun otomatis dibuat (Username: NIS, Password: password).', 'success');
                header('Location: ' . BASEURL . '/admin_siswa');
                exit;
            } else {
                Flasher::setFlash('Gagal', 'ditambahkan.', 'danger');
                header('Location: ' . BASEURL . '/admin_siswa');
                exit;
            }
        }
    }

    public function ubah() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? '';
            $nis = $_POST['nis'] ?? '';
            $nisn = $_POST['nisn'] ?? '';
            $rfid_uid = $_POST['rfid_uid'] ?? '';

            if ($this->model('Siswa_model')->checkDuplicateNisRfid($nis, $rfid_uid, $id)) {
                Flasher::setFlash('Gagal', 'NIS atau RFID UID sudah digunakan siswa lain.', 'danger');
                header('Location: ' . BASEURL . '/admin_siswa');
                exit;
            }

            if (!empty($nisn) && $this->model('Siswa_model')->checkDuplicateNisn($nisn, $id)) {
                Flasher::setFlash('Gagal', 'NISN sudah digunakan siswa lain.', 'danger');
                header('Location: ' . BASEURL . '/admin_siswa');
                exit;
            }

            $this->model('Siswa_model')->ubahDataSiswa($_POST);
            Flasher::setFlash('Berhasil', 'diubah.', 'success');
            
            // Redirect kembali ke halaman dengan parameter filter jika ada
            $redirect_url = BASEURL . '/admin_siswa';
            if(isset($_POST['filter_kelas']) && !empty($_POST['filter_kelas'])) {
                $redirect_url .= '?kelas_id=' . $_POST['filter_kelas'];
            }
            
            header('Location: ' . $redirect_url);
            exit;
        }
    }

    public function hapus($id) {
        if ($this->model('Siswa_model')->hapusDataSiswa($id) > 0) {
            Flasher::setFlash('Berhasil', 'dihapus (Akun otomatis terhapus).', 'success');
        } else {
            Flasher::setFlash('Gagal', 'dihapus.', 'danger');
        }
        header('Location: ' . BASEURL . '/admin_siswa');
        exit;
    }

    public function generate_barcode($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
            
            if ($this->model('Siswa_model')->generateBarcode($id)) {
                Flasher::setFlash('Berhasil', 'Barcode digenerate.', 'success');
            } else {
                Flasher::setFlash('Gagal', 'Siswa sudah memiliki barcode atau tidak ditemukan.', 'danger');
            }
            
            // Redirect back with filter if present
            $redirect_url = BASEURL . '/admin_siswa';
            if(isset($_POST['filter_kelas']) && !empty($_POST['filter_kelas'])) {
                $redirect_url .= '?kelas_id=' . $_POST['filter_kelas'];
            }
            header('Location: ' . $redirect_url);
            exit;
        }
    }

    public function cetak_barcode($id) {
        $siswa = $this->model('Siswa_model')->getSiswaById($id);
        if (!$siswa || empty($siswa['barcode'])) {
            Flasher::setFlash('Gagal', 'Data siswa atau barcode tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin_siswa');
            exit;
        }
        
        $data['judul'] = 'Cetak Barcode Siswa';
        $data['siswa'] = $siswa;
        $this->view('admin/siswa/print_barcode', $data);
    }
}
