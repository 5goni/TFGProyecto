<?php
/**
 * ============================================================================
 * ARCHIVO: guardar_puntos.php
 * ============================================================================
 * PROPÓSITO:
 *   Endpoint AJAX que guarda las puntuaciones de tests en la base de datos.
 *   Permite que el frontend registre resultados de exámenes (aciertos/total).
 *
 * FUNCIONALIDAD CLAVE:
 *   - Valida autenticación del usuario (retorna error si no está logueado)
 *   - Recibe datos POST: id (historial), aciertos, total
 *   - Actualiza tabla 'historial' con puntuación del test
 *   - Responde con JSON (success: true/false)
 *   - Usa prepared statements para evitar inyecciones SQL
 *
 * PARÁMETROS POST REQUERIDOS:
 *   - id: ID del registro en tabla historial
 *   - aciertos: Número de respuestas correctas
 *   - total: Número total de preguntas
 *
 * RESPUESTA JSON:
 *   - {"success": true} si se guardó correctamente
 *   - {"error": "No autenticado"} si falta autenticación
 *
 * DEPENDENCIAS:
 *   - conn.php (conexión a BD)
 *   - Session activa (usuario autenticado)
 *
 * USO TÍPICO:
 *   fetch('guardar_puntos.php', {
 *     method: 'POST',
 *     body: new FormData({id: 123, aciertos: 8, total: 10})
 *   })
 *
 * ============================================================================
 */

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