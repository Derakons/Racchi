<?php
/**
 * API para probar configuración de email
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/../../includes/bootstrap.php';

// Configurar headers para API
header('Content-Type: application/json');

// Verificar autenticación y rol para API
requireRoleAPI('admin');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Log de inicio
    error_log("test_email.php: Iniciando prueba de email");
    error_log("test_email.php: POST data recibida: " . json_encode($_POST));
    
    // Obtener configuración de email del formulario
    $smtpHost = trim($_POST['smtp_host'] ?? '');
    $smtpPort = intval($_POST['smtp_port'] ?? 587);
    $smtpUsername = trim($_POST['smtp_username'] ?? '');
    $smtpPassword = $_POST['smtp_password'] ?? '';
    $smtpSecure = isset($_POST['smtp_secure']);
    
    // Log de parámetros recibidos
    error_log("test_email.php: Host=$smtpHost, Port=$smtpPort, User=$smtpUsername, Secure=" . ($smtpSecure ? 'true' : 'false'));
    
    // Validar datos requeridos
    if (empty($smtpHost) || empty($smtpUsername)) {
        error_log("test_email.php: Faltan datos requeridos");
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
        error_log("test_email.php: Falla de conectividad - " . $connectivityCheck['message']);
        echo json_encode([
            'success' => false,
            'message' => 'Error de conectividad: No se puede conectar al servidor SMTP',
            'details' => $connectivityCheck,
            'suggestion' => 'Verifique su conexión a internet y la configuración del servidor SMTP'
        ]);
        exit;
    }
    
    // Si la contraseña es placeholder, usar la guardada
    if ($smtpPassword === '••••••••') {
        error_log("test_email.php: Usando contraseña guardada");
        $savedSettings = loadEmailSettings();
        $smtpPassword = $savedSettings['smtp_password'] ?? '';
        
        if (empty($smtpPassword)) {
            error_log("test_email.php: No hay contraseña guardada");
            echo json_encode([
                'success' => false,
                'message' => 'No hay contraseña guardada. Por favor, ingrese la contraseña SMTP.'
            ]);
            exit;
        }
    }
    
    // Intentar enviar email de prueba
    error_log("test_email.php: Iniciando prueba de envío");
    $result = sendTestEmail($smtpHost, $smtpPort, $smtpUsername, $smtpPassword, $smtpSecure);
    
    error_log("test_email.php: Resultado - " . json_encode($result));
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log("test_email.php: Error general - " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage(),
        'file' => basename(__FILE__),
        'line' => $e->getLine()
    ]);
}

function sendTestEmail($host, $port, $username, $password, $secure) {
    try {
        // Verificar si PHPMailer está disponible
        $phpmailerPath = __DIR__ . '/../../vendor/autoload.php';
        $hasComposer = file_exists($phpmailerPath);
        
        // Log del intento
        error_log("Probando email con host: $host, port: $port, username: $username");
        
        if ($hasComposer) {
            require_once $phpmailerPath;
            if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                return sendTestEmailWithPHPMailer($host, $port, $username, $password, $secure);
            }
        }
        
        // Fallback a simulación mejorada
        return sendTestEmailSimulation($host, $port, $username, $password, $secure);
        
    } catch (Exception $e) {
        error_log("Error en sendTestEmail: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error interno al probar email: ' . $e->getMessage(),
            'debug_info' => [
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'error' => $e->getMessage()
            ]
        ];
    }
}

function sendTestEmailWithPHPMailer($host, $port, $username, $password, $secure) {
    // Intentar cargar PHPMailer
    $phpmailerPath = __DIR__ . '/../../vendor/autoload.php';
    if (file_exists($phpmailerPath)) {
        require_once $phpmailerPath;
    }
    
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return [
            'success' => false,
            'message' => 'PHPMailer no está instalado',
            'suggestion' => 'Use el instalador automático o ejecute: composer require phpmailer/phpmailer'
        ];
    }
    
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Configuración del servidor con timeout extendido
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = $secure ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : false;
        $mail->Port = $port;
        
        // Configuración de debugging y timeouts
        $mail->SMTPDebug = 0; // Producción: 0, Debug: 2
        $mail->Timeout = 10;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Configuración del email
        $mail->setFrom($username, 'Portal Raqchi');
        $mail->addAddress($username); // Enviar a sí mismo para prueba
        
        $mail->isHTML(true);
        $mail->Subject = 'Prueba de Configuración de Email - Portal Raqchi';
        $mail->Body = '
            <h2>Prueba de Email Exitosa</h2>
            <p>Si recibe este email, la configuración SMTP está funcionando correctamente.</p>
            <p><strong>Configuración utilizada:</strong></p>
            <ul>
                <li>Host: ' . htmlspecialchars($host) . '</li>
                <li>Puerto: ' . $port . '</li>
                <li>Usuario: ' . htmlspecialchars($username) . '</li>
                <li>Seguro: ' . ($secure ? 'Sí' : 'No') . '</li>
            </ul>
            <p>Fecha y hora: ' . date('Y-m-d H:i:s') . '</p>
        ';
        
        $mail->send();
        
        logActivity('Email test successful', 'info', [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'secure' => $secure
        ]);
        
        return [
            'success' => true,
            'message' => 'Email de prueba enviado exitosamente con PHPMailer',
            'details' => [
                'method' => 'PHPMailer',
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'secure' => $secure
            ]
        ];
        
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        $suggestions = [];
        
        // Analizar el error y proporcionar sugerencias específicas
        if (strpos($errorMessage, 'Could not authenticate') !== false) {
            $suggestions[] = 'Credenciales incorrectas - verifique usuario y contraseña';
            if (strpos($host, 'gmail') !== false) {
                $suggestions[] = 'Para Gmail, use una contraseña de aplicación, no la contraseña normal';
            }
        } elseif (strpos($errorMessage, 'Connection refused') !== false) {
            $suggestions[] = 'Conexión rechazada - verifique host y puerto';
            $suggestions[] = 'Asegúrese de que el firewall permita la conexión';
        } elseif (strpos($errorMessage, 'timeout') !== false) {
            $suggestions[] = 'Timeout de conexión - verifique la conectividad de red';
            $suggestions[] = 'Pruebe con un puerto diferente (587, 465, 25)';
        }
        
        logActivity('Email test failed', 'error', [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'error' => $errorMessage,
            'suggestions' => $suggestions
        ]);
        
        return [
            'success' => false,
            'message' => 'Error al enviar email con PHPMailer: ' . $errorMessage,
            'suggestions' => $suggestions,
            'debug_info' => [
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'error_details' => $errorMessage
            ]
        ];
    }
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
        } elseif (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) && 
                  !filter_var($host, FILTER_VALIDATE_IP) && 
                  $host !== 'localhost' && $host !== '127.0.0.1') {
            $warnings[] = 'Host SMTP puede no ser válido: ' . $host;
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
        
        // Log de resultados
        logActivity('Email test simulated', 'info', [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'secure' => $secure,
            'connection_test' => $connectionTest,
            'warnings' => $warnings,
            'note' => 'Simulación - PHPMailer no disponible'
        ]);
        
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
                'note' => 'Para envío real de emails, instale PHPMailer via Composer: composer require phpmailer/phpmailer'
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

function loadEmailSettings() {
    $configFile = __DIR__ . '/../../config/email_settings.json';
    if (file_exists($configFile)) {
        return json_decode(file_get_contents($configFile), true) ?: [];
    }
    return [];
}
?>
