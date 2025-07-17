<?php
/**
 * Página de compra de tickets - Portal Digital de Raqchi
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
require_once __DIR__ . '/../includes/bootstrap.php';

// Generar token CSRF
generateCSRFToken();

includeHeader(rqText('tickets'), ['public.css']);
?>

<main class="main-content">
    <div class="container">
        <div class="page-header">
            <h1><?= rqText('tickets') ?></h1>
            <p class="lead"><?= rqText('tickets_description') ?></p>
        </div>

        <!-- Mensajes de estado -->
        <div id="ticketMessages" class="message-container" style="display: none;"></div>

        <!-- Grid de tipos de tickets -->
        <div id="ticketsGrid" class="tickets-grid">
            <!-- Los tickets se cargarán dinámicamente -->
        </div>

        <!-- Modal de compra -->
        <div id="buyTicketModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle">Comprar Ticket</h2>
                    <span class="close" onclick="closeBuyModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="buyTicketForm">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" id="modalTipoTicketId" name="tipo_ticket_id">
                        <input type="hidden" id="modalTipoEntrada" name="tipo_entrada" value="general">
                        
                        <div class="form-section">
                            <h3>Información del Ticket</h3>
                            <div id="ticketInfo" class="ticket-info"></div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Datos del Comprador</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre_comprador">Nombre Completo *</label>
                                    <input type="text" id="nombre_comprador" name="nombre_comprador" required>
                                </div>
                                <div class="form-group">
                                    <label for="email_comprador">Email *</label>
                                    <input type="email" id="email_comprador" name="email_comprador" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="telefono_comprador">Teléfono</label>
                                    <input type="tel" id="telefono_comprador" name="telefono_comprador">
                                </div>
                                <div class="form-group">
                                    <label for="documento_comprador">Documento</label>
                                    <input type="text" id="documento_comprador" name="documento_comprador">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Detalles de la Visita</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="cantidad">Cantidad de Tickets</label>
                                    <select id="cantidad" name="cantidad" onchange="updateTotalPrice()">
                                        <option value="1">1 persona</option>
                                        <option value="2">2 personas</option>
                                        <option value="3">3 personas</option>
                                        <option value="4">4 personas</option>
                                        <option value="5">5 personas</option>
                                        <option value="10">10+ personas (grupo)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="fecha_visita">Fecha de Visita</label>
                                    <input type="date" id="fecha_visita" name="fecha_visita" min="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="hora_visita">Hora Preferida</label>
                                    <select id="hora_visita" name="hora_visita">
                                        <option value="">Cualquier hora</option>
                                        <option value="08:00">8:00 AM</option>
                                        <option value="09:00">9:00 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="12:00">12:00 PM</option>
                                        <option value="13:00">1:00 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="15:00">3:00 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="metodo_pago">Método de Pago</label>
                                    <select id="metodo_pago" name="metodo_pago">
                                        <option value="efectivo">Efectivo en el lugar</option>
                                        <option value="transferencia">Transferencia bancaria</option>
                                        <option value="yape">Yape</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="price-summary">
                            <div class="price-row">
                                <span>Precio unitario:</span>
                                <span id="precioUnitario">S/ 0.00</span>
                            </div>
                            <div class="price-row">
                                <span>Cantidad:</span>
                                <span id="cantidadDisplay">1</span>
                            </div>
                            <div class="price-row total">
                                <span>Total:</span>
                                <span id="precioTotal">S/ 0.00</span>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="closeBuyModal()">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Confirmar Compra</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h2><?= rqText('visit_info') ?></h2>
            <div class="info-grid">
                <div class="info-card">
                    <h3><?= rqText('schedules') ?></h3>
                    <p>Lunes a Domingo: 8:00 AM - 5:00 PM<br>
                    Último ingreso: 4:30 PM</p>
                </div>
                <div class="info-card">
                    <h3><?= rqText('location') ?></h3>
                    <p>Complejo Arqueológico de Raqchi<br>
                    San Pedro de Cacha, Cusco</p>
                </div>
                <div class="info-card">
                    <h3><?= rqText('recommendations') ?></h3>
                    <p>Traer protector solar, agua y zapatos cómodos.<br>
                    Respetar las áreas restringidas.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
/* Estilos para la página de tickets */
.tickets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.ticket-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.ticket-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.ticket-image {
    height: 200px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.ticket-content {
    padding: 1.5rem;
}

.ticket-content h3 {
    margin: 0 0 1rem 0;
    color: #2d3748;
    font-size: 1.5rem;
}

.ticket-content p {
    color: #718096;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.ticket-features {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.ticket-features li {
    padding: 0.25rem 0;
    color: #4a5568;
    font-size: 0.9rem;
}

.ticket-features li:before {
    content: "✓ ";
    color: #38a169;
    font-weight: bold;
}

.ticket-prices {
    margin: 1.5rem 0;
}

.price-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    margin: 0.5rem 0;
    background: #f7fafc;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.price-option:hover {
    background: #edf2f7;
}

