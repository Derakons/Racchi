/**
 * JavaScript principal del Portal Digital de Raqchi
 * Funciones globales y utilidades comunes
 */

// Variables globales
let currentLanguage = 'es';
let csrfToken = '';

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Inicialización principal de la aplicación
 */
function initializeApp() {
    // Obtener token CSRF si existe
    const csrfInput = document.querySelector('input[name="csrf_token"]');
    if (csrfInput) {
        csrfToken = csrfInput.value;
    }
    
    // Obtener idioma actual
    const langSelect = document.getElementById('languageSelect');
    if (langSelect) {
        currentLanguage = langSelect.value;
    }
    
    // Inicializar componentes
    initializeFlashMessages();
    initializeMobileMenu();
    initializeScrollEffects();
    initializeLazyLoading();
    
    // Auto-cerrar mensajes flash después de 5 segundos
    setTimeout(function() {
        const flashMessage = document.getElementById('flashMessage');
        if (flashMessage) {
            closeFlashMessage();
        }
    }, 5000);
}

/**
 * Manejo de mensajes flash
 */
function initializeFlashMessages() {
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        flashMessage.style.display = 'flex';
        
        // Auto cerrar después de 5 segundos
        setTimeout(() => {
            closeFlashMessage();
        }, 5000);
    }
}

function closeFlashMessage() {
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        flashMessage.style.animation = 'slideUp 0.3s ease';
        setTimeout(() => {
            flashMessage.remove();
        }, 300);
    }
}

/**
 * Menú móvil
 */
function initializeMobileMenu() {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', toggleMobileMenu);
        
        // Cerrar menú al hacer clic en un enlace
        const navLinks = navMenu.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            });
        });
        
        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!navToggle.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            }
        });
    }
}

function toggleMobileMenu() {
    const navMenu = document.getElementById('navMenu');
    const navToggle = document.getElementById('navToggle');
    
    navMenu.classList.toggle('active');
    navToggle.classList.toggle('active');
}

/**
 * Cambio de idioma
 */
function changeLanguage(newLang) {
    // Verificar que el idioma sea válido
    if (!['es', 'en'].includes(newLang)) {
        console.error('Idioma no válido:', newLang);
        return;
    }
    
    // No hacer nada si es el mismo idioma actual
    if (newLang === currentLanguage) return;
    
    // Mostrar indicador de carga
    showLoadingIndicator();
    
    // Realizar petición para cambiar idioma
    fetch(SITE_URL + '/api/change-language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
            language: newLang
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Actualizar idioma actual y recargar página
            currentLanguage = newLang;
            window.location.reload();
        } else {
            throw new Error(data.message || 'Error desconocido al cambiar idioma');
        }
    })
    .catch(error => {
        console.error('Error al cambiar idioma:', error);
        showMessage('Error al cambiar idioma', 'error');
        
        // Restaurar selector al idioma actual
        const languageSelect = document.getElementById('languageSelect');
        if (languageSelect) {
            languageSelect.value = currentLanguage;
        }
    });
}

/**
 * Efectos de scroll
 */
function initializeScrollEffects() {
    // Scroll suave para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Animaciones al hacer scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    // Observar elementos que deben animarse
    document.querySelectorAll('.card, .service-card, .combo-card').forEach(el => {
        observer.observe(el);
    });
}

/**
 * Carga perezosa de imágenes
 */
function initializeLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

/**
 * Utilidades para peticiones AJAX
 */
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        }
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    return fetch(url, finalOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Request error:', error);
            throw error;
        });
}

/**
 * Mostrar/ocultar indicador de carga
 */
function showLoadingIndicator(target = document.body) {
    target.classList.add('loading');
    
    // Crear spinner si no existe
    if (!target.querySelector('.spinner')) {
        const spinner = document.createElement('div');
        spinner.className = 'spinner';
        spinner.id = 'globalSpinner';
        target.appendChild(spinner);
    }
}

function hideLoadingIndicator(target = document.body) {
    target.classList.remove('loading');
    
    // Remover spinner
    const spinner = target.querySelector('#globalSpinner');
    if (spinner) {
        spinner.remove();
    }
}

/**
 * Mostrar mensajes al usuario
 */
function showMessage(message, type = 'info', duration = 5000) {
    // Remover mensaje anterior si existe
    const existingMessage = document.getElementById('dynamicMessage');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    // Crear nuevo mensaje
    const messageEl = document.createElement('div');
    messageEl.id = 'dynamicMessage';
    messageEl.className = `flash-message flash-${type}`;
    messageEl.innerHTML = `
        ${message}
        <button onclick="this.parentElement.remove()" class="flash-close">&times;</button>
    `;
    
    // Insertar en el DOM
    document.body.appendChild(messageEl);
    
    // Auto-remover después del tiempo especificado
    if (duration > 0) {
        setTimeout(() => {
            if (messageEl.parentElement) {
                messageEl.remove();
            }
        }, duration);
    }
}

