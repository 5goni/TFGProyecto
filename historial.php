<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$usuario = $_SESSION['username'] ?? 'Usuario';

$sql = "SELECT * FROM historial WHERE user_id = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultado = $stmt->get_result();

$resumenes  = [];
$tests      = [];
$flashcards = [];
$mapas      = [];

while ($fila = $resultado->fetch_assoc()) {
    $tipo = $fila['tipo'] ?? 'resumen';
    if ($tipo === 'test')            $tests[]      = $fila;
    elseif ($tipo === 'flashcards')  $flashcards[] = $fila;
    elseif ($tipo === 'mapa')        $mapas[]      = $fila;
    else                             $resumenes[]  = $fila;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial - EduIA</title>
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
            color: #667eea;
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid #667eea;
            border-radius: 6px;
            transition: 0.2s;
        }

        .btn-outline:hover { background: #667eea; color: white; }

        .page-wrapper { max-width: 1000px; margin: 0 auto; padding: 48px 24px; }
        .page-header { margin-bottom: 32px; }
        .page-header h1 { font-size: 26px; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; }
        .page-header p { font-size: 14px; color: #7a7a9a; }

        .tabs-bar {
            display: flex;
            gap: 4px;
            background: white;
            border-radius: 10px;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1.5px solid #f0f0f8;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 9px 18px;
            border-radius: 8px;
            border: none;
            background: transparent;
            font-size: 14px;
            font-weight: 600;
            color: #888;
            cursor: pointer;
            transition: 0.2s;
        }

        .tab-btn.active { background: #667eea; color: white; }
        .tab-btn:not(.active):hover { background: #f4f6fb; color: #555; }

        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        .section-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .section-title { font-size: 15px; font-weight: 700; color: #333; }
        .section-count { font-size: 13px; color: #aaa; }

        .empty-state {
            background: white;
            border-radius: 14px;
            padding: 48px;
            text-align: center;
            border: 1.5px dashed #e0e0f0;
        }

        .empty-state p { color: #bbb; font-size: 14px; margin-bottom: 16px; }

        .btn-go {
            padding: 9px 20px;
            background: #f4f6fb;
            color: #667eea;
            border: 1.5px solid #e0daf8;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
        }

        .btn-go:hover { background: #ede9ff; }

        .list-items { display: flex; flex-direction: column; gap: 12px; }

        .history-item {
            background: white;
            border-radius: 12px;
            padding: 18px 22px;
            border: 1.5px solid #f0f0f8;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .history-item:hover { border-color: #c8c0f0; transform: translateX(2px); }

        .item-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .item-body { flex: 1; min-width: 0; }

        .item-title {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a2e;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 3px;
        }

        .item-meta { font-size: 12px; color: #aaa; }
        .item-right { flex-shrink: 0; }

        .score-badge {
            background: #e6f4ff;
            color: #2b7fd4;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .score-badge.good { background: #e4faf1; color: #1e8a5a; }
        .score-badge.bad  { background: #fff0f0; color: #b94040; }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(20,20,40,0.45);
            z-index: 200;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }

        .modal.active { display: flex; }

        .modal-card {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 640px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 16px 48px rgba(0,0,0,0.18);
            animation: popIn 0.2s ease;
        }

        @keyframes popIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .modal-header {
            padding: 24px 28px 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .modal-title { font-size: 18px; font-weight: 700; color: #1a1a2e; line-height: 1.4; }
        .modal-date  { font-size: 12px; color: #bbb; margin-top: 4px; }

        .modal-close {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: #f4f6fb;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            flex-shrink: 0;
            transition: 0.2s;
        }

        .modal-close:hover { background: #e8eaf0; }

        .modal-body { padding: 20px 28px 28px; }

        .modal-section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #ccc;
            margin-bottom: 10px;
        }

        .modal-text {
            font-size: 14px;
            line-height: 1.8;
            color: #333;
            background: #f9f9fb;
            padding: 16px;
            border-radius: 10px;
            white-space: pre-wrap;
        }

        .modal-score {
            margin-top: 16px;
            display: inline-block;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-left">
        <a href="index.php" class="topbar-logo">EduIA</a>
        <span class="topbar-sep">/</span>
        <a href="estudio.php" class="topbar-link">Herramientas</a>
        <span class="topbar-sep">/</span>
        <span class="topbar-current">Historial</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-user"><?php echo htmlspecialchars($usuario); ?></span>
        <a href="estudio.php" class="btn-outline">Herramientas</a>
    </div>
</div>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Mi Historial</h1>
        <p>Revisa todos tus resumenes, tests, flashcards y mapas conceptuales generados.</p>
    </div>

    <div class="tabs-bar">
        <button class="tab-btn active" onclick="switchHistTab('resumenes', this)">
            Resumenes <span style="opacity:0.6;font-weight:400;">(<?php echo count($resumenes); ?>)</span>
        </button>
        <button class="tab-btn" onclick="switchHistTab('tests', this)">
            Tests <span style="opacity:0.6;font-weight:400;">(<?php echo count($tests); ?>)</span>
        </button>
        <button class="tab-btn" onclick="switchHistTab('flashcards', this)">
            Flashcards <span style="opacity:0.6;font-weight:400;">(<?php echo count($flashcards); ?>)</span>
        </button>
        <button class="tab-btn" onclick="switchHistTab('mapas', this)">
            Mapas <span style="opacity:0.6;font-weight:400;">(<?php echo count($mapas); ?>)</span>
        </button>
    </div>

    <!-- RESUMENES -->
    <div class="tab-panel active" id="panel-resumenes">
        <?php if (empty($resumenes)): ?>
        <div class="empty-state">
            <p>Aun no has generado ningún resumen.</p>
            <a href="resumenes.php" class="btn-go">Generar resumen</a>
        </div>
        <?php else: ?>
        <div class="section-info">
            <span class="section-title">Resumenes generados</span>
            <span class="section-count"><?php echo count($resumenes); ?> en total</span>
        </div>
        <div class="list-items">
            <?php foreach ($resumenes as $fila): ?>
            <div class="history-item" onclick="openModal(<?php echo htmlspecialchars(json_encode($fila)); ?>, 'resumen')">
                <div class="item-icon" style="background:#f0ecff;">📄</div>
                <div class="item-body">
                    <div class="item-title"><?php echo htmlspecialchars($fila['pregunta']); ?></div>
                    <div class="item-meta"><?php echo date("d/m/Y H:i", strtotime($fila['fecha'])); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- TESTS -->
    <div class="tab-panel" id="panel-tests">
        <?php if (empty($tests)): ?>
        <div class="empty-state">
            <p>Aun no has completado ningún test.</p>
            <a href="test.php" class="btn-go">Hacer un test</a>
        </div>
        <?php else: ?>
        <div class="section-info">
            <span class="section-title">Tests realizados</span>
            <span class="section-count"><?php echo count($tests); ?> en total</span>
        </div>
        <div class="list-items">
            <?php foreach ($tests as $fila):
                $pct = ($fila['total_preguntas'] > 0) ? ($fila['aciertos'] / $fila['total_preguntas']) * 100 : 0;
                $badgeClass = $pct >= 80 ? 'good' : ($pct >= 50 ? '' : 'bad');
            ?>
            <div class="history-item" onclick="openModal(<?php echo htmlspecialchars(json_encode($fila)); ?>, 'test')">
                <div class="item-icon" style="background:#e6f4ff;">🧪</div>
                <div class="item-body">
                    <div class="item-title"><?php echo htmlspecialchars($fila['pregunta']); ?></div>
                    <div class="item-meta"><?php echo date("d/m/Y H:i", strtotime($fila['fecha'])); ?></div>
                </div>
                <div class="item-right">
                    <span class="score-badge <?php echo $badgeClass; ?>"><?php echo $fila['aciertos'] . '/' . $fila['total_preguntas']; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- FLASHCARDS -->
    <div class="tab-panel" id="panel-flashcards">
        <?php if (empty($flashcards)): ?>
        <div class="empty-state">
            <p>Aun no has generado ningún mazo de flashcards.</p>
            <a href="flashcards.php" class="btn-go">Crear flashcards</a>
        </div>
        <?php else: ?>
        <div class="section-info">
            <span class="section-title">Mazos de flashcards</span>
            <span class="section-count"><?php echo count($flashcards); ?> en total</span>
        </div>
        <div class="list-items">
            <?php foreach ($flashcards as $fila):
                $cards = json_decode($fila['contenido_json'] ?? '[]', true) ?: [];
            ?>
            <div class="history-item" onclick="openModalFlash(<?php echo htmlspecialchars(json_encode($fila)); ?>)">
                <div class="item-icon" style="background:#e4faf1;">🃏</div>
                <div class="item-body">
                    <div class="item-title"><?php echo htmlspecialchars($fila['pregunta']); ?></div>
                    <div class="item-meta"><?php echo date("d/m/Y H:i", strtotime($fila['fecha'])); ?></div>
                </div>
                <div class="item-right">
                    <span class="score-badge" style="background:#e4faf1;color:#1e8a5a;"><?php echo count($cards); ?> tarjetas</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- MAPAS -->
    <div class="tab-panel" id="panel-mapas">
        <?php if (empty($mapas)): ?>
        <div class="empty-state">
            <p>Aun no has generado ningún mapa conceptual.</p>
            <a href="mapa_conceptual.php" class="btn-go">Crear mapa</a>
        </div>
        <?php else: ?>
        <div class="section-info">
            <span class="section-title">Mapas conceptuales</span>
            <span class="section-count"><?php echo count($mapas); ?> en total</span>
        </div>
        <div class="list-items">
            <?php foreach ($mapas as $fila):
                $mapa = json_decode($fila['contenido_json'] ?? '{}', true) ?: [];
            ?>
            <div class="history-item" onclick="openModalMapa(<?php echo htmlspecialchars(json_encode($fila)); ?>)">
                <div class="item-icon" style="background:#fff7e6;">🗺️</div>
                <div class="item-body">
                    <div class="item-title"><?php echo htmlspecialchars($fila['pregunta']); ?></div>
                    <div class="item-meta"><?php echo date("d/m/Y H:i", strtotime($fila['fecha'])); ?></div>
                </div>
                <div class="item-right">
                    <span class="score-badge" style="background:#fff7e6;color:#b97a00;"><?php echo count($mapa['ramas'] ?? []); ?> ramas</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal detalle -->
<div class="modal" id="detailModal">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="modal-title"></div>
                <div class="modal-date"  id="modal-date"></div>
            </div>
            <button class="modal-close" onclick="closeModal()">&#x2715;</button>
        </div>
        <div class="modal-body" id="modal-body"></div>
    </div>
</div>

<script>
    function switchHistTab(tab, btn) {
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('panel-' + tab).classList.add('active');
        btn.classList.add('active');
    }

    function parseBold(str) {
        return escHtml(str).replace(/\*\*(.+?)\*\*/gs, '<strong>$1</strong>');
    }

    function openModal(data, tipo) {
        document.getElementById('modal-title').textContent = data.pregunta;
        document.getElementById('modal-date').textContent  = 'Generado el: ' + data.fecha;
        let html = '';
        if (tipo === 'resumen' && data.resumen) {
            html += '<div class="modal-section-label">Resumen</div>';
            html += '<div class="modal-text">' + parseBold(data.resumen) + '</div>';
        }
        if (tipo === 'test') {
            const ac   = parseInt(data.aciertos) || 0;
            const tot  = parseInt(data.total_preguntas) || 0;
            const pct  = tot > 0 ? Math.round((ac / tot) * 100) : 0;
            const col  = pct >= 80 ? '#1e8a5a' : pct >= 50 ? '#2b7fd4' : '#b94040';
            const bg   = pct >= 80 ? '#e4faf1' : pct >= 50 ? '#e6f4ff' : '#fff0f0';
            html += '<div class="modal-section-label">Resultado</div>';
            html += `<div class="modal-score" style="background:${bg};color:${col};">${ac} de ${tot} aciertos (${pct}%)</div>`;
        }
        document.getElementById('modal-body').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');
    }

    function openModalFlash(data) {
        const cards = JSON.parse(data.contenido_json || '[]');
        document.getElementById('modal-title').textContent = data.pregunta;
        document.getElementById('modal-date').textContent  = 'Generado el: ' + data.fecha;
        let html = '<div class="modal-section-label">Flashcards (' + cards.length + ' tarjetas)</div>';
        cards.forEach((c, i) => {
            html += `<div style="background:#f9f9fb;border-radius:8px;padding:12px 14px;margin-bottom:10px;border:1px solid #e8eaf0;">
                <div style="font-size:13px;font-weight:700;color:#1a1a2e;margin-bottom:6px;">${i+1}. ${escHtml(c.frente)}</div>
                <div style="font-size:13px;color:#555;border-left:3px solid #43d98e;padding-left:10px;">${escHtml(c.reverso)}</div>
            </div>`;
        });
        document.getElementById('modal-body').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');
    }

    function openModalMapa(data) {
        const mapa = JSON.parse(data.contenido_json || '{}');
        document.getElementById('modal-title').textContent = data.pregunta;
        document.getElementById('modal-date').textContent  = 'Generado el: ' + data.fecha;
        let html = '<div class="modal-section-label">Mapa conceptual</div>';
        if (mapa.ramas) {
            mapa.ramas.forEach(rama => {
                html += `<div style="margin-bottom:14px;">
                    <div style="background:#fff7e6;color:#b97a00;font-size:13px;font-weight:700;padding:7px 12px;border-radius:7px;display:inline-block;margin-bottom:8px;">${escHtml(rama.titulo)}</div>
                    <div style="display:flex;flex-wrap:wrap;gap:7px;padding-left:8px;">
                        ${(rama.subnodos||[]).map(s=>`<span style="background:#f4f6fb;border:1px solid #e0e0f0;border-radius:20px;padding:4px 12px;font-size:12px;color:#555;">${escHtml(s)}</span>`).join('')}
                    </div>
                </div>`;
            });
        }
        document.getElementById('modal-body').innerHTML = html;
        document.getElementById('detailModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('detailModal').classList.remove('active');
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    document.getElementById('detailModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>

</body>
</html>
