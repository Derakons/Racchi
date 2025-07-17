/**
 * JavaScript específico para la página de inicio
 * Maneja el carrusel de testimonios, filtro inteligente y otras funcionalidades
 */

// Variables específicas de la página de inicio
let currentTestimonialIndex = 0;
let testimonials = [];
let autoSlideInterval;

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    initializeHomePage();
});

/**
 * Inicialización de la página de inicio
 */
function initializeHomePage() {
    initializeTestimonialsCarousel();
    initializeSmartFilter();
    initializeBudgetSlider();
    initializeHeroVideo();
    initializeScrollAnimations();
    
    // Nuevas funciones
    initializeStatisticsAnimation();
    enhanceScrollAnimations();
    initializeParallaxEffect();
    enhanceSmartFilter();
    restorePageState();
}

/**
 * Carrusel de testimonios
 */
function initializeTestimonialsCarousel() {
    const carousel = document.getElementById('testimonialsCarousel');
    if (!carousel) return;
    
    testimonials = carousel.querySelectorAll('.testimonial-card');
    
    if (testimonials.length > 1) {
        // Iniciar auto-slide
        startAutoSlide();
        
        // Pausar auto-slide al hacer hover
        carousel.addEventListener('mouseenter', stopAutoSlide);
        carousel.addEventListener('mouseleave', startAutoSlide);
        
        // Controles de navegación
        const prevBtn = document.querySelector('.carousel-btn.prev');
        const nextBtn = document.querySelector('.carousel-btn.next');
        
        if (prevBtn) prevBtn.addEventListener('click', previousTestimonial);
        if (nextBtn) nextBtn.addEventListener('click', nextTestimonial);
        
        // Navegación con teclado
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') previousTestimonial();
            if (e.key === 'ArrowRight') nextTestimonial();
        });
        
        // Touch/swipe para móviles
        initializeTouchNavigation(carousel);
    }
}

function showTestimonial(index) {
    if (index < 0) index = testimonials.length - 1;
    if (index >= testimonials.length) index = 0;
    
    // Ocultar todos los testimonios
    testimonials.forEach((testimonial, i) => {
        testimonial.classList.remove('active');
        if (i === index) {
            testimonial.classList.add('active');
        }
    });
    
    currentTestimonialIndex = index;
}

function nextTestimonial() {
    showTestimonial(currentTestimonialIndex + 1);
    resetAutoSlide();
}

function previousTestimonial() {
    showTestimonial(currentTestimonialIndex - 1);
    resetAutoSlide();
}

function startAutoSlide() {
    if (testimonials.length > 1) {
        autoSlideInterval = setInterval(() => {
            nextTestimonial();
        }, 5000); // Cambiar cada 5 segundos
    }
}

function stopAutoSlide() {
    if (autoSlideInterval) {
        clearInterval(autoSlideInterval);
        autoSlideInterval = null;
    }
}

function resetAutoSlide() {
    stopAutoSlide();
    startAutoSlide();
}

/**
 * Navegación táctil para el carrusel
 */
function initializeTouchNavigation(carousel) {
    let startX = 0;
    let endX = 0;
    
    carousel.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
    });
    
    carousel.addEventListener('touchend', function(e) {
        endX = e.changedTouches[0].clientX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = startX - endX;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                nextTestimonial();
            } else {
                previousTestimonial();
            }
        }
    }
}

/**
 * Filtro inteligente
 */
function initializeSmartFilter() {
    const form = document.getElementById('smartFilterForm');
    if (!form) return;
    
    form.addEventListener('submit', handleSmartFilter);
    
    // Actualizar precio dinámicamente cuando cambian las opciones
    const visitorTypeInputs = form.querySelectorAll('input[name="filter_visitor_type"]');
    const guideInputs = form.querySelectorAll('input[name="filter_need_guide"]');
    
    visitorTypeInputs.forEach(input => {
        input.addEventListener('change', updatePricePreview);
    });
    
    guideInputs.forEach(input => {
        input.addEventListener('change', updatePricePreview);
    });
}

