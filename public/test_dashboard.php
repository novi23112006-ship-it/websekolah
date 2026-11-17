<?php
require 'app/init.php';
$model = new Dashboard_model();
$stats = $model->getSiswaStats(268);
print_r($stats);
?>
