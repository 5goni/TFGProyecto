# TFG - EduIA: Plataforma Inteligente de Estudio

## Descripción del Proyecto

**EduIA** es una plataforma web de educación asistida por inteligencia artificial que ofrece herramientas de estudio automatizadas. Permite a usuarios generar resúmenes, flashcards, tests interactivos y mapas conceptuales utilizando la API de Google Generative Language (Gemini).

## 🎯 Funcionalidades Principales

### 1. **Generación de Resúmenes**
- Entrada: Tema de estudio o documento PDF/imagen
- Procesamiento: API Gemini analiza y genera resumen
- Salida: Resumen estructurado con **conceptos clave** destacados
- Almacenamiento: En tabla `historial`

### 2. **Flashcards Interactivas**
- Genera 10 tarjetas de estudio automáticamente
- Formato: Pregunta-Respuesta (frente-reverso)
- Interfaz interactiva para estudiar
- Almacenamiento en formato JSON

### 3. **Tests de Autoevaluación**
- Genera 8-10 preguntas de opción múltiple
- Sistema de puntuación automático
- Almacena resultados (aciertos/total)
- Realimentación inmediata

### 4. **Mapas Conceptuales**
- Estructura jerárquica: nodo central + ramas
- Visualización gráfica de relaciones
- Almacenamiento en JSON estructurado

### 5. **Biblioteca Compartida**
- Resúmenes publicados por usuarios
- Búsqueda y filtrado
- Información del autor y fecha

### 6. **Historial Personal**
- Todos los trabajos generados
- Organización por tipo
- Opción de reutilizar contenido

## 🏗️ Arquitectura Técnica

### Stack Tecnológico
- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 8.2 (XAMPP)
- **Base de Datos**: MySQL/MariaDB 10.4
- **API IA**: Google Generative Language (Gemini 2.5 Flash)
- **Dependencias**: Mermaid.js (diagramas)

### Estructura de Carpetas
```
/TFG
├── _livechat/              # Módulo de chat (no comentado)
│   ├── auth.php
│   ├── chat.php
│   ├── uploads/            # Almacenamiento de archivos
│   └── ...
├── docs/                   # Documentación (diagramas Mermaid)
│   ├── diagrama_resumidor_ia.md
│   ├── diagrama_secuencia_login.md
│   └── diagrama_secuencia_logout.md
├── uploads/                # Directorio de uploads
├── api_key.php             # Gestión de clave API
├── conn.php                # Conexión a BD centralizada
├── estudio.php             # Dashboard de herramientas
├── flashcards.php          # Generador de flashcards
├── guardar_puntos.php      # API para guardar puntos de tests
├── historial.php           # Visualización de historial
├── index.html              # Landing page estática
├── index.php               # Página principal con login
├── libreria_resumenes.php  # Biblioteca compartida
├── listar_modelos.php      # Herramienta diagnóstico (APIs disponibles)
├── main.php                # Dashboard alternativo
├── mapa_conceptual.php     # Generador de mapas
├── pregunta_gemini.php     # Generador resumen + trivia
├── resumenes.php           # Generador de resúmenes
├── subir_documento.php     # Gestor de carga de documentos
├── test.php                # Generador de tests
├── fix_tildes_symbols.ps1  # Script para corregir encoding
├── package.json            # Dependencias Node.js
├── u336643015_livechat.sql # Esquema BD
└── README.md               # Este archivo
```

## 🗄️ Base de Datos

### Tablas Principales

#### `usuarios`
- `id` (INT): Identificador único
- `nombre` (VARCHAR): Nombre del usuario
- `email` (VARCHAR): Email único
- `contraseña` (VARCHAR): Hash de contraseña
- `fecha_registro` (TIMESTAMP): Fecha de registro

#### `historial`
- `id` (INT): Identificador único
- `user_id` (INT): Referencia a usuario
- `pregunta` (TEXT): Tema o consulta original
- `resumen` (TEXT): Contenido generado
- `tipo` (VARCHAR): 'resumen' | 'test' | 'flashcards' | 'mapa'
- `aciertos` (INT): Puntuación en tests
- `total_preguntas` (INT): Total de preguntas
- `contenido_json` (LONGTEXT): Datos estructurados (JSON)
- `fecha` (TIMESTAMP): Fecha de creación

#### `resumenes`
- `id` (INT): Identificador único
- `titulo` (VARCHAR): Título del resumen
- `descripcion` (TEXT): Descripción breve
- `user_id` (INT): Autor del resumen
- `documento` (VARCHAR): Nombre de archivo
- `fecha_creacion` (TIMESTAMP): Fecha de publicación

#### `mensajes`
- `id` (INT): Identificador único
- `usuario_id` (INT): Referencia a usuario
- `mensaje` (TEXT): Contenido del mensaje
- `fecha` (TIMESTAMP): Fecha del mensaje
- `archivo` (VARCHAR): Archivo adjunto
- `adjunto_tipo` (VARCHAR): Tipo de adjunto

