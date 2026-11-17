<?php
// snapshot_helper.php - Helper to capture full UI screenshots
require_once '../app/init.php';

$page = $_GET['page'] ?? 'admin';

// Map pages to user accounts
$accounts = [
    'admin' => ['user_id' => 1, 'username' => 'admin', 'role' => 'admin', 'nama' => 'Administrator'],
    'kepsek' => ['user_id' => 2, 'username' => 'kepsek', 'role' => 'kepala_sekolah', 'nama' => 'Drs. Budi Santoso'],
    'wali' => ['user_id' => 5, 'username' => 'walikelas1', 'role' => 'wali_kelas', 'nama' => 'Rina Marlina, S.Pd'],
    'guru' => ['user_id' => 3, 'username' => 'guru1', 'role' => 'guru', 'nama' => 'Siti Aminah, S.Pd'],
    'siswa' => ['user_id' => 35, 'username' => 'ahmad_fauzi_s', 'role' => 'siswa', 'nama' => 'Ahmad Fauzi'],
    'chatbot' => ['user_id' => 1, 'username' => 'admin', 'role' => 'admin', 'nama' => 'Administrator']
];

$user = $accounts[$page] ?? $accounts['admin'];

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['nama'] = $user['nama'];

// Add script to open chatbot if requested
if ($page === 'chatbot') {
    register_shutdown_function(function() {
        echo "<script>
            window.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    var btn = document.getElementById('chatBtn') || document.querySelector('.btn-chatbot') || document.querySelector('[onclick*=\"toggleChat\"]');
                    if (btn) btn.click();
                    var modal = document.getElementById('chatBox') || document.getElementById('chatWidget') || document.getElementById('chatbotModal');
                    if (modal) modal.style.display = 'block';
                }, 500);
            });
        </script>";
    });
}

switch($page) {
    case 'kepsek':
        require_once '../app/controllers/Kepala_sekolah.php';
        $ctrl = new Kepala_sekolah();
        $ctrl->index();
        break;
    case 'wali':
        require_once '../app/controllers/Wali_kelas.php';
        $ctrl = new Wali_kelas();
        $ctrl->index();
        break;
    case 'guru':
        require_once '../app/controllers/Guru.php';
        $ctrl = new Guru();
        $ctrl->index();
        break;
    case 'siswa':
        require_once '../app/controllers/Siswa.php';
        $ctrl = new Siswa();
        $ctrl->index();
        break;
    case 'chatbot':
    case 'admin':
    default:
        require_once '../app/controllers/Admin.php';
        $ctrl = new Admin();
        $ctrl->index();
        break;
}
