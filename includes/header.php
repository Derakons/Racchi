<?php
/**
 * Header común para todas las páginas
 */

// Prevenir acceso directo
if (!defined('RACCHI_ACCESS')) {
    die('Acceso directo no permitido');
}

function includeHeader($title = null, $additionalCSS = [], $additionalJS = []) {
    global $currentLang;
    $flashMessage = getFlashMessage();
    $pageTitle = $title ? $title . ' - ' . rqText('site_name') : rqText('site_name');
    
    echo '<!DOCTYPE html>';
    echo '<html lang="' . $currentLang . '">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . htmlspecialchars($pageTitle) . '</title>';
    echo '<meta name="description" content="Portal Digital de Raqchi - Compra tickets y servicios turísticos">';
    echo '<meta name="keywords" content="Raqchi, turismo, tickets, guías, Cusco, Perú">';
    
    // CSS principal
    echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/main.css">';
    echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/components.css">';
    
    // Font Awesome para íconos
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
    
    // CSS adicional
    foreach ($additionalCSS as $css) {
        echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/' . $css . '">';
    }
    
    // Favicon
    echo '<link rel="icon" type="image/x-icon" href="' . SITE_URL . '/assets/images/favicon.ico">';
    
    // Meta tags para redes sociales
    echo '<meta property="og:title" content="' . htmlspecialchars($pageTitle) . '">';
    echo '<meta property="og:description" content="Descubre la magia de Raqchi con nuestro portal digital">';
    echo '<meta property="og:image" content="' . SITE_URL . '/assets/images/og-image.jpg">';
    echo '<meta property="og:url" content="' . SITE_URL . '">';
    
    echo '</head>';
    echo '<body>';
    
    // Definir variables JS globales
    echo '<script>';
    echo 'window.csrfToken = "' . generateCSRFToken() . '";';
    echo 'window.SITE_URL = "' . SITE_URL . '";';
    echo 'window.currentLanguage = "' . getCurrentLanguage() . '";';
    echo '</script>';
    
    // Header de navegación
    includeNavigation();
    
    // Mensaje flash
    if ($flashMessage) {
        echo '<div class="flash-message flash-' . $flashMessage['type'] . '" id="flashMessage">';
        echo htmlspecialchars($flashMessage['text']);
        echo '<button onclick="closeFlashMessage()" class="flash-close">&times;</button>';
        echo '</div>';
    }
}

// Función para incluir navegación
function includeNavigation() {
    global $currentLang;
    
    echo '<nav class="main-nav">';
    echo '<div class="container">';
    echo '<div class="nav-content">';
    
    // Logo
    echo '<div class="nav-logo">';
    echo '<a href="' . SITE_URL . '/">';
    echo '<img src="' . SITE_URL . '/assets/images/logo.png" alt="' . rqText('site_name') . '" class="site_name">';
    echo '</a>';
    echo '</div>';
    
    // Menú principal
    echo '<div class="nav-menu" id="navMenu">';
    echo '<ul>';
    echo '<li><a href="' . SITE_URL . '/">' . rqText('home') . '</a></li>';
    echo '<li><a href="' . SITE_URL . '/public/tickets.php">' . rqText('tickets') . '</a></li>';
    echo '<li><a href="' . SITE_URL . '/public/services.php">' . rqText('services') . '</a></li>';
    echo '<li><a href="' . SITE_URL . '/public/reviews.php">' . rqText('reviews') . '</a></li>';
    echo '<li><a href="' . SITE_URL . '/public/gallery.php">' . rqText('gallery') . '</a></li>';
    echo '<li><a href="' . SITE_URL . '/public/faq.php">' . rqText('faq') . '</a></li>';
    echo '<li><a href="' . SITE_URL . '/public/help.php">' . rqText('help') . '</a></li>';
    echo '</ul>';
    echo '</div>';
    
    // Selector de idioma y botones
    echo '<div class="nav-actions">';
    
    // Selector de idioma
    echo '<div class="language-selector">';
    echo '<select onchange="changeLanguage(this.value)" id="languageSelect">';
    echo '<option value="es"' . ($currentLang === 'es' ? ' selected' : '') . '>ES</option>';
    echo '<option value="en"' . ($currentLang === 'en' ? ' selected' : '') . '>EN</option>';
    echo '</select>';
    echo '</div>';
    
    // Botón de login/admin
    if (isLoggedIn()) {
        if (hasRole('admin')) {
            echo '<a href="' . SITE_URL . '/admin/" class="btn btn-secondary btn-compact">' . rqText('admin') . '</a>';
        } elseif (hasRole('vendedor')) {
            echo '<a href="' . SITE_URL . '/taquilla/" class="btn btn-secondary btn-compact">' . rqText('ticket_office') . '</a>';
        }
        echo '<a href="' . SITE_URL . '/api/logout.php" class="btn btn-outline btn-compact">' . rqText('logout') . '</a>';
    } else {
        echo '<a href="' . SITE_URL . '/login.php" class="btn btn-outline btn-compact">' . rqText('login') . '</a>';
    }
    
    // Botón de menú móvil
    echo '<button class="nav-toggle" onclick="toggleMobileMenu()" id="navToggle">';
    echo '<span></span><span></span><span></span>';
    echo '</button>';
    
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</nav>';
}

// Función específica para header de admin (sin navegación principal)
function includeAdminHeader($title = null, $additionalCSS = [], $additionalJS = []) {
    global $currentLang;
    $flashMessage = getFlashMessage();
    $pageTitle = $title ? $title . ' - ' . rqText('site_name') : rqText('site_name');
    
    echo '<!DOCTYPE html>';
    echo '<html lang="' . $currentLang . '">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . htmlspecialchars($pageTitle) . '</title>';
    echo '<meta name="description" content="Portal Digital de Raqchi - Panel de Administración">';
    echo '<meta name="keywords" content="Raqchi, admin, panel, gestión">';
    
    // CSS principal
    echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/main.css">';
    echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/components.css">';
    
    // Font Awesome para íconos
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
    
    // CSS adicional
    foreach ($additionalCSS as $css) {
        echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/' . $css . '">';
    }
    
    // Favicon
    echo '<link rel="icon" type="image/x-icon" href="' . SITE_URL . '/assets/images/favicon.ico">';
    
    // Meta tags para redes sociales
    echo '<meta property="og:title" content="' . htmlspecialchars($pageTitle) . '">';
    echo '<meta property="og:description" content="Panel de administración - Portal Digital de Raqchi">';
    echo '<meta property="og:image" content="' . SITE_URL . '/assets/images/og-image.jpg">';
    echo '<meta property="og:url" content="' . SITE_URL . '">';
    
    echo '</head>';
    echo '<body class="admin-body">';
    
    // Definir variables JS globales
    echo '<script>';
    echo 'window.csrfToken = "' . generateCSRFToken() . '";';
    echo 'window.SITE_URL = "' . SITE_URL . '";';
    echo 'window.currentLanguage = "' . getCurrentLanguage() . '";';
    echo '</script>';
    
    // Mensaje flash para admin
    if ($flashMessage) {
        echo '<div class="flash-message flash-' . $flashMessage['type'] . ' admin-flash" id="flashMessage">';
        echo htmlspecialchars($flashMessage['text']);
        echo '<button onclick="closeFlashMessage()" class="flash-close">&times;</button>';
        echo '</div>';
    }
}
?>
