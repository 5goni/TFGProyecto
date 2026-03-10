<?php
session_start();

// --- 1. CONEXIÓN A MYSQL Y VERIFICACIÓN DE SESIÓN ---
include 'conn.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- 2. CONFIGURACIÓN API GEMINI ---
$apiKey = 'AIzaSyBuCCzzEbuf5kFdaH5q8LR9qW69G_plzEs';
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$systemPrompt = "Eres un profesor experto. No saludes, no des introducciones, no escribas frases como 'Aquí tienes', 'A continuación' ni similares. No uses separadores como ---. Empieza directamente con el contenido. En cuanto al test interactivo hazlo de 5-10 preguntas. Responde estrictamente en este formato:
RESUMEN: (resumen claro y completo, directo al contenido, usando **negrita** en conceptos clave)
JSON_START
[{\"p\":\"¿...?\",\"o\":[\"A\",\"B\",\"C\",\"D\"],\"correcta\":0}]
JSON_END";

$userInput = !empty($_POST['pregunta']) ? $_POST['pregunta'] : "Cultura general";
$promptFinal = "Analiza y genera resumen y trivia sobre: " . $userInput;

// Manejo de archivo
$inlineData = null;
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $base64Data = base64_encode(file_get_contents($_FILES['archivo']['tmp_name']));
    $inlineData = ["mime_type" => $_FILES['archivo']['type'], "data" => $base64Data];
}

$payload = [
    "system_instruction" => ["parts" => [["text" => $systemPrompt]]],
    "contents" => [["parts" => [["text" => $promptFinal]]]]
];
if ($inlineData) { $payload["contents"][0]["parts"][] = ["inline_data" => $inlineData]; }

// --- 3. EJECUTAR CURL ---
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

// --- 4. PROCESAR RESPUESTA Y GUARDAR EN DB ---
if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $rawText = $result['candidates'][0]['content']['parts'][0]['text'];

    preg_match('/RESUMEN:(.*?)JSON_START/s', $rawText, $resMatch);
    $resumen = trim($resMatch[1] ?? "Resumen no disponible");

    preg_match('/JSON_START(.*?)JSON_END/s', $rawText, $jsonMatch);
    $jsonFinal = isset($jsonMatch[1]) ? trim($jsonMatch[1]) : '[]';

    // Insertar registro inicial con user_id
    $stmt = $conn->prepare("INSERT INTO historial (user_id, pregunta, resumen) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $userInput, $resumen);
    $stmt->execute();
    $id_registro = $conn->insert_id;
} else {
    die("Error en la IA. Revisa tu API Key o conexión.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Profesor Particular Interactivo</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .resumen { font-size: 1.1em; line-height: 1.6; border-left: 5px solid #1a73e8; padding-left: 15px; margin-bottom: 30px; }
        .quiz-box { background: #46178f; color: white; padding: 25px; border-radius: 12px; }
        button { display:block; width:100%; margin: 8px 0; padding: 12px; border: none; border-radius: 8px; cursor: pointer; background: white; color: #46178f; font-weight: bold; font-size:16px; transition: 0.2s; }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
        #score-final { text-align: center; font-size: 1.5em; margin-top: 20px; display: none; color: #ffa000; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>👨‍🏫 Resumen de la Lección</h2>
    <div class="resumen"><?php echo nl2br(preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', htmlspecialchars($resumen))); ?></div>

    <div class="quiz-box">
        <h2 style="text-align:center; margin-top:0;">🎮 Trivia Interactiva</h2>
        <div id="quiz"></div>
        <div id="score-final"></div>
    </div>

    <br><center><a href="index.html" style="text-decoration:none; color:#1a73e8; font-weight:bold;">⬅ Volver a preguntar</a></center>
</div>



<script>
    const data = <?php echo $jsonFinal; ?>;
    let aciertos = 0;
    let respondidas = 0;
    const idRegistro = <?php echo $id_registro; ?>;

    function renderQuiz() {
        const quizDiv = document.getElementById('quiz');
        let html = '';
        data.forEach((q, i) => {
            html += `<div style="margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.2); padding-bottom:15px;">
                <p><strong>${i+1}. ${q.p}</strong></p>
                <div id="opts-${i}">
                    ${q.o.map((opt, idx) => `<button onclick="checkAnswer(${i}, ${idx}, this)">${opt}</button>`).join('')}
                </div>
            </div>`;
        });
        quizDiv.innerHTML = html;
    }

    function checkAnswer(qIdx, oIdx, btn) {
        const correct = data[qIdx].correcta;
        const buttons = document.getElementById(`opts-${qIdx}`).querySelectorAll('button');
        
        buttons.forEach(b => b.disabled = true);
        respondidas++;

        if (oIdx === correct) {
            btn.style.background = "#4CAF50"; btn.style.color = "white";
            aciertos++;
        } else {
            btn.style.background = "#f44336"; btn.style.color = "white";
            buttons[correct].style.background = "#4CAF50"; buttons[correct].style.color = "white";
        }

        if (respondidas === data.length) {
            finalizarJuego();
        }
    }

    function finalizarJuego() {
        document.getElementById('score-final').style.display = "block";
        document.getElementById('score-final').innerHTML = `¡Juego Terminado! Aciertos: ${aciertos} de ${data.length}`;
        
        // Enviar resultado a la DB por AJAX
        fetch('guardar_puntos.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${idRegistro}&aciertos=${aciertos}&total=${data.length}`
        });
    }

    renderQuiz();
</script>
</body>
</html>