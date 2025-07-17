<?php
/**
 * API para probar configuración de email - Versión de prueba sin autenticación
 */

// Solo para debugging - eliminar en producción
if (isset($_GET['test_mode']) && $_GET['test_mode'] === 'debug') {
    // Simular sesión de admin para pruebas
    session_start();
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Test Admin';
    $_SESSION['roles'] = ['admin'];
}

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/../../includes/bootstrap.php';

// Configurar headers para API
header('Content-Type: application/json');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Log de inicio
    error_log("test_email_debug.php: Iniciando prueba de email");
    error_log("test_email_debug.php: POST data recibida: " . json_encode($_POST));
    
    // Obtener configuración de email del formulario
    $smtpHost = trim($_POST['smtp_host'] ?? '');
    $smtpPort = intval($_POST['smtp_port'] ?? 587);
    $smtpUsername = trim($_POST['smtp_username'] ?? '');
    $smtpPassword = $_POST['smtp_password'] ?? '';
    $smtpSecure = isset($_POST['smtp_secure']);
    
    // Log de parámetros recibidos
    error_log("test_email_debug.php: Host=$smtpHost, Port=$smtpPort, User=$smtpUsername, Secure=" . ($smtpSecure ? 'true' : 'false'));
    
    // Validar datos requeridos
    if (empty($smtpHost) || empty($smtpUsername)) {
        error_log("test_email_debug.php: Faltan datos requeridos");
        echo json_encode([
            'success' => false,
            'message' => 'Host SMTP y usuario son requeridos',
            'missing_fields' => [
                'smtp_host' => empty($smtpHost),
                'smtp_username' => empty($smtpUsername)
            ],
            'suggestion' => 'Complete todos los campos requeridos antes de probar'
        ]);
        exit;
    }
    
    // Verificar conectividad básica primero
    $connectivityCheck = testSMTPConnection($smtpHost, $smtpPort);
    if ($connectivityCheck['status'] === 'error') {
        error_log("test_email_debug.php: Falla de conectividad - " . $connectivityCheck['message']);
        echo json_encode([
            'success' => false,
            'message' => 'Error de conectividad: No se puede conectar al servidor SMTP',
            'details' => $connectivityCheck,
            'suggestion' => 'Verifique su conexión a internet y la configuración del servidor SMTP'
        ]);
        exit;
    }
    
    // Validar contraseña
    if (empty($smtpPassword) || $smtpPassword === '••••••••') {
        echo json_encode([
            'success' => false,
            'message' => 'Por favor, ingrese una contraseña válida para la prueba',
            'suggestion' => 'Use una contraseña real para probar la conexión'
        ]);
        exit;
    }
    
    // Intentar enviar email de prueba (simulado)
    error_log("test_email_debug.php: Iniciando prueba de envío simulada");
    $result = sendTestEmailSimulation($smtpHost, $smtpPort, $smtpUsername, $smtpPassword, $smtpSecure);
    
    error_log("test_email_debug.php: Resultado - " . json_encode($result));
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("test_email_debug.php: Error general - " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage(),
        'file' => basename(__FILE__),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}

