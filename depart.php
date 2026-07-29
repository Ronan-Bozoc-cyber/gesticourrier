<?php
include 'admin/auth_check.php';
include 'partials/parametres.php';
require_once 'models/CourrierModel.php';

// Récupérer le prochain numéro d'ordre pour l'année en cours
$date = $_GET['date'] ?? date('Y-m-d');
$year = date('Y', strtotime($date));

$nextNumOrdre = CourrierModel::getNextNumOrdreDepart($year);

// Appeler la vue
require_once 'views/depart.view.php';
