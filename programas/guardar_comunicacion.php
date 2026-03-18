<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 2)) {
    header("Location: ../html/login.html");
    exit;
}

$titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
$tipo = mysqli_real_escape_string($conexion, $_POST['tipo']);
$estado = mysqli_real_escape_string($conexion, $_POST['estado']);
$contenido = mysqli_real_escape_string($conexion, $_POST['contenido']);

$sql = "INSERT INTO COMUNICACIONES (titulo, tipo, estado, contenido, fecha) VALUES ('$titulo','$tipo','$estado','$contenido',NOW())";

mysqli_query($conexion, $sql);

header("Location: ../html/placeholders/comunicaciones.html");
exit;
?>