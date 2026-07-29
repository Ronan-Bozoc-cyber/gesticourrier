<?php
$servername = "localhost";
$username = "utilisateur";
$password = "mdp1234";
$dbname = "courriers_db";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}
?>
