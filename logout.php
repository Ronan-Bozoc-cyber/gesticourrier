<?php
session_start();

include_once __DIR__ . '/partials/lock_system.php';
$lockState = getLockState();

if ($lockState['is_lock_holder']) {
    $lockFile = __DIR__ . '/data/lock_state.json';
    if (file_exists($lockFile)) {
        @unlink($lockFile);
    }
}

session_destroy();
header("Location: login.php");
exit;
?>
