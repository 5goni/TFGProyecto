<?php
session_start();

// Incluir conexión centralizada a base de datos livechat
include '../conn.php';

// Usar la conexión de livechat
$conn = $conn_chat;
?>