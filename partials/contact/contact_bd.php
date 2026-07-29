<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('partials/connexion.php');
/* DB connection now handled by Singleton in connexion.php */

if ($conn->connect_error) {
    die(json_encode(["error" => "Connexion échouée: " . $conn->connect_error]));
}

// Gestion des requêtes POST pour l'ajout, la modification et la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['name'];
        $adresse = $_POST['adresse'];

        if (!empty($name) && !empty($adresse)) {
            $stmt = $conn->prepare("INSERT INTO expediteurs (name, adresse) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $adresse);

            if ($stmt->execute()) {
                $response = ['success' => true, 'id' => $stmt->insert_id];
            } else {
                $response = ['success' => false, 'error' => $stmt->error];
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'error' => 'Tous les champs sont obligatoires.'];
        }
        echo json_encode($response);
        exit;
    }

    if ($action === 'update') {
        $id = intval($_POST['id']);
        $name = $_POST['name'];
        $adresse = $_POST['adresse'];

        if (!empty($name) && !empty($adresse)) {
            $stmt = $conn->prepare("UPDATE expediteurs SET name = ?, adresse = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $adresse, $id);

            if ($stmt->execute()) {
                $response = ['success' => true];
            } else {
                $response = ['success' => false, 'error' => $stmt->error];
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'error' => 'Tous les champs sont obligatoires.'];
        }
        echo json_encode($response);
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);

        $stmt = $conn->prepare("DELETE FROM expediteurs WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $response = ['success' => true];
        } else {
            $response = ['success' => false, 'error' => $stmt->error];
        }
        $stmt->close();
        echo json_encode($response);
        exit;
    }
}

// Récupération de la liste des expéditeurs
$expediteurs = [];
$result = $conn->query("SELECT id, name, adresse FROM expediteurs ORDER BY name ASC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expediteurs[] = $row;
    }
}
?>
