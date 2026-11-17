<?php

/**
 * Helper Notifikasi Telegram Bot API Resmi
 * Dokumentasi: https://core.telegram.org/bots/api
 *
 * Mengelola komunikasi aman antara Sistem Absensi SMK dan Telegram Bot API.
 * Fitur:
 * - Pemeriksaan koneksi bot (getMe)
 * - Pengiriman pesan uji oleh Admin (sendMessage)
 * - Notifikasi absensi siswa (nama, nis, kelas, tgl, waktu, status)
 * - Notifikasi absensi guru (nama, tgl, waktu, status)
 * - Pengajuan izin/sakit siswa & guru
 * - Keamanan tingkat tinggi: token tidak pernah dicatat ke log, response, atau browser.
 */
class TelegramHelper {

    /**
     * Periksa apakah fitur notifikasi Telegram diaktifkan dan terkonfigurasi lengkap.
     *
     * @return bool
     */
    public static function isEnabled() {
        if (!defined('TELEGRAM_NOTIFICATIONS_ENABLED') || !TELEGRAM_NOTIFICATIONS_ENABLED) {
            return false;
        }

        $token  = defined('TELEGRAM_BOT_TOKEN') ? trim(TELEGRAM_BOT_TOKEN) : '';
        $chatId = defined('TELEGRAM_CHAT_ID') ? trim(TELEGRAM_CHAT_ID) : '';

        return (!empty($token) && !empty($chatId));
    }

    /**
     * Dapatkan ringkasan status konfigurasi tanpa membocorkan token rahasia.
     *
     * @return array
     */
    public static function getConfigStatus() {
        $token  = defined('TELEGRAM_BOT_TOKEN') ? trim(TELEGRAM_BOT_TOKEN) : '';
        $chatId = defined('TELEGRAM_CHAT_ID') ? trim(TELEGRAM_CHAT_ID) : '';
        $enabled = defined('TELEGRAM_NOTIFICATIONS_ENABLED') && TELEGRAM_NOTIFICATIONS_ENABLED;

        $hasToken = !empty($token);
        $tokenMasked = 'Belum Diatur';
        if ($hasToken) {
            if (strlen($token) > 10) {
                $tokenMasked = substr($token, 0, 5) . '••••••••••••' . substr($token, -4);
            } else {
                $tokenMasked = '••••••••';
            }
        }

        return [
            'is_ready'      => self::isEnabled(),
            'enabled_flag'  => $enabled,
            'has_token'     => $hasToken,
            'token_masked'  => $tokenMasked,
            'chat_id'       => $chatId,
            'has_chat_id'   => !empty($chatId)
        ];
    }

