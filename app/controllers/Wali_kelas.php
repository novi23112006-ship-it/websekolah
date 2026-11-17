<?php

class Wali_kelas extends Controller {
    public function __construct() {
        AuthHelper::requireRole('wali_kelas');
    }

    /**
     * Dashboard Wali Kelas
     */
    public function index() {
        $data['judul'] = 'Dashboard Wali Kelas';
        $data['active_menu'] = 'dashboard';
        
        $data['stats'] = $this->model('Dashboard_model')->getWaliKelasStats($_SESSION['user_id']);

        $this->view('layouts/dashboard_header', $data);
        $this->view('wali_kelas/dashboard', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Data Siswa di kelas yang diampu
     */
    public function siswa() {
        $data['judul'] = 'Data Siswa Kelas';
        $data['active_menu'] = 'data_siswa';
        
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : null;
        $data['siswa'] = $this->model('Wali_kelas_model')->getSiswaByWali($_SESSION['user_id'], $keyword);
        $data['kelas'] = $this->model('Wali_kelas_model')->getKelasByUserId($_SESSION['user_id']);
        $data['keyword'] = $keyword;

        $this->view('layouts/dashboard_header', $data);
        $this->view('wali_kelas/siswa', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Absensi Kelas Hari Ini / Pertanggal
     */
    public function absensi() {
        $data['judul'] = 'Absensi Kelas';
        $data['active_menu'] = 'absensi_kelas';
        
        $tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $result = $this->model('Wali_kelas_model')->getAbsensiKelas($_SESSION['user_id'], $tanggal, $status);
        $data['kelas'] = $result['kelas'];
        $data['tanggal'] = $result['tanggal'];
        $data['siswa'] = $result['siswa'];
        $data['status'] = $status;

        $this->view('layouts/dashboard_header', $data);
        $this->view('wali_kelas/absensi', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Catatan Siswa: /wali-kelas/catatan-siswa and /wali-kelas/catatan-siswa/{id}
     */
    public function catatan_siswa($param1 = null, $param2 = null) {
        if ($param1 === 'add') {
            $this->add_catatan();
            return;
        }

        if ($param1 === 'edit') {
            $id = $param2;
            $this->edit_catatan($id);
            return;
        }

        if ($param1 === 'delete') {
            $id = $param2;
            $this->delete_catatan($id);
            return;
        }

        if (is_numeric($param1)) {
            // Detail view: /wali-kelas/catatan-siswa/{id}
            $this->detail_catatan($param1);
            return;
        }

        // List view: /wali-kelas/catatan-siswa
        $data['judul'] = 'Catatan Siswa';
        $data['active_menu'] = 'catatan_siswa';

        $siswa_id = isset($_GET['siswa_id']) ? $_GET['siswa_id'] : null;
        $kategori = isset($_GET['kategori']) ? $_GET['kategori'] : null;

        $data['catatan'] = $this->model('Wali_kelas_model')->getCatatanSiswa($_SESSION['user_id'], $siswa_id, $kategori);
        $data['siswa_list'] = $this->model('Wali_kelas_model')->getSiswaByWali($_SESSION['user_id']);
        $data['kelas'] = $this->model('Wali_kelas_model')->getKelasByUserId($_SESSION['user_id']);
        $data['siswa_id'] = $siswa_id;
        $data['kategori'] = $kategori;
        $data['csrf_token'] = AuthHelper::generateCsrfToken();

        $this->view('layouts/dashboard_header', $data);
        $this->view('wali_kelas/catatan_siswa', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Alias method for /wali_kelas/catatan
     */
    public function catatan($param1 = null, $param2 = null) {
        $this->catatan_siswa($param1, $param2);
    }

    /**
     * Detail Catatan Siswa
     */
    public function detail_catatan($id) {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $catatan = $this->model('Wali_kelas_model')->getCatatanById($_SESSION['user_id'], $id);

        if (!$catatan) {
            Flasher::setFlash('gagal', 'Catatan siswa tidak ditemukan atau Anda tidak memiliki akses.', 'danger');
            header('Location: ' . BASEURL . '/wali-kelas/catatan-siswa');
            exit;
        }

        $data['judul'] = 'Detail Catatan Siswa';
        $data['active_menu'] = 'catatan_siswa';
        $data['catatan'] = $catatan;
        $data['csrf_token'] = AuthHelper::generateCsrfToken();

        $this->view('layouts/dashboard_header', $data);
        $this->view('wali_kelas/catatan_detail', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Tambah Catatan Siswa (POST)
     */
    public function add_catatan() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

            $siswa_id = filter_var($_POST['siswa_id'], FILTER_SANITIZE_NUMBER_INT);
            $kategori = filter_var($_POST['kategori'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $tanggal = filter_var($_POST['tanggal'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $catatan = filter_var($_POST['catatan'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if (empty($siswa_id) || empty($kategori) || empty($tanggal) || empty($catatan)) {
                Flasher::setFlash('gagal', 'Semua field (Siswa, Kategori, Tanggal, Catatan) wajib diisi.', 'danger');
                header('Location: ' . BASEURL . '/wali-kelas/catatan-siswa');
                exit;
            }

            $data = [
                'siswa_id' => $siswa_id,
                'kategori' => $kategori,
                'tanggal' => $tanggal,
                'catatan' => $catatan
            ];

            if ($this->model('Wali_kelas_model')->addCatatanSiswa($_SESSION['user_id'], $data)) {
                Flasher::setFlash('berhasil', 'Catatan siswa berhasil ditambahkan.', 'success');
            } else {
                Flasher::setFlash('gagal', 'Gagal menambahkan catatan siswa (akses ditolak/data tidak valid).', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/wali-kelas/catatan-siswa');
        exit;
    }

    /**
     * Edit Catatan Siswa (POST)
     */
    public function edit_catatan($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');
            
            $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT) : filter_var($id, FILTER_SANITIZE_NUMBER_INT);
            $siswa_id = filter_var($_POST['siswa_id'], FILTER_SANITIZE_NUMBER_INT);
            $kategori = filter_var($_POST['kategori'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $tanggal = filter_var($_POST['tanggal'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $catatan = filter_var($_POST['catatan'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if (empty($id) || empty($siswa_id) || empty($kategori) || empty($tanggal) || empty($catatan)) {
                Flasher::setFlash('gagal', 'Semua field wajib diisi.', 'danger');
                header('Location: ' . BASEURL . '/wali-kelas/catatan-siswa');
                exit;
            }

            $data = [
                'siswa_id' => $siswa_id,
                'kategori' => $kategori,
                'tanggal' => $tanggal,
                'catatan' => $catatan
            ];

            if ($this->model('Wali_kelas_model')->updateCatatanSiswa($_SESSION['user_id'], $id, $data)) {
                Flasher::setFlash('berhasil', 'Catatan siswa berhasil diperbarui.', 'success');
            } else {
                Flasher::setFlash('gagal', 'Gagal memperbarui catatan siswa (akses ditolak/data tidak valid).', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/wali-kelas/catatan-siswa');
        exit;
    }

    /**
     * Hapus Catatan Siswa (POST)
     */
    public function delete_catatan($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            AuthHelper::verifyCsrfToken($_POST['csrf_token'] ?? '');

            if (!$id && isset($_POST['id'])) {
                $id = $_POST['id'];
            }
            $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

            if ($this->model('Wali_kelas_model')->deleteCatatanSiswa($_SESSION['user_id'], $id)) {
                Flasher::setFlash('berhasil', 'Catatan siswa berhasil dihapus.', 'success');
            } else {
                Flasher::setFlash('gagal', 'Gagal menghapus catatan siswa (akses ditolak/data tidak valid).', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/wali-kelas/catatan-siswa');
        exit;
    }

    /**
     * Rekap Absensi Kelas (TAHAP 10): /wali-kelas/rekap-absensi & /wali-kelas/rekap-absensi/siswa/{id}
     */
    public function rekap_absensi($param1 = null, $param2 = null) {
        if ($param1 === 'siswa' && is_numeric($param2)) {
            $this->detail_rekap_siswa($param2);
            return;
        }

        if (is_numeric($param1)) {
            $this->detail_rekap_siswa($param1);
            return;
        }

        $data['judul'] = 'Rekap Absensi Kelas';
        $data['active_menu'] = 'rekap_absensi';

        $tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
        $tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-t');
        $siswa_id = isset($_GET['siswa_id']) ? $_GET['siswa_id'] : null;
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $result = $this->model('Wali_kelas_model')->getRekapAbsensi($_SESSION['user_id'], $tgl_mulai, $tgl_akhir, $siswa_id, $status);
        $data['kelas'] = $result['kelas'];
        $data['tgl_mulai'] = $result['tgl_mulai'];
        $data['tgl_akhir'] = $result['tgl_akhir'];
        $data['siswa_id'] = $siswa_id;
        $data['status'] = $status;
        $data['ringkasan'] = $result['ringkasan'];
        $data['rekap'] = $result['rekap'];
        $data['siswa_list'] = $this->model('Wali_kelas_model')->getSiswaByWali($_SESSION['user_id']);

        $this->view('layouts/dashboard_header', $data);
        $this->view('wali_kelas/rekap_absensi', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Detail Riwayat Absensi Siswa Spesifik (TAHAP 10)
     */
    public function detail_rekap_siswa($siswa_id) {
        $siswa_id = filter_var($siswa_id, FILTER_SANITIZE_NUMBER_INT);

        $tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : null;
        $tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : null;
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $detail = $this->model('Wali_kelas_model')->getDetailAbsensiSiswaByWali($_SESSION['user_id'], $siswa_id, $tgl_mulai, $tgl_akhir, $status);

        if (!$detail) {
            Flasher::setFlash('gagal', 'Data siswa tidak ditemukan atau Anda tidak memiliki akses ke kelas siswa ini.', 'danger');
            header('Location: ' . BASEURL . '/wali-kelas/rekap-absensi');
            exit;
        }

        $data['judul'] = 'Detail Riwayat Absensi Siswa';
        $data['active_menu'] = 'rekap_absensi';
        $data['kelas'] = $detail['kelas'];
        $data['siswa'] = $detail['siswa'];
        $data['riwayat'] = $detail['riwayat'];
        $data['stats'] = $detail['stats'];
        $data['tgl_mulai'] = $tgl_mulai;
        $data['tgl_akhir'] = $tgl_akhir;
        $data['status'] = $status;

        $this->view('layouts/dashboard_header', $data);
        $this->view('wali_kelas/rekap_detail_siswa', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Alias method for /wali_kelas/rekap
     */
    public function rekap($param1 = null, $param2 = null) {
        $this->rekap_absensi($param1, $param2);
    }

    /**
     * Profil Wali Kelas
     */
    public function profil() {
        $data['judul'] = 'Profil Saya';
        $data['active_menu'] = 'profil';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();
        $data['profil'] = $this->model('User_model')->getProfil($_SESSION['user_id']);

        $this->view('layouts/dashboard_header', $data);
        $this->view('wali_kelas/profil', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Cetak Kartu Absensi Barcode Wali Kelas
     */
    public function cetak_barcode() {
        $guru = $this->model('User_model')->getProfil($_SESSION['user_id']);
        if (!$guru || empty($guru['barcode'])) {
            Flasher::setFlash('Gagal', 'Data profil atau barcode Anda belum tersedia.', 'danger');
            header('Location: ' . BASEURL . '/wali_kelas/profil');
            exit;
        }

        $data['judul'] = 'Cetak Kartu Absensi Guru & Wali Kelas';
        $data['guru'] = $guru;
        $this->view('admin/guru/print_barcode', $data);
    }

    public function update_foto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wali_kelas/profil');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/wali_kelas/profil');
            exit;
        }

        $file = $_FILES['foto'] ?? null;
        $hasil = $this->model('User_model')->updateFoto($_SESSION['user_id'], $file);

        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/wali_kelas/profil');
        exit;
    }

    public function hapus_foto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/wali_kelas/profil');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/wali_kelas/profil');
            exit;
        }

        $hasil = $this->model('User_model')->deleteFoto($_SESSION['user_id']);

        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/wali_kelas/profil');
        exit;
    }
}
