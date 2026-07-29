<?php
include 'partials/connexion.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Requête pour récupérer les années distinctes des courriers sortants
$sql = "SELECT DISTINCT annee FROM courriers_depart ORDER BY annee DESC";
$result = $conn->query($sql);

$years = array();
while ($row = $result->fetch_assoc()) {
    $years[] = $row['annee'];
}

header('Content-Type: application/json');
echo json_encode($years);

$conn->close();
?>
