<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['rol']) || intval($_SESSION['rol']) !== 1){
    header("Location: listar_documentos.php?status=unauthorized");
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: listar_documentos.php?status=delete_error");
    exit;
}

$id_documento = isset($_POST['id_documento']) ? intval($_POST['id_documento']) : 0;
if($id_documento <= 0){
    header("Location: listar_documentos.php?status=delete_error");
    exit;
}

$sql = "SELECT url_documento FROM DOCUMENTOS WHERE id_documento=$id_documento LIMIT 1";
$res = mysqli_query($conexion, $sql);
if(!$res || mysqli_num_rows($res) === 0){
    header("Location: listar_documentos.php?status=delete_error");
    exit;
}

$fila = mysqli_fetch_assoc($res);
$archivo = basename($fila['url_documento']);
$ruta = "../documentos/".$archivo;

if(!mysqli_query($conexion, "DELETE FROM DOCUMENTOS WHERE id_documento=$id_documento")){
    header("Location: listar_documentos.php?status=delete_error");
    exit;
}

if(is_file($ruta)){
    @unlink($ruta);
}

header("Location: listar_documentos.php?status=deleted");
exit;
