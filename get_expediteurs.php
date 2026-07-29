<?php
require_once __DIR__ . '/models/ExpediteurModel.php';

$expediteursData = ExpediteurModel::getAll();
$expediteurs = [];
foreach ($expediteursData as $row) {
    $expediteurs[] = ['label' => $row['name'], 'value' => $row['id']];
}

echo json_encode($expediteurs);
?>
