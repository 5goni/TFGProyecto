<?php
/**
 * ============================================================================
 * ARCHIVO: test.php
 * ============================================================================
 * PROPÓSITO:
 *   Genera tests interactivos de opción múltiple basados en temas usando IA.
 *   Permite a usuarios practicar conocimientos con evaluación automática.
 *
 * FUNCIONALIDAD CLAVE:
 *   - Valida autenticación del usuario
 *   - Genera 8-10 preguntas de opción múltiple (4 opciones cada una)
 *   - Soporta entrada de texto (tema) o archivo adjunto
 *   - Respuesta en formato JSON con preguntas y respuesta correcta
 *   - Almacena test en tabla 'historial' con tipo 'test'
 *   - Interfaz interactiva con puntuación en tiempo real
 *   - Sistema AJAX para guardar resultados sin recargar
 *
 * ESTRUCTURA JSON DE RESPUESTA:
 *   [
 *     {
 *       "p": "¿Pregunta?",
 *       "o": ["Opción A", "Opción B", "Opción C", "Opción D"],
 *       "correcta": 0  (índice de la respuesta correcta: 0-3)
 *     }
 *   ]
 *
 * FLUJO DE DATOS:
 *   1. Usuario envía tema (texto o archivo)
 *   2. Sistema solicita test a Gemini
 *   3. Gemini responde con JSON entre JSON_START y JSON_END
 *   4. Se extrae y valida JSON
 *   5. Se almacena en tabla historial
 *   6. Se muestra interfaz interactiva
 *   7. Usuario responde preguntas
 *   8. Sistema calcula puntuación y guarda resultados vía AJAX
 *
 * DEPENDENCIAS:
 *   - conn.php (conexión a BD)
 *   - api_key.php (clave de Gemini)
 *   - Session activa
 *   - API Gemini
 *
 * ============================================================================
 */

session_start();
include 'conn.php';
require_once 'api_key.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['username'] ?? 'Usuario';
$user_id = $_SESSION['user_id'];
$jsonFinal = null;
$temaGenerado = null;
$id_registro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tema'])) {
    $apiKey = API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

    $systemPrompt = "Eres un profesor experto. No saludes, no des introducciones. Genera únicamente un test de 8-10 preguntas de opción múltiple (4 opciones cada una) sobre el tema solicitado. Responde estrictamente en este formato y nada más:
