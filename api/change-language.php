<?php
// Desactivar errores y HTML inesperado
ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(0);
// Iniciar buffer para limpiar salidas accidentales
ob_start();
// Definir acceso al sistema
if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
// Cargar configuración y funciones sin salida adicional
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=UTF-8');

// Obtener token CSRF desde cabecera
$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

// Verificar CSRF
if (!verifyCSRFToken($csrfHeader)) {
    if (ob_get_length()) ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => rqText('invalid_token')]);
    exit;
}

// Leer cuerpo JSON
$body = file_get_contents('php://input');
$data = json_decode($body, true);
$lang = sanitizeInput($data['language'] ?? '');

// Intentar cambiar idioma
if (setLanguage($lang)) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => true]);
    exit;
} else {
    if (ob_get_length()) ob_clean();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => rqText('invalid_data')]);
    exit;
}
