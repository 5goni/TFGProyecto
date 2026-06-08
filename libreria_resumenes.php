<?php
/**
 * ============================================================================
 * ARCHIVO: libreria_resumenes.php
 * ============================================================================
 * PROPÓSITO:
 *   Biblioteca compartida de resúmenes creados por usuarios de la plataforma.
 *   Permite buscar, filtrar y visualizar resúmenes publicados por otros usuarios.
 *
 * FUNCIONALIDAD CLAVE:
 *   - Valida autenticación del usuario
 *   - Recupera resúmenes de tabla 'resumenes' usando JOIN con usuarios
 *   - Implementa buscador en tiempo real (título y descripción)
 *   - Muestra información del autor y fecha de creación
 *   - Permite descargar/ver documentos completos
 *   - Permite eliminar resúmenes propios
 *   - Limita visualización a 100 resúmenes más recientes
 *   - Interfaz responsive con tarjetas de presentación
 *
 * CONSULTAS SQL CLAVE:
 *   - SELECT desde tabla 'resumenes' con JOIN 'usuarios'
 *   - Búsqueda LIKE en titulo y descripcion
 *   - Ordenamiento por fecha_creacion DESC
 *
 * PARÁMETROS:
 *   - busqueda (POST): texto para filtrar por título/descripción
 *   - eliminar_id (POST): ID del resumen a eliminar (solo del usuario)
 *
 * CAMPOS MOSTRADOS:
 *   - Título del resumen
 *   - Descripción (primeros 200 caracteres)
 *   - Autor (nombre del usuario)
 *   - Fecha de creación
 *   - Enlace para descargar documento
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

$usuario = $_SESSION['username'] ?? 'Usuario';
$user_id = $_SESSION['user_id'];

// Procesar eliminación antes de enviar cualquier HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $eliminar_id = intval($_POST['eliminar_id']);
    $stmt_del = $conn->prepare("DELETE FROM resumenes WHERE id = ? AND user_id = ?");
    if ($stmt_del) {
        $stmt_del->bind_param("ii", $eliminar_id, $user_id);
        $stmt_del->execute();
        $stmt_del->close();
    }
    header("Location: libreria_resumenes.php");
    exit;
}

$busqueda = isset($_POST['busqueda']) ? trim($_POST['busqueda']) : '';

// Construir la consulta base con JOIN a usuarios para obtener nombre del autor
$query = "SELECT r.id, r.titulo, r.descripcion, r.user_id, r.documento, r.fecha_creacion, u.nombre as autor 
          FROM resumenes r 
          JOIN usuarios u ON r.user_id = u.id";
$params = [];
$types = "";

// Buscar en título o descripción
if (!empty($busqueda)) {
    $query .= " WHERE (r.titulo LIKE ? OR r.descripcion LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $types .= "ss";
}

$query .= " ORDER BY r.fecha_creacion DESC LIMIT 100";

$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($types)) {
        // Verificar que types coincida con params
        if (strlen($types) === count($params)) {
            $stmt->bind_param($types, ...$params);
        }
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $registros = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $registros = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librería de Resúmenes - EduIA</title>
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
            padding: 0 40px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-logo {
            font-size: 20px;
            font-weight: 800;
            background: #111;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            font-size: 14px;
            color: #333;
            font-weight: 600;
        }

        .btn-back {
            padding: 8px 16px;
            background: #333;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back:hover {
            background: #222;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header {
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 36px;
            color: #333;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 16px;
        }

        /* Search and Filter Section */
        .search-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .search-group {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #333;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-search, .btn-reset {
            padding: 10px 25px;
            background: #333;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .btn-search:hover {
            background: #222;
            transform: translateY(-1px);
        }

        .btn-reset {
            background: #999;
        }

        .btn-reset:hover {
            background: #777;
        }

        /* Registros Grid */
        .registros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .registro-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #E0E0E0;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .registro-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
            border-color: #333;
        }

        .registro-type {
            display: inline-block;
            padding: 5px 12px;
            background: #E0E0E0;
            color: #333;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 12px;
            width: fit-content;
        }

        .registro-type.resumen {
            background: #f5f5f5;
            color: #999;
        }

        .registro-type.pregunta {
            background: #F5F5F5;
            color: #111;
        }

        .registro-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
            word-break: break-word;
            line-height: 1.4;
        }

        .registro-preview {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
            flex-grow: 1;
            max-height: 100px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            text-overflow: ellipsis;
        }

        .registro-date {
            font-size: 12px;
            color: #999;
            margin-bottom: 15px;
        }

        .registro-actions {
            display: flex;
            gap: 10px;
        }

        .registro-actions form {
            flex: 1;
            display: contents;
        }

        .btn-ver, .btn-eliminar {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            text-align: center;
            flex: 1;
        }

        .btn-ver {
            background: #333;
            color: white;
        }

        .btn-ver:hover {
            background: #222;
        }

        .btn-eliminar {
            background: #F5F5F5;
            color: #999;
            border: 1px solid #f0f0f0;
        }

        .btn-eliminar:hover {
            background: #F5F5F5;
            border-color: #999;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            border: 1px solid #E0E0E0;
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .empty-state a {
            display: inline-block;
            padding: 12px 30px;
            background: #333;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .empty-state a:hover {
            background: #222;
            transform: translateY(-2px);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(3px);
        }

        .modal-content {
            background-color: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #E0E0E0;
        }

        .modal-header h2 {
            font-size: 24px;
            color: #333;
            flex: 1;
            word-break: break-word;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            color: #999;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            transition: color 0.2s ease;
        }

        .close:hover {
            color: #333;
        }

        .modal-body {
            line-height: 1.8;
            color: #333;
            font-size: 15px;
        }

        .stats-bar {
            background: #111;
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 13px;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .search-group {
                grid-template-columns: 1fr;
            }

            .topbar {
                padding: 0 20px;
            }

            .container {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 24px;
            }

            .registros-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 20px auto;
            }
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-logo">📚 EduIA - Librería</div>
        <div class="topbar-right">
            <span class="user-info"><?php echo htmlspecialchars($usuario); ?></span>
            <a href="main.php" class="btn-back">← Volver</a>
        </div>
    </div>

    <!-- Contenido -->
    <div class="container">
        <div class="header">
            <h1>📚 Librería de Resúmenes</h1>
            <p>Accede y gestiona todos los resúmenes y tests disponibles</p>
        </div>

        <!-- Estadísticas -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number"><?php echo count($registros); ?></div>
                <div class="stat-label">Documentos Totales</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo count(array_unique(array_column($registros, 'user_id'))); ?></div>
                <div class="stat-label">Usuarios Contribuyentes</div>
            </div>
        </div>

        <!-- Búsqueda y Filtros -->
        <form method="POST" class="search-section">
            <div class="search-group">
                <div class="form-group">
                    <label for="busqueda">🔍 Buscar documento:</label>
                    <input type="text" id="busqueda" name="busqueda" placeholder="Ej: Matemáticas, Historia..." value="<?php echo htmlspecialchars($busqueda); ?>">
                </div>
                <button type="submit" class="btn-search">Buscar</button>
                <a href="libreria_resumenes.php" class="btn-reset">Limpiar</a>
            </div>
        </form>

        <!-- Registros Grid -->
        <?php if (empty($registros)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h2>No hay documentos aún</h2>
                <p>Sé el primero en subir un documento a la librería</p>
                <a href="subir_documento.php">Subir Documento</a>
            </div>
        <?php else: ?>
            <div class="registros-grid">
                <?php foreach ($registros as $registro): ?>
                    <div class="registro-card">
                        <span class="registro-type resumen">📄 Documento</span>
                        <h3 class="registro-title"><?php echo htmlspecialchars($registro['titulo']); ?></h3>
                        <p class="registro-preview"><?php echo htmlspecialchars(substr($registro['descripcion'], 0, 150)); ?></p>
                        <p style="font-size: 12px; color: #333; margin: 8px 0;">👤 <?php echo htmlspecialchars($registro['autor']); ?></p>
                        <p class="registro-date">📅 <?php echo date('d/m/Y H:i', strtotime($registro['fecha_creacion'])); ?></p>
                        <div class="registro-actions">
                            <button class="btn-ver" onclick="abrirModal(<?php echo $registro['id']; ?>, '<?php echo htmlspecialchars(addslashes($registro['titulo']), ENT_QUOTES); ?>', '<?php echo htmlspecialchars(addslashes($registro['descripcion']), ENT_QUOTES); ?>', '<?php echo htmlspecialchars($registro['documento']); ?>')">👁️ Ver</button>
                            <?php if ($registro['user_id'] == $user_id): ?>
                                <form method="POST" style="display:inline; margin: 0;">
                                    <input type="hidden" name="eliminar_id" value="<?php echo $registro['id']; ?>">
                                    <button type="submit" class="btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este documento?');">🗑️ Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title"></h2>
                <button class="close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body" id="modal-body"></div>
            <div id="modal-viewer" style="margin-top: 20px; display: none;"></div>
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #E0E0E0;">
                <a id="modal-download" href="#" class="btn-ver" style="display: inline-block; text-decoration: none;">⬇️ Descargar</a>
            </div>
        </div>
    </div>

    <script>
        function abrirModal(id, titulo, descripcion, documento) {
            document.getElementById('modal-title').innerText = titulo;
            document.getElementById('modal-body').innerText = descripcion;
            
            const modalDownload = document.getElementById('modal-download');
            const modalViewer = document.getElementById('modal-viewer');
            
            if (documento && documento.trim()) {
                const fileUrl = '_livechat/uploads/' + documento;
                modalDownload.href = fileUrl;
                modalDownload.style.display = 'inline-block';
                
                // Determinar tipo de archivo para mostrar
                const extension = documento.split('.').pop().toLowerCase();
                if (extension === 'pdf') {
                    modalViewer.innerHTML = '<iframe src="' + fileUrl + '" width="100%" height="600px" style="border: none;"></iframe>';
                    modalViewer.style.display = 'block';
                } else if (['png', 'jpg', 'jpeg'].includes(extension)) {
                    modalViewer.innerHTML = '<img src="' + fileUrl + '" style="max-width: 100%; max-height: 600px; display: block; margin: 0 auto;">';
                    modalViewer.style.display = 'block';
                } else {
                    modalViewer.style.display = 'none';
                }
            } else {
                modalDownload.style.display = 'none';
                modalViewer.style.display = 'none';
            }
            
            document.getElementById('modal').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('modal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>


</body>
</html>


