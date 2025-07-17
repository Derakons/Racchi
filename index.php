<?php
/**
 * Página de inicio del Portal Digital de Raqchi
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Obtener datos para la página de inicio
$supabase = getSupabaseClient();

// Obtener testimonios destacados
$testimonials = $supabase->select('reseñas', '*', ['estado' => 'aprobado'], 'calificacion.desc', 4);

// Obtener servicios destacados
$services = $supabase->select('servicios_generales', '*', ['destacado' => true]);

// Procesar filtro inteligente si se envió
$suggestedCombos = [];
if ($_POST && isset($_POST['filter_visitor_type'])) {
    $visitorType = sanitizeInput($_POST['filter_visitor_type']);
    $needGuide = sanitizeInput($_POST['filter_need_guide']) === 'yes';
    $duration = sanitizeInput($_POST['filter_duration']) ?? null;
    $budget = sanitizeInput($_POST['filter_budget']) ?? null;
    
    // Lógica para generar combos sugeridos
    $basePrice = 0;
    switch ($visitorType) {
        case 'national':
            $basePrice = TICKET_ADULT_NATIONAL;
            break;
        case 'foreign':
            $basePrice = TICKET_ADULT_FOREIGN;
            break;
        case 'student':
            $basePrice = TICKET_STUDENT;
            break;
    }
    
    $totalPrice = $basePrice + ($needGuide ? GUIDE_SERVICE_PRICE : 0);
    
    $suggestedCombos = [
        [
            'title' => rqText($visitorType) . ($needGuide ? ' + ' . rqText('tourist_guides') : ''),
            'description' => $needGuide ? 'Entrada con guía especializado' : 'Solo entrada al complejo',
            'price' => $totalPrice,
            'duration' => $duration ?? '2-3 horas',
            'includes' => [
                'Entrada al complejo arqueológico',
                $needGuide ? 'Guía turístico especializado' : null,
                'Acceso a todas las áreas',
                'Mapa del sitio'
            ]
        ]
    ];
}

includeHeader(rqText('home'), ['home.css'], ['home.js']);
?>

<main class="main-content">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-background">
            <video autoplay muted loop class="hero-video" id="heroVideo">
                <source src="<?php echo SITE_URL; ?>/assets/images/raqchi-hero.mp4" type="video/mp4">
            </video>
            <div class="hero-overlay"></div>
        </div>
        
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title"><?php echo rqText('hero_title'); ?></h1>
                <p class="hero-subtitle"><?php echo rqText('hero_subtitle'); ?></p>
                
                <div class="hero-actions">
                    <a href="/compra.html" class="btn btn-primary btn-large">
                        <?php echo rqText('buy_ticket_button'); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Filtro Inteligente -->
    <section class="smart-filter-section">
        <div class="container">
            <div class="filter-card">
                <h2><?php echo rqText('visitor_type_question'); ?></h2>
                
                <form method="POST" id="smartFilterForm" class="smart-filter-form">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="filter-row">
                        <div class="filter-group">
                            <label><?php echo rqText('visitor_type_question'); ?></label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="filter_visitor_type" value="national" required>
                                    <span><?php echo rqText('national'); ?></span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="filter_visitor_type" value="foreign" required>
                                    <span><?php echo rqText('foreign'); ?></span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="filter_visitor_type" value="student" required>
                                    <span><?php echo rqText('student'); ?></span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label><?php echo rqText('need_guide_question'); ?></label>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="filter_need_guide" value="yes" required>
                                    <span><?php echo rqText('yes'); ?></span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="filter_need_guide" value="no" required>
                                    <span><?php echo rqText('no'); ?></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="filterDuration"><?php echo rqText('estimated_duration'); ?></label>
                            <select name="filter_duration" id="filterDuration">
                                <option value="1-2"><?php echo rqText('duration_1_2_hours'); ?></option>
                                <option value="2-3" selected><?php echo rqText('duration_2_3_hours'); ?></option>
                                <option value="3-4"><?php echo rqText('duration_3_4_hours'); ?></option>
                                <option value="todo-dia"><?php echo rqText('duration_full_day'); ?></option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="filterBudget"><?php echo rqText('budget'); ?></label>
                            <input type="range" name="filter_budget" id="filterBudget" 
                                   min="15" max="100" value="50" 
                                   oninput="updateBudgetDisplay(this.value)">
                            <div class="budget-display">
                                <span>S/ </span><span id="budgetValue">50</span>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?php echo rqText('search'); ?>
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Combos Sugeridos -->
    <?php if (!empty($suggestedCombos)): ?>
    <section class="suggested-combos-section">
        <div class="container">
            <h2><?php echo rqText('suggested_combos'); ?></h2>
            
            <div class="combos-grid">
                <?php foreach ($suggestedCombos as $combo): ?>
                <div class="combo-card">
                    <div class="combo-header">
                        <h3><?php echo htmlspecialchars($combo['title']); ?></h3>
                        <div class="combo-price">
                            <?php echo formatPrice($combo['price']); ?>
                        </div>
                    </div>
                    
                    <div class="combo-content">
                        <p class="combo-description">
                            <?php echo htmlspecialchars($combo['description']); ?>
                        </p>
                        
                        <div class="combo-duration">
                            <i class="icon-clock"></i>
                            <span><?php echo htmlspecialchars($combo['duration']); ?></span>
                        </div>
                        
                        <ul class="combo-includes">
                            <?php foreach ($combo['includes'] as $include): ?>
                                <?php if ($include): ?>
                                <li><?php echo htmlspecialchars($include); ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="combo-actions">
                        <a href="/compra.html?combo=<?php echo urlencode($combo['title']); ?>" 
                           class="btn btn-primary">
                            <?php echo rqText('buy_ticket_button'); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Sección de Estadísticas -->
    <section class="statistics-section">
        <div class="container">
            <div class="statistics-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="icon-visitors"></i>
                    </div>
                    <div class="stat-number" data-count="50000">0</div>
                    <div class="stat-label"><?php echo rqText('annual_visitors'); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="icon-history"></i>
                    </div>
                    <div class="stat-number" data-count="600">0</div>
                    <div class="stat-label"><?php echo rqText('years_history'); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="icon-area"></i>
                    </div>
                    <div class="stat-number" data-count="210">0</div>
                    <div class="stat-label"><?php echo rqText('hectares_area'); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="icon-satisfaction"></i>
                    </div>
                    <div class="stat-number" data-count="98">0</div>
                    <div class="stat-label"><?php echo rqText('satisfaction_rate'); ?>%</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Experiencias Destacadas -->
    <section class="experiences-section">
        <div class="container">
            <h2><?php echo rqText('unique_experience'); ?></h2>
            
            <div class="experiences-grid">
                <div class="experience-card">
                    <div class="experience-image">
                        <img src="<?php echo SITE_URL; ?>/assets/images/cultural-immersion.jpg" 
                             alt="<?php echo rqText('cultural_immersion'); ?>"
                             data-src="<?php echo SITE_URL; ?>/assets/images/cultural-immersion.jpg"
                             class="lazy">
                    </div>
                    <div class="experience-content">
                        <h3><?php echo rqText('cultural_immersion'); ?></h3>
                        <p>Sumérgete en la rica cultura andina y descubre las tradiciones ancestrales que aún perduran en Raqchi.</p>
                        <a href="/servicios.html#cultura" class="btn btn-outline">
                            <?php echo rqText('learn_more'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="experience-card">
                    <div class="experience-image">
                        <img src="<?php echo SITE_URL; ?>/assets/images/archaeological-tour.jpg" 
                             alt="<?php echo rqText('archaeological_tour'); ?>"
                             data-src="<?php echo SITE_URL; ?>/assets/images/archaeological-tour.jpg"
                             class="lazy">
                    </div>
                    <div class="experience-content">
                        <h3><?php echo rqText('archaeological_tour'); ?></h3>
                        <p>Explora las impresionantes estructuras incas y comprende la ingeniería avanzada de esta civilización.</p>
                        <a href="/servicios.html#arqueologia" class="btn btn-outline">
                            <?php echo rqText('learn_more'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="experience-card">
                    <div class="experience-image">
                        <img src="<?php echo SITE_URL; ?>/assets/images/traditional-crafts.jpg" 
                             alt="<?php echo rqText('traditional_crafts'); ?>"
                             data-src="<?php echo SITE_URL; ?>/assets/images/traditional-crafts.jpg"
                             class="lazy">
                    </div>
                    <div class="experience-content">
                        <h3><?php echo rqText('traditional_crafts'); ?></h3>
                        <p>Aprende técnicas ancestrales de cerámica y textilería de manos de artesanos locales.</p>
                        <a href="/servicios.html#talleres" class="btn btn-outline">
                            <?php echo rqText('learn_more'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonios -->
    <section class="testimonials-section">
        <div class="container">
            <h2><?php echo rqText('testimonials'); ?></h2>
            
            <div class="testimonials-carousel" id="testimonialsCarousel">
                <?php if ($testimonials['success'] && !empty($testimonials['data'])): ?>
                    <?php foreach ($testimonials['data'] as $index => $testimonial): ?>
                    <div class="testimonial-card <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="testimonial-content">
                            <div class="testimonial-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i <= $testimonial['calificacion'] ? 'filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            
                            <h3 class="testimonial-title">
                                <?php echo htmlspecialchars($testimonial['titulo']); ?>
                            </h3>
                            
                            <p class="testimonial-text">
                                "<?php echo htmlspecialchars($testimonial['comentario']); ?>"
                            </p>
                            
                            <div class="testimonial-author">
                                <div class="author-info">
                                    <strong><?php echo htmlspecialchars($testimonial['nombre_visitante'] ?? rqText('example_user')); ?></strong>
                                    <span class="author-origin"><?php echo htmlspecialchars($testimonial['origen'] ?? ''); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="testimonial-card active">
                        <div class="testimonial-content">
                            <div class="testimonial-rating">
                                <span class="star filled">★</span>
                                <span class="star filled">★</span>
                                <span class="star filled">★</span>
                                <span class="star filled">★</span>
                                <span class="star filled">★</span>
                            </div>
                            
                            <h3 class="testimonial-title">
                                <?php echo rqText('default_testimonial_title'); ?>
                            </h3>
                            
                            <p class="testimonial-text">
                                "<?php echo rqText('default_testimonial_text'); ?>"
                            </p>
                            
                            <div class="testimonial-author">
                                <div class="author-info">
                                    <strong><?php echo rqText('example_user'); ?></strong>
                                    <span class="author-origin">Cusco, Perú</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="carousel-controls">
                <button onclick="previousTestimonial()" class="carousel-btn prev">‹</button>
                <button onclick="nextTestimonial()" class="carousel-btn next">›</button>
            </div>
            
            <div class="testimonials-actions">
                <a href="/reseñas.html" class="btn btn-outline">
                    <?php echo rqText('see_all_experiences'); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Servicios Destacados -->
    <section class="services-preview-section">
        <div class="container">
            <h2><?php echo rqText('services'); ?></h2>
            
            <div class="services-grid">
                <?php if ($services['success'] && !empty($services['data'])): ?>
                    <?php foreach (array_slice($services['data'], 0, 3) as $service): ?>
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="icon-<?php echo strtolower(str_replace(' ', '-', $service['categoria'])); ?>"></i>
                        </div>
                        <div class="service-content">
                            <h3><?php echo htmlspecialchars($service['titulo']); ?></h3>
                            <p><?php echo htmlspecialchars($service['descripcion_corta']); ?></p>
                            <div class="service-price">
                                <?php echo formatPrice($service['precio_desde']); ?>
                            </div>
                        </div>
                        <div class="service-actions">
                            <a href="/servicios.html#<?php echo $service['id']; ?>" class="btn btn-outline">
                                <?php echo rqText('learn_more'); ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Servicios por defecto si no hay datos -->
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="icon-guide"></i>
                        </div>
                        <div class="service-content">
                            <h3><?php echo rqText('tourist_guides'); ?></h3>
                            <p><?php echo rqText('tourist_guides_description'); ?></p>
                            <div class="service-price">
                                <?php echo formatPrice(GUIDE_SERVICE_PRICE); ?>
                            </div>
                        </div>
                        <div class="service-actions">
                            <a href="/servicios.html#guias" class="btn btn-outline">
                                <?php echo rqText('learn_more'); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="icon-food"></i>
                        </div>
                        <div class="service-content">
                            <h3><?php echo rqText('gastronomy'); ?></h3>
                            <p><?php echo rqText('gastronomy_description'); ?></p>
                            <div class="service-price">
                                Desde <?php echo formatPrice(25); ?>
                            </div>
                        </div>
                        <div class="service-actions">
                            <a href="/servicios.html#gastronomia" class="btn btn-outline">
                                <?php echo rqText('learn_more'); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="icon-craft"></i>
                        </div>
                        <div class="service-content">
                            <h3><?php echo rqText('workshops'); ?></h3>
                            <p><?php echo rqText('workshops_description'); ?></p>
                            <div class="service-price">
                                Desde <?php echo formatPrice(35); ?>
                            </div>
                        </div>
                        <div class="service-actions">
                            <a href="/servicios.html#talleres" class="btn btn-outline">
                                <?php echo rqText('learn_more'); ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="services-actions">
                <a href="/servicios.html" class="btn btn-primary">
                    Ver todos los servicios
                </a>
            </div>
        </div>
    </section>
    
    <!-- Información importante -->
    <section class="info-section">
        <div class="container">
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="icon-clock"></i>
                    </div>
                    <h3><?php echo rqText('hours_operation'); ?></h3>
                    <p><?php echo rqText('daily_hours'); ?></p>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">
                        <i class="icon-location"></i>
                    </div>
                    <h3><?php echo rqText('location'); ?></h3>
                    <p><?php echo rqText('location_address'); ?></p>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">
                        <i class="icon-info"></i>
                    </div>
                    <h3><?php echo rqText('important_notes'); ?></h3>
                    <p><?php echo rqText('advance_booking'); ?></p>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- WhatsApp Button -->
<div class="whatsapp-float">
    <a href="https://wa.me/51984123456?text=<?php echo urlencode('Hola, me interesa visitar Raqchi. ¿Podrían darme más información?'); ?>" 
       target="_blank" 
       class="whatsapp-btn" 
       title="<?php echo rqText('contact_whatsapp'); ?>">
        <i class="icon-whatsapp"></i>
        <span class="whatsapp-text"><?php echo rqText('contact_us'); ?></span>
    </a>
</div>

<!-- Call to Action Final -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2><?php echo rqText('ready_to_discover'); ?></h2>
            <p><?php echo rqText('cta_description'); ?></p>
            <div class="cta-actions">
                <a href="/compra.html" class="btn btn-primary btn-large">
                    <?php echo rqText('buy_ticket_button'); ?>
                </a>
                <a href="/servicios.html" class="btn btn-outline btn-large">
                    <?php echo rqText('explore_services'); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<?php includeFooter(['home.js']); ?>
