<?php
/**
 * API de Reseñas - Versión corregida
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/../includes/bootstrap.php';

// Deshabilitar errores en salida para JSON limpio
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? 'get';
    
    switch ($action) {
        case 'get':
            handleGetReviews();
            break;
        case 'submit':
            handleSubmitReview();
            break;
        case 'helpful':
            handleMarkHelpful();
            break;
        default:
            handleGetReviews();
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor',
        'error' => $e->getMessage()
    ]);
}

function handleGetReviews() {
    try {
        $supabase = new SupabaseClient();
        
        // Obtener todas las reseñas aprobadas
        $result = $supabase->select(
            'resenas', 
            'id,titulo,comentario,calificacion,nombre,email,ubicacion,fecha_visita,created_at,aprobada',
            ['aprobada' => true], // Solo mostrar reseñas aprobadas
            ['order' => 'created_at.desc', 'limit' => 50]
        );
        
        if ($result['success'] && !empty($result['data']) && is_array($result['data'])) {
            $reviews = array_map(function($review) {
                // Si no hay nombre, usar "Usuario Verificado"
                $name = trim($review['nombre'] ?? '');
                if (empty($name)) {
                    $name = 'Usuario Verificado';
                }
                
                return [
                    'id' => $review['id'],
                    'name' => $name,
                    'rating' => (int)$review['calificacion'],
                    'title' => $review['titulo'] ?? '',
                    'content' => $review['comentario'],
                    'created_at' => $review['created_at'],
                    'date' => $review['created_at'], // Compatibilidad
                    'location' => $review['ubicacion'] ?? '',
                    'visit_date' => $review['fecha_visita'] ?? '',
                    'helpful_yes' => 0,
                    'helpful_no' => 0,
                    'verified' => true
                ];
            }, $result['data'] ?? []);
            
            $stats = [
                'total_reviews' => count($reviews),
                'average_rating' => count($reviews) > 0 ? 
                    round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0,
                'rating_distribution' => [
                    5 => count(array_filter($reviews, fn($r) => $r['rating'] == 5)),
                    4 => count(array_filter($reviews, fn($r) => $r['rating'] == 4)),
                    3 => count(array_filter($reviews, fn($r) => $r['rating'] == 3)),
                    2 => count(array_filter($reviews, fn($r) => $r['rating'] == 2)),
                    1 => count(array_filter($reviews, fn($r) => $r['rating'] == 1))
                ]
            ];
            
            echo json_encode([
                'success' => true,
                'reviews' => $reviews,
                'total' => count($reviews),
                'has_more' => false,
                'stats' => $stats
            ]);
        } else {
            // Si no hay reseñas, devolver estructura vacía válida
            $stats = [
                'total_reviews' => 0,
                'average_rating' => 0,
                'rating_distribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]
            ];
            
            echo json_encode([
                'success' => true,
                'reviews' => [],
                'total' => 0,
                'has_more' => false,
                'stats' => $stats
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al cargar reseñas',
            'error' => $e->getMessage()
        ]);
    }
}

function handleSubmitReview() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => rqText('method_not_allowed')]);
        return;
    }
    
    // Validar CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => rqText('invalid_token')]);
        return;
    }
    
    // Validar datos mínimos
    $rating = intval($_POST['rating'] ?? 0);
    $content = sanitizeInput($_POST['review_content'] ?? '');
    $name = sanitizeInput($_POST['reviewer_name'] ?? '');
    $email = sanitizeInput($_POST['reviewer_email'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $visitDate = sanitizeInput($_POST['visit_date'] ?? '');
    
    if ($rating < 1 || $rating > 5 || strlen($content) < 10 || empty($name)) {
        echo json_encode(['success' => false, 'message' => rqText('review_error')]);
        return;
    }
    
    // Insertar reseña en Supabase
    try {
        $supabase = new SupabaseClient(true); // Con privilegios de escritura
        
        $newReview = [
            'comentario' => $content,
            'calificacion' => $rating,
            'nombre' => $name,
            'email' => $email,
            'ubicacion' => $location,
            'fecha_visita' => $visitDate ?: null,
            'aprobada' => null, // Pendiente de aprobación
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        $result = $supabase->insert('resenas', $newReview);
        
        if ($result['success'] && !empty($result['data']) && is_array($result['data']) && isset($result['data'][0]['id'])) {
            echo json_encode([
                'success' => true,
                'message' => rqText('review_pending'),
                'review_id' => $result['data'][0]['id']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => rqText('review_error')]);
        }
    } catch (Exception $e) {
        error_log("Error inserting review: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => rqText('review_error')]);
    }
}

function handleMarkHelpful() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => rqText('method_not_allowed')]);
        return;
    }
    
    $reviewId = sanitizeInput($_POST['review_id'] ?? '');
    $helpful = $_POST['helpful'] === 'true';
    
    if (empty($reviewId)) {
        echo json_encode(['success' => false, 'message' => 'ID de reseña requerido']);
        return;
    }
    
    // Aquí podrías implementar la lógica para marcar como útil
    // Por ahora, simplemente devolvemos éxito
    echo json_encode([
        'success' => true,
        'message' => 'Voto registrado'
    ]);
}
?>
