<?php
/**
 * API de Tickets/Entradas - Conectado a Supabase
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
    $action = $_POST['action'] ?? $_GET['action'] ?? 'get_types';
    
    switch ($action) {
        case 'get_types':
            handleGetTicketTypes();
            break;
        case 'get_tickets':
            handleGetTickets();
            break;
        case 'create_ticket':
            handleCreateTicket();
            break;
        case 'validate_ticket':
            handleValidateTicket();
            break;
        case 'update_ticket_status':
            handleUpdateTicketStatus();
            break;
        default:
            handleGetTicketTypes();
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor',
        'error' => $e->getMessage()
    ]);
}

/**
 * Obtener tipos de tickets disponibles
 */
function handleGetTicketTypes() {
    try {
        $supabase = new SupabaseClient();
        
        $result = $supabase->select(
            'tipos_tickets',
            'id,nombre,descripcion,precio,precio_estudiante,precio_grupo,imagen_url,caracteristicas,disponible',
            ['disponible' => true],
            ['order' => 'precio.asc']
        );
        
        if ($result['success'] && is_array($result['data'])) {
            echo json_encode([
                'success' => true,
                'ticket_types' => $result['data']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cargar tipos de tickets',
                'error' => $result['error'] ?? 'Unknown error'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al cargar tipos de tickets',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Obtener tickets (para admin)
 */
function handleGetTickets() {
    try {
        // Verificar que sea admin
        if (!isLoggedIn() || !hasRole('admin')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }
        
        $supabase = new SupabaseClient();
        
        // Parámetros de filtrado
        $estado = $_GET['estado'] ?? '';
        $fecha_desde = $_GET['fecha_desde'] ?? '';
        $fecha_hasta = $_GET['fecha_hasta'] ?? '';
        $limit = min(intval($_GET['limit'] ?? 50), 100);
        $offset = max(intval($_GET['offset'] ?? 0), 0);
        
        // Construir filtros
        $filters = [];
        if (!empty($estado)) {
            $filters['estado'] = $estado;
        }
        
        // Construir opciones de consulta
        $options = [
            'order' => 'created_at.desc',
            'limit' => $limit,
            'offset' => $offset
        ];
        
        $result = $supabase->select(
            'tickets',
            'id,codigo_ticket,nombre_comprador,email_comprador,telefono_comprador,cantidad,precio_total,fecha_visita,hora_visita,estado,metodo_pago,created_at,usado_at',
            $filters,
            $options
        );
        
        if ($result['success']) {
            // También obtener el conteo total
            $countResult = $supabase->select(
                'tickets',
                'id',
                $filters,
                ['count' => 'exact']
            );
            
            echo json_encode([
                'success' => true,
                'tickets' => $result['data'] ?? [],
                'total' => $countResult['count'] ?? count($result['data'] ?? []),
                'has_more' => count($result['data'] ?? []) >= $limit
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al cargar tickets',
                'error' => $result['error'] ?? 'Unknown error'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al cargar tickets',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Crear un nuevo ticket
 */
function handleCreateTicket() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }
    
    // Validar CSRF si está disponible
    if (function_exists('verifyCsrfToken') && !verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
        return;
    }
    
    try {
        // Validar datos requeridos
        $tipoTicketId = sanitizeInput($_POST['tipo_ticket_id'] ?? '');
        $nombreComprador = sanitizeInput($_POST['nombre_comprador'] ?? '');
        $emailComprador = sanitizeInput($_POST['email_comprador'] ?? '');
        $telefonoComprador = sanitizeInput($_POST['telefono_comprador'] ?? '');
        $documentoComprador = sanitizeInput($_POST['documento_comprador'] ?? '');
        $tipoDocumento = sanitizeInput($_POST['tipo_documento'] ?? 'DNI');
        $cantidad = max(1, intval($_POST['cantidad'] ?? 1));
        $fechaVisita = sanitizeInput($_POST['fecha_visita'] ?? '');
        $horaVisita = sanitizeInput($_POST['hora_visita'] ?? '');
        $metodoPago = sanitizeInput($_POST['metodo_pago'] ?? 'efectivo');
        $tipoEntrada = sanitizeInput($_POST['tipo_entrada'] ?? 'general'); // general, estudiante, grupo
        
        // Validaciones básicas
        if (empty($tipoTicketId) || empty($nombreComprador) || empty($emailComprador)) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
            return;
        }
        
        if (!filter_var($emailComprador, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email inválido']);
            return;
        }
        
        $supabase = new SupabaseClient(true); // Con privilegios de escritura
        
        // Obtener información del tipo de ticket
        $tipoResult = $supabase->select(
            'tipos_tickets',
            'precio,precio_estudiante,precio_grupo,nombre',
            ['id' => $tipoTicketId, 'disponible' => true]
        );
        
        if (!$tipoResult['success'] || empty($tipoResult['data']) || !is_array($tipoResult['data'])) {
            echo json_encode(['success' => false, 'message' => 'Tipo de ticket no válido']);
            return;
        }
        
        $tipoTicket = $tipoResult['data'][0];
        
        // Determinar precio según el tipo de entrada
        $precioUnitario = $tipoTicket['precio'];
        if ($tipoEntrada === 'estudiante' && $tipoTicket['precio_estudiante']) {
            $precioUnitario = $tipoTicket['precio_estudiante'];
        } elseif ($tipoEntrada === 'grupo' && $tipoTicket['precio_grupo']) {
            $precioUnitario = $tipoTicket['precio_grupo'];
        }
        
        $precioTotal = $precioUnitario * $cantidad;
        
        // Crear el ticket
        $newTicket = [
            'tipo_ticket_id' => $tipoTicketId,
            'nombre_comprador' => $nombreComprador,
            'email_comprador' => $emailComprador,
            'telefono_comprador' => $telefonoComprador,
            'documento_comprador' => $documentoComprador,
            'tipo_documento' => $tipoDocumento,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'precio_total' => $precioTotal,
            'fecha_visita' => $fechaVisita ?: null,
            'hora_visita' => $horaVisita ?: null,
            'estado' => 'pendiente',
            'metodo_pago' => $metodoPago,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        $result = $supabase->insert('tickets', $newTicket);
        
        if ($result['success'] && !empty($result['data']) && is_array($result['data'])) {
            $ticket = $result['data'][0];
            
            // Log de actividad
            if (function_exists('logActivity')) {
                logActivity('Ticket creado', 'info', [
                    'ticket_id' => $ticket['id'],
                    'codigo' => $ticket['codigo_ticket'],
                    'comprador' => $nombreComprador
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Ticket creado exitosamente',
                'ticket' => [
                    'id' => $ticket['id'],
                    'codigo_ticket' => $ticket['codigo_ticket'],
                    'precio_total' => $precioTotal,
                    'tipo_ticket' => $tipoTicket['nombre']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear el ticket',
                'error' => $result['error'] ?? 'Unknown error'
            ]);
        }
    } catch (Exception $e) {
        error_log("Error creating ticket: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear el ticket',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Validar un ticket (marcar como usado)
 */
function handleValidateTicket() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }
    
    // Verificar que sea admin
    if (!isLoggedIn() || !hasRole('admin')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        return;
    }
    
    try {
        $codigoTicket = sanitizeInput($_POST['codigo_ticket'] ?? '');
        
        if (empty($codigoTicket)) {
            echo json_encode(['success' => false, 'message' => 'Código de ticket requerido']);
            return;
        }
        
        $supabase = new SupabaseClient(true);
        
        // Buscar el ticket
        $ticketResult = $supabase->select(
            'tickets',
            'id,estado,nombre_comprador,fecha_visita',
            ['codigo_ticket' => $codigoTicket]
        );
        
        if (!$ticketResult['success'] || empty($ticketResult['data']) || !is_array($ticketResult['data'])) {
            echo json_encode(['success' => false, 'message' => 'Ticket no encontrado']);
            return;
        }
        
        $ticket = $ticketResult['data'][0];
        
        if ($ticket['estado'] === 'usado') {
            echo json_encode(['success' => false, 'message' => 'Ticket ya ha sido usado']);
            return;
        }
        
        if ($ticket['estado'] !== 'pagado') {
            echo json_encode(['success' => false, 'message' => 'Ticket no está en estado válido para usar']);
            return;
        }
        
        // Marcar como usado
        $updateResult = $supabase->update(
            'tickets',
            [
                'estado' => 'usado',
                'usado_at' => date('Y-m-d H:i:s'),
                'usado_por' => $_SESSION['user_id'] ?? null
            ],
            ['id' => $ticket['id']]
        );
        
        if ($updateResult['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Ticket validado exitosamente',
                'ticket' => [
                    'codigo' => $codigoTicket,
                    'comprador' => $ticket['nombre_comprador'],
                    'fecha_visita' => $ticket['fecha_visita']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al validar ticket']);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al validar ticket',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Actualizar estado de ticket (para admin)
 */
function handleUpdateTicketStatus() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }
    
    // Verificar que sea admin
    if (!isLoggedIn() || !hasRole('admin')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
        return;
    }
    
    try {
        $ticketId = sanitizeInput($_POST['ticket_id'] ?? '');
        $nuevoEstado = sanitizeInput($_POST['estado'] ?? '');
        
        $estadosValidos = ['pendiente', 'pagado', 'usado', 'cancelado'];
        
        if (empty($ticketId) || !in_array($nuevoEstado, $estadosValidos)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }
        
        $supabase = new SupabaseClient(true);
        
        $updateData = ['estado' => $nuevoEstado];
        if ($nuevoEstado === 'usado') {
            $updateData['usado_at'] = date('Y-m-d H:i:s');
            $updateData['usado_por'] = $_SESSION['user_id'] ?? null;
        }
        
        $result = $supabase->update('tickets', $updateData, ['id' => $ticketId]);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Estado del ticket actualizado'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar ticket']);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar ticket',
            'error' => $e->getMessage()
        ]);
    }
}
?>