function handleSmartFilter(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    // Validar formulario
    const validation = RaqchiApp.validateForm(form);
    if (!validation.isValid) {
        validation.errors.forEach(error => {
            RaqchiApp.showMessage(error, 'error');
        });
        return;
    }
    
    // Mostrar indicador de carga
    form.classList.add('loading');
    
    // Enviar datos
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        // Reemplazar la página con el resultado
        document.open();
        document.write(html);
        document.close();
    })
    .catch(error => {
        console.error('Error:', error);
        RaqchiApp.showMessage('Error al procesar el filtro', 'error');
    })
    .finally(() => {
        form.classList.remove('loading');
    });
}

function updatePricePreview() {
    const form = document.getElementById('smartFilterForm');
    if (!form) return;
    
    const visitorType = form.querySelector('input[name="filter_visitor_type"]:checked')?.value;
    const needGuide = form.querySelector('input[name="filter_need_guide"]:checked')?.value === 'yes';
    
    if (!visitorType) return;
    
    // Precios base (deberían venir del servidor, pero usamos valores estáticos para el ejemplo)
    const prices = {
        'national': 15,
        'foreign': 30,
        'student': 8
    };
    
    const basePrice = prices[visitorType] || 0;
    const guidePrice = needGuide ? 50 : 0;
    const totalPrice = basePrice + guidePrice;
    
    // Actualizar preview si existe
    const pricePreview = document.getElementById('pricePreview');
    if (pricePreview) {
        pricePreview.textContent = RaqchiApp.formatPrice(totalPrice);
    }
}

/**
 * Slider de presupuesto
 */
function initializeBudgetSlider() {
    const budgetSlider = document.getElementById('filterBudget');
    if (!budgetSlider) return;
    
    budgetSlider.addEventListener('input', function() {
        updateBudgetDisplay(this.value);
    });
    
    // Inicializar valor
    updateBudgetDisplay(budgetSlider.value);
}

function updateBudgetDisplay(value) {
    const budgetValue = document.getElementById('budgetValue');
    if (budgetValue) {
        budgetValue.textContent = value;
        
        // Actualizar color según el valor
        const slider = document.getElementById('filterBudget');
        const percentage = (value - slider.min) / (slider.max - slider.min);
        
        if (percentage < 0.3) {
            budgetValue.style.color = '#FF6B6B'; // Rojo para presupuesto bajo
        } else if (percentage < 0.7) {
            budgetValue.style.color = '#4ECDC4'; // Verde azulado para medio
        } else {
            budgetValue.style.color = '#45B7D1'; // Azul para alto
        }
    }
    
    // Actualizar vista previa si está visible
    const preview = document.querySelector('.filter-preview');
    if (preview && preview.style.display !== 'none') {
        updateFilterPreview();
    }
}

/**
 * Video hero
 */
function initializeHeroVideo() {
    const heroVideo = document.getElementById('heroVideo');
    if (!heroVideo) return;
    
    // Pausar video si no está visible (para ahorrar batería en móviles)
    const videoObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                heroVideo.play();
            } else {
                heroVideo.pause();
            }
        });
    });
    
    videoObserver.observe(heroVideo);
    
    // Manejar errores de video
    heroVideo.addEventListener('error', function() {
        console.log('Error loading hero video, falling back to image');
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.style.backgroundImage = 'url(/assets/images/raqchi-hero-fallback.jpg)';
            heroSection.style.backgroundSize = 'cover';
            heroSection.style.backgroundPosition = 'center';
        }
    });
    
    // Reducir velocidad de reproducción para efecto cinematográfico
    heroVideo.playbackRate = 0.8;
}

/**
 * Animaciones de scroll específicas para la página de inicio
 */
