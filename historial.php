<?php
/**
 * ============================================================================
 * ARCHIVO: historial.php
 * ============================================================================
 * PROPÓSITO:
 *   Muestra el historial completo de trabajos generados por el usuario
 *   (resúmenes, tests, flashcards, mapas conceptuales).
 *   Permite visualizar, reutilizar y publicar contenido previo.
 *
 * FUNCIONALIDAD CLAVE:
 *   - Valida autenticación del usuario
 *   - Recupera todos los registros de la tabla 'historial' del usuario
 *   - Organiza elementos por tipo: resúmenes, tests, flashcards, mapas
 *   - Permite publicar resúmenes en la librería compartida
 *   - Interfaz con filtros y búsqueda
 *   - Muestra fecha, tema y resultados (si aplica)
 *   - Acceso directo al contenido guardado
 *
 * FLUJO DE DATOS:
 *   1. Usuario accede a historial.php
 *   2. Sistema recupera todos los registros del usuario de tabla 'historial'
 *   3. Se organizan por tipo en arrays separados
 *   4. Si el usuario elige publicar, se copia a tabla 'resumenes'
 *   5. Se muestra interfaz con todos los elementos ordenados
 *
 * CAMPOS MOSTRADOS:
 *   - pregunta (tema/consulta original)
 *   - resumen (contenido generado)
 *   - tipo (resumen/test/flashcards/mapa)
 *   - aciertos/total_preguntas (si es test)
 *   - fecha (cuándo se creó)
 *
 * DEPENDENCIAS:
 *   - conn.php (conexión a BD)
 *   - Session activa (usuario autenticado)
 *
 * ============================================================================
 */

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
$stmt->close();

