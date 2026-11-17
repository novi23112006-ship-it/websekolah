<?php
/**
 * Ai_model
 * Pengelolaan basis data untuk Manajemen Multi-API Key AI Chatbot.
 */
class Ai_model {

    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Ambil seluruh konfigurasi API Key (dengan API key tersamar untuk tampilan antarmuka)
     *
     * @return array
     */
    public function getAllConfigurations(): array {
        $this->db->query("SELECT * FROM ai_configurations ORDER BY is_active DESC, id ASC");
        $rows = $this->db->resultSet();

        foreach ($rows as &$r) {
            $r['api_key_masked'] = ChatbotHelper::getMaskedApiKey($r['api_key']);
            // Jangan ekspos raw API key ke view
            unset($r['api_key']);
        }
        unset($r);

        return $rows;
    }

    /**
     * Ambil data lengkap satu konfigurasi berdasarkan ID (khusus backend)
     *
     * @param int $id
     * @return array|null
     */
    public function getConfigurationById(int $id): ?array {
        $this->db->query("SELECT * FROM ai_configurations WHERE id = :id LIMIT 1");
        $this->db->bind('id', $id);
        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Ambil konfigurasi yang saat ini sedang berstatus aktif utama
     *
     * @return array|null
     */
    public function getActiveConfiguration(): ?array {
        $this->db->query("SELECT * FROM ai_configurations WHERE is_active = 1 LIMIT 1");
        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Tambah konfigurasi API Key baru
     *
     * @param array $data ['label', 'provider', 'api_key', 'model', 'base_url', 'temperature', 'max_tokens']
     * @return int|false
     */
    public function addConfiguration(array $data) {
        // Cek apakah sudah ada konfigurasi sebelumnya
        $this->db->query("SELECT COUNT(*) as total FROM ai_configurations");
        $count = (int)$this->db->single()['total'];
        $isActive = ($count === 0) ? 1 : 0;

        $sql = "INSERT INTO ai_configurations 
                (label, provider, api_key, model, base_url, temperature, max_tokens, is_active, last_status, last_tested_at)
                VALUES 
                (:label, :provider, :api_key, :model, :base_url, :temp, :tokens, :is_active, 'untested', NULL)";

        $this->db->query($sql);
        $this->db->bind('label', trim($data['label'] ?? 'Konfigurasi Baru'));
        $this->db->bind('provider', strtolower(trim($data['provider'] ?? 'gemini')));
        $this->db->bind('api_key', trim($data['api_key'] ?? ''));
        $this->db->bind('model', trim($data['model'] ?? 'gemini-3.1-flash-lite-preview'));
        $this->db->bind('base_url', trim($data['base_url'] ?? ''));
        $this->db->bind('temp', (float)($data['temperature'] ?? 0.2));
        $this->db->bind('tokens', (int)($data['max_tokens'] ?? 350));
        $this->db->bind('is_active', $isActive);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Perbarui konfigurasi API Key tertentu
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateConfiguration(int $id, array $data): bool {
        $existing = $this->getConfigurationById($id);
        if (!$existing) {
            return false;
        }

        $apiKey = !empty($data['api_key']) ? trim($data['api_key']) : $existing['api_key'];

        $sql = "UPDATE ai_configurations SET 
                label = :label,
                provider = :provider,
                api_key = :api_key,
                model = :model,
                base_url = :base_url,
                temperature = :temp,
                max_tokens = :tokens
                WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind('id', $id);
        $this->db->bind('label', trim($data['label'] ?? $existing['label']));
        $this->db->bind('provider', strtolower(trim($data['provider'] ?? $existing['provider'])));
        $this->db->bind('api_key', $apiKey);
        $this->db->bind('model', trim($data['model'] ?? $existing['model']));
        $this->db->bind('base_url', trim($data['base_url'] ?? $existing['base_url']));
        $this->db->bind('temp', isset($data['temperature']) ? (float)$data['temperature'] : (float)$existing['temperature']);
        $this->db->bind('tokens', isset($data['max_tokens']) ? (int)$data['max_tokens'] : (int)$existing['max_tokens']);

        $updated = $this->db->execute();

        // Jika konfigurasi yang diedit adalah konfigurasi aktif, perbarui juga .env
        if ($updated && (int)$existing['is_active'] === 1) {
            ChatbotHelper::saveEnvConfig([
                'provider'    => strtolower(trim($data['provider'] ?? $existing['provider'])),
                'api_key'     => $apiKey,
                'model'       => trim($data['model'] ?? $existing['model']),
                'base_url'    => trim($data['base_url'] ?? $existing['base_url']),
                'temperature' => isset($data['temperature']) ? (float)$data['temperature'] : (float)$existing['temperature'],
                'max_tokens'  => isset($data['max_tokens']) ? (int)$data['max_tokens'] : (int)$existing['max_tokens'],
                'status'      => 'active'
            ]);
        }

        return $updated;
    }

    /**
     * Aktifkan salah satu konfigurasi sebagai API utama chatbot
     *
     * @param int $id
     * @return bool
     */
    public function setActiveConfiguration(int $id): bool {
        $target = $this->getConfigurationById($id);
        if (!$target) {
            return false;
        }

        // Setel semua konfigurasi menjadi nonaktif (is_active = 0)
        $this->db->query("UPDATE ai_configurations SET is_active = 0");
        $this->db->execute();

        // Setel konfigurasi target menjadi aktif (is_active = 1)
        $this->db->query("UPDATE ai_configurations SET is_active = 1 WHERE id = :id");
        $this->db->bind('id', $id);
        $success = $this->db->execute();

        if ($success) {
            // Sinkronkan ke environment/.env untuk kompatibilitas penuh
            ChatbotHelper::saveEnvConfig([
                'provider'    => $target['provider'],
                'api_key'     => $target['api_key'],
                'model'       => $target['model'],
                'base_url'    => $target['base_url'],
                'temperature' => (float)$target['temperature'],
                'max_tokens'  => (int)$target['max_tokens'],
                'status'      => 'active'
            ]);
            return true;
        }

        return false;
    }

    /**
     * Hapus konfigurasi API Key dengan proteksi terhadap konfigurasi aktif
     *
     * @param int $id
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteConfiguration(int $id): array {
        $target = $this->getConfigurationById($id);
        if (!$target) {
            return ['success' => false, 'message' => 'Konfigurasi tidak ditemukan.'];
        }

        if ((int)$target['is_active'] === 1) {
            return [
                'success' => false,
                'message' => 'Konfigurasi yang sedang aktif sebagai API Utama tidak dapat dihapus. Silakan aktifkan konfigurasi lain terlebih dahulu.'
            ];
        }

        $this->db->query("DELETE FROM ai_configurations WHERE id = :id");
        $this->db->bind('id', $id);
        $deleted = $this->db->execute();

        if ($deleted) {
            return ['success' => true, 'message' => "Konfigurasi '{$target['label']}' berhasil dihapus."];
        }
        return ['success' => false, 'message' => 'Gagal menghapus konfigurasi dari basis data.'];
    }

    /**
     * Perbarui status hasil uji koneksi
     *
     * @param int $id
     * @param string $status 'success'|'failed'
     * @param string|null $errorMsg
     * @return bool
     */
    public function updateTestStatus(int $id, string $status, ?string $errorMsg = null): bool {
        $this->db->query("UPDATE ai_configurations SET 
                          last_status = :status, 
                          last_tested_at = NOW(), 
                          last_error_message = :err 
                          WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->bind('status', $status);
        $this->db->bind('err', $errorMsg);
        return $this->db->execute();
    }
}

