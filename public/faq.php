<?php
include_once '../includes/bootstrap.php';

get_header();
?>

<div class="main-content">
    <div class="container">
        <div class="page-header">
            <h1><?php echo translate('faq_title'); ?></h1>
            <p class="lead"><?php echo translate('faq_subtitle'); ?></p>
        </div>

        <div class="faq-section">
            <div class="faq-item">
                <h4><?php echo translate('faq_question_1'); ?></h4>
                <p><?php echo translate('faq_answer_1'); ?></p>
            </div>
            <div class="faq-item">
                <h4><?php echo translate('faq_question_2'); ?></h4>
                <p><?php echo translate('faq_answer_2'); ?></p>
            </div>
            <div class="faq-item">
                <h4><?php echo translate('faq_question_3'); ?></h4>
                <p><?php echo translate('faq_answer_3'); ?></p>
            </div>
            <div class="faq-item">
                <h4><?php echo translate('faq_question_4'); ?></h4>
                <p><?php echo translate('faq_answer_4'); ?></p>
            </div>
        </div>

        <div class="contact-support">
            <h2><?php echo translate('faq_contact_title'); ?></h2>
            <p><?php echo translate('faq_contact_subtitle'); ?></p>
            <a href="/public/help.php" class="btn btn-primary"><?php echo translate('contact_us'); ?></a>
        </div>
    </div>
</div>

<?php
get_footer();
?>
