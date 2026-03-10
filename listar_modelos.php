<?php
$apiKey = 'AIzaSyBuCCzzEbuf5kFdaH5q8LR9qW69G_plzEs';
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