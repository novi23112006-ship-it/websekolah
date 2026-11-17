<?php
/**
 * ChatbotHelper
 * Pengelolaan konfigurasi multi-provider, status, pengujian, dan penyimpanan pengaturan AI Chatbot.
 */
require_once __DIR__ . '/ai/AiProviderFactory.php';

class ChatbotHelper {

    private static ?array $activeDbConfig = null;
    private static bool $checkedDb = false;

    /**
     * Dapatkan konfigurasi aktif dari tabel basis data ai_configurations (jika tersedia)
     */
    public static function getActiveDbConfig(): ?array {
        if (self::$checkedDb) {
            return self::$activeDbConfig;
        }
        self::$checkedDb = true;
        try {
            if (class_exists('Database')) {
                $db = new Database();
                $db->query("SELECT * FROM ai_configurations WHERE is_active = 1 LIMIT 1");
                $row = $db->single();
                if ($row) {
                    self::$activeDbConfig = $row;
                }
            }
        } catch (\Throwable $e) {
            self::$activeDbConfig = null;
        }
        return self::$activeDbConfig;
    }

    /**
     * Reset cache database config (misal setelah switch active config)
     */
    public static function resetDbCache(): void {
        self::$activeDbConfig = null;
        self::$checkedDb = false;
    }

    /**
     * Ambil Provider AI aktif
     */
    public static function getProvider(): string {
        $dbCfg = self::getActiveDbConfig();
        if ($dbCfg && !empty($dbCfg['provider'])) {
            return strtolower(trim($dbCfg['provider']));
        }

        $env = getenv('AI_PROVIDER') ?: ($_ENV['AI_PROVIDER'] ?? null);
        if ($env !== null && $env !== '') {
            return strtolower(trim($env));
        }
        if (defined('AI_PROVIDER') && !empty(AI_PROVIDER)) {
            return strtolower(trim(AI_PROVIDER));
        }
        return 'gemini';
    }

