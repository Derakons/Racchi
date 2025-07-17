<?php
/**
 * Diagnóstico Completo del Sistema Portal Raqchi
 * Verifica todas las funcionalidades y sistemas incorporados
 */

require_once __DIR__ . '/includes/bootstrap.php';

function checkSystemHealth() {
    $report = [
        'timestamp' => date('Y-m-d H:i:s'),
        'overall_status' => 'UNKNOWN',
        'sections' => []
    ];
    
    $totalTests = 0;
    $passedTests = 0;
    
    // 1. CONFIGURACIÓN DEL SISTEMA
    $configSection = [
        'title' => 'Configuración del Sistema',
        'status' => 'PASS',
        'tests' => []
    ];
    
    $configs = [
        'SUPABASE_URL' => SUPABASE_URL,
        'SITE_URL' => SITE_URL,
        'DEFAULT_LANGUAGE' => DEFAULT_LANGUAGE,
        'TICKET_ADULT_NATIONAL' => TICKET_ADULT_NATIONAL,
        'TICKET_ADULT_FOREIGN' => TICKET_ADULT_FOREIGN
    ];
    
    foreach ($configs as $key => $value) {
        $totalTests++;
        $isValid = !empty($value);
        if ($isValid) $passedTests++;
        
        $configSection['tests'][] = [
            'name' => $key,
            'status' => $isValid ? 'PASS' : 'FAIL',
            'value' => is_string($value) ? substr($value, 0, 50) . '...' : $value
        ];
        
        if (!$isValid) $configSection['status'] = 'FAIL';
    }
    
    $report['sections']['config'] = $configSection;
    
    // 2. CONECTIVIDAD SUPABASE
    $supabaseSection = [
        'title' => 'Conectividad Supabase',
        'status' => 'UNKNOWN',
        'tests' => []
    ];
    
    try {
        $supabase = new SupabaseClient();
        
        // Test de conectividad básica
        $totalTests++;
        $connectionTest = $supabase->select('configuraciones', 'clave', [], ['limit' => 1]);
        
        if ($connectionTest['success']) {
            $passedTests++;
            $supabaseSection['tests'][] = [
                'name' => 'Conexión básica',
                'status' => 'PASS',
                'details' => 'Conectado exitosamente'
            ];
            $supabaseSection['status'] = 'PASS';
        } else {
            $supabaseSection['tests'][] = [
                'name' => 'Conexión básica',
                'status' => 'FAIL',
                'details' => $connectionTest['error'] ?? 'Error desconocido'
            ];
            $supabaseSection['status'] = 'FAIL';
        }
        
    } catch (Exception $e) {
        $totalTests++;
        $supabaseSection['tests'][] = [
            'name' => 'Conexión básica',
            'status' => 'FAIL',
            'details' => $e->getMessage()
        ];
        $supabaseSection['status'] = 'FAIL';
    }
    
    $report['sections']['supabase'] = $supabaseSection;
    
    // 3. ESTRUCTURA DE BASE DE DATOS
    $dbSection = [
        'title' => 'Estructura de Base de Datos',
        'status' => 'PASS',
        'tests' => []
    ];
    
    $coreTables = [
        'usuarios', 'configuraciones', 'categorias_servicios', 'servicios',
        'horarios_servicios', 'reservas', 'resenas', 'cupones'
    ];
    
    $newTables = [
        'votos_resenas', 'notificaciones', 'metricas', 'respuestas_resenas'
    ];
    
    $allTables = array_merge($coreTables, $newTables);
    
    foreach ($allTables as $table) {
        $totalTests++;
        try {
            $result = $supabase->select($table, 'count', [], ['limit' => 1]);
            if ($result['success']) {
                $passedTests++;
                $dbSection['tests'][] = [
                    'name' => "Tabla: $table",
                    'status' => 'PASS',
                    'details' => in_array($table, $newTables) ? 'Nueva tabla agregada ✨' : 'Tabla principal'
                ];
            } else {
                $dbSection['tests'][] = [
                    'name' => "Tabla: $table",
                    'status' => 'FAIL',
                    'details' => $result['error'] ?? 'Tabla no encontrada'
                ];
                $dbSection['status'] = 'FAIL';
            }
        } catch (Exception $e) {
            $dbSection['tests'][] = [
                'name' => "Tabla: $table",
                'status' => 'FAIL',
                'details' => $e->getMessage()
            ];
            $dbSection['status'] = 'FAIL';
        }
    }
    
    $report['sections']['database'] = $dbSection;
    
    // 4. DATOS INICIALES
    $dataSection = [
        'title' => 'Datos Iniciales',
        'status' => 'PASS',
        'tests' => []
    ];
    
    // Verificar configuraciones
    $totalTests++;
    $configs = $supabase->select('configuraciones', 'count');
    if ($configs['success'] && !empty($configs['data'])) {
        $passedTests++;
        $count = is_array($configs['data']) && isset($configs['data'][0]['count']) ? $configs['data'][0]['count'] : count($configs['data']);
        $dataSection['tests'][] = [
            'name' => 'Configuraciones del sistema',
            'status' => 'PASS',
            'details' => "Encontradas: $count configuraciones"
        ];
    } else {
        $dataSection['tests'][] = [
            'name' => 'Configuraciones del sistema',
            'status' => 'FAIL',
            'details' => 'Sin configuraciones encontradas'
        ];
        $dataSection['status'] = 'FAIL';
    }
    
    // Verificar servicios
    $totalTests++;
    $servicios = $supabase->select('servicios', 'count');
    if ($servicios['success'] && !empty($servicios['data'])) {
        $passedTests++;
        $count = is_array($servicios['data']) && isset($servicios['data'][0]['count']) ? $servicios['data'][0]['count'] : count($servicios['data']);
        $dataSection['tests'][] = [
            'name' => 'Catálogo de servicios',
            'status' => 'PASS',
            'details' => "Encontrados: $count servicios"
        ];
    } else {
        $dataSection['tests'][] = [
            'name' => 'Catálogo de servicios',
            'status' => 'FAIL',
            'details' => 'Sin servicios encontrados'
        ];
        $dataSection['status'] = 'FAIL';
    }
    
    // Verificar usuario admin
    $totalTests++;
    $admin = $supabase->select('usuarios', 'id,nombre,email', ['rol' => 'admin'], ['limit' => 1]);
    if ($admin['success'] && !empty($admin['data'])) {
        $passedTests++;
        $dataSection['tests'][] = [
            'name' => 'Usuario administrador',
            'status' => 'PASS',
            'details' => 'Admin: ' . ($admin['data'][0]['email'] ?? 'admin@raqchi.com')
        ];
    } else {
        $dataSection['tests'][] = [
            'name' => 'Usuario administrador',
            'status' => 'FAIL',
            'details' => 'Usuario admin no encontrado'
        ];
        $dataSection['status'] = 'FAIL';
    }
    
    $report['sections']['data'] = $dataSection;
    
    // 5. FUNCIONALIDADES DEL SISTEMA
    $featuresSection = [
        'title' => 'Funcionalidades del Sistema',
        'status' => 'PASS',
        'tests' => []
    ];
    
    $functions = [
        'rqText' => 'Sistema de traducción',
        'formatPrice' => 'Formato de precios',
        'generateCSRFToken' => 'Protección CSRF',
        'sanitizeInput' => 'Sanitización de datos',
        'getSupabaseClient' => 'Cliente Supabase'
    ];
    
    foreach ($functions as $func => $desc) {
        $totalTests++;
        if (function_exists($func)) {
            $passedTests++;
            $featuresSection['tests'][] = [
                'name' => $desc,
                'status' => 'PASS',
                'details' => "Función $func disponible"
            ];
        } else {
            $featuresSection['tests'][] = [
                'name' => $desc,
                'status' => 'FAIL',
                'details' => "Función $func no encontrada"
            ];
            $featuresSection['status'] = 'FAIL';
        }
    }
    
    $report['sections']['features'] = $featuresSection;
    
    // 6. ARCHIVOS CRÍTICOS
    $filesSection = [
        'title' => 'Archivos del Sistema',
        'status' => 'PASS',
        'tests' => []
    ];
    
    $criticalFiles = [
        '/config/config.php' => 'Configuración principal',
        '/includes/bootstrap.php' => 'Bootstrap del sistema',
        '/includes/supabase.php' => 'Cliente Supabase',
        '/includes/functions.php' => 'Funciones del sistema',
        '/setup/supabase_setup.sql' => 'Schema de base de datos',
        '/setup/datos_iniciales.sql' => 'Datos iniciales',
        '/index.php' => 'Página principal',
        '/admin/index.php' => 'Panel administrativo'
    ];
    
    foreach ($criticalFiles as $file => $desc) {
        $totalTests++;
        $fullPath = __DIR__ . $file;
        if (file_exists($fullPath)) {
            $passedTests++;
            $filesSection['tests'][] = [
                'name' => $desc,
                'status' => 'PASS',
                'details' => 'Archivo presente'
            ];
        } else {
            $filesSection['tests'][] = [
                'name' => $desc,
                'status' => 'FAIL',
                'details' => 'Archivo no encontrado'
            ];
            $filesSection['status'] = 'FAIL';
        }
    }
    
    $report['sections']['files'] = $filesSection;
    
    // CALCULAR STATUS GENERAL
    $successRate = $totalTests > 0 ? ($passedTests / $totalTests) * 100 : 0;
    
    if ($successRate >= 95) {
        $report['overall_status'] = 'EXCELLENT';
    } elseif ($successRate >= 80) {
        $report['overall_status'] = 'GOOD';
    } elseif ($successRate >= 60) {
        $report['overall_status'] = 'WARNING';
    } else {
        $report['overall_status'] = 'CRITICAL';
    }
    
    $report['statistics'] = [
        'total_tests' => $totalTests,
        'passed_tests' => $passedTests,
        'failed_tests' => $totalTests - $passedTests,
        'success_rate' => round($successRate, 2)
    ];
    
    return $report;
}

