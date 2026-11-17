<?php

class Flasher {
    public static function setFlash($pesan, $aksi, $tipe) {
        $_SESSION['flash'] = [
            'pesan' => $pesan,
            'aksi'  => $aksi,
            'tipe'  => $tipe
        ];
    }

    public static function flash() {
        if (isset($_SESSION['flash'])) {
            $pesan = htmlspecialchars($_SESSION['flash']['pesan'], ENT_QUOTES, 'UTF-8');
            $aksi = htmlspecialchars($_SESSION['flash']['aksi'], ENT_QUOTES, 'UTF-8');
            $tipe = htmlspecialchars($_SESSION['flash']['tipe'], ENT_QUOTES, 'UTF-8');
            
            $swalIcon = ($tipe === 'success') ? 'success' : (($tipe === 'danger') ? 'error' : (($tipe === 'warning') ? 'warning' : 'info'));
            $swalTitle = ucfirst($pesan);
            
            echo '<div class="alert alert-' . $tipe . ' alert-dismissible fade show" role="alert">
                    <strong>' . $pesan . '</strong> ' . $aksi . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';

            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    if (typeof Swal !== "undefined") {
                        Swal.fire({
                            icon: "' . $swalIcon . '",
                            title: "' . addslashes($swalTitle) . '",
                            html: "' . addslashes($aksi) . '",
                            confirmButtonColor: "#0d6efd",
                            confirmButtonText: "Tutup",
                            timer: ' . ($tipe === 'success' ? '4000' : '6000') . ',
                            timerProgressBar: true
                        });
                    }
                });
            </script>';

            unset($_SESSION['flash']);
        }
    }
}
