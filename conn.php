<?php
/**
 * ============================================================================
 * ARCHIVO: conn.php
 * ============================================================================
 * PROPÓSITO:
 *   Proporciona la conexión centralizada a la base de datos MySQL para toda
 *   la aplicación. Gestiona la inicialización de la conexión, validación de
 *   errores y configuración de caracteres UTF-8.
 *
 * FUNCIONALIDAD CLAVE:
 *   - Conecta a la base de datos local (XAMPP) usando credenciales hardcodeadas
 *   - Verifica que la conexión sea exitosa, si no, detiene la ejecución
 *   - Configura charset a utf8mb4 para soporte completo de caracteres Unicode
 *   - Define variables globales $conn y $conn_chat para toda la aplicación
 *
 * CONFIGURACIÓN:
 *   Host: localhost (XAMPP local)
 *   Usuario: root (usuario por defecto de XAMPP)
 *   Contraseña: "" (vacía por defecto en XAMPP)
 *   Base de datos: u336643015_livechat
 *
 * DEPENDENCIAS:
 *   - MySQLi (extensión de PHP)
 *
 * USO:
 *   include 'conn.php';
 *   $resultado = $conn->query("SELECT * FROM usuarios");
 *
 * NOTAS:
 *   - Se recomienda pasar a variables de entorno para producción
 *   - Esta conexión se utiliza en prácticamente todos los scripts
 *
 * ============================================================================
 */

// Configuración para XAMPP Local (desarrollo)
$host = "localhost";      // Servidor de base de datos
$user = "root";           // Usuario MySQL (root es el por defecto en XAMPP)
$pass = "";               // Contraseña (vacía por defecto en XAMPP)
$db   = "u336643015_livechat";  // Nombre de la base de datos

// Crear la conexión principal usando MySQLi (procedural style)
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión - si hay error, detener ejecución
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Configurar charset a utf8mb4 para soporte completo de caracteres especiales
// Esto es importante para caracteres acentuados, emojis, caracteres CJK, etc.
$conn->set_charset("utf8mb4");

// Alias de la conexión principal (para compatibilidad si se necesita)
$conn_chat = $conn;