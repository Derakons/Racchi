<?php
/**
 * Dashboard de Administración - Portal Digital de Raqchi
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Requerir acceso de administrador
requireRole('admin');

$supabase = getSupabaseClient();

// Obtener estadísticas del dashboard
$stats = [
    'tickets_vendidos_hoy' => 0,
    'ingresos_hoy' => 0,
    'tickets_mes' => 0,
    'ingresos_mes' => 0,
    'usuarios_activos' => 0,
    'servicios_activos' => 0
];

try {
    $hoy = date('Y-m-d');
    $mesActual = date('Y-m');
    
    // Tickets vendidos hoy
    $ticketsHoy = $supabase->select('tickets', 'COUNT(*)', [
        'fecha_compra' => ['operator' => 'gte', 'value' => $hoy . ' 00:00:00'],
        'estado' => 'pagado'
    ]);
    
    // Ingresos hoy
    $ingresosHoy = $supabase->select('tickets', 'SUM(precio_total)', [
        'fecha_compra' => ['operator' => 'gte', 'value' => $hoy . ' 00:00:00'],
        'estado' => 'pagado'
    ]);
    
    // Usuarios activos
    $usuariosActivos = $supabase->select('usuarios', 'COUNT(*)', [
        'estado' => 'activo'
    ]);
    
    // Servicios activos
    $serviciosActivos = $supabase->select('servicios_generales', 'COUNT(*)', [
        'estado' => 'activo'
    ]);
    
    if ($ticketsHoy['success'] && !empty($ticketsHoy['data'])) {
        $stats['tickets_vendidos_hoy'] = $ticketsHoy['data'][0]['count'] ?? 0;
    }
    
    if ($ingresosHoy['success'] && !empty($ingresosHoy['data'])) {
        $stats['ingresos_hoy'] = $ingresosHoy['data'][0]['sum'] ?? 0;
    }
    
    if ($usuariosActivos['success'] && !empty($usuariosActivos['data'])) {
        $stats['usuarios_activos'] = $usuariosActivos['data'][0]['count'] ?? 0;
    }
    
    if ($serviciosActivos['success'] && !empty($serviciosActivos['data'])) {
        $stats['servicios_activos'] = $serviciosActivos['data'][0]['count'] ?? 0;
    }
    
} catch (Exception $e) {
    error_log('Dashboard stats error: ' . $e->getMessage());
}

// Obtener tickets recientes
$ticketsRecientes = $supabase->select(
    'tickets', 
    'id,codigo_ticket,nombre_visitante,email_visitante,precio_total,estado,fecha_compra', 
    ['estado' => 'pagado'], 
    'fecha_compra.desc', 
    10
);

// Obtener reseñas pendientes
$reseñasPendientes = $supabase->select(
    'reseñas',
    'id,titulo,nombre_visitante,calificacion,fecha_creacion,estado',
    ['estado' => 'pendiente'],
    'fecha_creacion.desc',
    5
);

includeAdminHeader(rqText('admin_dashboard'), ['admin.css'], ['admin.js']);
?>

<main class="admin-main">
    <!-- Overlay para cerrar sidebar en móvil -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo rqText('site_name'); ?>">
                <span>Admin</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item active">
                    <a href="<?php echo SITE_URL; ?>/admin/">
                        <i class="fas fa-tachometer-alt"></i>
                        <span><?php echo rqText('dashboard'); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin/tickets.php">
                        <i class="fas fa-ticket-alt"></i>
                        <span><?php echo rqText('tickets'); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin/servicios.php">
                        <i class="fas fa-concierge-bell"></i>
                        <span><?php echo rqText('services'); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin/usuarios.php">
                        <i class="fas fa-users"></i>
                        <span><?php echo rqText('users'); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin/reseñas.php">
                        <i class="fas fa-star"></i>
                        <span><?php echo rqText('reviews'); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin/reportes.php">
                        <i class="fas fa-chart-bar"></i>
                        <span><?php echo rqText('reports'); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/admin/configuracion.php">
                        <i class="fas fa-cog"></i>
                        <span><?php echo rqText('settings'); ?></span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <span class="user-role"><?php echo rqText('administrator'); ?></span>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/api/logout.php?redirect=1" class="logout-btn" title="<?php echo rqText('logout'); ?>">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </aside>
    
    <!-- Contenido principal -->
    <div class="admin-content">
        <!-- Header del admin -->
        <header class="admin-header">
            <div class="header-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1><?php echo rqText('dashboard'); ?></h1>
            </div>
            
            <div class="header-right">
                <div class="header-stats">
                    <span class="date-display"><?php echo date('d/m/Y'); ?></span>
                </div>
                
                <div class="header-actions">
                    <button class="btn btn-outline btn-compact" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i>
                        <?php echo rqText('refresh'); ?>
                    </button>
                    
                    <a href="<?php echo SITE_URL; ?>/" class="btn btn-outline btn-compact" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <?php echo rqText('view_site'); ?>
                    </a>
                </div>
            </div>
        </header>
        
        <!-- Tarjetas de estadísticas -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($stats['tickets_vendidos_hoy']); ?></div>
                    <div class="stat-label"><?php echo rqText('tickets_today'); ?></div>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">S/ <?php echo number_format($stats['ingresos_hoy'], 2); ?></div>
                    <div class="stat-label"><?php echo rqText('revenue_today'); ?></div>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($stats['usuarios_activos']); ?></div>
                    <div class="stat-label"><?php echo rqText('active_users'); ?></div>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-concierge-bell"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo number_format($stats['servicios_activos']); ?></div>
                    <div class="stat-label"><?php echo rqText('active_services'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Contenido del dashboard -->
        <div class="dashboard-grid">
            <!-- Tickets recientes -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><?php echo rqText('recent_tickets'); ?></h2>
                    <a href="<?php echo SITE_URL; ?>/admin/tickets.php" class="btn btn-outline btn-compact">
                        <?php echo rqText('view_all'); ?>
                    </a>
                </div>
                
                <div class="card-content">
                    <?php if ($ticketsRecientes['success'] && !empty($ticketsRecientes['data'])): ?>
                        <div class="tickets-list">
                            <?php foreach ($ticketsRecientes['data'] as $ticket): ?>
                            <div class="ticket-item">
                                <div class="ticket-info">
                                    <div class="ticket-code"><?php echo htmlspecialchars($ticket['codigo_ticket']); ?></div>
                                    <div class="ticket-customer"><?php echo htmlspecialchars($ticket['nombre_visitante']); ?></div>
                                    <div class="ticket-date"><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_compra'])); ?></div>
                                </div>
                                <div class="ticket-amount">
                                    S/ <?php echo number_format($ticket['precio_total'], 2); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-ticket-alt"></i>
                            <p><?php echo rqText('no_recent_tickets'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Reseñas pendientes -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><?php echo rqText('pending_reviews'); ?></h2>
                    <a href="<?php echo SITE_URL; ?>/admin/reseñas.php" class="btn btn-outline btn-compact">
                        <?php echo rqText('moderate'); ?>
                    </a>
                </div>
                
                <div class="card-content">
                    <?php if ($reseñasPendientes['success'] && !empty($reseñasPendientes['data'])): ?>
                        <div class="reviews-list">
                            <?php foreach ($reseñasPendientes['data'] as $reseña): ?>
                            <div class="review-item">
                                <div class="review-info">
                                    <div class="review-title"><?php echo htmlspecialchars($reseña['titulo']); ?></div>
                                    <div class="review-author"><?php echo htmlspecialchars($reseña['nombre_visitante']); ?></div>
                                    <div class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i <= $reseña['calificacion'] ? 'filled' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="review-actions">
                                    <button class="btn btn-success btn-compact" onclick="approveReview(<?php echo $reseña['id']; ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-error btn-compact" onclick="rejectReview(<?php echo $reseña['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p><?php echo rqText('no_pending_reviews'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Acciones rápidas -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><?php echo rqText('quick_actions'); ?></h2>
                </div>
                
                <div class="card-content">
                    <div class="quick-actions">
                        <a href="<?php echo SITE_URL; ?>/taquilla/" class="action-item" target="_blank">
                            <i class="fas fa-cash-register"></i>
                            <span><?php echo rqText('pos_system'); ?></span>
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/admin/servicios.php?action=new" class="action-item">
                            <i class="fas fa-plus"></i>
                            <span><?php echo rqText('new_service'); ?></span>
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/admin/usuarios.php?action=new" class="action-item">
                            <i class="fas fa-user-plus"></i>
                            <span><?php echo rqText('new_user'); ?></span>
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/admin/reportes.php" class="action-item">
                            <i class="fas fa-chart-line"></i>
                            <span><?php echo rqText('generate_report'); ?></span>
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/admin/configuracion.php" class="action-item">
                            <i class="fas fa-cog"></i>
                            <span><?php echo rqText('system_settings'); ?></span>
                        </a>
                        
                        <a href="<?php echo SITE_URL; ?>/" class="action-item" target="_blank">
                            <i class="fas fa-globe"></i>
                            <span><?php echo rqText('view_website'); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


