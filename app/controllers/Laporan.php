<?php

class Laporan extends Controller {

    private $per_page = 15;

    public function index() {
        AuthHelper::requireLogin();
        $role = $_SESSION['role'];
        if ($role === 'guru') {
            header('Location: ' . BASEURL . '/laporan/guru');
            exit;
        }
        header('Location: ' . BASEURL . '/laporan/siswa');
        exit;
    }

    /**
     * Bangun role_context berdasarkan role pengguna yang sedang login.
     */
    private function getRoleContext() {
        $role = $_SESSION['role'];
        $user_id = $_SESSION['user_id'];
        $context = ['role' => $role];

        $kelas_id = $this->model('Laporan_model')->getKelasIdByUserId($user_id);
        if ($kelas_id) {
            $context['kelas_id'] = $kelas_id;
        }

        if ($role === 'wali_kelas') {
            $context['guru_id'] = $this->model('Laporan_model')->getGuruIdByUserId($user_id);
        } elseif ($role === 'guru') {
            $context['guru_id'] = $this->model('Laporan_model')->getGuruIdByUserId($user_id);
        } elseif ($role === 'siswa') {
            $context['siswa_id'] = $this->model('Laporan_model')->getSiswaIdByUserId($user_id);
        }

        return $context;
    }

    public function siswa() {
        AuthHelper::requireLogin();
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        // Cek apakah user adalah wali kelas (role 'wali_kelas' atau ditugaskan sebagai wali kelas di tabel kelas)
        $isWali = ($role === 'wali_kelas') || !empty($this->model('Laporan_model')->getKelasIdByUserId($user_id));

        // Hanya admin, wali_kelas, dan siswa yang boleh mengakses laporan siswa (Kepala Sekolah dibatasi hanya data guru)
        if (!in_array($role, ['admin', 'siswa']) && !$isWali) {
            if ($role === 'kepala_sekolah') {
                Flasher::setFlash('Akses Dibatasi', 'Akun Kepala Sekolah dibatasi hanya dapat melihat rekap data guru.', 'warning');
                header('Location: ' . BASEURL . '/kepala_sekolah/rekap_guru');
                exit;
            }
            header('Location: ' . BASEURL . '/auth/forbidden');
            exit;
        }

        $role_context = $this->getRoleContext();
        if ($isWali && $role !== 'admin') {
            $role_context['role'] = 'wali_kelas';
        }

        // Filter dari GET
        $filters = [
            'tanggal_mulai' => $_GET['tanggal_mulai'] ?? '',
            'tanggal_akhir' => $_GET['tanggal_akhir'] ?? '',
            'kelas_id'      => $_GET['kelas_id'] ?? '',
            'siswa_id'      => $_GET['siswa_id'] ?? '',
            'status'        => $_GET['status'] ?? '',
        ];

        // Handle export Excel (Hanya untuk non-siswa seperti admin & wali_kelas)
        if (isset($_GET['export']) && $_GET['export'] === 'excel') {
            if ($role === 'siswa') {
                header('Location: ' . BASEURL . '/laporan/siswa');
                exit;
            }
            $this->exportSiswaExcel($filters, $role_context);
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));

        $model = $this->model('Laporan_model');
        $total = $model->countLaporanSiswa($filters, $role_context);
        $data['laporan'] = $model->getLaporanSiswa($filters, $page, $this->per_page, $role_context);
        $data['kelas'] = $model->getAllKelasForFilter();

        // Untuk role tertentu, sediakan dropdown siswa
        if (in_array($role, ['admin', 'kepala_sekolah'])) {
            $data['siswa_list'] = $model->getAllSiswaForFilter($filters['kelas_id']);
        } elseif ($isWali) {
            $data['siswa_list'] = $model->getAllSiswaForFilter($role_context['kelas_id'] ?? null);
        } else {
            $data['siswa_list'] = [];
        }

        // Pagination info
        $data['total'] = $total;
        $data['page'] = $page;
        $data['per_page'] = $this->per_page;
        $data['total_pages'] = ceil($total / $this->per_page);
        $data['filters'] = $filters;
        $data['role_context'] = $role_context;
        $data['judul'] = 'Riwayat Absen Siswa';
        $data['active_menu'] = 'laporan_siswa';

