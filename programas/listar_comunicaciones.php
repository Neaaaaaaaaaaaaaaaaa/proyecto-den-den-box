<?php
session_start();
include("conexion.php");

$rol = $_SESSION['rol'] ?? null;

if ($rol === null) {
    header("Location: ../html/login.html");
    exit;
}

// Redirigir según el rol a la página correspondiente
if ($rol == 1 || $rol == 2) {
    // Admin y Operador
    $historial = isset($_GET['historial']) ? '?historial=1' : '';
    header("Location: listar_comunicaciones_admin.php" . $historial);
} else if ($rol == 3 || $rol == 4) {
    // Residente y Propietario
    $historial = isset($_GET['historial']) ? '?historial=1' : '';
    header("Location: listar_comunicaciones_residente.php" . $historial);
} else {
    header("Location: ../html/login.html");
}
exit;
?>