.price-option.selected {
    background: #e6fffa;
    border: 2px solid #38a169;
}

.price-label {
    font-weight: 500;
    color: #2d3748;
}

.price-value {
    font-weight: 700;
    color: #38a169;
    font-size: 1.1rem;
}

/* Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
    margin: 0;
    color: #2d3748;
}

.close {
    font-size: 1.5rem;
    cursor: pointer;
    color: #a0aec0;
}

.close:hover {
    color: #2d3748;
}

.modal-body {
    padding: 1.5rem;
}

.form-section {
    margin-bottom: 2rem;
}

.form-section h3 {
    margin: 0 0 1rem 0;
    color: #2d3748;
    font-size: 1.2rem;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #2d3748;
}

.form-group input,
.form-group select {
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3182ce;
    box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
}

.ticket-info {
    background: #f7fafc;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.price-summary {
    background: #f7fafc;
    padding: 1rem;
    border-radius: 8px;
    margin: 1.5rem 0;
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.price-row.total {
    font-weight: bold;
    font-size: 1.1rem;
    border-top: 1px solid #e2e8f0;
    padding-top: 0.5rem;
    margin-top: 0.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.message-container {
    margin: 1rem 0;
}

.message {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.message.success {
    background: #c6f6d5;
    color: #22543d;
    border: 1px solid #9ae6b4;
}

.message.error {
    background: #fed7d7;
    color: #742a2a;
    border: 1px solid #fc8181;
}

.info-section {
    margin-top: 4rem;
    padding: 2rem 0;
    border-top: 1px solid #e2e8f0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.info-card {
    text-align: center;
    padding: 1.5rem;
}

.info-card h3 {
    color: #2d3748;
    margin-bottom: 1rem;
}

.info-card p {
    color: #718096;
    line-height: 1.6;
}

/* Loading spinner */
.loading {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
}

.spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3182ce;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .tickets-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        width: 95%;
        margin: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
let ticketTypes = [];
let selectedTicket = null;
let selectedPriceType = 'general';

// Cargar tipos de tickets al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    loadTicketTypes();
});

