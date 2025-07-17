<?php
/**
 * Página de política de privacidad - Portal Digital de Raqchi
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
require_once __DIR__ . '/includes/bootstrap.php';

includeHeader('Política de Privacidad', ['public.css']);
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>Política de Privacidad</h1>
            <p class="lead">Cómo protegemos y utilizamos su información personal</p>
        </div>

        <div class="privacy-content">
            <section>
                <h2>1. Información que Recopilamos</h2>
                <p>Recopilamos información que usted nos proporciona directamente:</p>
                <ul>
                    <li>Información de contacto (nombre, email, teléfono)</li>
                    <li>Información de pago (procesada de forma segura)</li>
                    <li>Preferencias de visita y servicios</li>
                    <li>Comentarios y reseñas</li>
                </ul>
            </section>

            <section>
                <h2>2. Cómo Utilizamos su Información</h2>
                <p>Utilizamos su información para:</p>
                <ul>
                    <li>Procesar reservas y pagos</li>
                    <li>Enviar confirmaciones y actualizaciones</li>
                    <li>Mejorar nuestros servicios</li>
                    <li>Cumplir con obligaciones legales</li>
                </ul>
            </section>

            <section>
                <h2>3. Compartir Información</h2>
                <p>No vendemos ni alquilamos su información personal. Podemos compartir información limitada con:</p>
                <ul>
                    <li>Proveedores de servicios de pago</li>
                    <li>Autoridades cuando sea legalmente requerido</li>
                    <li>Terceros con su consentimiento explícito</li>
                </ul>
            </section>

            <section>
                <h2>4. Seguridad de los Datos</h2>
                <p>Implementamos medidas de seguridad apropiadas para proteger su información:</p>
                <ul>
                    <li>Encriptación SSL/TLS</li>
                    <li>Acceso restringido a datos personales</li>
                    <li>Monitoreo regular de seguridad</li>
                    <li>Respaldo seguro de datos</li>
                </ul>
            </section>

            <section>
                <h2>5. Sus Derechos</h2>
                <p>Usted tiene derecho a:</p>
                <ul>
                    <li>Acceder a su información personal</li>
                    <li>Corregir datos incorrectos</li>
                    <li>Solicitar la eliminación de sus datos</li>
                    <li>Retirar el consentimiento</li>
                </ul>
            </section>

            <section>
                <h2>6. Cookies y Tecnologías Similares</h2>
                <p>Utilizamos cookies para mejorar su experiencia en nuestro sitio web. Puede gestionar las preferencias de cookies en su navegador.</p>
            </section>

            <section>
                <h2>7. Retención de Datos</h2>
                <p>Conservamos su información personal solo durante el tiempo necesario para cumplir con los propósitos descritos en esta política.</p>
            </section>

            <section>
                <h2>8. Contacto</h2>
                <p>Para ejercer sus derechos o hacer preguntas sobre privacidad:</p>
                <p>Email: privacidad@raqchi.com<br>
                Teléfono: +51 984 123 456<br>
                Dirección: San Pedro, Canchis, Cusco, Perú</p>
            </section>
        </div>

        <div class="privacy-footer">
            <p><strong>Última actualización:</strong> 12 de julio de 2025</p>
            <div class="action-buttons">
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary">Aceptar y Continuar</a>
                <a href="<?php echo SITE_URL; ?>/" class="btn btn-outline">Volver al Inicio</a>
            </div>
        </div>
    </div>
</main>

<style>
.privacy-content {
    max-width: 800px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.privacy-content section {
    margin-bottom: 2rem;
}

.privacy-content h2 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.privacy-content p, .privacy-content li {
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 1rem;
}

.privacy-content ul {
    padding-left: 1.5rem;
}

.privacy-footer {
    text-align: center;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
}
</style>

<?php includeFooter(); ?>
