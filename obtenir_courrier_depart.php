<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'admin/auth_check.php';
include 'partials/connexion.php';

// Récupère le numéro d'ordre et l'année
$num_ordre = $_GET['num_ordre'] ?? null;
$annee = $_GET['annee'] ?? date('Y');

if (!$num_ordre) {
    echo json_encode(['error' => 'Numéro d\'ordre manquant.']);
    exit;
}

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connexion échouée: ' . $conn->connect_error]);
    exit;
}

// Requête SQL corrigée pour inclure l'année et les bonnes jointures
$stmt = $conn->prepare("
    SELECT
        cd.num_ordre,
        cd.date,
        cd.type_courrier,
        e.name AS expediteur_name,
        e.id AS expediteur_id,
        e.adresse AS expediteur_adresse,
        cd.num_recommande,
        cd.sujet_courrier,
        cd.categorie_courrier,
        ca.num_ordre AS courrier_arrive_num_ordre,
        cd.courrier_arrive_id,
        cd.document_path,
        cd.document_path2,
        cd.document_path3,
        cd.document_path4,
        cd.document_path5,
        cd.traite_par
    FROM courriers_depart cd
    LEFT JOIN expediteurs e ON cd.expediteur_id = e.id
    LEFT JOIN courriers_arrive ca ON cd.courrier_arrive_id = ca.id
    WHERE cd.num_ordre = ? AND cd.annee = ?
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
$conn->close();
?>
