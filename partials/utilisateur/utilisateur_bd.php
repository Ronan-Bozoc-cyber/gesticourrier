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
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $role = $_POST['role'];

        if (!empty($username) && !empty($email) && !empty($password) && !empty($role)) {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $password, $role);

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
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

        if (!empty($username) && !empty($email) && !empty($role)) {
            if ($password) {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $email, $role, $password, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $role, $id);
            }

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

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
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

// Récupération de la liste des utilisateurs
$utilisateurs = [];
$result = $conn->query("SELECT id, username, email, role FROM users ORDER BY username ASC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $utilisateurs[] = $row;
    }
}
?>
