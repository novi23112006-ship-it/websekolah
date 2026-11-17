<?php
/**
 * OpenAiAdapter
 * Adapter komunikasi dengan OpenAI Chat Completions API & endpoint yang kompatibel
 * (Groq, DeepSeek, OpenRouter, Mistral, Ollama, vLLM, dll).
 */
require_once __DIR__ . '/AiProviderInterface.php';

class OpenAiAdapter implements AiProviderInterface {

    private const DEFAULT_BASE_URL = 'https://api.openai.com/v1';
    private const DEFAULT_MODEL    = 'gpt-4o-mini';

    private bool $isCustom;

    public function __construct(bool $isCustom = false) {
        $this->isCustom = $isCustom;
    }

    public function getId(): string {
        return $this->isCustom ? 'custom' : 'openai';
    }

    public function getName(): string {
        return $this->isCustom ? 'OpenAI-Compatible (Custom / Groq / DeepSeek)' : 'OpenAI ChatGPT';
    }

    public function getDefaultModel(): string {
        return $this->isCustom ? 'llama-3.3-70b-versatile' : self::DEFAULT_MODEL;
    }

    /**
     * Susun URL endpoint chat completions
     */
    private function resolveEndpoint(?string $baseUrl): string {
        $base = trim($baseUrl ?? '');
        if (empty($base)) {
            $base = self::DEFAULT_BASE_URL;
        }
        $base = rtrim($base, '/');
        if (str_ends_with($base, '/chat/completions')) {
            return $base;
        }
        return $base . '/chat/completions';
    }

    /**
     * Kirim percakapan ke OpenAI / Compatible API
     */
    public function chat($systemPrompt, $userMessage, array $history = [], array $options = []): array {
        $apiKey = !empty($options['apiKey']) ? $options['apiKey'] : (defined('AI_API_KEY') ? AI_API_KEY : '');
        if (empty($apiKey) && !$this->isCustom) {
            return [
                'success' => false,
                'message' => '',
                'error'   => 'API Key OpenAI belum dikonfigurasi.'
            ];
        }

        $baseUrl     = !empty($options['baseUrl']) ? $options['baseUrl'] : (defined('AI_BASE_URL') ? AI_BASE_URL : null);
        $endpoint    = $this->resolveEndpoint($baseUrl);
        $model       = !empty($options['model']) ? trim($options['model']) : (defined('AI_MODEL') && AI_MODEL ? AI_MODEL : $this->getDefaultModel());
        $temperature = isset($options['temperature']) ? (float)$options['temperature'] : (defined('AI_TEMPERATURE') ? (float)AI_TEMPERATURE : 0.2);
        $maxTokens   = isset($options['maxTokens']) ? (int)$options['maxTokens'] : (defined('AI_MAX_TOKENS') ? (int)AI_MAX_TOKENS : 350);

        // Bangun array pesan
        $messages = [];
        if (!empty($systemPrompt)) {
            $messages[] = [
                'role'    => 'system',
                'content' => $systemPrompt
            ];
        }

        if (!empty($history) && is_array($history)) {
            foreach ($history as $h) {
                $role = ($h['role'] === 'user') ? 'user' : 'assistant';
                $text = trim($h['text'] ?? '');
                if ($text !== '') {
                    $messages[] = ['role' => $role, 'content' => $text];
                }
            }
        }

        $messages[] = [
            'role'    => 'user',
            'content' => $userMessage
        ];

        $payload = json_encode([
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
        ]);

        $res = $this->executeHttpRequest($endpoint, $payload, $apiKey, 5.0);

        if ($res['http_code'] === 200 && !empty($res['body'])) {
            $json = json_decode($res['body'], true);
            $content = $json['choices'][0]['message']['content'] ?? null;
            if ($content !== null) {
                $cleaned = trim($content);
                return [
                    'success' => true,
                    'message' => $cleaned,
                    'error'   => null
                ];
            }
        }

        $errMsg = 'Gagal mendapatkan respon dari AI.';
        if (!empty($res['body'])) {
            $json = json_decode($res['body'], true);
            if (!empty($json['error']['message'])) {
                $errMsg = $json['error']['message'];
            }
        }

        return [
            'success' => false,
            'message' => '',
            'error'   => $errMsg . " (HTTP {$res['http_code']})"
        ];
    }

