<?php
/**
 * Página de reseñas mejorada - Portal Digital de Raqchi
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
require_once __DIR__ . '/../includes/bootstrap.php';

// Generar token CSRF para el formulario
generateCSRFToken();

includeHeader(rqText('reviews'), ['public.css', 'reviews.css']);
?>

<main class="main-content">
    <div class="container">
        <!-- Header de la página -->
        <div class="page-header">
            <div class="page-header-content">
                <h1><?= rqText('reviews') ?></h1>
                <p class="lead"><?= rqText('reviews_description') ?></p>
            </div>
            <div class="page-header-image">
                <img src="<?= SITE_URL ?>/assets/images/raqchi-temple.jpg" alt="Templo de Raqchi" class="header-img">
            </div>
        </div>

        <!-- Estadísticas de reseñas -->
        <div class="reviews-stats-section">
            <div class="stats-container">
                <div class="stat-card main-stat">
                    <div class="stat-content">
                        <div class="stat-number" id="averageRating">4.8</div>
                        <div class="stat-label"><?= rqText('average_rating') ?></div>
                        <div class="stars-display" id="starsDisplay">
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star filled">★</span>
                            <span class="star half">★</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalReviews">256</div>
                    <div class="stat-label"><?= rqText('total_reviews') ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="satisfactionRate">98%</div>
                    <div class="stat-label"><?= rqText('satisfaction_rate') ?></div>
                </div>
            </div>
        </div>

        <!-- Controles de filtrado y ordenación -->
        <div class="reviews-controls">
            <div class="controls-left">
                <div class="control-group">
                    <label for="sortSelect"><?= rqText('sort_by') ?>:</label>
                    <select id="sortSelect" class="control-select">
                        <option value="recent"><?= rqText('most_recent') ?></option>
                        <option value="rating_high"><?= rqText('highest_rated') ?></option>
                        <option value="rating_low"><?= rqText('lowest_rated') ?></option>
                        <option value="helpful"><?= rqText('most_helpful') ?></option>
                    </select>
                </div>
                <div class="control-group">
                    <label for="ratingFilter"><?= rqText('filter_by_rating') ?>:</label>
                    <select id="ratingFilter" class="control-select">
                        <option value="0"><?= rqText('all_ratings') ?></option>
                        <option value="5">5 ★</option>
                        <option value="4">4 ★</option>
                        <option value="3">3 ★</option>
                        <option value="2">2 ★</option>
                        <option value="1">1 ★</option>
                    </select>
                </div>
            </div>
            <div class="controls-right">
                <button class="btn btn-primary" onclick="scrollToForm()">
                    <i class="fas fa-pen"></i> <?= rqText('leave_review') ?>
                </button>
            </div>
        </div>

        <!-- Lista de reseñas -->
        <div class="reviews-section">
            <div id="reviewsContainer" class="reviews-grid">
                <!-- Las reseñas se cargarán dinámicamente aquí -->
            </div>
            
            <div class="loading-indicator" id="loadingIndicator" style="display: none;">
                <div class="spinner"></div>
                <p>Cargando reseñas...</p>
            </div>
            
            <div class="load-more-container" id="loadMoreContainer" style="display: none;">
                <button class="btn btn-outline" id="loadMoreBtn"><?= rqText('load_more_reviews') ?></button>
            </div>
        </div>

        <!-- Formulario para nueva reseña -->
        <section class="review-form-section" id="reviewFormSection">
            <div class="container">
                <h2 class="section-title"><?= rqText('leave_review') ?></h2>
                
                <div class="form-container">
                    <form id="reviewForm" class="review-form">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="reviewer_name"><?= rqText('your_name') ?> <span class="required">*</span></label>
                                <input type="text" id="reviewer_name" name="reviewer_name" required>
                            </div>
                            <div class="form-group">
                                <label for="reviewer_email"><?= rqText('your_email') ?> <span class="required">*</span></label>
                                <input type="email" id="reviewer_email" name="reviewer_email" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="location"><?= rqText('your_location') ?></label>
                                <input type="text" id="location" name="location" placeholder="<?= rqText('city_country') ?>">
                            </div>
                            <div class="form-group">
                                <label for="visit_date"><?= rqText('visit_date') ?></label>
                                <input type="date" id="visit_date" name="visit_date">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rating"><?= rqText('your_rating') ?> <span class="required">*</span></label>
                            <div class="rating-input" id="ratingInput">
                                <span class="star" data-rating="1">★</span>
                                <span class="star" data-rating="2">★</span>
                                <span class="star" data-rating="3">★</span>
                                <span class="star" data-rating="4">★</span>
                                <span class="star" data-rating="5">★</span>
                            </div>
                            <input type="hidden" id="rating" name="rating" required>
                        </div>

                        <div class="form-group">
                            <label for="review_content"><?= rqText('your_review') ?> <span class="required">*</span></label>
                            <textarea id="review_content" name="review_content" rows="5" required 
                                      placeholder="<?= rqText('share_experience') ?>"></textarea>
                        </div>

                        <input type="hidden" name="action" value="submit">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-submit">
                                <i class="fas fa-paper-plane"></i> <?= rqText('submit_review') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <link rel="stylesheet" href="<?= SITE_URL ?>/css/reviews.css">
    <script>
        // Variables globales
        let allReviews = [];
        let displayedReviews = [];
        let reviewsPerPage = 6;
        let currentPage = 1;
        let currentSort = 'recent';
        let currentRatingFilter = 0;

        // Inicializar cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            loadReviews();
            setupEventListeners();
        });

        // Configurar event listeners
        function setupEventListeners() {
            // Formulario de reseña
            document.getElementById('reviewForm').addEventListener('submit', handleFormSubmit);
            
            // Estrellas de rating
            setupRatingInput();
            
            // Controles de filtro y ordenación
            document.getElementById('sortSelect').addEventListener('change', handleSortChange);
            document.getElementById('ratingFilter').addEventListener('change', handleRatingFilterChange);
            
            // Botón cargar más
            document.getElementById('loadMoreBtn').addEventListener('click', loadMoreReviews);
        }

        // Cargar reseñas desde la API
        async function loadReviews() {
            try {
                showLoading(true);
                const response = await fetch('<?= SITE_URL ?>/api/reviews.php');
                const data = await response.json();
                
                if (data.success) {
                    allReviews = data.reviews;
                    updateStats();
                    applyFiltersAndSort();
                } else {
                    console.error('Error loading reviews:', data.message);
                }
            } catch (error) {
                console.error('Error loading reviews:', error);
            } finally {
                showLoading(false);
            }
        }

        // Actualizar estadísticas
        function updateStats() {
            if (allReviews.length === 0) return;

            const totalReviews = allReviews.length;
            const ratings = allReviews.map(r => r.rating);
            const avgRating = (ratings.reduce((a, b) => a + b, 0) / totalReviews).toFixed(1);
            
            // Calcular distribución de estrellas
            const distribution = [0, 0, 0, 0, 0];
            ratings.forEach(rating => distribution[rating - 1]++);

            // Actualizar DOM
            document.getElementById('totalReviews').textContent = totalReviews;
            document.getElementById('averageRating').textContent = avgRating;
            document.getElementById('starsDisplay').innerHTML = generateStarsHTML(parseFloat(avgRating));

            // Actualizar barras de distribución
            for (let i = 1; i <= 5; i++) {
                const percentage = totalReviews > 0 ? (distribution[i - 1] / totalReviews * 100) : 0;
                const bar = document.querySelector(`[data-rating="${i}"] .progress-bar`);
                const count = document.querySelector(`[data-rating="${i}"] .rating-count`);
                if (bar) bar.style.width = `${percentage}%`;
                if (count) count.textContent = distribution[i - 1];
            }
        }

        // Aplicar filtros y ordenación
        function applyFiltersAndSort() {
            let filteredReviews = [...allReviews];

            // Aplicar filtro de rating
            if (currentRatingFilter > 0) {
                filteredReviews = filteredReviews.filter(review => review.rating === currentRatingFilter);
            }

            // Aplicar ordenación
            switch (currentSort) {
                case 'recent':
                    filteredReviews.sort((a, b) => new Date(b.date) - new Date(a.date));
                    break;
                case 'rating_high':
                    filteredReviews.sort((a, b) => b.rating - a.rating);
                    break;
                case 'rating_low':
                    filteredReviews.sort((a, b) => a.rating - b.rating);
                    break;
                case 'helpful':
                    filteredReviews.sort((a, b) => (b.helpful_yes || 0) - (a.helpful_yes || 0));
                    break;
            }

            displayedReviews = filteredReviews;
            currentPage = 1;
            renderReviews();
        }

        // Renderizar reseñas
        function renderReviews() {
            const container = document.getElementById('reviewsContainer');
            const reviewsToShow = displayedReviews.slice(0, currentPage * reviewsPerPage);
            
            container.innerHTML = reviewsToShow.map(review => generateReviewHTML(review)).join('');
            
            // Mostrar/ocultar botón "Cargar más"
            const loadMoreContainer = document.getElementById('loadMoreContainer');
            if (reviewsToShow.length < displayedReviews.length) {
                loadMoreContainer.style.display = 'block';
            } else {
                loadMoreContainer.style.display = 'none';
            }
        }

        // Generar HTML para una reseña
        function generateReviewHTML(review) {
            const starsHTML = generateStarsHTML(review.rating);
            const date = new Date(review.created_at).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            return `
                <div class="review-card" data-review-id="${review.id}">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">
                                ${review.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <h4>${escapeHtml(review.name)}</h4>
                                ${review.location ? `<span class="reviewer-location">${escapeHtml(review.location)}</span>` : ''}
                            </div>
                        </div>
                        <div class="review-rating">${starsHTML}</div>
                    </div>
                    <div class="review-content">
                        <p>${escapeHtml(review.content)}</p>
                    </div>
                    <div class="review-footer">
                        <span class="review-date">${date}</span>
                        <button class="helpful-btn ${review.user_voted ? 'voted' : ''}" 
                                onclick="markHelpful('${review.id}')" 
                                ${review.user_voted ? 'disabled' : ''}>
                            <i class="fas fa-thumbs-up"></i> 
                            Útil (${review.helpful_yes || 0})
                        </button>
                    </div>
                </div>
            `;
        }

        // Generar HTML de estrellas
        function generateStarsHTML(rating) {
            let starsHTML = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    starsHTML += '<span class="star filled">★</span>';
                } else if (i - 0.5 <= rating) {
                    starsHTML += '<span class="star half-filled">★</span>';
                } else {
                    starsHTML += '<span class="star">★</span>';
                }
            }
            return starsHTML;
        }

        // Configurar input de rating
        function setupRatingInput() {
            const stars = document.querySelectorAll('#ratingInput .star');
            const ratingInput = document.getElementById('rating');
            
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = parseInt(this.dataset.rating);
                    ratingInput.value = rating;
                    
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.classList.add('selected');
                        } else {
                            s.classList.remove('selected');
                        }
                    });
                });
                
                star.addEventListener('mouseover', function() {
                    const rating = parseInt(this.dataset.rating);
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.classList.add('hover');
                        } else {
                            s.classList.remove('hover');
                        }
                    });
                });
                
                star.addEventListener('mouseout', function() {
                    stars.forEach(s => s.classList.remove('hover'));
                });
            });
        }

        // Manejar envío del formulario
        async function handleFormSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            formData.set('action', 'submit');
            const csrfToken = form.querySelector('input[name=csrf_token]').value;
            formData.set('csrf_token', csrfToken);

            const submitBtn = form.querySelector('.btn-submit');
            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + '<?= rqText('submit_review') ?>';

                const response = await fetch('<?= SITE_URL ?>/api/submit-review.php', {
                    method: 'POST',
                    body: formData
                });
                // Debug: log raw response text to inspect server output
                const rawText = await response.text();
                console.log('Raw response text:', rawText);
                let data;
                try {
                    data = JSON.parse(rawText);
                } catch (err) {
                    console.error('Invalid JSON:', err);
                    alert('Respuesta inválida del servidor');
                    return;
                }

                if (data.success) {
                    alert('¡Gracias por tu reseña! ' + (data.message || ''));
                    form.reset();
                    document.getElementById('rating').value = '';
                    document.querySelectorAll('#ratingInput .star').forEach(s => s.classList.remove('selected'));
                    loadReviews();
                } else {
                    let msg = '';
                    if (Array.isArray(data.errors) && data.errors.length) {
                        msg = 'Errores:\n' + data.errors.join('\n');
                    } else {
                        msg = data.message || 'Ocurrió un error';
                    }
                    alert(msg);
                }
            } catch (error) {
                console.error('Error submitting review:', error);
                alert('Error al enviar la reseña. Por favor intenta de nuevo.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> <?= rqText('submit_review') ?>';
            }
        }

        // Marcar como útil
        async function markHelpful(reviewId) {
            // Parse and validate reviewId
            const id = parseInt(reviewId, 10);
            console.log('markHelpful called with reviewId:', reviewId, 'parsed id:', id);
            if (!id) {
                alert('ID de reseña inválido');
                return;
            }
            try {
                // Prepare URL-encoded parameters
                const formData = new URLSearchParams();
                formData.append('action', 'helpful');
                formData.append('review_id', id);
                formData.append('helpful', 'true');
                formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
                const response = await fetch('<?= SITE_URL ?>/api/reviews.php', {
                    method: 'POST',
                    body: formData
                });
                const rawText = await response.text();
                console.log('markHelpful raw response text:', rawText);
                let data;
                try {
                    data = JSON.parse(rawText);
                } catch (e) {
                    console.error('markHelpful invalid JSON:', e);
                    alert('Respuesta inválida al votar');
                    return;
                }

                if (data.success) {
                    // Actualizar el contador del botón específico
                    const button = document.querySelector(`button[onclick="markHelpful('${reviewId}')"]`);
                    if (button && data.helpful_count) {
                        const newCount = data.helpful_count.yes || 0;
                        button.innerHTML = `<i class="fas fa-thumbs-up"></i> Útil (${newCount})`;
                        button.disabled = true;
                        button.classList.add('voted');
                    }
                    // También recargar las reseñas para asegurar consistencia
                    setTimeout(() => loadReviews(), 500);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error marking helpful:', error);
            }
        }

        // Event handlers
        function handleSortChange(e) {
            currentSort = e.target.value;
            applyFiltersAndSort();
        }

        function handleRatingFilterChange(e) {
            currentRatingFilter = parseInt(e.target.value);
            applyFiltersAndSort();
        }

        function loadMoreReviews() {
            currentPage++;
            renderReviews();
        }

        function scrollToForm() {
            document.getElementById('reviewFormSection').scrollIntoView({ 
                behavior: 'smooth' 
            });
        }

        function showLoading(show) {
            document.getElementById('loadingIndicator').style.display = show ? 'block' : 'none';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</main>

<?php includeFooter(); ?>
