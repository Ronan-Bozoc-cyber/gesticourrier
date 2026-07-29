<?php
header('Content-Type: application/json');
require_once('../partials/connexion.php');

/* DB connection now handled by Singleton in connexion.php */

if ($conn->connect_error) {
    die(json_encode(["error" => "Connexion échouée: " . $conn->connect_error]));
}

$query = "SELECT id, username, email, role FROM users";
$result = $conn->query($query);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

/* DB connection intentionally left open for Singleton */

echo json_encode($users);
?>