    /**
     * Uji koneksi ke endpoint
     */
    public function testConnection($apiKey, $model = null, $baseUrl = null): array {
        $key = trim($apiKey ?? '');
        $endpoint = $this->resolveEndpoint($baseUrl);
        $chosenModel = !empty($model) ? trim($model) : $this->getDefaultModel();

        if (empty($key) && !str_contains($endpoint, 'localhost') && !str_contains($endpoint, '127.0.0.1')) {
            return [
                'status'    => false,
                'http_code' => 0,
                'pesan'     => 'API Key belum diisi. Masukkan API Key Anda.',
                'model'     => $chosenModel,
                'reply'     => ''
            ];
        }

        $payload = json_encode([
            'model'       => $chosenModel,
            'messages'    => [
                ['role' => 'system', 'content' => 'Kamu adalah asisten penguji koneksi sistem sekolah.'],
                ['role' => 'user', 'content' => 'Balas tepat 1 kata: AKTIF']
            ],
            'temperature' => 0.1,
            'max_tokens'  => 10
        ]);

        $res = $this->executeHttpRequest($endpoint, $payload, $key, 5.0);
        $httpCode = $res['http_code'];

        if ($httpCode === 200 && !empty($res['body'])) {
            $json = json_decode($res['body'], true);
            $reply = trim($json['choices'][0]['message']['content'] ?? 'AKTIF');
            $providerLabel = $this->getName();
            return [
                'status'    => true,
                'http_code' => 200,
                'pesan'     => "Koneksi ke {$providerLabel} Berhasil! Model [{$chosenModel}] aktif.",
                'model'     => $chosenModel,
                'reply'     => $reply
            ];
        }

        $errorDetail = '';
        if (!empty($res['body'])) {
            $json = json_decode($res['body'], true);
            if (!empty($json['error']['message'])) {
                $errorDetail = $json['error']['message'];
            }
        }

        if ($httpCode === 401) {
            $pesan = 'API Key tidak valid atau otorisasi gagal (HTTP 401). Periksa kembali API Key Anda.';
        } elseif ($httpCode === 404) {
            $pesan = "Model [{$chosenModel}] atau URL endpoint tidak ditemukan (HTTP 404). Periksa nama model & Base URL.";
        } elseif ($httpCode === 429) {
            $pesan = 'Batas kuota/rate limit terlampaui (HTTP 429). Periksa saldo kuota kredit API Anda.';
        } elseif (!empty($errorDetail)) {
            $pesan = "Gagal terhubung (HTTP {$httpCode}): {$errorDetail}";
        } else {
            $pesan = "Gagal terhubung ke endpoint API (HTTP {$httpCode}). Periksa URL endpoint dan koneksi internet server.";
        }

        return [
            'status'    => false,
            'http_code' => $httpCode,
            'pesan'     => $pesan,
            'model'     => $chosenModel,
            'reply'     => ''
        ];
    }

    /**
     * Transport HTTP dual-mode (cURL + stream context)
     */
    private function executeHttpRequest($url, $payload, $apiKey, $timeout = 5.0): array {
        $body = false;
        $httpCode = 0;

        $headers = [
            'Content-Type: application/json'
        ];
        if (!empty($apiKey)) {
            $headers[] = 'Authorization: Bearer ' . $apiKey;
        }

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
                    CURLOPT_CONNECTTIMEOUT => 3,
                    CURLOPT_HTTPHEADER     => $headers
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
                $rawHeader = "Content-Type: application/json\r\n";
                if (!empty($apiKey)) {
                    $rawHeader .= "Authorization: Bearer {$apiKey}\r\n";
                }
                $streamCtx = stream_context_create([
                    'http' => [
                        'method'        => 'POST',
                        'header'        => $rawHeader,
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

