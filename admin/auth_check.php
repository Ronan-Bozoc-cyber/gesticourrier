<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // Si c'est un appel AJAX/Fetch, retourner JSON au lieu de rediriger
    $isAjax = (
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
    );
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Session expirée. Veuillez vous reconnecter.']);
        exit;
    }
    header("Location: ./login.php");
    exit;
}
?>