function initializeScrollAnimations() {
    // Animación del hero al hacer scroll
    const heroSection = document.querySelector('.hero-section');
    const heroContent = document.querySelector('.hero-content');
    
    if (heroSection && heroContent) {
        window.addEventListener('scroll', RaqchiApp.throttle(function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            
            // Efecto parallax en el hero
            heroContent.style.transform = `translateY(${rate}px)`;
            
            // Fade out gradual
            const opacity = 1 - (scrolled / window.innerHeight);
            heroContent.style.opacity = Math.max(0, opacity);
        }, 16)); // ~60fps
    }
    
    // Animación de las tarjetas al aparecer
    const animatedElements = document.querySelectorAll('.combo-card, .service-card, .testimonial-card');
    
    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('fade-in-up');
                }, index * 100); // Delay escalonado
                animationObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(el => {
        animationObserver.observe(el);
    });
}

/**
 * Animación de números estadísticos
 */
function initializeStatisticsAnimation() {
    const statNumbers = document.querySelectorAll('.stat-number[data-count]');
    
    const animateNumber = (element) => {
        const target = parseInt(element.dataset.count);
        const duration = 2000; // 2 segundos
        const start = 0;
        const startTime = performance.now();
        
        const updateNumber = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Ease out animation
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + (target - start) * easeProgress);
            
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            } else {
                element.textContent = target.toLocaleString();
            }
        };
        
        requestAnimationFrame(updateNumber);
    };
    
    // Intersection Observer para animar cuando sea visible
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                entry.target.classList.add('animated');
                animateNumber(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    statNumbers.forEach(stat => observer.observe(stat));
}

/**
 * Mejorar las animaciones de scroll
 */
function enhanceScrollAnimations() {
    // Animaciones para las tarjetas
    const animateElements = document.querySelectorAll(
        '.experience-card, .service-card, .testimonial-card, .stat-card, .info-card'
    );
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                // Agregar delay escalonado para efecto de cascada
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    entry.target.classList.add('animate-in');
                }, index * 100);
            }
        });
    }, observerOptions);
    
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s cubic-bezier(0.4, 0.0, 0.2, 1)';
        observer.observe(el);
    });
}

/**
 * Efecto parallax suave para el hero
 */
function initializeParallaxEffect() {
    const heroSection = document.querySelector('.hero-section');
    const heroVideo = document.querySelector('.hero-video');
    
    if (!heroSection || !heroVideo) return;
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        
        heroVideo.style.transform = `translateY(${rate}px)`;
    });
}

/**
 * Mejoras para el filtro inteligente
 */
function enhanceSmartFilter() {
    const form = document.getElementById('smartFilterForm');
    if (!form) return;
    
    // Crear contenedor de vista previa si no existe
    let preview = form.querySelector('.filter-preview');
    if (!preview) {
        preview = document.createElement('div');
        preview.className = 'filter-preview';
        preview.style.display = 'none';
        form.appendChild(preview);
    }
    
    // Previsualización en tiempo real
    const inputs = form.querySelectorAll('input[type="radio"], select, input[type="range"]');
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            updateFilterPreview();
        });
    });
    
    // Animación suave al enviar
    form.addEventListener('submit', (e) => {
        const button = form.querySelector('button[type="submit"]');
        button.classList.add('loading');
        button.textContent = 'Buscando opciones...';
        
        // Simular tiempo de búsqueda
        setTimeout(() => {
            button.classList.remove('loading');
            button.textContent = 'Buscar';
        }, 2000);
    });
}

