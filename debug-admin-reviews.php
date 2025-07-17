<?php
/**
 * Debug para verificar conectividad de reseñas en admin
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/includes/bootstrap.php';

// Obtener cliente de Supabase con clave de servicio (como en admin)
$supabase = new SupabaseClient(true);

echo "<h2>Debug Admin Reseñas - Conectividad Supabase</h2>";

try {
    // Obtener todas las reseñas usando * como en admin
    $result = $supabase->select(
        'resenas',
        '*',
        [],
        ['order' => 'created_at.desc']
    );
    
    echo "<h3>Resultado de consulta:</h3>";
    echo "<pre>";
    echo "Success: " . ($result['success'] ? 'true' : 'false') . "\n";
    echo "Data count: " . (isset($result['data']) && is_array($result['data']) ? count($result['data']) : '0') . "\n";
    echo "Raw result:\n";
    print_r($result);
    echo "</pre>";
    
    if ($result['success'] && !empty($result['data']) && is_array($result['data'])) {
        echo "<h3>Reseñas encontradas:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Calificación</th><th>Comentario</th><th>Aprobada</th><th>Fecha</th></tr>";
        
        foreach ($result['data'] as $review) {
            $estado = 'pendiente';
            if ($review['aprobada'] === true) {
                $estado = 'aprobada';
            } elseif ($review['aprobada'] === false) {
                $estado = 'rechazada';
            }
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($review['id']) . "</td>";
            echo "<td>" . htmlspecialchars($review['nombre'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($review['email'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($review['calificacion'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars(substr($review['comentario'] ?? '', 0, 50)) . "...</td>";
            echo "<td style='color: " . ($estado === 'aprobada' ? 'green' : ($estado === 'rechazada' ? 'red' : 'orange')) . ";'>" . $estado . "</td>";
            echo "<td>" . htmlspecialchars($review['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>No se encontraron reseñas o hay un error en la consulta.</p>";
        if (isset($result['error'])) {
            echo "<p style='color: red;'>Error: " . htmlspecialchars($result['error']) . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Excepción: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<br><a href='admin/reseñas.php'>← Volver a Admin Reseñas</a>";
?>
