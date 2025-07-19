<?php
include_once '../includes/bootstrap.php';

get_header();

// Aquí puedes agregar la lógica para obtener las imágenes de la galería
$gallery_images = [
    ['url' => 'https://via.placeholder.com/400x300.png?text=Raqchi+1', 'alt' => 'Vista de Raqchi 1'],
    ['url' => 'https://via.placeholder.com/400x300.png?text=Raqchi+2', 'alt' => 'Vista de Raqchi 2'],
    ['url' => 'https://via.placeholder.com/400x300.png?text=Raqchi+3', 'alt' => 'Vista de Raqchi 3'],
    ['url' => 'https://via.placeholder.com/400x300.png?text=Raqchi+4', 'alt' => 'Vista de Raqchi 4'],
    ['url' => 'https://via.placeholder.com/400x300.png?text=Raqchi+5', 'alt' => 'Vista de Raqchi 5'],
    ['url' => 'https://via.placeholder.com/400x300.png?text=Raqchi+6', 'alt' => 'Vista de Raqchi 6'],
];
?>

<div class="main-content">
    <div class="container">
        <div class="page-header">
            <h1><?php echo translate('gallery_title'); ?></h1>
            <p class="lead"><?php echo translate('gallery_subtitle'); ?></p>
        </div>

        <div class="gallery-grid">
            <?php foreach ($gallery_images as $image) : ?>
                <div class="gallery-item">
                    <a href="<?php echo $image['url']; ?>" data-lightbox="raqchi-gallery" data-title="<?php echo $image['alt']; ?>">
                        <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>">
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
get_footer();
?>