function updateFilterPreview() {
    const form = document.getElementById('smartFilterForm');
    const preview = form.querySelector('.filter-preview');
    if (!preview) return;
    
    const formData = new FormData(form);
    
    const visitorType = formData.get('filter_visitor_type');
    const needGuide = formData.get('filter_need_guide');
    const duration = formData.get('filter_duration') || '2-3';
    const budget = formData.get('filter_budget') || '50';
    
    // Mostrar previsualización estimada
    if (visitorType) {
        let estimatedPrice = getBasePrice(visitorType);
        if (needGuide === 'yes') {
            estimatedPrice += 50; // Precio estimado del guía
        }
        
        const includes = [
            'Entrada al complejo arqueológico',
            'Mapa del sitio',
            'Acceso a todas las áreas'
        ];
        
        if (needGuide === 'yes') {
            includes.push('Guía turístico especializado');
            includes.push('Explicación histórica detallada');
        }
        
        const recommendations = getRecommendations(visitorType, needGuide, duration, budget);
        
        preview.innerHTML = `
            <h4><i class="icon-preview"></i> Vista previa de tu visita</h4>
            <div class="preview-details">
                <div class="preview-item">
                    <i class="icon-user"></i>
                    <span>Tipo: ${getVisitorTypeText(visitorType)}</span>
                </div>
                <div class="preview-item">
                    <i class="icon-clock"></i>
                    <span>Duración: ${getDurationText(duration)}</span>
                </div>
                <div class="preview-item">
                    <i class="icon-guide"></i>
                    <span>Guía: ${needGuide === 'yes' ? 'Incluido' : 'No incluido'}</span>
                </div>
                <div class="preview-item">
                    <i class="icon-budget"></i>
                    <span>Presupuesto: S/ ${budget}</span>
                </div>
            </div>
            
            <div class="preview-includes">
                <strong>Incluye:</strong>
                <ul>
                    ${includes.map(item => `<li>${item}</li>`).join('')}
                </ul>
            </div>
            
            ${recommendations ? `<div class="preview-recommendation">
                <strong>💡 Recomendación:</strong> ${recommendations}
            </div>` : ''}
            
            <div class="preview-price">
                Precio estimado: S/ ${estimatedPrice}
            </div>
        `;
        
        preview.style.display = 'block';
        preview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        preview.style.display = 'none';
    }
}

function getRecommendations(visitorType, needGuide, duration, budget) {
    const recommendations = [];
    
    if (visitorType === 'foreign' && needGuide !== 'yes') {
        recommendations.push('Te recomendamos incluir un guía para una mejor experiencia cultural');
    }
    
    if (duration === '1-2' && needGuide === 'yes') {
        recommendations.push('Con guía, recomendamos al menos 2-3 horas para aprovechar mejor la visita');
    }
    
    if (parseInt(budget) < 30 && visitorType === 'foreign') {
        recommendations.push('Considera aumentar tu presupuesto para incluir servicios adicionales');
    }
    
    if (duration === 'todo-dia') {
        recommendations.push('Para día completo, considera agregar almuerzo y talleres artesanales');
    }
    
    return recommendations.length > 0 ? recommendations[0] : null;
}

function getDurationText(duration) {
    const durations = {
        '1-2': '1-2 horas',
        '2-3': '2-3 horas',
        '3-4': '3-4 horas',
        'todo-dia': 'Todo el día'
    };
    return durations[duration] || duration;
}

/**
 * Mejorar la función de actualización del presupuesto
 */
function updateBudgetDisplay(value) {
    const budgetValue = document.getElementById('budgetValue');
    if (budgetValue) {
        budgetValue.textContent = value;
        
        // Actualizar color según el valor
        const slider = document.getElementById('filterBudget');
        const percentage = (value - slider.min) / (slider.max - slider.min);
        
        if (percentage < 0.3) {
            budgetValue.style.color = '#FF6B6B'; // Rojo para presupuesto bajo
        } else if (percentage < 0.7) {
            budgetValue.style.color = '#4ECDC4'; // Verde azulado para medio
        } else {
            budgetValue.style.color = '#45B7D1'; // Azul para alto
        }
    }
    
    // Actualizar vista previa si está visible
    const preview = document.querySelector('.filter-preview');
    if (preview && preview.style.display !== 'none') {
        updateFilterPreview();
    }
}

/**
 * Video hero
 */
