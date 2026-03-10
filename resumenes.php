<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['username'] ?? 'Usuario';
$user_id = $_SESSION['user_id'];
$resumen = null;
$temaGenerado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tema'])) {
    $apiKey = 'AIzaSyBuCCzzEbuf5kFdaH5q8LR9qW69G_plzEs';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

    $systemPrompt = "Eres un profesor experto. No saludes, no des introducciones, no escribas frases como 'Aquí tienes un resumen' ni 'A continuación' ni nada similar. No uses separadores como --- ni líneas horizontales. Empieza directamente con el contenido del resumen. El resumen debe ser claro, estructurado y completo, tan largo como sea necesario pero sin información irrelevante. Usa párrafos bien organizados. Usa **negrita** alrededor de los conceptos clave, términos importantes y datos relevantes. Tambien evita usar saltos de lineas que sean excesivos";

    $temaGenerado = trim($_POST['tema']);
    $promptFinal = "Genera un resumen completo sobre: " . $temaGenerado;

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
        $resumen = trim($result['candidates'][0]['content']['parts'][0]['text']);
        $resumen = preg_replace('/^[\s\-\_\*]+\n+/u', '', $resumen);

        $stmt = $conn->prepare("INSERT INTO historial (user_id, pregunta, resumen, tipo) VALUES (?, ?, ?, 'resumen')");
        if ($stmt) {
            $stmt->bind_param("iss", $user_id, $temaGenerado, $resumen);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt2 = $conn->prepare("INSERT INTO historial (user_id, pregunta, resumen) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $user_id, $temaGenerado, $resumen);
            $stmt2->execute();
            $stmt2->close();
        }
    } else {
        $resumen = "Error al generar el resumen. Revisa tu conexión o API Key.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen - EduIA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6fb;
            min-height: 100vh;
        }

        .topbar {
            background: white;
            border-bottom: 1px solid #e8eaf0;
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

        .topbar-logo { font-size: 18px; font-weight: 700; color: #667eea; text-decoration: none; }
        .topbar-sep { color: #ccc; }
        .topbar-link { color: #667eea; text-decoration: none; }
        .topbar-link:hover { text-decoration: underline; }
        .topbar-current { color: #555; font-weight: 500; }

        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-user { font-size: 14px; color: #666; }

        .btn-outline {
            font-size: 13px;
            color: #667eea;
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid #667eea;
            border-radius: 6px;
            transition: 0.2s;
        }

        .btn-outline:hover { background: #667eea; color: white; }

        .page-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 48px 24px;
        }

        .page-header { margin-bottom: 32px; }
        .page-header h1 { font-size: 26px; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; }
        .page-header p { color: #7a7a9a; font-size: 14px; }

        .form-card {
            background: white;
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            margin-bottom: 28px;
            border: 1.5px solid #e8e4ff;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e0e0f0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            color: #333;
            transition: border-color 0.2s;
            background: #fafafa;
            resize: vertical;
        }

        .form-input:focus {
            outline: none;
            border-color: #9c82f5;
            background: white;
        }

        .file-zone {
            margin-top: 16px;
            padding: 16px;
            border: 2px dashed #e0e0f0;
            border-radius: 8px;
            text-align: center;
            color: #aaa;
            font-size: 13px;
            transition: border-color 0.2s;
        }

        .file-zone:hover { border-color: #9c82f5; }
        .file-zone input { display: block; margin: 8px auto 0; }

        .btn-primary {
            margin-top: 20px;
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #9c82f5, #667eea);
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

        .result-card {
            background: white;
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            border: 1.5px solid #e8e4ff;
        }

        .result-card h2 {
            font-size: 18px;
            font-weight: 700;
            color: #7c5cbf;
            margin-bottom: 6px;
        }

        .result-tema {
            font-size: 13px;
            color: #aaa;
            margin-bottom: 20px;
        }

        .result-body {
            font-size: 15px;
            line-height: 1.7;
            color: #333;
        }

        .result-body p {
            margin-bottom: 10px;
        }

        .result-actions {
            margin-top: 24px;
            display: flex;
            gap: 10px;
        }

        .btn-action {
            padding: 9px 18px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            border: none;
        }

        .btn-again { background: #f0ecff; color: #7c5cbf; }
        .btn-again:hover { background: #e0d8ff; }

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
            border: 4px solid #e8e4ff;
            border-top-color: #9c82f5;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .loading-text { font-size: 15px; color: #7c5cbf; font-weight: 600; }
    </style>
</head>
<body>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">Generando resumen...</div>
</div>

<div class="topbar">
    <div class="topbar-left">
        <a href="index.php" class="topbar-logo">EduIA</a>
        <span class="topbar-sep">/</span>
        <a href="estudio.php" class="topbar-link">Herramientas</a>
        <span class="topbar-sep">/</span>
        <span class="topbar-current">Resumen</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-user"><?php echo htmlspecialchars($usuario); ?></span>
        <a href="estudio.php" class="btn-outline">Volver</a>
    </div>
</div>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Generador de Resumen</h1>
        <p>Escribe un tema o sube un archivo y la IA generará un resumen completo.</p>
    </div>

    <?php if (!$resumen): ?>
    <div class="form-card">
        <form method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('loadingOverlay').classList.add('active'); document.getElementById('btnSubmit').disabled=true;">
            <label class="form-label">Tema o pregunta</label>
            <textarea class="form-input" name="tema" rows="4" placeholder="Ej: La Segunda Guerra Mundial, Derivadas e integrales, El ciclo del agua..." required></textarea>

            <div class="file-zone">
                <span>Adjunta una imagen, PDF o documento (opcional)</span>
                <input type="file" name="archivo" accept="image/*,.pdf,.txt,.doc,.docx">
            </div>

            <button type="submit" class="btn-primary" id="btnSubmit">Generar resumen</button>
        </form>
    </div>
    <?php else: ?>
    <div class="result-card">
        <h2>Resumen</h2>
        <div class="result-tema">Tema: <?php echo htmlspecialchars($temaGenerado); ?></div>
        <div class="result-body"><?php
            $resumenEsc = htmlspecialchars($resumen);
            $resumenHtml = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $resumenEsc);
            $parrafos = preg_split('/\n{2,}/', trim($resumenHtml));
            foreach ($parrafos as $p) {
                echo '<p>' . nl2br(trim($p)) . '</p>';
            }
        ?></div>
        <div class="result-actions">
            <a href="resumenes.php" class="btn-action btn-again">Generar otro resumen</a>
            <a href="historial.php" class="btn-action" style="background:#f0ecff;color:#7c5cbf;">Ver historial</a>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
