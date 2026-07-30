<?php
ini_set('display_errors', '0');
error_reporting(0);

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) { header('Content-Type: application/json; charset=utf-8'); http_response_code(500); }
        echo json_encode(['success' => false, 'error' => 'Erreur fatale : ' . $err['message']]);
    }
});

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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

require_once __DIR__ . '/partials/connexion.php';

// ─── Migration : Créer la table destruction_logs si elle n'existe pas ──────────
$conn->query("
    CREATE TABLE IF NOT EXISTS destruction_logs (
        id                INT AUTO_INCREMENT PRIMARY KEY,
        user_id           INT NOT NULL,
        username          VARCHAR(100) NOT NULL,
        date_destruction  DATETIME NOT NULL,
        duree_conservation INT NOT NULL,
        nb_arrive         INT DEFAULT 0,
        nb_depart         INT DEFAULT 0,
        nb_total          INT DEFAULT 0,
        courriers_json    MEDIUMTEXT,
        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// ─── Paramètres de la requête ─────────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    echo json_encode(['success' => false, 'error' => 'Corps de la requête invalide (JSON attendu).']);
    exit;
}

$ids_arrive = array_filter(array_map('intval', $body['ids_arrive'] ?? []), fn($v) => $v > 0);
$ids_depart = array_filter(array_map('intval', $body['ids_depart'] ?? []), fn($v) => $v > 0);
$duree      = intval($body['duree'] ?? 0);
$userId     = intval($_SESSION['user_id']);
$username   = $_SESSION['username'] ?? $_SESSION['user_name'] ?? 'Inconnu';

if (empty($ids_arrive) && empty($ids_depart)) {
    echo json_encode(['success' => false, 'error' => 'Aucun courrier sélectionné pour la destruction.']);
    exit;
}

// ─── Récupérer les données complètes avant suppression (pour le certificat) ────
$allCourriers = [];

function fetchRows(mysqli $conn, string $table, array $ids): array {
    if (empty($ids)) return [];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("SELECT * FROM $table WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

$arriveRows = fetchRows($conn, 'courriers_arrive', array_values($ids_arrive));
$departRows = fetchRows($conn, 'courriers_depart',  array_values($ids_depart));

// Enrichir avec le nom de l'expéditeur
$expCache = [];
function getExpediteur(mysqli $conn, int $id, array &$cache): string {
    if (isset($cache[$id])) return $cache[$id];
    $stmt = $conn->prepare("SELECT name FROM expediteurs WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $cache[$id] = $r ? $r['name'] : 'Inconnu';
    return $cache[$id];
}

foreach ($arriveRows as &$row) {
    $row['flux'] = 'ARRIVE';
    $row['expediteur_nom'] = getExpediteur($conn, (int)$row['expediteur_id'], $expCache);
}
unset($row);
foreach ($departRows as &$row) {
    $row['flux'] = 'DEPART';
    $row['expediteur_nom'] = getExpediteur($conn, (int)$row['expediteur_id'], $expCache);
}
unset($row);

$allCourriers = array_merge($arriveRows, $departRows);

// ─── VALIDATION Q2 : Interdire la destruction si le courrier lié n'est pas sélectionné ───
$idsArriveSet = array_flip($ids_arrive);
$idsDepartSet = array_flip($ids_depart);

foreach ($arriveRows as $row) {
    if (!empty($row['courrier_depart_id'])) {
        $depId = (int)$row['courrier_depart_id'];
        if (!isset($idsDepartSet[$depId])) {
            // Le départ lié n'est pas sélectionné
            $numArr = $row['num_ordre'];
            echo json_encode(['success' => false, 'error' => "Action impossible : le courrier Arrivée N°{$numArr} est lié à un courrier Départ qui n'est pas sélectionné pour la destruction."]);
            exit;
        }
    }
}
foreach ($departRows as $row) {
    if (!empty($row['courrier_arrive_id'])) {
        $arrId = (int)$row['courrier_arrive_id'];
        if (!isset($idsArriveSet[$arrId])) {
            // L'arrivée liée n'est pas sélectionnée
            $numDep = $row['num_ordre'];
            echo json_encode(['success' => false, 'error' => "Action impossible : le courrier Départ N°{$numDep} est lié à un courrier Arrivée qui n'est pas sélectionné pour la destruction."]);
            exit;
        }
    }
}

// ─── Gestion des courriers liés (Nullification si besoin) ────────────────────
// Avec la règle ci-dessus, ce code ne s'exécutera que si les DEUX courriers sont détruits.
// La base de données gérera la suppression en cascade ou nous supprimons les deux de toute façon.
// Nous gardons ce code au cas où des orphelins existeraient déjà dans la base.
foreach ($arriveRows as $row) {
    if (!empty($row['courrier_depart_id']) && !isset($idsDepartSet[(int)$row['courrier_depart_id']])) {
        $conn->query("UPDATE courriers_depart SET courrier_arrive_id = NULL WHERE id = " . (int)$row['courrier_depart_id']);
    }
}
foreach ($departRows as $row) {
    if (!empty($row['courrier_arrive_id']) && !isset($idsArriveSet[(int)$row['courrier_arrive_id']])) {
        $conn->query("UPDATE courriers_arrive SET courrier_depart_id = NULL WHERE id = " . (int)$row['courrier_arrive_id']);
    }
}


// ─── Suppression des fichiers physiques ──────────────────────────────────────
$docFields = ['document_path','document_path2','document_path3','document_path4','document_path5'];
foreach ($allCourriers as $row) {
    foreach ($docFields as $field) {
        if (!empty($row[$field]) && file_exists($row[$field])) {
            @unlink($row[$field]);
        }
    }
}

// ─── Suppression en base ──────────────────────────────────────────────────────
if (!empty($ids_arrive)) {
    $pl = implode(',', array_fill(0, count($ids_arrive), '?'));
    $ty = str_repeat('i', count($ids_arrive));
    $st = $conn->prepare("DELETE FROM courriers_arrive WHERE id IN ($pl)");
    $st->bind_param($ty, ...array_values($ids_arrive));
    $st->execute();
    $st->close();
}
if (!empty($ids_depart)) {
    $pl = implode(',', array_fill(0, count($ids_depart), '?'));
    $ty = str_repeat('i', count($ids_depart));
    $st = $conn->prepare("DELETE FROM courriers_depart WHERE id IN ($pl)");
    $st->bind_param($ty, ...array_values($ids_depart));
    $st->execute();
    $st->close();
}

// ─── Enregistrement du log de destruction ────────────────────────────────────
$dateDestruction = date('Y-m-d H:i:s');
$nbArrive        = count($ids_arrive);
$nbDepart        = count($ids_depart);
$nbTotal         = $nbArrive + $nbDepart;
$courriersJson   = json_encode($allCourriers, JSON_UNESCAPED_UNICODE);

$stmtLog = $conn->prepare("
    INSERT INTO destruction_logs (user_id, username, date_destruction, duree_conservation, nb_arrive, nb_depart, nb_total, courriers_json)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmtLog->bind_param('issiiiis', $userId, $username, $dateDestruction, $duree, $nbArrive, $nbDepart, $nbTotal, $courriersJson);
$stmtLog->execute();
$logId = $conn->insert_id;
$stmtLog->close();

echo json_encode([
    'success'     => true,
    'log_id'      => $logId,
    'nb_arrive'   => $nbArrive,
    'nb_depart'   => $nbDepart,
    'nb_total'    => $nbTotal,
    'message'     => "$nbTotal courrier(s) détruits avec succès.",
]);
?>
