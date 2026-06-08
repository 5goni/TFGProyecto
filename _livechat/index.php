<?php include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
$usuario = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Cargar historial del usuario para el selector de adjuntos
$stmt = $conn->prepare("SELECT id, tipo, pregunta, aciertos, total_preguntas, contenido_json FROM historial WHERE user_id = ? ORDER BY fecha DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - EduIA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #F4F4F4;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            flex-shrink: 0;
        }

        .topbar-left { display: flex; align-items: center; gap: 12px; font-size: 14px; color: #666; }
        .topbar-logo { font-size: 18px; font-weight: 700; color: #111; text-decoration: none; }
        .topbar-sep { color: #bababa; }
        .topbar-link { color: #333; text-decoration: none; }
        .topbar-link:hover { text-decoration: underline; }
        .topbar-current { color: #444; font-weight: 500; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-user { font-size: 14px; color: #555; }

        .btn-outline {
            font-size: 13px; color: #333; text-decoration: none;
            padding: 6px 14px; border: 1px solid #333; border-radius: 6px; transition: 0.2s;
        }
        .btn-outline:hover { background: #333; color: white; }

        .btn-logout {
            font-size: 13px; color: #333; text-decoration: none;
            padding: 6px 14px; border: 1px solid #c0c0c0; border-radius: 6px;
            background: #f4f4f4; transition: 0.2s;
        }
        .btn-logout:hover { background: #e3e3e3; }

        .chat-wrapper {
            flex: 1; display: flex; align-items: center; justify-content: center; padding: 24px;
        }

        .chat-card {
            background: white; border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1.5px solid #E0E0E0;
            width: 100%; max-width: 700px; display: flex; flex-direction: column;
            height: calc(100vh - 130px); max-height: 700px; overflow: hidden;
        }

        .chat-header {
            padding: 16px 20px; border-bottom: 1px solid #EAEAEA;
            display: flex; align-items: center; gap: 12px; flex-shrink: 0;
        }

        .chat-header-icon {
            width: 38px; height: 38px;
            background: #d4d4d4;
            border-radius: 10px; display: flex; align-items: center;
            justify-content: center; font-size: 17px; flex-shrink: 0;
        }

        .chat-header-info h3 { font-size: 15px; font-weight: 700; color: #111; }
        .chat-header-info p  { font-size: 12px; color: #888; margin-top: 1px; }

        .online-dot {
            width: 7px; height: 7px; background: #555;
            border-radius: 50%; display: inline-block; margin-right: 5px;
        }

        #mensajes {
            flex: 1; overflow-y: auto; padding: 18px;
            display: flex; flex-direction: column; gap: 10px; background: #f4f4f4;
        }

        #mensajes::-webkit-scrollbar { width: 4px; }
        #mensajes::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 10px; }

        .msg-item { display: flex; flex-direction: column; max-width: 74%; }
        .msg-item.mine  { align-self: flex-end;  align-items: flex-end; }
        .msg-item.other { align-self: flex-start; align-items: flex-start; }

        .msg-author { font-size: 11px; font-weight: 700; color: #888; margin-bottom: 3px; padding: 0 4px; }

        .msg-bubble {
            padding: 10px 14px; border-radius: 14px;
            font-size: 14px; line-height: 1.5; word-break: break-word;
        }

        .msg-item.mine  .msg-bubble {
            background: #333;
            color: white; border-bottom-right-radius: 4px;
        }
        .msg-item.other .msg-bubble {
            background: white; color: #333;
            border: 1px solid #E0E0E0; border-bottom-left-radius: 4px;
        }

        .msg-bubble img { max-width: 220px; border-radius: 8px; display: block; margin-top: 6px; cursor: pointer; }

        /* Preview de archivo */
        .file-preview {
            margin-top: 8px; border-radius: 10px; overflow: hidden;
            max-width: 260px; display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; text-decoration: none;
        }
        .msg-item.mine  .file-preview {
            background: rgba(255,255,255,0.15); border: 1.5px solid rgba(255,255,255,0.25);
        }
        .msg-item.other .file-preview {
            background: #F4F4F4; border: 1.5px solid #E0E0E0;
        }
        .file-preview-icon {
            width: 38px; height: 38px; border-radius: 8px; display: flex;
            align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;
        }
        .msg-item.mine  .file-preview-icon { background: rgba(255,255,255,0.2); }
        .msg-item.other .file-preview-icon { background: white; border: 1px solid #E0E0E0; }
        .file-preview-info { flex: 1; min-width: 0; }
        .file-preview-name {
            font-size: 13px; font-weight: 600; white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis; display: block;
        }
        .msg-item.mine  .file-preview-name { color: rgba(255,255,255,0.95); }
        .msg-item.other .file-preview-name { color: #111; }
        .file-preview-size {
            font-size: 11px; margin-top: 2px; display: block;
        }
        .msg-item.mine  .file-preview-size { color: rgba(255,255,255,0.6); }
        .msg-item.other .file-preview-size { color: #888; }

        /* Lightbox imagen */
        .lightbox {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.85); z-index: 500;
            justify-content: center; align-items: center;
        }
        .lightbox.active { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 90vh; border-radius: 10px; }
        .lightbox-close {
            position: absolute; top: 20px; right: 28px;
            color: white; font-size: 32px; cursor: pointer;
            background: none; border: none; line-height: 1;
        }

        /* Tarjeta de adjunto dentro del mensaje */
        .adj-card {
            margin-top: 8px; border-radius: 10px; overflow: hidden;
            border: 1.5px solid rgba(255,255,255,0.3); max-width: 280px;
        }

        .msg-item.other .adj-card { border-color: #E0E0E0; }

        .adj-header {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 12px; font-size: 12px; font-weight: 700;
        }

        .msg-item.mine  .adj-header { background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.9); }
        .msg-item.other .adj-header { background: #F4F4F4; color: #555; }

        .adj-body { padding: 10px 12px; font-size: 13px; }
        .msg-item.mine  .adj-body { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.95); }
        .msg-item.other .adj-body { background: white; color: #333; }

        .adj-preview-line { margin-bottom: 5px; line-height: 1.4; }
        .adj-score { font-size: 12px; margin-top: 4px; font-weight: 700; }

        /* Barra de adjunto pendiente */
        .pending-adjunto {
            display: none; align-items: center; gap: 10px;
            padding: 8px 14px; background: #ebebeb; border-top: 1px solid #d5d5d5;
            flex-shrink: 0;
        }

        .pending-adjunto.visible { display: flex; }

        .pending-adjunto-icon { font-size: 18px; flex-shrink: 0; }
        .pending-adjunto-preview { width: 40px; height: 40px; object-fit: cover; border-radius: 6px; flex-shrink: 0; display: none; }
        .pending-adjunto.visible .pending-adjunto-preview { display: block; }

        .pending-adjunto-info { flex: 1; min-width: 0; }
        .pending-adjunto-info strong { font-size: 13px; font-weight: 700; color: #222; display: block; }
        .pending-adjunto-info span   { font-size: 11px; color: #555; }

        .pending-adjunto-remove {
            width: 24px; height: 24px; border-radius: 6px; border: none;
            background: rgba(0,0,0,0.08); color: #222; cursor: pointer;
            font-size: 14px; display: flex; align-items: center; justify-content: center;
            transition: 0.2s; flex-shrink: 0;
        }
        .pending-adjunto-remove:hover { background: rgba(0,0,0,0.14); }

        /* Input area */
        .chat-input-area {
            padding: 12px 14px; border-top: 1px solid #e1e1e1;
            display: flex; gap: 8px; align-items: center;
            background: white; flex-shrink: 0;
        }

        .file-label {
            width: 34px; height: 34px; border-radius: 8px;
            background: #F4F4F4; border: 1.5px solid #E0E0E0;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 15px; transition: 0.2s; flex-shrink: 0;
        }
        .file-label:hover { background: #ececec; border-color: #bdbdbd; }
        .file-label input { display: none; }

        .btn-adj-hist {
            width: 34px; height: 34px; border-radius: 8px;
            background: #f4f4f4; border: 1.5px solid #d8d8d8;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 15px; transition: 0.2s; flex-shrink: 0;
        }
        .btn-adj-hist:hover { background: #e7e7e7; border-color: #c4c4c4; }

        #msg {
            flex: 1; padding: 9px 13px; border: 1.5px solid #ddd;
            border-radius: 10px; font-size: 14px; font-family: inherit;
            background: #fafafa; transition: border-color 0.2s;
        }
        #msg:focus { outline: none; border-color: #444; background: white; }

        .send-btn {
            width: 36px; height: 36px; border-radius: 10px;
            background: #333;
            border: none; cursor: pointer; display: flex; align-items: center;
            justify-content: center; transition: 0.2s; flex-shrink: 0; font-size: 15px;
            color: white;
        }
        .send-btn:hover { opacity: 0.88; transform: scale(1.05); }

        /* Modal selector historial */
        .modal-hist {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.42); z-index: 300;
            justify-content: center; align-items: center; backdrop-filter: blur(4px);
        }
        .modal-hist.active { display: flex; }

        .modal-hist-card {
            background: white; border-radius: 16px; width: 90%; max-width: 560px;
            max-height: 75vh; display: flex; flex-direction: column;
            box-shadow: 0 16px 48px rgba(0,0,0,0.14); animation: popIn 0.2s ease;
        }

        @keyframes popIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .modal-hist-header {
            padding: 20px 24px 0; display: flex; align-items: center;
            justify-content: space-between;
        }

        .modal-hist-header h3 { font-size: 16px; font-weight: 700; color: #111; }

        .modal-hist-close {
            width: 30px; height: 30px; border-radius: 8px; border: none;
            background: #f0f0f0; cursor: pointer; font-size: 16px; color: #666; transition: 0.2s;
        }
        .modal-hist-close:hover { background: #dedede; }

        .modal-hist-tabs {
            display: flex; gap: 4px; padding: 14px 24px 0; flex-wrap: wrap;
        }

        .mh-tab {
            padding: 7px 14px; border-radius: 7px; border: none;
            background: #f4f4f4; font-size: 13px; font-weight: 600;
            color: #666; cursor: pointer; transition: 0.2s;
        }
        .mh-tab.active { background: #333; color: white; }
        .mh-tab:not(.active):hover { background: #e3e3e3; color: #222; }

        .modal-hist-list {
            flex: 1; overflow-y: auto; padding: 14px 24px 20px;
            display: flex; flex-direction: column; gap: 8px;
        }

        .mh-item {
            display: flex; align-items: center; gap: 12px; padding: 12px 14px;
            border-radius: 10px; border: 1.5px solid #e5e5e5;
            cursor: pointer; transition: 0.2s; background: white;
        }
        .mh-item:hover { border-color: #bdbdbd; background: #F5F5F5; }

        .mh-icon {
            width: 36px; height: 36px; border-radius: 9px; display: flex;
            align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;
            background: #d9d9d9;
        }

        .mh-info { flex: 1; min-width: 0; }
        .mh-title { font-size: 13px; font-weight: 600; color: #111; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mh-meta  { font-size: 11px; color: #777; margin-top: 2px; }

        .mh-empty { text-align: center; color: #999; font-size: 13px; padding: 24px 0; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-left">
        <a href="../index.php" class="topbar-logo">EduIA</a>
        <span class="topbar-sep">/</span>
        <a href="../estudio.php" class="topbar-link">Herramientas</a>
        <span class="topbar-sep">/</span>
        <span class="topbar-current">Chat</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-user"><?php echo htmlspecialchars($usuario); ?></span>
        <a href="../estudio.php" class="btn-outline">Herramientas</a>
        <a href="logout.php" class="btn-logout">Cerrar sesión</a>
    </div>
</div>

<div class="chat-wrapper">
    <div class="chat-card">

        <div class="chat-header">
            <div class="chat-header-icon">💬</div>
            <div class="chat-header-info">
                <h3>Chat en tiempo real</h3>
                <p><span class="online-dot"></span>Conectado como <?php echo htmlspecialchars($usuario); ?></p>
            </div>
        </div>

        <div id="mensajes"></div>

        <div class="pending-adjunto" id="pendingBar">
            <img class="pending-adjunto-preview" id="pendingPreview" src="" alt="">
            <div class="pending-adjunto-icon" id="pendingIcon"></div>
            <div class="pending-adjunto-info">
                <strong id="pendingTitle"></strong>
                <span id="pendingTipo"></span>
            </div>
            <button class="pending-adjunto-remove" onclick="quitarAdjunto()">&#x2715;</button>
        </div>

        <div class="chat-input-area">
            <label class="file-label" title="Adjuntar archivo">
                📁
                <input type="file" id="archivo" accept="image/*,.pdf,.doc,.docx,.txt,.ppt,.pptx,.xls,.xlsx" onchange="archivoSeleccionado(this)">
            </label>
            <button class="btn-adj-hist" onclick="abrirModalHist()" title="Compartir del historial">📚</button>
            <input type="text" id="msg" placeholder="Escribe un mensaje..." onkeydown="if(event.key==='Enter') enviar()">
            <button class="send-btn" onclick="enviar()">&#x27A4;</button>
        </div>

    </div>
</div>

<!-- Lightbox imagen -->
<div class="lightbox" id="lightbox" onclick="this.classList.remove('active')">
    <button class="lightbox-close" onclick="document.getElementById('lightbox').classList.remove('active')">&times;</button>
    <img id="lightboxImg" src="" alt="">
</div>

<!-- Modal selector historial -->
<div class="modal-hist" id="modalHist">
    <div class="modal-hist-card">
        <div class="modal-hist-header">
            <h3>Compartir del historial</h3>
            <button class="modal-hist-close" onclick="cerrarModalHist()">&#x2715;</button>
        </div>

        <div class="modal-hist-tabs">
            <button class="mh-tab active" onclick="switchMhTab('resumen',this)">Resumenes</button>
            <button class="mh-tab" onclick="switchMhTab('test',this)">Tests</button>
            <button class="mh-tab" onclick="switchMhTab('flashcards',this)">Flashcards</button>
            <button class="mh-tab" onclick="switchMhTab('mapa',this)">Mapas</button>
        </div>

        <div class="modal-hist-list" id="mhList"></div>
    </div>
</div>

<script>
const miUsuario   = <?php echo json_encode($usuario); ?>;
const historialJS = <?php echo json_encode($historial); ?>;

const tipoIcons = { resumen: '📄', test: '🧪', flashcards: '🧠', mapa: '🗺️' };
const tipoColors = { resumen: '#d8d8d8', test: '#dedede', flashcards: '#e4e4e4', mapa: '#f0f0f0' };
const tipoLabels = { resumen: 'Resumen', test: 'Test', flashcards: 'Flashcards', mapa: 'Mapa conceptual' };

const extIcons = {
    pdf:  { icon: '📄', color: '#e0e0e0', ext: 'PDF' },
    doc:  { icon: '📄', color: '#d8d8d8', ext: 'DOC' },
    docx: { icon: '📄', color: '#d8d8d8', ext: 'DOCX' },
    ppt:  { icon: '📊', color: '#e6e6e6', ext: 'PPT' },
    pptx: { icon: '📊', color: '#e6e6e6', ext: 'PPTX' },
    xls:  { icon: '📈', color: '#d8d8d8', ext: 'XLS' },
    xlsx: { icon: '📈', color: '#d8d8d8', ext: 'XLSX' },
    txt:  { icon: '📄', color: '#f4f4f4', ext: 'TXT' },
};

const imgExts = ['jpg','jpeg','png','gif','webp','bmp','svg'];

function getExt(nombre) {
    return (nombre || '').split('.').pop().toLowerCase();
}

function esImagen(nombre) {
    return imgExts.includes(getExt(nombre));
}

function formatBytes(b) {
    if (!b) return '';
    if (b < 1024) return b + ' B';
    if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
    return (b/1048576).toFixed(1) + ' MB';
}

let adjuntoPendiente = null;

// â”€â”€ Archivo seleccionado â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function archivoSeleccionado(input) {
    if (!input.files[0]) return;
    quitarAdjunto();
    const f    = input.files[0];
    const ext  = getExt(f.name);
    const info = extIcons[ext] || { icon: esImagen(f.name) ? '🖼️' : '📁', color: '#F4F4F4', ext: ext.toUpperCase() };

    const previewImg = document.getElementById('pendingPreview');
    const iconDiv = document.getElementById('pendingIcon');

    if (esImagen(f.name)) {
        previewImg.src = URL.createObjectURL(f);
        previewImg.style.display = 'block';
        iconDiv.style.display = 'none';
    } else {
        previewImg.style.display = 'none';
        iconDiv.style.display = 'block';
        iconDiv.textContent = info.icon;
    }

    document.getElementById('pendingTitle').textContent = f.name;
    document.getElementById('pendingTipo').textContent  = formatBytes(f.size);
    document.getElementById('pendingBar').classList.add('visible');
}

// â”€â”€ Enviar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function enviar() {
    const msg     = document.getElementById('msg').value.trim();
    const archivo = document.getElementById('archivo').files[0];
    if (!msg && !archivo && !adjuntoPendiente) return;

    const data = new FormData();
    data.append('mensaje', msg);
    if (archivo) data.append('archivo', archivo);
    if (adjuntoPendiente) {
        data.append('adjunto_tipo', adjuntoPendiente.tipo);
        data.append('adjunto_id',   adjuntoPendiente.id);
    }

    await fetch('send_msg.php', { method: 'POST', body: data });

    document.getElementById('msg').value     = '';
    document.getElementById('archivo').value = '';
    quitarAdjunto();
    cargar();
}

// â”€â”€ Cargar mensajes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
async function cargar() {
    try {
        const res  = await fetch('get_msgs.php');
        const data = await res.json();
        const div  = document.getElementById('mensajes');
        const alFinal = div.scrollHeight - div.scrollTop <= div.clientHeight + 60;

        div.innerHTML = data.map(m => {
            const esMio = m.nombre === miUsuario;
            let contenido = '';

            if (m.mensaje) contenido += escHtml(m.mensaje);

            if (m.archivo) {
                const orig = m.archivo_original || m.archivo;
                if (esImagen(orig)) {
                    contenido += `<img src="uploads/${m.archivo}" alt="${escHtml(orig)}" onclick="abrirLightbox(this.src)">`;
                } else {
                    contenido += renderFilePreview(m.archivo, orig);
                }
            }

            if (m.adjunto_tipo && m.adj_titulo) {
                contenido += renderAdjCard(m, esMio);
            }

            return `<div class="msg-item ${esMio ? 'mine' : 'other'}">
                ${!esMio ? `<div class="msg-author">${escHtml(m.nombre)}</div>` : ''}
                <div class="msg-bubble">${contenido}</div>
            </div>`;
        }).join('');

        if (alFinal) div.scrollTop = div.scrollHeight;
    } catch(e) { console.error(e); }
}

function renderFilePreview(archivo, original) {
    const ext  = getExt(original);
    const info = extIcons[ext] || { icon: '📁', color: '#F4F4F4', ext: ext.toUpperCase() || 'FILE' };
    return `<a class="file-preview" href="uploads/${escHtml(archivo)}" target="_blank" download="${escHtml(original)}">
        <div class="file-preview-icon" style="background:${info.color}">${info.icon}</div>
        <div class="file-preview-info">
            <span class="file-preview-name">${escHtml(original)}</span>
            <span class="file-preview-size">${info.ext} &middot; Descargar</span>
        </div>
    </a>`;
}

function abrirLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightbox').classList.add('active');
}

function renderAdjCard(m, esMio) {
    const icon  = tipoIcons[m.adjunto_tipo]  || '📁';
    const label = tipoLabels[m.adjunto_tipo] || m.adjunto_tipo;
    let preview = '';

    if (m.adjunto_tipo === 'resumen' && m.adj_resumen) {
        const txt = m.adj_resumen.replace(/\*\*/g, '').substring(0, 120);
        preview = `<div class="adj-preview-line">${escHtml(txt)}â€¦</div>`;
    } else if (m.adjunto_tipo === 'test') {
        const ac  = m.aciertos ?? '?';
        const tot = m.total_preguntas ?? '?';
        preview = `<div class="adj-score">Resultado: ${ac}/${tot}</div>`;
    } else if (m.adjunto_tipo === 'flashcards' && m.adj_json) {
        const cards = tryParseJSON(m.adj_json) || [];
        preview = `<div class="adj-preview-line">${cards.length} tarjetas</div>`;
        if (cards[0]) preview += `<div class="adj-preview-line">${escHtml(cards[0].frente)}</div>`;
    } else if (m.adjunto_tipo === 'mapa' && m.adj_json) {
        const mapa  = tryParseJSON(m.adj_json) || {};
        const ramas = (mapa.ramas || []).slice(0, 3).map(r => escHtml(r.titulo)).join(', ');
        preview = `<div class="adj-preview-line">${ramas}${(mapa.ramas||[]).length > 3 ? 'â€¦' : ''}</div>`;
    }

    return `<div class="adj-card">
        <div class="adj-header">${icon} ${escHtml(label)}: ${escHtml(m.adj_titulo)}</div>
        <div class="adj-body">${preview}</div>
    </div>`;
}

// â”€â”€ Modal historial â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
let mhTabActual = 'resumen';

function abrirModalHist() {
    document.getElementById('modalHist').classList.add('active');
    renderMhList('resumen');
}

function cerrarModalHist() {
    document.getElementById('modalHist').classList.remove('active');
}

function switchMhTab(tipo, btn) {
    mhTabActual = tipo;
    document.querySelectorAll('.mh-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderMhList(tipo);
}

function renderMhList(tipo) {
    const items = historialJS.filter(h => (h.tipo || 'resumen') === tipo);
    const list  = document.getElementById('mhList');

    if (!items.length) {
        list.innerHTML = `<div class="mh-empty">No tienes ${tipoLabels[tipo]?.toLowerCase() || tipo} guardados.</div>`;
        return;
    }

    list.innerHTML = items.map(h => {
        const cards = (tipo === 'flashcards' && h.contenido_json) ? (tryParseJSON(h.contenido_json)||[]).length : null;
        const ramas = (tipo === 'mapa' && h.contenido_json) ? (tryParseJSON(h.contenido_json)?.ramas||[]).length : null;
        let meta = '';
        if (tipo === 'test') meta = `${h.aciertos ?? '?'}/${h.total_preguntas ?? '?'} aciertos`;
        else if (cards !== null) meta = `${cards} tarjetas`;
        else if (ramas !== null) meta = `${ramas} ramas`;

        return `<div class="mh-item" data-id="${h.id}" data-tipo="${tipo}" data-titulo="${escHtml(h.pregunta).replace(/"/g,'&quot;')}">
            <div class="mh-icon" style="background:${tipoColors[tipo]}">${tipoIcons[tipo]}</div>
            <div class="mh-info">
                <div class="mh-title">${escHtml(h.pregunta)}</div>
                ${meta ? `<div class="mh-meta">${meta}</div>` : ''}
            </div>
        </div>`;
    }).join('');
}

function seleccionarAdjunto(id, tipo, titulo) {
    adjuntoPendiente = { id, tipo, titulo };
    document.getElementById('pendingIcon').textContent  = tipoIcons[tipo] || '📁';
    document.getElementById('pendingTitle').textContent = titulo;
    document.getElementById('pendingTipo').textContent  = tipoLabels[tipo] || tipo;
    document.getElementById('pendingBar').classList.add('visible');
    cerrarModalHist();
}

function quitarAdjunto() {
    adjuntoPendiente = null;
    document.getElementById('pendingBar').classList.remove('visible');
    document.getElementById('pendingTitle').textContent = '';
    document.getElementById('archivo').value = '';
    const previewImg = document.getElementById('pendingPreview');
    if (previewImg.src) {
        URL.revokeObjectURL(previewImg.src);
        previewImg.src = '';
    }
}

// â”€â”€ Utils â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
}

function tryParseJSON(str) {
    try { return JSON.parse(str); } catch(e) { return null; }
}

document.getElementById('modalHist').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalHist();
});

document.getElementById('mhList').addEventListener('click', function(e) {
    const item = e.target.closest('.mh-item');
    if (!item) return;
    seleccionarAdjunto(
        parseInt(item.dataset.id),
        item.dataset.tipo,
        item.dataset.titulo
    );
});

setInterval(cargar, 2000);
document.addEventListener('DOMContentLoaded', cargar);
</script>

</body>
</html>




