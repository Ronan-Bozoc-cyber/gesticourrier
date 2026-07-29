<?php
header('Content-Type: application/json');
require_once __DIR__ . '/models/CourrierModel.php';

$courriers = CourrierModel::getAllDepart();
echo json_encode($courriers);
?>
