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
$destinatario = $_POST['destinatario'] ?? 'global';

$id_inmueble = NULL;

if ($destinatario === 'inmueble' && !empty($_POST['num_inmueble'])) {
    $num_inmueble = mysqli_real_escape_string($conexion, $_POST['num_inmueble']);
    
    // Buscar el id_inmueble por número
    $sql_buscar = "SELECT id_inmueble FROM INMUEBLES WHERE numero='$num_inmueble'";
    $resultado_buscar = mysqli_query($conexion, $sql_buscar);
    
    if (mysqli_num_rows($resultado_buscar) > 0) {
        $fila = mysqli_fetch_assoc($resultado_buscar);
        $id_inmueble = $fila['id_inmueble'];
    } else {
        echo "<script>
        alert('El número de inmueble especificado no existe');
        window.history.back();
        </script>";
        exit;
    }
}

if ($id_inmueble === NULL) {
    $sql = "INSERT INTO COMUNICACIONES (titulo, tipo, estado, contenido, fecha, id_inmueble) VALUES ('$titulo','$tipo','$estado','$contenido',NOW(),NULL)";
} else {
    $sql = "INSERT INTO COMUNICACIONES (titulo, tipo, estado, contenido, fecha, id_inmueble) VALUES ('$titulo','$tipo','$estado','$contenido',NOW(),'$id_inmueble')";
}

mysqli_query($conexion, $sql);

header("Location: ../html/placeholders/comunicaciones.php");
exit;
?>