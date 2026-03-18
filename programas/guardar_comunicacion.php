<?php
include("conexion.php");

$titulo = $_POST['titulo'];
$tipo = $_POST['tipo'];
$estado = $_POST['estado'];
$contenido = $_POST['contenido'];

$sql = "INSERT INTO COMUNICACIONES (titulo, tipo, estado, contenido, fecha)
VALUES ('$titulo','$tipo','$estado','$contenido',NOW())";

mysqli_query($conexion, $sql);

header("Location: ../html/admin/comunicaciones.html");
?>