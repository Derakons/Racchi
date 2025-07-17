<?php
/**
 * Verificar estructura de base de datos para tickets
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

try {
    $supabase = new SupabaseClient();
    
    echo "<!-- Verificando estructura de base de datos -->\n";
    
    // Intentar obtener tickets
    echo "Probando tabla 'tickets':\n";
    $ticketsResult = $supabase->select('tickets', '*', [], ['limit' => 5]);
    
    echo "Resultado tickets:\n";
    echo json_encode([
        'success' => $ticketsResult['success'],
        'data' => $ticketsResult['data'] ?? [],
        'error' => $ticketsResult['error'] ?? null
    ], JSON_PRETTY_PRINT);
    
    echo "\n\nProbando tabla 'entradas':\n";
    $entradasResult = $supabase->select('entradas', '*', [], ['limit' => 5]);
    
    echo "Resultado entradas:\n";
    echo json_encode([
        'success' => $entradasResult['success'],
        'data' => $entradasResult['data'] ?? [],
        'error' => $entradasResult['error'] ?? null
    ], JSON_PRETTY_PRINT);
    
    echo "\n\nProbando tabla 'reservas':\n";
    $reservasResult = $supabase->select('reservas', '*', [], ['limit' => 5]);
    
    echo "Resultado reservas:\n";
    echo json_encode([
        'success' => $reservasResult['success'],
        'data' => $reservasResult['data'] ?? [],
        'error' => $reservasResult['error'] ?? null
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
?>
