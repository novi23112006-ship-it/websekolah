<?php
/**
 * GeminiAdapter
 * Adapter komunikasi dengan Google Gemini API (v1beta).
 */
require_once __DIR__ . '/AiProviderInterface.php';

class GeminiAdapter implements AiProviderInterface {

    private const DEFAULT_MODEL = 'gemini-3.1-flash-lite-preview';

    public function getId(): string {
        return 'gemini';
    }

    public function getName(): string {
        return 'Google Gemini AI';
    }

    public function getDefaultModel(): string {
        return self::DEFAULT_MODEL;
    }

    /**
     * Kirim percakapan ke Google Gemini
     */
    public function chat($systemPrompt, $userMessage, array $history = [], array $options = []): array {
        $apiKey = !empty($options['apiKey']) ? $options['apiKey'] : (defined('AI_API_KEY') ? AI_API_KEY : (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : ''));
        if (empty($apiKey) || $apiKey === 'ISI_API_KEY_ANDA_DI_SINI') {
            return [
                'success' => false,
                'message' => '',
                'error'   => 'API Key Google Gemini belum dikonfigurasi.'
            ];
        }

        $chosenModel = !empty($options['model']) ? trim($options['model']) : (defined('AI_MODEL') && AI_MODEL ? AI_MODEL : self::DEFAULT_MODEL);
        $temperature = isset($options['temperature']) ? (float)$options['temperature'] : (defined('AI_TEMPERATURE') ? (float)AI_TEMPERATURE : 0.2);
        $maxTokens   = isset($options['maxTokens']) ? (int)$options['maxTokens'] : (defined('AI_MAX_TOKENS') ? (int)AI_MAX_TOKENS : 350);

        // Susun daftar model untuk fallback otomatis jika model tertentu 404 / 503
        $modelsToTry = [$chosenModel];
        $fallbacks = ['gemini-3.1-flash-lite-preview', 'gemini-3.5-flash-lite', 'gemini-3-flash-preview', 'gemini-3.1-flash-lite', 'gemini-1.5-flash'];
        foreach ($fallbacks as $fb) {
            if (!in_array($fb, $modelsToTry, true)) {
                $modelsToTry[] = $fb;
            }
        }

        // Susun payload contents
        $contents = [];
        if (!empty($history) && is_array($history)) {
            foreach ($history as $item) {
                $role = ($item['role'] === 'model' || $item['role'] === 'assistant') ? 'model' : 'user';
                $text = trim($item['text'] ?? '');
                if ($text !== '') {
                    $contents[] = [
                        'role'  => $role,
                        'parts' => [['text' => $text]]
                    ];
                }
            }
        }
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $userMessage]]
        ];

        $payload = json_encode([
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature'     => $temperature,
                'maxOutputTokens' => $maxTokens,
                'topP'            => 0.85,
            ]
        ]);

        foreach ($modelsToTry as $model) {
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);
            $res = $this->executeHttpRequest($apiUrl, $payload, 4.0);

            if ($res['http_code'] === 200 && !empty($res['body'])) {
                $json = json_decode($res['body'], true);
                $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    $cleaned = trim($text);
                    $cleaned = str_replace('**', '', $cleaned);
                    return [
                        'success' => true,
                        'message' => $cleaned,
                        'error'   => null
                    ];
                }
            }
        }

        return [
            'success' => false,
            'message' => '',
            'error'   => 'Tidak ada respon dari Google Gemini API.'
        ];
    }

    /**
     * Uji koneksi Google Gemini
     */
    public function testConnection($apiKey, $model = null, $baseUrl = null): array {
        $key = trim($apiKey ?? '');
        if (empty($key) || $key === 'ISI_API_KEY_ANDA_DI_SINI') {
            return [
                'status'    => false,
                'http_code' => 0,
                'pesan'     => 'API Key Google Gemini belum diisi. Silakan masukkan API Key Anda.',
                'model'     => '',
                'reply'     => ''
            ];
        }

        $chosenModel = !empty($model) ? trim($model) : self::DEFAULT_MODEL;
        $modelsToTry = [$chosenModel];
        $fallbacks = ['gemini-3.1-flash-lite-preview', 'gemini-3.5-flash-lite', 'gemini-3-flash-preview', 'gemini-3.1-flash-lite'];
        foreach ($fallbacks as $fb) {
            if (!in_array($fb, $modelsToTry, true)) {
                $modelsToTry[] = $fb;
            }
        }

        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => 'Tes koneksi sistem absensi SMK. Balas tepat 1 kata: AKTIF']
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'     => 0.1,
                'maxOutputTokens' => 15
            ]
        ]);

        $lastHttpCode = 0;
        $lastErrorMsg = '';

        foreach ($modelsToTry as $m) {
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$m}:generateContent?key=" . urlencode($key);
            $res = $this->executeHttpRequest($apiUrl, $payload, 4.0);
            $lastHttpCode = $res['http_code'];

            if ($res['http_code'] === 200 && !empty($res['body'])) {
                $json = json_decode($res['body'], true);
                if (!empty($json['candidates'][0]['content']['parts'][0]['text'])) {
                    $reply = trim($json['candidates'][0]['content']['parts'][0]['text']);
                    return [
                        'status'    => true,
                        'http_code' => 200,
                        'pesan'     => "Koneksi ke Google Gemini AI Berhasil! Model [{$m}] aktif.",
                        'model'     => $m,
                        'reply'     => $reply
                    ];
                }
            }

            if (!empty($res['body'])) {
                $json = json_decode($res['body'], true);
                if (isset($json['error']['message'])) {
                    $msg = $json['error']['message'];
                    if (stripos($msg, 'API key not valid') !== false) {
                        return [
                            'status'    => false,
                            'http_code' => 400,
                            'pesan'     => 'API Key Google Gemini tidak valid. Silakan periksa kembali di Google AI Studio.',
                            'model'     => $m,
                            'reply'     => ''
                        ];
                    }
                    $lastErrorMsg = $msg;
                }
            }
        }

        return [
            'status'    => false,
            'http_code' => $lastHttpCode,
            'pesan'     => !empty($lastErrorMsg) ? "Gagal terhubung ke Google Gemini ({$lastErrorMsg})" : "Gagal terhubung ke Google Gemini (HTTP {$lastHttpCode}).",
            'model'     => $chosenModel,
            'reply'     => ''
        ];
    }

    /**
     * Transport HTTP dual-mode (cURL dengan fallback ke file_get_contents stream context)
     */
    private function executeHttpRequest($url, $payload, $timeout = 6.0): array {
        $body = false;
        $httpCode = 0;

        if (function_exists('curl_init')) {
            try {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL            => $url,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $payload,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_TIMEOUT        => (int)$timeout,
                    CURLOPT_CONNECTTIMEOUT => 4,
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/json']
                ]);
                $body = curl_exec($ch);
                $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (PHP_VERSION_ID < 80500) {
                    @curl_close($ch);
                }
            } catch (\Throwable $e) {
                $body = false;
            }
        }

        if ($body === false) {
            try {
                $streamCtx = stream_context_create([
                    'http' => [
                        'method'        => 'POST',
                        'header'        => "Content-Type: application/json\r\n",
                        'content'       => $payload,
                        'timeout'       => $timeout,
                        'ignore_errors' => true
                    ],
                    'ssl' => [
                        'verify_peer'      => false,
                        'verify_peer_name' => false
                    ]
                ]);
                $body = @file_get_contents($url, false, $streamCtx);
                if (function_exists('http_get_last_response_headers')) {
                    $responseHeaders = http_get_last_response_headers();
                } else {
                    $varName = 'http_response_header';
                    $responseHeaders = $$varName ?? [];
                }
                if (is_array($responseHeaders)) {
                    foreach ($responseHeaders as $hdr) {
                        if (preg_match('#HTTP/\S+\s+(\d+)#', $hdr, $hm)) {
                            $httpCode = (int)$hm[1];
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $body = false;
            }
        }

        return ['body' => $body, 'http_code' => $httpCode];
    }
}

