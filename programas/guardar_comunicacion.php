<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['rol']) || ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 2)) {
    header("Location: ../html/login.html");
    exit;
}

// Compatibilidad con bases antiguas: agrega id_inmueble si aún no existe.
$check_col = mysqli_query($conexion, "SHOW COLUMNS FROM COMUNICACIONES LIKE 'id_inmueble'");
if($check_col && mysqli_num_rows($check_col) === 0){
    mysqli_query($conexion, "ALTER TABLE COMUNICACIONES ADD COLUMN id_inmueble INT NULL");
}

$titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
$tipo = mysqli_real_escape_string($conexion, $_POST['tipo']);
$estado = mysqli_real_escape_string($conexion, $_POST['estado']);
$contenido = mysqli_real_escape_string($conexion, $_POST['contenido']);
$destinatario = $_POST['destinatario'] ?? 'global';


$id_inmueble = NULL;

if ($destinatario === 'inmueble') {
    $torre = trim($_POST['torre'] ?? '');
    $numero_inmueble = trim($_POST['numero_inmueble'] ?? '');

    if ($torre === '' || $numero_inmueble === '') {
        echo "<script>
        alert('Debes seleccionar torre y apartamento para comunicación específica');
        window.history.back();
        </script>";
        exit;
    }

    $torre_sql = mysqli_real_escape_string($conexion, $torre);
    $numero_sql = mysqli_real_escape_string($conexion, $numero_inmueble);
    $sql_buscar = "SELECT i.id_inmueble
                   FROM INMUEBLES i
                   INNER JOIN TORRES t ON i.id_torre = t.id_torre
                   WHERE t.nombre='$torre_sql' AND i.numero='$numero_sql'
                   LIMIT 1";
    $resultado_buscar = mysqli_query($conexion, $sql_buscar);

    if ($resultado_buscar && mysqli_num_rows($resultado_buscar) > 0) {
        $fila = mysqli_fetch_assoc($resultado_buscar);
        $id_inmueble = intval($fila['id_inmueble']);
    }

    if ($id_inmueble === NULL) {
        echo "<script>
        alert('No se encontró el inmueble con la torre y apartamento seleccionados');
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

header("Location: ../html/placeholders/comunicaciones.html");
exit;
?>