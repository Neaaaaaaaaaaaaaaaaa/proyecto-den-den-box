<?php
session_start();
include 'conexion.php';

// Verificar que es operador o admin
if (!isset($_SESSION['id_usuario']) || ($_SESSION['id_rol'] != 2 && $_SESSION['id_rol'] != 1)) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_tarea']) && isset($_POST['nuevo_estado'])) {
    $id_tarea = intval($_POST['id_tarea']);
    $nuevo_estado = $_POST['nuevo_estado'];
    
    // Validar que el estado sea válido
    $estados_validos = ['Activo', 'Pendiente', 'Finalizado'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        http_response_code(400);
        echo json_encode(['error' => 'Estado inválido']);
        exit();
    }
    
    // Actualizar el estado de la tarea
    $sql = "UPDATE tareas SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $id_tarea);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'mensaje' => 'Tarea actualizada correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar la tarea']);
    }
    
    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros no válidos']);
}
?>
