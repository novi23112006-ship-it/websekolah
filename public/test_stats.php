<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';
require_once '../config/config.php';
require_once '../app/models/Dashboard_model.php';

$model = new Dashboard_model();
$stats = $model->getSiswaStats(268);
if ($stats === false) {
    echo "FALSE\n";
} else {
    print_r($stats);
}
?>
