<?php

class Guru_absensi extends Controller {

    public function __construct() {
        // Hanya admin dan guru yang dapat mengakses terminal absensi guru
        AuthHelper::requireOneOfRoles(['admin', 'guru']);
    }

    public function index() {
        $data['judul'] = 'Absensi Guru';
        $data['active_menu'] = 'absensi_guru_scanner';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();
        $data['lokasi_sekolah'] = LokasiHelper::getPengaturanLokasi();

        $this->view('layouts/dashboard_header', $data);
        $this->view('guru_absensi/index', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Endpoint POST — mengembalikan JSON untuk AJAX.
     * Mencari guru berdasarkan guru.barcode (bukan siswa.barcode).
     */
    public function scan() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => false, 'pesan' => 'Method tidak diizinkan.']);
            exit;
        }

        // CSRF check
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            http_response_code(403);
            echo json_encode(['status' => false, 'pesan' => 'Token keamanan tidak valid.']);
            exit;
        }

        // Validasi Lokasi Geofencing (Maksimal 100 meter dari sekolah)
        $userLat = $_POST['latitude'] ?? null;
        $userLng = $_POST['longitude'] ?? null;
        $cekLokasi = LokasiHelper::validasiJarak($userLat, $userLng, $_SESSION['role'] ?? null);
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

        // Validasi barcode input
        $barcode = trim($_POST['barcode'] ?? '');
        if (empty($barcode)) {
            echo json_encode(['status' => false, 'pesan' => 'Barcode tidak boleh kosong.']);
            exit;
        }

        // Sanitasi: hanya izinkan karakter alfanumerik dan tanda strip
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $barcode)) {
            echo json_encode(['status' => false, 'pesan' => 'Format barcode tidak valid.']);
            exit;
        }

        // Mode absensi: auto (default), masuk, atau pulang
        $mode = strtolower(trim($_POST['mode'] ?? 'auto'));
        if (!in_array($mode, ['auto', 'masuk', 'pulang'])) {
            $mode = 'auto';
        }

        $hasil = $this->model('Absensi_model')->catatAbsensiGuruBarcode($barcode, null, $mode);
        if (is_array($hasil)) {
            $hasil['jarak'] = $cekLokasi['jarak'];
        }

        echo json_encode($hasil);
        exit;
    }
}
