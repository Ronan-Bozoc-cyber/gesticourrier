<?php
header('Content-Type: application/json');
include 'partials/connexion.php';

if (isset($_GET['id']) && intval($_GET['id']) > 0) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM courriers_arrive WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["error" => "Aucun enregistrement trouvé avec cet ID"]);
        exit;
    }

    $courrier = $result->fetch_assoc();
    echo json_encode($courrier);
} else {
    // Retourner tous les courriers
    $query = "SELECT * FROM courriers_arrive";
    $result = $conn->query($query);
    $courriers = [];

    while ($row = $result->fetch_assoc()) {
        $courriers[] = $row;
    }

    echo json_encode($courriers);
}

$conn->close();
?>

