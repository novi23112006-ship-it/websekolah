<?php

class AuthHelper {
    public static function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            Flasher::setFlash('Akses Ditolak', 'Silakan login terlebih dahulu.', 'danger');
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }
    }

    public static function requireRole($role) {
        self::requireLogin();
        
        if ($_SESSION['role'] !== $role) {
            header('Location: ' . BASEURL . '/auth/forbidden');
            exit;
        }
    }

    /**
     * Izinkan akses jika role saat ini ada dalam daftar $roles.
     */
    public static function requireOneOfRoles(array $roles) {
        self::requireLogin();

        if (!in_array($_SESSION['role'], $roles)) {
            header('Location: ' . BASEURL . '/auth/forbidden');
            exit;
        }
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function redirectBasedOnRole() {
        if (self::isLoggedIn()) {
            $role = $_SESSION['role'];
            switch ($role) {
                case 'admin':
                    header('Location: ' . BASEURL . '/admin');
                    exit;
                case 'guru':
                    header('Location: ' . BASEURL . '/guru');
                    exit;
                case 'kepala_sekolah':
                    header('Location: ' . BASEURL . '/kepala_sekolah');
                    exit;
                case 'wali_kelas':
                    header('Location: ' . BASEURL . '/wali_kelas');
                    exit;
                case 'siswa':
                    header('Location: ' . BASEURL . '/siswa');
                    exit;
                default:
                    header('Location: ' . BASEURL . '/auth/login');
                    exit;
            }
        }
    }

    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken($token) {
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            Flasher::setFlash('Akses Ditolak', 'Token keamanan tidak valid. Silakan ulangi aksi Anda.', 'danger');
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }
    }
}
