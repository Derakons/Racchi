<?php
/**
 * Verificador de extensiones PHP requeridas
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/includes/bootstrap.php';
requireRole('admin');

$action = $_GET['action'] ?? '';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Extensiones PHP</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .ext-check {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .ext-status {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .ext-enabled { background: #28a745; }
        .ext-disabled { background: #dc3545; }
        .ext-optional { background: #ffc107; }
    </style>
</head>
<body>
    <div class="container" style="max-width: 800px; margin: 2rem auto; padding: 2rem;">
        <h1>Verificación de Extensiones PHP</h1>
        
        <div class="help-section" style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2>Estado de Extensiones Requeridas</h2>
            
            <?php
            $extensions = [
                'openssl' => ['required' => true, 'description' => 'Necesario para conexiones SMTP seguras (TLS/SSL)'],
                'sockets' => ['required' => false, 'description' => 'Mejora las pruebas de conectividad de red'],
                'curl' => ['required' => true, 'description' => 'Necesario para conexiones HTTP/HTTPS'],
                'mbstring' => ['required' => true, 'description' => 'Manejo de strings multibyte'],
                'json' => ['required' => true, 'description' => 'Procesamiento de datos JSON'],
                'mysqli' => ['required' => true, 'description' => 'Conexión a base de datos MySQL'],
                'pdo' => ['required' => true, 'description' => 'Interfaz de base de datos PDO'],
                'gd' => ['required' => false, 'description' => 'Procesamiento de imágenes'],
                'fileinfo' => ['required' => false, 'description' => 'Detección de tipos de archivo']
            ];
            
            foreach ($extensions as $ext => $info) {
                $loaded = extension_loaded($ext);
                $statusClass = $loaded ? 'ext-enabled' : ($info['required'] ? 'ext-disabled' : 'ext-optional');
                $statusText = $loaded ? 'Habilitada' : 'No disponible';
                $priorityText = $info['required'] ? 'Requerida' : 'Opcional';
                
                echo "<div class='ext-check'>";
                echo "<div class='ext-status $statusClass'></div>";
                echo "<div>";
                echo "<strong>$ext</strong> ($priorityText): $statusText<br>";
                echo "<small>{$info['description']}</small>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
        
        <div class="help-section" style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 1rem;">
            <h2>Información del Sistema PHP</h2>
            <p><strong>Versión PHP:</strong> <?php echo PHP_VERSION; ?></p>
            <p><strong>Configuración PHP:</strong> <?php echo php_ini_loaded_file(); ?></p>
            <p><strong>XAMPP detectado:</strong> <?php echo file_exists('C:/xampp/php/php.ini') ? 'Sí' : 'No'; ?></p>
        </div>
        
        <?php if (!extension_loaded('sockets')): ?>
        <div class="help-section" style="background: #fff3cd; padding: 1.5rem; border-radius: 10px; border: 1px solid #ffc107; margin-top: 1rem;">
            <h3>⚠️ Extensión Sockets no habilitada</h3>
            <p>La extensión sockets mejora las pruebas de conectividad, pero no es crítica para el funcionamiento del email.</p>
            
            <h4>Para habilitar en XAMPP:</h4>
            <ol>
                <li>Abra el archivo <code>C:\xampp\php\php.ini</code></li>
                <li>Busque la línea <code>;extension=sockets</code></li>
                <li>Elimine el punto y coma (;) al inicio de la línea</li>
                <li>Guarde el archivo y reinicie Apache</li>
            </ol>
            
            <p><strong>Nota:</strong> El sistema de email funcionará sin esta extensión, solo las pruebas de conectividad serán menos precisas.</p>
        </div>
        <?php endif; ?>
        
        <div class="help-section" style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 1rem;">
            <h2>Solución de Problemas de Email</h2>
            
            <h3>Problema identificado en el diagnóstico:</h3>
            <ul>
                <li><strong>PHPMailer no instalado:</strong> Use el <a href="install_phpmailer.php">instalador automático</a></li>
                <li><strong>Conectividad Gmail limitada:</strong> Puede ser por firewall o configuración de red</li>
                <li><strong>Outlook funciona:</strong> Pruebe con smtp-mail.outlook.com si gmail falla</li>
            </ul>
            
            <h3>Configuraciones alternativas recomendadas:</h3>
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
                <h4>Para Outlook/Hotmail (Más confiable):</h4>
                <ul>
                    <li>Host: smtp-mail.outlook.com</li>
                    <li>Puerto: 587</li>
                    <li>Seguridad: TLS habilitado</li>
                    <li>Usuario: su_email@outlook.com</li>
                    <li>Contraseña: contraseña normal de la cuenta</li>
                </ul>
            </div>
            
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
                <h4>Para Gmail (Requiere configuración especial):</h4>
                <ul>
                    <li>Host: smtp.gmail.com</li>
                    <li>Puerto: 587</li>
                    <li>Seguridad: TLS habilitado</li>
                    <li>Usuario: su_email@gmail.com</li>
                    <li>Contraseña: <strong>Contraseña de aplicación</strong> (no la normal)</li>
                </ul>
                <p><small>Para Gmail: Configurar > Seguridad > Verificación en 2 pasos > Contraseñas de aplicaciones</small></p>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="admin/configuracion.php" class="btn btn-primary">Volver a Configuración</a>
            <a href="email_help.php" class="btn btn-secondary">Ayuda Completa</a>
        </div>
    </div>
</body>
</html>
