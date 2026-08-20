<?php
require_once __DIR__ . '/partials/connexion.php';

$username = 'ronan';
$password = 'CK44t!11m';
$role = 'admin';

$hash = password_hash($password, PASSWORD_DEFAULT);

// Créer la table destruction_logs si elle n'existe pas
$conn->query("
    CREATE TABLE IF NOT EXISTS destruction_logs (
        id                INT AUTO_INCREMENT PRIMARY KEY,
        user_id           INT NOT NULL,
        username          VARCHAR(100) NOT NULL,
        date_destruction  DATETIME NOT NULL,
        duree_conservation INT NOT NULL,
        nb_arrive         INT DEFAULT 0,
        nb_depart         INT DEFAULT 0,
        nb_total          INT DEFAULT 0,
        courriers_json    MEDIUMTEXT,
        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "Vérification / Création de la table 'destruction_logs' effectuée.\n";

// Vérifier si la table users existe
$checkTable = $conn->query("SHOW TABLES LIKE 'users'");
if ($checkTable->num_rows === 0) {
    echo "La table 'users' n'existe pas encore. Veuillez d'abord importer le schéma SQL.\n";
    exit(1);
}

// Vérifier si l'utilisateur existe déjà
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $updateStmt = $conn->prepare("UPDATE users SET password = ?, role = ? WHERE id = ?");
    $updateStmt->bind_param("ssi", $hash, $role, $row['id']);
    if ($updateStmt->execute()) {
        echo "Mot de passe et rôle de l'utilisateur '$username' mis à jour avec succès.\n";
    } else {
        echo "Erreur lors de la mise à jour: " . $conn->error . "\n";
    }
} else {
    $insertStmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $insertStmt->bind_param("sss", $username, $hash, $role);
    if ($insertStmt->execute()) {
        echo "Utilisateur '$username' (admin) créé avec succès.\n";
    } else {
        echo "Erreur lors de la création de l'utilisateur: " . $conn->error . "\n";
    }
}
?>
