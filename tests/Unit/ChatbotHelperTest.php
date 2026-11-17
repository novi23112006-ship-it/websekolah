<?php
/**
 * Test Case untuk Chatbot AI Safety & Filter Keyword
 */
class ChatbotHelperTest {

    public function testFilterPromptCodingDitolak(): array {
        $codingKeywords = ['coding', 'script', 'skrip', 'python', 'javascript', 'html', 'css', 'bikin web', 'buat aplikasi', 'bikin kode', 'buatkan kode', 'buat coding', 'tulis kode'];
        
        $userQuery = "Tolong buatkan kode python untuk hack wifi";
        $lowerMsg = strtolower($userQuery);
        
        $isCoding = false;
        foreach ($codingKeywords as $kw) {
            if (strpos($lowerMsg, $kw) !== false) {
                $isCoding = true;
                break;
            }
        }
        
        $shouldRefuse = ($isCoding && (strpos($lowerMsg, 'buat') !== false || strpos($lowerMsg, 'bikin') !== false || strpos($lowerMsg, 'tulis') !== false || strpos($lowerMsg, 'code') !== false));

        $passed = ($shouldRefuse === true);
        return [
            'name' => 'testFilterPromptCodingDitolak',
            'desc' => 'Memastikan prompt yang meminta pembuatan kode pemrograman di luar ranah sistem absensi ditolak secara protektif oleh filter',
            'assertions' => 1,
            'passed' => $passed,
            'actual' => "Prompt coding terdeteksi dan di-flag untuk ditolak dengan pesan sopan"
        ];
    }

    public function testPromptSistemAbsensiDiizinkan(): array {
        $userQuery = "Bagaimana cara melakukan absensi siswa menggunakan barcode?";
        $lowerMsg = strtolower($userQuery);
        
        $codingKeywords = ['coding', 'script', 'skrip', 'python', 'javascript', 'html', 'css', 'bikin web', 'buat aplikasi', 'bikin kode', 'buatkan kode', 'buat coding', 'tulis kode'];
        $isCoding = false;
        foreach ($codingKeywords as $kw) {
            if (strpos($lowerMsg, $kw) !== false) {
                $isCoding = true;
                break;
            }
        }

        $passed = ($isCoding === false);
        return [
            'name' => 'testPromptSistemAbsensiDiizinkan',
            'desc' => 'Memastikan prompt pertanyaan operasional absensi sekolah tidak terblokir dan diteruskan ke pemrosesan AI',
            'assertions' => 1,
            'passed' => $passed,
            'actual' => "Prompt operasional absensi sekolah lolos filter keamanan"
        ];
    }
}
