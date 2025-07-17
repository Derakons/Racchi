<?php
/**
 * Prueba simple de la API de reseñas
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

try {
    $supabase = new SupabaseClient();
    
    echo "<!-- Probando conexión a Supabase -->\n";
    
    // Obtener datos de la tabla resenas
    $result = $supabase->select('resenas', '*', [], ['limit' => 5]);
    
    echo json_encode([
        'success' => $result['success'],
        'data' => $result['data'] ?? [],
        'error' => $result['error'] ?? null,
        'debug' => [
            'result_keys' => array_keys($result),
            'data_count' => is_array($result['data'] ?? null) ? count($result['data'] ?? []) : 'not_array'
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
