<?php

class DateHelper {
    public static $hariMap = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu'
    ];

    public static $bulanMap = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];

    /**
     * Dapatkan nama hari ini dalam Bahasa Indonesia (Senin s/d Minggu)
     */
    public static function hariIni() {
        return self::$hariMap[date('l')] ?? 'Senin';
    }

    /**
     * Format tanggal Indonesia, contoh: "Jumat, 23 Oktober 2026"
     */
    public static function tanggalIndo($date = null, $withDay = true) {
        $time = $date ? strtotime($date) : time();
        $hari = self::$hariMap[date('l', $time)] ?? date('l', $time);
        $tgl  = date('d', $time);
        $bln  = self::$bulanMap[date('m', $time)] ?? date('F', $time);
        $thn  = date('Y', $time);

        if ($withDay) {
            return "{$hari}, {$tgl} {$bln} {$thn}";
        }
        return "{$tgl} {$bln} {$thn}";
    }

    /**
     * Dapatkan waktu sekarang (WIB)
     */
    public static function jamSekarang($format = 'H:i:s') {
        return date($format);
    }

    /**
     * Dapatkan daftar hari standar sekolah
     */
    public static function getDaftarHari($includeSabtu = true) {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        if ($includeSabtu) {
            $days[] = 'Sabtu';
        }
        return $days;
    }

    /**
     * Hitung status KBM secara sinkron dan real-time berdasarkan hari dan jam sekarang
     */
    public static function getStatusKBM($hari, $jamMulai, $jamSelesai) {
        $hariIni = self::hariIni();
        $currentTime = date('H:i:s');

        $mulai = (strlen($jamMulai) === 5) ? $jamMulai . ':00' : $jamMulai;
        $selesai = (strlen($jamSelesai) === 5) ? $jamSelesai . ':00' : $jamSelesai;

        if ($hari !== $hariIni) {
            return [
                'status'    => 'terjadwal',
                'label'     => 'Terjadwal',
                'badge'     => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25',
                'icon'      => 'bi-calendar-check',
                'is_now'    => false,
                'is_passed' => false
            ];
        }

        if ($currentTime >= $mulai && $currentTime <= $selesai) {
            return [
                'status'    => 'sedang_kbm',
                'label'     => 'Sedang KBM',
                'badge'     => 'bg-success text-white shadow-xs',
                'icon'      => 'bi-broadcast',
                'is_now'    => true,
                'is_passed' => false
            ];
        }

        if ($currentTime > $selesai) {
            return [
                'status'    => 'selesai',
                'label'     => 'Selesai',
                'badge'     => 'bg-secondary-subtle text-secondary border',
                'icon'      => 'bi-check2-all',
                'is_now'    => false,
                'is_passed' => true
            ];
        }

        return [
            'status'    => 'akan_datang',
            'label'     => 'Akan Datang',
            'badge'     => 'bg-primary-subtle text-primary border border-primary-subtle',
            'icon'      => 'bi-hourglass-split',
            'is_now'    => false,
            'is_passed' => false
        ];
    }
}
