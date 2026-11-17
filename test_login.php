<?php
require 'config/config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $conn->query("SELECT * FROM users WHERE username='siswa'");
$user = $res->fetch_assoc();
echo 'Found user: ' . $user['username'] . "\n";
echo 'Hash: ' . $user['password'] . "\n";
echo 'Verify (siswa): ' . (password_verify('siswa', $user['password']) ? 'OK' : 'FAIL') . "\n";
?>
