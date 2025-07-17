/**
 * JavaScript para el panel de administración
 * Gestión de sidebar, modales y funcionalidades del dashboard
 */

// Variables globales
let sidebarOpen = false;

// Inicialización cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    initializeAdmin();
});

/**
 * Inicializar funcionalidades del admin
 */
function initializeAdmin() {
    // Configurar eventos del sidebar
    setupSidebarEvents();
    
    // Configurar navegación activa
    setActiveNavigation();
    
    // Configurar tooltips si existen
    setupTooltips();
    
    // Auto-refresh del dashboard cada 5 minutos
    if (window.location.pathname.includes('/admin/') && window.location.pathname.endsWith('/')) {
        setInterval(refreshDashboard, 5 * 60 * 1000);
    }
}

/**
 * Configurar eventos del sidebar
 */
function setupSidebarEvents() {
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    // Cerrar sidebar al hacer clic en overlay
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
    
    // Cerrar sidebar con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebarOpen) {
            closeSidebar();
        }
    });
    
    // Gestionar resize de ventana
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024 && sidebarOpen) {
            closeSidebar();
        }
    });
}

/**
 * Toggle del sidebar (mostrar/ocultar)
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (!sidebar) return;
    
    sidebarOpen = !sidebarOpen;
    
    if (sidebarOpen) {
        sidebar.classList.add('sidebar-open');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    } else {
        sidebar.classList.remove('sidebar-open');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Cerrar sidebar
 */
function closeSidebar() {
    const sidebar = document.querySelector('.admin-sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (!sidebar) return;
    
    sidebarOpen = false;
    sidebar.classList.remove('sidebar-open');
    if (overlay) overlay.classList.remove('active');
    document.body.style.overflow = '';
}

/**
 * Establecer navegación activa basada en la URL actual
 */
function setActiveNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    const currentPath = window.location.pathname;
    
    navItems.forEach(item => {
        const link = item.querySelector('a');
        if (link) {
            const linkPath = new URL(link.href).pathname;
            
            // Remover clase activa
            item.classList.remove('active');
            
            // Agregar clase activa si coincide
            if (currentPath === linkPath || 
                (currentPath.includes('/admin/') && linkPath.includes(currentPath.split('/').pop()))) {
                item.classList.add('active');
            }
        }
    });
}

/**
 * Configurar tooltips
 */
