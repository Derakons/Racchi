<?php
/**
 * Bootstrap principal del proyecto Raqchi
 * Este archivo se incluye en todas las páginas para inicializar el sistema
 */

// Prevenir inclusión múltiple
if (defined('RACCHI_BOOTSTRAP_LOADED')) {
    return;
}
define('RACCHI_BOOTSTRAP_LOADED', true);

// Definir constante de acceso solo si no está definida
if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

// Incluir configuración principal
require_once __DIR__ . '/../config/config.php';

// Incluir funciones de utilidad
require_once __DIR__ . '/functions.php';

// Incluir cliente de Supabase
require_once __DIR__ . '/supabase.php';

// Incluir componentes de UI solo para solicitudes de página normal
if (!defined('API_REQUEST')) {
    require_once __DIR__ . '/header.php';
    require_once __DIR__ . '/footer.php';
}

// Autoload básico para clases adicionales
spl_autoload_register(function ($className) {
    $classFile = __DIR__ . '/' . strtolower($className) . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Inicializar variables globales
$currentLang = getCurrentLanguage();
$flashMessage = getFlashMessage();
?>
