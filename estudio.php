<?php
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
            gap: 16px;
        }

        .topbar-logo {
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
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
            color: #667eea;
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid #667eea;
            border-radius: 6px;
            transition: 0.2s;
        }

        .btn-back:hover {
            background: #667eea;
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
            color: #1a1a2e;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #7a7a9a;
            font-size: 15px;
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #aaa;
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

        .tool-card.purple { border-color: #e8e4ff; }
        .tool-card.purple:hover { border-color: #9c82f5; box-shadow: 0 10px 28px rgba(156,130,245,0.18); }

        .tool-card.blue { border-color: #dff0ff; }
        .tool-card.blue:hover { border-color: #4facfe; box-shadow: 0 10px 28px rgba(79,172,254,0.18); }

        .tool-card.green { border-color: #d8f5e8; }
        .tool-card.green:hover { border-color: #43d98e; box-shadow: 0 10px 28px rgba(67,217,142,0.18); }

        .tool-card.orange { border-color: #fff0dc; }
        .tool-card.orange:hover { border-color: #f5a623; box-shadow: 0 10px 28px rgba(245,166,35,0.18); }

        .tool-card.red { border-color: #ffe4e4; }
        .tool-card.red:hover { border-color: #f56060; box-shadow: 0 10px 28px rgba(245,96,96,0.18); }

        .tool-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .tool-icon.purple { background: #f0ecff; }
        .tool-icon.blue   { background: #e6f4ff; }
        .tool-icon.green  { background: #e4faf1; }
        .tool-icon.orange { background: #fff7e6; }
        .tool-icon.red    { background: #fff0f0; }

        .tool-name {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .tool-desc {
            font-size: 13px;
            color: #8a8aaa;
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

        .badge-purple { background: #f0ecff; color: #7c5cbf; }
        .badge-blue   { background: #e6f4ff; color: #2b7fd4; }
        .badge-green  { background: #e4faf1; color: #1e8a5a; }
        .badge-orange { background: #fff7e6; color: #b97a00; }
        .badge-red    { background: #fff0f0; color: #b94040; }
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
            <div class="tool-icon green">🃏</div>
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
