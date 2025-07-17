<?php
/**
 * Página de ayuda - Portal Digital de Raqchi
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
require_once __DIR__ . '/../includes/bootstrap.php';

includeHeader(rqText('help'), ['public.css']);
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1><?= rqText('help') ?></h1>
            <p class="lead"><?= rqText('help_description') ?></p>
        </div>

        <div class="help-search">
            <input type="text" placeholder="<?= rqText('search_help') ?>" class="search-input">
            <button class="btn btn-primary"><?= rqText('search') ?></button>
        </div>

        <div class="help-categories">
            <div class="category-card">
                <h3><?= rqText('tickets_help') ?></h3>
                <ul>
                    <li><a href="#how-to-buy"><?= rqText('how_to_buy_tickets') ?></a></li>
                    <li><a href="#ticket-types"><?= rqText('ticket_types_info') ?></a></li>
                    <li><a href="#discounts"><?= rqText('available_discounts') ?></a></li>
                    <li><a href="#refunds"><?= rqText('refund_policy') ?></a></li>
                </ul>
            </div>

            <div class="category-card">
                <h3><?= rqText('visit_help') ?></h3>
                <ul>
                    <li><a href="#location"><?= rqText('how_to_get_there') ?></a></li>
                    <li><a href="#schedules"><?= rqText('opening_hours') ?></a></li>
                    <li><a href="#what-to-bring"><?= rqText('what_to_bring') ?></a></li>
                    <li><a href="#accessibility"><?= rqText('accessibility_info') ?></a></li>
                </ul>
            </div>

            <div class="category-card">
                <h3><?= rqText('services_help') ?></h3>
                <ul>
                    <li><a href="#guides"><?= rqText('guide_booking') ?></a></li>
                    <li><a href="#transport"><?= rqText('transport_options') ?></a></li>
                    <li><a href="#food"><?= rqText('food_services') ?></a></li>
                    <li><a href="#workshops"><?= rqText('workshop_registration') ?></a></li>
                </ul>
            </div>

            <div class="category-card">
                <h3><?= rqText('technical_help') ?></h3>
                <ul>
                    <li><a href="#account"><?= rqText('account_issues') ?></a></li>
                    <li><a href="#payment"><?= rqText('payment_problems') ?></a></li>
                    <li><a href="#website"><?= rqText('website_issues') ?></a></li>
                    <li><a href="#contact"><?= rqText('contact_support') ?></a></li>
                </ul>
            </div>
        </div>

        <div class="faq-section">
            <h2><?= rqText('frequently_asked_questions') ?></h2>
            
            <div class="faq-item">
                <h4><?= rqText('faq_1_question') ?></h4>
                <p><?= rqText('faq_1_answer') ?></p>
            </div>

            <div class="faq-item">
                <h4><?= rqText('faq_2_question') ?></h4>
                <p><?= rqText('faq_2_answer') ?></p>
            </div>

            <div class="faq-item">
                <h4><?= rqText('faq_3_question') ?></h4>
                <p><?= rqText('faq_3_answer') ?></p>
            </div>

            <div class="faq-item">
                <h4><?= rqText('faq_4_question') ?></h4>
                <p><?= rqText('faq_4_answer') ?></p>
            </div>
        </div>

        <div class="contact-support">
            <h2><?= rqText('still_need_help') ?></h2>
            <p><?= rqText('contact_support_description') ?></p>
            
            <div class="contact-options">
                <div class="contact-option">
                    <h4><?= rqText('email_support') ?></h4>
                    <p>ayuda@raqchi.pe</p>
                    <p><?= rqText('email_response_time') ?></p>
                </div>
                
                <div class="contact-option">
                    <h4><?= rqText('phone_support') ?></h4>
                    <p>+51 984 123 456</p>
                    <p><?= rqText('phone_hours') ?></p>
                </div>
                
                <div class="contact-option">
                    <h4><?= rqText('whatsapp_support') ?></h4>
                    <p>+51 984 123 456</p>
                    <p><?= rqText('whatsapp_hours') ?></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php includeFooter(); ?>
