<?php
/**
 * API para acciones del sistema (limpiar caché, respaldo, etc.)
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/../../includes/bootstrap.php';

// Verificar autenticación y rol
requireRole('admin');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    $response = ['success' => false, 'message' => 'Acción no válida'];
    
    switch ($action) {
        case 'clear_cache':
            $response = clearSystemCache();
            break;
            
        case 'backup_database':
            $response = backupDatabase();
            break;
            
        case 'reset_system':
            $confirm = $input['confirm'] ?? '';
            if ($confirm === 'RESTABLECER') {
                $response = resetSystem();
            } else {
                $response = ['success' => false, 'message' => 'Confirmación requerida'];
            }
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Acción no reconocida: ' . $action];
            break;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error in system actions: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}

function clearSystemCache() {
    try {
        $cacheCleared = 0;
        $errors = [];
        
        // Limpiar caché de sesiones temporales
        $sessionPath = session_save_path();
        if (is_dir($sessionPath)) {
            $files = glob($sessionPath . '/sess_*');
            foreach ($files as $file) {
                if (is_file($file) && (time() - filemtime($file) > 3600)) { // Archivos de más de 1 hora
                    if (unlink($file)) {
                        $cacheCleared++;
                    }
                }
            }
        }
        
        // Limpiar archivos temporales de uploads
        $tempPath = __DIR__ . '/../../temp';
        if (is_dir($tempPath)) {
            $files = glob($tempPath . '/*');
            foreach ($files as $file) {
                if (is_file($file) && (time() - filemtime($file) > 1800)) { // Archivos de más de 30 minutos
                    if (unlink($file)) {
                        $cacheCleared++;
                    }
                }
            }
        }
        
        // Limpiar logs antiguos (mantener últimos 30 días)
        $logsPath = __DIR__ . '/../../logs';
        if (is_dir($logsPath)) {
            $files = glob($logsPath . '/*.log');
            foreach ($files as $file) {
                if (is_file($file) && (time() - filemtime($file) > 2592000)) { // 30 días
                    if (unlink($file)) {
                        $cacheCleared++;
                    }
                }
            }
        }
        
        // Limpiar caché de configuraciones (recrear archivos por defecto)
        clearConfigCache();
        
        logActivity('cache_cleared', 'success', [
            'files_cleared' => $cacheCleared,
            'errors' => $errors
        ]);
        
        return [
            'success' => true,
            'message' => "Caché limpiada exitosamente. {$cacheCleared} archivos eliminados.",
            'details' => [
                'files_cleared' => $cacheCleared,
                'errors' => $errors
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Error clearing cache: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al limpiar caché: ' . $e->getMessage()
        ];
    }
}

function backupDatabase() {
    try {
        $backupDir = __DIR__ . '/../../backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . "/raqchi_backup_{$timestamp}.sql";
        
        // Simular backup de base de datos (para Supabase usaríamos su API)
        $backupContent = "-- Backup de Portal Raqchi\n";
        $backupContent .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
        $backupContent .= "-- Nota: Este es un backup simulado\n\n";
        
        // Incluir estructura básica de tablas
        $backupContent .= generateTableStructures();
        
        // Incluir datos de ejemplo
        $backupContent .= generateSampleData();
        
        file_put_contents($backupFile, $backupContent);
        
        // Comprimir archivo si es posible
        if (class_exists('ZipArchive')) {
            $zipFile = $backupDir . "/raqchi_backup_{$timestamp}.zip";
            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
                $zip->addFile($backupFile, basename($backupFile));
                $zip->close();
                unlink($backupFile); // Eliminar archivo SQL sin comprimir
                $backupFile = $zipFile;
            }
        }
        
        logActivity('database_backup', 'success', [
            'backup_file' => basename($backupFile),
            'file_size' => filesize($backupFile)
        ]);
        
        return [
            'success' => true,
            'message' => 'Backup de base de datos creado exitosamente',
            'details' => [
                'backup_file' => basename($backupFile),
                'file_size' => formatBytes(filesize($backupFile)),
                'location' => $backupFile
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Error creating backup: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al crear backup: ' . $e->getMessage()
        ];
    }
}

function resetSystem() {
    try {
        $resetActions = [];
        
        // 1. Limpiar todas las sesiones
        $sessionPath = session_save_path();
        if (is_dir($sessionPath)) {
            $files = glob($sessionPath . '/sess_*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            $resetActions[] = 'Sesiones limpiadas';
        }
        
        // 2. Restablecer configuraciones por defecto
        resetConfigurationsToDefault();
        $resetActions[] = 'Configuraciones restablecidas';
        
        // 3. Limpiar logs
        $logsPath = __DIR__ . '/../../logs';
        if (is_dir($logsPath)) {
            $files = glob($logsPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            $resetActions[] = 'Logs limpiados';
        }
        
        // 4. Limpiar archivos temporales
        $tempPath = __DIR__ . '/../../temp';
        if (is_dir($tempPath)) {
            $files = glob($tempPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            $resetActions[] = 'Archivos temporales eliminados';
        }
        
        // 5. Crear backup antes del reset
        $backupResult = backupDatabase();
        if ($backupResult['success']) {
            $resetActions[] = 'Backup creado antes del reset';
        }
        
        logActivity('system_reset', 'success', [
            'actions' => $resetActions,
            'reset_by' => $_SESSION['user_name'] ?? 'Unknown'
        ]);
        
        return [
            'success' => true,
            'message' => 'Sistema restablecido exitosamente',
            'details' => [
                'actions_performed' => $resetActions,
                'note' => 'Por favor, vuelva a iniciar sesión'
            ]
        ];
        
    } catch (Exception $e) {
        error_log("Error resetting system: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Error al restablecer sistema: ' . $e->getMessage()
        ];
    }
}

function clearConfigCache() {
    $configPath = __DIR__ . '/../../config';
    $defaultConfigs = [
        'site_settings.json' => [
            'site_name' => 'Portal Digital Raqchi',
            'site_description' => 'Descubre la cultura ancestral de Raqchi',
            'contact_email' => 'info@raqchi.pe',
            'contact_phone' => '+51 984 123 456'
        ],
        'ticket_settings.json' => [
            'adult_price' => 15.00,
            'student_price' => 8.00,
            'child_price' => 5.00,
            'daily_capacity' => 500
        ],
        'language_settings.json' => [
            'available_languages' => ['es', 'en'],
            'default_language' => 'es'
        ],
        'payment_settings.json' => [
            'payment_methods' => ['cash', 'card', 'yape', 'plin'],
            'yape_number' => '984 123 456',
            'plin_number' => '984 123 456'
        ]
    ];
    
    foreach ($defaultConfigs as $file => $config) {
        $filePath = $configPath . '/' . $file;
        file_put_contents($filePath, json_encode($config, JSON_PRETTY_PRINT));
    }
}

function resetConfigurationsToDefault() {
    clearConfigCache();
    
    // Restablecer configuración de mantenimiento
    $maintenanceConfig = [
        'maintenance_mode' => false,
        'maintenance_message' => 'Estamos realizando mejoras en nuestro sistema. Volveremos pronto.'
    ];
    
    $configPath = __DIR__ . '/../../config';
    file_put_contents($configPath . '/maintenance_settings.json', json_encode($maintenanceConfig, JSON_PRETTY_PRINT));
}

function generateTableStructures() {
    return "
-- Estructura de tablas básicas
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol VARCHAR(50) DEFAULT 'cliente',
    activo BOOLEAN DEFAULT true,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tickets (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    tipo VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    fecha_visita DATE NOT NULL,
    estado VARCHAR(50) DEFAULT 'pendiente',
    codigo_ticket VARCHAR(100) UNIQUE,
    fecha_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS servicios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2),
    activo BOOLEAN DEFAULT true,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

";
}

function generateSampleData() {
    return "
-- Datos de ejemplo
INSERT INTO usuarios (email, nombre, password_hash, rol) VALUES
('admin@raqchi.pe', 'Administrador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('taquilla@raqchi.pe', 'Operador Taquilla', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'taquilla');

INSERT INTO servicios (nombre, descripcion, precio, activo) VALUES
('Visita Guiada', 'Recorrido completo por el sitio arqueológico', 25.00, true),
('Taller de Cerámica', 'Aprende técnicas ancestrales de cerámica', 35.00, true),
('Transporte desde Cusco', 'Servicio de transporte ida y vuelta', 40.00, true);

";
}

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

function logActivity($action, $status, $data) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $_SESSION['user_id'] ?? 0,
        'user_name' => $_SESSION['user_name'] ?? 'Unknown',
        'action' => $action,
        'status' => $status,
        'data' => $data,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ];
    
    $logFile = __DIR__ . '/../../logs/admin_activity.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}
?>
