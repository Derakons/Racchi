<?php
/**
 * Script para crear datos de prueba de reseñas
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/includes/bootstrap.php';

$supabase = getSupabaseClient();

// Reseñas de ejemplo
$resenasEjemplo = [
    [
        'nombre_reviewer' => 'María González',
        'email_reviewer' => 'maria.gonzalez@email.com',
        'rating' => 5,
        'contenido' => 'Una experiencia increíble. El lugar es mágico y los guías muy conocedores de la historia. Las vistas son espectaculares y aprendimos mucho sobre la cultura inca.',
        'estado' => 'pendiente',
        'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'ip_address' => '192.168.1.100'
    ],
    [
        'nombre_reviewer' => 'John Smith',
        'email_reviewer' => 'john.smith@email.com',
        'rating' => 4,
        'contenido' => 'Amazing place! The pottery workshops were fascinating and the food was delicious. Highly recommend visiting this archaeological site.',
        'estado' => 'aprobada',
        'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-1 week')),
        'ip_address' => '10.0.0.50'
    ],
    [
        'nombre_reviewer' => 'Carlos Mendoza',
        'email_reviewer' => 'carlos.mendoza@email.com',
        'rating' => 5,
        'contenido' => 'Muy recomendable. El transporte fue puntual y el sitio arqueológico impresionante. Los talleres de cerámica son una experiencia única.',
        'estado' => 'aprobada',
        'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
        'ip_address' => '172.16.0.25'
    ],
    [
        'nombre_reviewer' => 'Ana Quispe',
        'email_reviewer' => 'ana.quispe@email.com',
        'rating' => 5,
        'contenido' => 'Como cusqueña me enorgullece tener este patrimonio. Excelente conservación y muy buena atención del personal.',
        'estado' => 'aprobada',
        'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-1 month')),
        'ip_address' => '192.168.0.75'
    ],
    [
        'nombre_reviewer' => 'Roberto Silva',
        'email_reviewer' => 'roberto.silva@email.com',
        'rating' => 3,
        'contenido' => 'El lugar es bonito pero esperaba más información histórica. Los guías podrían mejorar su preparación.',
        'estado' => 'pendiente',
        'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-3 days')),
        'ip_address' => '203.0.113.10'
    ],
    [
        'nombre_reviewer' => 'Lisa Johnson',
        'email_reviewer' => 'lisa.johnson@email.com',
        'rating' => 2,
        'contenido' => 'The site was okay but not as impressive as other ruins we visited. Too crowded and expensive.',
        'estado' => 'rechazada',
        'fecha_creacion' => date('Y-m-d H:i:s', strtotime('-5 days')),
        'ip_address' => '198.51.100.42'
    ]
];

echo "<h1>Inicializando datos de prueba para reseñas</h1>";

foreach ($resenasEjemplo as $index => $resena) {
    try {
        $result = $supabase->insert('resenas', $resena);
        
        if ($result['success']) {
            echo "<p>✅ Reseña de {$resena['nombre_reviewer']} creada exitosamente</p>";
        } else {
            echo "<p>❌ Error al crear reseña de {$resena['nombre_reviewer']}: " . json_encode($result) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Excepción al crear reseña de {$resena['nombre_reviewer']}: " . $e->getMessage() . "</p>";
    }
}

echo "<h2>Proceso completado</h2>";
echo "<p><a href='admin/reseñas.php'>Ver panel de administración de reseñas</a></p>";
echo "<p><a href='public/reviews.php'>Ver página pública de reseñas</a></p>";
?>
