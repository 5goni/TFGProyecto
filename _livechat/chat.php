<?php
session_start();
include '../conn.php';
$conn = $conn_chat;

// Si recibimos un mensaje por POST, lo guardamos
if (isset($_POST['mensaje'])) {
    $usuario = $conn->real_escape_string($_POST['usuario']);
    $mensaje = $conn->real_escape_string($_POST['mensaje']);
    $conn->query("INSERT INTO mensajes (usuario, mensaje) VALUES ('$usuario', '$mensaje')");
    exit;
}

// Si es una petición normal, devolvemos los mensajes en JSON
$resultado = $conn->query("SELECT * FROM mensajes ORDER BY fecha DESC LIMIT 20");
$mensajes = [];
while ($fila = $resultado->fetch_assoc()) {
    $mensajes[] = $fila;
}
echo json_encode(array_reverse($mensajes));
?>