<?php
session_start();

// Charger les paramètres
require_once(__DIR__ . '/partials/parametres.php');

// Charger la connexion en utilisant $chemin
require_once($chemin . 'partials/connexion.php');

// Connexion MySQL avec mysqli
/* DB connection now handled by Singleton in connexion.php */

if ($conn->connect_error) {
    die("Connexion échouée: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT id, password, role FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($id, $hashed_password, $role);
$stmt->fetch();

if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
    $_SESSION['user_id'] = $id;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;

    // Redirection dynamique avec $urllogiciel
    header("Location: " . $urllogiciel . "index.php");
    exit;
} else {
    echo "Nom d'utilisateur ou mot de passe incorrect";
}

$stmt->close();
/* DB connection intentionally left open for Singleton */
?>
