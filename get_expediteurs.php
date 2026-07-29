<?php
require_once('partials/connexion.php');

/* DB connection now handled by Singleton in connexion.php */

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

/* DB connection intentionally left open for Singleton */

echo json_encode($expediteurs);
?>
