<?php
/**
 * Administración de Reseñas - Versión con datos locales
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/../includes/bootstrap.php';

// Requerir acceso de administrador
requireRole('admin');

// Archivo de datos local para reseñas
$reviewsFile = __DIR__ . '/../data/reviews.json';
$reviewsDir = dirname($reviewsFile);

if (!is_dir($reviewsDir)) {
    mkdir($reviewsDir, 0755, true);
}

// Manejar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        redirectWithMessage('reseñas.php', rqText('invalid_token'), 'error');
        exit;
    }
    
    $action = $_POST['action'];
    $reviewId = intval($_POST['review_id'] ?? 0);
    
    // Leer reseñas existentes
    $reviews = [];
    if (file_exists($reviewsFile)) {
        $reviews = json_decode(file_get_contents($reviewsFile), true) ?: [];
    }
    
    switch ($action) {
        case 'approve':
            if ($reviewId > 0) {
                foreach ($reviews as &$review) {
                    if ($review['id'] == $reviewId) {
                        $review['estado'] = 'aprobada';
                        break;
                    }
                }
                file_put_contents($reviewsFile, json_encode($reviews, JSON_PRETTY_PRINT));
                logActivity('Reseña aprobada', 'info', ['review_id' => $reviewId]);
                redirectWithMessage('reseñas.php', 'Reseña aprobada exitosamente', 'success');
            }
            break;
            
        case 'reject':
            if ($reviewId > 0) {
                foreach ($reviews as &$review) {
                    if ($review['id'] == $reviewId) {
                        $review['estado'] = 'rechazada';
                        break;
                    }
                }
                file_put_contents($reviewsFile, json_encode($reviews, JSON_PRETTY_PRINT));
                logActivity('Reseña rechazada', 'warning', ['review_id' => $reviewId]);
                redirectWithMessage('reseñas.php', 'Reseña rechazada', 'success');
            }
            break;
            
        case 'delete':
            if ($reviewId > 0) {
                $reviews = array_filter($reviews, function($review) use ($reviewId) {
                    return $review['id'] != $reviewId;
                });
                file_put_contents($reviewsFile, json_encode(array_values($reviews), JSON_PRETTY_PRINT));
                logActivity('Reseña eliminada', 'warning', ['review_id' => $reviewId]);
                redirectWithMessage('reseñas.php', 'Reseña eliminada exitosamente', 'success');
            }
            break;
    }
    
    exit;
}

// Obtener parámetros de filtrado y paginación
$estado = $_GET['estado'] ?? 'todas';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Leer reseñas
$allReviews = [];
if (file_exists($reviewsFile)) {
    $allReviews = json_decode(file_get_contents($reviewsFile), true) ?: [];
} else {
    // Crear datos de ejemplo si no existen
    $allReviews = [
        [
            'id' => 1,
            'nombre_reviewer' => 'María González',
            'email_reviewer' => 'maria.gonzalez@email.com',
            'rating' => 5,
            'contenido' => 'Una experiencia increíble. El lugar es mágico y los guías muy conocedores de la historia.',
            'estado' => 'pendiente',
            'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'ip_address' => '192.168.1.100'
        ],
        [
            'id' => 2,
            'nombre_reviewer' => 'John Smith',
            'email_reviewer' => 'john.smith@email.com',
            'rating' => 4,
            'contenido' => 'Amazing place! The pottery workshops were fascinating and the food was delicious.',
            'estado' => 'aprobada',
            'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-1 week')),
            'ip_address' => '10.0.0.50'
        ],
        [
            'id' => 3,
            'nombre_reviewer' => 'Carlos Mendoza',
            'email_reviewer' => 'carlos.mendoza@email.com',
            'rating' => 5,
            'contenido' => 'Muy recomendable. El transporte fue puntual y el sitio arqueológico impresionante.',
            'estado' => 'aprobada',
            'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
            'ip_address' => '172.16.0.25'
        ],
        [
            'id' => 4,
            'nombre_reviewer' => 'Roberto Silva',
            'email_reviewer' => 'roberto.silva@email.com',
            'rating' => 3,
            'contenido' => 'El lugar es bonito pero esperaba más información histórica.',
            'estado' => 'pendiente',
            'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-3 days')),
            'ip_address' => '203.0.113.10'
        ],
        [
            'id' => 5,
            'nombre_reviewer' => 'Lisa Johnson',
            'email_reviewer' => 'lisa.johnson@email.com',
            'rating' => 2,
            'contenido' => 'The site was okay but not as impressive as other ruins we visited.',
            'estado' => 'rechazada',
            'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'ip_address' => '198.51.100.42'
        ]
    ];
    
    file_put_contents($reviewsFile, json_encode($allReviews, JSON_PRETTY_PRINT));
}

// Filtrar por estado
$filteredReviews = $allReviews;
if ($estado !== 'todas') {
    $filteredReviews = array_filter($allReviews, function($review) use ($estado) {
        return $review['estado'] === $estado;
    });
}

// Ordenar por fecha de creación descendente
usort($filteredReviews, function($a, $b) {
    return strtotime($b['fecha_creacion']) - strtotime($a['fecha_creacion']);
});

// Paginación
$totalReviews = count($filteredReviews);
$totalPages = ceil($totalReviews / $limit);
$pagedReviews = array_slice($filteredReviews, $offset, $limit);

// Calcular estadísticas
$estadisticas = [
    'pendientes' => count(array_filter($allReviews, function($r) { return $r['estado'] === 'pendiente'; })),
    'aprobadas' => count(array_filter($allReviews, function($r) { return $r['estado'] === 'aprobada'; })),
    'rechazadas' => count(array_filter($allReviews, function($r) { return $r['estado'] === 'rechazada'; })),
    'total' => count($allReviews)
];

// Función para obtener estado con clase CSS
function getEstadoBadge($estado) {
    $badges = [
        'pendiente' => ['class' => 'badge-warning', 'text' => 'Pendiente'],
        'aprobada' => ['class' => 'badge-success', 'text' => 'Aprobada'],
        'rechazada' => ['class' => 'badge-danger', 'text' => 'Rechazada']
    ];
    
    return $badges[$estado] ?? ['class' => 'badge-secondary', 'text' => ucfirst($estado)];
}

// Función para generar estrellas
function renderStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fas fa-star text-warning"></i>';
        } else {
            $stars .= '<i class="far fa-star text-muted"></i>';
        }
    }
    return $stars;
}

includeAdminHeader('Administración de Reseñas', ['admin.css'], ['admin.js']);
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
                <li class="nav-item active"><a href="<?php echo SITE_URL; ?>/admin/reseñas.php"><i class="fas fa-star"></i><span><?php echo rqText('reviews'); ?></span></a></li>
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
                <h1><i class="fas fa-star"></i> Administración de Reseñas</h1>
            </div>
            <div class="header-right">
                <a href="<?php echo SITE_URL; ?>/public/reviews.php" class="btn btn-outline-primary" target="_blank">
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
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $estadisticas['aprobadas'] ?></div>
                        <div class="stat-label">Aprobadas</div>
                    </div>
                </div>
                
                <div class="dashboard-card stat-card">
                    <div class="stat-icon bg-danger">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $estadisticas['rechazadas'] ?></div>
                        <div class="stat-label">Rechazadas</div>
                    </div>
                </div>
                
                <div class="dashboard-card stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $estadisticas['total'] ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-filter"></i> Filtros</h2>
                </div>
                <div class="card-content">
                    <form method="GET" class="filter-form">
                        <div class="form-group">
                            <label for="estado">Filtrar por estado:</label>
                            <select name="estado" id="estado" class="form-control" onchange="this.form.submit()">
                                <option value="todas" <?= $estado === 'todas' ? 'selected' : '' ?>>Todas las reseñas</option>
                                <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                                <option value="aprobada" <?= $estado === 'aprobada' ? 'selected' : '' ?>>Aprobadas</option>
                                <option value="rechazada" <?= $estado === 'rechazada' ? 'selected' : '' ?>>Rechazadas</option>
                            </select>
                        </div>
                        <button type="button" onclick="location.href='reseñas.php'" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Limpiar filtros
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lista de reseñas -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-star"></i> Reseñas</h2>
                    <span class="text-muted">
                        Mostrando <?= count($pagedReviews) ?> de <?= $estadisticas['total'] ?> reseñas
                    </span>
                </div>
                <div class="card-content">
                    <?php if (!empty($pagedReviews)): ?>
                        <div class="reviews-grid">
                            <?php foreach ($pagedReviews as $resena): ?>
                                <?php $badge = getEstadoBadge($resena['estado']); ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <div class="reviewer-name">
                                                <i class="fas fa-user"></i>
                                                <?= htmlspecialchars($resena['nombre_reviewer']) ?>
                                            </div>
                                            <div class="reviewer-email">
                                                <i class="fas fa-envelope"></i>
                                                <?= htmlspecialchars($resena['email_reviewer']) ?>
                                            </div>
                                        </div>
                                        <div class="review-status">
                                            <span class="badge <?= $badge['class'] ?>"><?= $badge['text'] ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="review-rating">
                                        <?= renderStars($resena['rating']) ?>
                                        <span class="rating-number">(<?= $resena['rating'] ?>/5)</span>
                                    </div>
                                    
                                    <div class="review-content">
                                        <?= nl2br(htmlspecialchars($resena['contenido'])) ?>
                                    </div>
                                    
                                    <div class="review-meta">
                                        <div class="review-date">
                                            <i class="fas fa-calendar-alt"></i>
                                            <?= date('d/m/Y H:i', strtotime($resena['fecha_creacion'])) ?>
                                        </div>
                                        <?php if (!empty($resena['ip_address'])): ?>
                                            <div class="review-ip">
                                                <i class="fas fa-globe"></i>
                                                IP: <?= htmlspecialchars($resena['ip_address']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="review-actions">
                                        <?php if ($resena['estado'] === 'pendiente'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="review_id" value="<?= $resena['id'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Aprobar esta reseña?')">
                                                    <i class="fas fa-check"></i> Aprobar
                                                </button>
                                            </form>
                                            
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="review_id" value="<?= $resena['id'] ?>">
                                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('¿Rechazar esta reseña?')">
                                                    <i class="fas fa-times"></i> Rechazar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="review_id" value="<?= $resena['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta reseña permanentemente?')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Paginación -->
                        <?php if ($totalPages > 1): ?>
                            <nav class="pagination-nav">
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?estado=<?= urlencode($estado) ?>&page=<?= $page - 1 ?>">
                                                <i class="fas fa-chevron-left"></i> Anterior
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?estado=<?= urlencode($estado) ?>&page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?estado=<?= urlencode($estado) ?>&page=<?= $page + 1 ?>">
                                                Siguiente <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <h3>No hay reseñas</h3>
                            <p>No se encontraron reseñas con los filtros seleccionados.</p>
                            <a href="reseñas.php" class="btn btn-primary">
                                <i class="fas fa-redo"></i> Ver todas las reseñas
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
/* Estilos específicos para la administración de reseñas */
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

