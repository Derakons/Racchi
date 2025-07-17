<?php
/**
 * API para procesar envío de reseñas
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
require_once __DIR__ . '/../includes/bootstrap.php';

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Verificar CSRF token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => rqText('invalid_token')]);
    exit;
}

// Validar datos
$reviewerName = sanitizeInput($_POST['reviewer_name'] ?? '');
$reviewerEmail = sanitizeInput($_POST['reviewer_email'] ?? '');
$rating = intval($_POST['rating'] ?? 0);
$reviewContent = sanitizeInput($_POST['review_content'] ?? '');

$errors = [];

if (empty($reviewerName)) {
    $errors[] = 'El nombre es requerido';
}

if (empty($reviewerEmail) || !filter_var($reviewerEmail, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'El correo electrónico es requerido y debe ser válido';
}

if ($rating < 1 || $rating > 5) {
    $errors[] = 'La calificación debe ser entre 1 y 5 estrellas';
}

if (empty($reviewContent) || strlen($reviewContent) < 10) {
    $errors[] = 'La reseña debe tener al menos 10 caracteres';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => implode(', ', $errors)]);
    exit;
}

try {
    // Inicializar cliente de Supabase con service role para inserciones
    $supabase = new SupabaseClient(true);
    
    // Mapear datos de formulario a columnas existentes de la tabla resenas
    $reviewData = [
        'comentario'   => $reviewContent,
        'calificacion' => $rating,
        'aprobada'     => null
    ];
    
    $result = $supabase->insert('resenas', $reviewData);
    
    if ($result['success']) {
        // Log de la actividad
        logActivity('review_submitted', [
            'reviewer_name' => $reviewerName,
            'rating' => $rating,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Reseña enviada exitosamente']);
    } else {
        throw new Exception('Error al guardar la reseña en la base de datos');
    }
    
} catch (Exception $e) {
    error_log("Error al procesar reseña: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}
?>
