<?php
$servername = "localhost";
$username = "conques1";
$password = "N8qLkof61dpDjqk";
$dbname = "courriers_db";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}
?>
