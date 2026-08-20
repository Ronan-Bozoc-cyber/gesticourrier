<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$lockDir = __DIR__ . '/../data/';
if (!is_dir($lockDir)) {
    @mkdir($lockDir, 0777, true);
}
$lockFile = $lockDir . 'lock_state.json';
$lockTimeout = 900; // 15 minutes en secondes

function getLockState() {
    global $lockFile, $lockTimeout;
    
    $currentUser = $_SESSION['username'] ?? 'Anonyme';
    $currentUserId = $_SESSION['user_id'] ?? $_SESSION['username'] ?? 0;

    $defaultLock = [
        'lock_user_id'   => null,
        'lock_username'  => null,
        'lock_timestamp' => 0,
        'last_activity'  => 0,
        'is_active'      => false,
        'is_lock_holder' => false
    ];

    if (!file_exists($lockFile)) {
        return $defaultLock;
    }

    $json = @file_get_contents($lockFile);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        return $defaultLock;
    }

    $now = time();
    $lastActivity = intval($data['last_activity'] ?? 0);

    // Vérifier l'expiration du verrou par inactivité
    if (($now - $lastActivity) > $lockTimeout) {
        return $defaultLock;
    }

    $isHolder = ($data['lock_username'] === $currentUser);
    
    // Si c'est le détenteur, on met à jour son activité
    if ($isHolder) {
        $data['last_activity'] = $now;
        @file_put_contents($lockFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        @chmod($lockFile, 0666);
    }

    return [
        'lock_user_id'   => $data['lock_user_id'] ?? null,
        'lock_username'  => $data['lock_username'] ?? null,
        'lock_timestamp' => $data['lock_timestamp'] ?? 0,
        'last_activity'  => $data['last_activity'] ?? 0,
        'is_active'      => true,
        'is_lock_holder' => $isHolder
    ];
}

$lockState = getLockState();
$can_edit = !$lockState['is_active'] || $lockState['is_lock_holder'];
?>
