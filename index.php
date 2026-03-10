<?php
session_start();
include 'conn.php';

$usuario = $_SESSION['username'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$logueado = isset($_SESSION['user_id']);
$error = $_SESSION['error'] ?? null;

if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduIA - Plataforma de Estudio</title>
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
            color: #667eea;
            letter-spacing: -0.5px;
        }

        .topbar-right { display: flex; align-items: center; gap: 14px; }

        .topbar-user {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .btn-primary-sm {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary-sm:hover { background: #5568d4; }

        .btn-danger-sm {
            background: #fff0f0;
            color: #c0392b;
            border: none;
            padding: 8px 18px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-danger-sm:hover { background: #ffd8d8; }

        .hero {
            max-width: 860px;
            margin: 72px auto 0;
            padding: 0 24px;
            text-align: center;
        }

        .hero-tag {
            display: inline-block;
            background: #ede9ff;
            color: #7c5cbf;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 20px;
        }

        .hero h1 {
            font-size: 44px;
            font-weight: 800;
            color: #1a1a2e;
            line-height: 1.2;
            margin-bottom: 18px;
            letter-spacing: -1px;
        }

        .hero h1 span { color: #667eea; }

        .hero p {
            font-size: 17px;
            color: #7a7a9a;
            line-height: 1.7;
            max-width: 560px;
            margin: 0 auto 36px;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 64px;
        }

        .btn-hero {
            padding: 13px 28px;
            border-radius: 9px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            border: none;
            text-decoration: none;
            display: inline-block;
        }

        .btn-hero-main {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 16px rgba(102,126,234,0.35);
        }

        .btn-hero-main:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(102,126,234,0.4); }

        .btn-hero-sec {
            background: white;
            color: #667eea;
            border: 2px solid #e0daf8;
        }

        .btn-hero-sec:hover { border-color: #667eea; background: #f5f2ff; }

        .section {
            max-width: 1060px;
            margin: 0 auto;
            padding: 0 24px 72px;
        }

        .section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #bbb;
            margin-bottom: 18px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 18px;
        }

        .feature-card {
            background: white;
            border-radius: 14px;
            padding: 26px 22px;
            border: 1.5px solid #f0f0f8;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
            text-decoration: none;
            display: block;
            cursor: pointer;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 28px rgba(0,0,0,0.09);
        }

        .feature-card.purple:hover { border-color: #9c82f5; }
        .feature-card.blue:hover   { border-color: #4facfe; }
        .feature-card.green:hover  { border-color: #43d98e; }
        .feature-card.orange:hover { border-color: #f5a623; }
        .feature-card.pink:hover   { border-color: #f5576c; }
        .feature-card.teal:hover   { border-color: #06b6d4; }

        .feature-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 14px;
        }

        .feature-icon.purple { background: #f0ecff; }
        .feature-icon.blue   { background: #e6f4ff; }
        .feature-icon.green  { background: #e4faf1; }
        .feature-icon.orange { background: #fff7e6; }
        .feature-icon.pink   { background: #fff0f3; }
        .feature-icon.teal   { background: #e0fafb; }

        .feature-name {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
        }

        .feature-desc {
            font-size: 13px;
            color: #8a8aaa;
            line-height: 1.5;
        }

        .guest-cta {
            max-width: 540px;
            margin: 0 auto;
            text-align: center;
            background: white;
            border-radius: 18px;
            padding: 48px 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1.5px solid #ede9ff;
        }

        .guest-cta h2 { font-size: 24px; font-weight: 700; color: #1a1a2e; margin-bottom: 10px; }
        .guest-cta p { font-size: 15px; color: #7a7a9a; line-height: 1.6; margin-bottom: 28px; }

        .cta-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }

        .btn-cta {
            padding: 11px 26px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            border: none;
            text-decoration: none;
        }

        .btn-cta-main {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-cta-main:hover { opacity: 0.9; transform: translateY(-1px); }

        .btn-cta-sec {
            background: #f4f6fb;
            color: #667eea;
            border: 1.5px solid #e0daf8;
        }

        .btn-cta-sec:hover { background: #ede9ff; }

        /* Auth Modal */
        .auth-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(20,20,40,0.45);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }

        .auth-modal.active { display: flex; }

        .auth-card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
            animation: popIn 0.2s ease;
        }

        @keyframes popIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .auth-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 32px 28px 24px;
            text-align: center;
            position: relative;
        }

        .auth-header h2 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
        .auth-header p { font-size: 14px; opacity: 0.85; }

        .close-modal {
            position: absolute;
            top: 14px;
            right: 18px;
            font-size: 22px;
            font-weight: bold;
            cursor: pointer;
            color: rgba(255,255,255,0.8);
            background: none;
            border: none;
            line-height: 1;
        }

        .close-modal:hover { color: white; }

        .tabs {
            display: flex;
            border-bottom: 1.5px solid #f0f0f0;
        }

        .tab-button {
            flex: 1;
            padding: 14px;
            background: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #bbb;
            transition: 0.2s;
            border-bottom: 2.5px solid transparent;
        }

        .tab-button.active { color: #667eea; border-bottom-color: #667eea; }

        .tab-content { display: none; padding: 24px 28px 28px; }
        .tab-content.active { display: block; }

        .form-group { margin-bottom: 16px; }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #444;
            font-weight: 600;
            font-size: 13px;
        }

        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e8eaf0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
            background: #fafafa;
        }

        .form-group input:focus { outline: none; border-color: #667eea; background: white; }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 4px;
        }

        .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); }

        .error-message {
            background: #fff0f0;
            color: #c0392b;
            border: 1px solid #ffc0c0;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 13px;
            display: none;
        }

        .error-message.show { display: block; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-logo">EduIA</div>
    <div class="topbar-right">
        <?php if ($logueado): ?>
            <span class="topbar-user">Hola, <?php echo htmlspecialchars($usuario); ?></span>
            <a href="_livechat/logout.php" class="btn-danger-sm">Cerrar sesión</a>
        <?php else: ?>
            <button onclick="switchTab('register'); openAuthModal();" class="btn-hero-sec btn-hero" style="padding:8px 18px;font-size:14px;">Registrarse</button>
            <button onclick="openAuthModal()" class="btn-primary-sm">Iniciar sesión</button>
        <?php endif; ?>
    </div>
</div>

<?php if ($logueado): ?>

<div class="hero">
    <div class="hero-tag">Bienvenido, <?php echo htmlspecialchars($usuario); ?></div>
    <h1>Tu plataforma de<br><span>estudio inteligente</span></h1>
    <p>Genera resúmenes, tests, flashcards y mapas conceptuales con inteligencia artificial en segundos.</p>
    <div class="hero-actions">
        <a href="estudio.php" class="btn-hero btn-hero-main">Ir a herramientas</a>
        <a href="historial.php" class="btn-hero btn-hero-sec">Mi historial</a>
    </div>
</div>

<div class="section">
    <div class="section-label">Herramientas disponibles</div>
    <div class="cards-grid">

        <a href="resumenes.php" class="feature-card purple">
            <div class="feature-icon purple">📄</div>
            <div class="feature-name">Resumen</div>
            <div class="feature-desc">Resúmenes claros y completos sobre cualquier tema con IA.</div>
        </a>

        <a href="test.php" class="feature-card blue">
            <div class="feature-icon blue">🧪</div>
            <div class="feature-name">Test interactivo</div>
            <div class="feature-desc">Preguntas de opción múltiple para evaluar tu comprensión.</div>
        </a>

        <a href="flashcards.php" class="feature-card green">
            <div class="feature-icon green">🃏</div>
            <div class="feature-name">Flashcards</div>
            <div class="feature-desc">Tarjetas de memoria para repasar conceptos clave.</div>
        </a>

        <a href="mapa_conceptual.php" class="feature-card orange">
            <div class="feature-icon orange">🗺️</div>
            <div class="feature-name">Mapa Conceptual</div>
            <div class="feature-desc">Visualiza relaciones entre ideas en un mapa interactivo.</div>
        </a>

        <a href="_livechat/index.php" class="feature-card pink">
            <div class="feature-icon pink">💬</div>
            <div class="feature-name">Chat</div>
            <div class="feature-desc">Comunícate con otros estudiantes en tiempo real.</div>
        </a>

        <a href="historial.php" class="feature-card teal">
            <div class="feature-icon teal">📚</div>
            <div class="feature-name">Historial</div>
            <div class="feature-desc">Revisa tus sesiones de estudio y resultados anteriores.</div>
        </a>

    </div>
</div>

<?php else: ?>

<div class="hero" style="margin-bottom: 48px;">
    <div class="hero-tag">IA para estudiantes</div>
    <h1>Estudia más inteligente,<br><span>no más duro</span></h1>
    <p>Genera resúmenes, tests, flashcards y mapas conceptuales con IA. Todo en un solo lugar.</p>
    <div class="hero-actions">
        <button onclick="switchTab('register'); openAuthModal();" class="btn-hero btn-hero-main">Crear cuenta gratis</button>
        <button onclick="openAuthModal()" class="btn-hero btn-hero-sec">Iniciar sesión</button>
    </div>
</div>

<div class="section">
    <div class="section-label">Qué puedes hacer</div>
    <div class="cards-grid">

        <div class="feature-card purple" style="cursor:default;">
            <div class="feature-icon purple">📄</div>
            <div class="feature-name">Resúmenes con IA</div>
            <div class="feature-desc">Obtén resúmenes detallados sobre cualquier tema en segundos.</div>
        </div>

        <div class="feature-card blue" style="cursor:default;">
            <div class="feature-icon blue">🧪</div>
            <div class="feature-name">Tests interactivos</div>
            <div class="feature-desc">Evalúa tu conocimiento con preguntas generadas por IA.</div>
        </div>

        <div class="feature-card green" style="cursor:default;">
            <div class="feature-icon green">🃏</div>
            <div class="feature-name">Flashcards</div>
            <div class="feature-desc">Memoriza conceptos con tarjetas interactivas de doble cara.</div>
        </div>

        <div class="feature-card orange" style="cursor:default;">
            <div class="feature-icon orange">🗺️</div>
            <div class="feature-name">Mapas Conceptuales</div>
            <div class="feature-desc">Visualiza la estructura de cualquier tema de forma clara.</div>
        </div>

    </div>

    <div style="margin-top: 48px;">
        <div class="guest-cta">
            <h2>Empieza ahora gratis</h2>
            <p>Crea tu cuenta y accede a todas las herramientas de estudio con inteligencia artificial.</p>
            <div class="cta-btns">
                <button onclick="switchTab('register'); openAuthModal();" class="btn-cta btn-cta-main">Crear cuenta</button>
                <button onclick="openAuthModal()" class="btn-cta btn-cta-sec">Ya tengo cuenta</button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- Auth Modal -->
<div class="auth-modal" id="authModal">
    <div class="auth-card">
        <div class="auth-header">
            <button class="close-modal" onclick="closeAuthModal()">&times;</button>
            <h2>Bienvenido a EduIA</h2>
            <p>Inicia sesión o crea tu cuenta para continuar</p>
        </div>

        <div class="tabs">
            <button class="tab-button active" onclick="switchTab('login')">Iniciar sesión</button>
            <button class="tab-button" onclick="switchTab('register')">Registrarse</button>
        </div>

        <div class="tab-content active" id="login">
            <?php if ($error): ?>
                <div class="error-message show"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="_livechat/auth.php">
                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" name="nombre" placeholder="Tu nombre de usuario" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="Tu contraseña" required>
                </div>
                <input type="hidden" name="action" value="login">
                <button type="submit" class="btn-submit">Iniciar sesión</button>
            </form>
        </div>

        <div class="tab-content" id="register">
            <form method="POST" action="_livechat/auth.php">
                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" name="nombre" placeholder="Elige tu nombre de usuario" required>
                </div>
                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="email" placeholder="tu@correo.com" required>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" placeholder="Crea una contraseña segura" required>
                </div>
                <input type="hidden" name="action" value="register">
                <button type="submit" class="btn-submit">Crear cuenta</button>
            </form>
        </div>
    </div>
</div>

<script>
    function openAuthModal() {
        document.getElementById('authModal').classList.add('active');
    }

    function closeAuthModal() {
        document.getElementById('authModal').classList.remove('active');
    }

    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));
        const tab = document.getElementById(tabId);
        if (tab) tab.classList.add('active');
        const buttons = document.querySelectorAll('.tab-button');
        if (tabId === 'login') buttons[0].classList.add('active');
        else if (tabId === 'register') buttons[1].classList.add('active');
    }

    <?php if ($error): ?>
    openAuthModal();
    <?php endif; ?>

    document.getElementById('authModal').addEventListener('click', function(e) {
        if (e.target === this) closeAuthModal();
    });
</script>

</body>
</html>
