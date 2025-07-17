<?php
/**
 * API endpoint para obtener textos traducidos desde JavaScript
 * Retorna siempre JSON limpio
 */

// Desactivar salida de errores y HTML inesperado
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(0);

// Iniciar buffer de salida para limpiar cualquier output accidental
ob_start();

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir acceso al sistema y cargar configuraciones
if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Limpiar cualquier salida previa
if (ob_get_length()) ob_clean();

// Establecer cabecera de respuesta JSON
header('Content-Type: application/json; charset=UTF-8');

// Determinar método HTTP
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    // Servicio GET: devuelve un solo texto
    $key = sanitizeInput($_GET['key'] ?? '');
    $lang = sanitizeInput($_GET['lang'] ?? getCurrentLanguage());
    
    // Extraer parámetros adicionales
    $params = [];
    foreach ($_GET as $p => $v) {
        if (strpos($p, 'param_') === 0) {
            $params[substr($p, 6)] = sanitizeInput($v);
        }
    }
    
    // Validar lenguaje
    if (!in_array($lang, AVAILABLE_LANGUAGES)) {
        $lang = DEFAULT_LANGUAGE;
    }
    
    // Obtener texto
    $text = rqText($key, $lang, $params);
    echo json_encode(['success' => true, 'key' => $key, 'lang' => $lang, 'text' => $text, 'params' => $params]);
    exit;

} elseif ($method === 'POST') {
    // Servicio POST: devuelve múltiples textos
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
        exit;
    }
    
    $lang = sanitizeInput($data['lang'] ?? getCurrentLanguage());
    if (!in_array($lang, AVAILABLE_LANGUAGES)) {
        $lang = DEFAULT_LANGUAGE;
    }
    
    $keys = $data['keys'] ?? [];
    $results = [];
    
    if (is_array($keys)) {
        foreach ($keys as $item) {
            if (is_string($item)) {
                $results[$item] = rqText($item, $lang);
            } elseif (is_array($item) && isset($item['key'])) {
                $key = $item['key'];
                $params = is_array($item['params']) ? $item['params'] : [];
                $results[$key] = rqText($key, $lang, $params);
            }
        }
    }
    
    echo json_encode(['success' => true, 'lang' => $lang, 'texts' => $results]);
    exit;

} else {
    // Método no permitido
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}
