<?php
/**
 * Footer común para todas las páginas
 */

// Prevenir acceso directo
if (!defined('RACCHI_ACCESS')) {
    die('Acceso directo no permitido');
}

// Función para incluir footer común
function includeFooter($additionalJS = []) {
    echo '<footer class="main-footer">';
    echo '<div class="container">';
    echo '<div class="footer-content">';
    
    echo '<div class="footer-section">';
    echo '<h3>' . rqText('site_name') . '</h3>';
    echo '<p>' . rqText('footer_description') . '</p>';
    echo '<div class="social-links">';
    echo '<a href="#" aria-label="Facebook"><i class="icon-facebook"></i></a>';
    echo '<a href="#" aria-label="Instagram"><i class="icon-instagram"></i></a>';
    echo '<a href="#" aria-label="Twitter"><i class="icon-twitter"></i></a>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="footer-section">';
    echo '<h4>' . rqText('quick_links') . '</h4>';
    echo '<ul>';
    echo '<li><a href="/">' . rqText('home') . '</a></li>';
    echo '<li><a href="/compra.html">' . rqText('tickets') . '</a></li>';
    echo '<li><a href="/servicios.html">' . rqText('services') . '</a></li>';
    echo '<li><a href="/reseñas.html">' . rqText('reviews') . '</a></li>';
    echo '</ul>';
    echo '</div>';
    
    echo '<div class="footer-section">';
    echo '<h4>' . rqText('help') . '</h4>';
    echo '<ul>';
    echo '<li><a href="/ayuda.html">' . rqText('faq') . '</a></li>';
    echo '<li><a href="/terminos-condiciones.html">' . rqText('terms_and_conditions') . '</a></li>';
    echo '<li><a href="/politicas-privacidad.html">' . rqText('privacy_policy') . '</a></li>';
    echo '<li><a href="/libro-reclamaciones.html">' . rqText('complaints_book') . '</a></li>';
    echo '</ul>';
    echo '</div>';
    
    echo '<div class="footer-section">';
    echo '<h4>' . rqText('contact') . '</h4>';
    echo '<div class="contact-info">';
    echo '<p><i class="icon-email"></i> info@raqchi.com</p>';
    echo '<p><i class="icon-phone"></i> +51 984 123 456</p>';
    echo '<p><i class="icon-location"></i> San Pedro de Cacha, Canchis, Cusco</p>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
    
    echo '<div class="footer-bottom">';
    echo '<div class="footer-bottom-content">';
    echo '<p>&copy; ' . date('Y') . ' ' . rqText('site_name') . '. ' . rqText('all_rights_reserved') . '</p>';
    echo '<div class="footer-certifications">';
    echo '<span>' . rqText('certified_by') . ' MINCETUR</span>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
    echo '</footer>';
    
    // JavaScript principal
    echo '<script src="' . SITE_URL . '/assets/js/main.js"></script>';
    
    // JavaScript adicional
    foreach ($additionalJS as $js) {
        echo '<script src="' . SITE_URL . '/assets/js/' . $js . '"></script>';
    }
    
    echo '</body>';
    echo '</html>';
}
?>
