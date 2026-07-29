<?php
header('Content-Type: application/json');
require_once('partials/connexion.php');

$date = $_GET['date'] ?? date('Y-m-d');
$flux = $_GET['flux'] ?? 'ARRIVE';
$year = date('Y', strtotime($date));

$query = "SELECT MAX(num_ordre) AS max_num_ordre FROM courriers_arrive WHERE annee = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $year);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$nextNumOrdre = ($row['max_num_ordre'] ?? 0) + 1;
$stmt->close();
/* DB connection intentionally left open for Singleton */
error_log("Date: $date, Flux: $flux, Année: $year");
error_log("Max numéro d'ordre: " . ($row['max_num_ordre'] ?? 'NULL') . ", Prochain numéro d'ordre: $nextNumOrdre");


echo json_encode(['nextNumOrdre' => $nextNumOrdre]);
?>
