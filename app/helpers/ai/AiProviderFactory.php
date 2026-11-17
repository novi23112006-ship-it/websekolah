<?php
/**
 * AiProviderFactory
 * Factory untuk menghasilkan instance adapter provider AI sesuai konfigurasi sistem.
 */
require_once __DIR__ . '/AiProviderInterface.php';
require_once __DIR__ . '/GeminiAdapter.php';
require_once __DIR__ . '/OpenAiAdapter.php';

class AiProviderFactory {

    private static ?array $instances = null;

    /**
     * Dapatkan instance adapter provider AI
     *
     * @param string|null $provider 'gemini', 'openai', atau 'custom'
     * @return AiProviderInterface
     */
    public static function getAdapter(?string $provider = null): AiProviderInterface {
        $p = strtolower(trim($provider ?? ''));
        if (empty($p)) {
            $p = defined('AI_PROVIDER') ? strtolower(trim(AI_PROVIDER)) : 'gemini';
        }

        switch ($p) {
            case 'openai':
                return new OpenAiAdapter(false);
            case 'custom':
            case 'openai_compatible':
            case 'groq':
            case 'deepseek':
            case 'openrouter':
            case 'ollama':
                return new OpenAiAdapter(true);
            case 'gemini':
            default:
                return new GeminiAdapter();
        }
    }

    /**
     * Daftar seluruh provider yang didukung beserta konfigurasi standarnya
     *
     * @return array
     */
    public static function getSupportedProviders(): array {
        return [
            'gemini' => [
                'id'            => 'gemini',
                'name'          => 'Google Gemini AI',
                'badge'         => 'Rekomendasi (Cepat & Gratis)',
                'badge_class'   => 'success',
                'default_model' => 'gemini-3.1-flash-lite-preview',
                'models'        => [
                    'gemini-3.1-flash-lite-preview' => 'Gemini 3.1 Flash Lite Preview (Tercepat)',
                    'gemini-3.5-flash-lite'         => 'Gemini 3.5 Flash Lite',
                    'gemini-3-flash-preview'        => 'Gemini 3 Flash Preview',
                    'gemini-3.1-flash-lite'         => 'Gemini 3.1 Flash Lite',
                    'gemini-1.5-flash'              => 'Gemini 1.5 Flash (Stabil)',
                    'gemini-1.5-pro'                => 'Gemini 1.5 Pro (Akurasi Tinggi)'
                ],
                'needs_base_url'=> false,
                'default_base_url' => '',
                'key_placeholder' => 'Tempel Google Gemini API Key (misal: AQ... / AIza...)',
                'description'   => 'Didukung langsung oleh Google Generative AI API v1beta.'
            ],
            'openai' => [
                'id'            => 'openai',
                'name'          => 'OpenAI ChatGPT',
                'badge'         => 'Resmi OpenAI',
                'badge_class'   => 'primary',
                'default_model' => 'gpt-4o-mini',
                'models'        => [
                    'gpt-4o-mini'    => 'GPT-4o Mini (Hemat & Cepat)',
                    'gpt-4o'         => 'GPT-4o (Kemampuan Tertinggi)',
                    'gpt-3.5-turbo'  => 'GPT-3.5 Turbo (Klasik)'
                ],
                'needs_base_url'=> false,
                'default_base_url' => 'https://api.openai.com/v1',
                'key_placeholder' => 'Tempel OpenAI API Key (sk-...)',
                'description'   => 'Menggunakan endpoint resmi OpenAI Chat Completions API.'
            ],
            'custom' => [
                'id'            => 'custom',
                'name'          => 'Custom / OpenAI-Compatible (Groq, DeepSeek, OpenRouter, Ollama)',
                'badge'         => 'Fleksibel',
                'badge_class'   => 'info',
                'default_model' => 'llama-3.3-70b-versatile',
                'models'        => [
                    'llama-3.3-70b-versatile' => 'Groq: LLaMA 3.3 70B Versatile (Super Cepat)',
                    'llama-3.1-8b-instant'    => 'Groq: LLaMA 3.1 8B Instant',
                    'deepseek-chat'           => 'DeepSeek: DeepSeek-V3 (deepseek-chat)',
                    'deepseek-reasoner'       => 'DeepSeek: DeepSeek-R1 (deepseek-reasoner)',
                    'meta-llama/llama-3.3-70b-instruct' => 'OpenRouter: LLaMA 3.3 70B'
                ],
                'needs_base_url'=> true,
                'default_base_url' => 'https://api.groq.com/openai/v1',
                'key_placeholder' => 'Tempel API Key provider (gsk_... untuk Groq, sk-... untuk DeepSeek)',
                'description'   => 'Mendukung sembarang provider dengan format OpenAI Chat Completions standar.'
            ]
        ];
    }
}

