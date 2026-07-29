<?php
require_once('partials/connexion.php');
/* DB connection now handled by Singleton in connexion.php */

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

$sql = "SELECT name FROM categories";
$result = $conn->query($sql);

$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['name'];
    }
}

/* DB connection intentionally left open for Singleton */

echo json_encode($categories);
?>