function setupTooltips() {
    const elementsWithTooltip = document.querySelectorAll('[title]');
    
    elementsWithTooltip.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

/**
 * Mostrar tooltip
 */
function showTooltip(e) {
    const element = e.target;
    const title = element.getAttribute('title');
    
    if (!title) return;
    
    // Crear tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = title;
    tooltip.style.cssText = `
        position: absolute;
        background: var(--dark-gray);
        color: var(--white-full);
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        z-index: 9999;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    // Posicionar tooltip
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
    
    // Remover title para evitar tooltip nativo
    element.setAttribute('data-title', title);
    element.removeAttribute('title');
    
    // Guardar referencia
    element._tooltip = tooltip;
}

/**
 * Ocultar tooltip
 */
function hideTooltip(e) {
    const element = e.target;
    
    if (element._tooltip) {
        document.body.removeChild(element._tooltip);
        delete element._tooltip;
    }
    
    // Restaurar title
    const title = element.getAttribute('data-title');
    if (title) {
        element.setAttribute('title', title);
        element.removeAttribute('data-title');
    }
}

/**
 * Refrescar datos del dashboard
 */
function refreshDashboard() {
    // Mostrar indicador de carga
    showLoadingIndicator();
    
    // Recargar página completa por simplicidad
    // En producción, esto podría ser una llamada AJAX
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

/**
 * Mostrar indicador de carga
 */
function showLoadingIndicator() {
    const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
    if (refreshBtn) {
        const icon = refreshBtn.querySelector('i');
        if (icon) {
            icon.style.animation = 'spin 1s linear infinite';
        }
    }
}

/**
 * Aprobar reseña
 */
function approveReview(reviewId) {
    if (!confirm('¿Aprobar esta reseña?')) return;
    
    fetch(SITE_URL + '/admin/api/reviews.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'approve',
            id: reviewId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Reseña aprobada exitosamente', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification('Error al aprobar la reseña', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

/**
 * Rechazar reseña
 */
function rejectReview(reviewId) {
    if (!confirm('¿Rechazar esta reseña?')) return;
    
    fetch(SITE_URL + '/admin/api/reviews.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'reject',
            id: reviewId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Reseña rechazada', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showNotification('Error al rechazar la reseña', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error de conexión', 'error');
    });
}

/**
 * Mostrar notificación
 */
function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Convertir saltos de línea a <br> para HTML
    const htmlMessage = message.replace(/\n/g, '<br>');
    notification.innerHTML = htmlMessage;
    
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: var(--${type === 'error' ? 'error' : type === 'success' ? 'success' : 'info'}-color);
        color: var(--white-full);
        padding: 12px 20px;
        border-radius: 6px;
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        box-shadow: var(--shadow-md);
        max-width: 400px;
        min-width: 250px;
        word-wrap: break-word;
        line-height: 1.4;
        font-size: 14px;
    `;
    
    document.body.appendChild(notification);
    
    // Mostrar animación
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Ocultar después de 3 segundos
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

/**
 * Animación de spin para íconos
 */
const style = document.createElement('style');
style.textContent = `
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
`;
document.head.appendChild(style);

/**
 * Funciones para la página de configuración
 */
function initConfigurationPage() {
    // Inicializar envíos de formularios
    const forms = document.querySelectorAll('.settings-form');
    forms.forEach(form => {
        form.addEventListener('submit', handleSettingsSubmit);
    });
}

async function handleSettingsSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formId = form.id;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Mostrar estado de carga
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    submitBtn.disabled = true;
    
    try {
        const response = await fetch(`${SITE_URL}/api/admin/save_settings.php`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Configuración guardada exitosamente', 'success');
            
            // Si se cambiaron las configuraciones de idioma, recargar página
            if (formId === 'languageSettings') {
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        } else {
            showNotification(result.message || 'Error al guardar la configuración', 'error');
        }
    } catch (error) {
        console.error('Error saving settings:', error);
        showNotification('Error de conexión al guardar la configuración', 'error');
    } finally {
        // Restaurar estado del botón
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

async function testEmailConfig() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Probando...';
    btn.disabled = true;
    
    try {
        const form = document.getElementById('emailSettings');
        const formData = new FormData(form);
        formData.append('action', 'test_email');
        
        console.log('Enviando prueba de email...');
        
        const response = await fetch(`${SITE_URL}/api/admin/test_email.php`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('Respuesta recibida:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status} ${response.statusText}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Respuesta no es JSON. Content-Type: ${contentType}. Respuesta: ${text.substring(0, 200)}...`);
        }
        
        const result = await response.json();
        console.log('Resultado:', result);
        
        if (result.success) {
            let message = result.message;
            if (result.details) {
                message += '\n\nDetalles:';
                if (result.details.host) message += `\n• Host: ${result.details.host}`;
                if (result.details.port) message += `\n• Puerto: ${result.details.port}`;
                if (result.details.username) message += `\n• Usuario: ${result.details.username}`;
                if (result.details.secure) message += `\n• Seguridad: ${result.details.secure}`;
                if (result.details.connection_test) {
                    message += `\n• Conectividad: ${result.details.connection_test.message}`;
                    if (result.details.connection_test.server_response) {
                        message += `\n• Respuesta del servidor: ${result.details.connection_test.server_response}`;
                    }
                }
                if (result.details.warnings && result.details.warnings.length > 0) {
                    message += '\n\nAdvertencias:';
                    result.details.warnings.forEach(warning => {
                        message += `\n• ${warning}`;
                    });
                }
                if (result.details.config_tips && result.details.config_tips.length > 0) {
                    message += '\n\nConsejos de configuración:';
                    result.details.config_tips.forEach(tip => {
                        message += `\n• ${tip}`;
                    });
                }
                if (result.details.note) message += `\n\nNota: ${result.details.note}`;
            }
            showNotification(message, 'success');
        } else {
            let errorMessage = result.message || 'Error al enviar email de prueba';
            
            // Manejar errores específicos de autenticación
            if (result.error_code === 'NOT_AUTHENTICATED') {
                errorMessage = '❌ Sesión expirada. Por favor, recargue la página e inicie sesión nuevamente.';
                // Opcional: redirigir automáticamente al login después de un delay
                setTimeout(() => {
                    window.location.href = `${SITE_URL}/login.php`;
                }, 3000);
            } else if (result.error_code === 'INSUFFICIENT_PERMISSIONS') {
                errorMessage = '❌ Permisos insuficientes. Se requieren permisos de administrador.';
            }
            
            if (result.errors && Array.isArray(result.errors)) {
                errorMessage += '\n\nErrores encontrados:';
                result.errors.forEach(error => {
                    errorMessage += `\n• ${error}`;
                });
            }
            
            if (result.warnings && Array.isArray(result.warnings)) {
                errorMessage += '\n\nAdvertencias:';
                result.warnings.forEach(warning => {
                    errorMessage += `\n• ${warning}`;
                });
            }
            
            if (result.suggestions && Array.isArray(result.suggestions)) {
                errorMessage += '\n\nSugerencias:';
                result.suggestions.forEach(suggestion => {
                    errorMessage += `\n• ${suggestion}`;
                });
            }
            
            if (result.debug_info) {
                console.error('Debug info:', result.debug_info);
                if (result.debug_info.suggestions && Array.isArray(result.debug_info.suggestions)) {
                    errorMessage += '\n\nSugerencias adicionales:';
                    result.debug_info.suggestions.forEach(suggestion => {
                        errorMessage += `\n• ${suggestion}`;
                    });
                }
            }
            
            showNotification(errorMessage, 'error');
        }
    } catch (error) {
        console.error('Error testing email:', error);
        let errorMsg = 'Error de conexión al probar email';
        
        if (error.message.includes('Failed to fetch')) {
            errorMsg += '\n\nPosibles causas:\n• Servidor no disponible\n• Problema de red\n• XAMPP no está corriendo';
        } else if (error.message.includes('HTTP Error')) {
            errorMsg += `\n\nError del servidor: ${error.message}`;
        } else {
            errorMsg += `\n\nDetalle: ${error.message}`;
        }
        
        showNotification(errorMsg, 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function clearCache() {
    if (!confirm('¿Estás seguro de que deseas limpiar la caché del sistema?')) {
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Limpiando...';
    btn.disabled = true;
    
    try {
        const response = await fetch(`${SITE_URL}/api/admin/system_actions.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: 'clear_cache' })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Caché limpiada exitosamente', 'success');
        } else {
            showNotification(result.message || 'Error al limpiar la caché', 'error');
        }
    } catch (error) {
        console.error('Error clearing cache:', error);
        showNotification('Error de conexión al limpiar caché', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function exportData() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exportando...';
    btn.disabled = true;
    
    try {
        const response = await fetch(`${SITE_URL}/api/admin/export_data.php`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `raqchi_data_export_${new Date().toISOString().split('T')[0]}.zip`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            showNotification('Datos exportados exitosamente', 'success');
        } else {
            showNotification('Error al exportar datos', 'error');
        }
    } catch (error) {
        console.error('Error exporting data:', error);
        showNotification('Error de conexión al exportar datos', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function backupDatabase() {
    if (!confirm('¿Estás seguro de que deseas crear un respaldo de la base de datos?')) {
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Respaldando...';
    btn.disabled = true;
    
    try {
        const response = await fetch(`${SITE_URL}/api/admin/system_actions.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: 'backup_database' })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Base de datos respaldada exitosamente', 'success');
        } else {
            showNotification(result.message || 'Error al respaldar la base de datos', 'error');
        }
    } catch (error) {
        console.error('Error backing up database:', error);
        showNotification('Error de conexión al respaldar base de datos', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

function confirmSystemReset() {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Restablecimiento</h3>
            </div>
            <div class="modal-body">
                <p><strong>¡ADVERTENCIA!</strong> Esta acción eliminará todos los datos del sistema y no se puede deshacer.</p>
                <p>Por favor, escriba <strong>RESTABLECER</strong> para confirmar:</p>
                <input type="text" id="confirmText" class="form-control" placeholder="Escriba RESTABLECER">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="executeSystemReset()">Restablecer Sistema</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.getElementById('confirmText').focus();
}

async function executeSystemReset() {
    const confirmText = document.getElementById('confirmText').value;
    
    if (confirmText !== 'RESTABLECER') {
        showNotification('Debe escribir "RESTABLECER" para confirmar', 'error');
        return;
    }
    
    try {
        const response = await fetch(`${SITE_URL}/api/admin/system_actions.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: 'reset_system', confirm: 'RESTABLECER' })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Sistema restablecido exitosamente. Redirigiendo...', 'success');
            setTimeout(() => {
                window.location.href = `${SITE_URL}/login.php`;
            }, 2000);
        } else {
            showNotification(result.message || 'Error al restablecer el sistema', 'error');
        }
    } catch (error) {
        console.error('Error resetting system:', error);
        showNotification('Error de conexión al restablecer sistema', 'error');
    }
    
    closeModal();
}

function closeModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.remove();
    }
}

// Inicializar página de configuración cuando se carga el DOM
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.settings-form')) {
        initConfigurationPage();
    }
});
