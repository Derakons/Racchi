<?php
/**
 * API para guardar configuraciones del sistema
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}

require_once __DIR__ . '/../../includes/bootstrap.php';

// Verificar autenticación y rol
requireRole('admin');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

header('Content-Type: application/json');

try {
    $formId = $_POST['form_id'] ?? '';
    $response = ['success' => false, 'message' => 'Configuración no válida'];
    
    switch ($formId) {
        case 'generalSettings':
            $response = saveGeneralSettings($_POST);
            break;
            
        case 'ticketSettings':
            $response = saveTicketSettings($_POST);
            break;
            
        case 'languageSettings':
            $response = saveLanguageSettings($_POST);
            break;
            
        case 'paymentSettings':
            $response = savePaymentSettings($_POST);
            break;
            
        case 'emailSettings':
            $response = saveEmailSettings($_POST);
            break;
            
        case 'maintenanceSettings':
            $response = saveMaintenanceSettings($_POST);
            break;
            
        default:
            // Try to detect form by fields
            if (isset($_POST['site_name'])) {
                $response = saveGeneralSettings($_POST);
            } elseif (isset($_POST['adult_price'])) {
                $response = saveTicketSettings($_POST);
            } elseif (isset($_POST['languages'])) {
                $response = saveLanguageSettings($_POST);
            } elseif (isset($_POST['payment_methods'])) {
                $response = savePaymentSettings($_POST);
            } elseif (isset($_POST['smtp_host'])) {
                $response = saveEmailSettings($_POST);
            } elseif (isset($_POST['maintenance_mode']) || isset($_POST['maintenance_message'])) {
                $response = saveMaintenanceSettings($_POST);
            }
            break;
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error saving settings: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}

function saveGeneralSettings($data) {
    try {
        $settings = [
            'site_name' => sanitizeInput($data['site_name'] ?? ''),
            'site_description' => sanitizeInput($data['site_description'] ?? ''),
            'contact_email' => filter_var($data['contact_email'] ?? '', FILTER_VALIDATE_EMAIL),
            'contact_phone' => sanitizeInput($data['contact_phone'] ?? '')
        ];
        
        // Validar datos requeridos
        if (empty($settings['site_name']) || !$settings['contact_email']) {
            return ['success' => false, 'message' => 'Datos requeridos faltantes'];
        }
        
        // Guardar en archivo de configuración (simulado)
        $configFile = __DIR__ . '/../../config/site_settings.json';
        file_put_contents($configFile, json_encode($settings, JSON_PRETTY_PRINT));
        
        logActivity('settings_updated', 'general', $settings);
        
        return ['success' => true, 'message' => 'Configuración general guardada exitosamente'];
        
    } catch (Exception $e) {
        error_log("Error saving general settings: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al guardar configuración general'];
    }
}

function saveTicketSettings($data) {
    try {
        $settings = [
            'adult_price' => floatval($data['adult_price'] ?? 0),
            'student_price' => floatval($data['student_price'] ?? 0),
            'child_price' => floatval($data['child_price'] ?? 0),
            'daily_capacity' => intval($data['daily_capacity'] ?? 0)
        ];
        
        // Validar precios
        if ($settings['adult_price'] <= 0 || $settings['student_price'] <= 0 || 
            $settings['child_price'] <= 0 || $settings['daily_capacity'] <= 0) {
            return ['success' => false, 'message' => 'Todos los valores deben ser mayores a cero'];
        }
        
        $configFile = __DIR__ . '/../../config/ticket_settings.json';
        file_put_contents($configFile, json_encode($settings, JSON_PRETTY_PRINT));
        
        logActivity('settings_updated', 'tickets', $settings);
        
        return ['success' => true, 'message' => 'Configuración de tickets guardada exitosamente'];
        
    } catch (Exception $e) {
        error_log("Error saving ticket settings: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al guardar configuración de tickets'];
    }
}

function saveLanguageSettings($data) {
    try {
        $availableLanguages = ['es', 'en', 'qu'];
        $selectedLanguages = $data['languages'] ?? [];
        $defaultLanguage = $data['default_language'] ?? 'es';
        
        // Validar idiomas seleccionados
        $validLanguages = array_intersect($selectedLanguages, $availableLanguages);
        if (empty($validLanguages)) {
            return ['success' => false, 'message' => 'Debe seleccionar al menos un idioma'];
        }
        
        // Validar idioma por defecto
        if (!in_array($defaultLanguage, $validLanguages)) {
            return ['success' => false, 'message' => 'El idioma por defecto debe estar entre los seleccionados'];
        }
        
        $settings = [
            'available_languages' => $validLanguages,
            'default_language' => $defaultLanguage
        ];
        
        $configFile = __DIR__ . '/../../config/language_settings.json';
        file_put_contents($configFile, json_encode($settings, JSON_PRETTY_PRINT));
        
        logActivity('settings_updated', 'languages', $settings);
        
        return ['success' => true, 'message' => 'Configuración de idiomas guardada exitosamente'];
        
    } catch (Exception $e) {
        error_log("Error saving language settings: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al guardar configuración de idiomas'];
    }
}

function savePaymentSettings($data) {
    try {
        $availablePaymentMethods = ['cash', 'card', 'yape', 'plin'];
        $selectedMethods = $data['payment_methods'] ?? [];
        
        // Validar métodos de pago
        $validMethods = array_intersect($selectedMethods, $availablePaymentMethods);
        if (empty($validMethods)) {
            return ['success' => false, 'message' => 'Debe seleccionar al menos un método de pago'];
        }
        
        $settings = [
            'payment_methods' => $validMethods,
            'yape_number' => sanitizeInput($data['yape_number'] ?? ''),
            'plin_number' => sanitizeInput($data['plin_number'] ?? '')
        ];
        
        $configFile = __DIR__ . '/../../config/payment_settings.json';
        file_put_contents($configFile, json_encode($settings, JSON_PRETTY_PRINT));
        
        logActivity('settings_updated', 'payments', $settings);
        
        return ['success' => true, 'message' => 'Configuración de pagos guardada exitosamente'];
        
    } catch (Exception $e) {
        error_log("Error saving payment settings: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al guardar configuración de pagos'];
    }
}

function saveEmailSettings($data) {
    try {
        $settings = [
            'smtp_host' => sanitizeInput($data['smtp_host'] ?? ''),
            'smtp_port' => intval($data['smtp_port'] ?? 587),
            'smtp_username' => sanitizeInput($data['smtp_username'] ?? ''),
            'smtp_password' => $data['smtp_password'] ?? '',
            'smtp_secure' => isset($data['smtp_secure'])
        ];
        
        // Validar datos requeridos
        if (empty($settings['smtp_host']) || empty($settings['smtp_username'])) {
            return ['success' => false, 'message' => 'Host SMTP y usuario son requeridos'];
        }
        
        // No guardar contraseña si es placeholder
        if ($settings['smtp_password'] === '••••••••') {
            unset($settings['smtp_password']);
        }
        
        $configFile = __DIR__ . '/../../config/email_settings.json';
        file_put_contents($configFile, json_encode($settings, JSON_PRETTY_PRINT));
        
        logActivity('settings_updated', 'email', array_merge($settings, ['smtp_password' => '[HIDDEN]']));
        
        return ['success' => true, 'message' => 'Configuración de email guardada exitosamente'];
        
    } catch (Exception $e) {
        error_log("Error saving email settings: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al guardar configuración de email'];
    }
}

function saveMaintenanceSettings($data) {
    try {
        $settings = [
            'maintenance_mode' => isset($data['maintenance_mode']),
            'maintenance_message' => sanitizeInput($data['maintenance_message'] ?? '')
        ];
        
        $configFile = __DIR__ . '/../../config/maintenance_settings.json';
        file_put_contents($configFile, json_encode($settings, JSON_PRETTY_PRINT));
        
        logActivity('settings_updated', 'maintenance', $settings);
        
        return ['success' => true, 'message' => 'Configuración de mantenimiento guardada exitosamente'];
        
    } catch (Exception $e) {
        error_log("Error saving maintenance settings: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error al guardar configuración de mantenimiento'];
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function logActivity($action, $type, $data) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $_SESSION['user_id'] ?? 0,
        'user_name' => $_SESSION['user_name'] ?? 'Unknown',
        'action' => $action,
        'type' => $type,
        'data' => $data,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ];
    
    $logFile = __DIR__ . '/../../logs/admin_activity.log';
    file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
}
?>
