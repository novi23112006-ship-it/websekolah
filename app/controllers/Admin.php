<?php

class Admin extends Controller {
    public function __construct() {
        AuthHelper::requireRole('admin');
    }

    public function index() {
        $data['judul'] = 'Dashboard Admin';
        $data['active_menu'] = 'dashboard';
        
        $dashboardModel = $this->model('Dashboard_model');
        $data['stats'] = $dashboardModel->getAdminStats();
        $data['rekap_kelas'] = $dashboardModel->getStatistikPerKelas();
        $data['jadwal_kbm'] = $dashboardModel->getJadwalKBMHariIni();
        $data['absensi_siswa'] = $dashboardModel->getAdminLatestAbsensiSiswa(10);
        $data['absensi_guru'] = $dashboardModel->getAdminLatestAbsensiGuru(10);

        $this->view('layouts/dashboard_header', $data);
        $this->view('admin/dashboard', $data);
        $this->view('layouts/dashboard_footer');
    }

    public function profil() {
        $data['judul'] = 'Profil Admin';
        $data['active_menu'] = 'profil';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();
        $data['profil'] = $this->model('User_model')->getProfil($_SESSION['user_id']);

        $this->view('layouts/dashboard_header', $data);
        $this->view('admin/profil', $data);
        $this->view('layouts/dashboard_footer');
    }

