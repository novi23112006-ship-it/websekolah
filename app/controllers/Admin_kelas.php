<?php

class Admin_kelas extends Controller {
    public function __construct() {
        AuthHelper::requireRole('admin');
    }

    public function index() {
        $data['judul'] = 'Data Kelas';
        $data['active_menu'] = 'kelas';
        $data['kelas'] = $this->model('Kelas_model')->getAllKelas();
        
        // Perlu data guru untuk opsi Wali Kelas di form modal
        // Karena Guru_model belum dibuat, sementara kita asumsikan namanya
        if (file_exists('../app/models/Guru_model.php')) {
            $data['guru'] = $this->model('Guru_model')->getAllGuru();
        } else {
            $data['guru'] = []; // Fallback
        }

        $this->view('layouts/dashboard_header', $data);
        $this->view('admin/kelas/index', $data);
        $this->view('layouts/dashboard_footer');
    }

    public function tambah() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nama_kelas = $_POST['nama_kelas'] ?? '';

            if ($this->model('Kelas_model')->checkNamaKelasExists($nama_kelas)) {
                Flasher::setFlash('Gagal', 'Nama Kelas sudah digunakan.', 'danger');
                header('Location: ' . BASEURL . '/admin_kelas');
                exit;
            }

            if ($this->model('Kelas_model')->tambahDataKelas($_POST) > 0) {
                Flasher::setFlash('Berhasil', 'ditambahkan.', 'success');
                header('Location: ' . BASEURL . '/admin_kelas');
                exit;
            } else {
                Flasher::setFlash('Gagal', 'ditambahkan.', 'danger');
                header('Location: ' . BASEURL . '/admin_kelas');
                exit;
            }
        }
    }

    public function ubah() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? '';
            $nama_kelas = $_POST['nama_kelas'] ?? '';

            if ($this->model('Kelas_model')->checkNamaKelasExists($nama_kelas, $id)) {
                Flasher::setFlash('Gagal', 'Nama Kelas sudah digunakan.', 'danger');
                header('Location: ' . BASEURL . '/admin_kelas');
                exit;
            }

            // Ubah mengembalikan rowCount, bisa 0 jika tidak ada yg diubah. Tapi tidak berarti error.
            $this->model('Kelas_model')->ubahDataKelas($_POST);
            Flasher::setFlash('Berhasil', 'diubah.', 'success');
            header('Location: ' . BASEURL . '/admin_kelas');
            exit;
        }
    }

    public function hapus($id) {
        if ($this->model('Kelas_model')->hapusDataKelas($id) > 0) {
            Flasher::setFlash('Berhasil', 'dihapus.', 'success');
            header('Location: ' . BASEURL . '/admin_kelas');
            exit;
        } else {
            Flasher::setFlash('Gagal', 'dihapus.', 'danger');
            header('Location: ' . BASEURL . '/admin_kelas');
            exit;
        }
    }

    /**
     * Otomatisasi Kenaikan / Pindah Kelas Massal
     */
    public function naik_kelas_massal() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipe_aksi = $_POST['tipe_aksi'] ?? 'naik'; // 'naik' atau 'lulus'
            $kelas_asal_id = (int)($_POST['kelas_asal_id'] ?? 0);
            $kelas_tujuan_id = (int)($_POST['kelas_tujuan_id'] ?? 0);

            if ($kelas_asal_id <= 0) {
                Flasher::setFlash('Gagal', 'Silakan pilih kelas asal terlebih dahulu.', 'danger');
                header('Location: ' . BASEURL . '/admin_kelas');
                exit;
            }

            $model = $this->model('Kelas_model');
            $asal = $model->getKelasById($kelas_asal_id);
            if (!$asal) {
                Flasher::setFlash('Gagal', 'Kelas asal tidak ditemukan.', 'danger');
                header('Location: ' . BASEURL . '/admin_kelas');
                exit;
            }

            $totalSiswaAsal = $model->countSiswaByKelasId($kelas_asal_id);
            if ($totalSiswaAsal === 0) {
                Flasher::setFlash('Perhatian', "Tidak ada siswa aktif di kelas {$asal['nama_kelas']} untuk dipindahkan.", 'warning');
                header('Location: ' . BASEURL . '/admin_kelas');
                exit;
            }

            if ($tipe_aksi === 'lulus') {
                $affected = $model->luluskanKelasMassal($kelas_asal_id);
                Flasher::setFlash('Berhasil', "Proses kelulusan selesai. Sebanyak {$affected} siswa dari {$asal['nama_kelas']} telah diluluskan (status nonaktif/alumni).", 'success');
            } else {
                if ($kelas_tujuan_id <= 0) {
                    Flasher::setFlash('Gagal', 'Silakan pilih kelas tujuan untuk kenaikan/pindah kelas.', 'danger');
                    header('Location: ' . BASEURL . '/admin_kelas');
                    exit;
                }

                if ($kelas_asal_id === $kelas_tujuan_id) {
                    Flasher::setFlash('Gagal', 'Kelas asal dan kelas tujuan tidak boleh sama.', 'danger');
                    header('Location: ' . BASEURL . '/admin_kelas');
                    exit;
                }

                $tujuan = $model->getKelasById($kelas_tujuan_id);
                if (!$tujuan) {
                    Flasher::setFlash('Gagal', 'Kelas tujuan tidak ditemukan.', 'danger');
                    header('Location: ' . BASEURL . '/admin_kelas');
                    exit;
                }

                $affected = $model->naikKelasMassal($kelas_asal_id, $kelas_tujuan_id);
                Flasher::setFlash('Berhasil', "Otomatisasi kenaikan kelas berhasil! Sebanyak {$affected} siswa dari {$asal['nama_kelas']} berhasil dipindahkan ke {$tujuan['nama_kelas']}.", 'success');
            }

            header('Location: ' . BASEURL . '/admin_kelas');
            exit;
        }

        header('Location: ' . BASEURL . '/admin_kelas');
        exit;
    }

    /**
     * Endpoint API JSON untuk melihat siswa di suatu kelas
     */
    public function get_siswa_kelas($kelas_id) {
        header('Content-Type: application/json');
        $kelas_id = (int)$kelas_id;
        $siswa = $this->model('Kelas_model')->getSiswaByKelasId($kelas_id);
        echo json_encode([
            'status' => 'success',
            'count' => count($siswa),
            'data' => $siswa
        ]);
        exit;
    }
}
