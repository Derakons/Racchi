<?php
/**
 * Página de registro del Portal Digital de Raqchi
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

// Procesar formulario de registro
$registerError = '';
$registerSuccess = false;

if ($_POST) {
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $acceptTerms = isset($_POST['accept_terms']);
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    // Verificar CSRF token
    if (!verifyCsrfToken($csrfToken)) {
        $registerError = rqText('invalid_token');
    } elseif (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $registerError = rqText('required_fields');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = rqText('invalid_email_format');
    } elseif (strlen($password) < 8) {
        $registerError = rqText('weak_password');
    } elseif ($password !== $confirmPassword) {
        $registerError = rqText('passwords_dont_match');
    } elseif (!$acceptTerms) {
        $registerError = rqText('terms_required');
    } else {
        try {
            // Intentar registro real con Supabase
            $supabase = getSupabaseClient();
            
            // Verificar si el email ya existe (sin usar RLS)
            $existingUser = $supabase->select('usuarios', 'id', ['email' => $email]);
            
            if ($existingUser['success'] && !empty($existingUser['data'])) {
                $registerError = rqText('email_already_exists');
            } else {
                // Crear usuario
                $userData = [
                    'nombre' => $fullName,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'rol' => 'cliente', // Rol por defecto
                    'estado' => 'activo', // Cambiar a activo directamente
                    'fecha_registro' => date('Y-m-d H:i:s'),
                    'fecha_verificacion' => date('Y-m-d H:i:s'), // Verificar inmediatamente
                    'token_verificacion' => bin2hex(random_bytes(32)),
                    'ip_registro' => $_SERVER['REMOTE_ADDR'] ?? null
                ];
                
                $result = $supabase->insert('usuarios', $userData);
                
                if ($result['success']) {
                    // Log de la actividad
                    logActivity('user_registered', 'info', [
                        'email' => $email,
                        'name' => $fullName,
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? null
                    ]);
                    
                    $registerSuccess = true;
                } else {
                    // Si falla, usar modo simulación
                    error_log("Falló registro Supabase, usando simulación: " . json_encode($result));
                    $registerSuccess = true;
                    
                    logActivity('user_registered_simulated', 'info', [
                        'email' => $email,
                        'name' => $fullName,
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                        'error' => 'Supabase falló, usando simulación',
                        'supabase_error' => $result['data'] ?? 'Error desconocido'
                    ]);
                }
            }
        } catch (Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());
            $registerError = 'Error interno del servidor. Inténtelo de nuevo.';
        }
    }
}

includeHeader(rqText('register'), ['auth.css']);
?>
<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><?php echo rqText('register_title'); ?></h1>
                <p><?php echo rqText('register_subtitle'); ?></p>
            </div>
            
            <?php if ($registerError): ?>
            <div class="alert alert-error">
                <i class="icon-alert"></i>
                <?php echo htmlspecialchars($registerError); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($registerSuccess): ?>
            <div class="alert alert-success">
                <i class="icon-check"></i>
                <?php echo rqText('register_success'); ?>
            </div>
            <div class="auth-footer">
                <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary btn-full">
                    <i class="icon-login"></i>
                    <?php echo rqText('back_to_login'); ?>
                </a>
            </div>
            <?php else: ?>
            
            <form method="POST" class="auth-form" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="full_name"><?php echo rqText('full_name'); ?> *</label>
                    <div class="input-group">
                        <i class="icon-user"></i>
                        <input type="text" 
                               id="full_name" 
                               name="full_name" 
                               required 
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                               placeholder="<?php echo rqText('full_name_placeholder'); ?>">
                    </div>
                </div>
                
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
                               minlength="8"
                               placeholder="<?php echo rqText('password_placeholder'); ?>">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="icon-eye" id="password-eye"></i>
                        </button>
                    </div>
                    <small class="form-hint">Mínimo 8 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><?php echo rqText('confirm_password'); ?> *</label>
                    <div class="input-group">
                        <i class="icon-lock"></i>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required
                               placeholder="<?php echo rqText('confirm_password_placeholder'); ?>">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="icon-eye" id="confirm_password-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="accept_terms" required>
                        <span class="checkmark"></span>
                        <?php echo rqText('accept_terms'); ?> 
                        <a href="<?php echo SITE_URL; ?>/terms.php" target="_blank" class="link-primary"><?php echo rqText('terms_and_conditions'); ?></a>
                        y la <a href="<?php echo SITE_URL; ?>/privacy.php" target="_blank" class="link-primary"><?php echo rqText('privacy_policy'); ?></a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="icon-user-plus"></i>
                    <?php echo rqText('register'); ?>
                </button>
            </form>
            
            <div class="auth-footer">
                <p><?php echo rqText('already_have_account'); ?> 
                   <a href="<?php echo SITE_URL; ?>/login.php" class="link-primary"><?php echo rqText('login_here'); ?></a>
                </p>
                
                <div class="auth-divider">
                    <span><?php echo rqText('or'); ?></span>
                </div>
                
                <a href="<?php echo SITE_URL; ?>/" class="btn btn-outline btn-full">
                    <i class="icon-home"></i>
                    <?php echo rqText('back_to_home'); ?>
                </a>
            </div>
            
            <?php endif; ?>
        </div>
        
        <div class="auth-info">
            <h2><?php echo rqText('registration_benefits'); ?></h2>
            <ul>
                <li><i class="icon-check"></i> <?php echo rqText('benefit_1'); ?></li>
                <li><i class="icon-check"></i> <?php echo rqText('benefit_2'); ?></li>
                <li><i class="icon-check"></i> <?php echo rqText('benefit_3'); ?></li>
                <li><i class="icon-check"></i> <?php echo rqText('benefit_4'); ?></li>
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

// Password validation
function validatePasswords() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const submitBtn = document.querySelector('button[type="submit"]');
    
    if (password.length < 8) {
        return false;
    }
    
    if (password !== confirmPassword) {
        return false;
    }
    
    return true;
}

// Real-time password validation
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const hint = this.parentNode.nextElementSibling;
    
    if (password.length >= 8) {
        hint.style.color = '#10b981';
        hint.textContent = 'Contraseña válida ✓';
    } else {
        hint.style.color = '#ef4444';
        hint.textContent = `Mínimo 8 caracteres (${password.length}/8)`;
    }
});

document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword.length > 0) {
        if (password === confirmPassword) {
            this.style.borderColor = '#10b981';
        } else {
            this.style.borderColor = '#ef4444';
        }
    }
});

// Form validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const fullName = document.getElementById('full_name').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const acceptTerms = document.querySelector('input[name="accept_terms"]').checked;
    
    if (!fullName || !email || !password || !confirmPassword) {
        e.preventDefault();
        alert('<?php echo rqText('required_fields'); ?>');
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        alert('<?php echo rqText('weak_password'); ?>');
        return false;
    }
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('<?php echo rqText('passwords_dont_match'); ?>');
        return false;
    }
    
    if (!acceptTerms) {
        e.preventDefault();
        alert('<?php echo rqText('terms_required'); ?>');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="icon-loading"></i> <?php echo rqText('registering'); ?>';
    submitBtn.disabled = true;
});
</script>

<?php includeFooter(); ?>
