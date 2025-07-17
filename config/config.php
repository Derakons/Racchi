<?php
/**
 * Configuración principal del Portal Digital de Raqchi
 * 
 * Este archivo contiene todas las configuraciones necesarias para:
 * - Conexión a Supabase
 * - Configuraciones de pago
 * - Configuraciones generales del sitio
 */

// Prevenir acceso directo
if (!defined('RACCHI_ACCESS')) {
    die('Acceso directo no permitido');
}

// Configuración de Supabase
define('SUPABASE_URL', 'https://evitjnpybszhtaeeehuk.supabase.co');
define('SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImV2aXRqbnB5YnN6aHRhZWVlaHVrIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTIzMzQ2OTQsImV4cCI6MjA2NzkxMDY5NH0.yMECmnbUADXQzr9xN7j_ZCpSlpTt4shPwvNZtknjwZQ');
define('SUPABASE_SERVICE_ROLE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImV2aXRqbnB5YnN6aHRhZWVlaHVrIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1MjMzNDY5NCwiZXhwIjoyMDY3OTEwNjk0fQ.uh2QmRYDn-6t9jjWNIzE51TLZf4T6I3Os98CwDExi2c');

// Configuración del sitio
define('SITE_NAME', 'Portal Digital Raqchi');
define('SITE_URL', 'http://localhost/Racchi');
define('ADMIN_EMAIL', 'admin@raqchi.com');

// Configuración de idiomas
define('DEFAULT_LANGUAGE', 'es');
define('AVAILABLE_LANGUAGES', ['es', 'en']);

// Configuración de tickets
define('TICKET_ADULT_NATIONAL', 15);
define('TICKET_ADULT_FOREIGN', 30);
define('TICKET_STUDENT', 8);
define('GUIDE_SERVICE_PRICE', 50);

// Configuración de pagos
define('PAYPAL_CLIENT_ID', 'tu-paypal-client-id');
define('PAYPAL_CLIENT_SECRET', 'tu-paypal-client-secret');
define('PAYPAL_SANDBOX', true); // Cambiar a false en producción

// Configuración de email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'tu-email@gmail.com');
define('SMTP_PASSWORD', 'tu-password-app');

// Configuración de archivos
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Zona horaria
date_default_timezone_set('America/Lima');

// Configuración de errores (solo en desarrollo)
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('DEBUG_MODE', true);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    define('DEBUG_MODE', false);
}

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Configuración e inicio de sesión - solo si no hay sesión activa
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    session_start();
}
?>
