<?php
/**
 * ============================================================================
 * ARCHIVO: main.php
 * ============================================================================
 * PROPÓSITO:
 *   Dashboard principal y página de bienvenida para usuarios autenticados.
 *   Presenta visión general de la plataforma EduIA y acceso rápido a funciones.
 *
 * FUNCIONALIDAD CLAVE:
 *   - Verifica autenticación del usuario
 *   - Muestra información del usuario (nombre, datos de sesión)
 *   - Presenta tarjetas de características disponibles
 *   - Proporciona navegación rápida a todas las herramientas
 *   - Interfaz responsiva con topbar sticky
 *   - Botones de acción para login/logout
 *   - Información de características de la plataforma
 *
 * SECCIONES PRINCIPALES:
 *   1. Topbar: Logo, nombre de usuario, botones de acción
 *   2. Hero: Bienvenida y proposición de valor
 *   3. Características: Tarjetas de funcionalidades disponibles
 *   4. CTA (Call to Action): Botones para acceder a funciones
 *
 * CARACTERÍSTICAS DESTACADAS:
 *   - Generación de resúmenes con IA
 *   - Creación de flashcards interactivas
 *   - Tests de autoevaluación
 *   - Mapas conceptuales
 *   - Biblioteca compartida de resúmenes
 *   - Historial de trabajos
 *
 * FLUJO DE NAVEGACIÓN:
 *   - Usuario no autenticado: Mostrar CTA para login
 *   - Usuario autenticado: Mostrar dashboard completo
 *   - Desde aquí: Acceder a estudio.php, resumenes.php, flashcards.php, etc.
 *
 * DEPENDENCIAS:
 *   - conn.php (conexión a BD)
 *   - Session activa
 *
 * VARIABLES DE SESIÓN USADAS:
 *   - $_SESSION['username']: Nombre de usuario
 *   - $_SESSION['user_id']: ID del usuario
 *
 * ============================================================================
 */

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
    <title>EduIA - Plataforma de Estudio Inteligente</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f4f4;
            min-height: 100vh;
            color: #111;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Navbar */
        .navbar {
            background: white;
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 18px rgba(0, 0, 0, 0.14);
        }

        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-logo {
            font-size: 28px;
            font-weight: 800;
            color: #111;
            letter-spacing: -0.5px;
        }

        .navbar-right {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .user-info {
            color: #111;
            font-weight: 600;
        }

        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #111;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: white;
            color: #111;
            border: 2px solid #111;
        }

        .btn-secondary:hover {
            background: #111;
            color: white;
        }

        /* Hero Section */
        .hero {
            text-align: center;
            color: #111;
            padding: 80px 20px;
        }

        .hero h1 {
            font-size: 54px;
            margin-bottom: 20px;
            font-weight: 800;
            line-height: 1.2;
        }

        .hero p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.95;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 60px;
        }

        .btn-hero {
            padding: 15px 40px;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-hero-primary {
            background: white;
            color: #111;
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .btn-hero-secondary {
            background: #111;
            color: white;
            border: 2px solid #111;
        }

        .btn-hero-secondary:hover {
            background: #000;
        }

        /* Features Grid */
        .features {
            padding: 80px 20px;
            background: white;
        }

        .features h2 {
            text-align: center;
            font-size: 42px;
            margin-bottom: 60px;
            color: #111;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #c4c4c4;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.16);
            border-color: #888;
        }

        .feature-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #333;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
            font-size: 15px;
        }

        /* Modules Section */
        .modules {
            padding: 80px 20px;
            background: #e5e5e5;
        }

        .modules h2 {
            text-align: center;
            font-size: 42px;
            margin-bottom: 60px;
            color: #111;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .module-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            border: 1px solid #d1d1d1;
        }

        .module-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.18);
            border-color: #999;
        }

        .module-icon {
            font-size: 42px;
            margin-bottom: 15px;
        }

        .module-card h3 {
            font-size: 20px;
            margin-bottom: 12px;
            color: #333;
        }

        .module-card p {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .module-btn {
            display: inline-block;
            padding: 8px 16px;
            background: #111;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .module-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        /* Stats Section */
        .stats {
            padding: 60px 20px;
            background: white;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
        }

        .stat {
            padding: 20px;
        }

        .stat-number {
            font-size: 36px;
            font-weight: 800;
            color: #111;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-size: 15px;
        }

        /* Footer */
        .footer {
            background: #111;
            color: white;
            text-align: center;
            padding: 40px 20px;
            font-size: 14px;
        }

        .footer p {
            margin: 10px 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 36px;
            }

            .hero p {
                font-size: 16px;
            }

            .navbar-right {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }

            .navbar-right a, .navbar-right button {
                width: 100%;
                text-align: center;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn-hero {
                width: 100%;
            }

            .features h2, .modules h2 {
                font-size: 32px;
            }
        }

        /* Auth Modal */
        .auth-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
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
            background: #111;
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
            color: #666;
            transition: 0.2s;
            border-bottom: 2.5px solid transparent;
        }

        .tab-button.active { color: #111; border-bottom-color: #111; }

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
            border: 1.5px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s;
            background: white;
        }

        .form-group input:focus { outline: none; border-color: #111; background: white; }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #111;
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
            background: #ececec;
            color: #111;
            border: 1px solid #999;
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
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="navbar-logo">🚀 EduIA</div>
                <div class="navbar-right">
                    <?php if ($logueado): ?>
                        <span class="user-info">Bienvenido, <?php echo htmlspecialchars($usuario); ?></span>
                        <a href="index.php" class="btn-secondary">Ir a la aplicación</a>
                        <a href="_livechat/logout.php" class="btn-primary">Cerrar sesión</a>
                    <?php else: ?>
                        <button onclick="openAuthModal()" class="btn-secondary">Iniciar sesión</button>
                        <button onclick="switchTab('register'); openAuthModal();" class="btn-primary">Registrarse</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Tu Asistente de Estudio Inteligente</h1>
            <p>Aprende más rápido con IA generativa. Resuelve dudas, crea resúmenes, memoriza flashcards y domina cualquier tema.</p>
            <div class="hero-buttons">
                <?php if ($logueado): ?>
                    <a href="index.html" class="btn-hero btn-hero-primary">Comenzar ahora</a>
                    <a href="#modulos" class="btn-hero btn-hero-secondary">Explorar funciones</a>
                <?php else: ?>
                    <button onclick="switchTab('register'); openAuthModal();" class="btn-hero btn-hero-primary">Registrarse gratis</button>
                    <a href="#modulos" class="btn-hero btn-hero-secondary">Ver características</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2>¿Por qué elegir EduIA?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🤖</div>
                    <h3>IA Generativa Avanzada</h3>
                    <p>Powered by Gemini 2.5 Multimodal para respuestas precisas y contextualizadas</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📚</div>
                    <h3>Aprendizaje Personalizado</h3>
                    <p>Adapta el contenido a tu ritmo y estilo de aprendizaje</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Herramientas Poderosas</h3>
                    <p>Flashcards, resúmenes, mapas conceptuales y más para estudiar efectivamente</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Seguimiento del Progreso</h3>
                    <p>Visualiza tu historial y monitorea tu avance en cada tema</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Seguro y Privado</h3>
                    <p>Tus datos están protegidos y seguros en nuestros servidores</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>Soporte en Tiempo Real</h3>
                    <p>Chat en vivo para resolver tus dudas al instante</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Modules Section -->
    <section class="modules" id="modulos">
        <div class="container">
            <h2>Módulos Disponibles</h2>
            <div class="modules-grid">
                <div class="module-card">
                    <div class="module-icon">💡</div>
                    <h3>Preguntas con IA</h3>
                    <p>Haz cualquier pregunta y obtén respuestas detalladas con análisis de archivos</p>
                    <a href="index.html" class="module-btn">Acceder</a>
                </div>
                <div class="module-card">
                    <div class="module-icon">📝</div>
                    <h3>Flashcards</h3>
                    <p>Crea y estudia tarjetas interactivas para memorizar rápidamente</p>
                    <a href="flashcards.php" class="module-btn">Acceder</a>
                </div>
                <div class="module-card">
                    <div class="module-icon">📖</div>
                    <h3>Resúmenes</h3>
                    <p>Genera resúmenes automáticos de tus apuntes y documentos</p>
                    <a href="resumenes.php" class="module-btn">Acceder</a>
                </div>
                <div class="module-card">
                    <div class="module-icon">🗺️</div>
                    <h3>Mapas Conceptuales</h3>
                    <p>Visualiza relaciones entre conceptos de forma interactiva</p>
                    <a href="mapa_conceptual.php" class="module-btn">Acceder</a>
                </div>
                <div class="module-card">
                    <div class="module-icon">🎓</div>
                    <h3>Estudio Guiado</h3>
                    <p>Lecciones estructuradas con ejercicios y evaluaciones</p>
                    <a href="estudio.php" class="module-btn">Acceder</a>
                </div>
                <div class="module-card">
                    <div class="module-icon">📜</div>
                    <h3>Historial</h3>
                    <p>Revisa todas tus preguntas, respuestas y progreso anterior</p>
                    <a href="historial.php" class="module-btn">Acceder</a>
                </div>
                <div class="module-card">
                    <div class="module-icon">�</div>
                    <h3>Librería de Resúmenes</h3>
                    <p>Accede a todos los resúmenes y tests que has creado</p>
                    <a href="libreria_resumenes.php" class="module-btn">Acceder</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat">
                    <div class="stat-number">10K+</div>
                    <div class="stat-label">Estudiantes activos</div>
                </div>
                <div class="stat">
                    <div class="stat-number">50K+</div>
                    <div class="stat-label">Preguntas respondidas</div>
                </div>
                <div class="stat">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Satisfacción</div>
                </div>
                <div class="stat">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Disponibilidad</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 EduIA - Plataforma de Estudio Inteligente</p>
            <p>Powered by Gemini 2.5 Multimodal</p>
        </div>
    </footer>

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
