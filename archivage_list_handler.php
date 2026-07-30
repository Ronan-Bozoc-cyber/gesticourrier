<?php
ini_set('display_errors', '0');
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) @session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié.']);
    exit;
}
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Réservé aux administrateurs.']);
    exit;
}

require_once __DIR__ . '/partials/connexion.php';

$duree = intval($_GET['duree'] ?? 0);
if (!in_array($duree, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10])) {
    echo json_encode(['success' => false, 'error' => 'Durée invalide. Choisissez entre 1 et 10 ans.']);
    exit;
}

$cutoffDate = date('Y-m-d', strtotime("-{$duree} years"));

// ─── Courriers ARRIVE ─────────────────────────────────────────────────────────
$sqlArrive = "
    SELECT ca.id, ca.num_ordre, ca.annee, ca.date, ca.type_courrier,
           ca.sujet_courrier, ca.num_recommande, ca.categorie_courrier,
           ca.document_path, ca.document_path2, ca.document_path3,
           ca.document_path4, ca.document_path5,
           ca.courrier_depart_id,
           cd.num_ordre AS depart_num_ordre, cd.annee AS depart_annee,
           u.username AS traite_par,
           e.name AS expediteur
    FROM courriers_arrive ca
    LEFT JOIN courriers_depart cd ON ca.courrier_depart_id = cd.id
    LEFT JOIN users u ON ca.traite_par = u.id
    LEFT JOIN expediteurs e ON ca.expediteur_id = e.id
    WHERE ca.date < ?
    ORDER BY ca.date ASC, ca.num_ordre ASC
";

$stmtA = $conn->prepare($sqlArrive);
$stmtA->bind_param('s', $cutoffDate);
$stmtA->execute();
$arrive = [];
$resA = $stmtA->get_result();
while ($row = $resA->fetch_assoc()) {
    $nb = 0;
    foreach (['document_path','document_path2','document_path3','document_path4','document_path5'] as $dp) {
        if (!empty($row[$dp])) $nb++;
    }
    $row['nb_documents'] = $nb;
    $row['flux'] = 'ARRIVE';
    $arrive[] = $row;
}
$stmtA->close();

// ─── Courriers DEPART ─────────────────────────────────────────────────────────
$sqlDepart = "
    SELECT cd.id, cd.num_ordre, cd.annee, cd.date, cd.type_courrier,
           cd.sujet_courrier, cd.num_recommande, cd.categorie_courrier,
           cd.document_path, cd.document_path2, cd.document_path3,
           cd.document_path4, cd.document_path5,
           cd.courrier_arrive_id,
           ca.num_ordre AS arrive_num_ordre, ca.annee AS arrive_annee,
           u.username AS traite_par,
           e.name AS expediteur
    FROM courriers_depart cd
    LEFT JOIN courriers_arrive ca ON cd.courrier_arrive_id = ca.id
    LEFT JOIN users u ON cd.traite_par = u.id
    LEFT JOIN expediteurs e ON cd.expediteur_id = e.id
    WHERE cd.date < ?
    ORDER BY cd.date ASC, cd.num_ordre ASC
";

$stmtD = $conn->prepare($sqlDepart);
$stmtD->bind_param('s', $cutoffDate);
$stmtD->execute();
$depart = [];
$resD = $stmtD->get_result();
while ($row = $resD->fetch_assoc()) {
    $nb = 0;
    foreach (['document_path','document_path2','document_path3','document_path4','document_path5'] as $dp) {
        if (!empty($row[$dp])) $nb++;
    }
    $row['nb_documents'] = $nb;
    $row['flux'] = 'DEPART';
    $depart[] = $row;
}
$stmtD->close();

echo json_encode([
    'success'     => true,
    'cutoff_date' => $cutoffDate,
    'duree'       => $duree,
    'arrive'      => $arrive,
    'depart'      => $depart,
    'total'       => count($arrive) + count($depart),
]);
?>
