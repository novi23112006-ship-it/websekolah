<?php
/**
 * Test Case untuk LokasiHelper (Haversine Formula & Geofencing)
 */
class LokasiHelperTest {

    public function testHitungJarakMeterHaversineTepat(): array {
        // Koordinat titik 1: Monas Jakarta (-6.175392, 106.827153)
        // Koordinat titik 2: Balai Kota Jakarta (-6.180495, 106.828343) ~ Jarak lurus ~580 meter
        $jarak = LokasiHelper::hitungJarakMeter(-6.175392, 106.827153, -6.180495, 106.828343);
        
        $passed = ($jarak >= 560 && $jarak <= 600);
        return [
            'name' => 'testHitungJarakMeterHaversineTepat',
            'desc' => 'Menghitung jarak geodesik dua titik koordinat GPS menggunakan formula Haversine',
            'assertions' => 1,
            'passed' => $passed,
            'actual' => "Jarak terhitung: {$jarak} meter (rentang toleransi 560-600m)"
        ];
    }

    public function testHitungJarakTitikSamaNolMeter(): array {
        $lat = -6.208763;
        $lon = 106.845599;
        $jarak = LokasiHelper::hitungJarakMeter($lat, $lon, $lat, $lon);
        
        $passed = ($jarak === 0);
        return [
            'name' => 'testHitungJarakTitikSamaNolMeter',
            'desc' => 'Menghitung jarak antara dua koordinat yang identik harus menghasilkan nilai 0 meter',
            'assertions' => 1,
            'passed' => $passed,
            'actual' => "Jarak terhitung: {$jarak} meter"
        ];
    }

    public function testValidasiJarakDalamRadius100Meter(): array {
        // Asumsikan sekolah di (-6.208763, 106.845599)
        // Pengguna berada di jarak ~45 meter (-6.208500, 106.845800)
        $cfg = LokasiHelper::getPengaturanLokasi();
        $jarak = LokasiHelper::hitungJarakMeter($cfg['latitude'], $cfg['longitude'], $cfg['latitude'] + 0.0003, $cfg['longitude'] + 0.0002);
        
        $passed = ($jarak <= 100);
        return [
            'name' => 'testValidasiJarakDalamRadius100Meter',
            'desc' => 'Verifikasi bahwa pengguna dalam radius <= 100 meter dari sekolah diizinkan presensi',
            'assertions' => 1,
            'passed' => $passed,
            'actual' => "Jarak terhitung: {$jarak} meter (diizinkan presensi)"
        ];
    }

    public function testValidasiJarakDiLuarRadius100MeterDitolak(): array {
        $cfg = LokasiHelper::getPengaturanLokasi();
        // Jarak jauh (~2 km)
        $jarak = LokasiHelper::hitungJarakMeter($cfg['latitude'], $cfg['longitude'], $cfg['latitude'] + 0.02, $cfg['longitude'] + 0.02);
        
        $passed = ($jarak > 100);
        return [
            'name' => 'testValidasiJarakDiLuarRadius100MeterDitolak',
            'desc' => 'Verifikasi bahwa pengguna di luar radius > 100 meter dari sekolah ditolak presensinya',
            'assertions' => 1,
            'passed' => $passed,
            'actual' => "Jarak terhitung: {$jarak} meter (status ditolak sesuai aturan)"
        ];
    }
}
