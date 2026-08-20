<?php
require_once('partials/connexion.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

$num_ordre = $_POST['num_ordre'] ?? $_GET['num_ordre'] ?? null;
$annee = $_POST['annee'] ?? $_GET['annee'] ?? null;
$flux = $_POST['flux'] ?? $_GET['flux'] ?? 'ARRIVE';
$doc_num = intval($_POST['doc_num'] ?? $_GET['doc_num'] ?? 0);

if (!$num_ordre || !$annee || $doc_num < 1 || $doc_num > 5) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Paramètres invalides']);
    exit;
}

$table = ($flux === 'ARRIVE') ? 'courriers_arrive' : 'courriers_depart';
$column = ($doc_num === 1) ? 'document_path' : 'document_path' . $doc_num;

// Récupérer le chemin du fichier actuel
$stmt = $conn->prepare("SELECT $column FROM $table WHERE num_ordre = ? AND annee = ?");
$stmt->bind_param("ii", $num_ordre, $annee);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $filePath = $row[$column];
    if (!empty($filePath)) {
        // Suppression du fichier physique
        $fileName = basename($filePath);
        $fullPath = __DIR__ . '/uploads/' . $fileName;
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
        
        // Mise à jour de la base de données
        $updateStmt = $conn->prepare("UPDATE $table SET $column = NULL WHERE num_ordre = ? AND annee = ?");
        $updateStmt->bind_param("ii", $num_ordre, $annee);
        $updateStmt->execute();
        $updateStmt->close();
    }
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
