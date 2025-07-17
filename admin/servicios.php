<?php
/**
 * Gestión de Servicios - Admin
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Requerir acceso de administrador
requireRole('admin');

includeAdminHeader(rqText('services') . ' - ' . rqText('admin'), ['admin.css'], ['admin.js']);
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
                <li class="nav-item">
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
                <li class="nav-item active">
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
                <h1><?php echo rqText('services'); ?></h1>
            </div>
            
            <div class="header-right">
                <div class="header-actions">
                    <button class="btn btn-primary btn-compact">
                        <i class="fas fa-plus"></i>
                        Nuevo Servicio
                    </button>
                </div>
            </div>
        </header>
        
        <!-- Contenido -->
        <div style="padding: var(--spacing-lg);">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Gestión de Servicios</h2>
                </div>
                <div class="card-content">
                    <div class="empty-state">
                        <i class="fas fa-concierge-bell"></i>
                        <p>Página en construcción - Gestión de servicios</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php includeFooter(['admin.js']); ?>
