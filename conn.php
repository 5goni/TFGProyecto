<?php
// Archivo centralizado de conexión a la base de datos (XAMPP Local)

// Configuración para XAMPP Local
$host = "localhost"; 
$user = "root";              
$pass = "";                  
$db   = "u336643015_livechat";   

// Crear la conexión principal
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Configurar caracteres
$conn->set_charset("utf8mb4");

$conn_chat = $conn;