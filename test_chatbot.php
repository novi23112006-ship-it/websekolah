<?php
require_once 'config/config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Controller.php';
require_once 'app/models/Chatbot_model.php';

echo "Testing Chatbot Model (Database-free)...\n";

$model = new Chatbot_model();
$insert = $model->simpanRiwayat(1, 'admin', 'Halo', 'Hai');
echo "Simpan Riwayat: " . ($insert ? "OK (No DB used)" : "FAILED") . "\n";

$history = $model->getRiwayat(1, 5);
echo "Get Riwayat count: " . count($history) . " (Expected: 0)\n";

$db = new Database();
$db->query("SHOW TABLES LIKE 'chat_history'");
$res = $db->resultSet();
echo "Table chat_history exists in DB: " . (empty($res) ? "NO (Verified successfully removed)" : "YES (Error)") . "\n";
