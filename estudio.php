<?php
/**
 * ============================================================================
 * ARCHIVO: estudio.php
 * ============================================================================
 * PROPÓSITO:
 *   Dashboard principal de herramientas de estudio para usuarios autenticados.
 *   Proporciona acceso a todas las funcionalidades de generación de contenido
 *   educativo usando IA (resúmenes, flashcards, tests, mapas conceptuales).
 *
 * FUNCIONALIDAD CLAVE:
 *   - Verifica autenticación del usuario (redirecciona a index.php si no está logueado)
 *   - Muestra interfaz con tarjetas de herramientas disponibles
 *   - Proporciona navegación a diferentes módulos de estudio
 *   - Incluye barra superior (topbar) con información del usuario
 *   - Organiza herramientas en secciones (Generación de contenido, Biblioteca)
 *
 * HERRAMIENTAS DISPONIBLES:
 *   1. Resúmenes - Generar resúmenes de temas con IA
 *   2. Flashcards - Crear tarjetas de estudio interactivas
 *   3. Tests - Generar preguntas de opción múltiple
 *   4. Mapas conceptuales - Visualizar relaciones entre conceptos
 *   5. Pregunta a Gemini - Chat directo con la IA
 *   6. Historial - Ver trabajos anteriores
 *   7. Librería de resúmenes - Acceder a resúmenes compartidos
 *   8. Subir documentos - Cargar documentos propios
 *
 * DEPENDENCIAS:
 *   - conn.php (conexión a BD)
 *   - Session activa (usuario autenticado)
 *
 * FLUJO:
 *   1. Inicia sesión
 *   2. Verifica autenticación
 *   3. Obtiene nombre de usuario
 *   4. Muestra interfaz con tarjetas de herramientas
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herramientas de Estudio</title>
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
            gap: 16px;
        }

        .topbar-logo {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            text-decoration: none;
        }

        .topbar-sep {
            color: #ccc;
            font-size: 18px;
        }

        .topbar-title {
            font-size: 15px;
            color: #555;
            font-weight: 500;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-user {
            font-size: 14px;
            color: #666;
        }

        .btn-back {
            font-size: 13px;
            color: #333;
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid #333;
            border-radius: 6px;
            transition: 0.2s;
        }

        .btn-back:hover {
            background: #333;
            color: white;
        }

        .page-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 48px 24px;
        }

        .page-header {
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #111;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #555;
            font-size: 15px;
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 16px;
            margin-top: 40px;
        }

        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }

        .tool-card {
            background: white;
            border-radius: 14px;
            padding: 28px 24px;
            text-decoration: none;
            border: 1.5px solid transparent;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
            display: flex;
            flex-direction: column;
            gap: 12px;
            cursor: pointer;
        }

        .tool-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 28px rgba(0,0,0,0.10);
        }

        .tool-card.purple { border-color: #F0F0F0; }
        .tool-card.purple:hover { border-color: #444; box-shadow: 0 10px 28px rgba(156,130,245,0.18); }

        .tool-card.blue { border-color: #F0F0F0; }
        .tool-card.blue:hover { border-color: #333; box-shadow: 0 10px 28px rgba(79,172,254,0.18); }

        .tool-card.green { border-color: #F0F0F0; }
        .tool-card.green:hover { border-color: #333; box-shadow: 0 10px 28px rgba(67,217,142,0.18); }

        .tool-card.orange { border-color: #F5F5F5; }
        .tool-card.orange:hover { border-color: #999; box-shadow: 0 10px 28px rgba(245,166,35,0.18); }

        .tool-card.red { border-color: #F5F5F5; }
        .tool-card.red:hover { border-color: #999; box-shadow: 0 10px 28px rgba(245,96,96,0.18); }

        .tool-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .tool-icon.purple { background: #F5F5F5; }
        .tool-icon.blue   { background: #F5F5F5; }
        .tool-icon.green  { background: #F5F5F5; }
        .tool-icon.orange { background: #F5F5F5; }
        .tool-icon.red    { background: #F5F5F5; }

        .tool-name {
            font-size: 16px;
            font-weight: 700;
            color: #111;
        }

        .tool-desc {
            font-size: 13px;
            color: #888;
            line-height: 1.5;
        }

        .tool-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-purple { background: #F5F5F5; color: #444; }
        .badge-blue   { background: #F5F5F5; color: #333; }
        .badge-green  { background: #F5F5F5; color: #333; }
        .badge-orange { background: #F5F5F5; color: #888; }
        .badge-red    { background: #F5F5F5; color: #999; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-left">
        <a href="index.php" class="topbar-logo">EduIA</a>
        <span class="topbar-sep">/</span>
        <span class="topbar-title">Herramientas de Estudio</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-user">Hola, <?php echo htmlspecialchars($usuario); ?></span>
        <a href="index.php" class="btn-back">Inicio</a>
    </div>
</div>

<div class="page-wrapper">
    <div class="page-header">
        <h1>Herramientas de Estudio</h1>
        <p>Elige una herramienta para generar contenido con IA sobre cualquier tema.</p>
    </div>

    <div class="section-label">Generar contenido</div>
    <div class="tools-grid">

        <a href="resumenes.php" class="tool-card purple">
            <div class="tool-icon purple">📄</div>
            <div class="tool-name">Resumen</div>
            <div class="tool-desc">Obtén un resumen claro y estructurado sobre cualquier tema o documento.</div>
            <span class="tool-badge badge-purple">IA Gemini</span>
        </a>

        <a href="test.php" class="tool-card blue">
            <div class="tool-icon blue">🧪</div>
            <div class="tool-name">Test interactivo</div>
            <div class="tool-desc">Genera preguntas tipo test para evaluar tu comprensión del tema.</div>
            <span class="tool-badge badge-blue">Quiz</span>
        </a>

        <a href="flashcards.php" class="tool-card green">
            <div class="tool-icon green">🧠</div>
            <div class="tool-name">Flashcards</div>
            <div class="tool-desc">Crea tarjetas de memoria con pregunta y respuesta para repasar conceptos clave.</div>
            <span class="tool-badge badge-green">Memorización</span>
        </a>

        <a href="mapa_conceptual.php" class="tool-card orange">
            <div class="tool-icon orange">🗺️</div>
            <div class="tool-name">Mapa Conceptual</div>
            <div class="tool-desc">Visualiza las relaciones entre conceptos en un mapa interactivo.</div>
            <span class="tool-badge badge-orange">Visual</span>
        </a>

        <a href="libreria_resumenes.php" class="tool-card red">
            <div class="tool-icon red">📚</div>
            <div class="tool-name">Biblioteca de Resúmenes</div>
            <div class="tool-desc">Accede a tus resúmenes guardados y revisa contenido previo rápidamente.</div>
            <span class="tool-badge badge-red">Historial</span>
        </a>

    </div>

    <div class="section-label">Mi actividad</div>
    <div class="tools-grid">

        <a href="historial.php" class="tool-card red">
            <div class="tool-icon red">📚</div>
            <div class="tool-name">Historial</div>
            <div class="tool-desc">Revisa todos tus resúmenes generados y resultados de test anteriores.</div>
            <span class="tool-badge badge-red">Registro</span>
        </a>

    </div>
</div>

</body>
</html>



