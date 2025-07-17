<?php
/**
 * Clase para manejo de conexión con Supabase
 */

// Prevenir inclusión múltiple
if (defined('RACCHI_SUPABASE_LOADED')) {
    return;
}
define('RACCHI_SUPABASE_LOADED', true);

class SupabaseClient {
    private $url;
    private $key;
    private $headers;
    
    public function __construct($useServiceRole = false) {
        $this->url = SUPABASE_URL;
        $this->key = $useServiceRole ? SUPABASE_SERVICE_ROLE_KEY : SUPABASE_ANON_KEY;
        
        $this->headers = [
            'Content-Type: application/json',
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key
        ];
    }
    
    /**
     * Realizar consulta SELECT
     */
    public function select($table, $columns = '*', $filters = [], $options = []) {
        $url = $this->url . '/rest/v1/' . $table . '?select=' . $columns;
        
        // Agregar filtros
        foreach ($filters as $column => $value) {
            if (is_array($value)) {
                $operator = $value['operator'] ?? 'eq';
                $filterValue = $value['value'];
                $url .= '&' . $column . '=' . $operator . '.' . urlencode($filterValue);
            } else {
                $url .= '&' . $column . '=eq.' . urlencode($value);
            }
        }
        
        // Agregar ordenamiento
        if (!empty($options['order'])) {
            $url .= '&order=' . $options['order'];
        }
        
        // Agregar límite
        if (!empty($options['limit'])) {
            $url .= '&limit=' . $options['limit'];
        }
        
        // Agregar offset
        if (!empty($options['offset'])) {
            $url .= '&offset=' . $options['offset'];
        }
        
        return $this->makeRequest('GET', $url);
    }
    
    /**
     * Realizar inserción
     */
    public function insert($table, $data) {
        $url = $this->url . '/rest/v1/' . $table;
        return $this->makeRequest('POST', $url, $data);
    }
    
    /**
     * Realizar actualización
     */
    public function update($table, $data, $filters) {
        $url = $this->url . '/rest/v1/' . $table;
        
        foreach ($filters as $column => $value) {
            $url .= '?' . $column . '=eq.' . urlencode($value);
            break; // Solo el primer filtro para el WHERE
        }
        
        return $this->makeRequest('PATCH', $url, $data);
    }
    
    /**
     * Realizar eliminación
     */
    public function delete($table, $filters) {
        $url = $this->url . '/rest/v1/' . $table;
        
        foreach ($filters as $column => $value) {
            $url .= '?' . $column . '=eq.' . urlencode($value);
            break;
        }
        
        return $this->makeRequest('DELETE', $url);
    }
    
    /**
     * Subir archivo al Storage
     */
    public function uploadFile($bucket, $path, $file) {
        $url = $this->url . '/storage/v1/object/' . $bucket . '/' . $path;
        
        $headers = [
            'Authorization: Bearer ' . $this->key,
            'Content-Type: ' . mime_content_type($file)
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => json_decode($result, true),
            'http_code' => $httpCode
        ];
    }
    
    /**
     * Contar registros
     */
    public function count($table, $filters = []) {
        $url = $this->url . '/rest/v1/' . $table . '?select=count()';
        
        // Agregar filtros
        foreach ($filters as $column => $value) {
            if (is_array($value)) {
                $operator = $value['operator'] ?? 'eq';
                $filterValue = $value['value'];
                $url .= '&' . $column . '=' . $operator . '.' . urlencode($filterValue);
            } else {
                $url .= '&' . $column . '=eq.' . urlencode($value);
            }
        }
        
        $result = $this->makeRequest('GET', $url);
        
        if ($result['success'] && !empty($result['data'])) {
            return [
                'success' => true,
                'data' => $result['data'][0]['count'] ?? 0
            ];
        }
        
        return ['success' => false, 'data' => 0];
    }
    
    /**
     * Realizar petición HTTP
     */
    private function makeRequest($method, $url, $data = null) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($data && ($method === 'POST' || $method === 'PATCH')) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'http_code' => $httpCode,
                'data' => null
            ];
        }
        
        $decodedResult = json_decode($result, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $decodedResult,
                'http_code' => $httpCode,
                'error' => null
            ];
        } else {
            return [
                'success' => false,
                'data' => null,
                'http_code' => $httpCode,
                'error' => isset($decodedResult['message']) ? $decodedResult['message'] : 'Error HTTP ' . $httpCode
            ];
        }
    }
}

/**
 * Función helper para obtener instancia de Supabase
 */
function getSupabaseClient($useServiceRole = false) {
    return new SupabaseClient($useServiceRole);
}
?>
