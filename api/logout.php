<?php
/**
 * API para cerrar sesión
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Solo permitir POST y GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    if (isset($_GET['redirect']) || !isAjaxRequest()) {
        header('Location: ' . SITE_URL . '/login.php?error=method_not_allowed');
        exit;
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Verificar que hay una sesión activa
    if (!isLoggedIn()) {
        if (isset($_GET['redirect']) || !isAjaxRequest()) {
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => rqText('not_logged_in')]);
        exit;
    }
    
    // Obtener datos del usuario antes de cerrar sesión para log
    $userId = $_SESSION['user_id'] ?? null;
    $userEmail = $_SESSION['user_email'] ?? '';
    $userName = $_SESSION['user_name'] ?? '';
    
    // Limpiar todas las variables de sesión
    $_SESSION = array();
    
    // Destruir la cookie de sesión si existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
    
    // Regenerar ID de sesión para nueva sesión
    session_start();
    session_regenerate_id(true);
    
    // Registrar logout en logs
    if ($userId) {
        try {
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'user_id' => $userId,
                'user_name' => $userName,
                'action' => 'logout',
                'description' => 'Usuario cerró sesión',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ];
            
            $logFile = __DIR__ . '/../logs/user_activity.log';
            if (!file_exists(dirname($logFile))) {
                mkdir(dirname($logFile), 0755, true);
            }
            file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
            
            // También intentar registrar en Supabase si está disponible
            $supabase = getSupabaseClient();
            if ($supabase) {
                $supabase->insert('logs_sistema', [
                    'usuario_id' => $userId,
                    'accion' => 'logout',
                    'descripcion' => 'Usuario cerró sesión',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'fecha' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (Exception $logError) {
            error_log('Error logging logout: ' . $logError->getMessage());
        }
    }
    
    // Verificar si es una petición de redirección directa o AJAX
    if (isset($_GET['redirect']) || !isAjaxRequest()) {
        // Redirección directa para enlaces normales
        header('Location: ' . SITE_URL . '/login.php?message=' . urlencode(rqText('logout_success')));
        exit;
    }
    
    // Respuesta JSON para peticiones AJAX
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => rqText('logout_success'),
        'redirect' => SITE_URL . '/login.php'
    ]);
    
} catch (Exception $e) {
    error_log('Logout error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => rqText('logout_error')
    ]);
}
?>