function initializeHeroVideo() {
    const heroVideo = document.getElementById('heroVideo');
    if (!heroVideo) return;
    
    // Pausar video si no está visible (para ahorrar batería en móviles)
    const videoObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                heroVideo.play();
            } else {
                heroVideo.pause();
            }
        });
    });
    
    videoObserver.observe(heroVideo);
    
    // Manejar errores de video
    heroVideo.addEventListener('error', function() {
        console.log('Error loading hero video, falling back to image');
        const heroSection = document.querySelector('.hero-section');
        if (heroSection) {
            heroSection.style.backgroundImage = 'url(/assets/images/raqchi-hero-fallback.jpg)';
            heroSection.style.backgroundSize = 'cover';
            heroSection.style.backgroundPosition = 'center';
        }
    });
    
    // Reducir velocidad de reproducción para efecto cinematográfico
    heroVideo.playbackRate = 0.8;
}

/**
 * Animaciones de scroll específicas para la página de inicio
 */
function initializeScrollAnimations() {
    // Animación del hero al hacer scroll
    const heroSection = document.querySelector('.hero-section');
    const heroContent = document.querySelector('.hero-content');
    
    if (heroSection && heroContent) {
        window.addEventListener('scroll', RaqchiApp.throttle(function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            
            // Efecto parallax en el hero
            heroContent.style.transform = `translateY(${rate}px)`;
            
            // Fade out gradual
            const opacity = 1 - (scrolled / window.innerHeight);
            heroContent.style.opacity = Math.max(0, opacity);
        }, 16)); // ~60fps
    }
    
    // Animación de las tarjetas al aparecer
    const animatedElements = document.querySelectorAll('.combo-card, .service-card, .testimonial-card');
    
    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.classList.add('fade-in-up');
                }, index * 100); // Delay escalonado
                animationObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(el => {
        animationObserver.observe(el);
    });
}

/**
 * Animación de números estadísticos
 */
function initializeStatisticsAnimation() {
    const statNumbers = document.querySelectorAll('.stat-number[data-count]');
    
    const animateNumber = (element) => {
        const target = parseInt(element.dataset.count);
        const duration = 2000; // 2 segundos
        const start = 0;
        const startTime = performance.now();
        
        const updateNumber = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Ease out animation
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + (target - start) * easeProgress);
            
            element.textContent = current.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            } else {
                element.textContent = target.toLocaleString();
            }
        };
        
        requestAnimationFrame(updateNumber);
    };
    
    // Intersection Observer para animar cuando sea visible
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                entry.target.classList.add('animated');
                animateNumber(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    statNumbers.forEach(stat => observer.observe(stat));
}

/**
 * Mejorar las animaciones de scroll
 */
function enhanceScrollAnimations() {
    // Animaciones para las tarjetas
    const animateElements = document.querySelectorAll(
        '.experience-card, .service-card, .testimonial-card, .stat-card, .info-card'
    );
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                // Agregar delay escalonado para efecto de cascada
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    entry.target.classList.add('animate-in');
                }, index * 100);
            }
        });
    }, observerOptions);
    
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s cubic-bezier(0.4, 0.0, 0.2, 1)';
        observer.observe(el);
    });
}

/**
 * Efecto parallax suave para el hero
 */
function initializeParallaxEffect() {
    const heroSection = document.querySelector('.hero-section');
    const heroVideo = document.querySelector('.hero-video');
    
    if (!heroSection || !heroVideo) return;
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        
        heroVideo.style.transform = `translateY(${rate}px)`;
    });
}

/**
 * Mejoras para el filtro inteligente
 */
function enhanceSmartFilter() {
    const form = document.getElementById('smartFilterForm');
    if (!form) return;
    
    // Crear contenedor de vista previa si no existe
    let preview = form.querySelector('.filter-preview');
    if (!preview) {
        preview = document.createElement('div');
        preview.className = 'filter-preview';
        preview.style.display = 'none';
        form.appendChild(preview);
    }
    
    // Previsualización en tiempo real
    const inputs = form.querySelectorAll('input[type="radio"], select, input[type="range"]');
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            updateFilterPreview();
        });
    });
    
    // Animación suave al enviar
    form.addEventListener('submit', (e) => {
        const button = form.querySelector('button[type="submit"]');
        button.classList.add('loading');
        button.textContent = 'Buscando opciones...';
        
        // Simular tiempo de búsqueda
        setTimeout(() => {
            button.classList.remove('loading');
            button.textContent = 'Buscar';
        }, 2000);
    });
}

