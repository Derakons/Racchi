<?php
/**
 * Diagnóstico rápido del sistema de email
 */

// Configuración básica
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

try {
    // Información del sistema
    $diagnostics = [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'script_path' => __FILE__,
        'current_directory' => getcwd(),
        'include_path' => get_include_path(),
        'loaded_extensions' => get_loaded_extensions(),
        'post_data' => $_POST,
        'get_data' => $_GET,
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'None',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ];
    
    // Verificar archivos importantes
    $files_check = [
        'bootstrap.php' => file_exists(__DIR__ . '/includes/bootstrap.php'),
        'config.php' => file_exists(__DIR__ . '/config/config.php'),
        'functions.php' => file_exists(__DIR__ . '/includes/functions.php'),
        'composer_autoload' => file_exists(__DIR__ . '/vendor/autoload.php'),
        'test_email_api' => file_exists(__DIR__ . '/api/admin/test_email.php')
    ];
    
    // Verificar extensiones de PHP necesarias
    $extensions_check = [
        'openssl' => extension_loaded('openssl'),
        'sockets' => extension_loaded('sockets'),
        'curl' => extension_loaded('curl'),
        'mbstring' => extension_loaded('mbstring'),
        'json' => extension_loaded('json')
    ];
    
    // Verificar configuración de red
    $network_check = [];
    $test_hosts = ['smtp.gmail.com', 'smtp.outlook.com', 'google.com'];
    
    foreach ($test_hosts as $host) {
        $start = microtime(true);
        $socket = @fsockopen($host, 80, $errno, $errstr, 5);
        $end = microtime(true);
        
        if ($socket) {
            fclose($socket);
            $network_check[$host] = [
                'status' => 'success',
                'time' => round(($end - $start) * 1000, 2) . 'ms'
            ];
        } else {
            $network_check[$host] = [
                'status' => 'failed',
                'error' => $errstr,
                'errno' => $errno
            ];
        }
    }
    
    // Respuesta exitosa
    echo json_encode([
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s'),
        'diagnostics' => $diagnostics,
        'files_check' => $files_check,
        'extensions_check' => $extensions_check,
        'network_check' => $network_check
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
