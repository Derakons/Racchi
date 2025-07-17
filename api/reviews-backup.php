}

function handleGetReviews() {
// Deshabilitar errores en salida
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? 'get';
    
function handleSubmitReview() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => rqText('method_not_allowed')]);
        return;
    }
    // Validar CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => rqText('csrf_token_invalid')]);
        return;
    }
    // Validar datos mínimos
    $rating = intval($_POST['rating'] ?? 0);
    $content = sanitizeInput($_POST['review_content'] ?? '');
    if ($rating < 1 || $rating > 5 || strlen($content) < 10) {
        echo json_encode(['success' => false, 'message' => rqText('review_error')]);
        return;
    }
    // Insertar reseña en Supabase (solo comentario y calificación)
    try {
        $supabase = new SupabaseClient(true);
        $newReview = [
            'comentario'  => $content,
            'calificacion'=> $rating,
            'aprobada'    => null
        ];
        $result = $supabase->insert('resenas', $newReview);
        if ($result['success'] && !empty($result['data'][0]['id'])) {
            echo json_encode([
                'success'   => true,
                'message'   => rqText('review_pending'),
                'review_id' => $result['data'][0]['id']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => rqText('review_error')]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => rqText('review_error')]);
    }
}
            'rating' => $rating,
            'reviewer' => $name
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => rqText('review_pending'),
            'review_id' => $reviewId
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => rqText('review_error')]);
    }
}

function handleGetReviews() {
    try {
        $supabase = getSupabaseClient();
        
        // Obtener todas las reseñas (mostrar pendientes y aprobadas)
        $result = $supabase->select(
            'resenas', 
            'id,titulo,comentario,calificacion,nombre,email,created_at,aprobada',
            [],
            ['order' => 'created_at.desc', 'limit' => 50]
        );
        
        if ($result['success'] && !empty($result['data']) && is_array($result['data'])) {
            $reviews = array_map(function($review) {
                return [
                    'id' => $review['id'],
                    'name' => $review['nombre'] ?? 'Usuario Verificado',
                    'rating' => $review['calificacion'],
                    'title' => $review['titulo'] ?? '',
                    'content' => $review['comentario'],
                    'date' => $review['created_at'],
                    'location' => '',
                    'helpful_yes' => 0,
                    'helpful_no' => 0,
                    'verified' => true
                ];
            }, $result['data']);
            
            $stats = [
                'total_reviews' => count($reviews),
                'average_rating' => count($reviews) > 0 ? 
                    array_sum(array_column($reviews, 'rating')) / count($reviews) : 0,
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
            $reviews = [];
            
            $stats = [
                'total_reviews' => 0,
                'average_rating' => 0,
                'rating_distribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]
            ];
            
            echo json_encode([
                'success' => true,
                'reviews' => $reviews,
                'total' => 0,
                'has_more' => false,
                'stats' => $stats
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error del servidor',
            'error' => $e->getMessage()
        ]);
    }
}

function handleMarkHelpful() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => rqText('method_not_allowed')]);
        return;
    }
    
    $reviewId = intval($_POST['review_id'] ?? 0);
    $helpful = $_POST['helpful'] === 'true';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    if ($reviewId <= 0) {
        echo json_encode(['success' => false, 'message' => rqText('invalid_review_id')]);
        return;
    }
    
    // Verificar si ya votó desde esta IP
    if (hasVoted($reviewId, $ip)) {
        echo json_encode(['success' => false, 'message' => rqText('already_voted')]);
        return;
    }
    
    $success = markReviewHelpful($reviewId, $helpful, $ip);
    
    if ($success) {
        $helpfulCount = getHelpfulCount($reviewId);
        echo json_encode([
            'success' => true,
            'helpful_count' => $helpfulCount
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => rqText('vote_error')]);
    }
}

function reviewExists($email) {
    try {
        $supabase = getSupabaseClient();
        
        $result = $supabase->select(
            'resenas',
            'id',
            ['email' => $email],
            ['limit' => 1]
        );
        
        return $result['success'] && !empty($result['data']) && is_array($result['data']);
    } catch (Exception $e) {
        error_log("Error verificando reseña existente: " . $e->getMessage());
        return false;
    }
}

function saveReview($data) {
    try {
        // Insertar solo comentario y calificación; aprobada=NULL indica pendiente
        $supabase = getSupabaseClient();
        $reviewData = [
            'comentario'  => $data['content'],
            'calificacion'=> (int)$data['rating'],
            'aprobada'    => null
        ];
        $result = $supabase->insert('resenas', $reviewData);
        if ($result['success'] && !empty($result['data'][0]['id'])) {
            return $result['data'][0]['id'];
        }
        return false;
    } catch (Exception $e) {
        error_log("Error guardando reseña: " . $e->getMessage());
        return false;
    }
}