function updateFilterPreview() {
    const form = document.getElementById('smartFilterForm');
    const preview = form.querySelector('.filter-preview');
    if (!preview) return;
    
    const formData = new FormData(form);
    
    const visitorType = formData.get('filter_visitor_type');
    const needGuide = formData.get('filter_need_guide');
    const duration = formData.get('filter_duration') || '2-3';
    const budget = formData.get('filter_budget') || '50';
    
    // Mostrar previsualización estimada
    if (visitorType) {
        let estimatedPrice = getBasePrice(visitorType);
        if (needGuide === 'yes') {
            estimatedPrice += 50; // Precio estimado del guía
        }
        
        const includes = [
            'Entrada al complejo arqueológico',
            'Mapa del sitio',
            'Acceso a todas las áreas'
        ];
        
        if (needGuide === 'yes') {
            includes.push('Guía turístico especializado');
            includes.push('Explicación histórica detallada');
        }
        
        const recommendations = getRecommendations(visitorType, needGuide, duration, budget);
        
        preview.innerHTML = `
            <h4><i class="icon-preview"></i> Vista previa de tu visita</h4>
            <div class="preview-details">
                <div class="preview-item">
                    <i class="icon-user"></i>
                    <span>Tipo: ${getVisitorTypeText(visitorType)}</span>
                </div>
                <div class="preview-item">
                    <i class="icon-clock"></i>
                    <span>Duración: ${getDurationText(duration)}</span>
                </div>
                <div class="preview-item">
                    <i class="icon-guide"></i>
                    <span>Guía: ${needGuide === 'yes' ? 'Incluido' : 'No incluido'}</span>
                </div>
                <div class="preview-item">
                    <i class="icon-budget"></i>
                    <span>Presupuesto: S/ ${budget}</span>
                </div>
            </div>
            
            <div class="preview-includes">
                <strong>Incluye:</strong>
                <ul>
                    ${includes.map(item => `<li>${item}</li>`).join('')}
                </ul>
            </div>
            
            ${recommendations ? `<div class="preview-recommendation">
                <strong>💡 Recomendación:</strong> ${recommendations}
            </div>` : ''}
            
            <div class="preview-price">
                Precio estimado: S/ ${estimatedPrice}
            </div>
        `;
        
        preview.style.display = 'block';
        preview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        preview.style.display = 'none';
    }
}

function getRecommendations(visitorType, needGuide, duration, budget) {
    const recommendations = [];
    
    if (visitorType === 'foreign' && needGuide !== 'yes') {
        recommendations.push('Te recomendamos incluir un guía para una mejor experiencia cultural');
    }
    
    if (duration === '1-2' && needGuide === 'yes') {
        recommendations.push('Con guía, recomendamos al menos 2-3 horas para aprovechar mejor la visita');
    }
    
    if (parseInt(budget) < 30 && visitorType === 'foreign') {
        recommendations.push('Considera aumentar tu presupuesto para incluir servicios adicionales');
    }
    
    if (duration === 'todo-dia') {
        recommendations.push('Para día completo, considera agregar almuerzo y talleres artesanales');
    }
    
    return recommendations.length > 0 ? recommendations[0] : null;
}

function getDurationText(duration) {
    const durations = {
        '1-2': '1-2 horas',
        '2-3': '2-3 horas',
        '3-4': '3-4 horas',
        'todo-dia': 'Todo el día'
    };
    return durations[duration] || duration;
}

/**
 * Gestión de estado de la página
 */
