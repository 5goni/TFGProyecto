<?php
/**
 * ============================================================================
 * ARCHIVO: mapa_conceptual.php
 * ============================================================================
 * PROPÓSITO:
 *   Genera mapas conceptuales visuales basados en un tema usando la IA Gemini.
 *   Permite a usuarios crear representaciones gráficas jerárquicas de conceptos.
 *
 * FUNCIONALIDAD CLAVE:
 *   - Valida autenticación del usuario
 *   - Genera mapas conceptuales con nodo central + 4-8 ramas principales
 *   - Cada rama contiene 2-4 subnodos (conceptos relacionados)
 *   - Respuesta en formato JSON estructurado
 *   - Almacena mapas en tabla 'historial' con tipo 'mapa'
 *   - Interfaz de visualización interactiva del mapa
 *   - Manejo de errores y validaciones API
 *
 * ESTRUCTURA DEL MAPA:
 *   {
 *     "centro": "Tema Principal",
 *     "ramas": [
 *       {"titulo": "Rama 1", "subnodos": [...]},
 *       {"titulo": "Rama 2", "subnodos": [...]}
 *     ]
 *   }
 *
 * FLUJO DE DATOS:
 *   1. Usuario envía tema
 *   2. Sistema solicita a Gemini generar mapa
 *   3. Gemini responde con JSON entre MAPA_START y MAPA_END
 *   4. Se valida estructura JSON
 *   5. Se almacena en tabla historial
 *   6. Se muestra mapa visualizado en interfaz
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
$mapaData = null;
$temaGenerado = null;
$errorMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tema'])) {
    $apiKey = API_KEY;
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

    $systemPrompt = "Eres un profesor experto en crear mapas conceptuales. No saludes. Genera un mapa conceptual estructurado sobre el tema solicitado. El mapa debe tener un nodo central y entre 4-8 ramas principales, cada una con 2-4 subnodos. Responde ÃšNICAMENTE con este formato JSON y nada más:
MAPA_START
{\"centro\":\"Tema Central\",\"ramas\":[{\"titulo\":\"Rama 1\",\"subnodos\":[\"Concepto A\",\"Concepto B\",\"Concepto C\"]},{\"titulo\":\"Rama 2\",\"subnodos\":[\"Concepto D\",\"Concepto E\"]}]}
MAPA_END";

    $temaGenerado = trim($_POST['tema']);
    $promptFinal = "Genera un mapa conceptual sobre: " . $temaGenerado;

    $payload = [
        "system_instruction" => ["parts" => [["text" => $systemPrompt]]],
        "contents" => [["parts" => [["text" => $promptFinal]]]]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if ($response === false) {
        $errorMsg = 'Error de conexión: ' . curl_error($ch);
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);
    if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
        $errorMsg = 'Respuesta inválida de la API: ' . json_last_error_msg();
    }

    if (!$errorMsg && isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
        $mapaData = null;

        if (preg_match('/MAPA_START\s*(\{.*?\})\s*MAPA_END/s', $rawText, $match)) {
            $mapaData = json_decode(trim($match[1]), true);
        } else {
            $mapaData = json_decode(trim($rawText), true);
        }

        if ($mapaData && isset($mapaData['centro']) && isset($mapaData['ramas']) && is_array($mapaData['ramas'])) {
            $jsonGuardar = json_encode($mapaData, JSON_UNESCAPED_UNICODE);
            $stmt = $conn->prepare("INSERT INTO historial (user_id, pregunta, resumen, tipo, contenido_json) VALUES (?, ?, '', 'mapa', ?)");
            if ($stmt) {
                $stmt->bind_param("iss", $user_id, $temaGenerado, $jsonGuardar);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $errorMsg = 'JSON de mapa inválido o incompleto. Respuesta: ' . htmlspecialchars($rawText);
        }
    } elseif (!$errorMsg && isset($result['error']['message'])) {
        $errorMsg = 'Error de la API: ' . $result['error']['message'];
    } elseif (!$errorMsg) {
        $errorMsg = 'No se recibió contenido válido de la API.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa Conceptual - EduIA</title>
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

        .topbar-left { display: flex; align-items: center; gap: 12px; font-size: 14px; color: #888; }
        .topbar-logo { font-size: 18px; font-weight: 700; color: #333; text-decoration: none; }
        .topbar-sep { color: #ccc; }
        .topbar-link { color: #333; text-decoration: none; }
        .topbar-link:hover { text-decoration: underline; }
        .topbar-current { color: #555; font-weight: 500; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-user { font-size: 14px; color: #666; }

        .btn-outline {
            font-size: 13px;
            color: #888;
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid #888;
            border-radius: 6px;
            transition: 0.2s;
        }

        .btn-outline:hover { background: #888; color: white; }

        .page-wrapper { max-width: 1000px; margin: 0 auto; padding: 48px 24px; }
        .page-header { margin-bottom: 32px; }
        .page-header h1 { font-size: 26px; font-weight: 700; color: #111; margin-bottom: 6px; }
        .page-header p { color: #555; font-size: 14px; }

        .form-card {
            background: white;
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            border: 1.5px solid #F5F5F5;
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

        .form-input:focus { outline: none; border-color: #999; background: white; }

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

        .map-container {
            background: white;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            border: 1.5px solid #F5F5F5;
            overflow: hidden;
        }

        .map-toolbar {
            padding: 16px 24px;
            border-bottom: 1px solid #F5F5F5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .map-toolbar h2 { font-size: 16px; font-weight: 700; color: #111; }
        .map-toolbar p { font-size: 13px; color: #888; margin-top: 2px; }

        .toolbar-actions { display: flex; gap: 8px; }

        .tb-btn {
            padding: 7px 14px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .tb-btn-orange { background: #F5F5F5; color: #888; }
        .tb-btn-orange:hover { background: #f5f5f5; }

        #mapCanvas {
            display: block;
            width: 100%;
        }

        .list-view {
            padding: 28px;
            display: none;
        }

        .branch-item {
            margin-bottom: 20px;
        }

        .branch-title {
            font-size: 15px;
            font-weight: 700;
            color: #888;
            margin-bottom: 8px;
            padding: 8px 14px;
            background: #F5F5F5;
            border-radius: 8px;
            display: inline-block;
        }

        .subnodes-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding-left: 16px;
            margin-top: 8px;
        }

        .subnode-chip {
            background: #F4F4F4;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 13px;
            color: #555;
        }

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
            border: 4px solid #F5F5F5;
            border-top-color: #999;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { font-size: 15px; color: #888; font-weight: 600; }
    </style>
</head>
<body>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">Generando mapa conceptual...</div>
</div>

<div class="topbar">
    <div class="topbar-left">
        <a href="index.php" class="topbar-logo">EduIA</a>
        <span class="topbar-sep">/</span>
        <a href="estudio.php" class="topbar-link">Herramientas</a>
        <span class="topbar-sep">/</span>
        <span class="topbar-current">Mapa Conceptual</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-user"><?php echo htmlspecialchars($usuario); ?></span>
        <a href="estudio.php" class="btn-outline">Volver</a>
    </div>
</div>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Mapa Conceptual</h1>
        <p>Genera un mapa visual de los conceptos y relaciones clave sobre cualquier tema.</p>
    </div>

    <?php if (!$mapaData): ?>
    <div class="form-card">
        <form method="POST" onsubmit="document.getElementById('loadingOverlay').classList.add('active'); document.getElementById('btnSubmit').disabled=true;">
            <label class="form-label">Tema del mapa conceptual</label>
            <textarea class="form-input" name="tema" rows="3" placeholder="Ej: El sistema solar, La fotosíntesis, La Revolución Industrial, Redes de computadores..." required></textarea>
            <button type="submit" class="btn-primary" id="btnSubmit">Generar mapa conceptual</button>
        </form>
    </div>
    <?php if ($errorMsg): ?>
    <div style="margin-top:20px;padding:18px;background:#f5f5f5;border:1px solid #ccc;color:#111;border-radius:12px;">
        <strong>Error:</strong> <?php echo htmlspecialchars($errorMsg); ?>
    </div>
    <?php endif; ?>

    <?php elseif ($mapaData): ?>
    <div class="map-container">
        <div class="map-toolbar">
            <div>
                <h2><?php echo htmlspecialchars($mapaData['centro']); ?></h2>
                <p><?php echo count($mapaData['ramas']); ?> ramas principales</p>
            </div>
            <div class="toolbar-actions">
                <button class="tb-btn tb-btn-orange" onclick="toggleView()" id="viewToggle">Ver lista</button>
                <a href="mapa_conceptual.php" class="tb-btn tb-btn-orange">Nuevo mapa</a>
            </div>
        </div>

        <canvas id="mapCanvas"></canvas>

        <div class="list-view" id="listView">
            <?php foreach ($mapaData['ramas'] as $rama): ?>
            <div class="branch-item">
                <div class="branch-title"><?php echo htmlspecialchars($rama['titulo']); ?></div>
                <div class="subnodes-list">
                    <?php foreach ($rama['subnodos'] as $sub): ?>
                    <span class="subnode-chip"><?php echo htmlspecialchars($sub); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        const mapaData = <?php echo json_encode($mapaData); ?>;
        let showingList = false;

        const branchColors = [
            '#999', '#333', '#333', '#999',
            '#333', '#444', '#888', '#333'
        ];

        function toggleView() {
            showingList = !showingList;
            document.getElementById('mapCanvas').style.display = showingList ? 'none' : 'block';
            document.getElementById('listView').style.display = showingList ? 'block' : 'none';
            document.getElementById('viewToggle').textContent = showingList ? 'Ver mapa' : 'Ver lista';
        }

        function drawMap() {
            const canvas = document.getElementById('mapCanvas');
            const container = canvas.parentElement;
            const W = container.clientWidth;

            const ramas = mapaData.ramas;
            if (!Array.isArray(ramas) || ramas.length === 0) {
                return;
            }
            const maxSubnodes = Math.max(...ramas.map(r => r.subnodos.length));
            const rowsNeeded = Math.ceil(ramas.length / 2);
            const H = Math.max(520, rowsNeeded * 220 + 120);

            canvas.width = W;
            canvas.height = H;

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, W, H);

            const cx = W / 2;
            const cy = H / 2;

            function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
                const words = text.split(' ');
                let line = '';
                const lines = [];
                for (let w of words) {
                    const test = line + w + ' ';
                    if (ctx.measureText(test).width > maxWidth && line !== '') {
                        lines.push(line.trim());
                        line = w + ' ';
                    } else {
                        line = test;
                    }
                }
                lines.push(line.trim());
                const totalH = lines.length * lineHeight;
                lines.forEach((l, i) => ctx.fillText(l, x, y - totalH / 2 + i * lineHeight + lineHeight / 2));
            }

            function drawRoundRect(ctx, x, y, w, h, r) {
                ctx.beginPath();
                ctx.moveTo(x + r, y);
                ctx.lineTo(x + w - r, y);
                ctx.quadraticCurveTo(x + w, y, x + w, y + r);
                ctx.lineTo(x + w, y + h - r);
                ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
                ctx.lineTo(x + r, y + h);
                ctx.quadraticCurveTo(x, y + h, x, y + h - r);
                ctx.lineTo(x, y + r);
                ctx.quadraticCurveTo(x, y, x + r, y);
                ctx.closePath();
            }

            const centerW = 160;
            const centerH = 52;

            ctx.shadowColor = 'rgba(0,0,0,0.12)';
            ctx.shadowBlur = 14;
            drawRoundRect(ctx, cx - centerW/2, cy - centerH/2, centerW, centerH, 12);
            ctx.fillStyle = '#111';
            ctx.fill();
            ctx.shadowBlur = 0;

            ctx.fillStyle = 'white';
            ctx.font = 'bold 14px Segoe UI, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            wrapText(ctx, mapaData.centro, cx, cy, centerW - 20, 17);

            const leftRamas = [];
            const rightRamas = [];
            ramas.forEach((r, i) => {
                if (i % 2 === 0) rightRamas.push(r);
                else leftRamas.push(r);
            });

            function drawSide(ramaList, side) {
                const startX = side === 'right' ? cx + 90 : cx - 90;
                const branchX = side === 'right' ? cx + 210 : cx - 210;
                const subX = side === 'right' ? cx + 360 : cx - 360;
                const totalH = H;
                const spacing = totalH / (ramaList.length + 1);

                ramaList.forEach((rama, idx) => {
                    const color = branchColors[(ramas.indexOf(rama)) % branchColors.length];
                    const by = spacing * (idx + 1);

                    ctx.beginPath();
                    ctx.moveTo(side === 'right' ? cx + centerW/2 : cx - centerW/2, cy);
                    ctx.bezierCurveTo(startX + (side === 'right' ? 30 : -30), cy, branchX - (side === 'right' ? 30 : -30), by, branchX, by);
                    ctx.strokeStyle = color;
                    ctx.lineWidth = 2.5;
                    ctx.setLineDash([]);
                    ctx.stroke();

                    const bw = 140;
                    const bh = 36;
                    ctx.shadowColor = 'rgba(0,0,0,0.10)';
                    ctx.shadowBlur = 8;
                    drawRoundRect(ctx, branchX - (side === 'right' ? 0 : bw), by - bh/2, bw, bh, 8);
                    ctx.fillStyle = color;
                    ctx.fill();
                    ctx.shadowBlur = 0;

                    ctx.fillStyle = 'white';
                    ctx.font = 'bold 12px Segoe UI, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    wrapText(ctx, rama.titulo, branchX + (side === 'right' ? bw/2 : -bw/2), by, bw - 16, 15);

                    const subSpacing = bh * 1.6;
                    const subTotalH = (rama.subnodos.length - 1) * subSpacing;
                    const subStartY = by - subTotalH / 2;

                    rama.subnodos.forEach((sub, si) => {
                        const sy = subStartY + si * subSpacing;
                        const sw = 130;
                        const sh = 28;
                        const sax = side === 'right' ? branchX + bw : branchX - bw;
                        const sbx = side === 'right' ? subX : cx - 360;

                        ctx.beginPath();
                        ctx.moveTo(sax, by);
                        ctx.bezierCurveTo(sax + (side === 'right' ? 20 : -20), by, sbx - (side === 'right' ? 20 : -20), sy, sbx, sy);
                        ctx.strokeStyle = color + '88';
                        ctx.lineWidth = 1.5;
                        ctx.stroke();

                        ctx.shadowColor = 'rgba(0,0,0,0.07)';
                        ctx.shadowBlur = 6;
                        drawRoundRect(ctx, sbx - (side === 'right' ? 0 : sw), sy - sh/2, sw, sh, 7);
                        ctx.fillStyle = 'white';
                        ctx.fill();
                        ctx.strokeStyle = color + 'AA';
                        ctx.lineWidth = 1.5;
                        ctx.stroke();
                        ctx.shadowBlur = 0;

                        ctx.fillStyle = '#333';
                        ctx.font = '11px Segoe UI, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        wrapText(ctx, sub, sbx + (side === 'right' ? sw/2 : -sw/2), sy, sw - 14, 14);
                    });
                });
            }

            drawSide(rightRamas, 'right');
            drawSide(leftRamas, 'left');
        }

        drawMap();
        window.addEventListener('resize', drawMap);
    </script>
    <?php else: ?>
    <div style="background:white;border-radius:14px;padding:32px;text-align:center;color:#888;">
        No se pudo generar el mapa. Inténtalo de nuevo.
        <br><br>
        <a href="mapa_conceptual.php" style="color:#888;font-weight:600;">Volver a intentarlo</a>
    </div>
    <?php endif; ?>
</div>

</body>
</html>


