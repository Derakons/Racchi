<?php
/**
 * Página de servicios - Portal Digital de Raqchi
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
require_once __DIR__ . '/../includes/bootstrap.php';

includeHeader(rqText('services'), ['public.css']);
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1><?= rqText('services') ?></h1>
            <p class="lead"><?= rqText('services_description') ?></p>
        </div>

        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="icon-guide"></i>
                </div>
                <div class="service-content">
                    <h3><?= rqText('tour_guides') ?></h3>
                    <p><?= rqText('tour_guides_description') ?></p>
                    <ul class="service-features">
                        <li><?= rqText('multilingual_guides') ?></li>
                        <li><?= rqText('local_expertise') ?></li>
                        <li><?= rqText('group_tours') ?></li>
                    </ul>
                    <button class="btn btn-primary"><?= rqText('request_guide') ?></button>
                </div>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="icon-transport"></i>
                </div>
                <div class="service-content">
                    <h3><?= rqText('transportation') ?></h3>
                    <p><?= rqText('transportation_description') ?></p>
                    <ul class="service-features">
                        <li><?= rqText('comfortable_vehicles') ?></li>
                        <li><?= rqText('professional_drivers') ?></li>
                        <li><?= rqText('flexible_schedules') ?></li>
                    </ul>
                    <button class="btn btn-primary"><?= rqText('book_transport') ?></button>
                </div>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="icon-food"></i>
                </div>
                <div class="service-content">
                    <h3><?= rqText('local_cuisine') ?></h3>
                    <p><?= rqText('local_cuisine_description') ?></p>
                    <ul class="service-features">
                        <li><?= rqText('traditional_dishes') ?></li>
                        <li><?= rqText('fresh_ingredients') ?></li>
                        <li><?= rqText('dietary_options') ?></li>
                    </ul>
                    <button class="btn btn-primary"><?= rqText('view_menu') ?></button>
                </div>
            </div>

            <div class="service-card">
                <div class="service-icon">
                    <i class="icon-workshop"></i>
                </div>
                <div class="service-content">
                    <h3><?= rqText('workshops') ?></h3>
                    <p><?= rqText('workshops_description') ?></p>
                    <ul class="service-features">
                        <li><?= rqText('pottery_classes') ?></li>
                        <li><?= rqText('textile_weaving') ?></li>
                        <li><?= rqText('cultural_immersion') ?></li>
                    </ul>
                    <button class="btn btn-primary"><?= rqText('join_workshop') ?></button>
                </div>
            </div>
        </div>

        <div class="contact-section">
            <h2><?= rqText('need_more_info') ?></h2>
            <p><?= rqText('contact_us_services') ?></p>
            <div class="contact-grid">
                <div class="contact-item">
                    <strong><?= rqText('phone') ?>:</strong>
                    <span>+51 984 123 456</span>
                </div>
                <div class="contact-item">
                    <strong><?= rqText('email') ?>:</strong>
                    <span>servicios@raqchi.pe</span>
                </div>
                <div class="contact-item">
                    <strong><?= rqText('whatsapp') ?>:</strong>
                    <span>+51 984 123 456</span>
                </div>
            </div>
        </div>
    </div>
</main>

<?php includeFooter(); ?>
