<?php
require_once('partials/connexion.php');

$num_ordre = $_GET['num_ordre'] ?? null;
$annee = $_GET['annee'] ?? null;

if ($num_ordre && $annee) {
    $sql = "SELECT
                document_path,
                document_path2,
                document_path3,
                document_path4,
                document_path5
            FROM
                courriers_depart
            WHERE
                num_ordre = ? AND annee = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $num_ordre, $annee);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $documents = array();
    if ($row) {
        foreach ($row as $path) {
            if (!empty($path)) {
                $documents[] = $path;
            }
        }
    }
    header('Content-Type: application/json');
    echo json_encode($documents);
} else {
    header('Content-Type: application/json');
    echo json_encode([]);
}
/* DB connection intentionally left open for Singleton */
?>
