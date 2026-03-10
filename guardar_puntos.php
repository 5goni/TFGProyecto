<?php
include 'conn.php';
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'No autenticado']));
}

if (isset($_POST['id']) && isset($_POST['aciertos'])) {
    $id = intval($_POST['id']);
    $aciertos = intval($_POST['aciertos']);
    $total = intval($_POST['total']);

    $stmt = $conn->prepare("UPDATE historial SET aciertos = ?, total_preguntas = ? WHERE id = ? AND user_id = ?");
    $user_id = $_SESSION['user_id'];
    $stmt->bind_param("iiii", $aciertos, $total, $id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true]);
}
?>