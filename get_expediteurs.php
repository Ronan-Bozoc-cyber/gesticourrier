<?php
include 'partials/connexion.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

$sql = "SELECT id, name FROM expediteurs";
$result = $conn->query($sql);

$expediteurs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expediteurs[] = ['label' => $row['name'], 'value' => $row['id']];
    }
}

$conn->close();

echo json_encode($expediteurs);
?>
