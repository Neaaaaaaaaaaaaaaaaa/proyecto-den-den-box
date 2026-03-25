<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado y es operador o admin
if (!isset($_SESSION['id_usuario']) || ($_SESSION['id_rol'] != 2 && $_SESSION['id_rol'] != 1)) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $residente = $_POST['residente'];
    $empresa = $_POST['empresa'];
    $observaciones = $_POST['observaciones'];
    $estado = $_POST['estado'];

    // Insertar en la base de datos
    $sql = "INSERT INTO paquetes (residente, empresa, observaciones, estado) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $residente, $empresa, $observaciones, $estado);

    if ($stmt->execute()) {
        echo "Paquete registrado exitosamente.";
        // Redirigir o mostrar mensaje
        header("Location: ../html/operator_dashboard.html");
    } else {
        echo "Error al registrar el paquete: " . $conn-> $error;
    }

    $stmt->close();
    $conn->close();
}
?>