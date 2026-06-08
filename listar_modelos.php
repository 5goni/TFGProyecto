<?php
/**
 * ============================================================================
 * ARCHIVO: listar_modelos.php
 * ============================================================================
 * PROPÓSITO:
 *   Herramienta de diagnóstico que lista todos los modelos de IA disponibles
 *   en la cuenta de Google Generative Language API.
 *   Útil para verificar que la API Key sea válida y explorar qué modelos
 *   están disponibles para generar contenido.
 *
 * FUNCIONALIDAD CLAVE:
 *   - Requiere api_key.php para obtener la clave de API
 *   - Realiza petición HTTP GET a Google Generative Language API
 *   - Filtra solo modelos que soportan 'generateContent'
 *   - Muestra ID, nombre y descripción de cada modelo
 *   - Útil para debugging y verificación de configuración
 *
 * RESPUESTA:
 *   Muestra lista en formato <pre> con:
 *   - ID: nombre del modelo (ej: models/gemini-2.5-flash)
 *   - Descripción: información del modelo
 *
 * DEPENDENCIAS:
 *   - api_key.php (clave de API)
 *   - cURL habilitado en PHP
 *   - Conexión a internet
 *
 * NOTAS:
 *   - Página de diagnóstico (no está integrada en la app)
 *   - Acceso directo: http://localhost/tfg/TFG/listar_modelos.php
 *
 * ============================================================================
 */

require_once 'api_key.php';
$apiKey = API_KEY;
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$decoded = json_decode($response, true);

echo "<h2>Modelos disponibles para tu cuenta:</h2>";
echo "<pre>";

if (isset($decoded['models'])) {
    foreach ($decoded['models'] as $model) {
        // Solo mostramos los que permiten generar contenido
        if (in_array("generateContent", $model['supportedGenerationMethods'])) {
            echo "ID: <b>" . $model['name'] . "</b>\n";
            echo "Descripción: " . $model['description'] . "\n";
            echo "------------------------------------------\n";
        }
    }
} else {
    print_r($decoded);
}

echo "</pre>";
curl_close($ch);
?>