// Procesar publicación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publicar_id'])) {
    $publicar_id = intval($_POST['publicar_id']);
    
    // Obtener datos del historial
    $stmt_hist = $conn->prepare("SELECT pregunta, resumen FROM historial WHERE id = ? AND user_id = ? AND tipo = 'resumen'");
    if ($stmt_hist) {
        $stmt_hist->bind_param("ii", $publicar_id, $user_id);
        $stmt_hist->execute();
        $result_hist = $stmt_hist->get_result();
        
        if ($result_hist->num_rows > 0) {
            $hist = $result_hist->fetch_assoc();
            $titulo = $hist['pregunta'];
            $descripcion = substr($hist['resumen'], 0, 200) . (strlen($hist['resumen']) > 200 ? '...' : '');
            $documento_contenido = $hist['pregunta'] . "\n\n" . $hist['resumen'];
            
            // Guardar como archivo de texto
            $nombre_archivo = time() . '_' . bin2hex(random_bytes(8)) . '.txt';
            $upload_dir = __DIR__ . '/_livechat/uploads/';
            $ruta_destino = $upload_dir . $nombre_archivo;
            
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    // Error
                }
            }
            
            if (file_put_contents($ruta_destino, $documento_contenido)) {
                // Insertar en base de datos
                $stmt = $conn->prepare("INSERT INTO resumenes (titulo, descripcion, user_id, documento, fecha_creacion) VALUES (?, ?, ?, ?, NOW())");
                if ($stmt) {
                    $stmt->bind_param("ssis", $titulo, $descripcion, $user_id, $nombre_archivo);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
        $stmt_hist->close();
    }
    
    // Recargar página
    header("Location: historial.php");
    exit;
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
            color: #333;
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid #333;
            border-radius: 6px;
            transition: 0.2s;
        }

        .btn-outline:hover { background: #333; color: white; }

        .page-wrapper { max-width: 1000px; margin: 0 auto; padding: 48px 24px; }
        .page-header { margin-bottom: 32px; }
        .page-header h1 { font-size: 26px; font-weight: 700; color: #111; margin-bottom: 6px; }
        .page-header p { font-size: 14px; color: #555; }

        .tabs-bar {
            display: flex;
            gap: 4px;
            background: white;
            border-radius: 10px;
            padding: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1.5px solid #EAEAEA;
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

        .tab-btn.active { background: #333; color: white; }
        .tab-btn:not(.active):hover { background: #F4F4F4; color: #555; }

        .tab-panel { display: none; }
        .tab-panel.active { display: block; }

        .section-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .section-title { font-size: 15px; font-weight: 700; color: #333; }
        .section-count { font-size: 13px; color: #888; }

        .empty-state {
            background: white;
            border-radius: 14px;
            padding: 48px;
            text-align: center;
            border: 1.5px dashed #e0e0e0;
        }

        .empty-state p { color: #bbb; font-size: 14px; margin-bottom: 16px; }

        .btn-go {
            padding: 9px 20px;
            background: #F4F4F4;
            color: #333;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
        }

        .btn-go:hover { background: #F4F4F4; }

        .list-items { display: flex; flex-direction: column; gap: 12px; }

        .history-item {
            background: white;
            border-radius: 12px;
            padding: 18px 22px;
            border: 1.5px solid #EAEAEA;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .history-item:hover { border-color: #d1d1d1; transform: translateX(2px); }

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
            color: #111;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 3px;
        }

        .item-meta { font-size: 12px; color: #888; }
        .item-right { flex-shrink: 0; }

        .score-badge {
            background: #F5F5F5;
            color: #333;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .score-badge.good { background: #F5F5F5; color: #333; }
        .score-badge.bad  { background: #F5F5F5; color: #999; }

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

        .modal-title { font-size: 18px; font-weight: 700; color: #111; line-height: 1.4; }
        .modal-date  { font-size: 12px; color: #bbb; margin-top: 4px; }

        .modal-close {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: #F4F4F4;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            flex-shrink: 0;
            transition: 0.2s;
        }

        .modal-close:hover { background: #E0E0E0; }

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
            background: #F4F4F4;
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

        .modal-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-publish {
            padding: 8px 16px;
            background: #333;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-publish:hover {
            background: #222;
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
                <div class="item-icon" style="background:#F5F5F5;">📄</div>
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
                <div class="item-icon" style="background:#F5F5F5;">🧪</div>
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
                <div class="item-icon" style="background:#F5F5F5;">🧠</div>
                <div class="item-body">
                    <div class="item-title"><?php echo htmlspecialchars($fila['pregunta']); ?></div>
                    <div class="item-meta"><?php echo date("d/m/Y H:i", strtotime($fila['fecha'])); ?></div>
                </div>
                <div class="item-right">
                    <span class="score-badge" style="background:#F5F5F5;color:#333;"><?php echo count($cards); ?> tarjetas</span>
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
                <div class="item-icon" style="background:#F5F5F5;">🗺️</div>
                <div class="item-body">
                    <div class="item-title"><?php echo htmlspecialchars($fila['pregunta']); ?></div>
                    <div class="item-meta"><?php echo date("d/m/Y H:i", strtotime($fila['fecha'])); ?></div>
                </div>
                <div class="item-right">
                    <span class="score-badge" style="background:#F5F5F5;color:#888;"><?php echo count($mapa['ramas'] ?? []); ?> ramas</span>
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
        <div class="modal-actions" id="modal-actions"></div>
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
        let actions = '';
        if (tipo === 'resumen' && data.resumen) {
            html += '<div class="modal-section-label">Resumen</div>';
            html += '<div class="modal-text">' + parseBold(data.resumen) + '</div>';
            actions = '<form method="POST" style="display:inline;"><input type="hidden" name="publicar_id" value="' + data.id + '"><button type="submit" class="btn-publish">📚 Publicar en biblioteca</button></form>';
        }
        if (tipo === 'test') {
            const ac   = parseInt(data.aciertos) || 0;
            const tot  = parseInt(data.total_preguntas) || 0;
            const pct  = tot > 0 ? Math.round((ac / tot) * 100) : 0;
            const col  = pct >= 80 ? '#333' : pct >= 50 ? '#333' : '#999';
            const bg   = pct >= 80 ? '#F5F5F5' : pct >= 50 ? '#F5F5F5' : '#F5F5F5';
            html += '<div class="modal-section-label">Resultado</div>';
            html += `<div class="modal-score" style="background:${bg};color:${col};">${ac} de ${tot} aciertos (${pct}%)</div>`;
        }
        document.getElementById('modal-body').innerHTML = html;
        document.getElementById('modal-actions').innerHTML = actions;
        document.getElementById('detailModal').classList.add('active');
    }

    function openModalFlash(data) {
        const cards = JSON.parse(data.contenido_json || '[]');
        document.getElementById('modal-title').textContent = data.pregunta;
        document.getElementById('modal-date').textContent  = 'Generado el: ' + data.fecha;
        let html = '<div class="modal-section-label">Flashcards (' + cards.length + ' tarjetas)</div>';
        cards.forEach((c, i) => {
            html += `<div style="background:#F4F4F4;border-radius:8px;padding:12px 14px;margin-bottom:10px;border:1px solid #E0E0E0;">
                <div style="font-size:13px;font-weight:700;color:#111;margin-bottom:6px;">${i+1}. ${escHtml(c.frente)}</div>
                <div style="font-size:13px;color:#555;border-left:3px solid #333;padding-left:10px;">${escHtml(c.reverso)}</div>
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
                    <div style="background:#F5F5F5;color:#888;font-size:13px;font-weight:700;padding:7px 12px;border-radius:7px;display:inline-block;margin-bottom:8px;">${escHtml(rama.titulo)}</div>
                    <div style="display:flex;flex-wrap:wrap;gap:7px;padding-left:8px;">
                        ${(rama.subnodos||[]).map(s=>`<span style="background:#F4F4F4;border:1px solid #e0e0e0;border-radius:20px;padding:4px 12px;font-size:12px;color:#555;">${escHtml(s)}</span>`).join('')}
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



