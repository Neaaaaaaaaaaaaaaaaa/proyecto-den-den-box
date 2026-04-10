<?php
session_start();
include("../comun/conexion.php");

if (!isset($_SESSION['rol']) || intval($_SESSION['rol']) !== 1) {
    header("Location: ../../html/comun/login.html");
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: listar_novedades_admin.php");
    exit;
}

// Compatibilidad con bases antiguas: agrega campo de asignacion si aun no existe.
$check_col_asig = mysqli_query($conexion, "SHOW COLUMNS FROM NOVEDAD LIKE 'id_usuario_asignado'");
if($check_col_asig && mysqli_num_rows($check_col_asig) === 0){
    mysqli_query($conexion, "ALTER TABLE NOVEDAD ADD COLUMN id_usuario_asignado INT NULL");
}

$id_novedad = isset($_POST['id_novedad']) ? intval($_POST['id_novedad']) : 0;
$id_operador_raw = isset($_POST['id_operador']) ? trim((string)$_POST['id_operador']) : '';

if($id_novedad <= 0){
    header("Location: listar_novedades_admin.php");
    exit;
}

$id_operador_sql = "NULL";

if($id_operador_raw !== ''){
    $id_operador = intval($id_operador_raw);
    $sql_op = "SELECT id_usuario FROM USUARIOS WHERE id_usuario=$id_operador AND id_rol=2 LIMIT 1";
    $res_op = mysqli_query($conexion, $sql_op);
    if($res_op && mysqli_num_rows($res_op) > 0){
        $id_operador_sql = (string)$id_operador;
    }
}

mysqli_query($conexion, "UPDATE NOVEDAD SET id_usuario_asignado=$id_operador_sql WHERE id_novedad=$id_novedad LIMIT 1");

header("Location: listar_novedades_admin.php");
exit;
