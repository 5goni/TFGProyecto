<?php
/**
 * ============================================================================
 * ARCHIVO: subir_documento.php
 * ============================================================================
 * PROPÓSITO:
 *   Permite a usuarios subir documentos a la librería compartida de resúmenes.
 *   Soporta dos modos: subir archivo nuevo o reutilizar contenido del historial.
 *
 * FUNCIONALIDAD CLAVE:
 *   - Valida autenticación del usuario
 *   - Modo 1: Subir archivo nuevo (PDF, DOC, DOCX, TXT, PNG, JPG)
 *   - Modo 2: Convertir contenido del historial en documento
 *   - Validaciones: tipo de archivo, tamaño máximo (50MB)
 *   - Genera nombres únicos para archivos (timestamp + hash)
 *   - Almacena en tabla 'resumenes' con metadata
 *   - Guarda archivos en directorio /_livechat/uploads/
 *
 * PARÁMETROS POST:
 *   - tipo: 'nuevo' (subir archivo) o valor historial_id (reutilizar)
 *   - titulo: Título del documento (requerido)
 *   - descripcion: Descripción opcional
 *   - documento: Archivo (requerido si tipo='nuevo')
 *   - historial_id: ID del historial (requerido si tipo != 'nuevo')
 *
 * TIPOS DE ARCHIVO PERMITIDOS:
 *   - PDF (application/pdf)
 *   - Word (.doc, .docx)
 *   - Texto (.txt)
 *   - Imágenes (.png, .jpg, .jpeg)
 *
 * ERRORES VALIDADOS:
 *   - Título vacío
 *   - Archivo no seleccionado
 *   - Tipo de archivo no permitido
 *   - Archivo muy grande (> 50MB)
 *   - Error al crear directorio
 *   - Error al subir archivo
 *   - Error al guardar en BD
 *
 * DEPENDENCIAS:
 *   - conn.php (conexión a BD)
 *   - Session activa
 *   - Directorio /_livechat/uploads/ con permisos de escritura
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
$error = '';
$exito = '';

// Procesar envío de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tipo = $_POST['tipo'] ?? 'nuevo';
    
    if (empty($titulo)) {
        $error = 'El título es obligatorio';
    } else {
        if ($tipo === 'nuevo') {
            // Subir archivo nuevo
            if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Por favor selecciona un archivo';
            } else {
                $archivo = $_FILES['documento'];
                $nombre_original = $archivo['name'];
                $nombre_archivo = time() . '_' . bin2hex(random_bytes(8)) . '_' . basename($nombre_original);
                
                // Usar ruta absoluta
                $upload_dir = __DIR__ . '/_livechat/uploads/';
                $ruta_destino = $upload_dir . $nombre_archivo;
                
                // Validar tipo de archivo
                $tipos_permitidos = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain', 'image/png', 'image/jpeg'];
                if (!in_array($archivo['type'], $tipos_permitidos)) {
                    $error = 'Tipo de archivo no permitido. Se aceptan: PDF, DOC, DOCX, TXT, PNG, JPG';
                } elseif ($archivo['size'] > 50 * 1024 * 1024) { // 50MB
                    $error = 'El archivo es demasiado grande. Máximo 50MB';
                } else {
                    // Crear carpeta si no existe
                    if (!is_dir($upload_dir)) {
                        if (!mkdir($upload_dir, 0777, true)) {
                            $error = 'No se pudo crear el directorio de uploads';
                        }
                    }
                    
                    if (empty($error) && move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
                        // Insertar en base de datos
                        $stmt = $conn->prepare("INSERT INTO resumenes (titulo, descripcion, user_id, documento, fecha_creacion) VALUES (?, ?, ?, ?, NOW())");
                        if ($stmt) {
                            $stmt->bind_param("ssis", $titulo, $descripcion, $user_id, $nombre_archivo);
                            if ($stmt->execute()) {
                                $exito = 'Documento subido exitosamente';
                                header("Location: libreria_resumenes.php");
                                exit;
                            } else {
                                $error = 'Error al guardar en la base de datos';
                                unlink($ruta_destino); // Eliminar archivo si falla la BD
                            }
                            $stmt->close();
                        }
                    } else {
                        $error = 'Error al subir el archivo';
                    }
                }
            }
        } else {
            // Usar contenido previo del historial
            $historial_id = intval($_POST['historial_id'] ?? 0);
            if ($historial_id <= 0) {
                $error = 'Por favor selecciona un documento previo';
            } else {
                // Obtener datos del historial
                $stmt_hist = $conn->prepare("SELECT pregunta, resumen, tipo FROM historial WHERE id = ? AND user_id = ?");
                if ($stmt_hist) {
                    $stmt_hist->bind_param("ii", $historial_id, $user_id);
                    $stmt_hist->execute();
                    $result_hist = $stmt_hist->get_result();
                    
                    if ($result_hist->num_rows > 0) {
                        $hist = $result_hist->fetch_assoc();
                        $documento_contenido = $hist['pregunta'] . "\n\n" . $hist['resumen'];
                        
                        // Guardar como archivo de texto
                        $nombre_archivo = time() . '_' . bin2hex(random_bytes(8)) . '.txt';
                        $upload_dir = __DIR__ . '/_livechat/uploads/';
                        $ruta_destino = $upload_dir . $nombre_archivo;
                        
                        if (!is_dir($upload_dir)) {
                            if (!mkdir($upload_dir, 0777, true)) {
                                $error = 'No se pudo crear el directorio de uploads';
                            }
                        }
                        
                        if (empty($error) && file_put_contents($ruta_destino, $documento_contenido)) {
                            // Insertar en base de datos
                            $stmt = $conn->prepare("INSERT INTO resumenes (titulo, descripcion, user_id, documento, fecha_creacion) VALUES (?, ?, ?, ?, NOW())");
                            if ($stmt) {
                                $tipo_hist = $hist['tipo'] === 'resumen' ? 'Resumen IA' : 'Test/Pregunta';
                                $desc = "Importado desde: " . $tipo_hist;
                                $stmt->bind_param("ssis", $titulo, $desc, $user_id, $nombre_archivo);
                                if ($stmt->execute()) {
                                    $exito = 'Documento creado exitosamente desde contenido previo';
                                    header("Location: libreria_resumenes.php");
                                    exit;
                                } else {
                                    $error = 'Error al guardar en la base de datos';
                                    unlink($ruta_destino);
                                }
                                $stmt->close();
                            }
                        } else {
                            $error = 'Error al crear el archivo';
                        }
                    } else {
                        $error = 'Documento previo no encontrado';
                    }
                    $stmt_hist->close();
                }
            }
        }
    }
}

// Obtener historial del usuario para la opción de usar contenido previo
$historial = [];
$stmt_hist = $conn->prepare("SELECT id, pregunta, resumen, tipo, fecha FROM historial WHERE user_id = ? ORDER BY fecha DESC LIMIT 50");
if ($stmt_hist) {
    $stmt_hist->bind_param("i", $user_id);
    $stmt_hist->execute();
    $result_hist = $stmt_hist->get_result();
    $historial = $result_hist->fetch_all(MYSQLI_ASSOC);
    $stmt_hist->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Documento - EduIA</title>
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
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header {
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 16px;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-error {
            background: #F5F5F5;
            color: #999;
            border: 1px solid #999;
        }

        .alert-success {
            background: #f4f4f4;
            color: #111;
            border: 1px solid #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group input[type="text"],
        .form-group textarea,
        .form-group input[type="file"],
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group textarea:focus,
        .form-group input[type="file"]:focus,
        .form-group select:focus {
            outline: none;
            border-color: #333;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 2px solid #E0E0E0;
        }

        .tab-btn {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            font-size: 14px;
            font-weight: 600;
            color: #999;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            color: #333;
            border-bottom-color: #333;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #333;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: #222;
            transform: translateY(-2px);
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 40px;
            border: 2px dashed #333;
            border-radius: 6px;
            background: #f4f4f4;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #333;
        }

        .file-input-label:hover {
            background: #f4f4f4;
            border-color: #222;
        }

        #documento::-webkit-file-upload-button {
            visibility: hidden;
        }

        #documento {
            display: none;
        }

        .historial-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            margin-top: 10px;
        }

        .historial-item {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .historial-item:hover {
            background: #f4f4f4;
        }

        .historial-item input[type="radio"] {
            margin-right: 10px;
        }

        .historial-item label {
            cursor: pointer;
            display: flex;
            align-items: center;
            margin: 0;
        }

        .historial-text {
            flex: 1;
        }

        .historial-title {
            font-weight: 600;
            color: #333;
        }

        .historial-preview {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 24px;
            }

            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-logo">📚 EduIA</div>
        <div class="topbar-right">
            <span class="user-info"><?php echo htmlspecialchars($usuario); ?></span>
            <a href="libreria_resumenes.php" class="btn-back">← Volver</a>
        </div>
    </div>

    <!-- Contenido -->
    <div class="container">
        <div class="header">
            <h1>📤 Subir Documento</h1>
            <p>Comparte un documento o contenido previo con la comunidad</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($exito)): ?>
            <div class="alert alert-success">âœ… <?php echo htmlspecialchars($exito); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-card">
            <!-- Nombre/Título (común para ambas opciones) -->
            <div class="form-group">
                <label for="titulo">📝 Título del Documento *</label>
                <input type="text" id="titulo" name="titulo" placeholder="Ej: Apuntes de Matemáticas" required value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>">
            </div>

            <!-- Descripción (común para ambas opciones) -->
            <div class="form-group">
                <label for="descripcion">📋 Descripción</label>
                <textarea id="descripcion" name="descripcion" placeholder="Describe brevemente el contenido del documento..."><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
            </div>

            <!-- Tabs para elegir tipo -->
            <div class="tabs">
                <button type="button" class="tab-btn active" onclick="cambiarTab('nuevo')">🆕 Nuevo Archivo</button>
                <?php if (!empty($historial)): ?>
                    <button type="button" class="tab-btn" onclick="cambiarTab('previo')">📚 Contenido Previo</button>
                <?php endif; ?>
            </div>

            <!-- Tab 1: Subir archivo nuevo -->
            <div id="tab-nuevo" class="tab-content active">
                <div class="form-group">
                    <label for="documento">📁 Selecciona un archivo</label>
                    <div class="file-input-wrapper">
                        <label for="documento" class="file-input-label">
                            📥 Arrastra tu archivo aquí o haz clic para seleccionar
                        </label>
                        <input type="file" id="documento" name="documento" accept=".pdf,.doc,.docx,.txt,.png,.jpg,.jpeg">
                    </div>
                    <p style="font-size: 12px; color: #999; margin-top: 10px;">Formatos permitidos: PDF, DOC, DOCX, TXT, PNG, JPG (máx. 50MB)</p>
                </div>
                <input type="hidden" name="tipo" value="nuevo">
            </div>

            <!-- Tab 2: Usar contenido previo -->
            <?php if (!empty($historial)): ?>
                <div id="tab-previo" class="tab-content">
                    <div class="form-group">
                        <label>Selecciona un contenido previo:</label>
                        <div class="historial-list">
                            <?php foreach ($historial as $item): ?>
                                <div class="historial-item">
                                    <label>
                                        <input type="radio" name="historial_id" value="<?php echo $item['id']; ?>">
                                        <div class="historial-text">
                                            <div class="historial-title"><?php echo htmlspecialchars(substr($item['pregunta'], 0, 50)); ?><?php echo strlen($item['pregunta']) > 50 ? '...' : ''; ?></div>
                                            <div class="historial-preview"><?php echo htmlspecialchars(substr($item['resumen'], 0, 80)); ?>...</div>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <input type="hidden" name="tipo" value="previo">
                </div>
            <?php endif; ?>

            <button type="submit" class="btn-submit">âœ… Guardar Documento</button>
        </form>
    </div>

    <script>
        function cambiarTab(tab) {
            // Ocultar todos los tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Mostrar tab seleccionado
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
            
            // Actualizar tipo en el formulario
            document.querySelector('input[name="tipo"]').value = tab;
        }

        // Drag and drop para archivo
        const dropZone = document.querySelector('.file-input-label');
        const fileInput = document.getElementById('documento');

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.style.background = '#f4f4f4';
            dropZone.style.borderColor = '#222';
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.style.background = '#f4f4f4';
            dropZone.style.borderColor = '#333';
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.style.background = '#f4f4f4';
            dropZone.style.borderColor = '#333';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
            }
        });
    </script>
</body>
</html>



