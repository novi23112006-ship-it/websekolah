<?php

class LokasiHelper {
    private static $configFile = __DIR__ . '/../../config/lokasi_sekolah.json';

    /**
     * Ambil konfigurasi lokasi dan radius absensi sekolah.
     */
    public static function getPengaturanLokasi() {
        $default = [
            'nama_sekolah'   => 'SMK Negeri',
            'latitude'       => -6.208763,
            'longitude'      => 106.845599,
            'radius_meter'   => 100, // Batasan jarak 100 meter
            'geofence_aktif' => false // Default nonaktif
        ];

        if (file_exists(self::$configFile)) {
            $content = file_get_contents(self::$configFile);
            $json = json_decode($content, true);
            if (is_array($json)) {
                return array_merge($default, $json);
            }
        }

        return $default;
    }

    /**
     * Simpan pembaruan titik koordinat dan batas jarak sekolah.
     */
    public static function savePengaturanLokasi($data) {
        $default = [
            'nama_sekolah'   => 'SMK Negeri',
            'latitude'       => -6.208763,
            'longitude'      => 106.845599,
            'radius_meter'   => 100, // Batasan jarak 100 meter
            'geofence_aktif' => false
        ];

        if (file_exists(self::$configFile)) {
            $content = file_get_contents(self::$configFile);
            $json = json_decode($content, true);
            if (is_array($json)) {
                $default = array_merge($default, $json);
            }
        }

        $payload = [
            'nama_sekolah'   => !empty($data['nama_sekolah']) ? trim($data['nama_sekolah']) : $default['nama_sekolah'],
            'latitude'       => isset($data['latitude']) && is_numeric($data['latitude']) ? (float)$data['latitude'] : (float)$default['latitude'],
            'longitude'      => isset($data['longitude']) && is_numeric($data['longitude']) ? (float)$data['longitude'] : (float)$default['longitude'],
            'radius_meter'   => isset($data['radius_meter']) && (int)$data['radius_meter'] > 0 ? (int)$data['radius_meter'] : 100,
            'geofence_aktif' => isset($data['geofence_aktif']) ? (bool)$data['geofence_aktif'] : false,
            'updated_at'     => date('Y-m-d H:i:s')
        ];

        $dir = dirname(self::$configFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(self::$configFile, json_encode($payload, JSON_PRETTY_PRINT));
        return $payload;
    }

    /**
     * Hitung jarak lurus (geodesik) antara dua titik koordinat dalam meter menggunakan rumus Haversine.
     */
    public static function hitungJarakMeter($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000; // Radius rata-rata bumi dalam meter

        $lat1Rad = deg2rad((float)$lat1);
        $lon1Rad = deg2rad((float)$lon1);
        $lat2Rad = deg2rad((float)$lat2);
        $lon2Rad = deg2rad((float)$lon2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return (int)round($distance);
    }

    /**
     * Validasi apakah koordinat pengguna berada di dalam radius maksimal (maksimal 100 meter).
     */
    public static function validasiJarak($userLat, $userLng, $userRole = null) {
        $config = self::getPengaturanLokasi();

        // 1. Jika batasan jarak (geofencing) dinonaktifkan:
        if (empty($config['geofence_aktif']) || $config['geofence_aktif'] === false) {
            $jarak = null;
            if ($userLat !== null && $userLng !== null && $userLat !== '' && $userLng !== '' && is_numeric($userLat) && is_numeric($userLng)) {
                $jarak = self::hitungJarakMeter($userLat, $userLng, $config['latitude'], $config['longitude']);
            }
            return [
                'valid'          => true,
                'jarak'          => $jarak ?? 0,
                'max_radius'     => 100,
                'di_luar_radius' => false,
                'pesan'          => 'Batasan jarak absensi dinonaktifkan (bebas radius).'
            ];
        }

        // 2. Batasan jarak maksimal STRICT 100 meter (sesuai aturan sistem tidak boleh lebih dari 100 meter)
        $maxRadius = 100;
        if (!empty($config['radius_meter']) && (int)$config['radius_meter'] > 0) {
            $maxRadius = min(100, (int)$config['radius_meter']);
        }

        // Jika koordinat GPS tersedia, hitung dan validasi jarak
        if ($userLat !== null && $userLng !== null && $userLat !== '' && $userLng !== '' && is_numeric($userLat) && is_numeric($userLng)) {
            $jarak = self::hitungJarakMeter($userLat, $userLng, $config['latitude'], $config['longitude']);
            if ($jarak > $maxRadius) {
                return [
                    'valid'          => false,
                    'jarak'          => $jarak,
                    'max_radius'     => $maxRadius,
                    'di_luar_radius' => true,
                    'pesan'          => "Absensi ditolak: Anda berada di luar area sekolah! Jarak Anda saat ini {$jarak} meter dari sekolah (Batas maksimal adalah {$maxRadius} meter)."
                ];
            }
            return [
                'valid'          => true,
                'jarak'          => $jarak,
                'max_radius'     => $maxRadius,
                'di_luar_radius' => false,
                'pesan'          => "Lokasi terverifikasi ({$jarak}m dari sekolah)."
            ];
        }

        // Jika koordinat GPS tidak dikirim atau tidak terdeteksi saat geofence aktif
        return [
            'valid'          => false,
            'jarak'          => null,
            'max_radius'     => $maxRadius,
            'di_luar_radius' => true,
            'pesan'          => "Absensi ditolak: Koordinat GPS tidak terdeteksi. Harap aktifkan GPS dan izinkan akses lokasi pada browser untuk memastikan Anda berada dalam radius maksimal {$maxRadius} meter dari sekolah."
        ];
    }
}
