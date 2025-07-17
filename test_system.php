<?php
/**
 * Script de pruebas del sistema completo
 * Verifica la conectividad con Supabase y la funcionalidad de los sistemas incorporados
 */

require_once __DIR__ . '/includes/bootstrap.php';

$results = [];

// Test 1: Verificar configuración de Supabase
$results['config'] = [
    'title' => 'Configuración de Supabase',
    'tests' => []
];

$results['config']['tests']['url'] = [
    'name' => 'URL de Supabase',
    'status' => !empty(SUPABASE_URL) ? 'PASS' : 'FAIL',
    'value' => SUPABASE_URL
];

$results['config']['tests']['anon_key'] = [
    'name' => 'Clave Anon configurada',
    'status' => !empty(SUPABASE_ANON_KEY) ? 'PASS' : 'FAIL',
    'value' => substr(SUPABASE_ANON_KEY, 0, 20) . '...'
];

$results['config']['tests']['service_key'] = [
    'name' => 'Clave Service Role configurada',
    'status' => !empty(SUPABASE_SERVICE_ROLE_KEY) ? 'PASS' : 'FAIL',
    'value' => substr(SUPABASE_SERVICE_ROLE_KEY, 0, 20) . '...'
];

// Test 2: Conectividad con Supabase
$results['connectivity'] = [
    'title' => 'Conectividad con Supabase',
    'tests' => []
];

try {
    $supabase = new SupabaseClient();
    
    // Test básico de conectividad
    $testConnection = $supabase->select('configuraciones', 'clave', [], ['limit' => 1]);
    
    $results['connectivity']['tests']['connection'] = [
        'name' => 'Conexión básica',
        'status' => $testConnection['success'] ? 'PASS' : 'FAIL',
        'details' => $testConnection['success'] ? 'Conectado exitosamente' : $testConnection['error']
    ];
    
} catch (Exception $e) {
    $results['connectivity']['tests']['connection'] = [
        'name' => 'Conexión básica',
        'status' => 'FAIL',
        'details' => $e->getMessage()
    ];
}

// Test 3: Verificar estructura de base de datos
$results['database'] = [
    'title' => 'Estructura de Base de Datos',
    'tests' => []
];

$requiredTables = [
    'usuarios', 'configuraciones', 'categorias_servicios', 'servicios', 
    'horarios_servicios', 'reservas', 'detalles_reservas', 'pagos', 
    'resenas', 'cupones', 'tickets', 'respuestas_tickets',
    'votos_resenas', 'notificaciones', 'metricas', 'respuestas_resenas'
];

