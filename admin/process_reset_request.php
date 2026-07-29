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
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32));
    $expires = date('U') + 1800;
    $sql = 'INSERT INTO password_reset (email, token, expires) VALUES (:email, :token, :expires)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email, 'token' => $token, 'expires' => $expires]);
    $to = $email;
    $subject = 'Réinitialisation de votre mot de passe - GestCourrier-Conques';
    $resetUrl = $urllogiciel . 'admin/reset_password.php?token=' . $token;
    $message = 'Voici le lien pour réinitialiser votre mot de passe : ' . "\r\n";
    $message .= $resetUrl;
    $headers = 'From: no-reply@gesticourrier' . "\r\n" .
               'Reply-To: no-reply@gesticourrier' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    if (mail($to, $subject, $message, $headers)) {
        echo 'Un e-mail de réinitialisation a été envoyé à votre adresse e-mail.';
        // Redirection après 2 secondes en utilisant $urllogiciel
        header("refresh:2;url=" . $urllogiciel . "login.php");
        exit();
    } else {
        echo 'Erreur lors de l\'envoi de l\'e-mail.';
    }
}
?>
