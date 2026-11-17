<?php
// Timezone Configuration (WIB - Waktu Indonesia Barat)
date_default_timezone_set('Asia/Jakarta');

define('BASEURL', 'http://localhost/websekolah3/public');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_absensi_smk');

// App Configuration
define('JAM_MASUK_NORMAL', '07:00:00');
define('JAM_MASUK_GURU', '06:45:00');

// Geolocation & Geofencing (Batasan Jarak Maksimal 100 Meter)
define('SEKOLAH_LATITUDE', -6.208763);
define('SEKOLAH_LONGITUDE', 106.845599);
define('MAX_JARAK_ABSEN_METER', 100);

// ─── Load Environment Variables (.env) ────────────────────────────────────────
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '//') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($envKey, $envVal) = explode('=', $line, 2);
            $envKey = trim($envKey);
            $envVal = trim($envVal);
            if ((str_starts_with($envVal, '"') && str_ends_with($envVal, '"')) ||
                (str_starts_with($envVal, "'") && str_ends_with($envVal, "'"))) {
                $envVal = substr($envVal, 1, -1);
            }
            if (!empty($envKey)) {
                putenv("{$envKey}={$envVal}");
                $_ENV[$envKey] = $envVal;
                $_SERVER[$envKey] = $envVal;
            }
        }
    }
}

// ─── Multi-Provider AI Configuration (Chatbot Asisten Pintar) ────────────────
$rawAiKey = getenv('AI_API_KEY') ?: ($_ENV['AI_API_KEY'] ?? (getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? '')));
define('AI_PROVIDER', getenv('AI_PROVIDER') ?: ($_ENV['AI_PROVIDER'] ?? 'gemini'));
define('AI_API_KEY', $rawAiKey);
define('AI_MODEL', getenv('AI_MODEL') ?: ($_ENV['AI_MODEL'] ?? 'gemini-3.1-flash-lite-preview'));
define('AI_BASE_URL', getenv('AI_BASE_URL') ?: ($_ENV['AI_BASE_URL'] ?? ''));
define('AI_TEMPERATURE', (float)(getenv('AI_TEMPERATURE') ?: ($_ENV['AI_TEMPERATURE'] ?? 0.2)));
define('AI_MAX_TOKENS', (int)(getenv('AI_MAX_TOKENS') ?: ($_ENV['AI_MAX_TOKENS'] ?? 350)));
define('AI_STATUS', (getenv('AI_STATUS') ?: ($_ENV['AI_STATUS'] ?? 'active')) === 'active');

// Kompatibilitas mundur dengan referensi modul sebelumnya
define('GEMINI_API_KEY', AI_API_KEY);
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/' . AI_MODEL . ':generateContent');
define('GEMINI_API_FALLBACK_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent');

// Telegram Bot API Configuration (https://core.telegram.org/bots/api)
define('TELEGRAM_BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN') ?: ($_ENV['TELEGRAM_BOT_TOKEN'] ?? ''));
define('TELEGRAM_CHAT_ID', getenv('TELEGRAM_CHAT_ID') ?: ($_ENV['TELEGRAM_CHAT_ID'] ?? ''));
define('TELEGRAM_NOTIFICATIONS_ENABLED', filter_var(getenv('TELEGRAM_NOTIFICATIONS_ENABLED') ?: ($_ENV['TELEGRAM_NOTIFICATIONS_ENABLED'] ?? false), FILTER_VALIDATE_BOOLEAN));

