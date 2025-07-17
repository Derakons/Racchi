<?php
/**
 * Script de verificación de conexión a Supabase
 * Ejecutar desde: http://localhost/Racchi/setup/test_supabase.php
 */

if (!defined('RACCHI_ACCESS')) {
    define('RACCHI_ACCESS', true);
}
require_once __DIR__ . '/../includes/bootstrap.php';

echo "<h1>Test de Conexión Supabase</h1>";
echo "<h2>Portal Digital Raqchi</h2>";

// Test de configuración
echo "<h3>1. Configuraciones</h3>";
echo "SUPABASE_URL: " . (defined('SUPABASE_URL') ? SUPABASE_URL : 'NO DEFINIDA') . "<br>";
echo "SUPABASE_ANON_KEY: " . (defined('SUPABASE_ANON_KEY') ? 'DEFINIDA (oculta)' : 'NO DEFINIDA') . "<br>";
echo "SUPABASE_SERVICE_ROLE_KEY: " . (defined('SUPABASE_SERVICE_ROLE_KEY') ? 'DEFINIDA (oculta)' : 'NO DEFINIDA') . "<br>";

// Test de conexión básica
echo "<h3>2. Test de Conexión</h3>";
try {
    $supabase = getSupabaseClient();
    echo "✅ Cliente Supabase creado correctamente<br>";
    
    // Test de ping básico
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SUPABASE_URL . '/rest/v1/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_ANON_KEY
    ]);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✅ Conexión a Supabase exitosa (HTTP $httpCode)<br>";
    } else {
        echo "❌ Error de conexión a Supabase (HTTP $httpCode)<br>";
        echo "Respuesta: " . htmlspecialchars($result) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error creando cliente: " . $e->getMessage() . "<br>";
}

// Test de tablas existentes
echo "<h3>3. Test de Tablas</h3>";
try {
    $supabase = getSupabaseClient();
    
    // Listar tablas
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SUPABASE_URL . '/rest/v1/?select=*');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_ANON_KEY
    ]);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Código HTTP para listar tablas: $httpCode<br>";
    
    // Probar tabla usuarios específicamente
    $result = $supabase->select('usuarios', 'id', [], null, 1);
    
    if ($result['success']) {
        echo "✅ Tabla 'usuarios' existe y es accesible<br>";
        echo "Datos encontrados: " . count($result['data']) . " registros<br>";
    } else {
        echo "❌ Error accediendo tabla 'usuarios'<br>";
        echo "HTTP Code: " . $result['http_code'] . "<br>";
        echo "Error: " . htmlspecialchars(json_encode($result['data'])) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error probando tablas: " . $e->getMessage() . "<br>";
}

// Test de inserción simulada
echo "<h3>4. Test de Configuraciones</h3>";
try {
    $result = $supabase->select('configuraciones', 'clave,valor', [], null, 5);
    
    if ($result['success']) {
        echo "✅ Tabla 'configuraciones' accesible<br>";
        echo "Configuraciones encontradas: " . count($result['data']) . "<br>";
        foreach ($result['data'] as $config) {
            echo "- " . $config['clave'] . ": " . $config['valor'] . "<br>";
        }
    } else {
        echo "❌ Error accediendo configuraciones<br>";
        echo "HTTP Code: " . $result['http_code'] . "<br>";
        echo "Error: " . htmlspecialchars(json_encode($result['data'])) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error probando configuraciones: " . $e->getMessage() . "<br>";
}

echo "<h3>5. Diagnóstico</h3>";

// Diagnosticar el problema
if (!defined('SUPABASE_URL')) {
    echo "❌ CRÍTICO: Las constantes de Supabase no están definidas<br>";
    echo "➡️ Verifica el archivo config/config.php<br>";
} else {
    echo "✅ Configuración básica presente<br>";
    
    // Test de headers
    echo "<h4>Headers de prueba:</h4>";
    echo "Content-Type: application/json<br>";
    echo "apikey: " . substr(SUPABASE_ANON_KEY, 0, 20) . "...<br>";
    echo "Authorization: Bearer " . substr(SUPABASE_ANON_KEY, 0, 20) . "...<br>";
}

echo "<hr>";
echo "<h3>Instrucciones de Solución</h3>";
echo "<ol>";
echo "<li><strong>Ve a Supabase Dashboard:</strong> <a href='https://supabase.com/dashboard' target='_blank'>https://supabase.com/dashboard</a></li>";
echo "<li><strong>Abre tu proyecto</strong> (evitjnpybszhtaeeehuk)</li>";
echo "<li><strong>Ve a SQL Editor</strong> en el menú lateral</li>";
echo "<li><strong>Ejecuta el archivo:</strong> <code>setup/supabase_setup.sql</code></li>";
echo "<li><strong>Luego ejecuta:</strong> <code>setup/datos_iniciales.sql</code></li>";
echo "<li><strong>Recarga esta página</strong> para verificar</li>";
echo "</ol>";

echo "<p><strong>¿Las tablas ya existen?</strong> El error indica problemas con las políticas RLS. Ejecuta este comando en Supabase:</p>";
echo "<pre>DROP POLICY IF EXISTS \"Usuarios pueden crear usuarios\" ON usuarios;</pre>";

echo "<hr>";
echo "<p><small>Test ejecutado: " . date('Y-m-d H:i:s') . "</small></p>";
?>
