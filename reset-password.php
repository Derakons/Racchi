<?php
/**
 * Página de reset de contraseña del Portal Digital de Raqchi
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
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

$resetError = '';
$resetSuccess = false;

// Verificar si es un enlace de reset válido
$token = $_GET['token'] ?? '';
$isResetForm = !empty($token);

if ($_POST) {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    // Verificar CSRF token
    if (!verifyCsrfToken($csrfToken)) {
        $resetError = rqText('invalid_token');
    } elseif ($isResetForm) {
        // Procesar reset de contraseña
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($newPassword) || empty($confirmPassword)) {
            $resetError = rqText('required_fields');
        } elseif (strlen($newPassword) < 8) {
            $resetError = rqText('weak_password');
        } elseif ($newPassword !== $confirmPassword) {
            $resetError = rqText('passwords_dont_match');
        } else {
            try {
                $supabase = getSupabaseClient();
                
                // Verificar token válido
                $userResult = $supabase->select('usuarios', '*', [
                    'token_reset' => $token,
                    'estado' => 'activo'
                ]);
                
                if ($userResult['success'] && !empty($userResult['data'])) {
                    $user = $userResult['data'][0];
                    
                    // Verificar que el token no haya expirado (24 horas)
                    $tokenTime = strtotime($user['fecha_token_reset']);
                    if ($tokenTime && (time() - $tokenTime) <= 86400) {
                        // Actualizar contraseña
                        $updateResult = $supabase->update('usuarios', [
                            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                            'token_reset' => null,
                            'fecha_token_reset' => null,
                            'ultimo_cambio_password' => date('Y-m-d H:i:s')
                        ], ['id' => $user['id']]);
                        
                        if ($updateResult['success']) {
                            logActivity('password_reset', [
                                'user_id' => $user['id'],
                                'email' => $user['email'],
                                'ip' => $_SERVER['REMOTE_ADDR'] ?? null
                            ]);
                            
                            $resetSuccess = true;
                        } else {
                            throw new Exception('Error al actualizar la contraseña');
                        }
                    } else {
                        $resetError = 'El enlace de restablecimiento ha expirado';
                    }
                } else {
                    $resetError = 'Enlace de restablecimiento inválido';
                }
            } catch (Exception $e) {
                error_log("Error en reset de contraseña: " . $e->getMessage());
                $resetError = 'Error interno del servidor';
            }
        }
    } else {
        // Procesar solicitud de reset
        $email = sanitizeInput($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $resetError = 'Correo electrónico inválido';
        } else {
            try {
                $supabase = getSupabaseClient();
                
                // Buscar usuario por email
                $userResult = $supabase->select('usuarios', '*', [
                    'email' => $email,
                    'estado' => 'activo'
                ]);
                
                if ($userResult['success'] && !empty($userResult['data'])) {
                    $user = $userResult['data'][0];
                    
                    // Generar token de reset
                    $resetToken = bin2hex(random_bytes(32));
                    
                    $updateResult = $supabase->update('usuarios', [
                        'token_reset' => $resetToken,
                        'fecha_token_reset' => date('Y-m-d H:i:s')
                    ], ['id' => $user['id']]);
                    
                    if ($updateResult['success']) {
                        // En una implementación real, enviar email con el enlace
                        // $resetLink = SITE_URL . '/reset-password.php?token=' . $resetToken;
                        // sendResetEmail($email, $resetLink);
                        
                        logActivity('password_reset_requested', [
                            'email' => $email,
                            'ip' => $_SERVER['REMOTE_ADDR'] ?? null
                        ]);
                        
                        $resetSuccess = true;
                    } else {
                        throw new Exception('Error al generar token de reset');
                    }
                } else {
                    // Por seguridad, mostrar éxito aunque el email no exista
                    $resetSuccess = true;
                }
            } catch (Exception $e) {
                error_log("Error en solicitud de reset: " . $e->getMessage());
                $resetError = 'Error interno del servidor';
            }
        }
    }
}

includeHeader(rqText('reset_password'), ['auth.css']);
?>

<main class="auth-main">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo rqText('site_name'); ?>">
                </div>
                <h1><?php echo rqText('reset_password_title'); ?></h1>
                <p><?php echo $isResetForm ? 'Ingresa tu nueva contraseña' : rqText('reset_password_subtitle'); ?></p>
            </div>
            
            <?php if ($resetError): ?>
            <div class="alert alert-error">
                <i class="icon-alert"></i>
                <?php echo htmlspecialchars($resetError); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($resetSuccess): ?>
            <div class="alert alert-success">
                <i class="icon-check"></i>
                <?php echo $isResetForm ? rqText('password_reset_success') : rqText('reset_link_sent'); ?>
            </div>
            <div class="auth-footer">
                <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary btn-full">
                    <i class="icon-login"></i>
                    <?php echo rqText('back_to_login'); ?>
                </a>
            </div>
            <?php else: ?>
            
            <form method="POST" class="auth-form" id="resetForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <?php if ($isResetForm): ?>
                <!-- Formulario de nueva contraseña -->
                <div class="form-group">
                    <label for="new_password"><?php echo rqText('new_password'); ?> *</label>
                    <div class="input-group">
                        <i class="icon-lock"></i>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               required
                               minlength="8"
                               placeholder="<?php echo rqText('new_password_placeholder'); ?>">
                        <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                            <i class="icon-eye" id="new_password-eye"></i>
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
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="icon-check"></i>
                    <?php echo rqText('reset_password'); ?>
                </button>
                
                <?php else: ?>
                <!-- Formulario de solicitud de reset -->
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
                
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="icon-send"></i>
                    <?php echo rqText('send_reset_link'); ?>
                </button>
                
                <?php endif; ?>
            </form>
            
            <div class="auth-footer">
                <a href="<?php echo SITE_URL; ?>/login.php" class="link-primary">
                    <i class="icon-arrow-left"></i>
                    <?php echo rqText('back_to_login'); ?>
                </a>
                
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
            <h2><?php echo rqText('need_help'); ?></h2>
            <ul>
                <li><i class="icon-check"></i> Verifica tu correo electrónico</li>
                <li><i class="icon-check"></i> El enlace expira en 24 horas</li>
                <li><i class="icon-check"></i> Revisa tu carpeta de spam</li>
            </ul>
            
            <div class="contact-support">
                <h3><?php echo rqText('contact_support'); ?></h3>
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

// Password validation for reset form
<?php if ($isResetForm): ?>
document.getElementById('new_password').addEventListener('input', function() {
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
    const password = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword.length > 0) {
        if (password === confirmPassword) {
            this.style.borderColor = '#10b981';
        } else {
            this.style.borderColor = '#ef4444';
        }
    }
});
<?php endif; ?>

// Form validation
document.getElementById('resetForm').addEventListener('submit', function(e) {
    <?php if ($isResetForm): ?>
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (!newPassword || !confirmPassword) {
        e.preventDefault();
        alert('<?php echo rqText('required_fields'); ?>');
        return false;
    }
    
    if (newPassword.length < 8) {
        e.preventDefault();
        alert('<?php echo rqText('weak_password'); ?>');
        return false;
    }
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('<?php echo rqText('passwords_dont_match'); ?>');
        return false;
    }
    <?php else: ?>
    const email = document.getElementById('email').value;
    
    if (!email) {
        e.preventDefault();
        alert('El correo electrónico es requerido');
        return false;
    }
    <?php endif; ?>
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="icon-loading"></i> Procesando...';
    submitBtn.disabled = true;
});
</script>

<?php includeFooter(); ?>
