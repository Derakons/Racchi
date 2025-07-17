<?php
/**
 * API para exportar datos del sistema
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

try {
    $exportData = generateExportData();
    
    if (class_exists('ZipArchive')) {
        sendZippedExport($exportData);
    } else {
        sendJSONExport($exportData);
    }
    
} catch (Exception $e) {
    error_log("Error exporting data: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al exportar datos: ' . $e->getMessage()
    ]);
}

function generateExportData() {
    $exportData = [
        'export_info' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'exported_by' => $_SESSION['user_name'] ?? 'Unknown',
            'portal_version' => '1.0.0',
            'export_type' => 'full_system_export'
        ],
        'configurations' => loadAllConfigurations(),
        'users' => generateUsersExport(),
        'tickets' => generateTicketsExport(),
        'services' => generateServicesExport(),
        'reviews' => generateReviewsExport(),
        'analytics' => generateAnalyticsExport(),
        'logs' => loadRecentLogs()
    ];
    
    return $exportData;
}

function loadAllConfigurations() {
    $configPath = __DIR__ . '/../../config';
    $configurations = [];
    
    $configFiles = [
        'site_settings.json',
        'ticket_settings.json',
        'language_settings.json',
        'payment_settings.json',
        'email_settings.json',
        'maintenance_settings.json'
    ];
    
    foreach ($configFiles as $file) {
        $filePath = $configPath . '/' . $file;
        if (file_exists($filePath)) {
            $configName = str_replace('.json', '', $file);
            $configurations[$configName] = json_decode(file_get_contents($filePath), true);
            
            // Ocultar información sensible
            if ($configName === 'email_settings' && isset($configurations[$configName]['smtp_password'])) {
                $configurations[$configName]['smtp_password'] = '[HIDDEN]';
            }
        }
    }
    
    return $configurations;
}

function generateUsersExport() {
    // Simular datos de usuarios (en producción vendría de Supabase)
    return [
        [
            'id' => 1,
            'email' => 'admin@raqchi.pe',
            'nombre' => 'Administrador',
            'rol' => 'admin',
            'activo' => true,
            'fecha_registro' => '2024-01-01 00:00:00',
            'ultimo_acceso' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'email' => 'taquilla@raqchi.pe',
            'nombre' => 'Operador Taquilla',
            'rol' => 'taquilla',
            'activo' => true,
            'fecha_registro' => '2024-01-01 00:00:00',
            'ultimo_acceso' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 3,
            'email' => 'usuario@ejemplo.com',
            'nombre' => 'Usuario Demo',
            'rol' => 'cliente',
            'activo' => true,
            'fecha_registro' => '2024-01-15 10:30:00',
            'ultimo_acceso' => '2024-01-20 14:15:00'
        ]
    ];
}

function generateTicketsExport() {
    // Simular datos de tickets
    return [
        [
            'id' => 1,
            'usuario_id' => 3,
            'tipo' => 'adulto',
            'precio' => 15.00,
            'fecha_visita' => '2024-02-01',
            'estado' => 'confirmado',
            'codigo_ticket' => 'RQ-' . date('Y') . '-001',
            'fecha_compra' => '2024-01-20 14:15:00'
        ],
        [
            'id' => 2,
            'usuario_id' => 3,
            'tipo' => 'estudiante',
            'precio' => 8.00,
            'fecha_visita' => '2024-02-01',
            'estado' => 'confirmado',
            'codigo_ticket' => 'RQ-' . date('Y') . '-002',
            'fecha_compra' => '2024-01-20 14:15:00'
        ]
    ];
}

function generateServicesExport() {
    return [
        [
            'id' => 1,
            'nombre' => 'Visita Guiada',
            'descripcion' => 'Recorrido completo por el sitio arqueológico con guía especializado',
            'precio' => 25.00,
            'activo' => true,
            'fecha_creacion' => '2024-01-01 00:00:00'
        ],
        [
            'id' => 2,
            'nombre' => 'Taller de Cerámica',
            'descripcion' => 'Aprende las técnicas ancestrales de cerámica de la cultura Inca',
            'precio' => 35.00,
            'activo' => true,
            'fecha_creacion' => '2024-01-01 00:00:00'
        ],
        [
            'id' => 3,
            'nombre' => 'Transporte desde Cusco',
            'descripcion' => 'Servicio de transporte ida y vuelta desde la ciudad de Cusco',
            'precio' => 40.00,
            'activo' => true,
            'fecha_creacion' => '2024-01-01 00:00:00'
        ]
    ];
}

function generateReviewsExport() {
    return [
        [
            'id' => 1,
            'usuario_nombre' => 'María González',
            'calificacion' => 5,
            'comentario' => 'Una experiencia increíble. El lugar es mágico y los guías muy conocedores de la historia.',
            'fecha' => '2024-01-18 16:30:00',
            'aprobado' => true
        ],
        [
            'id' => 2,
            'usuario_nombre' => 'John Smith',
            'calificacion' => 5,
            'comentario' => 'Amazing place! The pottery workshops were fascinating and the food was delicious.',
            'fecha' => '2024-01-15 11:20:00',
            'aprobado' => true
        ],
        [
            'id' => 3,
            'usuario_nombre' => 'Carlos Mendoza',
            'calificacion' => 4,
            'comentario' => 'Muy recomendable. El transporte fue puntual y el sitio arqueológico impresionante.',
            'fecha' => '2024-01-10 09:45:00',
            'aprobado' => true
        ]
    ];
}

function generateAnalyticsExport() {
    return [
        'resumen_mensual' => [
            'tickets_vendidos' => 156,
            'ingresos_tickets' => 2340.00,
            'servicios_contratados' => 89,
            'ingresos_servicios' => 2970.00,
            'visitantes_nuevos' => 142,
            'visitantes_recurrentes' => 14
        ],
        'visitantes_por_dia' => [
            '2024-01-20' => 12,
            '2024-01-19' => 8,
            '2024-01-18' => 15,
            '2024-01-17' => 9,
            '2024-01-16' => 11
        ],
        'tipos_ticket_populares' => [
            'adulto' => 89,
            'estudiante' => 45,
            'niño' => 22
        ],
        'servicios_populares' => [
            'Visita Guiada' => 67,
            'Taller de Cerámica' => 23,
            'Transporte desde Cusco' => 34
        ]
    ];
}

function loadRecentLogs() {
    $logFile = __DIR__ . '/../../logs/admin_activity.log';
    $logs = [];
    
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $logLines = explode("\n", trim($logContent));
        
        // Obtener las últimas 50 entradas
        $recentLines = array_slice($logLines, -50);
        
        foreach ($recentLines as $line) {
            if (!empty($line)) {
                $logEntry = json_decode($line, true);
                if ($logEntry) {
                    // Ocultar información sensible
                    if (isset($logEntry['data']['smtp_password'])) {
                        $logEntry['data']['smtp_password'] = '[HIDDEN]';
                    }
                    $logs[] = $logEntry;
                }
            }
        }
    }
    
    return $logs;
}

function sendZippedExport($exportData) {
    $tempDir = __DIR__ . '/../../temp';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d_H-i-s');
    $zipFile = $tempDir . "/raqchi_export_{$timestamp}.zip";
    
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
        throw new Exception('No se pudo crear el archivo ZIP');
    }
    
    // Agregar archivo principal de datos
    $zip->addFromString('raqchi_data.json', json_encode($exportData, JSON_PRETTY_PRINT));
    
    // Agregar archivos de configuración individuales
    foreach ($exportData['configurations'] as $configName => $configData) {
        $zip->addFromString("config/{$configName}.json", json_encode($configData, JSON_PRETTY_PRINT));
    }
    
    // Agregar archivo README
    $readme = generateExportReadme();
    $zip->addFromString('README.txt', $readme);
    
    $zip->close();
    
    // Registrar la exportación
    logActivity('data_export', 'success', [
        'export_file' => basename($zipFile),
        'file_size' => filesize($zipFile),
        'records_exported' => [
            'users' => count($exportData['users']),
            'tickets' => count($exportData['tickets']),
            'services' => count($exportData['services']),
            'reviews' => count($exportData['reviews']),
            'logs' => count($exportData['logs'])
        ]
    ]);
    
    // Enviar archivo
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
    header('Content-Length: ' . filesize($zipFile));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    readfile($zipFile);
    unlink($zipFile); // Eliminar archivo temporal
    exit;
}

function sendJSONExport($exportData) {
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "raqchi_export_{$timestamp}.json";
    
    // Registrar la exportación
    logActivity('data_export', 'success', [
        'export_file' => $filename,
        'format' => 'json',
        'records_exported' => [
            'users' => count($exportData['users']),
            'tickets' => count($exportData['tickets']),
            'services' => count($exportData['services']),
            'reviews' => count($exportData['reviews']),
            'logs' => count($exportData['logs'])
        ]
    ]);
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    echo json_encode($exportData, JSON_PRETTY_PRINT);
    exit;
}

function generateExportReadme() {
    return "Portal Digital Raqchi - Exportación de Datos
=====================================

Fecha de exportación: " . date('Y-m-d H:i:s') . "
Exportado por: " . ($_SESSION['user_name'] ?? 'Unknown') . "

CONTENIDO:
---------
- raqchi_data.json: Archivo principal con todos los datos
- config/: Archivos de configuración individuales
- README.txt: Este archivo

ESTRUCTURA DE DATOS:
-------------------
- export_info: Información sobre la exportación
- configurations: Configuraciones del sistema
- users: Datos de usuarios (contraseñas hasheadas)
- tickets: Información de tickets vendidos
- services: Servicios disponibles
- reviews: Reseñas de usuarios
- analytics: Datos de analítica
- logs: Logs de actividad recientes

NOTAS IMPORTANTES:
-----------------
- Las contraseñas están hasheadas y no son reversibles
- Información sensible como contraseñas SMTP están ocultas
- Los logs incluyen solo las últimas 50 entradas
- Este export es compatible con el sistema de importación

Para más información, contacte al administrador del sistema.
";
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
