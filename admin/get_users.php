<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/UserModel.php';

$users = UserModel::getAll();
echo json_encode($users);
?>