## 🔐 Seguridad

- **Autenticación**: Sessions PHP con validación de credenciales
- **Prepared Statements**: Prevención de inyección SQL
- **Charset UTF-8MB4**: Prevención de encoding attacks
- **Validación de archivos**: Tipos permitidos y tamaño máximo
- **API Key**: Almacenada centralmente en `api_key.php`

## 🚀 Instalación y Uso

### Requisitos
- XAMPP (PHP 8.2+, MySQL/MariaDB)
- Clave API de Google Generative Language
- Navegador web moderno

### Pasos de Instalación

1. **Clonar/Descargar el proyecto**
   ```bash
   cd c:\xampp\htdocs\tfg\TFG
   ```

2. **Restaurar base de datos**
   - Abrir phpMyAdmin
   - Crear base de datos: `u336643015_livechat`
   - Importar: `u336643015_livechat.sql`

3. **Configurar API Key**
   - Editar: `api_key.php`
   - Reemplazar clave de ejemplo con tu API key

4. **Instalar dependencias (opcional)**
   ```bash
   npm install
   ```

5. **Acceder a la plataforma**
   - URL: `http://localhost/tfg/TFG/`
   - O: `http://localhost/tfg/TFG/index.php`

### Flujo del Usuario

```
index.html o index.php
      ↓
   [Login]
      ↓
index.php (verificar autenticación)
      ↓
main.php o estudio.php (Dashboard)
      ↓
[Seleccionar herramienta]
      ├→ resumenes.php       → Generar resumen
      ├→ flashcards.php      → Generar flashcards
      ├→ test.php            → Generar test
      ├→ mapa_conceptual.php → Generar mapa
      ├→ pregunta_gemini.php → Resumen + trivia
      ├→ historial.php       → Ver historial
      ├→ libreria_resumenes.php → Ver biblioteca
      └→ subir_documento.php → Subir archivo
```

## 📝 Archivos Clave Comentados

### Backend (PHP)
- `api_key.php`: Gestión centralizada de clave API
- `conn.php`: Conexión a BD
- `estudio.php`: Dashboard principal
- `resumenes.php`: Generación de resúmenes
- `flashcards.php`: Generación de flashcards
- `test.php`: Generación de tests
- `mapa_conceptual.php`: Generación de mapas
- `historial.php`: Visualización de historial
- `libreria_resumenes.php`: Biblioteca compartida
- `subir_documento.php`: Gestión de documentos
- `guardar_puntos.php`: API AJAX para guardar puntuaciones

### Configuración y Scripts
- `package.json`: Dependencias Node.js
- `fix_tildes_symbols.ps1`: Corrección de encoding
- `u336643015_livechat.sql`: Esquema de BD

### Documentación
- `docs/diagrama_resumidor_ia.md`: Flujo de generación de resúmenes
- `docs/diagrama_secuencia_login.md`: Proceso de autenticación
- `docs/diagrama_secuencia_logout.md`: Proceso de logout

## 🔗 Integración con API

### Google Generative Language (Gemini)

**Endpoint**: `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent`

**Parámetros**:
- `system_instruction`: Instrucciones para el modelo
- `contents`: Contenido a procesar (texto + opcional archivos)
- `key`: API Key

**Respuesta**: JSON con `candidates[0].content.parts[0].text`

## 📊 Ejemplos de Uso

### Generar Resumen
```bash
POST resumenes.php
tema: "Teoría de la Evolución"
archivo: [opcional PDF]
```

### Generar Flashcards
```bash
POST flashcards.php
tema: "Capitalismo"
```

### Generar Test
```bash
POST test.php
tema: "Cambio Climático"
```

## 🐛 Troubleshooting

### Error: "No autenticado"
- Verificar que la sesión está activa
- Hacer login en index.php
- Limpiar cookies del navegador

### Error: "Error de conexión a BD"
- Verificar que XAMPP está corriendo
- Comprobar credenciales en conn.php
- Restaurar BD con u336643015_livechat.sql

### Error: "API Key inválida"
- Verificar API key en api_key.php
- Regenerar API key en Google Cloud Console
- Comprobar que la clave tenga permisos

### Caracteres especiales dañados (Ã¡, Ã©, etc.)
- Ejecutar: `fix_tildes_symbols.ps1`
- Verificar charset en conn.php (debe ser utf8mb4)

## 📚 Recursos Adicionales

- [Google Generative Language API](https://ai.google.dev/)
- [Mermaid Diagrams](https://mermaid.js.org/)
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)

## 👥 Autor

Proyecto de Trabajo Final de Grado (TFG) - Plataforma EduIA

## 📄 Licencia

Proyecto académico - Uso educativo

---

**Última actualización**: Mayo 2026
