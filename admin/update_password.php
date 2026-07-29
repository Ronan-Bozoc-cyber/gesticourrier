<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Charger les paramètres
require_once(__DIR__ . '/../partials/parametres.php');

// Charger la connexion en utilisant $chemin
require_once($chemin . 'partials/connexion.php');

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = 'SELECT email FROM password_reset WHERE token = :token AND expires >= :now';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['token' => $token, 'now' => date('U')]);
    $email = $stmt->fetchColumn();

    if ($email) {
        $sql = 'UPDATE users SET password = :password WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['password' => $new_password, 'email' => $email]);

        $sql = 'DELETE FROM password_reset WHERE email = :email';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);

        echo 'Votre mot de passe a été mis à jour avec succès.';
    } else {
        echo 'Lien de réinitialisation invalide ou expiré.';
    }
}
?>
