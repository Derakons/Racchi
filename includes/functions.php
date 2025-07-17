<?php
/**
 * Funciones de utilidad general para el proyecto Raqchi
 */

// Prevenir acceso directo
if (!defined('RACCHI_ACCESS')) {
    die('Acceso directo no permitido');
}

// Prevenir inclusión múltiple
if (defined('RACCHI_FUNCTIONS_LOADED')) {
    return;
}
define('RACCHI_FUNCTIONS_LOADED', true);

/**
 * Sanitizar entrada de usuario
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generar token único
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Formatear precio
 */
function formatPrice($price, $currency = 'PEN') {
    switch ($currency) {
        case 'USD':
            return '$' . number_format($price, 2);
        case 'PEN':
        default:
            return 'S/ ' . number_format($price, 2);
    }
}

/**
 * Obtener idioma actual
 */
function getCurrentLanguage() {
    return $_SESSION['language'] ?? DEFAULT_LANGUAGE;
}

/**
 * Cambiar idioma
 */
function setLanguage($lang) {
    if (in_array($lang, AVAILABLE_LANGUAGES)) {
        $_SESSION['language'] = $lang;
        return true;
    }
    return false;
}

/**
 * Obtener texto traducido con soporte de parámetros
 * Función principal de traducciones del sistema Raqchi
 */
function rqText($key, $lang = null, $params = []) {
    if (!$lang) {
        $lang = getCurrentLanguage();
    }

    static $translations = [];

    // Cargar traducciones por idioma si no están en caché
    if (!isset($translations[$lang])) {
        $translationFile = __DIR__ . '/../config/translations_' . $lang . '.php';
        if (file_exists($translationFile)) {
            $translations[$lang] = include $translationFile;
        } else {
            $translations[$lang] = [];
        }
    }

    // Texto base
    $text = $translations[$lang][$key] ?? $key;

    // Reemplazar parámetros si existen
    if (!empty($params) && is_array($params)) {
        foreach ($params as $placeholder => $value) {
            $text = str_replace('{' . $placeholder . '}', $value, $text);
        }
    }

    return $text;
}

/**
 * Función de traducción principal para el sistema Raqchi
 * Usamos un nombre único para evitar conflictos
 */
function getTextRq($key, $lang = null, $params = []) {
    return rqText($key, $lang, $params);
}

/**
 * Función de traducción personalizada para evitar conflictos con gettext
 * Usamos un nombre único para el sistema Raqchi
 */
function getTextRqCustom($key, $lang = null, $params = []) {
    return rqText($key, $lang, $params);
}

/**
 * Alias para compatibilidad - solo si no existe la función nativa
 */
if (!function_exists('getText')) {
    function getText($key, $lang = null, $params = []) {
        return rqText($key, $lang, $params);
    }
}

/**
 * Alias para compatibilidad con la API
 */
function getRacchiText($key, $lang = null, $params = []) {
    return rqText($key, $lang, $params);
}

/**
 * Redireccionar con mensaje
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Location: ' . $url);
    exit;
}

/**
 * Obtener y limpiar mensaje flash
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'text' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }
    return null;
}

/**
 * Verificar si el usuario está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Verificar rol del usuario
 */
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'] ?? '';
    
    // Si es un array de roles, verificar si el usuario tiene alguno
    if (is_array($role)) {
        return in_array($userRole, $role);
    }
    
    // Si es un solo rol, verificar exactamente
    return $userRole === $role;
}

/**
 * Requerir login
 */
function requireLogin($redirect = null) {
    if (!isLoggedIn()) {
        // Si no se especifica redirect, usar la página de login del sitio
        if ($redirect === null) {
            $redirect = SITE_URL . '/login.php';
        }
        redirectWithMessage($redirect, rqText('login_required'), 'error');
    }
}

/**
 * Requerir rol específico
 */
function requireRole($role, $redirect = null) {
    // Si no se especifica redirect, usar la página de login del sitio
    if ($redirect === null) {
        $redirect = SITE_URL . '/login.php';
    }
    requireLogin($redirect);
    if (!hasRole($role)) {
        redirectWithMessage($redirect, rqText('insufficient_permissions'), 'error');
    }
}

/**
 * Requerir rol específico para APIs (devuelve JSON en lugar de redirigir)
 */
function requireRoleAPI($role) {
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Sesión no válida. Por favor, inicie sesión.',
            'error_code' => 'NOT_AUTHENTICATED'
        ]);
        exit;
    }
    
    // Verificar si tiene el rol requerido
    if (!hasRole($role)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Permisos insuficientes. Se requiere rol: ' . $role,
            'error_code' => 'INSUFFICIENT_PERMISSIONS'
        ]);
        exit;
    }
}

/**
 * Generar CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Registrar log
 */
function logActivity($message, $level = 'info', $context = []) {
    if (DEBUG_MODE) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'user_id' => $_SESSION['user_id'] ?? 'guest',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $logFile = __DIR__ . '/../uploads/logs/app_' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    }
}

/**
 * Validar archivo subido
 */
function validateUploadedFile($file, $allowedTypes = ALLOWED_IMAGE_TYPES, $maxSize = MAX_FILE_SIZE) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Error al subir el archivo'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'El archivo es demasiado grande'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        return ['valid' => false, 'error' => 'Tipo de archivo no permitido'];
    }
    
    return ['valid' => true];
}

/**
 * Generar QR Code (requiere librería externa)
 */
function generateQRCode($data, $size = 200) {
    // Nota: Implementar con librería como phpqrcode
    // Por ahora, retornamos una URL de servicio externo
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
}

/**
 * Enviar email
 */
function sendEmail($to, $subject, $body, $attachments = []) {
    // Configuración básica de email
    $headers = [
        'From: ' . ADMIN_EMAIL,
        'Reply-To: ' . ADMIN_EMAIL,
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    // Para implementación completa con SMTP, usar PHPMailer
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * Formatear fecha según idioma
 */
function formatDate($date, $format = null) {
    $lang = getCurrentLanguage();
    
    if (!$format) {
        $format = $lang === 'en' ? 'M d, Y' : 'd/m/Y';
    }
    
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    
    return $date->format($format);
}

/**
 * Calcular precio total de ticket
 */
function calculateTicketPrice($ticketType, $includeGuide = false, $quantity = 1) {
    $prices = [
        'adult_national' => TICKET_ADULT_NATIONAL,
        'adult_foreign' => TICKET_ADULT_FOREIGN,
        'student' => TICKET_STUDENT
    ];
    
    $basePrice = $prices[$ticketType] ?? 0;
    $guidePrice = $includeGuide ? GUIDE_SERVICE_PRICE : 0;
    
    return ($basePrice + $guidePrice) * $quantity;
}

/**
 * Verificar si la petición es AJAX
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?>
