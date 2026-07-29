<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

header('Content-Type: application/json');

require_once('../partials/connexion.php');

/* DB connection now handled by Singleton in connexion.php */

if ($conn->connect_error) {
    die(json_encode(["error" => "Connexion échouée: " . $conn->connect_error]));
}

$username = $_POST['username'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = $_POST['role'];

$query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $username, $email, $password, $role);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => $stmt->error]);
}

$stmt->close();
/* DB connection intentionally left open for Singleton */
?>
