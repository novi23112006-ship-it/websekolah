<?php
/**
 * Test Case untuk DateHelper (Konversi Waktu & Bahasa Indonesia)
 */
class DateHelperTest {

    public function testKonversiHariIndonesia(): array {
        $senin = DateHelper::$hariMap['Monday'] ?? '';
        $jumat = DateHelper::$hariMap['Friday'] ?? '';
        $minggu = DateHelper::$hariMap['Sunday'] ?? '';

        $passed = ($senin === 'Senin' && $jumat === 'Jumat' && $minggu === 'Minggu');
        return [
            'name' => 'testKonversiHariIndonesia',
            'desc' => 'Memastikan pemetaan hari bahasa Inggris (Monday, Friday, Sunday) dikonversi tepat ke bahasa Indonesia',
            'assertions' => 3,
            'passed' => $passed,
            'actual' => "Hasil konversi: Monday->{$senin}, Friday->{$jumat}, Sunday->{$minggu}"
        ];
    }

    public function testKonversiBulanIndonesia(): array {
        $jan = DateHelper::$bulanMap['01'] ?? '';
        $agu = DateHelper::$bulanMap['08'] ?? '';
        $des = DateHelper::$bulanMap['12'] ?? '';

        $passed = ($jan === 'Januari' && $agu === 'Agustus' && $des === 'Desember');
        return [
            'name' => 'testKonversiBulanIndonesia',
            'desc' => 'Memastikan kode bulan (01, 08, 12) dikonversi tepat ke nama bulan baku bahasa Indonesia',
            'assertions' => 3,
            'passed' => $passed,
            'actual' => "Hasil konversi: 01->{$jan}, 08->{$agu}, 12->{$des}"
        ];
    }
}
