<?php
/**
 * Gestión de Tickets - Admin - Conectado a Supabase
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/../includes/bootstrap.php';

// Requerir acceso de administrador
requireRole('admin');

// Obtener cliente de Supabase con clave de servicio
$supabase = new SupabaseClient(true);

// Manejar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        redirectWithMessage('tickets.php', rqText('invalid_token'), 'error');
        exit;
    }
    
    $action = $_POST['action'];
    $ticketId = $_POST['ticket_id'] ?? '';
    
    switch ($action) {
        case 'update_status':
            if (!empty($ticketId) && !empty($_POST['estado'])) {
                $nuevoEstado = $_POST['estado'];
                $estadosValidos = ['pendiente', 'pagado', 'usado', 'cancelado'];
                
                if (in_array($nuevoEstado, $estadosValidos)) {
                    $updateData = ['estado' => $nuevoEstado];
                    if ($nuevoEstado === 'usado') {
                        $updateData['usado_at'] = date('Y-m-d H:i:s');
                        $updateData['usado_por'] = $_SESSION['user_id'] ?? null;
                    }
                    
                    $result = $supabase->update('tickets', $updateData, ['id' => $ticketId]);
                    
                    if ($result['success']) {
                        logActivity('Estado de ticket actualizado', 'info', ['ticket_id' => $ticketId, 'nuevo_estado' => $nuevoEstado]);
                        redirectWithMessage('tickets.php', 'Estado del ticket actualizado exitosamente', 'success');
                    } else {
                        redirectWithMessage('tickets.php', 'Error al actualizar estado del ticket', 'error');
                    }
                }
            }
            break;
            
        case 'validate_ticket':
            $codigoTicket = $_POST['codigo_ticket'] ?? '';
            if (!empty($codigoTicket)) {
                // Buscar el ticket
                $ticketResult = $supabase->select(
                    'tickets',
                    'id,estado,nombre_comprador,email_comprador,cantidad,precio_total',
                    ['codigo_ticket' => $codigoTicket]
                );
                
                if ($ticketResult['success'] && !empty($ticketResult['data']) && is_array($ticketResult['data'])) {
                    $ticket = $ticketResult['data'][0];
                    
                    if ($ticket['estado'] === 'usado') {
                        redirectWithMessage('tickets.php', 'Este ticket ya ha sido usado', 'warning');
                    } elseif ($ticket['estado'] === 'pagado') {
                        // Marcar como usado
                        $updateResult = $supabase->update(
                            'tickets',
                            [
                                'estado' => 'usado',
                                'usado_at' => date('Y-m-d H:i:s'),
                                'usado_por' => $_SESSION['user_id'] ?? null
                            ],
                            ['id' => $ticket['id']]
                        );
                        
                        if ($updateResult['success']) {
                            logActivity('Ticket validado', 'info', ['codigo' => $codigoTicket, 'comprador' => $ticket['nombre_comprador']]);
                            redirectWithMessage('tickets.php', "Ticket validado exitosamente para {$ticket['nombre_comprador']}", 'success');
                        } else {
                            redirectWithMessage('tickets.php', 'Error al validar el ticket', 'error');
                        }
                    } else {
                        redirectWithMessage('tickets.php', 'El ticket no está en estado válido para usar (debe estar pagado)', 'warning');
                    }
                } else {
                    redirectWithMessage('tickets.php', 'Ticket no encontrado', 'error');
                }
            }
            break;
    }
    
    exit;
}

// Obtener estadísticas
$estadisticas = [
    'total' => 0,
    'pendientes' => 0,
    'pagados' => 0,
    'usados' => 0,
    'cancelados' => 0,
    'ingresos_hoy' => 0,
    'ingresos_mes' => 0
];

try {
    // Contar tickets por estado
    $allTicketsResult = $supabase->select('tickets', 'estado,precio_total,created_at', [], ['limit' => 1000]);
    
    if ($allTicketsResult['success'] && is_array($allTicketsResult['data'])) {
        $tickets = $allTicketsResult['data'];
        $estadisticas['total'] = count($tickets);
        
        $hoy = date('Y-m-d');
        $mesActual = date('Y-m');
        
        foreach ($tickets as $ticket) {
            $estado = $ticket['estado'] ?? 'pendiente';
            if (isset($estadisticas[$estado . 's'])) {
                $estadisticas[$estado . 's']++;
            }
            
            // Calcular ingresos solo de tickets pagados y usados
            if (in_array($estado, ['pagado', 'usado'])) {
                $precio = floatval($ticket['precio_total'] ?? 0);
                $fechaCreacion = substr($ticket['created_at'] ?? '', 0, 10);
                $mesCreacion = substr($ticket['created_at'] ?? '', 0, 7);
                
                if ($fechaCreacion === $hoy) {
                    $estadisticas['ingresos_hoy'] += $precio;
                }
                if ($mesCreacion === $mesActual) {
                    $estadisticas['ingresos_mes'] += $precio;
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Error obteniendo estadísticas de tickets: " . $e->getMessage());
}

includeAdminHeader(rqText('tickets') . ' - ' . rqText('admin'), ['admin.css'], ['admin.js']);
?>

<main class="admin-main">
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo rqText('site_name'); ?>">
                <span>Admin</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/admin/"><i class="fas fa-tachometer-alt"></i><span><?php echo rqText('dashboard'); ?></span></a></li>
                <li class="nav-item active"><a href="<?php echo SITE_URL; ?>/admin/tickets.php"><i class="fas fa-ticket-alt"></i><span><?php echo rqText('tickets'); ?></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/admin/servicios.php"><i class="fas fa-concierge-bell"></i><span><?php echo rqText('services'); ?></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/admin/usuarios.php"><i class="fas fa-users"></i><span><?php echo rqText('users'); ?></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/admin/reseñas.php"><i class="fas fa-star"></i><span><?php echo rqText('reviews'); ?></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/admin/reportes.php"><i class="fas fa-chart-bar"></i><span><?php echo rqText('reports'); ?></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/admin/configuracion.php"><i class="fas fa-cog"></i><span><?php echo rqText('settings'); ?></span></a></li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><i class="fas fa-user"></i></div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <span class="user-role"><?php echo rqText('administrator'); ?></span>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/api/logout.php?redirect=1" class="logout-btn" title="<?php echo rqText('logout'); ?>"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </aside>
    
    <div class="admin-content">
        <header class="admin-header">
            <div class="header-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <h1><i class="fas fa-ticket-alt"></i> Gestión de Tickets</h1>
            </div>
            <div class="header-right">
                <a href="<?php echo SITE_URL; ?>/public/tickets.php" class="btn btn-outline-primary" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Ver página pública
                </a>
            </div>
        </header>

        <!-- Mostrar mensajes -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible">
                <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'"></button>
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <div style="padding: var(--spacing-lg);">
            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="dashboard-card stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $estadisticas['total'] ?></div>
                        <div class="stat-label">Total Tickets</div>
                    </div>
                </div>
                
                <div class="dashboard-card stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $estadisticas['pendientes'] ?></div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
                
                <div class="dashboard-card stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $estadisticas['usados'] ?></div>
                        <div class="stat-label">Usados</div>
                    </div>
                </div>
                
                <div class="dashboard-card stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">S/ <?= number_format($estadisticas['ingresos_mes'], 2) ?></div>
                        <div class="stat-label">Ingresos del Mes</div>
                    </div>
                </div>
            </div>

            <!-- Validador de tickets -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-qrcode"></i> Validar Ticket</h2>
                </div>
                <div class="card-content">
                    <form method="POST" class="validator-form">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="validate_ticket">
                        <div class="form-group">
                            <label for="codigo_ticket">Código de Ticket:</label>
                            <div class="input-group">
                                <input type="text" id="codigo_ticket" name="codigo_ticket" placeholder="Ej: RQ2025123456" required>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Validar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de tickets -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Tickets Recientes</h2>
                    <div class="card-actions">
                        <button class="btn btn-primary" onclick="loadTickets()">
                            <i class="fas fa-sync"></i> Actualizar
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="filters-row">
                        <select id="estadoFilter" onchange="loadTickets()">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="pagado">Pagados</option>
                            <option value="usado">Usados</option>
                            <option value="cancelado">Cancelados</option>
                        </select>
                        <input type="date" id="fechaFilter" onchange="loadTickets()" placeholder="Filtrar por fecha">
                    </div>
                    
                    <div id="ticketsContainer" class="tickets-container">
                        <!-- Los tickets se cargarán aquí -->
                    </div>
                    
                    <div id="loadingIndicator" class="loading-indicator" style="display: none;">
                        <div class="spinner"></div>
                        <p>Cargando tickets...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
/* Estilos específicos para la gestión de tickets */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem !important;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.bg-primary { background-color: #3b82f6; }
.bg-warning { background-color: #f59e0b; }
.bg-success { background-color: #10b981; }
.bg-danger { background-color: #ef4444; }
.bg-info { background-color: #06b6d4; }

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.stat-label {
    color: #6b7280;
    font-weight: 500;
    margin: 0;
}

.validator-form {
    max-width: 600px;
}

.input-group {
    display: flex;
    gap: 0.5rem;
}

.input-group input {
    flex: 1;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 1rem;
}

.filters-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    align-items: center;
}

.filters-row select,
.filters-row input {
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
}

.tickets-container {
    display: grid;
    gap: 1rem;
}

.ticket-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1rem;
    display: grid;
    grid-template-columns: auto 1fr auto auto;
    gap: 1rem;
    align-items: center;
}

.ticket-code {
    font-family: monospace;
    font-weight: bold;
    color: #1f2937;
    background: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.ticket-info h4 {
    margin: 0 0 0.25rem 0;
    color: #1f2937;
}

.ticket-info p {
    margin: 0;
    color: #6b7280;
    font-size: 0.9rem;
}

.ticket-status {
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-pendiente { background-color: #fef3c7; color: #92400e; }
.status-pagado { background-color: #d1fae5; color: #065f46; }
.status-usado { background-color: #dbeafe; color: #1e40af; }
.status-cancelado { background-color: #fee2e2; color: #991b1b; }

.ticket-actions {
    display: flex;
    gap: 0.5rem;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    position: relative;
}

.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }

.btn-close {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    opacity: 0.6;
}

.btn-close:hover { opacity: 1; }

.loading-indicator {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    gap: 1rem;
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
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .ticket-item {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .input-group {
        flex-direction: column;
    }
}
</style>

<script>
// Cargar tickets al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    loadTickets();
});

// Cargar tickets desde la API
async function loadTickets() {
    try {
        showLoading();
        
        const estado = document.getElementById('estadoFilter').value;
        const fecha = document.getElementById('fechaFilter').value;
        
        let url = '<?= SITE_URL ?>/api/tickets.php?action=get_tickets&limit=20';
        if (estado) url += '&estado=' + encodeURIComponent(estado);
        if (fecha) url += '&fecha_desde=' + encodeURIComponent(fecha) + '&fecha_hasta=' + encodeURIComponent(fecha);
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            renderTickets(data.tickets);
        } else {
            showError('Error al cargar tickets: ' + (data.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error al conectar con el servidor');
    } finally {
        hideLoading();
    }
}

// Renderizar tickets
function renderTickets(tickets) {
    const container = document.getElementById('ticketsContainer');
    
    if (tickets.length === 0) {
        container.innerHTML = '<div class="empty-state">No se encontraron tickets</div>';
        return;
    }
    
    container.innerHTML = tickets.map(ticket => {
        const fecha = new Date(ticket.created_at).toLocaleDateString('es-ES');
        const statusClass = `status-${ticket.estado}`;
        
        return `
            <div class="ticket-item">
                <div class="ticket-code">${escapeHtml(ticket.codigo_ticket)}</div>
                <div class="ticket-info">
                    <h4>${escapeHtml(ticket.nombre_comprador)}</h4>
                    <p>${escapeHtml(ticket.email_comprador)} • ${ticket.cantidad} tickets • S/ ${parseFloat(ticket.precio_total).toFixed(2)}</p>
                    <p>Creado: ${fecha}${ticket.fecha_visita ? ` • Visita: ${ticket.fecha_visita}` : ''}</p>
                </div>
                <div class="ticket-status ${statusClass}">
                    ${getStatusLabel(ticket.estado)}
                </div>
                <div class="ticket-actions">
                    ${generateTicketActions(ticket)}
                </div>
            </div>
        `;
    }).join('');
}

// Generar acciones para cada ticket
function generateTicketActions(ticket) {
    let actions = '';
    
    if (ticket.estado === 'pendiente') {
        actions += `
            <button class="btn btn-sm btn-success" onclick="updateTicketStatus('${ticket.id}', 'pagado')">
                <i class="fas fa-check"></i> Marcar Pagado
            </button>
        `;
    }
    
    if (ticket.estado === 'pagado') {
        actions += `
            <button class="btn btn-sm btn-primary" onclick="updateTicketStatus('${ticket.id}', 'usado')">
                <i class="fas fa-check-double"></i> Marcar Usado
            </button>
        `;
    }
    
    if (ticket.estado !== 'cancelado' && ticket.estado !== 'usado') {
        actions += `
            <button class="btn btn-sm btn-danger" onclick="updateTicketStatus('${ticket.id}', 'cancelado')">
                <i class="fas fa-times"></i> Cancelar
            </button>
        `;
    }
    
    return actions;
}

// Actualizar estado de ticket
async function updateTicketStatus(ticketId, nuevoEstado) {
    if (!confirm(`¿Está seguro de cambiar el estado a "${getStatusLabel(nuevoEstado)}"?`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('ticket_id', ticketId);
        formData.append('estado', nuevoEstado);
        formData.append('csrf_token', '<?= generateCsrfToken() ?>');
        
        const response = await fetch('<?= SITE_URL ?>/admin/tickets.php', {
            method: 'POST',
            body: formData
        });
        
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            loadTickets(); // Recargar la lista
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al actualizar el estado del ticket');
    }
}

// Obtener etiqueta del estado
function getStatusLabel(estado) {
    const labels = {
        'pendiente': 'Pendiente',
        'pagado': 'Pagado',
        'usado': 'Usado',
        'cancelado': 'Cancelado'
    };
    return labels[estado] || estado;
}

// Funciones de utilidad
function showLoading() {
    document.getElementById('loadingIndicator').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingIndicator').style.display = 'none';
}

function showError(message) {
    const container = document.getElementById('ticketsContainer');
    container.innerHTML = `<div class="alert alert-error">${message}</div>`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php includeFooter(['admin.js']); ?>