function sendTestEmailSimulation($host, $port, $username, $password, $secure) {
    try {
        // Log de inicio de simulación
        error_log("Iniciando simulación de email con: $host:$port");
        
        // Validaciones básicas mejoradas
        $errors = [];
        $warnings = [];
        
        // Validar host
        if (empty($host)) {
            $errors[] = 'Host SMTP es requerido';
        }
        
        // Validar puerto
        if (!is_numeric($port) || $port < 1 || $port > 65535) {
            $errors[] = 'Puerto SMTP no válido: ' . $port;
        } elseif (!in_array($port, [25, 465, 587, 993, 995])) {
            $warnings[] = 'Puerto no estándar para SMTP: ' . $port . ' (puertos comunes: 25, 465, 587)';
        }
        
        // Validar email
        if (empty($username)) {
            $errors[] = 'Usuario SMTP es requerido';
        } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email de usuario no válido: ' . $username;
        }
        
        // Validar contraseña
        if (empty($password)) {
            $errors[] = 'Contraseña SMTP es requerida';
        } elseif ($password === '••••••••') {
            $errors[] = 'Por favor, ingrese la contraseña real (no el placeholder)';
        } elseif (strlen($password) < 3) {
            $warnings[] = 'Contraseña muy corta (mínimo recomendado: 8 caracteres)';
        }
        
        // Validar configuración de seguridad
        if ($port == 465 && !$secure) {
            $warnings[] = 'Puerto 465 normalmente requiere SSL/TLS';
        } elseif ($port == 587 && !$secure) {
            $warnings[] = 'Puerto 587 normalmente requiere STARTTLS';
        }
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => 'Errores de configuración encontrados',
                'errors' => $errors,
                'warnings' => $warnings
            ];
        }
        
        // Simular verificación de conectividad
        $connectionTest = testSMTPConnection($host, $port);
        
        // Verificar configuraciones comunes problemáticas
        $configTips = [];
        if ($host === 'smtp.gmail.com') {
            $configTips[] = 'Gmail requiere contraseña de aplicación, no la contraseña normal de cuenta';
            $configTips[] = 'Debe habilitar autenticación de 2 factores y generar una contraseña de aplicación';
        } elseif ($host === 'smtp.outlook.com' || $host === 'smtp-mail.outlook.com') {
            $configTips[] = 'Outlook/Hotmail puede requerir autenticación moderna (OAuth2)';
        }
        
        return [
            'success' => true,
            'message' => 'Configuración validada correctamente (modo simulación)',
            'details' => [
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'secure' => $secure ? 'TLS habilitado' : 'Sin encriptación',
                'connection_test' => $connectionTest,
                'warnings' => $warnings,
                'config_tips' => $configTips,
                'note' => 'Simulación sin PHPMailer - Para envío real instale: composer require phpmailer/phpmailer'
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Error en simulación de email: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error en simulación de email: ' . $e->getMessage()
        ];
    }
}

function testSMTPConnection($host, $port) {
    try {
        // Intentar conexión básica por socket (sin autenticación)
        $timeout = 10;
        $errno = 0;
        $errstr = '';
        
        error_log("Probando conexión SMTP a $host:$port con timeout de $timeout segundos");
        
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        
        if ($socket) {
            // Intentar leer respuesta del servidor
            $response = @fgets($socket, 512);
            @fclose($socket);
            
            $result = [
                'status' => 'success',
                'message' => "Conexión al servidor SMTP exitosa ($host:$port)"
            ];
            
            if ($response) {
                $result['server_response'] = trim($response);
                
                // Analizar respuesta del servidor
                if (preg_match('/^220/', $response)) {
                    $result['message'] .= ' - Servidor SMTP válido';
                } else {
                    $result['message'] .= ' - Respuesta inesperada del servidor';
                    $result['status'] = 'warning';
                }
            }
            
            return $result;
        } else {
            $errorMsg = "No se pudo conectar a $host:$port";
            if ($errstr) {
                $errorMsg .= " - Error: $errstr";
            }
            if ($errno) {
                $errorMsg .= " (Código: $errno)";
            }
            
            // Sugerir posibles soluciones
            $suggestions = [];
            if ($errno == 110 || strpos($errstr, 'timeout') !== false) {
                $suggestions[] = 'Timeout de conexión - verifique la conectividad a internet';
                $suggestions[] = 'El servidor SMTP puede estar bloqueado por firewall';
            } elseif ($errno == 111 || strpos($errstr, 'refused') !== false) {
                $suggestions[] = 'Conexión rechazada - verifique el host y puerto';
                $suggestions[] = 'El servicio SMTP puede no estar ejecutándose';
            }
            
            return [
                'status' => 'error',
                'message' => $errorMsg,
                'errno' => $errno,
                'errstr' => $errstr,
                'suggestions' => $suggestions
            ];
        }
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => 'Error al probar conexión: ' . $e->getMessage()
        ];
    }
}
?>
