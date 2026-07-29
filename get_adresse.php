<?php
require_once('partials/connexion.php');

/* DB connection now handled by Singleton in connexion.php */

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

if (isset($_GET['expediteur_id'])) {
    $expediteur_id = $_GET['expediteur_id'];
    $stmt = $conn->prepare("SELECT adresse FROM expediteurs WHERE id = ?");
    $stmt->bind_param("i", $expediteur_id);
    $stmt->execute();
    $stmt->bind_result($adresse);
    $stmt->fetch();
    echo json_encode(['adresse' => $adresse]);
    $stmt->close();
}

/* DB connection intentionally left open for Singleton */
?>
