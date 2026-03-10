<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) exit;

$uid  = $_SESSION['user_id'];
$msg  = $conn->real_escape_string($_POST['mensaje'] ?? '');
$nombre_archivo   = null;
$archivo_original = null;
$adjunto_tipo     = null;
$adjunto_id       = null;

$file = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $file = $_FILES['foto'];
} elseif (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
    $file = $_FILES['archivo'];
}

if ($file) {
    $extension        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $nombre_archivo   = time() . "_" . bin2hex(random_bytes(5)) . "." . $extension;
    $archivo_original = $file['name'];
    move_uploaded_file($file['tmp_name'], "uploads/" . $nombre_archivo);
}

if (!empty($_POST['adjunto_tipo']) && !empty($_POST['adjunto_id'])) {
    $adjunto_tipo = $conn->real_escape_string($_POST['adjunto_tipo']);
    $adjunto_id   = intval($_POST['adjunto_id']);
}

$stmt = $conn->prepare("INSERT INTO mensajes (usuario_id, mensaje, archivo, archivo_original, adjunto_tipo, adjunto_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssi", $uid, $msg, $nombre_archivo, $archivo_original, $adjunto_tipo, $adjunto_id);
$stmt->execute();
$stmt->close();
?>
