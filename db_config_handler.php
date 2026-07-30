<?php
// ─── SÉCURITÉ MAXIMALE : toujours répondre en JSON même en cas d'erreur fatale ───
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(0);

// Attraper les erreurs fatales et les renvoyer en JSON
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // S'assurer que le header JSON est envoyé
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
        }
        echo json_encode([
            'success' => false,
            'error'   => 'Erreur serveur : ' . $err['message'] . ' (ligne ' . $err['line'] . ')'
        ]);
    }
});

header('Content-Type: application/json; charset=utf-8');

// ─── SESSION ───────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// ─── AUTHENTIFICATION ──────────────────────────────────────────────────────────
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié. Veuillez vous reconnecter.']);
    exit;
}

if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accès refusé. Seuls les administrateurs peuvent modifier ces paramètres.']);
    exit;
}

// ─── VÉRIFIER QUE MYSQLI EST DISPONIBLE ───────────────────────────────────────
if (!extension_loaded('mysqli')) {
    echo json_encode(['success' => false, 'error' => 'Extension PHP mysqli non disponible sur ce serveur.']);
    exit;
}

$envFile = __DIR__ . '/.env';

// ─── LECTURE DES VALEURS ACTUELLES (GET) ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $env = [];
    if (file_exists($envFile)) {
        foreach (file($envFile) as $line) {
            $line = trim($line);
            if ($line && strpos($line, '=') !== false) {
                [$k, $v] = explode('=', $line, 2);
                $env[trim($k)] = trim($v);
            }
        }
    }
    echo json_encode([
        'success'    => true,
        'DB_HOST'    => $env['DB_HOST'] ?? '',
        'DB_USER'    => $env['DB_USER'] ?? '',
        'DB_NAME'    => $env['DB_NAME'] ?? '',
        'DB_PASS_SET'=> !empty($env['DB_PASS']),
    ]);
    exit;
}

// ─── POST : TESTER OU SAUVEGARDER ─────────────────────────────────────────────
$action = trim($_POST['action'] ?? 'save');  // 'test' ou 'save'
$host   = trim($_POST['DB_HOST'] ?? '');
$user   = trim($_POST['DB_USER'] ?? '');
$pass   = trim($_POST['DB_PASS'] ?? '');
$dbname = trim($_POST['DB_NAME'] ?? '');

if (!$host || !$user || !$dbname) {
    echo json_encode(['success' => false, 'error' => 'Hôte, utilisateur et nom de la base sont obligatoires.']);
    exit;
}

// Si le mot de passe est vide, utiliser celui actuellement stocké dans .env
if ($pass === '') {
    $env = [];
    if (file_exists($envFile)) {
        foreach (file($envFile) as $line) {
            $line = trim($line);
            if ($line && strpos($line, '=') !== false) {
                [$k, $v] = explode('=', $line, 2);
                $env[trim($k)] = trim($v);
            }
        }
    }
    $pass = $env['DB_PASS'] ?? '';
}

// ─── TEST DE CONNEXION ────────────────────────────────────────────────────────
// PHP 8.1+ lance des exceptions mysqli - le @ ne suffit pas, on utilise try/catch
mysqli_report(MYSQLI_REPORT_OFF); // Désactiver les exceptions mysqli
try {
    $testConn = new mysqli($host, $user, $pass, $dbname);
    if ($testConn->connect_error) {
        echo json_encode([
            'success' => false,
            'error'   => 'Connexion échouée : ' . $testConn->connect_error
        ]);
        exit;
    }
    $testConn->close();
} catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => 'Connexion échouée : ' . $e->getMessage()
    ]);
    exit;
}


// ─── SI SEULEMENT TEST, S'ARRÊTER ICI ────────────────────────────────────────
if ($action === 'test') {
    echo json_encode(['success' => true, 'message' => '✅ Connexion réussie à la base de données "' . htmlspecialchars($dbname) . '" sur "' . htmlspecialchars($host) . '".']);
    exit;
}

// ─── SAUVEGARDER DANS .ENV ────────────────────────────────────────────────────
if (!is_writable($envFile) && !is_writable(dirname($envFile))) {
    echo json_encode(['success' => false, 'error' => 'Le fichier .env n\'est pas accessible en écriture (chmod 666 .env requis).']);
    exit;
}

$content = "DB_HOST=$host\nDB_USER=$user\nDB_PASS=$pass\nDB_NAME=$dbname\n";
if (file_put_contents($envFile, $content) !== false) {
    echo json_encode(['success' => true, 'message' => 'Paramètres enregistrés avec succès. La base de données est accessible.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Impossible d\'écrire dans le fichier .env.']);
}
?>
