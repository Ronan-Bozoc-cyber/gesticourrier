<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure la connexion à la base de données
require_once('partials/connexion.php');

// Vérifiez que la connexion à la base de données est établie
if (!isset($conn) || $conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Erreur de connexion à la base de données: ' . ($conn ? $conn->connect_error : 'Connexion non définie')]);
    exit;
}

// Récupérer le mois depuis les paramètres GET, sinon utiliser le mois en cours
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Fonction pour obtenir le nombre de courriers entrants/sortants par mois
function getCourriersCount($conn, $month) {
    $arriveQuery = "SELECT COUNT(*) FROM courriers_arrive WHERE DATE_FORMAT(date, '%Y-%m') = ?";
    $departQuery = "SELECT COUNT(*) FROM courriers_depart WHERE DATE_FORMAT(date, '%Y-%m') = ?";

    $stmtArrive = $conn->prepare($arriveQuery);
    if (!$stmtArrive) {
        return ['arrive' => 0, 'depart' => 0, 'label' => $month];
    }

    $stmtArrive->bind_param("s", $month);
    $stmtArrive->execute();
    $stmtArrive->bind_result($arriveCount);
    $stmtArrive->fetch();
    $stmtArrive->close();

    $stmtDepart = $conn->prepare($departQuery);
    if (!$stmtDepart) {
        return ['arrive' => $arriveCount, 'depart' => 0, 'label' => $month];
    }

    $stmtDepart->bind_param("s", $month);
    $stmtDepart->execute();
    $stmtDepart->bind_result($departCount);
    $stmtDepart->fetch();
    $stmtDepart->close();

    return [
        'arrive' => $arriveCount,
        'depart' => $departCount,
        'label' => $month
    ];
}

// Fonction pour obtenir les catégories des courriers entrants par mois
function getCategoriesArrive($conn, $month) {
    $query = "
        SELECT categorie_courrier, COUNT(*)
        FROM courriers_arrive
        WHERE DATE_FORMAT(date, '%Y-%m') = ?
        GROUP BY categorie_courrier
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("s", $month);
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[$row['categorie_courrier']] = $row['COUNT(*)'];
    }
    $stmt->close();

    return $categories;
}

// Fonction pour obtenir les catégories des courriers sortants par mois
function getCategoriesDepart($conn, $month) {
    $query = "
        SELECT categorie_courrier, COUNT(*)
        FROM courriers_depart
        WHERE DATE_FORMAT(date, '%Y-%m') = ?
        GROUP BY categorie_courrier
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("s", $month);
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[$row['categorie_courrier']] = $row['COUNT(*)'];
    }
    $stmt->close();

    return $categories;
}

// Récupérer les données pour le mois spécifié
$monthData = getCourriersCount($conn, $month);
$monthData['categoriesArrive'] = getCategoriesArrive($conn, $month);
$monthData['categoriesDepart'] = getCategoriesDepart($conn, $month);

// Retourner les données au format JSON
header('Content-Type: application/json');
echo json_encode($monthData);

/* DB connection intentionally left open for Singleton */
?>