function savePageState() {
    const state = {
        currentTestimonial: currentTestimonialIndex,
        scrollPosition: window.pageYOffset,
        timestamp: Date.now()
    };
    
    sessionStorage.setItem('homePageState', JSON.stringify(state));
}

function restorePageState() {
    const saved = sessionStorage.getItem('homePageState');
    if (saved) {
        const state = JSON.parse(saved);
        
        // Restaurar solo si es reciente (menos de 5 minutos)
        if (Date.now() - state.timestamp < 5 * 60 * 1000) {
            if (state.currentTestimonial) {
                showTestimonial(state.currentTestimonial);
            }
        }
    }
}

// Guardar estado antes de salir
window.addEventListener('beforeunload', savePageState);

/**
 * Funciones de navegación para usar desde los botones del carrusel
 */
window.nextTestimonial = nextTestimonial;
window.previousTestimonial = previousTestimonial;
window.updateBudgetDisplay = updateBudgetDisplay;

/**
 * Funciones para manejar clicks en las tarjetas de combo
 */
function selectCombo(comboData) {
    // Guardar datos del combo seleccionado en localStorage
    RaqchiApp.Storage.set('selectedCombo', comboData);
    
    // Redirigir a la página de compra
    window.location.href = '/compra.html';
}

/**
 * Optimización de performance
 */
function optimizePerformance() {
    // Lazy loading para videos
    const videos = document.querySelectorAll('video[data-src]');
    videos.forEach(video => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    video.src = video.dataset.src;
                    video.load();
                    observer.unobserve(video);
                }
            });
        });
        observer.observe(video);
    });
    
    // Preload para páginas críticas
    const criticalPages = ['/compra.html', '/servicios.html'];
    criticalPages.forEach(page => {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = page;
        document.head.appendChild(link);
    });
}

// Ejecutar optimizaciones después de la carga inicial
window.addEventListener('load', function() {
    setTimeout(optimizePerformance, 1000);
});

/**
 * Analytics y seguimiento (placeholder para implementación futura)
 */
function trackUserInteraction(action, category, label) {
    // Implementar tracking con Google Analytics, etc.
    if (typeof gtag !== 'undefined') {
        gtag('event', action, {
            'event_category': category,
            'event_label': label
        });
    }
    
    console.log('Track:', action, category, label);
}

// Tracking de interacciones importantes
document.addEventListener('click', function(e) {
    const target = e.target.closest('a, button');
    if (!target) return;
    
    // Tracking de botones de compra
    if (target.href && target.href.includes('compra.html')) {
        trackUserInteraction('click', 'purchase', 'buy_ticket_button');
    }
    
    // Tracking de filtros
    if (target.closest('#smartFilterForm')) {
        trackUserInteraction('click', 'filter', 'smart_filter');
    }
    
    // Tracking de servicios
    if (target.href && target.href.includes('servicios.html')) {
        trackUserInteraction('click', 'navigation', 'services');
    }
});

/**
 * Función para manejar el envío del formulario de newsletter (si existe)
 */
function handleNewsletterSubscription(email) {
    if (!RaqchiApp.validateForm({querySelector: () => ({value: email, type: 'email'})})) {
        RaqchiApp.showMessage('Por favor ingresa un email válido', 'error');
        return;
    }
    
    RaqchiApp.makeRequest('/api/newsletter-subscribe.php', {
        method: 'POST',
        body: JSON.stringify({email: email})
    })
    .then(data => {
        if (data.success) {
            RaqchiApp.showMessage('¡Gracias por suscribirte!', 'success');
        } else {
            RaqchiApp.showMessage(data.message || 'Error al suscribirse', 'error');
        }
    })
    .catch(error => {
        console.error('Newsletter subscription error:', error);
        RaqchiApp.showMessage('Error al procesar la suscripción', 'error');
    });
}

// Exponer funciones específicas de la página
window.HomePageApp = {
    nextTestimonial,
    previousTestimonial,
    updateBudgetDisplay,
    selectCombo,
    handleNewsletterSubscription,
    trackUserInteraction
};