    /**
     * Periksa validitas token bot ke Telegram Bot API via endpoint resmi getMe.
     * Token tidak pernah dicatat ke log atau bocor ke response.
     *
     * @param string|null $customToken Token alternatif untuk pengujian
     * @return array ['status' => bool, 'pesan' => string, 'bot' => array|null, 'http_code' => int]
     */
    public static function checkBotConnection($customToken = null) {
        $token = $customToken !== null ? trim($customToken) : (defined('TELEGRAM_BOT_TOKEN') ? trim(TELEGRAM_BOT_TOKEN) : '');

        if (empty($token)) {
            return [
                'status'    => false,
                'http_code' => 0,
                'pesan'     => 'Token bot belum diisi dalam konfigurasi lingkungan (.env).',
                'bot'       => null
            ];
        }

        // Validasi dasar format token Telegram (misal: 123456789:ABCDefgh...)
        if (!preg_match('/^[0-9]+:[a-zA-Z0-9_\-]+$/', $token)) {
            return [
                'status'    => false,
                'http_code' => 0,
                'pesan'     => 'Format token bot tidak sesuai standar Telegram Bot API (angka_id:token_string).',
                'bot'       => null
            ];
        }

        $apiUrl = "https://api.telegram.org/bot{$token}/getMe";
        $response = false;
        $httpCode = 0;

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_HTTPGET        => true
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);
        }

        // Fallback jika cURL tidak tersedia atau gagal
        if ($response === false) {
            $opts = [
                'http' => [
                    'method'        => 'GET',
                    'timeout'       => 8,
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false
                ]
            ];
            $context = stream_context_create($opts);
            $response = @file_get_contents($apiUrl, false, $context);
            if (isset($http_response_header) && is_array($http_response_header) && !empty($http_response_header[0])) {
                if (preg_match('/HTTP\/\S+\s+(\d+)/', $http_response_header[0], $m)) {
                    $httpCode = (int)$m[1];
                }
            } else {
                $httpCode = ($response !== false) ? 200 : 0;
            }
        }

        if ($response === false) {
            return [
                'status'    => false,
                'http_code' => 0,
                'pesan'     => 'Gagal menghubungi Telegram Bot API (Koneksi jaringan atau timeout server).',
                'bot'       => null
            ];
        }

        $json = json_decode($response, true);

        if ($httpCode === 401 || (is_array($json) && empty($json['ok']) && ($json['error_code'] ?? 0) === 401)) {
            return [
                'status'    => false,
                'http_code' => 401,
                'pesan'     => 'Token bot tidak valid atau telah dicabut oleh BotFather (HTTP 401 Unauthorized).',
                'bot'       => null
            ];
        }

        if ($httpCode !== 200 || !is_array($json) || empty($json['ok'])) {
            $desc = $json['description'] ?? "HTTP {$httpCode}";
            return [
                'status'    => false,
                'http_code' => $httpCode,
                'pesan'     => "Telegram API Error ({$httpCode}): {$desc}",
                'bot'       => null
            ];
        }

        $botData = $json['result'] ?? [];
        $botName = htmlspecialchars($botData['first_name'] ?? 'Bot');
        $botUser = htmlspecialchars($botData['username'] ?? '-');

        return [
            'status'    => true,
            'http_code' => 200,
            'pesan'     => "Bot Telegram terverifikasi: {$botName} (@{$botUser}).",
            'bot'       => [
                'id'         => $botData['id'] ?? null,
                'first_name' => $botName,
                'username'   => $botUser,
                'can_join_groups' => $botData['can_join_groups'] ?? true
            ]
        ];
    }

    /**
     * Kirim pesan uji oleh Admin yang terautentikasi untuk memverifikasi chat ID dan bot.
     *
     * @param string|null $targetChatId Chat ID tujuan uji
     * @param string|null $customToken  Token alternatif uji
     * @return array
     */
    public static function testKirimPesan($targetChatId = null, $customToken = null) {
        // 1. Verifikasi koneksi bot terlebih dahulu
        $botCheck = self::checkBotConnection($customToken);
        if (!$botCheck['status']) {
            return $botCheck;
        }

        $token  = $customToken !== null ? trim($customToken) : trim(TELEGRAM_BOT_TOKEN);
        $chatId = $targetChatId !== null ? trim($targetChatId) : (defined('TELEGRAM_CHAT_ID') ? trim(TELEGRAM_CHAT_ID) : '');

        if (empty($chatId)) {
            return [
                'status'    => false,
                'http_code' => 0,
                'pesan'     => 'Chat ID tujuan belum diatur. Masukkan Chat ID (contoh: 5671569450).',
                'bot'       => $botCheck['bot']
            ];
        }

        $botUser = $botCheck['bot']['username'] ?? 'Bot';
        $waktu   = date('d F Y H:i:s') . ' WIB';

        $pesanUji = "🔔 <b>UJI KONEKSI NOTIFIKASI TELEGRAM</b>\n\n"
                  . "Sistem Absensi SMK berhasil terhubung dengan Telegram Bot API.\n"
                  . "<b>Bot:</b> @{$botUser}\n"
                  . "<b>Chat ID:</b> {$chatId}\n"
                  . "<b>Waktu Uji:</b> {$waktu}\n"
                  . "<b>Status:</b> Koneksi Berhasil & Siap Digunakan ✅\n"
                  . "<b>Sumber:</b> Tindakan Uji Administrator";

        $apiUrl = "https://api.telegram.org/bot{$token}/sendMessage";
        $payload = [
            'chat_id'                  => $chatId,
            'text'                     => $pesanUji,
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $apiUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log("[Telegram Test Message] cURL Error: {$curlErr}");
            return [
                'status'    => false,
                'http_code' => 0,
                'pesan'     => 'Gagal mengirim pesan uji ke Telegram API (Masalah koneksi jaringan atau timeout).',
                'bot'       => $botCheck['bot']
            ];
        }

        $json = json_decode($response, true);

        if ($httpCode === 200 && is_array($json) && !empty($json['ok'])) {
            return [
                'status'     => true,
                'http_code'  => 200,
                'pesan'      => "Pesan uji berhasil dikirim ke Chat ID {$chatId} melalui Bot @{$botUser}.",
                'chat_id'    => $chatId,
                'bot'        => $botCheck['bot'],
                'message_id' => $json['result']['message_id'] ?? null
            ];
        }

        // Analisis spesifik pesan kegagalan dari Telegram
        $desc = $json['description'] ?? "HTTP {$httpCode}";
        $saran = '';

        if (stripos($desc, 'chat not found') !== false) {
            $saran = " Chat ID ({$chatId}) tidak ditemukan. Pastikan Anda telah membuka bot @{$botUser} di Telegram dan menekan tombol START (atau kirim perintah /start). Jika ini adalah grup, pastikan bot telah ditambahkan ke grup tersebut.";
        } elseif (stripos($desc, 'bot was blocked') !== false) {
            $saran = " Bot diblokir oleh akun Chat ID ({$chatId}). Buka Telegram, buka ruang obrolan dengan @{$botUser}, lalu unblock dan kirim /start.";
        } elseif (stripos($desc, 'bot is not a member') !== false) {
            $saran = " Bot bukan merupakan anggota grup Chat ID ({$chatId}). Tambahkan bot @{$botUser} ke dalam grup tersebut terlebih dahulu.";
        }

        error_log("[Telegram Test Message Error] HTTP {$httpCode}: {$desc}");

        return [
            'status'    => false,
            'http_code' => $httpCode,
            'pesan'     => "Gagal mengirim pesan ke Chat ID {$chatId}: {$desc}.{$saran}",
            'bot'       => $botCheck['bot']
        ];
    }

    /**
     * Kirim pesan teks ke Telegram Chat ID yang dikonfigurasi melalui Telegram Bot API resmi (HTTPS POST).
     *
     * @param string $pesanText Pesan yang akan dikirim (format teks biasa atau HTML)
     * @param string $parseMode Mode parsing ('HTML' atau 'Markdown')
     * @return array Status pengiriman ['status' => bool, 'pesan' => string]
     */
    public static function kirimPesan($pesanText, $parseMode = 'HTML') {
        // 1. Validasi status aktif dan kelengkapan kredensial
        if (!self::isEnabled()) {
            return [
                'status' => false,
                'pesan'  => 'Notifikasi Telegram dinonaktifkan atau konfigurasi belum lengkap.'
            ];
        }

        $token  = trim(TELEGRAM_BOT_TOKEN);
        $chatId = trim(TELEGRAM_CHAT_ID);

        // 2. Siapkan URL endpoint resmi Telegram Bot API
        $apiUrl = "https://api.telegram.org/bot{$token}/sendMessage";

        // 3. Susun data payload
        $payload = [
            'chat_id'                  => $chatId,
            'text'                     => $pesanText,
            'parse_mode'               => $parseMode,
            'disable_web_page_preview' => true
        ];

        try {
            $response = false;
            $httpCode = 0;

            // 4. Eksekusi pengiriman HTTPS POST menggunakan cURL jika aktif
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL            => $apiUrl,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => http_build_query($payload),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_TIMEOUT        => 8,
                    CURLOPT_CONNECTTIMEOUT => 4,
                    CURLOPT_HTTPHEADER     => [
                        'Content-Type: application/x-www-form-urlencoded'
                    ]
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($response !== false && $httpCode === 200) {
                    $json = json_decode($response, true);
                    if (is_array($json) && !empty($json['ok'])) {
                        return [
                            'status' => true,
                            'pesan'  => 'Notifikasi Telegram berhasil terkirim.'
                        ];
                    }
                }
            }

            // Fallback stream context jika cURL tidak aktif atau cURL gagal
            $opts = [
                'http' => [
                    'method'        => 'POST',
                    'header'        => "Content-Type: application/x-www-form-urlencoded\r\nConnection: close\r\n",
                    'content'       => http_build_query($payload),
                    'timeout'       => 10,
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false
                ]
            ];
            $context = stream_context_create($opts);
            $response = @file_get_contents($apiUrl, false, $context);

            if ($response !== false) {
                $json = json_decode($response, true);
                if (is_array($json) && !empty($json['ok'])) {
                    return [
                        'status' => true,
                        'pesan'  => 'Notifikasi Telegram berhasil terkirim.'
                    ];
                }
                $desc = $json['description'] ?? 'Respon tidak valid';
                error_log("[Telegram Notification Error] API Error: {$desc}");
                return [
                    'status' => false,
                    'pesan'  => "Telegram API Error: {$desc}"
                ];
            }

            error_log("[Telegram Notification Error] Pengiriman notifikasi Telegram gagal timeout / jaringan.");
            return ['status' => false, 'pesan' => 'Gagal mengirim pesan ke Telegram.'];

        } catch (\Throwable $e) {
            // Tangkap semua error agar TIDAK PERNAH mengganggu alur penyimpanan absensi
            error_log("[Telegram Notification Exception] " . $e->getMessage());
            return [
                'status' => false,
                'pesan'  => 'Terjadi kesalahan internal saat memproses notifikasi Telegram.'
            ];
        }
    }

    /**
     * Format dan kirim notifikasi absensi siswa yang berhasil dicatat (Masuk atau Pulang).
     *
     * @param array $data ['nama', 'nis', 'nama_kelas', 'tanggal', 'jam', 'status', 'tipe', 'jam_masuk', 'jam_pulang']
     * @return array
     */
    public static function notifikasiAbsensiSiswa($data) {
        $nama      = htmlspecialchars($data['nama'] ?? 'Siswa');
        $nis       = !empty($data['nis']) && $data['nis'] !== '-' ? htmlspecialchars($data['nis']) : '-';
        $kelas     = htmlspecialchars($data['nama_kelas'] ?? '-');
        $rawTgl    = $data['tanggal'] ?? date('Y-m-d');
        $rawJam    = $data['jam'] ?? date('H:i:s');
        $rawStat   = strtolower($data['status'] ?? 'hadir');
        $tipe      = strtolower($data['tipe'] ?? 'masuk');
        $jamMasuk  = !empty($data['jam_masuk']) ? substr($data['jam_masuk'], 0, 5) . ' WIB' : null;
        $jamPulang = !empty($data['jam_pulang']) ? substr($data['jam_pulang'], 0, 5) . ' WIB' : substr($rawJam, 0, 5) . ' WIB';

        $tanggal = DateHelper::tanggalIndo($rawTgl, true);
        $waktu   = substr($rawJam, 0, 8) . ' WIB';

        // Format label status sebenarnya
        $statusLabel = ucfirst($rawStat);
        if ($rawStat === 'terlambat') {
            $statusLabel = 'Terlambat ⚠️';
        } elseif ($rawStat === 'hadir') {
            $statusLabel = 'Hadir (Tepat Waktu) ✅';
        }

        if ($tipe === 'pulang') {
            $pesan = "🔔 <b>NOTIFIKASI ABSENSI PULANG SISWA</b>\n\n"
                   . "<b>Nama:</b> {$nama}\n";
            if ($nis !== '-') {
                $pesan .= "<b>NIS:</b> {$nis}\n";
            }
            $pesan .= "<b>Kelas:</b> {$kelas}\n"
                   . "<b>Tanggal:</b> {$tanggal}\n";
            if ($jamMasuk) {
                $pesan .= "<b>Jam Masuk:</b> {$jamMasuk}\n";
            }
            $pesan .= "<b>Jam Pulang:</b> {$jamPulang}\n"
                   . "<b>Status:</b> {$statusLabel}\n"
                   . "<b>Keterangan:</b> Selesai KBM Hari Ini 🏠\n"
                   . "<b>Sumber:</b> Sistem Absensi SMK";
        } else {
            $pesan = "🔔 <b>NOTIFIKASI ABSENSI MASUK SISWA</b>\n\n"
                   . "<b>Nama:</b> {$nama}\n";
            if ($nis !== '-') {
                $pesan .= "<b>NIS:</b> {$nis}\n";
            }
            $pesan .= "<b>Kelas:</b> {$kelas}\n"
                   . "<b>Tanggal:</b> {$tanggal}\n"
                   . "<b>Waktu Masuk:</b> {$waktu}\n"
                   . "<b>Status:</b> {$statusLabel}\n"
                   . "<b>Sumber:</b> Sistem Absensi SMK";
        }

        return self::kirimPesan($pesan, 'HTML');
    }

    /**
     * Format dan kirim notifikasi absensi guru yang berhasil dicatat (Masuk atau Pulang).
     *
     * @param array $data ['nama', 'nip', 'tanggal', 'jam', 'status', 'tipe', 'jam_masuk', 'jam_pulang']
     * @return array
     */
    public static function notifikasiAbsensiGuru($data) {
        $nama      = htmlspecialchars($data['nama'] ?? 'Bapak/Ibu Guru');
        $nip       = !empty($data['nip']) && $data['nip'] !== '-' ? htmlspecialchars($data['nip']) : '-';
        $rawTgl    = $data['tanggal'] ?? date('Y-m-d');
        $rawJam    = $data['jam'] ?? date('H:i:s');
        $rawStat   = strtolower($data['status'] ?? 'hadir');
        $tipe      = strtolower($data['tipe'] ?? 'masuk');
        $jamMasuk  = !empty($data['jam_masuk']) ? substr($data['jam_masuk'], 0, 5) . ' WIB' : null;
        $jamPulang = !empty($data['jam_pulang']) ? substr($data['jam_pulang'], 0, 5) . ' WIB' : substr($rawJam, 0, 5) . ' WIB';

        $tanggal = DateHelper::tanggalIndo($rawTgl, true);
        $waktu   = substr($rawJam, 0, 8) . ' WIB';

        // Format label status sebenarnya
        $statusLabel = ucfirst($rawStat);
        if ($rawStat === 'terlambat') {
            $statusLabel = 'Terlambat ⚠️';
        } elseif ($rawStat === 'hadir') {
            $statusLabel = 'Hadir (Tepat Waktu) ✅';
        }

        if ($tipe === 'pulang') {
            $pesan = "🔔 <b>NOTIFIKASI ABSENSI PULANG GURU</b>\n\n"
                   . "<b>Nama:</b> {$nama}\n";
            if ($nip !== '-') {
                $pesan .= "<b>NIP:</b> {$nip}\n";
            }
            $pesan .= "<b>Tanggal:</b> {$tanggal}\n";
            if ($jamMasuk) {
                $pesan .= "<b>Jam Masuk:</b> {$jamMasuk}\n";
            }
            $pesan .= "<b>Jam Pulang:</b> {$jamPulang}\n"
                   . "<b>Status:</b> {$statusLabel}\n"
                   . "<b>Keterangan:</b> Selesai Tugas Mengajar Hari Ini 🏠\n"
                   . "<b>Sumber:</b> Sistem Absensi SMK";
        } else {
            $pesan = "🔔 <b>NOTIFIKASI ABSENSI MASUK GURU</b>\n\n"
                   . "<b>Nama:</b> {$nama}\n";
            if ($nip !== '-') {
                $pesan .= "<b>NIP:</b> {$nip}\n";
            }
            $pesan .= "<b>Tanggal:</b> {$tanggal}\n"
                   . "<b>Waktu Masuk:</b> {$waktu}\n"
                   . "<b>Status:</b> {$statusLabel}\n"
                   . "<b>Sumber:</b> Sistem Absensi SMK";
        }

        return self::kirimPesan($pesan, 'HTML');
    }

    /**
     * Format dan kirim notifikasi pengajuan izin / sakit siswa yang berhasil dicatat.
     *
     * @param array $data ['nama', 'nama_kelas', 'tgl_mulai', 'tgl_selesai', 'status', 'keterangan']
     * @return array
     */
    public static function notifikasiPengajuanIzinSiswa($data) {
        $nama       = htmlspecialchars($data['nama'] ?? 'Siswa');
        $kelas      = htmlspecialchars($data['nama_kelas'] ?? '-');
        $tglMulai   = DateHelper::tanggalIndo($data['tgl_mulai'] ?? date('Y-m-d'), false);
        $tglSelesai = DateHelper::tanggalIndo($data['tgl_selesai'] ?? date('Y-m-d'), false);
        $status     = ucfirst(strtolower($data['status'] ?? 'Izin'));
        $keterangan = htmlspecialchars(mb_strimwidth($data['keterangan'] ?? '-', 0, 100, '...'));

        $periode = ($tglMulai === $tglSelesai) ? $tglMulai : "{$tglMulai} s/d {$tglSelesai}";

        $pesan = "🔔 <b>NOTIFIKASI PENGAJUAN " . strtoupper($status) . " SISWA</b>\n\n"
               . "<b>Nama:</b> {$nama}\n"
               . "<b>Kelas:</b> {$kelas}\n"
               . "<b>Periode:</b> {$periode}\n"
               . "<b>Kategori:</b> {$status}\n"
               . "<b>Keterangan:</b> {$keterangan}\n"
               . "<b>Sumber:</b> Sistem Absensi SMK";

        return self::kirimPesan($pesan, 'HTML');
    }

    /**
     * Format dan kirim notifikasi pengajuan izin / sakit guru yang berhasil dicatat.
     *
     * @param array $data ['nama', 'tgl_mulai', 'tgl_selesai', 'status', 'keterangan']
     * @return array
     */
    public static function notifikasiPengajuanIzinGuru($data) {
        $nama       = htmlspecialchars($data['nama'] ?? 'Guru');
        $tglMulai   = DateHelper::tanggalIndo($data['tgl_mulai'] ?? date('Y-m-d'), false);
        $tglSelesai = DateHelper::tanggalIndo($data['tgl_selesai'] ?? date('Y-m-d'), false);
        $status     = ucfirst(strtolower($data['status'] ?? 'Izin'));
        $keterangan = htmlspecialchars(mb_strimwidth($data['keterangan'] ?? '-', 0, 100, '...'));

        $periode = ($tglMulai === $tglSelesai) ? $tglMulai : "{$tglMulai} s/d {$tglSelesai}";

        $pesan = "🔔 <b>NOTIFIKASI PENGAJUAN " . strtoupper($status) . " GURU</b>\n\n"
               . "<b>Nama:</b> {$nama}\n"
               . "<b>Periode:</b> {$periode}\n"
               . "<b>Kategori:</b> {$status}\n"
               . "<b>Keterangan:</b> {$keterangan}\n"
               . "<b>Sumber:</b> Sistem Absensi SMK";

        return self::kirimPesan($pesan, 'HTML');
    }

    /**
     * Simpan pembaruan konfigurasi Telegram ke file .env secara aman.
     *
     * @param string $token
     * @param string $chatId
     * @param bool   $enabled
     * @return bool
     */
    public static function saveEnvConfig($token, $chatId, $enabled) {
        $envPath = dirname(dirname(__DIR__)) . '/.env';

        $token   = trim($token);
        $chatId  = trim($chatId);
        $enabledStr = $enabled ? 'true' : 'false';

        $existingContent = file_exists($envPath) ? file_get_contents($envPath) : '';
        $lines = explode("\n", $existingContent);

        $keysHandled = [
            'TELEGRAM_BOT_TOKEN'            => false,
            'TELEGRAM_CHAT_ID'              => false,
            'TELEGRAM_NOTIFICATIONS_ENABLED' => false,
        ];

        $newLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (strpos($trimmed, 'TELEGRAM_BOT_TOKEN=') === 0) {
                // Jika token baru kosong dan token lama ada, pertahankan token lama jika diinginkan
                $newLines[] = "TELEGRAM_BOT_TOKEN={$token}";
                $keysHandled['TELEGRAM_BOT_TOKEN'] = true;
            } elseif (strpos($trimmed, 'TELEGRAM_CHAT_ID=') === 0) {
                $newLines[] = "TELEGRAM_CHAT_ID={$chatId}";
                $keysHandled['TELEGRAM_CHAT_ID'] = true;
            } elseif (strpos($trimmed, 'TELEGRAM_NOTIFICATIONS_ENABLED=') === 0) {
                $newLines[] = "TELEGRAM_NOTIFICATIONS_ENABLED={$enabledStr}";
                $keysHandled['TELEGRAM_NOTIFICATIONS_ENABLED'] = true;
            } else {
                if ($line !== '' || count($newLines) > 0) {
                    $newLines[] = $line;
                }
            }
        }

        // Tambahkan key yang belum ada
        if (!$keysHandled['TELEGRAM_BOT_TOKEN']) {
            $newLines[] = "TELEGRAM_BOT_TOKEN={$token}";
        }
        if (!$keysHandled['TELEGRAM_CHAT_ID']) {
            $newLines[] = "TELEGRAM_CHAT_ID={$chatId}";
        }
        if (!$keysHandled['TELEGRAM_NOTIFICATIONS_ENABLED']) {
            $newLines[] = "TELEGRAM_NOTIFICATIONS_ENABLED={$enabledStr}";
        }

        $finalContent = implode("\n", array_filter($newLines, fn($l) => $l !== null)) . "\n";
        return file_put_contents($envPath, $finalContent, LOCK_EX) !== false;
    }
}