    public function update_foto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/profil');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/profil');
            exit;
        }

        $file = $_FILES['foto'] ?? null;
        $hasil = $this->model('User_model')->updateFoto($_SESSION['user_id'], $file);

        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/admin/profil');
        header('Location: ' . $redirect);
        exit;
    }

    public function hapus_foto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan');
            exit;
        }

        $hasil = $this->model('User_model')->deleteFoto($_SESSION['user_id']);

        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/admin/pengaturan');
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Halaman Pengaturan Akun Admin
     */
    public function pengaturan() {
        $data['judul'] = 'Pengaturan Akun & Sistem';
        $data['active_menu'] = 'pengaturan';
        $data['csrf_token'] = AuthHelper::generateCsrfToken();
        $data['admin'] = $this->model('User_model')->getUserById($_SESSION['user_id']);
        $data['users_list'] = $this->model('User_model')->getAllUsers(30);
        $data['lokasi'] = LokasiHelper::getPengaturanLokasi();
        $data['telegram'] = TelegramHelper::getConfigStatus();
        $data['chatbot'] = ChatbotHelper::getConfigStatus();

        $this->view('layouts/dashboard_header', $data);
        $this->view('admin/pengaturan', $data);
        $this->view('layouts/dashboard_footer');
    }

    /**
     * Uji Koneksi API Key Chatbot AI Multi-Provider via AJAX / POST oleh Admin (berdasarkan ID atau input form)
     */
    public function chatbot_check() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => false, 'pesan' => 'Token keamanan (CSRF) tidak valid. Silakan refresh halaman.']);
            exit;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            $hasil = ChatbotHelper::testConnectionById($id);
        } else {
            $provider = !empty($_POST['provider']) ? trim($_POST['provider']) : null;
            $apiKey   = !empty($_POST['api_key']) ? trim($_POST['api_key']) : null;
            $model    = !empty($_POST['model']) ? trim($_POST['model']) : null;
            $baseUrl  = isset($_POST['base_url']) ? trim($_POST['base_url']) : null;

            $hasil = ChatbotHelper::testConnection($provider, $apiKey, $model, $baseUrl);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($hasil);
        exit;
    }

    /**
     * Tambah Konfigurasi API Key AI Baru ke Daftar Manajemen
     */
    public function chatbot_add_config() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan (CSRF) tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $label       = trim($_POST['label'] ?? '');
        $provider    = strtolower(trim($_POST['provider'] ?? 'gemini'));
        $apiKey      = trim($_POST['api_key'] ?? '');
        $model       = trim($_POST['model'] ?? '');
        $baseUrl     = trim($_POST['base_url'] ?? '');
        $temperature = isset($_POST['temperature']) ? (float)$_POST['temperature'] : 0.2;
        $maxTokens   = isset($_POST['max_tokens']) ? (int)$_POST['max_tokens'] : 350;

        if (empty($label)) {
            $label = ucfirst($provider) . ' Config ' . date('d-m-Y');
        }

        if (empty($model)) {
            $adapter = AiProviderFactory::getAdapter($provider);
            $model = $adapter->getDefaultModel();
        }

        $isLocal = str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1');
        if (empty($apiKey) && !$isLocal) {
            Flasher::setFlash('Gagal', 'API Key tidak boleh kosong.', 'warning');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        // Uji koneksi sebelum menambahkan konfigurasi baru
        $test = ChatbotHelper::testConnection($provider, $apiKey, $model, $baseUrl);
        if (!$test['status']) {
            $errMsg = htmlspecialchars($test['pesan'] ?? 'Koneksi gagal');
            Flasher::setFlash('Validasi Gagal', "Konfigurasi tidak dapat disimpan karena uji koneksi gagal: {$errMsg}", 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $aiModel = $this->model('Ai_model');
        $newId = $aiModel->addConfiguration([
            'label'       => $label,
            'provider'    => $provider,
            'api_key'     => $apiKey,
            'model'       => $model,
            'base_url'    => $baseUrl,
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens
        ]);

        if ($newId) {
            $aiModel->updateTestStatus($newId, 'success', null);
            Flasher::setFlash('Berhasil', "Konfigurasi API Key '{$label}' berhasil ditambahkan dan diverifikasi.", 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal menyimpan konfigurasi ke basis data.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
        exit;
    }

    /**
     * Edit / Perbarui Konfigurasi API Key AI Tertentu
     */
    public function chatbot_update_config() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan (CSRF) tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $id          = (int)($_POST['id'] ?? 0);
        $label       = trim($_POST['label'] ?? '');
        $provider    = strtolower(trim($_POST['provider'] ?? 'gemini'));
        $apiKey      = trim($_POST['api_key'] ?? '');
        $model       = trim($_POST['model'] ?? '');
        $baseUrl     = trim($_POST['base_url'] ?? '');
        $temperature = isset($_POST['temperature']) ? (float)$_POST['temperature'] : 0.2;
        $maxTokens   = isset($_POST['max_tokens']) ? (int)$_POST['max_tokens'] : 350;

        $aiModel = $this->model('Ai_model');
        $existing = $aiModel->getConfigurationById($id);
        if (!$existing) {
            Flasher::setFlash('Gagal', 'Data konfigurasi tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $keyToTest = !empty($apiKey) ? $apiKey : $existing['api_key'];
        if (empty($model)) {
            $adapter = AiProviderFactory::getAdapter($provider);
            $model = $adapter->getDefaultModel();
        }

        // Uji koneksi dengan parameter baru sebelum menyimpan
        $test = ChatbotHelper::testConnection($provider, $keyToTest, $model, $baseUrl);
        if (!$test['status']) {
            $errMsg = htmlspecialchars($test['pesan'] ?? 'Koneksi gagal');
            Flasher::setFlash('Validasi Gagal', "Perubahan tidak dapat disimpan karena uji koneksi gagal: {$errMsg}. Konfigurasi sebelumnya dipertahankan.", 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $updated = $aiModel->updateConfiguration($id, [
            'label'       => $label ?: $existing['label'],
            'provider'    => $provider,
            'api_key'     => $apiKey, // jika kosong, model akan mempertahankan key lama
            'model'       => $model,
            'base_url'    => $baseUrl,
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens
        ]);

        if ($updated) {
            $aiModel->updateTestStatus($id, 'success', null);
            ChatbotHelper::resetDbCache();
            Flasher::setFlash('Berhasil', "Konfigurasi '{$label}' berhasil diperbarui.", 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal memperbarui konfigurasi di basis data.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
        exit;
    }

    /**
     * Aktifkan Konfigurasi Tertentu Sebagai API Utama Chatbot
     */
    public function chatbot_activate_config($id = 0) {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $csrf = $_REQUEST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan (CSRF) tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $aiModel = $this->model('Ai_model');
        $target = $aiModel->getConfigurationById($id);
        if (!$target) {
            Flasher::setFlash('Gagal', 'Konfigurasi tidak ditemukan.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        // VALIDASI KONEKSI SEBELUM MENGAKTIFKAN
        $test = ChatbotHelper::testConnection($target['provider'], $target['api_key'], $target['model'], $target['base_url']);
        if (!$test['status']) {
            $aiModel->updateTestStatus($id, 'failed', $test['pesan']);
            $errMsg = htmlspecialchars($test['pesan'] ?? 'Koneksi gagal');
            Flasher::setFlash('Gagal Diaktifkan', "Konfigurasi '{$target['label']}' tidak dapat diaktifkan karena uji koneksi gagal: {$errMsg}. Konfigurasi utama saat ini tetap dipertahankan.", 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $activated = $aiModel->setActiveConfiguration($id);
        if ($activated) {
            $aiModel->updateTestStatus($id, 'success', null);
            ChatbotHelper::resetDbCache();
            Flasher::setFlash('Berhasil Diaktifkan', "Konfigurasi '{$target['label']}' ({$target['provider']} - {$target['model']}) kini aktif sebagai API Utama Chatbot.", 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal mengaktifkan konfigurasi di sistem.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
        exit;
    }

    /**
     * Hapus Konfigurasi API Key Tertentu (Dengan Konfirmasi & Proteksi Konfigurasi Aktif)
     */
    public function chatbot_delete_config($id = 0) {
        $id = (int)$id;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan (CSRF) tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $aiModel = $this->model('Ai_model');
        $result = $aiModel->deleteConfiguration($id);

        if ($result['success']) {
            ChatbotHelper::resetDbCache();
            Flasher::setFlash('Berhasil Dihapus', $result['message'], 'success');
        } else {
            Flasher::setFlash('Gagal Menghapus', $result['message'], 'warning');
        }

        header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
        exit;
    }

    /**
     * Simpan Pengaturan Multi-Provider AI Chatbot ke .env dengan Validasi Koneksi Terlebih Dahulu
     */
    public function update_chatbot_api() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan (CSRF) tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        $provider    = strtolower(trim($_POST['ai_provider'] ?? 'gemini'));
        $apiKeyInput = trim($_POST['ai_api_key'] ?? ($_POST['gemini_api_key'] ?? ''));
        $model       = trim($_POST['ai_model'] ?? '');
        $baseUrl     = trim($_POST['ai_base_url'] ?? '');
        $temperature = isset($_POST['ai_temperature']) ? (float)$_POST['ai_temperature'] : 0.2;
        $maxTokens   = isset($_POST['ai_max_tokens']) ? (int)$_POST['ai_max_tokens'] : 350;
        $status      = trim($_POST['ai_status'] ?? 'active');

        // Jika API Key dikosongkan pada form, pertahankan kunci yang sudah tersimpan
        if ($apiKeyInput === '') {
            $apiKeyInput = ChatbotHelper::getApiKey();
        }

        // Tentukan model default jika kosong
        if (empty($model)) {
            $adapter = AiProviderFactory::getAdapter($provider);
            $model = $adapter->getDefaultModel();
        }

        // Validasi keberadaan API Key (kecuali endpoint lokal yang tidak butuh kunci)
        $isLocal = str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1');
        if (empty($apiKeyInput) && !$isLocal) {
            Flasher::setFlash('Gagal', 'API Key Chatbot tidak boleh kosong.', 'warning');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
            exit;
        }

        // VALIDASI KONEKSI SEBELUM MENYIMPAN: jika status aktif, uji koneksi ke API
        if ($status === 'active') {
            $testRes = ChatbotHelper::testConnection($provider, $apiKeyInput, $model, $baseUrl);
            if (!$testRes['status']) {
                $pesanError = htmlspecialchars($testRes['pesan'] ?? 'Uji koneksi gagal.');
                Flasher::setFlash('Validasi Gagal', "Konfigurasi tidak dapat disimpan karena uji koneksi gagal: {$pesanError}. Konfigurasi aktif sebelumnya tetap dipertahankan.", 'danger');
                header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
                exit;
            }
        }

        $saved = ChatbotHelper::saveEnvConfig([
            'provider'    => $provider,
            'api_key'     => $apiKeyInput,
            'model'       => $model,
            'base_url'    => $baseUrl,
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
            'status'      => $status
        ]);

        if ($saved) {
            $adapter = AiProviderFactory::getAdapter($provider);
            $providerName = $adapter->getName();
            Flasher::setFlash('Berhasil', "Konfigurasi AI Chatbot ({$providerName} - {$model}) berhasil divalidasi dan disimpan.", 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal memperbarui file .env. Pastikan hak akses file server memadai.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturan#tab-chatbot');
        exit;
    }

    /**
     * Uji Koneksi Bot Telegram (getMe) via AJAX / POST oleh Admin
     */
    public function telegram_check() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-telegram');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            echo json_encode(['status' => false, 'pesan' => 'Token keamanan (CSRF) tidak valid.']);
            exit;
        }

        $tokenInput = !empty($_POST['token']) ? trim($_POST['token']) : null;
        $hasil = TelegramHelper::checkBotConnection($tokenInput);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($hasil);
        exit;
    }

    /**
     * Kirim Pesan Uji Telegram (sendMessage) via POST oleh Admin
     */
    public function telegram_test() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-telegram');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(['status' => false, 'pesan' => 'Token keamanan (CSRF) tidak valid.']);
                exit;
            }
            Flasher::setFlash('Gagal', 'Token keamanan (CSRF) tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-telegram');
            exit;
        }

        $tokenInput = !empty($_POST['token']) ? trim($_POST['token']) : null;
        $chatIdInput = !empty($_POST['chat_id']) ? trim($_POST['chat_id']) : null;

        $hasil = TelegramHelper::testKirimPesan($chatIdInput, $tokenInput);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($hasil);
            exit;
        }

        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Perhatian', $hasil['pesan'], 'warning');
        }

        header('Location: ' . BASEURL . '/admin/pengaturan#tab-telegram');
        exit;
    }

    /**
     * Simpan Pengaturan Konfigurasi Telegram ke .env
     */
    public function update_telegram() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-telegram');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan (CSRF) tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-telegram');
            exit;
        }

        $tokenInput = trim($_POST['telegram_token'] ?? '');
        $chatId     = trim($_POST['telegram_chat_id'] ?? '5671569450');
        $enabled    = isset($_POST['telegram_enabled']) && $_POST['telegram_enabled'] == '1';

        // Jika token dikosongkan pada form namun sudah ada token tersimpan di .env, pertahankan token lama
        if ($tokenInput === '' && defined('TELEGRAM_BOT_TOKEN') && !empty(TELEGRAM_BOT_TOKEN)) {
            $tokenInput = TELEGRAM_BOT_TOKEN;
        }

        // Jika admin mengaktifkan notifikasi (enabled = true), validasi kelengkapan token dan chat ID
        if ($enabled && (empty($tokenInput) || empty($chatId))) {
            Flasher::setFlash('Gagal', 'Untuk mengaktifkan notifikasi, Token Bot dan Chat ID wajib diisi dan diverifikasi terlebih dahulu.', 'warning');
            header('Location: ' . BASEURL . '/admin/pengaturan#tab-telegram');
            exit;
        }

        $saved = TelegramHelper::saveEnvConfig($tokenInput, $chatId, $enabled);

        if ($saved) {
            $statusTeks = $enabled ? 'diaktifkan' : 'dinonaktifkan';
            Flasher::setFlash('Berhasil', "Konfigurasi Telegram berhasil disimpan. Notifikasi {$statusTeks}.", 'success');
        } else {
            Flasher::setFlash('Gagal', 'Gagal memperbarui file .env. Pastikan hak akses file server memadai.', 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturan#tab-telegram');
        exit;
    }

    /**
     * Update Pengaturan Titik Lokasi & Radius Geofencing Sekolah (100 Meter)
     */
    public function update_lokasi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan');
            exit;
        }

        $payload = [
            'nama_sekolah'   => $_POST['nama_sekolah'] ?? 'SMK Negeri',
            'latitude'       => $_POST['latitude'] ?? -6.208763,
            'longitude'      => $_POST['longitude'] ?? 106.845599,
            'radius_meter'   => !empty($_POST['radius_meter']) ? (int)$_POST['radius_meter'] : 100,
            'geofence_aktif' => isset($_POST['geofence_aktif']) ? true : false,
        ];

        LokasiHelper::savePengaturanLokasi($payload);
        Flasher::setFlash('Berhasil', "Titik lokasi sekolah dan batas jarak absensi ({$payload['radius_meter']} meter) berhasil disimpan.", 'success');
        header('Location: ' . BASEURL . '/admin/pengaturan#tab-lokasi');
        exit;
    }

    /**
     * Proses Update Identitas Akun Admin
     */
    public function update_akun() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan');
            exit;
        }

        $hasil = $this->model('User_model')->updateAkunAdmin($_SESSION['user_id'], $_POST);
        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturan');
        exit;
    }

    /**
     * Proses Ganti Password Admin
     */
    public function ganti_password() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/admin/pengaturan');
            exit;
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            Flasher::setFlash('Gagal', 'Token keamanan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/admin/pengaturan');
            exit;
        }

        $hasil = $this->model('User_model')->gantiPassword($_SESSION['user_id'], $_POST);
        if ($hasil['status']) {
            Flasher::setFlash('Berhasil', $hasil['pesan'], 'success');
        } else {
            Flasher::setFlash('Gagal', $hasil['pesan'], 'danger');
        }

        header('Location: ' . BASEURL . '/admin/pengaturan');
        exit;
    }
}

