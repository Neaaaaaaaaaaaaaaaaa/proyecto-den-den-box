<?php
session_start();
include("../comun/conexion.php");

// Verificar si el usuario esta logueado y es operador o admin
$rol = isset($_SESSION['id_rol']) ? intval($_SESSION['id_rol']) : intval($_SESSION['rol'] ?? 0);
if (!isset($_SESSION['id_usuario']) || ($rol !== 2 && $rol !== 1)) {
    header("Location: ../../html/comun/login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $residente = trim($_POST['residente'] ?? '');
    $empresa = trim($_POST['empresa'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');
    $estado = trim($_POST['estado'] ?? '');

    // Insertar en la base de datos
    $sql = "INSERT INTO paquetes (residente, empresa, observaciones, estado) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssss", $residente, $empresa, $observaciones, $estado);

    if ($stmt->execute()) {
        header("Location: ../../html/operador/operator_dashboard.html?paquete=ok");
        exit();
    } else {
        echo "Error al registrar el paquete: " . $conexion->error;
    }

    $stmt->close();
    $conexion->close();
}
?>