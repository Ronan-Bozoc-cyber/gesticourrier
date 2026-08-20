<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/partials/lock_system.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$currentUser = $_SESSION['username'] ?? null;
$currentUserId = $_SESSION['user_id'] ?? $currentUser;
$currentUserRole = $_SESSION['role'] ?? 'user';

$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';

if (empty($currentUser)) {
    header('Location: login.php');
    exit;
}

$now = time();
$lockDir = __DIR__ . '/data/';
if (!is_dir($lockDir)) {
    @mkdir($lockDir, 0777, true);
}
$lockFile = $lockDir . 'lock_state.json';

if ($action === 'claim' || $action === 'force_claim') {
    $newData = [
        'lock_user_id'   => $currentUserId,
        'lock_username'  => $currentUser,
        'lock_timestamp' => $now,
        'last_activity'  => $now
    ];
    file_put_contents($lockFile, json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    @chmod($lockFile, 0666);
}

if ($action === 'release') {
    $currentState = getLockState();
    if ($currentState['is_lock_holder'] || $currentUserRole === 'admin') {
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }
    }
}

header('Location: ' . $referer);
exit;
?>
