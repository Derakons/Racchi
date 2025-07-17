<?php
/**
 * Reportes - Admin
 */

require_once __DIR__ . '/../includes/bootstrap.php';
requireRole('admin');
includeAdminHeader(rqText('reports') . ' - ' . rqText('admin'), ['admin.css'], ['admin.js']);
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
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/admin/tickets.php"><i class="fas fa-ticket-alt"></i><span><?php echo rqText('tickets'); ?></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/admin/servicios.php"><i class="fas fa-concierge-bell"></i><span><?php echo rqText('services'); ?></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/admin/usuarios.php"><i class="fas fa-users"></i><span><?php echo rqText('users'); ?></span></a></li>
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/admin/reseñas.php"><i class="fas fa-star"></i><span><?php echo rqText('reviews'); ?></span></a></li>
                <li class="nav-item active"><a href="<?php echo SITE_URL; ?>/admin/reportes.php"><i class="fas fa-chart-bar"></i><span><?php echo rqText('reports'); ?></span></a></li>
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
                <h1><?php echo rqText('reports'); ?></h1>
            </div>
        </header>
        
        <div style="padding: var(--spacing-lg);">
            <div class="dashboard-card">
                <div class="card-header"><h2>Reportes y Estadísticas</h2></div>
                <div class="card-content">
                    <div class="empty-state">
                        <i class="fas fa-chart-bar"></i>
                        <p>Página en construcción - Reportes y estadísticas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php includeFooter(['admin.js']); ?>
