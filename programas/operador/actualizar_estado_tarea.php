<?php
session_start();
include("../comun/conexion.php");

$rol = isset($_SESSION['id_rol']) ? intval($_SESSION['id_rol']) : intval($_SESSION['rol'] ?? 0);
if (!isset($_SESSION['id_usuario']) || ($rol !== 2 && $rol !== 1)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_tarea'], $_POST['nuevo_estado'])) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Parametros no validos']);
    exit();
}

$id_tarea = intval($_POST['id_tarea']);
$nuevo_estado = trim($_POST['nuevo_estado']);
$estados_validos = ['Activo', 'Pendiente', 'Finalizado'];

if (!in_array($nuevo_estado, $estados_validos, true)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Estado invalido']);
    exit();
}

$sql = "UPDATE tareas SET estado = ? WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("si", $nuevo_estado, $id_tarea);

header('Content-Type: application/json; charset=utf-8');
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Tarea actualizada correctamente']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar la tarea']);
}

$stmt->close();
$conexion->close();
