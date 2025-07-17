<?php
/**
 * Configuración - Admin
 */

require_once __DIR__ . '/../includes/bootstrap.php';
requireRole('admin');
includeAdminHeader(rqText('settings') . ' - ' . rqText('admin'), ['admin.css'], ['admin.js']);
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
                <li class="nav-item"><a href="<?php echo SITE_URL; ?>/admin/reportes.php"><i class="fas fa-chart-bar"></i><span><?php echo rqText('reports'); ?></span></a></li>
                <li class="nav-item active"><a href="<?php echo SITE_URL; ?>/admin/configuracion.php"><i class="fas fa-cog"></i><span><?php echo rqText('settings'); ?></span></a></li>
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
                <h1><?php echo rqText('settings'); ?></h1>
            </div>
        </header>
        
        <div style="padding: var(--spacing-lg);">
            <!-- Configuración General -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-cog"></i> <?php echo rqText('general_settings'); ?></h2>
                </div>
                <div class="card-content">
                    <form class="settings-form" id="generalSettings">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="site_name"><?php echo rqText('site_name'); ?></label>
                                <input type="text" id="site_name" name="site_name" value="Portal Digital Raqchi" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="site_description"><?php echo rqText('site_description'); ?></label>
                                <textarea id="site_description" name="site_description" class="form-control" rows="3"><?php echo rqText('site_description_text'); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="contact_email"><?php echo rqText('contact_email'); ?></label>
                                <input type="email" id="contact_email" name="contact_email" value="info@raqchi.pe" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="contact_phone"><?php echo rqText('contact_phone'); ?></label>
                                <input type="tel" id="contact_phone" name="contact_phone" value="+51 984 123 456" class="form-control">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo rqText('save_changes'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configuración de Tickets -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-ticket-alt"></i> <?php echo rqText('ticket_settings'); ?></h2>
                </div>
                <div class="card-content">
                    <form class="settings-form" id="ticketSettings">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="adult_price"><?php echo rqText('adult_price'); ?></label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" id="adult_price" name="adult_price" value="15" class="form-control" step="0.01">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="student_price"><?php echo rqText('student_price'); ?></label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" id="student_price" name="student_price" value="8" class="form-control" step="0.01">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="child_price"><?php echo rqText('child_price'); ?></label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" id="child_price" name="child_price" value="5" class="form-control" step="0.01">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="daily_capacity"><?php echo rqText('daily_capacity'); ?></label>
                                <input type="number" id="daily_capacity" name="daily_capacity" value="500" class="form-control">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo rqText('save_changes'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configuración de Idiomas -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-globe"></i> <?php echo rqText('language_settings'); ?></h2>
                </div>
                <div class="card-content">
                    <form class="settings-form" id="languageSettings">
                        <div class="form-group">
                            <label><?php echo rqText('available_languages'); ?></label>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="languages[]" value="es" checked>
                                    <span class="checkmark"></span>
                                    <span><?php echo rqText('spanish'); ?></span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="languages[]" value="en" checked>
                                    <span class="checkmark"></span>
                                    <span><?php echo rqText('english'); ?></span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="languages[]" value="qu">
                                    <span class="checkmark"></span>
                                    <span><?php echo rqText('quechua'); ?></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="default_language"><?php echo rqText('default_language'); ?></label>
                            <select id="default_language" name="default_language" class="form-control">
                                <option value="es" selected><?php echo rqText('spanish'); ?></option>
                                <option value="en"><?php echo rqText('english'); ?></option>
                                <option value="qu"><?php echo rqText('quechua'); ?></option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo rqText('save_changes'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configuración de Pagos -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-credit-card"></i> <?php echo rqText('payment_settings'); ?></h2>
                </div>
                <div class="card-content">
                    <form class="settings-form" id="paymentSettings">
                        <div class="form-group">
                            <label><?php echo rqText('payment_methods'); ?></label>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="payment_methods[]" value="cash" checked>
                                    <span class="checkmark"></span>
                                    <span><?php echo rqText('cash'); ?></span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="payment_methods[]" value="card" checked>
                                    <span class="checkmark"></span>
                                    <span><?php echo rqText('credit_debit_card'); ?></span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="payment_methods[]" value="yape" checked>
                                    <span class="checkmark"></span>
                                    <span>Yape</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="payment_methods[]" value="plin" checked>
                                    <span class="checkmark"></span>
                                    <span>Plin</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="yape_number"><?php echo rqText('yape_number'); ?></label>
                                <input type="tel" id="yape_number" name="yape_number" value="984 123 456" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="plin_number"><?php echo rqText('plin_number'); ?></label>
                                <input type="tel" id="plin_number" name="plin_number" value="984 123 456" class="form-control">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo rqText('save_changes'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configuración de Email -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-envelope"></i> <?php echo rqText('email_settings'); ?></h2>
                </div>
                <div class="card-content">
                    <form class="settings-form" id="emailSettings">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="smtp_host"><?php echo rqText('smtp_host'); ?></label>
                                <input type="text" id="smtp_host" name="smtp_host" value="smtp.gmail.com" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="smtp_port"><?php echo rqText('smtp_port'); ?></label>
                                <input type="number" id="smtp_port" name="smtp_port" value="587" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="smtp_username"><?php echo rqText('smtp_username'); ?></label>
                                <input type="email" id="smtp_username" name="smtp_username" value="noreply@raqchi.pe" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="smtp_password"><?php echo rqText('smtp_password'); ?></label>
                                <input type="password" id="smtp_password" name="smtp_password" value="••••••••" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="smtp_secure" checked>
                                    <span class="checkmark"></span>
                                    <span><?php echo rqText('use_secure_connection'); ?></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo rqText('save_changes'); ?>
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="testEmailConfig()">
                                <i class="fas fa-paper-plane"></i> <?php echo rqText('test_email'); ?>
                            </button>
                            <a href="<?php echo SITE_URL; ?>/install_phpmailer.php" class="btn btn-outline" target="_blank">
                                <i class="fas fa-download"></i> Instalar PHPMailer
                            </a>
                            <a href="<?php echo SITE_URL; ?>/email_help.php" class="btn btn-info" target="_blank">
                                <i class="fas fa-question-circle"></i> Ayuda
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Configuración de Mantenimiento -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-tools"></i> <?php echo rqText('maintenance_settings'); ?></h2>
                </div>
                <div class="card-content">
                    <form class="settings-form" id="maintenanceSettings">
                        <div class="form-group">
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="maintenance_mode">
                                    <span class="checkmark"></span>
                                    <span><?php echo rqText('enable_maintenance_mode'); ?></span>
                                </label>
                            </div>
                            <small class="form-text"><?php echo rqText('maintenance_mode_description'); ?></small>
                        </div>
                        <div class="form-group">
                            <label for="maintenance_message"><?php echo rqText('maintenance_message'); ?></label>
                            <textarea id="maintenance_message" name="maintenance_message" class="form-control" rows="3"><?php echo rqText('default_maintenance_message'); ?></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo rqText('save_changes'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Acciones del Sistema -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-database"></i> <?php echo rqText('system_actions'); ?></h2>
                </div>
                <div class="card-content">
                    <div class="action-buttons">
                        <button type="button" class="btn btn-info" onclick="clearCache()">
                            <i class="fas fa-broom"></i> <?php echo rqText('clear_cache'); ?>
                        </button>
                        <button type="button" class="btn btn-warning" onclick="exportData()">
                            <i class="fas fa-download"></i> <?php echo rqText('export_data'); ?>
                        </button>
                        <button type="button" class="btn btn-success" onclick="backupDatabase()">
                            <i class="fas fa-shield-alt"></i> <?php echo rqText('backup_database'); ?>
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmSystemReset()">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo rqText('reset_system'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php includeFooter(['admin.js']); ?>