/**
 * Validación de formularios
 */
function validateForm(form) {
    const errors = [];
    
    // Validar campos requeridos
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            errors.push(`El campo ${field.name} es requerido`);
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });
    
    // Validar emails
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            errors.push('El formato del email no es válido');
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });
    
    return {
        isValid: errors.length === 0,
        errors: errors
    };
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Formateo de números y precios
 */
function formatPrice(amount, currency = 'PEN') {
    const formatted = new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
    
    switch (currency) {
        case 'USD':
            return `$${formatted}`;
        case 'PEN':
        default:
            return `S/ ${formatted}`;
    }
}

/**
 * Utilidades de fecha
 */
function formatDate(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    const locale = currentLanguage === 'en' ? 'en-US' : 'es-PE';
    
    return new Intl.DateTimeFormat(locale, finalOptions).format(new Date(date));
}

/**
 * Gestión del estado de conexión (para modo offline)
 */
function initializeConnectionStatus() {
    const statusIndicator = document.getElementById('connectionStatus');
    
    function updateConnectionStatus() {
        const isOnline = navigator.onLine;
        
        if (statusIndicator) {
            statusIndicator.className = isOnline ? 'connection-online' : 'connection-offline';
            statusIndicator.textContent = isOnline ? 'Conectado' : 'Sin conexión';
        }
        
        // Mostrar mensaje si se pierde la conexión
        if (!isOnline) {
            showMessage('Conexión perdida. Trabajando en modo offline.', 'warning', 0);
        } else {
            // Ocultar mensaje de offline si se recupera la conexión
            const offlineMessage = document.getElementById('dynamicMessage');
            if (offlineMessage && offlineMessage.textContent.includes('offline')) {
                offlineMessage.remove();
                showMessage('Conexión restaurada', 'success');
            }
        }
    }
    
    // Escuchar eventos de conexión
    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
    
    // Estado inicial
    updateConnectionStatus();
}

/**
 * Manejo de errores globales
 */
window.addEventListener('error', function(e) {
    if (window.location.hostname === 'localhost') {
        console.error('Error global:', e.error);
    }
    
    // En producción, enviar errores al servidor
    if (window.location.hostname !== 'localhost') {
        fetch(SITE_URL + '/api/log-error.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: e.message,
                filename: e.filename,
                lineno: e.lineno,
                colno: e.colno,
                stack: e.error ? e.error.stack : null,
                url: window.location.href,
                userAgent: navigator.userAgent
            })
        }).catch(err => {
            console.error('Failed to log error:', err);
        });
    }
});

/**
 * Funciones de utilidad para localStorage
 */
const Storage = {
    set: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('Error saving to localStorage:', e);
            return false;
        }
    },
    
    get: function(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            console.error('Error reading from localStorage:', e);
            return defaultValue;
        }
    },
    
    remove: function(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (e) {
            console.error('Error removing from localStorage:', e);
            return false;
        }
    },
    
    clear: function() {
        try {
            localStorage.clear();
            return true;
        } catch (e) {
            console.error('Error clearing localStorage:', e);
            return false;
        }
    }
};

/**
 * Debounce function para optimizar eventos
 */
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

/**
 * Throttle function para optimizar eventos
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Exponer funciones globalmente
window.RaqchiApp = {
    changeLanguage,
    toggleMobileMenu,
    closeFlashMessage,
    makeRequest,
    showLoadingIndicator,
    hideLoadingIndicator,
    showMessage,
    validateForm,
    formatPrice,
    formatDate,
    Storage,
    debounce,
    throttle
};

// CSS para animaciones adicionales
const additionalStyles = `
@keyframes slideUp {
    from {
        transform: translate(-50%, 0);
        opacity: 1;
    }
    to {
        transform: translate(-50%, -100%);
        opacity: 0;
    }
}

.nav-toggle.active span:nth-child(1) {
    transform: rotate(-45deg) translate(-5px, 6px);
}

.nav-toggle.active span:nth-child(2) {
    opacity: 0;
}

.nav-toggle.active span:nth-child(3) {
    transform: rotate(45deg) translate(-5px, -6px);
}

.connection-online {
    color: var(--success-color);
}

.connection-offline {
    color: var(--error-color);
}

.lazy {
    opacity: 0;
    transition: opacity 0.3s;
}

.form-control.error {
    border-color: var(--error-color);
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}
`;

// Inyectar estilos adicionales
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);
