<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'admin/auth_check.php';
require_once('partials/connexion.php');

// Récupère le numéro d'ordre et l'année
$num_ordre = $_GET['num_ordre'] ?? null;
$annee = $_GET['annee'] ?? date('Y');

if (!$num_ordre) {
    echo json_encode(['error' => 'Numéro d\'ordre manquant.']);
    exit;
}

// Requête SQL corrigée pour inclure l'année
$stmt = $conn->prepare("
    SELECT
        ca.num_ordre,
        ca.date,
        ca.type_courrier,
        e.name AS expediteur_name,
        e.id AS expediteur_id,
        e.adresse AS expediteur_adresse,
        ca.num_recommande,
        ca.sujet_courrier,
        ca.categorie_courrier,
        cd.num_ordre AS courrier_depart_num_ordre,
        ca.courrier_depart_id,
        ca.document_path,
        ca.document_path2,
        ca.document_path3,
        ca.document_path4,
        ca.document_path5,
        ca.traite_par
    FROM courriers_arrive ca
    LEFT JOIN expediteurs e ON ca.expediteur_id = e.id
    LEFT JOIN courriers_depart cd ON ca.courrier_depart_id = cd.id
    WHERE ca.num_ordre = ? AND ca.annee = ?
");

if (!$stmt) {
    echo json_encode(['error' => 'Erreur de préparation de la requête: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $num_ordre, $annee);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Aucun courrier trouvé avec ce numéro d\'ordre et cette année.']);
} else {
    $courrier = $result->fetch_assoc();
    echo json_encode($courrier);
}

$stmt->close();
/* DB connection intentionally left open for Singleton */
?>