// Generar el reporte
$healthReport = checkSystemHealth();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del Sistema - Portal Raqchi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .header .timestamp {
            opacity: 0.8;
            font-size: 1.1em;
        }
        
        .status-banner {
            padding: 20px;
            text-align: center;
            font-weight: bold;
            font-size: 1.3em;
        }
        
        .status-excellent { background: #d4edda; color: #155724; }
        .status-good { background: #cce7ff; color: #004085; }
        .status-warning { background: #fff3cd; color: #856404; }
        .status-critical { background: #f8d7da; color: #721c24; }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            text-transform: uppercase;
            font-size: 0.85em;
            font-weight: 500;
            letter-spacing: 1px;
        }
        
        .sections {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .section-header {
            padding: 20px;
            font-weight: bold;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .section-header.pass { background: #d4edda; color: #155724; }
        .section-header.fail { background: #f8d7da; color: #721c24; }
        .section-header.unknown { background: #e2e3e5; color: #383d41; }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .badge-pass { background: #28a745; color: white; }
        .badge-fail { background: #dc3545; color: white; }
        .badge-unknown { background: #6c757d; color: white; }
        
        .test-grid {
            display: grid;
            gap: 1px;
            background: #e0e0e0;
        }
        
        .test-item {
            background: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .test-info h4 {
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .test-details {
            color: #666;
            font-size: 0.9em;
        }
        
        .test-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85em;
            font-weight: bold;
        }
        
        .test-status.pass { background: #d4edda; color: #155724; }
        .test-status.fail { background: #f8d7da; color: #721c24; }
        
        .actions {
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .progress-fill {
            height: 100%;
            transition: width 0.5s ease;
        }
        
        .progress-excellent { background: #28a745; }
        .progress-good { background: #17a2b8; }
        .progress-warning { background: #ffc107; }
        .progress-critical { background: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Diagnóstico del Sistema</h1>
            <p class="timestamp">Portal Digital Raqchi - <?php echo $healthReport['timestamp']; ?></p>
        </div>
        
        <div class="status-banner status-<?php echo strtolower($healthReport['overall_status']); ?>">
            Estado General: <?php echo $healthReport['overall_status']; ?>
            <div class="progress-bar">
                <div class="progress-fill progress-<?php echo strtolower($healthReport['overall_status']); ?>" 
                     style="width: <?php echo $healthReport['statistics']['success_rate']; ?>%"></div>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $healthReport['statistics']['total_tests']; ?></div>
                <div class="stat-label">Total Tests</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #28a745"><?php echo $healthReport['statistics']['passed_tests']; ?></div>
                <div class="stat-label">Pasados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #dc3545"><?php echo $healthReport['statistics']['failed_tests']; ?></div>
                <div class="stat-label">Fallidos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #007bff"><?php echo $healthReport['statistics']['success_rate']; ?>%</div>
                <div class="stat-label">Éxito</div>
            </div>
        </div>
        
        <div class="sections">
            <?php foreach ($healthReport['sections'] as $sectionKey => $section): ?>
            <div class="section">
                <div class="section-header <?php echo strtolower($section['status']); ?>">
                    <span><?php echo $section['title']; ?></span>
                    <span class="status-badge badge-<?php echo strtolower($section['status']); ?>">
                        <?php echo $section['status']; ?>
                    </span>
                </div>
                
                <div class="test-grid">
                    <?php foreach ($section['tests'] as $test): ?>
                    <div class="test-item">
                        <div class="test-info">
                            <h4><?php echo htmlspecialchars($test['name']); ?></h4>
                            <?php if (isset($test['details'])): ?>
                                <div class="test-details"><?php echo htmlspecialchars($test['details']); ?></div>
                            <?php endif; ?>
                            <?php if (isset($test['value'])): ?>
                                <div class="test-details">Valor: <?php echo htmlspecialchars($test['value']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="test-status <?php echo strtolower($test['status']); ?>">
                            <?php echo $test['status']; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="actions">
            <a href="index.php" class="btn btn-primary">🏠 Ir al Portal</a>
            <a href="admin/" class="btn btn-secondary">⚙️ Panel Admin</a>
            <a href="javascript:location.reload()" class="btn btn-secondary">🔄 Refrescar</a>
        </div>
    </div>
</body>
</html>
