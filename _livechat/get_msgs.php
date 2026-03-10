<?php
include 'config.php';

$res = $conn->query("
    SELECT u.nombre, m.mensaje, m.archivo, m.archivo_original, m.adjunto_tipo, m.adjunto_id,
           h.pregunta AS adj_titulo, h.resumen AS adj_resumen, h.contenido_json AS adj_json,
           h.aciertos, h.total_preguntas
    FROM mensajes m
    JOIN usuarios u ON m.usuario_id = u.id
    LEFT JOIN historial h ON m.adjunto_id = h.id AND m.adjunto_tipo IS NOT NULL
    ORDER BY m.fecha DESC LIMIT 40
");

$rows = array_reverse($res->fetch_all(MYSQLI_ASSOC));
echo json_encode($rows);
?>
