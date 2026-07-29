<?php
include 'partials/connexion.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

// Requête SQL pour récupérer tous les champs nécessaires, y compris le nom de l'expéditeur
$sql = "SELECT c.id, c.num_ordre, c.date, e.name as expediteur, c.sujet_courrier 
        FROM courriers_depart c
        LEFT JOIN expediteurs e ON c.expediteur_id = e.id
        ORDER BY c.num_ordre DESC";
$result = $conn->query($sql);

$courriers = array();
while ($row = $result->fetch_assoc()) {
    $courriers[] = array(
        'id' => $row['id'],
        'label' => $row['num_ordre'] . " - " . $row['expediteur'] . " - " . $row['sujet_courrier'] . " - " . $row['date']
    );
}

header('Content-Type: application/json');
echo json_encode($courriers);

$conn->close();
?>