function getReviews($page = 1, $limit = 6, $sort = 'recent', $rating_filter = 0) {
    $reviewsFile = __DIR__ . '/../data/reviews.json';
    $sampleReviews = getSampleReviews();
    $userIp = $_SERVER['REMOTE_ADDR'] ?? '';
    
    $reviews = [];
    if (file_exists($reviewsFile)) {
        $savedReviews = json_decode(file_get_contents($reviewsFile), true) ?: [];
        // Normalizar estructura de datos y filtrar solo aprobadas
        $savedReviews = array_filter(array_map('normalizeReview', $savedReviews), function($review) {
            return isset($review['status']) && $review['status'] === 'approved';
        });
        $reviews = array_merge($savedReviews, $sampleReviews);
    } else {
        $reviews = $sampleReviews;
    }
    
    // Agregar información de voto del usuario actual
    foreach ($reviews as &$review) {
        $review['user_voted'] = hasVoted($review['id'], $userIp);
    }
    
    // Filtrar por calificación si se especifica
    if ($rating_filter > 0) {
        $reviews = array_filter($reviews, function($review) use ($rating_filter) {
            return $review['rating'] == $rating_filter;
        });
    }
    
    // Ordenar
    switch ($sort) {
        case 'rating_high':
            usort($reviews, function($a, $b) {
                return $b['rating'] - $a['rating'];
            });
            break;
        case 'rating_low':
            usort($reviews, function($a, $b) {
                return $a['rating'] - $b['rating'];
            });
            break;
        case 'helpful':
            usort($reviews, function($a, $b) {
                return $b['helpful_yes'] - $a['helpful_yes'];
            });
            break;
        case 'recent':
        default:
            usort($reviews, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            break;
    }
    
    $total = count($reviews);
    $offset = ($page - 1) * $limit;
    $paginatedReviews = array_slice($reviews, $offset, $limit);
    $hasMore = $offset + $limit < $total;
    
    return [
        'reviews' => $paginatedReviews,
        'total' => $total,
        'has_more' => $hasMore
    ];
}

function getReviewStats() {
    $reviewsFile = __DIR__ . '/../data/reviews.json';
    $sampleReviews = getSampleReviews();
    
    $reviews = $sampleReviews;
    if (file_exists($reviewsFile)) {
        $savedReviews = json_decode(file_get_contents($reviewsFile), true) ?: [];
        $approvedReviews = array_filter(array_map('normalizeReview', $savedReviews), function($review) {
            return isset($review['status']) && $review['status'] === 'approved';
        });
        $reviews = array_merge($approvedReviews, $sampleReviews);
    }
    
    $totalReviews = count($reviews);
    $totalRating = array_sum(array_column($reviews, 'rating'));
    $averageRating = $totalReviews > 0 ? round($totalRating / $totalReviews, 1) : 0;
    
    // Calcular tasa de satisfacción (4-5 estrellas)
    $satisfiedCount = count(array_filter($reviews, function($review) {
        return $review['rating'] >= 4;
    }));
    $satisfactionRate = $totalReviews > 0 ? round(($satisfiedCount / $totalReviews) * 100) : 0;
    
    return [
        'total_reviews' => $totalReviews,
        'average_rating' => $averageRating,
        'satisfaction_rate' => $satisfactionRate
    ];
}

function getSampleReviews() {
    return [
        [
            'id' => 'sample_1',
            'name' => 'María García',
            'location' => 'Lima, Perú',
            'rating' => 5,
            'content' => 'Una experiencia increíble visitando Raqchi. La arquitectura inca es impresionante y el paisaje del valle es simplemente hermoso. El guía fue muy conocedor y nos explicó la historia de manera fascinante. Definitivamente recomiendo esta visita a cualquier persona interesada en la cultura peruana.',
            'created_at' => '2024-03-15 14:30:00',
            'visit_date' => '2024-03-10',
            'helpful_yes' => 15,
            'helpful_no' => 2,
            'verified' => true,
            'status' => 'approved'
        ],
        [
            'id' => 'sample_2',
            'name' => 'John Smith',
            'location' => 'California, USA',
            'rating' => 5,
            'content' => 'Amazing archaeological site! The Temple of Wiracocha is breathtaking. I was impressed by the preservation of the structures and the beautiful views of the Andes. The staff was very helpful and spoke excellent English. A must-visit destination in Peru!',
            'created_at' => '2024-03-12 16:45:00',
            'visit_date' => '2024-03-08',
            'helpful_yes' => 12,
            'helpful_no' => 1,
            'verified' => true,
            'status' => 'approved'
        ],
        [
            'id' => 'sample_3',
            'name' => 'Ana Rodríguez',
            'location' => 'Buenos Aires, Argentina',
            'rating' => 4,
            'content' => 'Muy buen sitio arqueológico, aunque esperaba un poco más de información en español. Las ruinas son impresionantes y el entorno natural es precioso. La visita vale la pena, especialmente si te gusta la historia precolombina. El único punto negativo fue que hacía mucho viento ese día.',
            'created_at' => '2024-03-08 11:20:00',
            'visit_date' => '2024-03-05',
            'helpful_yes' => 8,
            'helpful_no' => 3,
            'verified' => true,
            'status' => 'approved'
        ],
        [
            'id' => 'sample_4',
            'name' => 'Carlos Mendoza',
            'location' => 'Cusco, Perú',
            'rating' => 5,
            'content' => 'Como cusqueño, me llena de orgullo tener sitios arqueológicos tan importantes cerca de casa. Raqchi es una joya que todos deberían conocer. La explanada del templo es enorme y te hace imaginar cómo era la vida en tiempos del Tahuantinsuyo. Excelente para venir en familia.',
            'created_at' => '2024-03-01 09:15:00',
            'visit_date' => '2024-02-28',
            'helpful_yes' => 20,
            'helpful_no' => 0,
            'verified' => true,
            'status' => 'approved'
        ],
        [
            'id' => 'sample_5',
            'name' => 'Sophie Dubois',
            'location' => 'Lyon, France',
            'rating' => 5,
            'content' => 'Magnifique site archéologique! La visite de Raqchi a été un des moments forts de notre voyage au Pérou. L\'architecture inca est remarquable et le paysage environnant est à couper le souffle. Le personnel était très accueillant malgré la barrière de la langue.',
            'created_at' => '2024-02-25 13:40:00',
            'visit_date' => '2024-02-20',
            'helpful_yes' => 14,
            'helpful_no' => 1,
            'verified' => true,
            'status' => 'approved'
        ],
        [
            'id' => 'sample_6',
            'name' => 'Roberto Silva',
            'location' => 'São Paulo, Brasil',
            'rating' => 4,
            'content' => 'Lugar incrível para conhecer a história inca. As ruínas são bem preservadas e a paisagem é deslumbrante. Recomendo contratar um guia para entender melhor a história do local. Única observação é que poderia ter mais infraestrutura para turistas, mas ainda assim vale muito a pena.',
            'created_at' => '2024-02-18 15:25:00',
            'visit_date' => '2024-02-15',
            'helpful_yes' => 11,
            'helpful_no' => 2,
            'verified' => true,
            'status' => 'approved'
        ]
    ];
}

function hasVoted($reviewId, $ip) {
    $votesFile = __DIR__ . '/../data/review_votes.json';
    if (!file_exists($votesFile)) return false;
    
    $votes = json_decode(file_get_contents($votesFile), true) ?: [];
    $voteKey = $reviewId . '_' . $ip;
    
    return isset($votes[$voteKey]);
}

function markReviewHelpful($reviewId, $helpful, $ip) {
    $votesFile = __DIR__ . '/../data/review_votes.json';
    $votesDir = dirname($votesFile);
    
    if (!is_dir($votesDir)) {
        mkdir($votesDir, 0755, true);
    }
    
    $votes = [];
    if (file_exists($votesFile)) {
        $votes = json_decode(file_get_contents($votesFile), true) ?: [];
    }
    
    $voteKey = $reviewId . '_' . $ip;
    $votes[$voteKey] = [
        'review_id' => $reviewId,
        'helpful' => $helpful,
        'ip' => $ip,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    return file_put_contents($votesFile, json_encode($votes, JSON_PRETTY_PRINT)) !== false;
}

function getHelpfulCount($reviewId) {
    $votesFile = __DIR__ . '/../data/review_votes.json';
    if (!file_exists($votesFile)) return ['yes' => 0, 'no' => 0];
    
    $votes = json_decode(file_get_contents($votesFile), true) ?: [];
    $yesCount = 0;
    $noCount = 0;
    
    foreach ($votes as $vote) {
        if ($vote['review_id'] == $reviewId) {
            if ($vote['helpful']) {
                $yesCount++;
            } else {
                $noCount++;
            }
        }
    }
    
    return ['yes' => $yesCount, 'no' => $noCount];
}

/**
 * Normalizar estructura de reseñas para compatibilidad entre admin y API
 */
function normalizeReview($review) {
    // Si ya está en formato nuevo, devolverlo tal como está
    if (isset($review['name']) && isset($review['content'])) {
        return $review;
    }
    
    // Convertir del formato admin al formato API
    return [
        'id' => $review['id'] ?? 0,
        'name' => $review['nombre_reviewer'] ?? $review['name'] ?? '',
        'email' => $review['email_reviewer'] ?? $review['email'] ?? '',
        'rating' => $review['rating'] ?? 0,
        'content' => $review['contenido'] ?? $review['content'] ?? '',
        'location' => $review['ubicacion'] ?? $review['location'] ?? '',
        'visit_date' => $review['fecha_visita'] ?? $review['visit_date'] ?? '',
        'created_at' => $review['fecha_creacion'] ?? $review['created_at'] ?? date('Y-m-d H:i:s'),
        'status' => mapStatus($review['estado'] ?? $review['status'] ?? 'pending'),
        'helpful_yes' => $review['helpful_yes'] ?? 0,
        'helpful_no' => $review['helpful_no'] ?? 0,
        'verified' => $review['verified'] ?? false,
        'ip_address' => $review['ip_address'] ?? '',
        'user_agent' => $review['user_agent'] ?? ''
    };
}

/**
 * Mapear estados entre formatos
 */
function mapStatus($status) {
    $statusMap = [
        'aprobada' => 'approved',
        'pendiente' => 'pending', 
        'rechazada' => 'rejected'
    ];
    
    return $statusMap[$status] ?? $status;
}
?>