        $this->view('layouts/dashboard_header', $data);
        $this->view('laporan/siswa', $data);
        $this->view('layouts/dashboard_footer');
    }

    public function guru() {
        // Guru, wali_kelas, admin, kepala_sekolah bisa akses
        $allowed = ['admin', 'kepala_sekolah', 'guru', 'wali_kelas'];
        AuthHelper::requireOneOfRoles($allowed);

        $role_context = $this->getRoleContext();

        $filters = [
            'tanggal_mulai' => $_GET['tanggal_mulai'] ?? '',
            'tanggal_akhir' => $_GET['tanggal_akhir'] ?? '',
            'guru_id'       => $_GET['guru_id'] ?? '',
            'status'        => $_GET['status'] ?? '',
        ];

        // Handle export Excel
        if (isset($_GET['export']) && $_GET['export'] === 'excel') {
            $this->exportGuruExcel($filters, $role_context);
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));

        $model = $this->model('Laporan_model');
        $total = $model->countLaporanGuru($filters, $role_context);
        $data['laporan'] = $model->getLaporanGuru($filters, $page, $this->per_page, $role_context);
        $data['guru_list'] = (in_array($_SESSION['role'], ['admin', 'kepala_sekolah'])) 
            ? $model->getAllGuruForFilter() 
            : [];

        $data['total'] = $total;
        $data['page'] = $page;
        $data['per_page'] = $this->per_page;
        $data['total_pages'] = ceil($total / $this->per_page);
        $data['filters'] = $filters;
        $data['role_context'] = $role_context;
        $data['judul'] = 'Riwayat Absen Guru';
        $data['active_menu'] = 'laporan_guru';

        $this->view('layouts/dashboard_header', $data);
        $this->view('laporan/guru', $data);
        $this->view('layouts/dashboard_footer');
    }

    // --- Print views (standalone, no sidebar) ---
    public function printSiswa() {
        AuthHelper::requireLogin();
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        $isWali = ($role === 'wali_kelas') || !empty($this->model('Laporan_model')->getKelasIdByUserId($user_id));

        // Siswa tidak memiliki akses cetak riwayat absensi
        if ($role === 'siswa' || (!in_array($role, ['admin']) && !$isWali)) {
            header('Location: ' . BASEURL . ($role === 'kepala_sekolah' ? '/kepala_sekolah/rekap_guru' : '/laporan/siswa'));
            exit;
        }

        $role_context = $this->getRoleContext();
        if ($isWali && $role !== 'admin') {
            $role_context['role'] = 'wali_kelas';
        }

        $filters = [
            'tanggal_mulai' => $_GET['tanggal_mulai'] ?? '',
            'tanggal_akhir' => $_GET['tanggal_akhir'] ?? '',
            'kelas_id'      => $_GET['kelas_id'] ?? '',
            'siswa_id'      => $_GET['siswa_id'] ?? '',
            'status'        => $_GET['status'] ?? '',
        ];

        $model = $this->model('Laporan_model');
        $data['laporan'] = $model->getAllLaporanSiswaForExport($filters, $role_context);
        $data['filters'] = $filters;
        $data['judul'] = 'Riwayat Absen Siswa';

        $this->view('laporan/print_siswa', $data);
    }

    public function printGuru() {
        $allowed = ['admin', 'kepala_sekolah', 'guru', 'wali_kelas'];
        AuthHelper::requireOneOfRoles($allowed);

        $role_context = $this->getRoleContext();
        $filters = [
            'tanggal_mulai' => $_GET['tanggal_mulai'] ?? '',
            'tanggal_akhir' => $_GET['tanggal_akhir'] ?? '',
            'guru_id'       => $_GET['guru_id'] ?? '',
            'status'        => $_GET['status'] ?? '',
        ];

        $model = $this->model('Laporan_model');
        $data['laporan'] = $model->getAllLaporanGuruForExport($filters, $role_context);
        $data['filters'] = $filters;
        $data['judul'] = 'Riwayat Absen Guru';

        $this->view('laporan/print_guru', $data);
    }

    // --- Export Excel ---
    private function exportSiswaExcel($filters, $role_context) {
        $data = $this->model('Laporan_model')->getAllLaporanSiswaForExport($filters, $role_context);
        
        $filename = 'laporan_absensi_siswa_' . date('Ymd') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo '<table border="1">';
        echo '<tr><th>No</th><th>Tanggal</th><th>NIS</th><th>Nama Siswa</th><th>Kelas</th><th>Jam Masuk</th><th>Status</th><th>Keterangan</th></tr>';
        $no = 1;
        foreach ($data as $row) {
            echo '<tr>';
            echo '<td>' . $no++ . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['nis']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nama_kelas'] ?? '-') . '</td>';
            echo '<td>' . ($row['jam_masuk'] ?? '-') . '</td>';
            echo '<td>' . ucfirst($row['status']) . '</td>';
            echo '<td>' . htmlspecialchars($row['keterangan'] ?? '') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }

    private function exportGuruExcel($filters, $role_context) {
        $data = $this->model('Laporan_model')->getAllLaporanGuruForExport($filters, $role_context);
        
        $filename = 'laporan_absensi_guru_' . date('Ymd') . '.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo '<table border="1">';
        echo '<tr><th>No</th><th>Tanggal</th><th>NIP</th><th>Nama Guru</th><th>Mata Pelajaran</th><th>Jam Scan</th><th>Status</th><th>Keterangan</th></tr>';
        $no = 1;
        foreach ($data as $row) {
            echo '<tr>';
            echo '<td>' . $no++ . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['nip'] ?? '-') . '</td>';
            echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
            echo '<td>' . htmlspecialchars($row['mata_pelajaran'] ?? '-') . '</td>';
            echo '<td>' . ($row['jam_scan'] ?? '-') . '</td>';
            echo '<td>' . ucfirst($row['status']) . '</td>';
            echo '<td>' . htmlspecialchars($row['keterangan'] ?? '') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }
}