// Cargar tipos de tickets desde la API
async function loadTicketTypes() {
    try {
        showLoading();
        const response = await fetch('<?= SITE_URL ?>/api/tickets.php?action=get_types');
        const data = await response.json();
        
        if (data.success) {
            ticketTypes = data.ticket_types;
            renderTickets();
        } else {
            showMessage('Error al cargar tipos de tickets: ' + (data.message || 'Error desconocido'), 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error al conectar con el servidor', 'error');
    } finally {
        hideLoading();
    }
}

// Renderizar tickets en la página
function renderTickets() {
    const grid = document.getElementById('ticketsGrid');
    
    if (ticketTypes.length === 0) {
        grid.innerHTML = '<div class="loading">No hay tipos de tickets disponibles</div>';
        return;
    }
    
    grid.innerHTML = ticketTypes.map(ticket => {
        const features = ticket.caracteristicas || [];
        const featuresHTML = Array.isArray(features) ? 
            features.map(f => `<li>${f}</li>`).join('') : '';
        
        return `
            <div class="ticket-card">
                <div class="ticket-image">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="ticket-content">
                    <h3>${escapeHtml(ticket.nombre)}</h3>
                    <p>${escapeHtml(ticket.descripcion || '')}</p>
                    
                    ${featuresHTML ? `<ul class="ticket-features">${featuresHTML}</ul>` : ''}
                    
                    <div class="ticket-prices">
                        <div class="price-option" onclick="selectTicket('${ticket.id}', 'general', ${ticket.precio})">
                            <span class="price-label">Entrada General</span>
                            <span class="price-value">S/ ${parseFloat(ticket.precio).toFixed(2)}</span>
                        </div>
                        
                        ${ticket.precio_estudiante ? `
                            <div class="price-option" onclick="selectTicket('${ticket.id}', 'estudiante', ${ticket.precio_estudiante})">
                                <span class="price-label">Estudiante</span>
                                <span class="price-value">S/ ${parseFloat(ticket.precio_estudiante).toFixed(2)}</span>
                            </div>
                        ` : ''}
                        
                        ${ticket.precio_grupo ? `
                            <div class="price-option" onclick="selectTicket('${ticket.id}', 'grupo', ${ticket.precio_grupo})">
                                <span class="price-label">Grupo (10+)</span>
                                <span class="price-value">S/ ${parseFloat(ticket.precio_grupo).toFixed(2)}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Seleccionar ticket y abrir modal
function selectTicket(ticketId, priceType, price) {
    selectedTicket = ticketTypes.find(t => t.id === ticketId);
    selectedPriceType = priceType;
    
    if (!selectedTicket) return;
    
    // Llenar información del modal
    document.getElementById('modalTitle').textContent = `Comprar: ${selectedTicket.nombre}`;
    document.getElementById('modalTipoTicketId').value = ticketId;
    document.getElementById('modalTipoEntrada').value = priceType;
    
    // Mostrar información del ticket
    const ticketInfo = document.getElementById('ticketInfo');
    ticketInfo.innerHTML = `
        <h4>${selectedTicket.nombre} - ${getPriceTypeLabel(priceType)}</h4>
        <p>${selectedTicket.descripcion}</p>
        <p><strong>Precio:</strong> S/ ${parseFloat(price).toFixed(2)}</p>
    `;
    
    // Actualizar precio
    document.getElementById('precioUnitario').textContent = `S/ ${parseFloat(price).toFixed(2)}`;
    updateTotalPrice();
    
    // Mostrar modal
    document.getElementById('buyTicketModal').style.display = 'flex';
}

// Cerrar modal
function closeBuyModal() {
    document.getElementById('buyTicketModal').style.display = 'none';
    document.getElementById('buyTicketForm').reset();
}

// Actualizar precio total
function updateTotalPrice() {
    const cantidad = parseInt(document.getElementById('cantidad').value) || 1;
    const precioUnitarioText = document.getElementById('precioUnitario').textContent.replace('S/ ', '');
    const precioUnitario = parseFloat(precioUnitarioText) || 0;
    
    let finalPrice = precioUnitario;
    
    // Si selecciona 10+ personas, cambiar automáticamente a precio de grupo
    if (cantidad >= 10 && selectedTicket.precio_grupo) {
        finalPrice = parseFloat(selectedTicket.precio_grupo);
        document.getElementById('modalTipoEntrada').value = 'grupo';
        document.getElementById('precioUnitario').textContent = `S/ ${finalPrice.toFixed(2)}`;
    }
    
    const total = finalPrice * cantidad;
    
    document.getElementById('cantidadDisplay').textContent = cantidad;
    document.getElementById('precioTotal').textContent = `S/ ${total.toFixed(2)}`;
}

// Obtener etiqueta del tipo de precio
function getPriceTypeLabel(type) {
    const labels = {
        'general': 'Entrada General',
        'estudiante': 'Estudiante',
        'grupo': 'Grupo (10+)'
    };
    return labels[type] || 'Entrada General';
}

// Manejar envío del formulario
document.getElementById('buyTicketForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    try {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Procesando...';
        
        const formData = new FormData(e.target);
        formData.append('action', 'create_ticket');
        
        const response = await fetch('<?= SITE_URL ?>/api/tickets.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(`¡Ticket creado exitosamente! Código: ${data.ticket.codigo_ticket}. Total: S/ ${data.ticket.precio_total}`, 'success');
            closeBuyModal();
            
            // Opcional: mostrar información de pago
            setTimeout(() => {
                alert(`Su ticket ha sido creado con el código: ${data.ticket.codigo_ticket}\n\nPor favor, conserve este código para su visita.\n\nTotal a pagar: S/ ${data.ticket.precio_total}`);
            }, 1000);
        } else {
            showMessage('Error al crear el ticket: ' + (data.message || 'Error desconocido'), 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error al conectar con el servidor', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});

// Funciones de utilidad
function showMessage(message, type) {
    const container = document.getElementById('ticketMessages');
    container.innerHTML = `<div class="message ${type}">${message}</div>`;
    container.style.display = 'block';
    
    // Auto-hide después de 5 segundos
    setTimeout(() => {
        container.style.display = 'none';
    }, 5000);
}

function showLoading() {
    document.getElementById('ticketsGrid').innerHTML = '<div class="loading"><div class="spinner"></div></div>';
}

function hideLoading() {
    // Se oculta automáticamente al renderizar contenido
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Cerrar modal con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBuyModal();
    }
});

// Cerrar modal al hacer clic fuera
document.getElementById('buyTicketModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBuyModal();
    }
});
</script>

<?php includeFooter(); ?>
