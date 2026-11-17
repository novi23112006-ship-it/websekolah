<?php

/**
 * Chatbot_model
 * Chatbot kini beroperasi tanpa database (stateless / direct AI).
 * Metode pembantu dipertahankan agar tidak merusak ketergantungan lama jika ada.
 */
class Chatbot_model {
    public function __construct() {
        // Chatbot tidak lagi menggunakan database
    }

    public function simpanRiwayat($user_id, $role, $message, $response) {
        // Tidak menyimpan riwayat ke database
        return true;
    }

    public function getRiwayat($user_id, $limit = 5) {
        // Mengembalikan array kosong karena chatbot tidak menggunakan database
        return [];
    }
}
