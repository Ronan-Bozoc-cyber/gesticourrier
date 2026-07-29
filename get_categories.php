<?php
require_once __DIR__ . '/models/CategorieModel.php';

$categoriesData = CategorieModel::getAll();
$categoriesNames = array_column($categoriesData, 'name');

echo json_encode($categoriesNames);
?>
