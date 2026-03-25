<?php
include("conexion.php");

$id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
$nuevo_estado = isset($_POST['nuevo_estado']) ? trim((string)$_POST['nuevo_estado']) : '';

if($id_usuario <= 0){
    echo "<script>alert('Usuario invalido');window.location.href='../html/gestionar_usuarios.html';</script>";
    exit;
}

if($nuevo_estado !== '0' && $nuevo_estado !== '1'){
    echo "<script>alert('Estado invalido');window.location.href='../html/gestionar_usuarios.html';</script>";
    exit;
}

$stmt = mysqli_prepare($conexion, "UPDATE USUARIOS SET estado = ? WHERE id_usuario = ? LIMIT 1");
if(!$stmt){
    echo "<script>alert('No se pudo preparar la actualizacion');window.location.href='../html/gestionar_usuarios.html';</script>";
    exit;
}

mysqli_stmt_bind_param($stmt, "si", $nuevo_estado, $id_usuario);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if(!$ok){
    echo "<script>alert('No se pudo actualizar el estado');window.location.href='../html/gestionar_usuarios.html';</script>";
    exit;
}

echo "<script>alert('Estado de usuario actualizado correctamente');window.location.href='../html/gestionar_usuarios.html';</script>";
exit;
?>