JSON_START
[{\"p\":\"¿Pregunta?\",\"o\":[\"Opción A\",\"Opción B\",\"Opción C\",\"Opción D\"],\"correcta\":0}]
JSON_END
Donde \"correcta\" es el índice (0-3) de la opción correcta.";

    $temaGenerado = trim($_POST['tema']);
    $promptFinal = "Genera un test de 8-10 preguntas sobre: " . $temaGenerado;

    $payload = [
        "system_instruction" => ["parts" => [["text" => $systemPrompt]]],
        "contents" => [["parts" => [["text" => $promptFinal]]]]
    ];

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $base64Data = base64_encode(file_get_contents($_FILES['archivo']['tmp_name']));
        $payload["contents"][0]["parts"][] = ["inline_data" => ["mime_type" => $_FILES['archivo']['type'], "data" => $base64Data]];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
        preg_match('/JSON_START(.*?)JSON_END/s', $rawText, $jsonMatch);
        $jsonFinal = isset($jsonMatch[1]) ? trim($jsonMatch[1]) : '[]';

        $stmt = $conn->prepare("INSERT INTO historial (user_id, pregunta, resumen, tipo) VALUES (?, ?, ?, 'test')");
        if ($stmt) {
            $resumenVacio = '';
            $stmt->bind_param("iss", $user_id, $temaGenerado, $resumenVacio);
            $stmt->execute();
            $id_registro = $conn->insert_id;
            $stmt->close();
        } else {
            $stmt2 = $conn->prepare("INSERT INTO historial (user_id, pregunta, resumen) VALUES (?, ?, ?)");
            $resumenVacio = '';
            $stmt2->bind_param("iss", $user_id, $temaGenerado, $resumenVacio);
            $stmt2->execute();
            $id_registro = $conn->insert_id;
            $stmt2->close();
        }
    } else {
        $jsonFinal = '[]';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Interactivo - EduIA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #F4F4F4;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            border-bottom: 1px solid #E0E0E0;
            padding: 0 32px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #888;
        }

        .topbar-logo { font-size: 18px; font-weight: 700; color: #333; text-decoration: none; }
        .topbar-sep { color: #ccc; }
        .topbar-link { color: #333; text-decoration: none; }
        .topbar-link:hover { text-decoration: underline; }
        .topbar-current { color: #555; font-weight: 500; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-user { font-size: 14px; color: #666; }

        .btn-outline {
            font-size: 13px;
            color: #333;
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid #333;
            border-radius: 6px;
            transition: 0.2s;
        }

        .btn-outline:hover { background: #333; color: white; }

        .page-wrapper { max-width: 820px; margin: 0 auto; padding: 48px 24px; }

        .page-header { margin-bottom: 32px; }
        .page-header h1 { font-size: 26px; font-weight: 700; color: #111; margin-bottom: 6px; }
        .page-header p { color: #555; font-size: 14px; }

        .form-card {
            background: white;
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            border: 1.5px solid #F0F0F0;
        }

        .form-label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 8px; }

        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            color: #333;
            transition: border-color 0.2s;
            background: #fafafa;
            resize: vertical;
        }

        .form-input:focus { outline: none; border-color: #333; background: white; }

        .file-zone {
            margin-top: 16px;
            padding: 16px;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            text-align: center;
            color: #888;
            font-size: 13px;
        }

        .file-zone input { display: block; margin: 8px auto 0; }

        .btn-primary {
            margin-top: 20px;
            width: 100%;
            padding: 13px;
            background: #111;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .quiz-header {
            background: white;
            border-radius: 14px;
            padding: 24px 32px;
            margin-bottom: 20px;
            border: 1.5px solid #F0F0F0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .quiz-header-left h2 { font-size: 20px; font-weight: 700; color: #111; }
        .quiz-header-left p { font-size: 13px; color: #888; margin-top: 4px; }

        .score-badge {
            background: #F5F5F5;
            color: #333;
            font-size: 15px;
            font-weight: 700;
            padding: 8px 18px;
            border-radius: 8px;
            display: none;
        }

        .question-card {
            background: white;
            border-radius: 12px;
            padding: 24px 28px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1.5px solid #EAEAEA;
        }

        .question-text {
            font-size: 15px;
            font-weight: 600;
            color: #222;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        @media (max-width: 600px) {
            .options-grid { grid-template-columns: 1fr; }
        }

        .opt-btn {
            padding: 11px 16px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            background: #fafafa;
            color: #333;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
            text-align: left;
            font-family: inherit;
        }

        .opt-btn:hover:not(:disabled) { border-color: #333; background: #f4f4f4; }
        .opt-btn:disabled { cursor: not-allowed; }
        .opt-btn.correct { background: #f5f5f5; border-color: #333; color: #333; font-weight: 600; }
        .opt-btn.wrong { background: #F5F5F5; border-color: #999; color: #999; }

        .final-result {
            background: white;
            border-radius: 14px;
            padding: 32px;
            text-align: center;
            margin-top: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            border: 1.5px solid #F0F0F0;
            display: none;
        }

        .final-result h3 { font-size: 22px; font-weight: 700; color: #111; margin-bottom: 8px; }
        .final-result .score-big { font-size: 40px; font-weight: 800; color: #333; margin: 12px 0; }
        .final-result p { color: #888; font-size: 14px; }

        .final-actions { margin-top: 20px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }

        .btn-action {
            padding: 9px 20px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            border: none;
        }

        .btn-new { background: #F5F5F5; color: #333; }
        .btn-new:hover { background: #f0f0f0; }

        .loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(244,246,251,0.85);
            z-index: 200;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .loading-overlay.active { display: flex; }

        .spinner {
            width: 44px;
            height: 44px;
            border: 4px solid #F0F0F0;
            border-top-color: #333;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { font-size: 15px; color: #333; font-weight: 600; }
    </style>
</head>
<body>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">Generando test...</div>
</div>

<div class="topbar">
    <div class="topbar-left">
        <a href="index.php" class="topbar-logo">EduIA</a>
        <span class="topbar-sep">/</span>
        <a href="estudio.php" class="topbar-link">Herramientas</a>
        <span class="topbar-sep">/</span>
        <span class="topbar-current">Test interactivo</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-user"><?php echo htmlspecialchars($usuario); ?></span>
        <a href="estudio.php" class="btn-outline">Volver</a>
    </div>
</div>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Test Interactivo</h1>
        <p>Genera preguntas de opción múltiple sobre cualquier tema y pon a prueba tu conocimiento.</p>
    </div>

    <?php if (!$jsonFinal): ?>
    <div class="form-card">
        <form method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('loadingOverlay').classList.add('active'); document.getElementById('btnSubmit').disabled=true;">
            <label class="form-label">Tema del test</label>
            <textarea class="form-input" name="tema" rows="3" placeholder="Ej: La Revolución Francesa, Trigonometría, Programación en Python..." required></textarea>

            <div class="file-zone">
                <span>Adjunta una imagen o documento para basar el test en su contenido (opcional)</span>
                <input type="file" name="archivo" accept="image/*,.pdf,.txt">
            </div>

            <button type="submit" class="btn-primary" id="btnSubmit">Generar test</button>
        </form>
    </div>

    <?php else: ?>
    <div class="quiz-header">
        <div class="quiz-header-left">
            <h2>Test: <?php echo htmlspecialchars($temaGenerado); ?></h2>
            <p>Responde todas las preguntas para ver tu resultado final.</p>
        </div>
        <div class="score-badge" id="liveBadge">0 / 0</div>
    </div>

    <div id="quizContainer"></div>

    <div class="final-result" id="finalResult">
        <h3>Test completado</h3>
        <div class="score-big" id="scoreBig"></div>
        <p id="scoreMsg"></p>
        <div class="final-actions">
            <a href="test.php" class="btn-action btn-new">Nuevo test</a>
            <a href="historial.php" class="btn-action" style="background:#F5F5F5;color:#444;">Ver historial</a>
        </div>
    </div>

    <script>
        const data = <?php echo $jsonFinal; ?>;
        const idRegistro = <?php echo $id_registro ?? 'null'; ?>;
        let aciertos = 0;
        let respondidas = 0;

        function render() {
            const container = document.getElementById('quizContainer');
            let html = '';
            data.forEach((q, i) => {
                html += `<div class="question-card" id="qcard-${i}">
                    <div class="question-text">${i+1}. ${q.p}</div>
                    <div class="options-grid" id="opts-${i}">
                        ${q.o.map((opt, idx) => `<button class="opt-btn" onclick="check(${i}, ${idx}, this)">${opt}</button>`).join('')}
                    </div>
                </div>`;
            });
            container.innerHTML = html;
            document.getElementById('liveBadge').style.display = 'block';
            updateBadge();
        }

        function updateBadge() {
            document.getElementById('liveBadge').textContent = aciertos + ' / ' + respondidas + ' respondidas';
        }

        function check(qIdx, oIdx, btn) {
            const correct = data[qIdx].correcta;
            const buttons = document.getElementById('opts-' + qIdx).querySelectorAll('.opt-btn');
            buttons.forEach(b => b.disabled = true);
            respondidas++;

            if (oIdx === correct) {
                btn.classList.add('correct');
                aciertos++;
            } else {
                btn.classList.add('wrong');
                buttons[correct].classList.add('correct');
            }

            updateBadge();

            if (respondidas === data.length) {
                finish();
            }
        }

        function finish() {
            const pct = Math.round((aciertos / data.length) * 100);
            let msg = pct >= 80 ? 'Excelente trabajo.' : pct >= 50 ? 'Buen intento, sigue practicando.' : 'Repasa el tema e inténtalo de nuevo.';

            document.getElementById('scoreBig').textContent = aciertos + ' / ' + data.length;
            document.getElementById('scoreMsg').textContent = pct + '% de aciertos. ' + msg;
            document.getElementById('finalResult').style.display = 'block';
            document.getElementById('finalResult').scrollIntoView({ behavior: 'smooth' });

            if (idRegistro) {
                fetch('guardar_puntos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + idRegistro + '&aciertos=' + aciertos + '&total=' + data.length
                });
            }
        }

        render();
    </script>
    <?php endif; ?>
</div>

</body>
</html>




