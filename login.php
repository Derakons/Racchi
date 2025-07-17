<?php
/**
 * Página de login del Portal Digital de Raqchi
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Si ya está logueado, redirigir al dashboard apropiado
if (isLoggedIn()) {
    if (hasRole('admin')) {
        header('Location: ' . SITE_URL . '/admin/');
        exit;
    } elseif (hasRole('vendedor')) {
        header('Location: ' . SITE_URL . '/taquilla/');
        exit;
    } else {
        header('Location: ' . SITE_URL . '/');
        exit;
    }
}

// Procesar formulario de login
$loginError = '';
if ($_POST) {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    // Verificar CSRF token
    if (!verifyCsrfToken($csrfToken)) {
        $loginError = rqText('invalid_token');
    } elseif (empty($email) || empty($password)) {
        $loginError = rqText('required_fields');
    } else {
        // Intentar login
        $supabase = getSupabaseClient();
        
        // Buscar usuario por email
        $userResult = $supabase->select('usuarios', '*', ['email' => $email]);
        
        if ($userResult['success'] && !empty($userResult['data'])) {
            $user = $userResult['data'][0];
            
            // Verificar contraseña
            if (password_verify($password, $user['password'])) {
                // Verificar que el usuario esté activo
                if ($user['estado'] === 'activo') {
                    // Iniciar sesión
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_role'] = $user['rol'];
                    
                    // Registrar último login
                    $supabase->update('usuarios', 
                        ['ultimo_login' => date('Y-m-d H:i:s')], 
                        ['id' => $user['id']]
                    );
                    
                    // Redirigir según rol
                    if ($user['rol'] === 'admin') {
                        header('Location: ' . SITE_URL . '/admin/');
                        exit;
                    } elseif ($user['rol'] === 'vendedor') {
                        header('Location: ' . SITE_URL . '/taquilla/');
                        exit;
                    } else {
                        header('Location: ' . SITE_URL . '/');
                        exit;
                    }
                } else {
                    $loginError = rqText('account_inactive');
                }
            } else {
                $loginError = rqText('invalid_credentials');
            }
        } else {
            $loginError = rqText('invalid_credentials');
        }
    }
}

includeHeader(rqText('login'), ['auth.css']);
?>

<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo rqText('site_name'); ?>">
                </div>
                <h1><?php echo rqText('login'); ?></h1>
                <p><?php echo rqText('login_subtitle'); ?></p>
            </div>
            
            <?php if ($loginError): ?>
            <div class="alert alert-error">
                <i class="icon-alert"></i>
                <?php echo htmlspecialchars($loginError); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="email"><?php echo rqText('email'); ?> *</label>
                    <div class="input-group">
                        <i class="icon-email"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               placeholder="<?php echo rqText('email_placeholder'); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password"><?php echo rqText('password'); ?> *</label>
                    <div class="input-group">
                        <i class="icon-lock"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               placeholder="<?php echo rqText('password_placeholder'); ?>">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="icon-eye" id="password-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me">
                        <span class="checkmark"></span>
                        <?php echo rqText('remember_me'); ?>
                    </label>
                    
                    <a href="<?php echo SITE_URL; ?>/reset-password.php" class="link-secondary">
                        <?php echo rqText('forgot_password'); ?>
                    </a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="icon-login"></i>
                    <?php echo rqText('login'); ?>
                </button>
            </form>
            
            <div class="auth-footer">
                <p><?php echo rqText('no_account'); ?> 
                   <a href="<?php echo SITE_URL; ?>/register.php" class="link-primary"><?php echo rqText('register_here'); ?></a>
                </p>
                
                <div class="auth-divider">
                    <span><?php echo rqText('or'); ?></span>
                </div>
                
                <a href="<?php echo SITE_URL; ?>/" class="btn btn-outline btn-full">
                    <i class="icon-home"></i>
                    <?php echo rqText('back_to_home'); ?>
                </a>
            </div>
        </div>
        
        <div class="auth-info">
            <h2><?php echo rqText('portal_access'); ?></h2>
            <ul>
                <li><i class="icon-check"></i> <?php echo rqText('admin_access_desc'); ?></li>
                <li><i class="icon-check"></i> <?php echo rqText('vendor_access_desc'); ?></li>
                <li><i class="icon-check"></i> <?php echo rqText('secure_platform'); ?></li>
            </ul>
            
            <div class="contact-support">
                <h3><?php echo rqText('need_help'); ?></h3>
                <p><?php echo rqText('contact_support_desc'); ?></p>
                <a href="mailto:support@raqchi.com" class="btn btn-outline btn-compact">
                    <i class="icon-email"></i>
                    <?php echo rqText('contact_support'); ?>
                </a>
            </div>
        </div>
    </div>
</main>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const eye = document.getElementById(fieldId + '-eye');
    
    if (field.type === 'password') {
        field.type = 'text';
        eye.className = 'icon-eye-off';
    } else {
        field.type = 'password';
        eye.className = 'icon-eye';
    }
}

// Form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        alert('<?php echo rqText('required_fields'); ?>');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="icon-loading"></i> <?php echo rqText('logging_in'); ?>';
    submitBtn.disabled = true;
});
</script>

<?php includeFooter(); ?>