    /**
     * Ambil API Key AI aktif
     */
    public static function getApiKey(): string {
        $dbCfg = self::getActiveDbConfig();
        if ($dbCfg && !empty($dbCfg['api_key'])) {
            return trim($dbCfg['api_key']);
        }

        $env = getenv('AI_API_KEY') ?: ($_ENV['AI_API_KEY'] ?? null);
        if ($env !== null && $env !== '') {
            return trim($env);
        }
        if (defined('AI_API_KEY') && !empty(AI_API_KEY)) {
            return trim(AI_API_KEY);
        }
        $legacy = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : ''));
        return trim($legacy);
    }

    /**
     * Ambil Model AI aktif
     */
    public static function getModel(): string {
        $dbCfg = self::getActiveDbConfig();
        if ($dbCfg && !empty($dbCfg['model'])) {
            return trim($dbCfg['model']);
        }

        $env = getenv('AI_MODEL') ?: ($_ENV['AI_MODEL'] ?? null);
        if ($env !== null && $env !== '') {
            return trim($env);
        }
        if (defined('AI_MODEL') && !empty(AI_MODEL)) {
            return trim(AI_MODEL);
        }
        $adapter = AiProviderFactory::getAdapter(self::getProvider());
        return $adapter->getDefaultModel();
    }

    /**
     * Ambil Base URL aktif (untuk custom provider)
     */
    public static function getBaseUrl(): string {
        $dbCfg = self::getActiveDbConfig();
        if ($dbCfg && isset($dbCfg['base_url'])) {
            return trim($dbCfg['base_url']);
        }

        $env = getenv('AI_BASE_URL') ?: ($_ENV['AI_BASE_URL'] ?? null);
        if ($env !== null) {
            return trim($env);
        }
        if (defined('AI_BASE_URL')) {
            return trim(AI_BASE_URL);
        }
        return '';
    }

    /**
     * Ambil parameter Temperature (0.0 - 1.0)
     */
    public static function getTemperature(): float {
        $dbCfg = self::getActiveDbConfig();
        if ($dbCfg && isset($dbCfg['temperature'])) {
            return (float)$dbCfg['temperature'];
        }

        $env = getenv('AI_TEMPERATURE') ?: ($_ENV['AI_TEMPERATURE'] ?? null);
        if ($env !== null && $env !== '') {
            return (float)$env;
        }
        if (defined('AI_TEMPERATURE')) {
            return (float)AI_TEMPERATURE;
        }
        return 0.2;
    }

    /**
     * Ambil parameter Max Tokens
     */
    public static function getMaxTokens(): int {
        $dbCfg = self::getActiveDbConfig();
        if ($dbCfg && isset($dbCfg['max_tokens'])) {
            return (int)$dbCfg['max_tokens'];
        }

        $env = getenv('AI_MAX_TOKENS') ?: ($_ENV['AI_MAX_TOKENS'] ?? null);
        if ($env !== null && $env !== '') {
            return (int)$env;
        }
        if (defined('AI_MAX_TOKENS')) {
            return (int)AI_MAX_TOKENS;
        }
        return 350;
    }

    /**
     * Cek apakah status AI aktif / enabled
     */
    public static function isActive(): bool {
        $env = getenv('AI_STATUS') ?: ($_ENV['AI_STATUS'] ?? null);
        if ($env !== null && $env !== '') {
            return $env === 'active' || $env === '1' || $env === 'true';
        }
        if (defined('AI_STATUS')) {
            return (bool)AI_STATUS;
        }
        return true;
    }

    /**
     * Dapatkan representasi token tersamar (masked) untuk keamanan tampilan UI Admin
     */
    public static function getMaskedApiKey(?string $key = null): string {
        $k = ($key !== null) ? trim($key) : self::getApiKey();
        if (empty($k) || $k === 'ISI_API_KEY_ANDA_DI_SINI') {
            return 'Belum Diatur';
        }

        $len = strlen($k);
        if ($len <= 8) {
            return substr($k, 0, 2) . '••••••';
        }
        if ($len <= 14) {
            return substr($k, 0, 4) . '••••••••' . substr($k, -2);
        }

        $prefix = substr($k, 0, 7);
        $suffix = substr($k, -4);
        return $prefix . '••••••••••••' . $suffix;
    }

    /**
     * Cek apakah Chatbot AI sudah dikonfigurasi API Key-nya
     */
    public static function isConfigured(): bool {
        $provider = self::getProvider();
        $key = self::getApiKey();
        if ($provider === 'custom' && (str_contains(self::getBaseUrl(), 'localhost') || str_contains(self::getBaseUrl(), '127.0.0.1'))) {
            return true; // Local Ollama might not require an API key
        }
        return !empty($key) && $key !== 'ISI_API_KEY_ANDA_DI_SINI';
    }

    /**
     * Dapatkan ringkasan status konfigurasi Chatbot AI lengkap beserta daftar konfigurasi untuk Admin
     */
    public static function getConfigStatus(): array {
        $provider = self::getProvider();
        $adapter = AiProviderFactory::getAdapter($provider);
        $isConfigured = self::isConfigured();
        $isActive = self::isActive();
        $activeDb = self::getActiveDbConfig();

        $statusLabel = 'Belum Dikonfigurasi';
        $statusBadge = 'secondary';
        if ($isConfigured) {
            if ($isActive) {
                $statusLabel = 'Aktif & Siap Digunakan';
                $statusBadge = 'success';
            } else {
                $statusLabel = 'Dinonaktifkan Admin';
                $statusBadge = 'warning';
            }
        }

        // Ambil daftar seluruh konfigurasi API Key dari basis data
        $allConfigs = [];
        try {
            if (class_exists('Database')) {
                $db = new Database();
                $db->query("SELECT * FROM ai_configurations ORDER BY is_active DESC, id ASC");
                $rows = $db->resultSet();
                foreach ($rows as $r) {
                    $r['api_key_masked'] = self::getMaskedApiKey($r['api_key']);
                    unset($r['api_key']); // Proteksi: jangan sertakan raw key ke frontend
                    $allConfigs[] = $r;
                }
            }
        } catch (\Throwable $e) {
            $allConfigs = [];
        }

        return [
            'is_configured'       => $isConfigured,
            'is_active'           => $isActive,
            'active_id'           => $activeDb['id'] ?? null,
            'active_label'        => $activeDb['label'] ?? 'Konfigurasi Utama (.env)',
            'provider'            => $provider,
            'provider_label'      => $adapter->getName(),
            'model_name'          => self::getModel(),
            'base_url'            => self::getBaseUrl(),
            'temperature'         => self::getTemperature(),
            'max_tokens'          => self::getMaxTokens(),
            'api_key_masked'      => self::getMaskedApiKey(),
            'status_label'        => $statusLabel,
            'status_badge'        => $statusBadge,
            'all_configurations'  => $allConfigs,
            'supported_providers' => AiProviderFactory::getSupportedProviders()
        ];
    }

    /**
     * Uji koneksi ke endpoint AI Provider menggunakan konfigurasi yang diberikan atau aktif
     */
    public static function testConnection($provider = null, $customKey = null, $customModel = null, $customBaseUrl = null): array {
        $p = !empty($provider) ? strtolower(trim($provider)) : self::getProvider();
        $key = ($customKey !== null && trim($customKey) !== '') ? trim($customKey) : self::getApiKey();
        $model = !empty($customModel) ? trim($customModel) : self::getModel();
        $baseUrl = ($customBaseUrl !== null) ? trim($customBaseUrl) : self::getBaseUrl();

        $adapter = AiProviderFactory::getAdapter($p);
        return $adapter->testConnection($key, $model, $baseUrl);
    }

    /**
     * Uji koneksi konfigurasi tertentu berdasarkan ID di database
     *
     * @param int $id
     * @return array
     */
    public static function testConnectionById(int $id): array {
        try {
            $db = new Database();
            $db->query("SELECT * FROM ai_configurations WHERE id = :id LIMIT 1");
            $db->bind('id', $id);
            $row = $db->single();
            if (!$row) {
                return [
                    'status'    => false,
                    'http_code' => 404,
                    'pesan'     => 'Konfigurasi tidak ditemukan.',
                    'model'     => '',
                    'reply'     => ''
                ];
            }

            $res = self::testConnection($row['provider'], $row['api_key'], $row['model'], $row['base_url']);

            $statusStr = $res['status'] ? 'success' : 'failed';
            $errMsg = $res['status'] ? null : ($res['pesan'] ?? 'Gagal');

            $db->query("UPDATE ai_configurations SET 
                        last_status = :status, 
                        last_tested_at = NOW(), 
                        last_error_message = :err 
                        WHERE id = :id");
            $db->bind('id', $id);
            $db->bind('status', $statusStr);
            $db->bind('err', $errMsg);
            $db->execute();

            return $res;
        } catch (\Throwable $e) {
            return [
                'status'    => false,
                'http_code' => 500,
                'pesan'     => 'Kesalahan basis data: ' . $e->getMessage(),
                'model'     => '',
                'reply'     => ''
            ];
        }
    }

    /**
     * Simpan seluruh pengaturan Multi-Provider AI ke file .env secara aman
     */
    public static function saveEnvConfig(array $settings): bool {
        $envPath = dirname(dirname(__DIR__)) . '/.env';

        $provider    = strtolower(trim($settings['provider'] ?? 'gemini'));
        $apiKey      = trim($settings['api_key'] ?? '');
        $model       = trim($settings['model'] ?? '');
        $baseUrl     = trim($settings['base_url'] ?? '');
        $temperature = isset($settings['temperature']) ? (float)$settings['temperature'] : 0.2;
        $maxTokens   = isset($settings['max_tokens']) ? (int)$settings['max_tokens'] : 350;
        $status      = (!empty($settings['status']) && $settings['status'] === 'inactive') ? 'inactive' : 'active';

        if ($apiKey === '') {
            $apiKey = self::getApiKey();
        }

        $map = [
            'AI_PROVIDER'    => $provider,
            'AI_API_KEY'     => $apiKey,
            'AI_MODEL'       => $model,
            'AI_BASE_URL'    => $baseUrl,
            'AI_TEMPERATURE' => (string)$temperature,
            'AI_MAX_TOKENS'  => (string)$maxTokens,
            'AI_STATUS'      => $status,
            'GEMINI_API_KEY' => $apiKey // Pertahankan sinkronisasi untuk backward compatibility
        ];

        $existingContent = file_exists($envPath) ? file_get_contents($envPath) : '';
        $lines = explode("\n", str_replace("\r\n", "\n", $existingContent));

        $handledKeys = [];
        $newLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            $foundKey = null;
            foreach ($map as $envK => $envV) {
                if (str_starts_with($trimmed, "{$envK}=")) {
                    $foundKey = $envK;
                    break;
                }
            }

            if ($foundKey !== null) {
                $newLines[] = "{$foundKey}={$map[$foundKey]}";
                $handledKeys[$foundKey] = true;
            } else {
                $newLines[] = $line;
            }
        }

        foreach ($map as $envK => $envV) {
            if (empty($handledKeys[$envK])) {
                $newLines[] = "{$envK}={$envV}";
            }
        }

        $finalContent = implode("\n", $newLines);
        $written = @file_put_contents($envPath, $finalContent);

        if ($written !== false) {
            foreach ($map as $k => $v) {
                putenv("{$k}={$v}");
                $_ENV[$k] = $v;
                $_SERVER[$k] = $v;
            }
            self::resetDbCache();
            return true;
        }

        return false;
    }
}
