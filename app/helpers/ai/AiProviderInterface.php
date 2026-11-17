<?php
/**
 * AiProviderInterface
 * Kontrak seragam untuk seluruh adapter provider AI Chatbot Sistem Absensi SMK.
 */
interface AiProviderInterface {

    /**
     * Kirim permintaan percakapan ke AI Provider
     *
     * @param string $systemPrompt Instruksi utama / role-based context
     * @param string $userMessage Pesan pengguna
     * @param array $history Riwayat percakapan sebelumnya [['role' => 'user'|'model'|'assistant', 'text' => string]]
     * @param array $options Opsi tambahan (apiKey, model, baseUrl, temperature, maxTokens)
     * @return array Format seragam: ['success' => bool, 'message' => string, 'error' => string|null]
     */
    public function chat($systemPrompt, $userMessage, array $history = [], array $options = []): array;

    /**
     * Uji koneksi cepat ke API Provider
     *
     * @param string $apiKey Kunci API
     * @param string|null $model Nama model
     * @param string|null $baseUrl URL dasar endpoint kustom (jika didukung)
     * @return array Format seragam: ['status' => bool, 'http_code' => int, 'pesan' => string, 'model' => string, 'reply' => string]
     */
    public function testConnection($apiKey, $model = null, $baseUrl = null): array;

    /**
     * Nama identifikasi unik provider (misal: gemini, openai, custom)
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Nama label tampilan provider (misal: Google Gemini AI, OpenAI ChatGPT)
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Model bawaan/rekomendasi untuk provider ini
     *
     * @return string
     */
    public function getDefaultModel(): string;
}

