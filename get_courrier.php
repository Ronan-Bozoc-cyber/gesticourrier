<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

header('Content-Type: application/json');

require_once('partials/connexion.php');

/* DB connection now handled by Singleton in connexion.php */

if ($conn->connect_error) {
    error_log("Connexion échouée: " . $conn->connect_error);  // Journalisation de l'erreur de connexion
    echo json_encode(["error" => "Connexion échouée: " . $conn->connect_error]);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    error_log("ID invalide reçu: " . $id);  // Journalisation de l'ID invalide
    echo json_encode(["error" => "ID invalide"]);
    exit;
}

$query = "SELECT * FROM depart WHERE id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    error_log("Erreur de préparation de la requête: " . $conn->error);  // Journalisation de l'erreur de préparation
    echo json_encode(["error" => "Erreur de préparation de la requête: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    error_log("Erreur lors de l'exécution de la requête: " . $stmt->error);  // Journalisation de l'erreur d'exécution
    echo json_encode(["error" => "Erreur lors de l'exécution de la requête: " . $stmt->error]);
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("Aucun enregistrement trouvé avec l'ID: " . $id);  // Journalisation de l'absence d'enregistrement
    echo json_encode(["error" => "Aucun enregistrement trouvé avec cet ID"]);
    exit;
}

$courrier = $result->fetch_assoc();

$stmt->close();
/* DB connection intentionally left open for Singleton */

error_log("Enregistrement récupéré avec succès: " . json_encode($courrier));  // Journalisation de la récupération réussie
echo json_encode($courrier);
?>
