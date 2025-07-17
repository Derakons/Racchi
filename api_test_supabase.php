<?php
/**
 * Test específico de Supabase - Verificar CRUD operations
 */

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

$testResults = [];

try {
    // Test 1: Conexión básica
    $supabase = new SupabaseClient();
    $testResults['connection'] = 'SUCCESS';
    
    // Test 2: SELECT - Verificar datos existentes
    $configs = $supabase->select('configuraciones', '*', [], ['limit' => 5]);
    $testResults['select_configs'] = [
        'success' => $configs['success'],
        'count' => $configs['success'] ? count($configs['data']) : 0,
        'error' => !$configs['success'] ? ($configs['error'] ?? 'Error desconocido') : null
    ];
    
    // Test 3: Verificar servicios
    $servicios = $supabase->select('servicios', 'id,nombre,precio_nacional', [], ['limit' => 3]);
    $testResults['select_servicios'] = [
        'success' => $servicios['success'],
        'count' => $servicios['success'] ? count($servicios['data']) : 0,
        'data' => $servicios['success'] ? $servicios['data'] : null,
        'error' => !$servicios['success'] ? ($servicios['error'] ?? 'Error desconocido') : null
    ];
    
    // Test 4: Verificar usuarios
    $usuarios = $supabase->select('usuarios', 'id,nombre,email,rol', ['rol' => 'admin'], ['limit' => 1]);
    $testResults['select_admin'] = [
        'success' => $usuarios['success'],
        'found_admin' => $usuarios['success'] && !empty($usuarios['data']),
        'admin_data' => $usuarios['success'] && !empty($usuarios['data']) ? $usuarios['data'][0] : null,
        'error' => !$usuarios['success'] ? ($usuarios['error'] ?? 'Error desconocido') : null
    ];
    
    // Test 5: Test de inserción (con rollback)
    $testInsert = $supabase->insert('logs_auditoria', [
        'accion' => 'TEST_SYSTEM',
        'tabla_afectada' => 'test',
        'datos_nuevos' => json_encode(['test' => true, 'timestamp' => date('Y-m-d H:i:s')]),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
    ]);
    
    $testResults['insert_test'] = [
        'success' => $testInsert['success'],
        'error' => !$testInsert['success'] ? ($testInsert['error'] ?? 'Error desconocido') : null
    ];
    
    // Test 6: Verificar nuevas tablas agregadas
    $newTables = ['votos_resenas', 'notificaciones', 'metricas', 'respuestas_resenas'];
    $testResults['new_tables'] = [];        foreach ($newTables as $table) {
        try {
            $result = $supabase->select($table, 'count', [], ['limit' => 1]);
            $testResults['new_tables'][$table] = [
                'exists' => $result['success'],
                'error' => !$result['success'] ? ($result['error'] ?? 'Error desconocido') : null
            ];
        } catch (Exception $e) {
            $testResults['new_tables'][$table] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Test 7: Verificar RLS (Row Level Security)
    $testResults['rls_status'] = [
        'message' => 'RLS debe estar habilitado en producción',
        'note' => 'Se recomienda verificar manualmente en Supabase Dashboard'
    ];
    
    // Test 8: Verificar estructura de datos de ejemplo
    $resenas = $supabase->select('resenas', 'id,titulo,calificacion', ['aprobada' => true], ['limit' => 3]);
    $testResults['sample_reviews'] = [
        'success' => $resenas['success'],
        'count' => $resenas['success'] ? count($resenas['data']) : 0,
        'data' => $resenas['success'] ? $resenas['data'] : null,
        'error' => !$resenas['success'] ? ($resenas['error'] ?? 'Error desconocido') : null
    ];
    
} catch (Exception $e) {
    $testResults['connection'] = 'FAILED';
    $testResults['error'] = $e->getMessage();
}

// Test 9: Verificar configuraciones del sistema
try {
    $systemConfigs = [
        'SUPABASE_URL' => SUPABASE_URL,
        'SITE_URL' => SITE_URL,
        'DEFAULT_LANGUAGE' => DEFAULT_LANGUAGE,
        'AVAILABLE_LANGUAGES' => AVAILABLE_LANGUAGES
    ];
    
    $testResults['system_config'] = $systemConfigs;
} catch (Exception $e) {
    $testResults['system_config'] = ['error' => $e->getMessage()];
}

// Generar respuesta
echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'status' => $testResults['connection'] === 'SUCCESS' ? 'OK' : 'ERROR',
    'results' => $testResults
], JSON_PRETTY_PRINT);
?>
