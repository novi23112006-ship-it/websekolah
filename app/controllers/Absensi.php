<?php

class Absensi extends Controller {
    
    // Halaman ini mungkin diakses secara publik (sebagai mesin kiosk), 
    // atau dilindungi oleh role tertentu. Untuk sekarang kita buat publik untuk mesin.
    // Jika perlu dilindungi, kita bisa panggil AuthHelper.

    public function index() {
        // Redirect default ke siswa
        header('Location: ' . BASEURL . '/absensi/siswa');
        exit;
    }

    public function siswa() {
        header('Location: ' . BASEURL . '/absensi_siswa');
        exit;
    }

    public function guru() {
        header('Location: ' . BASEURL . '/guru_absensi');
        exit;
    }
}
