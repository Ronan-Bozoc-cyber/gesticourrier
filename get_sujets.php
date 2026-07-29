<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Charger les paramètres
require_once(__DIR__ . '/partials/parametres.php');

// Charger la connexion en utilisant $chemin
require_once($chemin . 'partials/connexion.php');


// Requête SQL pour récupérer les sujets distincts des deux tables
$sql_arrive = "SELECT DISTINCT sujet_courrier FROM courriers_arrive";
$sql_depart = "SELECT DISTINCT sujet_courrier FROM courriers_depart";

$result_arrive = $conn->query($sql_arrive);
$result_depart = $conn->query($sql_depart);

$sujets = array();
while ($row = $result_arrive->fetch_assoc()) {
    $sujets[] = $row['sujet_courrier'];
}
while ($row = $result_depart->fetch_assoc()) {
    if (!in_array($row['sujet_courrier'], $sujets)) {
        $sujets[] = $row['sujet_courrier'];
    }
}

// Affichage des sujets pour le débogage
error_log("Sujets récupérés : " . print_r($sujets, true));

header('Content-Type: application/json');
echo json_encode($sujets);

$conn->close();
?>