.bg-warning { background-color: #f59e0b; }
.bg-success { background-color: #10b981; }
.bg-danger { background-color: #ef4444; }
.bg-primary { background-color: #3b82f6; }

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

.filter-form {
    display: flex;
    gap: 1rem;
    align-items: end;
}

.filter-form .form-group {
    margin-bottom: 0;
}

.reviews-grid {
    display: grid;
    gap: 1.5rem;
    margin-top: 1rem;
}

.review-card {
    background: #f9fafb;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

.review-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.reviewer-name {
    font-weight: 600;
    font-size: 1.1rem;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.reviewer-email {
    font-size: 0.9rem;
    color: #6b7280;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.rating-number {
    font-weight: 600;
    color: #6b7280;
}

.review-content {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    line-height: 1.6;
    border-left: 4px solid #e5e7eb;
}

.review-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    font-size: 0.9rem;
    color: #6b7280;
}

.review-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
}

.badge-warning { background-color: #f59e0b; color: white; }
.badge-success { background-color: #10b981; color: white; }
.badge-danger { background-color: #ef4444; color: white; }
.badge-secondary { background-color: #6b7280; color: white; }

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.pagination-nav {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
}

.pagination {
    display: flex;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
    margin: 0;
}

.page-link {
    padding: 0.75rem 1rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #374151;
    text-decoration: none;
    transition: all 0.3s ease;
}

.page-link:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.page-item.active .page-link {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
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

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .review-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .review-actions {
        justify-content: center;
    }
    
    .filter-form {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<?php includeFooter(['admin.js']); ?>