foreach ($requiredTables as $table) {
    try {
        $testTable = $supabase->select($table, 'count', [], ['limit' => 1]);
        $results['database']['tests'][$table] = [
            'name' => "Tabla: $table",
            'status' => $testTable['success'] ? 'PASS' : 'FAIL',
            'details' => $testTable['success'] ? 'Tabla existe' : 'Tabla no encontrada'
        ];
    } catch (Exception $e) {
        $results['database']['tests'][$table] = [
            'name' => "Tabla: $table",
            'status' => 'FAIL',
            'details' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Test 4: Verificar datos iniciales
$results['data'] = [
    'title' => 'Datos Iniciales',
    'tests' => []
];

// Verificar configuraciones
try {
    $configs = $supabase->select('configuraciones', 'count');
    $results['data']['tests']['configuraciones'] = [
        'name' => 'Configuraciones del sistema',
        'status' => ($configs['success'] && isset($configs['data'][0]['count']) && $configs['data'][0]['count'] > 0) ? 'PASS' : 'FAIL',
        'details' => $configs['success'] ? "Configuraciones encontradas: " . ($configs['data'][0]['count'] ?? 0) : 'Sin configuraciones'
    ];
} catch (Exception $e) {
    $results['data']['tests']['configuraciones'] = [
        'name' => 'Configuraciones del sistema',
        'status' => 'FAIL',
        'details' => $e->getMessage()
    ];
}

// Verificar usuario admin
try {
    $admin = $supabase->select('usuarios', 'count', ['email' => 'admin@raqchi.com']);
    $results['data']['tests']['admin'] = [
        'name' => 'Usuario administrador',
        'status' => ($admin['success'] && isset($admin['data'][0]['count']) && $admin['data'][0]['count'] > 0) ? 'PASS' : 'FAIL',
        'details' => $admin['success'] ? 'Usuario admin existe' : 'Usuario admin no encontrado'
    ];
} catch (Exception $e) {
    $results['data']['tests']['admin'] = [
        'name' => 'Usuario administrador',
        'status' => 'FAIL',
        'details' => $e->getMessage()
    ];
}

// Verificar servicios
try {
    $servicios = $supabase->select('servicios', 'count');
    $results['data']['tests']['servicios'] = [
        'name' => 'Servicios del portal',
        'status' => ($servicios['success'] && isset($servicios['data'][0]['count']) && $servicios['data'][0]['count'] > 0) ? 'PASS' : 'FAIL',
        'details' => $servicios['success'] ? "Servicios encontrados: " . ($servicios['data'][0]['count'] ?? 0) : 'Sin servicios'
    ];
} catch (Exception $e) {
    $results['data']['tests']['servicios'] = [
        'name' => 'Servicios del portal',
        'status' => 'FAIL',
        'details' => $e->getMessage()
    ];
}

// Test 5: Funcionalidades del sistema
$results['features'] = [
    'title' => 'Funcionalidades del Sistema',
    'tests' => []
];

// Test de sistema de traducción
$results['features']['tests']['translations'] = [
    'name' => 'Sistema de traducción',
    'status' => function_exists('rqText') ? 'PASS' : 'FAIL',
    'details' => function_exists('rqText') ? 'Función rqText disponible' : 'Sistema de traducción no disponible'
];

// Test de funciones de formato
$results['features']['tests']['formatting'] = [
    'name' => 'Funciones de formato',
    'status' => function_exists('formatPrice') ? 'PASS' : 'FAIL',
    'details' => function_exists('formatPrice') ? 'Funciones de formato disponibles' : 'Funciones de formato no disponibles'
];

// Test de seguridad CSRF
$results['features']['tests']['csrf'] = [
    'name' => 'Protección CSRF',
    'status' => function_exists('generateCSRFToken') ? 'PASS' : 'FAIL',
    'details' => function_exists('generateCSRFToken') ? 'Sistema CSRF activo' : 'Sistema CSRF no disponible'
];

// Test 6: Archivos críticos
$results['files'] = [
    'title' => 'Archivos Críticos',
    'tests' => []
];

$criticalFiles = [
    '/config/config.php' => 'Configuración principal',
    '/includes/bootstrap.php' => 'Bootstrap del sistema',
    '/includes/supabase.php' => 'Cliente Supabase',
    '/includes/functions.php' => 'Funciones del sistema',
    '/includes/header.php' => 'Header del sitio',
    '/includes/footer.php' => 'Footer del sitio',
    '/setup/supabase_setup.sql' => 'Script de base de datos',
    '/setup/datos_iniciales.sql' => 'Datos iniciales'
];

foreach ($criticalFiles as $file => $description) {
    $fullPath = __DIR__ . $file;
    $results['files']['tests'][basename($file)] = [
        'name' => $description,
        'status' => file_exists($fullPath) ? 'PASS' : 'FAIL',
        'details' => file_exists($fullPath) ? 'Archivo existe' : 'Archivo no encontrado'
    ];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test del Sistema - Portal Raqchi</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .test-section {
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            overflow: hidden;
        }
        .section-header {
            background: #34495e;
            color: white;
            padding: 15px;
            font-weight: bold;
            font-size: 1.1em;
        }
        .test-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .test-item:last-child {
            border-bottom: none;
        }
        .test-name {
            font-weight: 500;
        }
        .test-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .test-status.pass {
            background: #d4edda;
            color: #155724;
        }
        .test-status.fail {
            background: #f8d7da;
            color: #721c24;
        }
        .test-details {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .summary {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .summary-item {
            display: inline-block;
            margin: 0 20px;
            padding: 10px;
        }
        .summary-number {
            font-size: 2em;
            font-weight: bold;
        }
        .summary-label {
            color: #666;
            font-size: 0.9em;
        }
        .pass-count { color: #28a745; }
        .fail-count { color: #dc3545; }
        .refresh-btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }
        .refresh-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test del Sistema - Portal Digital Raqchi</h1>
        
        <?php
        // Calcular estadísticas
        $totalTests = 0;
        $passedTests = 0;
        $failedTests = 0;
        
        foreach ($results as $category) {
            foreach ($category['tests'] as $test) {
                $totalTests++;
                if ($test['status'] === 'PASS') {
                    $passedTests++;
                } else {
                    $failedTests++;
                }
            }
        }
        ?>
        
        <div class="summary">
            <div class="summary-item">
                <div class="summary-number"><?php echo $totalTests; ?></div>
                <div class="summary-label">Total Tests</div>
            </div>
            <div class="summary-item">
                <div class="summary-number pass-count"><?php echo $passedTests; ?></div>
                <div class="summary-label">Pasados</div>
            </div>
            <div class="summary-item">
                <div class="summary-number fail-count"><?php echo $failedTests; ?></div>
                <div class="summary-label">Fallidos</div>
            </div>
            <div class="summary-item">
                <button class="refresh-btn" onclick="location.reload()">🔄 Refrescar Tests</button>
            </div>
        </div>
        
        <?php foreach ($results as $categoryKey => $category): ?>
        <div class="test-section">
            <div class="section-header">
                <?php echo $category['title']; ?>
            </div>
            
            <?php foreach ($category['tests'] as $testKey => $test): ?>
            <div class="test-item">
                <div>
                    <div class="test-name"><?php echo $test['name']; ?></div>
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
        <?php endforeach; ?>
        
        <div style="text-align: center; margin-top: 30px; color: #666;">
            <p>⏰ Ejecutado el: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>🏠 <a href="index.php">Volver al Portal</a> | 🔧 <a href="admin/">Panel Admin</a></p>
        </div>
    </div>
</body>
</html>
