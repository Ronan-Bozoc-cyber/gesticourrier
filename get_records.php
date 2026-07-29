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
    die(json_encode(["error" => "Connexion échouée: " . $conn->connect_error]));
}

$query = "SELECT * FROM depart";
$result = $conn->query($query);

$records = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

/* DB connection intentionally left open for Singleton */

echo json_encode($records);
?>
