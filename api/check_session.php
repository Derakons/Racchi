<?php
/**
 * API para verificar estado de sesión
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json');

try {
    if (isLoggedIn()) {
        echo json_encode([
            'logged_in' => true,
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_name' => $_SESSION['user_name'] ?? '',
            'user_email' => $_SESSION['user_email'] ?? '',
            'user_role' => $_SESSION['user_role'] ?? '',
            'session_start' => $_SESSION['session_start'] ?? null
        ]);
    } else {
        echo json_encode([
            'logged_in' => false,
            'message' => rqText('not_logged_in')
        ]);
    }
} catch (Exception $e) {
    error_log('Session check error: ' . $e->getMessage());
    echo json_encode([
        'logged_in' => false,
        'error' => 'Internal server error'
    ]);
}
?>
