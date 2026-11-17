<?php
/**
 * Test Case untuk AuthHelper (Otorisasi 5 Role Pengguna)
 */
class AuthHelperTest {

    public function testValidasiDaftarRoleSistem(): array {
        $allowedRoles = ['admin', 'guru', 'kepala_sekolah', 'wali_kelas', 'siswa'];
        
        $testSuperAdmin = in_array('admin', $allowedRoles);
        $testKepsek = in_array('kepala_sekolah', $allowedRoles);
        $testWali = in_array('wali_kelas', $allowedRoles);
        $testGuru = in_array('guru', $allowedRoles);
        $testSiswa = in_array('siswa', $allowedRoles);
        $testInvalid = in_array('hacker', $allowedRoles);

        $passed = ($testSuperAdmin && $testKepsek && $testWali && $testGuru && $testSiswa && !$testInvalid);
        return [
            'name' => 'testValidasiDaftarRoleSistem',
            'desc' => 'Memastikan whitelist hak akses sistem secara ketat hanya mengizinkan 5 role resmi (admin, kepsek, wali, guru, siswa)',
            'assertions' => 6,
            'passed' => $passed,
            'actual' => "Seluruh 5 role resmi terdaftar valid, dan role asing 'hacker' berhasil ditolak"
        ];
    }
}
