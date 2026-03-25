<?php
// Conectar a la base de datos
include 'conexion.php';

// Verificar si se enviaron datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $tipo = $_POST['tipo'];
    $asunto = $_POST['asunto'];
    $descripcion = $_POST['descripcion'];

    // Aquí deberías obtener el ID del residente de la sesión
    // Por ahora usaremos un ID fijo para pruebas
    $residente_id = 1; // Cambiar esto por el ID real de la sesión

    // Preparar la consulta SQL
    $sql = "INSERT INTO pqrs (residente_id, tipo, asunto, descripcion, fecha, estado) VALUES (?, ?, ?, ?, NOW(), 'pendiente')";

    // Preparar la declaración
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $residente_id, $tipo, $asunto, $descripcion);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "<script>alert('PQRS registrado exitosamente'); window.location.href='../html/placeholders/registrar_pqrs.html';</script>";
    } else {
        echo "<script>alert('Error al registrar PQRS: " . $stmt->error . "'); window.history.back();</script>";
    }

    // Cerrar la declaración y conexión
    $stmt->close();
    $conn->close();
} else {
    // Si no es una petición POST, redirigir al formulario
    header("Location: ../html/placeholders/registrar_pqrs.html");
    exit();
}
?>