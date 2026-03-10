<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION['username'] ?? 'Usuario';
$user_id = $_SESSION['user_id'];
$cards = null;
$temaGenerado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tema'])) {
    $apiKey = 'AIzaSyBuCCzzEbuf5kFdaH5q8LR9qW69G_plzEs';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

    $systemPrompt = "Eres un profesor experto. No saludes, no des introducciones. Genera exactamente 10 flashcards sobre el tema solicitado. Cada flashcard tiene una pregunta o concepto en el frente y la respuesta o definición en el reverso. Responde estrictamente en este formato JSON y nada más:
CARDS_START
[{\"frente\":\"¿Pregunta o concepto?\",\"reverso\":\"Respuesta o definición clara y concisa.\"}]
CARDS_END";

    $temaGenerado = trim($_POST['tema']);
    $promptFinal = "Genera 10 flashcards sobre: " . $temaGenerado;

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
        preg_match('/CARDS_START(.*?)CARDS_END/s', $rawText, $match);
        $cardsJson = isset($match[1]) ? trim($match[1]) : '[]';
        $cards = json_decode($cardsJson, true);
        if (!$cards) $cards = [];

        if (!empty($cards)) {
            $jsonGuardar = json_encode($cards, JSON_UNESCAPED_UNICODE);
            $stmt = $conn->prepare("INSERT INTO historial (user_id, pregunta, resumen, tipo, contenido_json) VALUES (?, ?, '', 'flashcards', ?)");
            $stmt->bind_param("iss", $user_id, $temaGenerado, $jsonGuardar);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flashcards - EduIA</title>
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

        .topbar-left { display: flex; align-items: center; gap: 12px; font-size: 14px; color: #888; }
        .topbar-logo { font-size: 18px; font-weight: 700; color: #667eea; text-decoration: none; }
        .topbar-sep { color: #ccc; }
        .topbar-link { color: #667eea; text-decoration: none; }
        .topbar-link:hover { text-decoration: underline; }
        .topbar-current { color: #555; font-weight: 500; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-user { font-size: 14px; color: #666; }

        .btn-outline {
            font-size: 13px;
            color: #1e8a5a;
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid #1e8a5a;
            border-radius: 6px;
            transition: 0.2s;
        }

        .btn-outline:hover { background: #1e8a5a; color: white; }

        .page-wrapper { max-width: 900px; margin: 0 auto; padding: 48px 24px; }
        .page-header { margin-bottom: 32px; }
        .page-header h1 { font-size: 26px; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; }
        .page-header p { color: #7a7a9a; font-size: 14px; }

        .form-card {
            background: white;
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            border: 1.5px solid #d8f5e8;
        }

        .form-label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 8px; }

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

        .form-input:focus { outline: none; border-color: #43d98e; background: white; }

        .file-zone {
            margin-top: 16px;
            padding: 16px;
            border: 2px dashed #e0e0f0;
            border-radius: 8px;
            text-align: center;
            color: #aaa;
            font-size: 13px;
        }

        .file-zone input { display: block; margin: 8px auto 0; }

        .btn-primary {
            margin-top: 20px;
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #43d98e, #1e8a5a);
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

        .deck-header {
            background: white;
            border-radius: 14px;
            padding: 20px 28px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1.5px solid #d8f5e8;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .deck-header h2 { font-size: 18px; font-weight: 700; color: #1a1a2e; }
        .deck-counter { font-size: 13px; color: #43d98e; font-weight: 700; }

        .flashcard-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 24px;
        }

        .nav-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid #d8f5e8;
            background: white;
            font-size: 18px;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e8a5a;
        }

        .nav-btn:hover:not(:disabled) { background: #e4faf1; border-color: #43d98e; }
        .nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        .card-indicator { font-size: 14px; color: #888; font-weight: 600; min-width: 80px; text-align: center; }

        .flashcard-wrapper {
            perspective: 1000px;
            height: 260px;
            cursor: pointer;
            user-select: none;
        }

        .flashcard-inner {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.45s cubic-bezier(0.4,0,0.2,1);
        }

        .flashcard-inner.flipped { transform: rotateY(180deg); }

        .flashcard-face {
            position: absolute;
            inset: 0;
            border-radius: 16px;
            backface-visibility: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.10);
        }

        .flashcard-front {
            background: white;
            border: 2px solid #d8f5e8;
        }

        .flashcard-back {
            background: linear-gradient(135deg, #43d98e, #1e8a5a);
            transform: rotateY(180deg);
        }

        .card-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 14px;
        }

        .flashcard-front .card-label { color: #43d98e; }
        .flashcard-back .card-label { color: rgba(255,255,255,0.7); }

        .card-text {
            font-size: 18px;
            font-weight: 600;
            line-height: 1.5;
        }

        .flashcard-front .card-text { color: #1a1a2e; }
        .flashcard-back .card-text { color: white; }

        .flip-hint {
            margin-top: 12px;
            font-size: 12px;
            color: #bbb;
            text-align: center;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
            margin-top: 32px;
        }

        .grid-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1.5px solid #d8f5e8;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .grid-card-q { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 10px; }
        .grid-card-a { font-size: 13px; color: #666; line-height: 1.5; }
        .grid-card-sep { width: 30px; height: 2px; background: #43d98e; margin-bottom: 10px; border-radius: 2px; }

        .deck-actions { margin-top: 28px; display: flex; gap: 10px; flex-wrap: wrap; }

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

        .btn-new { background: #e4faf1; color: #1e8a5a; }
        .btn-new:hover { background: #c8f5e0; }

        .view-toggle {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }

        .toggle-btn {
            padding: 7px 16px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #d8f5e8;
            background: white;
            color: #888;
            transition: 0.2s;
        }

        .toggle-btn.active { background: #e4faf1; border-color: #43d98e; color: #1e8a5a; }

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
            border: 4px solid #d8f5e8;
            border-top-color: #43d98e;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { font-size: 15px; color: #1e8a5a; font-weight: 600; }
    </style>
</head>
<body>

<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">Generando flashcards...</div>
</div>

<div class="topbar">
    <div class="topbar-left">
        <a href="index.php" class="topbar-logo">EduIA</a>
        <span class="topbar-sep">/</span>
        <a href="estudio.php" class="topbar-link">Herramientas</a>
        <span class="topbar-sep">/</span>
        <span class="topbar-current">Flashcards</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-user"><?php echo htmlspecialchars($usuario); ?></span>
        <a href="estudio.php" class="btn-outline">Volver</a>
    </div>
</div>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Flashcards</h1>
        <p>Genera tarjetas de memoria sobre cualquier tema. Haz clic en cada tarjeta para voltearla.</p>
    </div>

    <?php if (!$cards): ?>
    <div class="form-card">
        <form method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('loadingOverlay').classList.add('active'); document.getElementById('btnSubmit').disabled=true;">
            <label class="form-label">Tema de las flashcards</label>
            <textarea class="form-input" name="tema" rows="3" placeholder="Ej: Vocabulario de inglés, Huesos del cuerpo humano, Capitales de Europa..." required></textarea>

            <div class="file-zone">
                <span>Adjunta un documento para basar las tarjetas en su contenido (opcional)</span>
                <input type="file" name="archivo" accept="image/*,.pdf,.txt">
            </div>

            <button type="submit" class="btn-primary" id="btnSubmit">Generar flashcards</button>
        </form>
    </div>

    <?php elseif (count($cards) > 0): ?>
    <div class="deck-header">
        <div>
            <h2><?php echo htmlspecialchars($temaGenerado); ?></h2>
            <div class="deck-counter"><?php echo count($cards); ?> tarjetas generadas</div>
        </div>
    </div>

    <div class="view-toggle">
        <button class="toggle-btn active" onclick="setView('deck', this)">Modo estudio</button>
        <button class="toggle-btn" onclick="setView('grid', this)">Ver todas</button>
    </div>

    <div id="deckView">
        <div class="flashcard-nav">
            <button class="nav-btn" id="prevBtn" onclick="navigate(-1)" disabled>&#8592;</button>
            <span class="card-indicator" id="cardIndicator">1 / <?php echo count($cards); ?></span>
            <button class="nav-btn" id="nextBtn" onclick="navigate(1)">&#8594;</button>
        </div>

        <div class="flashcard-wrapper" onclick="flipCard()">
            <div class="flashcard-inner" id="flashcard">
                <div class="flashcard-face flashcard-front">
                    <div class="card-label">Pregunta</div>
                    <div class="card-text" id="frontText"></div>
                </div>
                <div class="flashcard-face flashcard-back">
                    <div class="card-label">Respuesta</div>
                    <div class="card-text" id="backText"></div>
                </div>
            </div>
        </div>
        <div class="flip-hint">Haz clic en la tarjeta para voltearla</div>
    </div>

    <div id="gridView" style="display:none;">
        <div class="cards-grid">
            <?php foreach ($cards as $card): ?>
            <div class="grid-card">
                <div class="grid-card-q"><?php echo htmlspecialchars($card['frente']); ?></div>
                <div class="grid-card-sep"></div>
                <div class="grid-card-a"><?php echo htmlspecialchars($card['reverso']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="deck-actions">
        <a href="flashcards.php" class="btn-action btn-new">Nuevas flashcards</a>
    </div>

    <script>
        const cards = <?php echo json_encode($cards); ?>;
        let current = 0;

        function render() {
            document.getElementById('frontText').textContent = cards[current].frente;
            document.getElementById('backText').textContent = cards[current].reverso;
            document.getElementById('cardIndicator').textContent = (current + 1) + ' / ' + cards.length;
            document.getElementById('prevBtn').disabled = current === 0;
            document.getElementById('nextBtn').disabled = current === cards.length - 1;
            document.getElementById('flashcard').classList.remove('flipped');
        }

        function flipCard() {
            document.getElementById('flashcard').classList.toggle('flipped');
        }

        function navigate(dir) {
            current = Math.max(0, Math.min(cards.length - 1, current + dir));
            render();
        }

        function setView(view, btn) {
            document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('deckView').style.display = view === 'deck' ? 'block' : 'none';
            document.getElementById('gridView').style.display = view === 'grid' ? 'block' : 'none';
        }

        render();
    </script>
    <?php else: ?>
    <div style="background:white;border-radius:14px;padding:32px;text-align:center;color:#888;">
        No se pudieron generar las flashcards. Inténtalo de nuevo.
        <br><br>
        <a href="flashcards.php" style="color:#1e8a5a;font-weight:600;">Volver a intentarlo</a>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
