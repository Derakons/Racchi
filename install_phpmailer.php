<?php
/**
 * Script para instalar PHPMailer automáticamente
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/includes/bootstrap.php';

// Solo administradores pueden ejecutar esto
requireRole('admin');

$action = $_GET['action'] ?? '';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador de PHPMailer - Portal Raqchi</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .installer-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .step {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
            border-left: 4px solid #007bff;
            background: #f8f9fa;
        }
        .success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .code-block {
            background: #f1f3f4;
            padding: 1rem;
            border-radius: 5px;
            font-family: monospace;
            margin: 0.5rem 0;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <h1>Instalador de PHPMailer para Portal Raqchi</h1>
        
        <?php if ($action === 'install'): ?>
            <h2>Ejecutando instalación...</h2>
            <?php
            $output = [];
            $returnCode = 0;
            
            // Verificar si Composer está instalado
            exec('composer --version 2>&1', $composerCheck, $composerReturn);
            
            if ($composerReturn !== 0) {
                echo '<div class="step error">';
                echo '<h3>❌ Composer no encontrado</h3>';
                echo '<p>Composer no está instalado o no está en el PATH del sistema.</p>';
                echo '<h4>Para instalar Composer:</h4>';
                echo '<ol>';
                echo '<li>Descargue Composer desde <a href="https://getcomposer.org/download/" target="_blank">getcomposer.org</a></li>';
                echo '<li>Instale siguiendo las instrucciones para su sistema operativo</li>';
                echo '<li>Asegúrese de que esté en el PATH del sistema</li>';
                echo '</ol>';
                echo '</div>';
            } else {
                echo '<div class="step success">';
                echo '<h3>✅ Composer encontrado</h3>';
                echo '<div class="code-block">' . implode("\n", $composerCheck) . '</div>';
                echo '</div>';
                
                // Verificar si existe composer.json
                $composerJsonPath = __DIR__ . '/composer.json';
                if (!file_exists($composerJsonPath)) {
                    echo '<div class="step warning">';
                    echo '<h3>⚠️ Creando composer.json</h3>';
                    
                    $composerConfig = [
                        'name' => 'raqchi/portal',
                        'description' => 'Portal Digital Raqchi',
                        'type' => 'project',
                        'require' => [
                            'phpmailer/phpmailer' => '^6.8'
                        ]
                    ];
                    
                    file_put_contents($composerJsonPath, json_encode($composerConfig, JSON_PRETTY_PRINT));
                    echo '<p>composer.json creado correctamente</p>';
                    echo '</div>';
                }
                
                // Ejecutar composer install
                echo '<div class="step">';
                echo '<h3>📦 Instalando PHPMailer...</h3>';
                
                $currentDir = getcwd();
                chdir(__DIR__);
                
                exec('composer require phpmailer/phpmailer 2>&1', $output, $returnCode);
                
                chdir($currentDir);
                
                if ($returnCode === 0) {
                    echo '<div class="step success">';
                    echo '<h3>✅ PHPMailer instalado correctamente</h3>';
                    echo '<div class="code-block">' . implode("\n", $output) . '</div>';
                    echo '</div>';
                    
                    // Verificar instalación
                    $autoloadPath = __DIR__ . '/vendor/autoload.php';
                    if (file_exists($autoloadPath)) {
                        require_once $autoloadPath;
                        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                            echo '<div class="step success">';
                            echo '<h3>✅ Verificación exitosa</h3>';
                            echo '<p>PHPMailer está disponible y listo para usar.</p>';
                            echo '<p><a href="admin/configuracion.php" class="btn btn-primary">Ir a Configuración de Email</a></p>';
                            echo '</div>';
                        }
                    }
                } else {
                    echo '<div class="step error">';
                    echo '<h3>❌ Error en la instalación</h3>';
                    echo '<div class="code-block">' . implode("\n", $output) . '</div>';
                    echo '</div>';
                }
            }
            ?>
            
        <?php else: ?>
            <div class="step">
                <h2>Estado actual del sistema</h2>
                
                <?php
                $phpmailerPath = __DIR__ . '/vendor/autoload.php';
                $hasComposer = file_exists($phpmailerPath);
                
                if ($hasComposer) {
                    require_once $phpmailerPath;
                    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
                        echo '<div class="step success">';
                        echo '<h3>✅ PHPMailer ya está instalado</h3>';
                        echo '<p>PHPMailer está disponible y listo para usar.</p>';
                        echo '<p><a href="admin/configuracion.php" class="btn btn-primary">Ir a Configuración de Email</a></p>';
                        echo '</div>';
                    } else {
                        echo '<div class="step warning">';
                        echo '<h3>⚠️ Autoload encontrado pero PHPMailer no disponible</h3>';
                        echo '<p>Es posible que necesite reinstalar las dependencias.</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="step error">';
                    echo '<h3>❌ PHPMailer no está instalado</h3>';
                    echo '<p>PHPMailer es necesario para el envío real de emails. Sin él, solo funcionará el modo simulación.</p>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <div class="step">
                <h2>Opciones de instalación</h2>
                
                <h3>Opción 1: Instalación automática (recomendada)</h3>
                <p>Este script intentará instalar PHPMailer automáticamente usando Composer.</p>
                <p><a href="?action=install" class="btn btn-primary">Instalar PHPMailer automáticamente</a></p>
                
                <h3>Opción 2: Instalación manual</h3>
                <p>Si la instalación automática falla, puede instalar manualmente:</p>
                <div class="code-block">
# En la terminal, desde el directorio raíz del proyecto:
composer require phpmailer/phpmailer

# O si no tiene composer.json:
composer init
composer require phpmailer/phpmailer
                </div>
                
                <h3>Opción 3: Descarga manual</h3>
                <p>Como última opción, puede descargar PHPMailer manualmente:</p>
                <ol>
                    <li>Descargar desde <a href="https://github.com/PHPMailer/PHPMailer/releases" target="_blank">GitHub</a></li>
                    <li>Extraer en la carpeta <code>vendor/phpmailer/</code></li>
                    <li>Incluir manualmente los archivos necesarios</li>
                </ol>
            </div>
        <?php endif; ?>
        
        <div class="step">
            <p><a href="admin/configuracion.php">← Volver a Configuración</a></p>
        </div>
    </div>
</body>
</html>
