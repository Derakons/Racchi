<?php
/**
 * Página de ayuda para solución de problemas de email
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/includes/bootstrap.php';

// Solo administradores pueden acceder
requireRole('admin');

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayuda - Configuración de Email</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .help-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .help-section {
            background: white;
            margin: 1rem 0;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .provider-config {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 1rem;
            margin: 1rem 0;
        }
        .config-table {
            width: 100%;
            border-collapse: collapse;
        }
        .config-table th,
        .config-table td {
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            text-align: left;
        }
        .config-table th {
            background: #e9ecef;
            font-weight: bold;
        }
        .diagnostic-btn {
            margin: 0.5rem;
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        .status-ok { background: #28a745; }
        .status-warning { background: #ffc107; }
        .status-error { background: #dc3545; }
    </style>
</head>
<body>
    <div class="help-container">
        <h1>🛠️ Ayuda - Configuración de Email</h1>
        
        <!-- Diagnóstico del sistema -->
        <div class="help-section">
            <h2>🔍 Diagnóstico del Sistema</h2>
            <p>Ejecute estas pruebas para identificar problemas:</p>
            
            <button class="btn btn-primary diagnostic-btn" onclick="runDiagnostic()">
                <i class="fas fa-stethoscope"></i> Ejecutar Diagnóstico
            </button>
            
            <button class="btn btn-secondary diagnostic-btn" onclick="testConnectivity()">
                <i class="fas fa-network-wired"></i> Probar Conectividad
            </button>
            
            <a href="diagnose_system.php" target="_blank" class="btn btn-info diagnostic-btn">
                <i class="fas fa-external-link-alt"></i> Diagnóstico Técnico
            </a>
            
            <div id="diagnosticResults" style="margin-top: 1rem;"></div>
        </div>
        
        <!-- Configuraciones por proveedor -->
        <div class="help-section">
            <h2>📧 Configuraciones por Proveedor</h2>
            
            <div class="provider-config">
                <h3>Gmail</h3>
                <table class="config-table">
                    <tr><th>Host SMTP</th><td>smtp.gmail.com</td></tr>
                    <tr><th>Puerto</th><td>587 (TLS) o 465 (SSL)</td></tr>
                    <tr><th>Seguridad</th><td>TLS/SSL habilitado</td></tr>
                    <tr><th>Usuario</th><td>su_email@gmail.com</td></tr>
                    <tr><th>Contraseña</th><td>Contraseña de aplicación (no la de la cuenta)</td></tr>
                </table>
                <p><strong>Nota importante:</strong> Gmail requiere contraseñas de aplicación. Vaya a su cuenta de Google > Seguridad > Verificación en 2 pasos > Contraseñas de aplicaciones.</p>
            </div>
            
            <div class="provider-config">
                <h3>Outlook/Hotmail</h3>
                <table class="config-table">
                    <tr><th>Host SMTP</th><td>smtp-mail.outlook.com</td></tr>
                    <tr><th>Puerto</th><td>587</td></tr>
                    <tr><th>Seguridad</th><td>TLS habilitado</td></tr>
                    <tr><th>Usuario</th><td>su_email@outlook.com</td></tr>
                    <tr><th>Contraseña</th><td>Contraseña de la cuenta</td></tr>
                </table>
            </div>
            
            <div class="provider-config">
                <h3>Yahoo</h3>
                <table class="config-table">
                    <tr><th>Host SMTP</th><td>smtp.mail.yahoo.com</td></tr>
                    <tr><th>Puerto</th><td>587</td></tr>
                    <tr><th>Seguridad</th><td>TLS habilitado</td></tr>
                    <tr><th>Usuario</th><td>su_email@yahoo.com</td></tr>
                    <tr><th>Contraseña</th><td>Contraseña de aplicación</td></tr>
                </table>
            </div>
        </div>
        
        <!-- Problemas comunes -->
        <div class="help-section">
            <h2>⚠️ Problemas Comunes y Soluciones</h2>
            
            <h3>Error: "No se pudo conectar al servidor"</h3>
            <ul>
                <li>Verifique que XAMPP esté ejecutándose</li>
                <li>Revise su conexión a internet</li>
                <li>Compruebe que el firewall no bloquee la conexión</li>
                <li>Pruebe con diferentes puertos (587, 465, 25)</li>
            </ul>
            
            <h3>Error: "Autenticación fallida"</h3>
            <ul>
                <li>Para Gmail: Use contraseña de aplicación, no la contraseña normal</li>
                <li>Verifique que el usuario sea un email válido</li>
                <li>Asegúrese de que la contraseña sea correcta</li>
                <li>Para algunos proveedores, habilite "aplicaciones menos seguras"</li>
            </ul>
            
            <h3>Error: "PHPMailer no está instalado"</h3>
            <ul>
                <li>Use el <a href="install_phpmailer.php">instalador automático</a></li>
                <li>O instale manualmente con: <code>composer require phpmailer/phpmailer</code></li>
            </ul>
            
            <h3>El email se envía pero no llega</h3>
            <ul>
                <li>Revise la carpeta de spam/correo no deseado</li>
                <li>Verifique que el email de origen esté verificado</li>
                <li>Compruebe los límites de envío del proveedor</li>
            </ul>
        </div>
        
        <!-- Logs y depuración -->
        <div class="help-section">
            <h2>📋 Logs y Depuración</h2>
            
            <p>Los logs del sistema se guardan en:</p>
            <ul>
                <li><code>logs/admin_activity.log</code> - Actividad de administrador</li>
                <li><code>logs/user_activity.log</code> - Actividad de usuarios</li>
                <li>PHP Error Log - Verifique la configuración de PHP</li>
            </ul>
            
            <button class="btn btn-info diagnostic-btn" onclick="viewLogs()">
                <i class="fas fa-file-alt"></i> Ver Logs Recientes
            </button>
            
            <a href="check_php_extensions.php" target="_blank" class="btn btn-warning diagnostic-btn">
                <i class="fas fa-cogs"></i> Verificar Extensiones PHP
            </a>
        </div>
        
        <!-- Testing avanzado -->
        <div class="help-section">
            <h2>🧪 Pruebas Avanzadas</h2>
            
            <p>Para pruebas más detalladas:</p>
            
            <a href="test_email_simple.php" target="_blank" class="btn btn-secondary">
                <i class="fas fa-flask"></i> Prueba Simple de Email
            </a>
            
            <a href="admin/configuracion.php" class="btn btn-primary">
                <i class="fas fa-cog"></i> Volver a Configuración
            </a>
        </div>
    </div>

    <script>
        async function runDiagnostic() {
            const resultsDiv = document.getElementById('diagnosticResults');
            resultsDiv.innerHTML = '<div class="alert alert-info">Ejecutando diagnóstico...</div>';
            
            try {
                const response = await fetch('<?php echo SITE_URL; ?>/diagnose_system.php');
                const data = await response.json();
                
                let html = '<h4>Resultados del Diagnóstico:</h4>';
                
                // Estado de archivos
                html += '<h5>📁 Archivos del sistema:</h5><ul>';
                Object.entries(data.files_check).forEach(([file, exists]) => {
                    const status = exists ? 'status-ok' : 'status-error';
                    html += `<li><span class="status-indicator ${status}"></span>${file}: ${exists ? 'OK' : 'No encontrado'}</li>`;
                });
                html += '</ul>';
                
                // Extensiones de PHP
                html += '<h5>🔧 Extensiones de PHP:</h5><ul>';
                Object.entries(data.extensions_check).forEach(([ext, loaded]) => {
                    const status = loaded ? 'status-ok' : 'status-error';
                    html += `<li><span class="status-indicator ${status}"></span>${ext}: ${loaded ? 'Cargada' : 'No disponible'}</li>`;
                });
                html += '</ul>';
                
                // Conectividad de red
                html += '<h5>🌐 Conectividad de red:</h5><ul>';
                Object.entries(data.network_check).forEach(([host, result]) => {
                    const status = result.status === 'success' ? 'status-ok' : 'status-error';
                    html += `<li><span class="status-indicator ${status}"></span>${host}: ${result.status === 'success' ? `OK (${result.time})` : `Error: ${result.error}`}</li>`;
                });
                html += '</ul>';
                
                resultsDiv.innerHTML = `<div class="alert alert-success">${html}</div>`;
                
            } catch (error) {
                resultsDiv.innerHTML = `<div class="alert alert-danger">Error en diagnóstico: ${error.message}</div>`;
            }
        }
        
        async function testConnectivity() {
            const resultsDiv = document.getElementById('diagnosticResults');
            resultsDiv.innerHTML = '<div class="alert alert-info">Probando conectividad SMTP...</div>';
            
            const hosts = [
                { host: 'smtp.gmail.com', port: 587 },
                { host: 'smtp.gmail.com', port: 465 },
                { host: 'smtp-mail.outlook.com', port: 587 }
            ];
            
            let html = '<h4>Pruebas de Conectividad SMTP:</h4><ul>';
            
            for (const config of hosts) {
                try {
                    const response = await fetch('test_connection.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(config)
                    });
                    
                    const result = await response.json();
                    const status = result.success ? 'status-ok' : 'status-error';
                    html += `<li><span class="status-indicator ${status}"></span>${config.host}:${config.port} - ${result.message}</li>`;
                    
                } catch (error) {
                    html += `<li><span class="status-indicator status-error"></span>${config.host}:${config.port} - Error: ${error.message}</li>`;
                }
            }
            
            html += '</ul>';
            resultsDiv.innerHTML = `<div class="alert alert-info">${html}</div>`;
        }
        
        function viewLogs() {
            // Implementar visualización de logs
            alert('Funcionalidad de logs en desarrollo. Por ahora, revise manualmente la carpeta logs/');
        }
        
        // Agregar estilos para las alertas
        const style = document.createElement('style');
        style.textContent = `
            .alert { padding: 1rem; margin: 1rem 0; border-radius: 5px; }
            .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
            .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
