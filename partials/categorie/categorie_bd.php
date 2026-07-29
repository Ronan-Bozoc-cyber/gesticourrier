<?php
function update_courrier_categories($conn, $oldName, $newName) {
    // Mettre à jour les enregistrements dans courriers_arrive
    $updateArriveStmt = $conn->prepare("UPDATE courriers_arrive SET categorie_courrier = ? WHERE categorie_courrier = ?");
    $updateArriveStmt->bind_param("ss", $newName, $oldName);

    // Mettre à jour les enregistrements dans courriers_depart
    $updateDepartStmt = $conn->prepare("UPDATE courriers_depart SET categorie_courrier = ? WHERE categorie_courrier = ?");
    $updateDepartStmt->bind_param("ss", $newName, $oldName);

    $successArrive = $updateArriveStmt->execute();
    $successDepart = $updateDepartStmt->execute();

    $updateArriveStmt->close();
    $updateDepartStmt->close();

    return $successArrive && $successDepart;
}

// Script principal
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'partials/connexion.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Connexion échouée: " . $conn->connect_error]);
    exit;
}

// Gestion des requêtes POST pour l'ajout, la modification et la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['name'];

        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $name);

            if ($stmt->execute()) {
                $response = ['success' => true, 'id' => $stmt->insert_id];
            } else {
                $response = ['success' => false, 'error' => $stmt->error];
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'error' => 'Le champ est obligatoire.'];
        }
        echo json_encode($response);
        exit;
    }

    if ($action === 'update') {
        $id = intval($_POST['id']);
        $name = $_POST['name'];
        $update_all = isset($_POST['update_all']) ? true : false;

        // Obtenir le nom de l'ancienne catégorie
        $oldCategoryQuery = $conn->prepare("SELECT name FROM categories WHERE id = ?");
        $oldCategoryQuery->bind_param("i", $id);
        $oldCategoryQuery->execute();
        $oldCategoryQuery->bind_result($oldName);
        $oldCategoryQuery->fetch();
        $oldCategoryQuery->close();

        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);

            if ($stmt->execute()) {
                if ($update_all) {
                    if (update_courrier_categories($conn, $oldName, $name)) {
                        $response = ['success' => true, 'message' => 'Tous les enregistrements associés ont été mis à jour.'];
                    } else {
                        $response = ['success' => false, 'error' => 'Erreur lors de la mise à jour des enregistrements associés.'];
                    }
                } else {
                    $response = ['success' => true];
                }
            } else {
                $response = ['success' => false, 'error' => $stmt->error];
            }
            $stmt->close();
        } else {
            $response = ['success' => false, 'error' => 'Le champ est obligatoire.'];
        }
        echo json_encode($response);
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);

        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
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

// Récupération de la liste des catégories
$categories = [];
$result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
