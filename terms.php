<?php
/**
 * Página de términos y condiciones - Portal Digital de Raqchi
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
require_once __DIR__ . '/includes/bootstrap.php';

includeHeader('Términos y Condiciones', ['public.css']);
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1>Términos y Condiciones</h1>
            <p class="lead">Condiciones de uso del Portal Digital de Raqchi</p>
        </div>

        <div class="terms-content">
            <section>
                <h2>1. Aceptación de los Términos</h2>
                <p>Al acceder y utilizar el Portal Digital de Raqchi, usted acepta estar sujeto a estos términos y condiciones de uso.</p>
            </section>

            <section>
                <h2>2. Uso del Servicio</h2>
                <p>El Portal Digital de Raqchi es una plataforma para la compra de tickets y servicios turísticos relacionados con el sitio arqueológico de Raqchi.</p>
                <ul>
                    <li>Debe proporcionar información veraz y actualizada</li>
                    <li>Es responsable de mantener la confidencialidad de su cuenta</li>
                    <li>No debe usar el servicio para actividades ilegales o no autorizadas</li>
                </ul>
            </section>

            <section>
                <h2>3. Compras y Pagos</h2>
                <p>Todas las transacciones están sujetas a:</p>
                <ul>
                    <li>Disponibilidad de tickets y servicios</li>
                    <li>Precios vigentes al momento de la compra</li>
                    <li>Políticas de cancelación y reembolso específicas</li>
                </ul>
            </section>

            <section>
                <h2>4. Política de Cancelación</h2>
                <p>Las cancelaciones deben realizarse con al menos 24 horas de anticipación para tener derecho a reembolso completo.</p>
            </section>

            <section>
                <h2>5. Limitación de Responsabilidad</h2>
                <p>El Portal Digital de Raqchi no se hace responsable por daños indirectos, incidentales o consecuenciales derivados del uso del servicio.</p>
            </section>

            <section>
                <h2>6. Modificaciones</h2>
                <p>Nos reservamos el derecho de modificar estos términos en cualquier momento. Los cambios serán efectivos inmediatamente después de su publicación.</p>
            </section>

            <section>
                <h2>7. Contacto</h2>
                <p>Para preguntas sobre estos términos, contáctanos en:</p>
                <p>Email: legal@raqchi.com<br>
                Teléfono: +51 984 123 456</p>
            </section>
        </div>

        <div class="terms-footer">
            <p><strong>Última actualización:</strong> 12 de julio de 2025</p>
            <div class="action-buttons">
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary">Aceptar y Continuar</a>
                <a href="<?php echo SITE_URL; ?>/" class="btn btn-outline">Volver al Inicio</a>
            </div>
        </div>
    </div>
</main>

<style>
.terms-content {
    max-width: 800px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.terms-content section {
    margin-bottom: 2rem;
}

.terms-content h2 {
    color: var(--primary-color);
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.terms-content p, .terms-content li {
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 1rem;
}

.terms-content ul {
    padding-left: 1.5rem;
}

.terms-footer {
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
