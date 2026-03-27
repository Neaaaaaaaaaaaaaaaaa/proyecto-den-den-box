<?php
include("../comun/conexion.php");

$id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
if($id_usuario <= 0){
	echo "<script>alert('Usuario inválido');window.location.href='../../html/comun/index.html';</script>";
	exit;
}

function limpiar_residente_por_persona($conexion, $id_persona) {
	$q = mysqli_query($conexion, "SELECT id_residente FROM RESIDENTES WHERE id_persona=".intval($id_persona));
	while($r = mysqli_fetch_assoc($q)){
		$id_residente = intval($r['id_residente']);
		mysqli_query($conexion, "DELETE FROM CONTACTOS_DE_EMERGENCIA WHERE id_residente=$id_residente");
		mysqli_query($conexion, "DELETE FROM MASCOTAS WHERE id_residente=$id_residente");
		mysqli_query($conexion, "DELETE FROM RESIDENTE_INMUEBLE WHERE id_residente=$id_residente");
		mysqli_query($conexion, "DELETE FROM RESIDENTES WHERE id_residente=$id_residente");
	}
}

function limpiar_propietario_por_persona($conexion, $id_persona) {
	$q = mysqli_query($conexion, "SELECT id_propietario FROM PROPIETARIOS WHERE id_persona=".intval($id_persona));
	while($r = mysqli_fetch_assoc($q)){
		$id_propietario = intval($r['id_propietario']);
		mysqli_query($conexion, "UPDATE INMUEBLES SET id_propietario=NULL WHERE id_propietario=$id_propietario");
		mysqli_query($conexion, "DELETE FROM PROPIETARIOS WHERE id_propietario=$id_propietario");
	}
}

mysqli_begin_transaction($conexion);

try {
	$r = mysqli_query($conexion, "SELECT id_persona FROM USUARIOS WHERE id_usuario=$id_usuario LIMIT 1");
	if(!$r || mysqli_num_rows($r) === 0){
		throw new Exception("No existe el usuario.");
	}

	$id_persona = intval(mysqli_fetch_assoc($r)['id_persona']);

	limpiar_residente_por_persona($conexion, $id_persona);
	limpiar_propietario_por_persona($conexion, $id_persona);

	mysqli_query($conexion, "DELETE FROM USUARIOS WHERE id_usuario=$id_usuario");
	mysqli_query($conexion, "DELETE FROM PERSONAS WHERE id_persona=$id_persona");

	mysqli_commit($conexion);
	echo "<script>alert('Usuario eliminado correctamente');window.location.href='../../html/admin/gestionar_usuarios.html';</script>";

} catch (Exception $e) {
	mysqli_rollback($conexion);
	$msg = addslashes($e->getMessage());
	echo "<script>alert('$msg');window.location.href='../../html/admin/gestionar_usuarios.html';</script>";
}

exit;
